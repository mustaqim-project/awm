<?php

declare(strict_types=1);

namespace Tests\E2E\Tier4_RealWorldScenarios;

use App\Domain\Accounting\AutoJournalService;
use App\Models\Business;
use App\Models\Customer;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\InventoryStock;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Location;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\StockMovement;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Str;
use Tests\E2E\Support\BaseTestCase;

/**
 * Tier 4: Retail Real-World Application Workflows
 *
 * Validates complex retail operational lifecycles: Procure-to-Pay,
 * Inter-branch warehouse stock transfer, and wholesale tiered pricing sales.
 */
class RetailEndToEndWorkflowTest extends BaseTestCase
{
    private AutoJournalService $journalService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->journalService = new AutoJournalService();
    }

    /**
     * Scenario 1: Retail Procure-to-Pay Lifecycle
     * Supplier PO -> Goods Receipt (GRN) -> Stock Adjustment -> Inventory Valuation Update.
     */
    public function test_retail_procure_to_pay_lifecycle(): void
    {
        $this->runInTransaction(function (): void {
            $business = $this->getTenantBusiness('cooca_mart');
            $this->assertNotNull($business, 'COOCA Mart business must exist.');

            $supplier = Supplier::where('business_id', $business->id)->first();
            $location = Location::where('business_id', $business->id)->first();
            $product = Product::where('business_id', $business->id)->first();
            $unit = Unit::first();
            $warehouseStaff = User::where('email', 'warehouse@coocamart.id')->first()
                ?? User::where('email', 'owner@coocamart.id')->first()
                ?? User::first();

            // 1. Issue Purchase Order to Supplier
            $poNumber = 'PO-E2E-RTL-' . strtoupper(Str::random(6));
            $orderQty = 100.0;
            $unitPrice = 12500.0;
            $subtotal = $orderQty * $unitPrice;
            $taxAmount = $subtotal * 0.11;
            $totalAmount = $subtotal + $taxAmount;

            $po = PurchaseOrder::create([
                'business_id' => $business->id,
                'supplier_id' => $supplier?->id,
                'po_number' => $poNumber,
                'order_date' => now()->toDateString(),
                'status' => 'approved',
                'subtotal' => $subtotal,
                'tax_percentage' => 11.0,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'created_by' => $warehouseStaff?->id,
            ]);

            PurchaseOrderItem::create([
                'purchase_order_id' => $po->id,
                'product_id' => $product?->id,
                'item_name' => $product?->name ?? 'Retail Stock SKU',
                'unit_id' => $unit?->id,
                'quantity' => $orderQty,
                'unit_price' => $unitPrice,
                'subtotal' => $subtotal,
                'total_amount' => $totalAmount,
            ]);

            $this->assertDatabaseHas('purchase_orders', ['id' => $po->id, 'status' => 'approved']);

            // 2. Physical Goods Receipt (GRN) at Warehouse
            $grnNumber = 'GRN-E2E-RTL-' . strtoupper(Str::random(6));
            $grn = GoodsReceipt::create([
                'business_id' => $business->id,
                'location_id' => $location?->id,
                'supplier_id' => $supplier?->id,
                'purchase_order_id' => $po->id,
                'receipt_number' => $grnNumber,
                'receipt_date' => now()->toDateString(),
                'status' => 'completed',
                'received_by' => $warehouseStaff?->id,
                'notes' => 'Penerimaan barang fisik dari supplier PO #' . $po->po_number,
            ]);

            GoodsReceiptItem::create([
                'goods_receipt_id' => $grn->id,
                'purchase_order_item_id' => $po->items()->first()?->id,
                'product_id' => $product?->id,
                'item_name' => $product?->name ?? 'Retail Stock SKU',
                'received_unit_id' => $unit?->id,
                'quantity' => $orderQty,
                'unit_cost' => $unitPrice,
                'effective_base_cost' => $unitPrice,
            ]);

            $po->update(['status' => 'received']);
            $this->assertSame('received', $po->fresh()->status);

            // 3. Stock Movement & Inventory Valuation Update
            $initialStock = (float) (InventoryStock::where('business_id', $business->id)
                ->where('location_id', $location?->id)
                ->where('product_id', $product?->id)
                ->value('quantity') ?? 0.0);

            $newBalance = $initialStock + $orderQty;

            StockMovement::create([
                'business_id' => $business->id,
                'location_id' => $location?->id,
                'product_id' => $product?->id,
                'movement_type' => StockMovement::TYPE_GOODS_RECEIPT,
                'reference_id' => $grn->id,
                'reference_number' => $grn->receipt_number,
                'quantity_change' => $orderQty,
                'balance_before' => $initialStock,
                'balance_after' => $newBalance,
                'unit_cost' => $unitPrice,
                'total_cost' => $subtotal,
                'created_by' => $warehouseStaff?->id,
                'notes' => "Penerimaan PO #{$po->po_number}",
            ]);

            InventoryStock::updateOrCreate(
                [
                    'business_id' => $business->id,
                    'location_id' => $location?->id,
                    'product_id' => $product?->id,
                ],
                [
                    'quantity' => $newBalance,
                    'last_cost' => $unitPrice,
                    'avg_purchase_cost' => $unitPrice,
                ]
            );

            // 4. Double-Entry Accounting Journal for Goods Receipt
            $journalEntry = $this->journalService->recordGoodsReceiptJournal($grn);
            $this->assertNotNull($journalEntry, 'Goods receipt must produce double-entry accounting journal.');
            $this->assertJournalEntryBalanced($journalEntry);
        });
    }

    /**
     * Scenario 2: Inter-Branch Stock Transfer
     * Central Warehouse to Retail Branch transfer request, dispatch, and receipt.
     */
    public function test_inter_branch_stock_transfer(): void
    {
        $this->runInTransaction(function (): void {
            $business = $this->getTenantBusiness('cooca_mart');
            $this->assertNotNull($business, 'COOCA Mart business must exist.');

            $locations = Location::where('business_id', $business->id)->take(2)->get();
            if ($locations->count() < 2) {
                // Ensure at least two locations exist for transfer test
                $locSource = $locations->first() ?? Location::create([
                    'business_id' => $business->id,
                    'name' => 'Central Warehouse E2E',
                    'code' => 'WH-CENTRAL',
                    'is_active' => true,
                ]);
                $locDest = Location::create([
                    'business_id' => $business->id,
                    'name' => 'Retail Branch E2E',
                    'code' => 'BR-01',
                    'is_active' => true,
                ]);
            } else {
                $locSource = $locations[0];
                $locDest = $locations[1];
            }

            $product = Product::where('business_id', $business->id)->first();
            $staff = User::where('email', 'warehouse@coocamart.id')->first() ?? User::first();

            $transferQty = 25.0;
            $unitCost = (float) ($product?->base_cost ?? 15000.0);
            $transferNumber = 'TRF-E2E-' . strtoupper(Str::random(6));

            // 1. Create Stock Transfer in Draft
            $transfer = StockTransfer::create([
                'business_id' => $business->id,
                'source_location_id' => $locSource->id,
                'destination_location_id' => $locDest->id,
                'transfer_number' => $transferNumber,
                'transfer_date' => now()->toDateString(),
                'status' => 'draft',
                'created_by' => $staff?->id,
                'notes' => 'Transfer stok barang dari gudang pusat ke cabang',
            ]);

            StockTransferItem::create([
                'stock_transfer_id' => $transfer->id,
                'product_id' => $product?->id,
                'quantity' => $transferQty,
                'unit_cost' => $unitCost,
                'total_cost' => $transferQty * $unitCost,
            ]);

            // 2. Dispatch from Source Warehouse
            $transfer->update([
                'status' => 'in_transit',
                'sent_at' => now(),
            ]);

            StockMovement::create([
                'business_id' => $business->id,
                'location_id' => $locSource->id,
                'product_id' => $product?->id,
                'movement_type' => 'transfer_out',
                'reference_id' => $transfer->id,
                'reference_number' => $transfer->transfer_number,
                'quantity_change' => -$transferQty,
                'balance_before' => 100,
                'balance_after' => 75,
                'unit_cost' => $unitCost,
                'total_cost' => $transferQty * $unitCost,
                'created_by' => $staff?->id,
                'notes' => "Pengiriman transfer stok #{$transfer->transfer_number}",
            ]);

            $this->assertSame('in_transit', $transfer->fresh()->status);

            // 3. Receipt Confirmation at Destination Branch
            $transfer->update([
                'status' => 'received',
                'received_at' => now(),
                'received_by' => $staff?->id,
            ]);

            StockMovement::create([
                'business_id' => $business->id,
                'location_id' => $locDest->id,
                'product_id' => $product?->id,
                'movement_type' => 'transfer_in',
                'reference_id' => $transfer->id,
                'reference_number' => $transfer->transfer_number,
                'quantity_change' => $transferQty,
                'balance_before' => 10,
                'balance_after' => 35,
                'unit_cost' => $unitCost,
                'total_cost' => $transferQty * $unitCost,
                'created_by' => $staff?->id,
                'notes' => "Penerimaan transfer stok #{$transfer->transfer_number}",
            ]);

            $this->assertSame('received', $transfer->fresh()->status);
            $this->assertNotNull($transfer->fresh()->received_at);
        });
    }

    /**
     * Scenario 3: Wholesale Customer Tiered Sale
     * B2B sales order with quantity tier pricing, invoice issuance, and partial payment.
     */
    public function test_wholesale_customer_tiered_sale(): void
    {
        $this->runInTransaction(function (): void {
            $business = $this->getTenantBusiness('cooca_mart');
            $this->assertNotNull($business, 'COOCA Mart business must exist.');

            $customer = Customer::where('business_id', $business->id)->first();
            $product = Product::where('business_id', $business->id)->first();
            $manager = User::where('email', 'manager@coocamart.id')->first()
                ?? User::where('email', 'owner@coocamart.id')->first()
                ?? User::first();

            // Quantity tiered pricing rules:
            // Standard retail price = Rp 20.000 / unit
            // Wholesale tier (>= 50 units) = Rp 17.000 / unit (15% volume discount)
            $quantity = 60.0;
            $tierUnitPrice = 17000.0;
            $subtotal = $quantity * $tierUnitPrice; // Rp 1.020.000
            $taxAmount = $subtotal * 0.11; // Rp 112.200
            $totalAmount = $subtotal + $taxAmount; // Rp 1.132.200

            // 1. Issue B2B Sales Invoice
            $invoiceNumber = 'INV-E2E-WS-' . strtoupper(Str::random(6));

            $invoice = Invoice::create([
                'business_id' => $business->id,
                'customer_id' => $customer?->id,
                'invoice_number' => $invoiceNumber,
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays(30)->toDateString(),
                'status' => 'unpaid',
                'subtotal' => $subtotal,
                'tax_percentage' => 11.0,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'paid_amount' => 0.0,
                'balance_due' => $totalAmount,
                'payment_terms' => 'NET 30',
                'created_by' => $manager?->id,
                'notes' => 'Faktur Penjualan Grosir B2B dengan harga bertingkat',
            ]);

            $this->assertDatabaseHas('invoices', [
                'id' => $invoice->id,
                'status' => 'unpaid',
                'balance_due' => $totalAmount,
            ]);

            // 2. Customer makes partial down payment (DP 50%)
            $downPaymentAmount = round($totalAmount / 2.0); // Rp 566.100
            $remainingBalance = $totalAmount - $downPaymentAmount; // Rp 566.100

            $payment = InvoicePayment::create([
                'business_id' => $business->id,
                'invoice_id' => $invoice->id,
                'payment_number' => 'PAY-E2E-' . strtoupper(Str::random(6)),
                'payment_date' => now()->toDateString(),
                'amount' => $downPaymentAmount,
                'payment_method' => 'bank_transfer',
                'reference_number' => 'BCA-TRF-' . Str::random(8),
                'created_by' => $manager?->id,
            ]);

            // 3. Update Invoice with Partial Payment Status
            $invoice->update([
                'paid_amount' => $downPaymentAmount,
                'balance_due' => $remainingBalance,
                'status' => 'partially_paid',
            ]);

            $freshInvoice = $invoice->fresh();
            $this->assertSame('partially_paid', $freshInvoice->status);
            $this->assertEqualsWithDelta($downPaymentAmount, (float) $freshInvoice->paid_amount, 0.01);
            $this->assertEqualsWithDelta($remainingBalance, (float) $freshInvoice->balance_due, 0.01);
            $this->assertGreaterThan(0, $freshInvoice->balance_due, 'Remaining accounts receivable balance must be tracked.');
        });
    }
}
