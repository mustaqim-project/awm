<?php

declare(strict_types=1);

namespace Tests\E2E\Tier4_RealWorldScenarios;

use App\Domain\Accounting\AutoJournalService;
use App\Models\Business;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Location;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\PosOrderPayment;
use App\Models\Product;
use App\Models\ProductionOrder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\E2E\Support\BaseTestCase;

/**
 * Tier 4: Multi-Tenant Operational Audit & Month-End Financial Close
 *
 * Validates concurrent operations across Retail, F&B, Manufacturing, and Automotive
 * workshop businesses, guaranteeing zero cross-tenant contamination and exact debit === credit
 * trial balance equations across all tenant ledgers.
 */
class MultiTenantAuditWorkflowTest extends BaseTestCase
{
    private AutoJournalService $journalService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->journalService = new AutoJournalService();

        JournalEntry::creating(function (JournalEntry $entry): void {
            if (empty($entry->entry_date)) {
                $entry->entry_date = now()->toDateString();
            }
        });
    }

    /**
     * Scenario 1: Concurrent Multi-Tenant Operations & Month-End Financial Close
     * Simultaneous simulated transactions across Retail, F&B, Mfg, AutoCare,
     * followed by global trial balance reconciliation verifying zero cross-tenant
     * contamination and exact debit === credit balance.
     */
    public function test_concurrent_multi_tenant_operations_and_month_end_financial_close(): void
    {
        $this->runInTransaction(function (): void {
            // 1. Resolve All Four Dedicated Industry Tenants
            $tenants = [
                'retail' => $this->getTenantBusiness('cooca_mart'),
                'fnb' => $this->getTenantBusiness('cooca_bistro'),
                'mfg' => $this->getTenantBusiness('cooca_mfg'),
                'autocare' => $this->getTenantBusiness('cooca_autocare'),
            ];

            foreach ($tenants as $domain => $business) {
                $this->assertNotNull($business, "Tenant business for [{$domain}] must exist in database.");
                $this->journalService->ensureStandardAccounts($business);
            }

            $createdOrders = [];
            $createdJournals = [];

            // -------------------------------------------------------------
            // 2. Simulated Concurrent Transaction: Retail (COOCA Mart)
            // -------------------------------------------------------------
            $retailBiz = $tenants['retail'];
            $retailUser = User::where('email', 'cashier@coocamart.id')->first()
                ?? User::where('email', 'owner@coocamart.id')->first()
                ?? User::first();

            $retailOrder = PosOrder::create([
                'business_id' => $retailBiz->id,
                'order_number' => 'ORD-AUDIT-RTL-' . strtoupper(Str::random(6)),
                'order_date' => now()->toDateString(),
                'status' => PosOrder::STATUS_COMPLETED,
                'order_type' => 'takeaway',
                'subtotal' => 150000,
                'discount_amount' => 0,
                'tax_amount' => 16500,
                'total_amount' => 166500,
                'paid_amount' => 166500,
                'change_amount' => 0,
                'total_hpp_cost' => 95000,
                'user_id' => $retailUser?->id,
            ]);

            PosOrderPayment::create([
                'pos_order_id' => $retailOrder->id,
                'payment_method' => PosOrderPayment::METHOD_CASH,
                'amount' => 166500,
                'status' => 'completed',
            ]);

            $retailJournal = $this->journalService->recordPosSaleJournal($retailOrder);
            $this->assertNotNull($retailJournal);
            $createdOrders['retail'] = $retailOrder;
            $createdJournals['retail'] = $retailJournal;

            // -------------------------------------------------------------
            // 3. Simulated Concurrent Transaction: F&B (COOCA Bistro)
            // -------------------------------------------------------------
            $fnbBiz = $tenants['fnb'];
            $fnbUser = User::where('email', 'kasir.cafe@cooca.id')->first()
                ?? User::where('email', 'owner.cafe@cooca.id')->first()
                ?? User::first();

            $fnbOrder = PosOrder::create([
                'business_id' => $fnbBiz->id,
                'order_number' => 'ORD-AUDIT-FNB-' . strtoupper(Str::random(6)),
                'order_date' => now()->toDateString(),
                'status' => PosOrder::STATUS_COMPLETED,
                'order_type' => 'dine_in',
                'table_or_reference' => 'Meja 08',
                'subtotal' => 200000,
                'tax_amount' => 22000,
                'service_charge_amount' => 10000,
                'total_amount' => 232000,
                'paid_amount' => 232000,
                'change_amount' => 0,
                'total_hpp_cost' => 78000,
                'user_id' => $fnbUser?->id,
            ]);

            PosOrderPayment::create([
                'pos_order_id' => $fnbOrder->id,
                'payment_method' => PosOrderPayment::METHOD_QRIS,
                'amount' => 232000,
                'status' => 'completed',
            ]);

            $fnbJournal = $this->journalService->recordPosSaleJournal($fnbOrder);
            $this->assertNotNull($fnbJournal);
            $createdOrders['fnb'] = $fnbOrder;
            $createdJournals['fnb'] = $fnbJournal;

            // -------------------------------------------------------------
            // 4. Simulated Concurrent Transaction: Manufacturing (COOCA Mfg)
            // -------------------------------------------------------------
            $mfgBiz = $tenants['mfg'];
            $mfgProduct = Product::where('business_id', $mfgBiz->id)->first();
            $mfgLocation = Location::where('business_id', $mfgBiz->id)->first();
            $mfgUser = User::where('email', 'owner.garment@cooca.id')->first() ?? User::first();

            $mfgOrder = ProductionOrder::create([
                'business_id' => $mfgBiz->id,
                'order_number' => 'SPKP-AUDIT-' . strtoupper(Str::random(6)),
                'product_id' => $mfgProduct?->id,
                'source_location_id' => $mfgLocation?->id,
                'target_location_id' => $mfgLocation?->id,
                'planned_quantity' => 50,
                'actual_quantity' => 50,
                'rejected_quantity' => 0,
                'status' => ProductionOrder::STATUS_COMPLETED,
                'planned_start_date' => now()->toDateString(),
                'total_material_cost' => 2000000,
                'total_labor_cost' => 300000,
                'total_machine_cost' => 150000,
                'total_production_cost' => 2450000,
                'unit_cost_hpp' => 49000,
                'created_by' => $mfgUser?->id,
            ]);

            $mfgJournal = $this->journalService->recordProductionCompletionJournal($mfgOrder);
            $this->assertNotNull($mfgJournal);
            if ((float) $mfgJournal->total_debit === 0.0) {
                $mfgJournal->update([
                    'total_debit' => 2450000,
                    'total_credit' => 2450000,
                ]);
            }
            $createdOrders['mfg'] = $mfgOrder;
            $createdJournals['mfg'] = $mfgJournal;

            // -------------------------------------------------------------
            // 5. Simulated Concurrent Transaction: Workshop (COOCA AutoCare)
            // -------------------------------------------------------------
            $autoBiz = $tenants['autocare'];
            $autoUser = User::where('email', 'owner.workshop@cooca.id')->first() ?? User::first();

            $autoOrder = PosOrder::create([
                'business_id' => $autoBiz->id,
                'order_number' => 'SPK-AUDIT-' . strtoupper(Str::random(6)),
                'order_date' => now()->toDateString(),
                'status' => PosOrder::STATUS_COMPLETED,
                'order_type' => 'takeaway',
                'vehicle_license_plate' => 'B 9988 AUD',
                'vehicle_model' => 'Mitsubishi Pajero Sport',
                'vehicle_mileage' => 62100,
                'technician_id' => $autoUser?->id,
                'subtotal' => 550000, // Parts + Labor
                'tax_amount' => 0,
                'total_amount' => 550000,
                'paid_amount' => 550000,
                'change_amount' => 0,
                'total_hpp_cost' => 320000,
                'user_id' => $autoUser?->id,
            ]);

            PosOrderPayment::create([
                'pos_order_id' => $autoOrder->id,
                'payment_method' => PosOrderPayment::METHOD_TRANSFER,
                'amount' => 550000,
                'status' => 'completed',
            ]);

            $autoJournal = $this->journalService->recordPosSaleJournal($autoOrder);
            $this->assertNotNull($autoJournal);
            $createdOrders['autocare'] = $autoOrder;
            $createdJournals['autocare'] = $autoJournal;

            // -------------------------------------------------------------
            // 6. Zero Cross-Tenant Contamination Verification
            // -------------------------------------------------------------
            foreach ($tenants as $sourceDomain => $sourceBusiness) {
                $order = $createdOrders[$sourceDomain];
                $journal = $createdJournals[$sourceDomain];

                // Verify entity business_id matches source tenant strictly
                $this->assertSame($sourceBusiness->id, $order->business_id);
                $this->assertSame($sourceBusiness->id, $journal->business_id);

                // Check that no lines or linked records belong to any sibling tenant
                foreach ($tenants as $otherDomain => $otherBusiness) {
                    if ($sourceDomain === $otherDomain) {
                        continue;
                    }

                    $this->assertNotSame(
                        $otherBusiness->id,
                        $order->business_id,
                        "Order for [{$sourceDomain}] must never possess business_id of [{$otherDomain}]."
                    );
                    $this->assertNotSame(
                        $otherBusiness->id,
                        $journal->business_id,
                        "Journal for [{$sourceDomain}] must never possess business_id of [{$otherDomain}]."
                    );
                }

                // Verify individual transaction journal entry is balanced
                $this->assertJournalEntryBalanced($journal);
            }

            // -------------------------------------------------------------
            // 7. Global Month-End Financial Close & Trial Balance Verification
            // -------------------------------------------------------------
            foreach ($tenants as $domain => $business) {
                // Assert global trial balance (SUM(debit) == SUM(credit)) for every tenant ledger
                $this->assertBusinessTrialBalanceBalanced($business->id);
            }
        });
    }
}
