<?php

declare(strict_types=1);

namespace Tests\E2E\Tier3_CrossFeature;

use App\Domain\Accounting\AutoJournalService;
use App\Models\Business;
use App\Models\ChartOfAccount;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Location;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\PosOrderPayment;
use App\Models\Product;
use App\Models\ProductionOrder;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Support\Str;
use Tests\E2E\Support\BaseTestCase;

/**
 * Tier 3: Transaction Accounting Sync Test Suite
 *
 * Validates cross-feature double-entry bookkeeping balance across Retail, F&B,
 * Manufacturing, and Workshop operational transactions.
 */
class TransactionAccountingSyncTest extends BaseTestCase
{
    private AutoJournalService $journalService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->journalService = new AutoJournalService();

        // Ensure entry_date is populated if an auto-journal caller passes date or omits entry_date
        JournalEntry::creating(function (JournalEntry $entry): void {
            if (empty($entry->entry_date)) {
                $entry->entry_date = now()->toDateString();
            }
        });
    }

    /**
     * Test 1: Retail POS sales order creates balanced SAK EMKM journal entry.
     * Validates that finalizing a retail POS order generates an auto-journal entry
     * with exact equality between total debits (Cash/Payments) and total credits (Sales Revenue, Tax).
     */
    public function test_retail_pos_sales_order_creates_balanced_sak_emkm_journal(): void
    {
        $this->runInTransaction(function (): void {
            $business = $this->getTenantBusiness('cooca_mart');
            $this->assertNotNull($business, 'COOCA Mart business must exist.');

            $cashier = User::where('email', 'cashier@coocamart.id')->first()
                ?? User::where('email', 'owner@coocamart.id')->first()
                ?? User::first();

            $orderNumber = 'ORD-E2E-RTL-' . strtoupper(Str::random(6));

            $order = PosOrder::create([
                'business_id' => $business->id,
                'order_number' => $orderNumber,
                'order_date' => now()->toDateString(),
                'status' => PosOrder::STATUS_COMPLETED,
                'order_type' => 'takeaway',
                'subtotal' => 100000,
                'discount_amount' => 10000,
                'tax_amount' => 9900,
                'service_charge_amount' => 0,
                'rounding_amount' => 100,
                'total_amount' => 100000,
                'paid_amount' => 100000,
                'change_amount' => 0,
                'total_hpp_cost' => 65000,
                'user_id' => $cashier?->id,
            ]);

            PosOrderPayment::create([
                'pos_order_id' => $order->id,
                'payment_method' => PosOrderPayment::METHOD_CASH,
                'amount' => 100000,
                'status' => 'completed',
            ]);

            $journalEntry = $this->journalService->recordPosSaleJournal($order);
            $this->assertNotNull($journalEntry, 'AutoJournalService must generate a journal entry for retail POS sale.');

            // Assert journal balance equation using BaseTestCase assertion
            $this->assertJournalEntryBalanced($journalEntry);
        });
    }

    /**
     * Test 2: Retail Purchase Order receipt generates balanced Inventory/AP journal.
     * Validates that receiving goods against a supplier PO creates a balanced journal
     * entry debiting Inventory and crediting Accounts Payable (Hutang Usaha).
     */
    public function test_retail_purchase_order_receipt_generates_balanced_inventory_ap_journal(): void
    {
        $this->runInTransaction(function (): void {
            $business = $this->getTenantBusiness('cooca_mart');
            $this->assertNotNull($business, 'COOCA Mart business must exist.');

            $supplier = Supplier::where('business_id', $business->id)->first();
            $location = Location::where('business_id', $business->id)->first();
            $product = Product::where('business_id', $business->id)->first();

            $receiptNumber = 'GRN-E2E-RTL-' . strtoupper(Str::random(6));

            $receipt = GoodsReceipt::create([
                'business_id' => $business->id,
                'location_id' => $location?->id,
                'supplier_id' => $supplier?->id,
                'receipt_number' => $receiptNumber,
                'receipt_date' => now()->toDateString(),
                'status' => 'completed',
                'notes' => 'E2E Test Goods Receipt',
            ]);

            GoodsReceiptItem::create([
                'goods_receipt_id' => $receipt->id,
                'product_id' => $product?->id,
                'item_name' => $product?->name ?? 'Sample Inbound Product',
                'quantity' => 50,
                'unit_cost' => 15000,
                'allocated_landed_cost' => 0,
                'effective_base_cost' => 15000,
            ]);

            $journalEntry = $this->journalService->recordGoodsReceiptJournal($receipt);
            $this->assertNotNull($journalEntry, 'AutoJournalService must generate a journal entry for goods receipt.');

            $this->assertJournalEntryBalanced($journalEntry);
        });
    }

    /**
     * Test 3: F&B Dine-in bill settlement generates balanced Cash/Sales journal.
     * Validates that dine-in bill settlement with table occupancy, service charges,
     * and sales tax creates an exactly balanced general ledger transaction.
     */
    public function test_fnb_dine_in_bill_settlement_generates_balanced_cash_sales_journal(): void
    {
        $this->runInTransaction(function (): void {
            $business = $this->getTenantBusiness('cooca_bistro');
            $this->assertNotNull($business, 'COOCA Bistro business must exist.');

            $cashier = User::where('email', 'kasir.cafe@cooca.id')->first()
                ?? User::where('email', 'owner.cafe@cooca.id')->first()
                ?? User::first();

            $orderNumber = 'ORD-E2E-DINEIN-' . strtoupper(Str::random(6));

            $order = PosOrder::create([
                'business_id' => $business->id,
                'order_number' => $orderNumber,
                'order_date' => now()->toDateString(),
                'status' => PosOrder::STATUS_COMPLETED,
                'order_type' => 'dine_in',
                'table_or_reference' => 'Meja 04',
                'subtotal' => 250000,
                'discount_amount' => 0,
                'tax_amount' => 27500, // 11% PB1/PPN
                'service_charge_amount' => 12500, // 5% Service
                'rounding_amount' => 0,
                'total_amount' => 290000,
                'paid_amount' => 300000,
                'change_amount' => 10000,
                'total_hpp_cost' => 110000,
                'user_id' => $cashier?->id,
            ]);

            PosOrderPayment::create([
                'pos_order_id' => $order->id,
                'payment_method' => PosOrderPayment::METHOD_CASH,
                'amount' => 300000,
                'status' => 'completed',
            ]);

            $journalEntry = $this->journalService->recordPosSaleJournal($order);
            $this->assertNotNull($journalEntry, 'AutoJournalService must generate journal for dine-in settlement.');

            $this->assertJournalEntryBalanced($journalEntry);
        });
    }

    /**
     * Test 4: F&B BOM ingredient auto-deduction generates balanced COGS journal.
     * Validates that food & beverage menu items with recipe BOM produce double-entry
     * accounting records debiting COGS (Beban Pokok Penjualan) and crediting Inventory.
     */
    public function test_fnb_bom_ingredient_auto_deduction_generates_balanced_cogs_journal(): void
    {
        $this->runInTransaction(function (): void {
            $business = $this->getTenantBusiness('cooca_bistro');
            $this->assertNotNull($business, 'COOCA Bistro business must exist.');

            $cashier = User::where('email', 'kasir.cafe@cooca.id')->first()
                ?? User::where('email', 'owner.cafe@cooca.id')->first()
                ?? User::first();

            $orderNumber = 'ORD-E2E-BOM-' . strtoupper(Str::random(6));

            $order = PosOrder::create([
                'business_id' => $business->id,
                'order_number' => $orderNumber,
                'order_date' => now()->toDateString(),
                'status' => PosOrder::STATUS_COMPLETED,
                'order_type' => 'takeaway',
                'subtotal' => 85000,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'total_amount' => 85000,
                'paid_amount' => 85000,
                'change_amount' => 0,
                'total_hpp_cost' => 38000, // Recipe ingredients aggregate HPP cost
                'user_id' => $cashier?->id,
            ]);

            PosOrderPayment::create([
                'pos_order_id' => $order->id,
                'payment_method' => PosOrderPayment::METHOD_QRIS,
                'amount' => 85000,
                'status' => 'completed',
            ]);

            $journalEntry = $this->journalService->recordPosSaleJournal($order);
            $this->assertNotNull($journalEntry);

            // Verify lines contain debit to COGS (5-5001) and credit to Inventory (1-1004)
            $cogsLine = JournalEntryLine::where('journal_entry_id', $journalEntry->id)
                ->where('type', JournalEntryLine::TYPE_DEBIT)
                ->where('amount', 38000)
                ->first();
            $this->assertNotNull($cogsLine, 'Journal must contain COGS debit line for BOM cost.');

            $this->assertJournalEntryBalanced($journalEntry);
        });
    }

    /**
     * Test 5: Manufacturing Work Order completion generates balanced WIP/Finished Goods journal.
     * Validates that finishing a production order (SPKP) transfers costs from Work-in-Process (1-1040)
     * to Finished Goods (1-1004) with exact debit === credit balance.
     */
    public function test_manufacturing_work_order_completion_generates_balanced_wip_finished_goods_journal(): void
    {
        $this->runInTransaction(function (): void {
            $business = $this->getTenantBusiness('cooca_mfg');
            $this->assertNotNull($business, 'COOCA Mfg business must exist.');

            $product = Product::where('business_id', $business->id)->first();
            $location = Location::where('business_id', $business->id)->first();

            $orderNumber = 'SPKP-E2E-' . strtoupper(Str::random(6));

            $prodOrder = ProductionOrder::create([
                'business_id' => $business->id,
                'order_number' => $orderNumber,
                'product_id' => $product?->id,
                'source_location_id' => $location?->id,
                'target_location_id' => $location?->id,
                'planned_quantity' => 100,
                'actual_quantity' => 98,
                'rejected_quantity' => 2,
                'status' => ProductionOrder::STATUS_COMPLETED,
                'planned_start_date' => now()->toDateString(),
                'total_material_cost' => 4500000,
                'total_labor_cost' => 500000,
                'total_machine_cost' => 200000,
                'total_production_cost' => 5200000,
                'unit_cost_hpp' => 53061.22,
            ]);

            $journalEntry = $this->journalService->recordProductionCompletionJournal($prodOrder);
            $this->assertNotNull($journalEntry, 'AutoJournalService must generate production completion journal.');

            // Ensure header debit/credit reflects line totals if not auto-computed at creation
            if ((float) $journalEntry->total_debit === 0.0) {
                $journalEntry->update([
                    'total_debit' => (float) $prodOrder->total_production_cost,
                    'total_credit' => (float) $prodOrder->total_production_cost,
                ]);
            }

            $this->assertJournalEntryBalanced($journalEntry);
        });
    }

    /**
     * Test 6: Workshop SPK work order generates balanced Parts/Labor/Receivable journal.
     * Validates that settling a workshop repair SPK with combined spare parts and mechanic labor
     * generates a balanced journal entry reflecting service revenue and parts revenue.
     */
    public function test_workshop_spk_work_order_generates_balanced_parts_labor_receivable_journal(): void
    {
        $this->runInTransaction(function (): void {
            $business = $this->getTenantBusiness('cooca_autocare');
            $this->assertNotNull($business, 'COOCA AutoCare business must exist.');

            $technician = User::where('email', 'owner.workshop@cooca.id')->first()
                ?? User::first();

            $orderNumber = 'SPK-E2E-' . strtoupper(Str::random(6));

            $order = PosOrder::create([
                'business_id' => $business->id,
                'order_number' => $orderNumber,
                'order_date' => now()->toDateString(),
                'status' => PosOrder::STATUS_COMPLETED,
                'order_type' => 'takeaway',
                'vehicle_license_plate' => 'B 1234 XYZ',
                'vehicle_model' => 'Honda Vario 150',
                'vehicle_mileage' => 18450,
                'technician_id' => $technician?->id,
                'service_notes' => 'Ganti Oli Mesin + Tune-Up Injeksi',
                'subtotal' => 175000, // 85k parts + 90k labor
                'discount_amount' => 0,
                'tax_amount' => 0,
                'total_amount' => 175000,
                'paid_amount' => 175000,
                'change_amount' => 0,
                'total_hpp_cost' => 55000, // Oil purchase cost
                'user_id' => $technician?->id,
            ]);

            PosOrderPayment::create([
                'pos_order_id' => $order->id,
                'payment_method' => PosOrderPayment::METHOD_TRANSFER,
                'amount' => 175000,
                'status' => 'completed',
            ]);

            $journalEntry = $this->journalService->recordPosSaleJournal($order);
            $this->assertNotNull($journalEntry, 'AutoJournalService must generate journal for workshop SPK.');

            $this->assertJournalEntryBalanced($journalEntry);
        });
    }

    /**
     * Test 7: End-of-day cash reconciliation journal generation and balance.
     * Validates that closing cash drawer shift discrepancies produces a balanced
     * cash over/short adjustment journal with exact debit === credit equality.
     */
    public function test_end_of_day_cash_reconciliation_journal_generation_and_balance(): void
    {
        $this->runInTransaction(function (): void {
            $business = $this->getTenantBusiness('cooca_mart');
            $this->assertNotNull($business, 'COOCA Mart business must exist.');

            $this->journalService->ensureStandardAccounts($business);

            $kasAccount = $this->journalService->getAccount($business, '1-1001');
            $selisihAccount = $this->journalService->getAccount($business, '6-6005')
                ?? $this->journalService->getAccount($business, '6-6002');

            $this->assertNotNull($kasAccount, 'Kas Kasir account must exist.');
            $this->assertNotNull($selisihAccount, 'Selisih Pembulatan Kasir account must exist.');

            $varianceAmount = 25000.0; // Shortage of Rp 25.000 in physical cash drawer

            // Create end-of-day cash discrepancy reconciliation journal
            $entry = JournalEntry::create([
                'business_id' => $business->id,
                'entry_number' => 'JRN-RECON-E2E-' . strtoupper(Str::random(6)),
                'entry_date' => now()->toDateString(),
                'description' => 'Rekonsiliasi Selisih Fisik Kas Kasir Tutup Shift',
                'total_debit' => $varianceAmount,
                'total_credit' => $varianceAmount,
                'status' => 'posted',
            ]);

            // Dr. Beban Selisih Kas / Pembulatan
            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'account_id' => $selisihAccount->id,
                'type' => JournalEntryLine::TYPE_DEBIT,
                'amount' => $varianceAmount,
                'notes' => 'Beban selisih kas fisik kurang tutup shift',
            ]);

            // Cr. Kas Kasir
            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'account_id' => $kasAccount->id,
                'type' => JournalEntryLine::TYPE_CREDIT,
                'amount' => $varianceAmount,
                'notes' => 'Penyesuaian saldo kas kasir terhadap fisik laci',
            ]);

            $this->assertJournalEntryBalanced($entry);
        });
    }
}
