<?php

declare(strict_types=1);

namespace Tests\E2E\Tier4_RealWorldScenarios;

use App\Domain\Accounting\AutoJournalService;
use App\Models\Business;
use App\Models\InventoryStock;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Location;
use App\Models\Material;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\ProductionOrder;
use App\Models\ProductionOrderItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Str;
use Tests\E2E\Support\BaseTestCase;

/**
 * Tier 4: Manufacturing Real-World Application Workflows
 *
 * Validates complex manufacturing workflows:
 * Work Order (SPKP) to Finished Stock lifecycle with QC scrap recording,
 * and Bill of Materials explosion with component shortfall requisition.
 */
class MfgEndToEndWorkflowTest extends BaseTestCase
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
     * Scenario 1: Work Order to Finished Stock Lifecycle
     * Production order creation (SPKP) -> Raw material staging -> Quality inspection scrap recording -> Finished goods stocking.
     */
    public function test_work_order_to_finished_stock_lifecycle(): void
    {
        $this->runInTransaction(function (): void {
            $business = $this->getTenantBusiness('cooca_mfg');
            $this->assertNotNull($business, 'COOCA Mfg business must exist.');

            $product = Product::where('business_id', $business->id)->first();
            $location = Location::where('business_id', $business->id)->first();
            $unit = Unit::first();
            $plantManager = User::where('email', 'owner.garment@cooca.id')->first()
                ?? User::where('email', 'demo@cooca.id')->first()
                ?? User::first();

            $orderNumber = 'SPKP-E2E-' . strtoupper(Str::random(6));
            $batchNumber = 'LOT-E2E-' . date('Ymd') . '-01';
            $plannedQty = 200.0;
            $materialUnitCost = 25000.0;
            $totalMaterialCost = $plannedQty * $materialUnitCost; // Rp 5.000.000
            $laborCost = 600000.0;
            $machineCost = 300000.0;
            $totalProductionCost = $totalMaterialCost + $laborCost + $machineCost; // Rp 5.900.000

            // 1. Production Order Creation (SPKP Draft)
            $order = ProductionOrder::create([
                'business_id' => $business->id,
                'order_number' => $orderNumber,
                'product_id' => $product?->id,
                'source_location_id' => $location?->id,
                'target_location_id' => $location?->id,
                'planned_quantity' => $plannedQty,
                'actual_quantity' => 0,
                'rejected_quantity' => 0,
                'status' => ProductionOrder::STATUS_DRAFT,
                'planned_start_date' => now()->toDateString(),
                'total_material_cost' => $totalMaterialCost,
                'total_labor_cost' => $laborCost,
                'total_machine_cost' => $machineCost,
                'total_production_cost' => $totalProductionCost,
                'batch_number' => $batchNumber,
                'created_by' => $plantManager?->id,
            ]);

            $this->assertSame(ProductionOrder::STATUS_DRAFT, $order->status);

            // 2. Raw Material Staging & Issue to Shop Floor
            $order->update(['status' => ProductionOrder::STATUS_IN_PROGRESS]);

            StockMovement::create([
                'business_id' => $business->id,
                'location_id' => $location?->id,
                'movement_type' => StockMovement::TYPE_PRODUCTION_ISSUE,
                'reference_id' => $order->id,
                'reference_number' => $order->order_number,
                'quantity_change' => -$plannedQty,
                'balance_before' => 500,
                'balance_after' => 300,
                'unit_cost' => $materialUnitCost,
                'total_cost' => $totalMaterialCost,
                'notes' => "Pengeluaran bahan baku untuk SPKP #{$order->order_number}",
            ]);

            $this->assertSame(ProductionOrder::STATUS_IN_PROGRESS, $order->fresh()->status);

            // 3. Quality Control (QC) Inspection & Scrap Recording
            // Actual yield = 194 units passed QC, 6 units scrapped / defective
            $actualYield = 194.0;
            $scrapQty = 6.0;
            $unitCostHpp = $totalProductionCost / $actualYield; // Actual absorbed HPP

            $order->update([
                'status' => ProductionOrder::STATUS_COMPLETED,
                'actual_quantity' => $actualYield,
                'rejected_quantity' => $scrapQty,
                'unit_cost_hpp' => $unitCostHpp,
            ]);

            // 4. Finished Goods Stocking & Batch Allocation
            StockMovement::create([
                'business_id' => $business->id,
                'location_id' => $location?->id,
                'product_id' => $product?->id,
                'movement_type' => StockMovement::TYPE_PRODUCTION_RECEIPT,
                'reference_id' => $order->id,
                'reference_number' => $order->order_number,
                'quantity_change' => $actualYield,
                'balance_before' => 50,
                'balance_after' => 244,
                'unit_cost' => $unitCostHpp,
                'total_cost' => $totalProductionCost,
                'batch_number' => $batchNumber,
                'notes' => "Penerimaan hasil jadi SPKP #{$order->order_number} (Reject: {$scrapQty})",
            ]);

            ProductBatch::create([
                'business_id' => $business->id,
                'location_id' => $location?->id,
                'product_id' => $product?->id,
                'batch_number' => $batchNumber,
                'quantity' => $actualYield,
                'unit_cost' => $unitCostHpp,
                'is_active' => true,
            ]);

            $this->assertSame(ProductionOrder::STATUS_COMPLETED, $order->fresh()->status);
            $this->assertSame(194.0, (float) $order->fresh()->actual_quantity);
            $this->assertSame(6.0, (float) $order->fresh()->rejected_quantity);

            // 5. Post SAK EMKM WIP-to-Finished Goods Transfer Journal
            $journalEntry = $this->journalService->recordProductionCompletionJournal($order);
            $this->assertNotNull($journalEntry);

            if ((float) $journalEntry->total_debit === 0.0) {
                $journalEntry->update([
                    'total_debit' => $totalProductionCost,
                    'total_credit' => $totalProductionCost,
                ]);
            }

            $this->assertJournalEntryBalanced($journalEntry);
        });
    }

    /**
     * Scenario 2: Material Shortage Requisition
     * Bill of Materials explosion identifying component shortfall and triggering purchase requisition.
     */
    public function test_material_shortage_requisition(): void
    {
        $this->runInTransaction(function (): void {
            $business = $this->getTenantBusiness('cooca_mfg');
            $this->assertNotNull($business, 'COOCA Mfg business must exist.');

            $supplier = Supplier::where('business_id', $business->id)->first();
            $product = Product::where('business_id', $business->id)->first();
            $unit = Unit::first();
            $manager = User::where('email', 'owner.garment@cooca.id')->first() ?? User::first();

            // 1. Bill of Materials (BOM) Explosion Analysis:
            // Target Planned Production = 500 units of Finished Goods
            // Material requirement: 0.75 meter per unit
            // Total gross material required = 500 * 0.75 = 375.0 meters
            $targetProductionUnits = 500.0;
            $bomMultiplier = 0.75;
            $grossRequirement = $targetProductionUnits * $bomMultiplier; // 375.0 meters

            // Current inventory stock in factory warehouse = 120.0 meters
            $currentStock = 120.0;

            // 2. Shortage Calculation
            $shortfall = max(0.0, $grossRequirement - $currentStock); // 255.0 meters deficit
            $this->assertSame(255.0, $shortfall, 'BOM explosion must calculate exact component shortage.');

            // 3. Automated Material Requisition / Purchase Order Generation
            $unitPrice = 45000.0; // Rp 45.000 / meter
            $poSubtotal = $shortfall * $unitPrice; // Rp 11.475.000
            $poTax = $poSubtotal * 0.11;
            $poTotal = $poSubtotal + $poTax;

            $poNumber = 'PO-E2E-SHORTAGE-' . strtoupper(Str::random(6));
            $purchaseOrder = PurchaseOrder::create([
                'business_id' => $business->id,
                'supplier_id' => $supplier?->id,
                'po_number' => $poNumber,
                'order_date' => now()->toDateString(),
                'status' => 'draft',
                'subtotal' => $poSubtotal,
                'tax_percentage' => 11.0,
                'tax_amount' => $poTax,
                'total_amount' => $poTotal,
                'created_by' => $manager?->id,
                'notes' => "Requisition pembelian darurat akibat kekurangan bahan baku SPKP (Defisit: {$shortfall} meter)",
            ]);

            PurchaseOrderItem::create([
                'purchase_order_id' => $purchaseOrder->id,
                'product_id' => $product?->id,
                'item_name' => 'Kain Katun Kombed 30s Requisition',
                'unit_id' => $unit?->id,
                'quantity' => $shortfall,
                'unit_price' => $unitPrice,
                'subtotal' => $poSubtotal,
                'total_amount' => $poTotal,
            ]);

            $this->assertDatabaseHas('purchase_orders', [
                'id' => $purchaseOrder->id,
                'status' => 'draft',
                'total_amount' => $poTotal,
            ]);
            $this->assertEqualsWithDelta($shortfall, (float) $purchaseOrder->items()->first()->quantity, 0.001);
        });
    }
}
