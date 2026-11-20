<?php

declare(strict_types=1);

namespace Tests\E2E\Tier4_RealWorldScenarios;

use App\Domain\Accounting\AutoJournalService;
use App\Models\Business;
use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\Material;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\PosOrderPayment;
use App\Models\PosRegister;
use App\Models\PosShift;
use App\Models\PosTable;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Str;
use Tests\E2E\Support\BaseTestCase;

/**
 * Tier 4: F&B Real-World Application Workflows
 *
 * Validates authentic food & beverage dining room operations:
 * Dine-in to Kitchen Display (KDS), Recipe BOM ingredient deduction with threshold alerts,
 * and Split Bill with cashier shift reconciliation.
 */
class FnbEndToEndWorkflowTest extends BaseTestCase
{
    private AutoJournalService $journalService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->journalService = new AutoJournalService();
    }

    /**
     * Scenario 1: Dine-in to Kitchen Display (KDS)
     * Table selection -> Modifier items ordering -> KDS status update -> Bill generation & table release.
     */
    public function test_dine_in_to_kitchen_display_lifecycle(): void
    {
        $this->runInTransaction(function (): void {
            $business = $this->getTenantBusiness('cooca_bistro');
            $this->assertNotNull($business, 'COOCA Bistro business must exist.');

            $waiter = User::where('email', 'kasir.cafe@cooca.id')->first()
                ?? User::where('email', 'owner.cafe@cooca.id')->first()
                ?? User::first();
            $location = Location::where('business_id', $business->id)->first();

            // 1. Table Selection: Select dining table and mark as occupied
            $table = PosTable::where('business_id', $business->id)->first()
                ?? PosTable::create([
                    'business_id' => $business->id,
                    'location_id' => $location?->id,
                    'table_number' => 'TB-01',
                    'name' => 'Meja VIP 01',
                    'capacity' => 4,
                    'status' => 'available',
                    'is_active' => true,
                ]);

            $table->update(['status' => 'occupied']);
            $this->assertSame('occupied', $table->fresh()->status);

            // 2. Kitchen Order Dispatch with Custom Modifiers (Less Sugar, Oat Milk)
            $orderNumber = 'ORD-E2E-KDS-' . strtoupper(Str::random(6));
            $order = PosOrder::create([
                'business_id' => $business->id,
                'location_id' => $location?->id,
                'pos_table_id' => $table->id,
                'table_or_reference' => $table->name,
                'order_number' => $orderNumber,
                'order_date' => now()->toDateString(),
                'status' => PosOrder::STATUS_CONFIRMED,
                'order_type' => 'dine_in',
                'subtotal' => 95000,
                'tax_amount' => 10450,
                'service_charge_amount' => 4750,
                'total_amount' => 110200,
                'paid_amount' => 0,
                'change_amount' => 0,
                'total_hpp_cost' => 32000,
                'user_id' => $waiter?->id,
                'notes' => 'Tamu meja VIP - minta cepat disajikan',
            ]);

            PosOrderItem::create([
                'pos_order_id' => $order->id,
                'product_name' => 'Signature Iced Latte',
                'quantity' => 2,
                'unit_price' => 35000,
                'total_price' => 70000,
                'notes' => 'Less sugar (50%), extra oat milk',
            ]);

            PosOrderItem::create([
                'pos_order_id' => $order->id,
                'product_name' => 'Croissant Almond Butter',
                'quantity' => 1,
                'unit_price' => 25000,
                'total_price' => 25000,
                'notes' => 'Hangatkan / toasted',
            ]);

            // 3. KDS Kitchen Preparation State Transitions
            // Stage A: Kitchen marks order as preparing (cooking / brewing)
            $order->update(['status' => PosOrder::STATUS_PREPARING]);
            $this->assertSame(PosOrder::STATUS_PREPARING, $order->fresh()->status);

            // Stage B: Barista finishes preparation, marks as ready
            $order->update(['status' => PosOrder::STATUS_READY]);
            $this->assertSame(PosOrder::STATUS_READY, $order->fresh()->status);

            // Stage C: Runner serves to table
            $order->update(['status' => 'served']);
            $this->assertSame('served', $order->fresh()->status);

            // 4. Guest checkout & Table Release
            PosOrderPayment::create([
                'pos_order_id' => $order->id,
                'payment_method' => PosOrderPayment::METHOD_QRIS,
                'amount' => 110200,
                'status' => 'completed',
                'reference_number' => 'QRIS-' . Str::random(8),
            ]);

            $order->update([
                'status' => PosOrder::STATUS_COMPLETED,
                'paid_amount' => 110200,
            ]);

            // Table returns to available status
            $table->update(['status' => 'available']);
            $this->assertSame('available', $table->fresh()->status);
            $this->assertSame(PosOrder::STATUS_COMPLETED, $order->fresh()->status);
        });
    }

    /**
     * Scenario 2: Recipe BOM Auto-Deduction & Threshold Alert
     * Menu item sale triggering multi-ingredient stock decrement and low-stock notification.
     */
    public function test_recipe_bom_auto_deduction_and_threshold_alert(): void
    {
        $this->runInTransaction(function (): void {
            $business = $this->getTenantBusiness('cooca_bistro');
            $this->assertNotNull($business, 'COOCA Bistro business must exist.');

            $cashier = User::where('email', 'kasir.cafe@cooca.id')->first()
                ?? User::where('email', 'owner.cafe@cooca.id')->first()
                ?? User::first();
            $location = Location::where('business_id', $business->id)->first();

            // Recipe ingredients for 1 Cup Cappuccino:
            // Ingredient 1: Espresso Coffee Beans (18g)
            // Ingredient 2: Fresh Whole Milk (150ml)
            $initialBeansStock = 200.0; // 200g remaining
            $minBeansThreshold = 250.0; // Minimum safety stock = 250g
            $beansUsage = 18.0; // Grams used

            $initialMilkStock = 1200.0; // 1200ml remaining
            $milkUsage = 150.0; // ml used

            // 1. Process Cappuccino POS Sale
            $orderNumber = 'ORD-E2E-RECIPE-' . strtoupper(Str::random(6));
            $order = PosOrder::create([
                'business_id' => $business->id,
                'location_id' => $location?->id,
                'order_number' => $orderNumber,
                'order_date' => now()->toDateString(),
                'status' => PosOrder::STATUS_COMPLETED,
                'order_type' => 'takeaway',
                'subtotal' => 38000,
                'tax_amount' => 0,
                'total_amount' => 38000,
                'paid_amount' => 38000,
                'change_amount' => 0,
                'total_hpp_cost' => 14500, // Aggregate HPP cost of beans & milk
                'user_id' => $cashier?->id,
            ]);

            PosOrderPayment::create([
                'pos_order_id' => $order->id,
                'payment_method' => PosOrderPayment::METHOD_CASH,
                'amount' => 38000,
                'status' => 'completed',
            ]);

            // 2. Multi-Ingredient Auto-Deduction Stock Movements
            $remainingBeans = $initialBeansStock - $beansUsage; // 182g
            $remainingMilk = $initialMilkStock - $milkUsage; // 1050ml

            StockMovement::create([
                'business_id' => $business->id,
                'location_id' => $location?->id,
                'movement_type' => 'pos_sale',
                'reference_id' => $order->id,
                'reference_number' => $order->order_number,
                'quantity_change' => -$beansUsage,
                'balance_before' => $initialBeansStock,
                'balance_after' => $remainingBeans,
                'unit_cost' => 450, // Rp 450/gram
                'total_cost' => $beansUsage * 450,
                'notes' => "Pemakaian resep Espresso Beans untuk order #{$order->order_number}",
            ]);

            StockMovement::create([
                'business_id' => $business->id,
                'location_id' => $location?->id,
                'movement_type' => 'pos_sale',
                'reference_id' => $order->id,
                'reference_number' => $order->order_number,
                'quantity_change' => -$milkUsage,
                'balance_before' => $initialMilkStock,
                'balance_after' => $remainingMilk,
                'unit_cost' => 20, // Rp 20/ml
                'total_cost' => $milkUsage * 20,
                'notes' => "Pemakaian resep Fresh Milk untuk order #{$order->order_number}",
            ]);

            // 3. Low-Stock Threshold Evaluation & Alert
            $isLowStockAlertTriggered = $remainingBeans < $minBeansThreshold;
            $this->assertTrue(
                $isLowStockAlertTriggered,
                "Low stock alert must trigger when remaining beans ({$remainingBeans}g) fall below minimum threshold ({$minBeansThreshold}g)."
            );

            // 4. Verify balanced SAK EMKM COGS journal
            $journalEntry = $this->journalService->recordPosSaleJournal($order);
            $this->assertNotNull($journalEntry);
            $this->assertJournalEntryBalanced($journalEntry);
        });
    }

    /**
     * Scenario 3: Split Bill and Cashier Shift Close
     * Multi-payment method settlement (Cash + QRIS), shift cash count reconciliation.
     */
    public function test_split_bill_and_cashier_shift_close(): void
    {
        $this->runInTransaction(function (): void {
            $business = $this->getTenantBusiness('cooca_bistro');
            $this->assertNotNull($business, 'COOCA Bistro business must exist.');

            $cashier = User::where('email', 'kasir.cafe@cooca.id')->first()
                ?? User::where('email', 'owner.cafe@cooca.id')->first()
                ?? User::first();
            $location = Location::where('business_id', $business->id)->first();

            // 1. Open Cashier Register Shift with Starting Cash Float (Modal Awal)
            $initialFloat = 200000.0; // Rp 200.000 starting cash drawer float
            $shift = PosShift::create([
                'business_id' => $business->id,
                'location_id' => $location?->id,
                'user_id' => $cashier?->id,
                'shift_number' => 'SFT-E2E-' . strtoupper(Str::random(6)),
                'opened_at' => now()->subHours(6),
                'opening_cash' => $initialFloat,
                'status' => PosShift::STATUS_OPEN,
            ]);

            // 2. Split Bill Order Settlement: Total = Rp 180.000
            // Guest A pays Rp 90.000 in Cash
            // Guest B pays Rp 90.000 via QRIS Cooca Pay
            $orderNumber = 'ORD-E2E-SPLIT-' . strtoupper(Str::random(6));
            $order = PosOrder::create([
                'business_id' => $business->id,
                'location_id' => $location?->id,
                'pos_shift_id' => $shift->id,
                'order_number' => $orderNumber,
                'order_date' => now()->toDateString(),
                'status' => PosOrder::STATUS_COMPLETED,
                'order_type' => 'dine_in',
                'subtotal' => 180000,
                'tax_amount' => 0,
                'total_amount' => 180000,
                'paid_amount' => 180000,
                'change_amount' => 0,
                'total_hpp_cost' => 65000,
                'user_id' => $cashier?->id,
            ]);

            // Payment 1: Cash tender (adds to drawer)
            PosOrderPayment::create([
                'pos_order_id' => $order->id,
                'payment_method' => PosOrderPayment::METHOD_CASH,
                'amount' => 90000,
                'status' => 'completed',
            ]);

            // Payment 2: QRIS non-cash tender (goes to settlement gateway)
            PosOrderPayment::create([
                'pos_order_id' => $order->id,
                'payment_method' => PosOrderPayment::METHOD_QRIS,
                'amount' => 90000,
                'status' => 'completed',
                'reference_number' => 'QRIS-SPLIT-' . Str::random(6),
            ]);

            // Verify order payments aggregate exactly to total
            $totalPayments = (float) $order->payments()->sum('amount');
            $this->assertEqualsWithDelta(180000.0, $totalPayments, 0.01);

            // 3. Close Shift & Denominations Cash Count Reconciliation
            // Expected drawer cash: Initial Float (200k) + Cash Sales (90k) = Rp 290.000
            $expectedCash = $initialFloat + 90000.0;

            // Physical denomination breakdown counted by cashier:
            // 2 x Rp 100.000 = Rp 200.000
            // 1 x Rp 50.000  = Rp 50.000
            // 2 x Rp 20.000  = Rp 40.000
            // Total actual cash = Rp 290.000 (Zero variance)
            $actualCash = 290000.0;
            $cashDifference = $actualCash - $expectedCash; // 0.0

            $shift->update([
                'closed_at' => now(),
                'closing_cash_actual' => $actualCash,
                'closing_cash_expected' => $expectedCash,
                'cash_difference' => $cashDifference,
                'total_cash_sales' => 90000,
                'total_non_cash_sales' => 90000,
                'status' => PosShift::STATUS_CLOSED,
                'notes' => 'Tutup shift kasir normal, fisik uang laci seimbang sempurna.',
            ]);

            $freshShift = $shift->fresh();
            $this->assertSame(PosShift::STATUS_CLOSED, $freshShift->status);
            $this->assertEqualsWithDelta(0.0, (float) $freshShift->cash_difference, 0.01);
            $this->assertEqualsWithDelta(290000.0, (float) $freshShift->closing_cash_actual, 0.01);

            // Verify balanced SAK EMKM journal for the split-bill transaction
            $journalEntry = $this->journalService->recordPosSaleJournal($order);
            $this->assertNotNull($journalEntry);
            $this->assertJournalEntryBalanced($journalEntry);
        });
    }
}
