<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Commerce\PurchaseOrderService;
use App\Domain\Inventory\CostingEngine;
use App\Domain\Inventory\StockService;
use App\Domain\Manufacturing\ProductionService;
use App\Domain\Material\UnitConversionService;
use App\Domain\Pos\PosOrderService;
use App\Domain\Purchasing\GoodsReceiptService;
use App\Models\BomHeader;
use App\Models\BomItem;
use App\Models\Business;
use App\Models\CostModel;
use App\Models\Customer;
use App\Models\GoodsReceipt;
use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\Material;
use App\Models\MaterialPrice;
use App\Models\MaterialUnitConversion;
use App\Models\PeriodicInventoryClosing;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\UnitConversion;
use App\Models\User;
use App\Support\Context;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CostingAndUnitConversionIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $user;
    private Location $location;
    private Supplier $supplier;

    private Unit $unitDus;
    private Unit $unitPcs;
    private Unit $unitLiter;
    private Unit $unitMl;

    private UnitConversionService $conversionService;
    private CostingEngine $costingEngine;
    private StockService $stockService;
    private GoodsReceiptService $receiptService;
    private PurchaseOrderService $poService;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->user = User::create([
            'name' => 'Owner Cafe & Resto',
            'email' => 'owner_cafe@example.com',
            'password' => 'password123',
        ]);

        $this->business = Business::create([
            'name' => 'Cooca Coffee & Eatery',
        ]);

        $this->business->users()->attach($this->user->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->user->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);

        $this->location = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Gudang Utama & Barista Bar',
            'type' => 'warehouse',
            'is_primary' => true,
            'is_active' => true,
        ]);

        $this->supplier = Supplier::create([
            'business_id' => $this->business->id,
            'name' => 'PT Greenfields Indonesia Distribution',
        ]);

        // Units: Dus, Pcs, Liter, ML
        $this->unitDus = Unit::create([
            'business_id' => $this->business->id,
            'code' => 'DUS',
            'name' => 'Dus',
            'symbol' => 'dus',
            'category' => Unit::CATEGORY_VOLUME,
        ]);

        $this->unitPcs = Unit::create([
            'business_id' => $this->business->id,
            'code' => 'PCS',
            'name' => 'Pieces',
            'symbol' => 'pcs',
            'category' => Unit::CATEGORY_VOLUME,
        ]);

        $this->unitLiter = Unit::create([
            'business_id' => $this->business->id,
            'code' => 'L',
            'name' => 'Liter',
            'symbol' => 'L',
            'category' => Unit::CATEGORY_VOLUME,
        ]);

        $this->unitMl = Unit::create([
            'business_id' => $this->business->id,
            'code' => 'ML',
            'name' => 'Mililiter',
            'symbol' => 'ml',
            'category' => Unit::CATEGORY_VOLUME,
        ]);

        // Global unit conversion: 1 Liter = 1000 ML
        UnitConversion::create([
            'business_id' => $this->business->id,
            'from_unit_id' => $this->unitLiter->id,
            'to_unit_id' => $this->unitMl->id,
            'factor' => 1000.0,
        ]);

        $this->conversionService = new UnitConversionService();
        $this->costingEngine = new CostingEngine($this->conversionService);
        $this->stockService = new StockService(costingEngine: $this->costingEngine);
        $this->receiptService = new GoodsReceiptService(stockService: $this->stockService, costingEngine: $this->costingEngine);
        $this->poService = new PurchaseOrderService(unitConversionService: $this->conversionService);
    }

    /**
     * Skenario A: Pembelian bahan baku dengan konversi multi-level
     * (20 dus -> 240 liter -> 240.000 ml) dan pembaruan stok dalam satuan dasar.
     */
    public function test_scenario_a_multi_level_unit_conversion_purchase_and_receipt(): void
    {
        // 1. Setup Material: Greenfields UHT Full Cream Milk dengan base unit = ML
        $milk = Material::create([
            'business_id' => $this->business->id,
            'name' => 'Greenfields UHT Full Cream Milk',
            'code' => 'MAT-MILK-UHT',
            'unit_id' => $this->unitMl->id, // base unit
        ]);

        // Specific Material conversions:
        // 1 Dus = 12 Pcs
        MaterialUnitConversion::create([
            'business_id' => $this->business->id,
            'material_id' => $milk->id,
            'from_unit_id' => $this->unitDus->id,
            'to_unit_id' => $this->unitPcs->id,
            'factor' => 12.0,
            'effective_from' => now()->toDateString(),
        ]);

        // 1 Pcs = 1 Liter
        MaterialUnitConversion::create([
            'business_id' => $this->business->id,
            'material_id' => $milk->id,
            'from_unit_id' => $this->unitPcs->id,
            'to_unit_id' => $this->unitLiter->id,
            'factor' => 1.0,
            'effective_from' => now()->toDateString(),
        ]);

        // Verify Graph BFS resolves Dus -> ML: 1 Dus * 12 Pcs * 1 L * 1000 ML = 12.000 ML
        $factor = $this->conversionService->getConversionFactor(
            $this->unitDus->id,
            $this->unitMl->id,
            $milk->id,
            $this->business->id
        );
        $this->assertEquals(12000.0, $factor);

        // 2. Create Purchase Order for 20 Dus @ Rp150.000
        $po = $this->poService->createPurchaseOrder($this->business, [
            'supplier_id' => $this->supplier->id,
            'po_type' => PurchaseOrder::TYPE_SUPPLIER,
            'order_date' => Carbon::now()->toDateString(),
            'notes' => 'Pembelian susu UHT untuk barista bar',
        ], [
            [
                'material_id' => $milk->id,
                'item_name' => $milk->name,
                'quantity' => 20, // 20 dus
                'unit_id' => $this->unitDus->id,
                'unit_price' => 150000.0, // Rp150.000 / dus
            ],
        ]);

        $item = $po->items->first();
        $this->assertEquals(20.0, (float) $item->quantity);
        $this->assertEquals($this->unitMl->id, $item->base_unit_id);
        $this->assertEquals(12000.0, (float) $item->conversion_factor);
        $this->assertEquals(240000.0, (float) $item->base_quantity); // 20 * 12.000 = 240.000 ml
        $this->assertEquals(12.5, (float) $item->estimated_base_cost); // Rp150.000 / 12.000 = Rp12,5 / ml
        $this->assertEquals(3000000.0, (float) $item->subtotal);

        // Confirm PO before receiving
        $this->poService->confirm($po);

        // 3. Receive Goods (Receipt)
        $receipt = $this->receiptService->receive($po, [
            'location_id' => $this->location->id,
            'receipt_number' => 'RCV-001',
            'receipt_date' => Carbon::now()->toDateString(),
            'items' => [
                [
                    'purchase_order_item_id' => $item->id,
                    'material_id' => $milk->id,
                    'item_name' => $milk->name,
                    'quantity' => 20, // 20 dus
                    'received_unit_id' => $this->unitDus->id,
                    'conversion_factor' => 12000.0,
                    'unit_cost' => 150000.0,
                ],
            ],
        ], $this->user->id);

        // 4. Assert stock was incremented in ML
        $milkStock = InventoryStock::where('business_id', $this->business->id)
            ->where('material_id', $milk->id)
            ->where('location_id', $this->location->id)
            ->first();

        $this->assertNotNull($milkStock);
        $this->assertEquals(240000.0, (float) $milkStock->quantity); // 240.000 ML

        // Assert Material latestPrice updated to Rp12,50 / ML
        $milk->refresh();
        $this->assertEquals(12.5, (float) $milk->effective_cost);

        // Assert CostingEngine returns Rp12,50 when requested in ML, and Rp12.500 when requested in Liter
        $costInMl = $this->costingEngine->getEffectiveUnitCost($milk, $this->location->id, $this->business->id, $this->unitMl->id);
        $this->assertEquals(12.5, $costInMl);

        $costInLiter = $this->costingEngine->getEffectiveUnitCost($milk, $this->location->id, $this->business->id, $this->unitLiter->id);
        $this->assertEquals(12500.0, $costInLiter);
    }

    /**
     * Skenario B: Perhitungan moving average cost dengan beberapa penerimaan bertahap pada harga berbeda.
     * Stok awal: 100 L @ Rp10.000 = Rp1.000.000
     * Pembelian baru: 100 L @ Rp12.000 = Rp1.200.000
     * Hasil New Average: (1.000.000 + 1.200.000) / 200 = Rp11.000 / L
     */
    public function test_scenario_b_moving_average_cost_calculation(): void
    {
        $material = Material::create([
            'business_id' => $this->business->id,
            'name' => 'Sirup Karamel',
            'code' => 'MAT-SYRUP-CRM',
            'unit_id' => $this->unitLiter->id,
        ]);

        // Initial stock: 100 L @ Rp10.000
        $this->stockService->recordMovement(
            businessId: $this->business->id,
            locationId: $this->location->id,
            productId: null,
            movementType: StockMovement::TYPE_INITIAL,
            quantityChange: 100.0,
            unitCost: 10000.0,
            userId: $this->user->id,
            notes: 'Saldo awal persediaan',
            materialId: $material->id
        );

        $currentCost = $this->costingEngine->getEffectiveUnitCost($material, $this->location->id, $this->business->id);
        $this->assertEquals(10000.0, $currentCost);

        // New purchase: 100 L @ Rp12.000
        $po = $this->poService->createPurchaseOrder($this->business, [
            'supplier_id' => $this->supplier->id,
            'po_type' => PurchaseOrder::TYPE_SUPPLIER,
            'order_date' => Carbon::now()->toDateString(),
        ], [
            [
                'material_id' => $material->id,
                'item_name' => $material->name,
                'quantity' => 100.0,
                'unit_id' => $this->unitLiter->id,
                'unit_price' => 12000.0,
            ],
        ]);

        $this->poService->confirm($po);

        $this->receiptService->receive($po, [
            'location_id' => $this->location->id,
            'receipt_number' => 'RCV-SYRUP-01',
            'receipt_date' => Carbon::now()->toDateString(),
            'items' => [
                [
                    'purchase_order_item_id' => $po->items->first()->id,
                    'material_id' => $material->id,
                    'item_name' => $material->name,
                    'quantity' => 100.0,
                    'received_unit_id' => $this->unitLiter->id,
                    'conversion_factor' => 1.0,
                    'unit_cost' => 12000.0,
                ],
            ],
        ], $this->user->id);

        // Assert Stock is now 200 L
        $stock = InventoryStock::where('business_id', $this->business->id)
            ->where('material_id', $material->id)
            ->where('location_id', $this->location->id)
            ->first();
        $this->assertEquals(200.0, (float) $stock->quantity);

        // Assert moving average cost = Rp11.000 / L
        $newCost = $this->costingEngine->getEffectiveUnitCost($material, $this->location->id, $this->business->id);
        $this->assertEquals(11000.0, $newCost);

        // Assert audit trail in stock movements
        $movement = StockMovement::where('material_id', $material->id)
            ->where('movement_type', StockMovement::TYPE_GOODS_RECEIPT)
            ->latest()
            ->first();
        $this->assertNotNull($movement);
        $this->assertEquals(100.0, (float) $movement->balance_before);
        $this->assertEquals(10000.0, (float) $movement->cost_before);
        $this->assertEquals(11000.0, (float) $movement->cost_after);
    }

    /**
     * Skenario C: PO baru dibuat tetapi belum diterima, memastikan stok dan average cost tidak berubah.
     */
    public function test_scenario_c_unreceived_po_does_not_change_stock_or_average_cost(): void
    {
        $material = Material::create([
            'business_id' => $this->business->id,
            'name' => 'Biji Kopi Arabika',
            'code' => 'MAT-COFFEE-ARB',
            'unit_id' => $this->unitLiter->id,
        ]);

        $this->stockService->recordMovement(
            businessId: $this->business->id,
            locationId: $this->location->id,
            productId: null,
            movementType: StockMovement::TYPE_INITIAL,
            quantityChange: 50.0,
            unitCost: 50000.0,
            userId: $this->user->id,
            materialId: $material->id
        );

        // Create PO with higher price Rp70.000
        $po = $this->poService->createPurchaseOrder($this->business, [
            'supplier_id' => $this->supplier->id,
            'po_type' => PurchaseOrder::TYPE_SUPPLIER,
            'order_date' => Carbon::now()->toDateString(),
        ], [
            [
                'material_id' => $material->id,
                'item_name' => $material->name,
                'quantity' => 100.0,
                'unit_id' => $this->unitLiter->id,
                'unit_price' => 70000.0,
            ],
        ]);

        $poItem = $po->items->first();
        $this->assertEquals(70000.0, (float) $poItem->estimated_base_cost);

        // Check stock still 50.0 and cost still 50.000
        $stock = InventoryStock::where('business_id', $this->business->id)
            ->where('material_id', $material->id)
            ->where('location_id', $this->location->id)
            ->first();
        $this->assertEquals(50.0, (float) $stock->quantity);

        $cost = $this->costingEngine->getEffectiveUnitCost($material, $this->location->id, $this->business->id);
        $this->assertEquals(50000.0, $cost);
    }

    /**
     * Skenario D: Penerimaan barang parsial (misal pesan 100 liter, baru diterima 60 liter).
     */
    public function test_scenario_d_partial_goods_receipt(): void
    {
        $material = Material::create([
            'business_id' => $this->business->id,
            'name' => 'Gula Cair Tebu',
            'code' => 'MAT-SUGAR-LIQ',
            'unit_id' => $this->unitLiter->id,
        ]);

        $po = $this->poService->createPurchaseOrder($this->business, [
            'supplier_id' => $this->supplier->id,
            'po_type' => PurchaseOrder::TYPE_SUPPLIER,
            'order_date' => Carbon::now()->toDateString(),
        ], [
            [
                'material_id' => $material->id,
                'item_name' => $material->name,
                'quantity' => 100.0,
                'unit_id' => $this->unitLiter->id,
                'unit_price' => 20000.0,
            ],
        ]);

        $this->poService->confirm($po);

        // Receive only 60 L
        $this->receiptService->receive($po, [
            'location_id' => $this->location->id,
            'receipt_number' => 'RCV-PARTIAL-01',
            'receipt_date' => Carbon::now()->toDateString(),
            'items' => [
                [
                    'purchase_order_item_id' => $po->items->first()->id,
                    'material_id' => $material->id,
                    'item_name' => $material->name,
                    'quantity' => 60.0, // Partial
                    'received_unit_id' => $this->unitLiter->id,
                    'conversion_factor' => 1.0,
                    'unit_cost' => 20000.0,
                ],
            ],
        ], $this->user->id);

        $stock = InventoryStock::where('business_id', $this->business->id)
            ->where('material_id', $material->id)
            ->where('location_id', $this->location->id)
            ->first();

        $this->assertEquals(60.0, (float) $stock->quantity);

        $cost = $this->costingEngine->getEffectiveUnitCost($material, $this->location->id, $this->business->id);
        $this->assertEquals(20000.0, $cost);
    }

    /**
     * Skenario E: Perhitungan periodic average cost dan penutupan periode bulanan.
     */
    public function test_scenario_e_periodic_monthly_average_cost_and_closing(): void
    {
        $material = Material::create([
            'business_id' => $this->business->id,
            'name' => 'Matcha Powder Premium',
            'code' => 'MAT-MATCHA-PREM',
            'unit_id' => $this->unitLiter->id,
        ]);

        // Initial stock 10 L @ Rp100.000
        $this->stockService->recordMovement(
            businessId: $this->business->id,
            locationId: $this->location->id,
            productId: null,
            movementType: StockMovement::TYPE_INITIAL,
            quantityChange: 10.0,
            unitCost: 100000.0,
            userId: $this->user->id,
            materialId: $material->id
        );

        // Purchase in same period: 20 L @ Rp130.000
        $this->stockService->recordMovement(
            businessId: $this->business->id,
            locationId: $this->location->id,
            productId: null,
            movementType: StockMovement::TYPE_GOODS_RECEIPT,
            quantityChange: 20.0,
            unitCost: 130000.0,
            userId: $this->user->id,
            materialId: $material->id
        );

        // Periodic Average Calculation:
        // (10 * 100.000 + 20 * 130.000) / (10 + 20) = (1.000.000 + 2.600.000) / 30 = 3.600.000 / 30 = Rp120.000 / L
        $now = Carbon::now();
        $metrics = $this->costingEngine->calculatePeriodicMonthlyAverage(
            businessId: $this->business->id,
            year: (int) $now->year,
            month: (int) $now->month,
            materialId: $material->id,
            locationId: $this->location->id
        );

        $this->assertEquals(120000.0, $metrics['closing_average_cost']);

        // Perform Period Closing
        $closing = $this->costingEngine->closePeriod(
            businessId: $this->business->id,
            year: (int) $now->year,
            month: (int) $now->month,
            itemType: 'material',
            itemId: $material->id,
            locationId: $this->location->id,
            userId: $this->user->id
        );

        $this->assertInstanceOf(PeriodicInventoryClosing::class, $closing);
        $this->assertTrue($closing->is_locked);
        $this->assertEquals(120000.0, (float) $closing->closing_average_cost);
    }

    /**
     * Skenario F: Penerimaan barang dengan alokasi landed cost (ongkir/asuransi).
     */
    public function test_scenario_f_landed_cost_allocation(): void
    {
        $materialA = Material::create([
            'business_id' => $this->business->id,
            'name' => 'Bahan Baku A',
            'code' => 'MAT-A',
            'unit_id' => $this->unitLiter->id,
        ]);

        $materialB = Material::create([
            'business_id' => $this->business->id,
            'name' => 'Bahan Baku B',
            'code' => 'MAT-B',
            'unit_id' => $this->unitLiter->id,
        ]);

        // PO with two items:
        // Item A: 100 L @ Rp20.000 = Rp2.000.000
        // Item B: 50 L @ Rp20.000 = Rp1.000.000
        // Total purchase = Rp3.000.000
        $po = $this->poService->createPurchaseOrder($this->business, [
            'supplier_id' => $this->supplier->id,
            'po_type' => PurchaseOrder::TYPE_SUPPLIER,
            'order_date' => Carbon::now()->toDateString(),
        ], [
            [
                'material_id' => $materialA->id,
                'item_name' => $materialA->name,
                'quantity' => 100.0,
                'unit_id' => $this->unitLiter->id,
                'unit_price' => 20000.0,
            ],
            [
                'material_id' => $materialB->id,
                'item_name' => $materialB->name,
                'quantity' => 50.0,
                'unit_id' => $this->unitLiter->id,
                'unit_price' => 20000.0,
            ],
        ]);

        $this->poService->confirm($po);

        // Receive with Landed Cost Rp120.000 allocated proportionally by value
        // Item A gets 2/3 * 120.000 = Rp80.000 -> effective unit cost = (2.000.000 + 80.000) / 100 = Rp20.800
        // Item B gets 1/3 * 120.000 = Rp40.000 -> effective unit cost = (1.000.000 + 40.000) / 50 = Rp20.800
        $receipt = $this->receiptService->receive($po, [
            'location_id' => $this->location->id,
            'receipt_number' => 'RCV-LANDED-01',
            'receipt_date' => Carbon::now()->toDateString(),
            'landed_cost_amount' => 120000.0,
            'landed_cost_allocation_method' => 'value',
            'items' => [
                [
                    'purchase_order_item_id' => $po->items[0]->id,
                    'material_id' => $materialA->id,
                    'item_name' => $materialA->name,
                    'quantity' => 100.0,
                    'received_unit_id' => $this->unitLiter->id,
                    'conversion_factor' => 1.0,
                    'unit_cost' => 20000.0,
                ],
                [
                    'purchase_order_item_id' => $po->items[1]->id,
                    'material_id' => $materialB->id,
                    'item_name' => $materialB->name,
                    'quantity' => 50.0,
                    'received_unit_id' => $this->unitLiter->id,
                    'conversion_factor' => 1.0,
                    'unit_cost' => 20000.0,
                ],
            ],
        ], $this->user->id);

        $costA = $this->costingEngine->getEffectiveUnitCost($materialA, $this->location->id, $this->business->id);
        $costB = $this->costingEngine->getEffectiveUnitCost($materialB, $this->location->id, $this->business->id);

        $this->assertEquals(20800.0, $costA);
        $this->assertEquals(20800.0, $costB);
        $this->assertEquals(120000.0, (float) $receipt->landed_cost_amount);
    }

    /**
     * Skenario G: Produksi manufaktur mengonsumsi bahan baku dengan satuan resep (ml/gram)
     * dan menghasilkan produk jadi dengan HPP yang benar.
     */
    public function test_scenario_g_manufacturing_bom_consumption_and_finished_good_costing(): void
    {
        // 1. Raw Material: Milk @ Rp12,50 / ML
        $milk = Material::create([
            'business_id' => $this->business->id,
            'name' => 'Susu UHT Fresh',
            'code' => 'MAT-MILK-FRESH',
            'unit_id' => $this->unitMl->id,
        ]);

        $this->stockService->recordMovement(
            businessId: $this->business->id,
            locationId: $this->location->id,
            productId: null,
            movementType: StockMovement::TYPE_INITIAL,
            quantityChange: 10000.0, // 10.000 ML
            unitCost: 12.5,
            userId: $this->user->id,
            materialId: $milk->id
        );

        // 2. Finished Good: Caffe Latte
        $latteCategory = ProductCategory::create([
            'business_id' => $this->business->id,
            'name' => 'Minuman Kopi',
        ]);

        $latte = Product::create([
            'business_id' => $this->business->id,
            'category_id' => $latteCategory->id,
            'output_unit_id' => $this->unitPcs->id,
            'name' => 'Caffe Latte Cup',
            'code' => 'PRD-LATTE',
            'type' => Product::TYPE_GOODS,
            'base_cost' => 0.0,
            'selling_price' => 25000.0,
            'is_active' => true,
        ]);

        $costModel = CostModel::create([
            'business_id' => $this->business->id,
            'product_id' => $latte->id,
            'name' => 'Model HPP Latte',
            'method' => CostModel::METHOD_RECIPE_BOM,
            'is_active' => true,
        ]);

        $bomHeader = BomHeader::create([
            'cost_model_id' => $costModel->id,
            'name' => 'Resep 1 Cup Latte',
            'type' => BomHeader::TYPE_RECIPE,
            'level' => 1,
        ]);

        // 3. BOM: 1 Cup Caffe Latte requires 200 ML Milk
        BomItem::create([
            'bom_header_id' => $bomHeader->id,
            'material_id' => $milk->id,
            'quantity' => 200.0, // 200 ml
            'unit_id' => $this->unitMl->id,
            'waste_percentage' => 0,
        ]);

        // 4. Run Manufacturing Order
        $prodService = new ProductionService(
            stockService: $this->stockService,
            unitConversionService: $this->conversionService,
            costingEngine: $this->costingEngine
        );

        $order = $prodService->createOrder($this->business, [
            'product_id' => $latte->id,
            'planned_quantity' => 10.0, // Produce 10 cups
            'source_location_id' => $this->location->id,
            'target_location_id' => $this->location->id,
            'planned_start_date' => Carbon::now()->toDateString(),
            'total_labor_cost' => 15000.0, // Rp1.500 / cup labor
            'total_machine_cost' => 5000.0, // Rp500 / cup overhead
        ], $this->user);

        // Issue materials to production floor
        $prodService->issueMaterials($order, [], $this->user);

        // Complete Production Order
        $prodService->completeOrder($order, [
            'actual_quantity' => 10.0,
            'rejected_quantity' => 0.0,
            'update_product_base_cost' => true,
        ], $this->user);

        // Raw material consumed: 10 * 200 ML = 2.000 ML. Balance left: 8.000 ML
        $milkStock = InventoryStock::where('business_id', $this->business->id)
            ->where('material_id', $milk->id)
            ->where('location_id', $this->location->id)
            ->first();
        $this->assertEquals(8000.0, (float) $milkStock->quantity);

        // Finished Good stock: 10 cups produced
        $latteStock = InventoryStock::where('business_id', $this->business->id)
            ->where('product_id', $latte->id)
            ->where('location_id', $this->location->id)
            ->first();
        $this->assertEquals(10.0, (float) $latteStock->quantity);

        // Cost per cup: Material (200 * 12.5 = 2.500) + Labor (15.000/10 = 1.500) + Overhead (5.000/10 = 500) = Rp4.500
        $latte->refresh();
        $this->assertEquals(4500.0, (float) $latte->base_cost);
    }

    /**
     * Skenario H: Penjualan/POS yang mengurangi stok produk jadi,
     * memastikan tidak ada double deduction dan HPP transaksi tercatat akurat.
     */
    public function test_scenario_h_pos_checkout_stock_deduction_and_hpp_snapshot(): void
    {
        $latteCategory = ProductCategory::create([
            'business_id' => $this->business->id,
            'name' => 'Minuman Kopi',
        ]);

        $latte = Product::create([
            'business_id' => $this->business->id,
            'category_id' => $latteCategory->id,
            'output_unit_id' => $this->unitPcs->id,
            'name' => 'Caffe Latte Cup',
            'code' => 'PRD-LATTE-POS',
            'type' => Product::TYPE_GOODS,
            'base_cost' => 4500.0,
            'selling_price' => 25000.0,
            'is_active' => true,
        ]);

        // Initial stock 10 cups
        $this->stockService->recordMovement(
            businessId: $this->business->id,
            locationId: $this->location->id,
            productId: $latte->id,
            movementType: StockMovement::TYPE_INITIAL,
            quantityChange: 10.0,
            unitCost: 4500.0,
            userId: $this->user->id
        );

        $posService = new PosOrderService(
            stockService: $this->stockService,
            costingEngine: $this->costingEngine
        );

        $posOrder = $posService->checkout(
            $this->business,
            $this->user,
            [
                [
                    'product_id' => $latte->id,
                    'unit_price' => 25000.0,
                    'quantity' => 2.0,
                ],
            ],
            [
                [
                    'payment_method' => 'cash',
                    'amount' => 50000.0,
                ],
            ],
            [
                'location_id' => $this->location->id,
            ]
        );

        // Assert Order Item HPP snapshot recorded
        $orderItem = $posOrder->items->first();
        $this->assertEquals(4500.0, (float) $orderItem->unit_cost_hpp);
        $this->assertEquals(9000.0, (float) $orderItem->total_hpp);

        // Assert stock reduced exactly by 2 (from 10 to 8)
        $stock = InventoryStock::where('business_id', $this->business->id)
            ->where('product_id', $latte->id)
            ->where('location_id', $this->location->id)
            ->first();
        $this->assertEquals(8.0, (float) $stock->quantity);
    }

    /**
     * Skenario I: Retur penjualan mengembalikan stok pada HPP snapshot asli.
     */
    public function test_scenario_i_sales_return_stock_restoration_at_snapshot_hpp(): void
    {
        $product = Product::create([
            'business_id' => $this->business->id,
            'output_unit_id' => $this->unitPcs->id,
            'name' => 'Tumbler Cooca',
            'code' => 'PRD-TMBLR',
            'type' => Product::TYPE_GOODS,
            'base_cost' => 50000.0,
            'selling_price' => 100000.0,
            'is_active' => true,
        ]);

        // Initial stock 5 pcs
        $this->stockService->recordMovement(
            businessId: $this->business->id,
            locationId: $this->location->id,
            productId: $product->id,
            movementType: StockMovement::TYPE_INITIAL,
            quantityChange: 5.0,
            unitCost: 50000.0,
            userId: $this->user->id
        );

        // Simulate sales return restoration
        $res = $this->stockService->restoreForSalesReturn(
            businessId: $this->business->id,
            locationId: $this->location->id,
            product: $product,
            quantity: 1.0,
            unitCost: 50000.0,
            returnId: (string) Str::uuid(),
            returnNumber: 'SR-001',
            userId: $this->user->id
        );

        $this->assertNotEmpty($res);
        $movement = $res[0];
        $this->assertEquals(StockMovement::TYPE_POS_REFUND, $movement->movement_type);
        $this->assertEquals(50000.0, (float) $movement->unit_cost);

        $stock = InventoryStock::where('business_id', $this->business->id)
            ->where('product_id', $product->id)
            ->where('location_id', $this->location->id)
            ->first();
        $this->assertEquals(6.0, (float) $stock->quantity);
    }

    /**
     * Skenario J: Multi-warehouse dan multi-business untuk memastikan data tidak bocor antar tenant.
     */
    public function test_scenario_j_multi_tenant_and_multi_warehouse_isolation(): void
    {
        // Business B (Tenant lain)
        $businessB = Business::create(['name' => 'Competitor Coffee Shop']);
        $locationB = Location::create([
            'business_id' => $businessB->id,
            'name' => 'Gudang Competitor',
            'type' => 'warehouse',
            'is_primary' => true,
            'is_active' => true,
        ]);

        $materialB = Material::create([
            'business_id' => $businessB->id,
            'name' => 'Greenfields Milk B',
            'code' => 'MAT-MILK-B',
            'unit_id' => $this->unitMl->id,
        ]);

        MaterialPrice::create([
            'business_id' => $businessB->id,
            'material_id' => $materialB->id,
            'purchase_price' => 18.0,
            'purchase_unit_id' => $this->unitMl->id,
            'effective_date' => now()->toDateString(),
        ]);

        $this->stockService->recordMovement(
            businessId: $businessB->id,
            locationId: $locationB->id,
            productId: null,
            movementType: StockMovement::TYPE_INITIAL,
            quantityChange: 5000.0,
            unitCost: 18.0,
            userId: $this->user->id,
            materialId: $materialB->id
        );

        // Check Business B cost is 18.0
        $costB = $this->costingEngine->getEffectiveUnitCost($materialB, $locationB->id, $businessB->id);
        $this->assertEquals(18.0, $costB);

        // Check Business A cannot see Business B stock
        $stockA = InventoryStock::where('business_id', $this->business->id)
            ->where('material_id', $materialB->id)
            ->first();
        $this->assertNull($stockA);

        // Multi-warehouse inside Business A
        $location2 = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Cabang Mall Senayan',
            'type' => 'store',
            'is_primary' => false,
            'is_active' => true,
        ]);

        $materialA = Material::create([
            'business_id' => $this->business->id,
            'name' => 'Biji Kopi House Blend',
            'code' => 'MAT-HOUSE-BLEND',
            'unit_id' => $this->unitLiter->id,
        ]);

        // Warehouse 1 has 10 L
        $this->stockService->recordMovement(
            businessId: $this->business->id,
            locationId: $this->location->id,
            productId: null,
            movementType: StockMovement::TYPE_INITIAL,
            quantityChange: 10.0,
            unitCost: 80000.0,
            userId: $this->user->id,
            materialId: $materialA->id
        );

        // Warehouse 2 has 25 L
        $this->stockService->recordMovement(
            businessId: $this->business->id,
            locationId: $location2->id,
            productId: null,
            movementType: StockMovement::TYPE_INITIAL,
            quantityChange: 25.0,
            unitCost: 85000.0,
            userId: $this->user->id,
            materialId: $materialA->id
        );

        $stockLoc1 = InventoryStock::where('location_id', $this->location->id)->where('material_id', $materialA->id)->first();
        $stockLoc2 = InventoryStock::where('location_id', $location2->id)->where('material_id', $materialA->id)->first();

        $this->assertEquals(10.0, (float) $stockLoc1->quantity);
        $this->assertEquals(25.0, (float) $stockLoc2->quantity);

        $costLoc1 = $this->costingEngine->getEffectiveUnitCost($materialA, $this->location->id, $this->business->id);
        $costLoc2 = $this->costingEngine->getEffectiveUnitCost($materialA, $location2->id, $this->business->id);

        $this->assertEquals(80000.0, $costLoc1);
        $this->assertEquals(85000.0, $costLoc2);
    }
}
