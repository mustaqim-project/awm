<?php

declare(strict_types=1);

namespace Tests\Feature\Manufacturing;

use App\Domain\Inventory\StockService;
use App\Domain\Manufacturing\ProductionService;
use App\Domain\Pos\PosOrderService;
use App\Models\BomHeader;
use App\Models\BomItem;
use App\Models\Business;
use App\Models\CostModel;
use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\Material;
use App\Models\MaterialPrice;
use App\Models\Product;
use App\Models\ProductionOrder;
use App\Models\StockMovement;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class ManufacturingAndHybridInventoryTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $user;
    private Location $rawWarehouse;
    private Location $fgWarehouse;
    private Unit $kgUnit;
    private Unit $literUnit;
    private Unit $pcsUnit;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->business = Business::create([
            'id' => (string) Str::uuid(),
            'name' => 'Pabrik Roti Makmur Jaya',
            'slug' => 'pabrik-roti-makmur',
            'industry_category' => 'manufacturing',
            'template_code' => 'mfg_craft',
            'currency' => 'IDR',
            'allow_negative_stock' => false,
            'is_active' => true,
        ]);

        $this->user = User::create([
            'id' => (string) Str::uuid(),
            'name' => 'Budi Pabrik Manager',
            'email' => 'budi.factory@cooca.test',
            'password' => bcrypt('secret123'),
        ]);

        $this->rawWarehouse = Location::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'name' => 'Gudang Bahan Baku (RM)',
            'type' => 'warehouse',
            'is_active' => true,
        ]);

        $this->fgWarehouse = Location::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'name' => 'Gudang Barang Jadi (FG)',
            'type' => 'warehouse',
            'is_active' => true,
        ]);

        $this->kgUnit = Unit::create(['id' => (string) Str::uuid(), 'code' => 'kg', 'name' => 'Kilogram', 'category' => 'weight']);
        $this->literUnit = Unit::create(['id' => (string) Str::uuid(), 'code' => 'liter', 'name' => 'Liter', 'category' => 'volume']);
        $this->pcsUnit = Unit::create(['id' => (string) Str::uuid(), 'code' => 'pcs', 'name' => 'Pcs', 'category' => 'quantity']);

        Context::setBusiness($this->business);
    }

    public function test_make_to_stock_manufacturing_production_order_lifecycle(): void
    {
        $stockService = app(StockService::class);
        $productionService = app(ProductionService::class);
        $posService = app(PosOrderService::class);

        // 1. Setup Raw Materials: Tepung (100kg), Gula (50kg), Susu (30L)
        $tepung = Material::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'name' => 'Tepung Terigu Cakra',
            'unit_id' => $this->kgUnit->id,
            'code' => 'RM-TPG',
            'price' => 12000,
        ]);
        MaterialPrice::create([
            'business_id' => $this->business->id,
            'material_id' => $tepung->id,
            'purchase_unit_id' => $this->kgUnit->id,
            'purchase_price' => 12000,
            'effective_date' => now()->toDateString(),
        ]);

        $gula = Material::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'name' => 'Gula Pasir Kristal',
            'unit_id' => $this->kgUnit->id,
            'code' => 'RM-GLA',
            'price' => 16000,
        ]);
        MaterialPrice::create([
            'business_id' => $this->business->id,
            'material_id' => $gula->id,
            'purchase_unit_id' => $this->kgUnit->id,
            'purchase_price' => 16000,
            'effective_date' => now()->toDateString(),
        ]);

        $susu = Material::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'name' => 'Susu Segar UHT',
            'unit_id' => $this->literUnit->id,
            'code' => 'RM-SSU',
            'price' => 20000,
        ]);
        MaterialPrice::create([
            'business_id' => $this->business->id,
            'material_id' => $susu->id,
            'purchase_unit_id' => $this->literUnit->id,
            'purchase_price' => 20000,
            'effective_date' => now()->toDateString(),
        ]);

        // Stock initial materials in Raw Warehouse: Tepung 200kg, Gula 100kg, Susu 60L
        $stockService->recordMovement($this->business->id, $this->rawWarehouse->id, null, StockMovement::TYPE_INITIAL, 200, 12000, materialId: $tepung->id);
        $stockService->recordMovement($this->business->id, $this->rawWarehouse->id, null, StockMovement::TYPE_INITIAL, 100, 16000, materialId: $gula->id);
        $stockService->recordMovement($this->business->id, $this->rawWarehouse->id, null, StockMovement::TYPE_INITIAL, 60, 20000, materialId: $susu->id);

        // 2. Setup Finished Good Product & BOM: 1 Batch (500 unit Roti) needs 100kg Tepung, 50kg Gula, 30L Susu
        $roti = Product::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'name' => 'Roti Manis Pabrik',
            'code' => 'FG-ROTI-01',
            'output_unit_id' => $this->pcsUnit->id,
            'type' => Product::TYPE_GOODS,
            'selling_price' => 15000,
            'base_cost' => 0,
            'is_active' => true,
        ]);

        $costModel = CostModel::create([
            'business_id' => $this->business->id,
            'product_id' => $roti->id,
            'name' => 'Model HPP Roti 500 Pcs',
            'method' => CostModel::METHOD_RECIPE_BOM,
            'is_active' => true,
        ]);

        $bomHeader = BomHeader::create([
            'cost_model_id' => $costModel->id,
            'name' => 'Resep Batch 500 Pcs Roti',
            'type' => BomHeader::TYPE_RECIPE,
            'level' => 1,
        ]);

        // Per unit requirement: 100kg/500 = 0.2kg Tepung, 50kg/500 = 0.1kg Gula, 30L/500 = 0.06L Susu
        BomItem::create(['bom_header_id' => $bomHeader->id, 'material_id' => $tepung->id, 'quantity' => 0.2, 'unit_id' => $this->kgUnit->id, 'waste_percentage' => 0]);
        BomItem::create(['bom_header_id' => $bomHeader->id, 'material_id' => $gula->id, 'quantity' => 0.1, 'unit_id' => $this->kgUnit->id, 'waste_percentage' => 0]);
        BomItem::create(['bom_header_id' => $bomHeader->id, 'material_id' => $susu->id, 'quantity' => 0.06, 'unit_id' => $this->literUnit->id, 'waste_percentage' => 0]);

        // 3. Create Production Order for 500 units
        $order = $productionService->createOrder($this->business, [
            'product_id' => $roti->id,
            'planned_quantity' => 500,
            'source_location_id' => $this->rawWarehouse->id,
            'target_location_id' => $this->fgWarehouse->id,
            'planned_start_date' => now()->toDateString(),
            'total_labor_cost' => 500000, // Upah buruh 500rb
            'total_machine_cost' => 200000, // Jam mesin 200rb
        ], $this->user);

        $this->assertEquals(ProductionOrder::STATUS_DRAFT, $order->status);
        $this->assertCount(3, $order->items);

        // 4. Issue Materials to Factory Floor (WIP)
        $productionService->issueMaterials($order, [], $this->user);
        $order->refresh();

        $this->assertEquals(ProductionOrder::STATUS_IN_PROGRESS, $order->status);

        // Verify Raw Materials stock reduced:
        // Tepung: 200 - 100 = 100 kg
        // Gula: 100 - 50 = 50 kg
        // Susu: 60 - 30 = 30 L
        $stockTepung = (float) InventoryStock::where('business_id', $this->business->id)->where('location_id', $this->rawWarehouse->id)->where('material_id', $tepung->id)->value('quantity');
        $stockGula = (float) InventoryStock::where('business_id', $this->business->id)->where('location_id', $this->rawWarehouse->id)->where('material_id', $gula->id)->value('quantity');
        $stockSusu = (float) InventoryStock::where('business_id', $this->business->id)->where('location_id', $this->rawWarehouse->id)->where('material_id', $susu->id)->value('quantity');

        $this->assertEquals(100.0, $stockTepung);
        $this->assertEquals(50.0, $stockGula);
        $this->assertEquals(30.0, $stockSusu);

        // 5. Complete Production: 500 units Good output, 10 units Reject
        // Material Cost: 100*12k + 50*16k + 30*20k = 1.2jt + 800rb + 600rb = 2.6jt
        // Labor: 500rb, Machine: 200rb -> Total: 3.3jt
        // Unit HPP = 3.3jt / 500 = 6.600 / unit
        $productionService->completeOrder($order, [
            'actual_quantity' => 500,
            'rejected_quantity' => 10,
            'batch_number' => 'LOT-ROTI-202611-A',
            'update_product_base_cost' => true,
        ], $this->user);

        $order->refresh();
        $this->assertEquals(ProductionOrder::STATUS_COMPLETED, $order->status);
        $this->assertEquals(6600.0, (float) $order->unit_cost_hpp);

        // Verify Finished Goods stock in FG Warehouse is +500 units:
        $stockRoti = (float) InventoryStock::where('business_id', $this->business->id)->where('location_id', $this->fgWarehouse->id)->where('product_id', $roti->id)->value('quantity');
        $this->assertEquals(500.0, $stockRoti);

        // Verify product base_cost synchronized to manufacturing HPP:
        $roti->refresh();
        $this->assertEquals(6600.0, (float) $roti->base_cost);

        // 6. Sell 50 units in POS terminal from FG Warehouse
        $stockService->deductForPosSale(
            businessId: $this->business->id,
            locationId: $this->fgWarehouse->id,
            product: $roti,
            quantity: 50,
            unitCost: (float) $roti->base_cost,
            orderId: (string) Str::uuid(),
            orderNumber: 'POS-TEST-001',
            userId: $this->user->id
        );

        // 7. Critical Manufacturing Assertions:
        // Finished goods stock must decrease from 500 to 450:
        $stockRotiAfterSale = (float) InventoryStock::where('business_id', $this->business->id)->where('location_id', $this->fgWarehouse->id)->where('product_id', $roti->id)->value('quantity');
        $this->assertEquals(450.0, $stockRotiAfterSale);

        // RAW MATERIALS MUST NOT BE DEDUCTED AGAIN!
        $stockTepungAfterSale = (float) InventoryStock::where('business_id', $this->business->id)->where('location_id', $this->rawWarehouse->id)->where('material_id', $tepung->id)->value('quantity');
        $stockGulaAfterSale = (float) InventoryStock::where('business_id', $this->business->id)->where('location_id', $this->rawWarehouse->id)->where('material_id', $gula->id)->value('quantity');
        $stockSusuAfterSale = (float) InventoryStock::where('business_id', $this->business->id)->where('location_id', $this->rawWarehouse->id)->where('material_id', $susu->id)->value('quantity');

        $this->assertEquals(100.0, $stockTepungAfterSale, 'Tepung must remain 100kg and not be deducted again.');
        $this->assertEquals(50.0, $stockGulaAfterSale, 'Gula must remain 50kg and not be deducted again.');
        $this->assertEquals(30.0, $stockSusuAfterSale, 'Susu must remain 30L and not be deducted again.');
    }

    public function test_split_deduction_prevents_ghost_stock_and_over_deduction(): void
    {
        $stockService = app(StockService::class);

        // Material Telur (1 Pcs Cake needs 2 Eggs)
        $telur = Material::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'name' => 'Telur Ayam',
            'unit_id' => $this->pcsUnit->id,
            'code' => 'RM-TLR',
            'price' => 2000,
        ]);

        // Initial Telur stock = 100 pcs
        $stockService->recordMovement($this->business->id, $this->fgWarehouse->id, null, StockMovement::TYPE_INITIAL, 100, 2000, materialId: $telur->id);

        // Cake Product with Recipe
        $cake = Product::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'name' => 'Bolu Cake',
            'code' => 'FG-CAKE',
            'output_unit_id' => $this->pcsUnit->id,
            'type' => Product::TYPE_GOODS,
            'selling_price' => 50000,
            'base_cost' => 20000,
            'is_active' => true,
        ]);

        $costModel = CostModel::create([
            'business_id' => $this->business->id,
            'product_id' => $cake->id,
            'name' => 'Model HPP Cake',
            'method' => CostModel::METHOD_RECIPE_BOM,
            'is_active' => true,
        ]);

        $bomHeader = BomHeader::create([
            'cost_model_id' => $costModel->id,
            'name' => 'Resep 1 Cake',
            'type' => BomHeader::TYPE_RECIPE,
            'output_quantity' => 1,
            'level' => 1,
        ]);
        BomItem::create(['bom_header_id' => $bomHeader->id, 'material_id' => $telur->id, 'quantity' => 2, 'unit_id' => $this->pcsUnit->id, 'waste_percentage' => 0]);

        // Place 10 units of physical Finished Goods Cake in warehouse
        $stockService->recordMovement($this->business->id, $this->fgWarehouse->id, $cake->id, StockMovement::TYPE_INITIAL, 10, 20000);

        // Sell 15 units of Cake (10 units exist as physical finished stock, 5 units must be made on-demand)
        $stockService->deductForProductSale(
            businessId: $this->business->id,
            locationId: $this->fgWarehouse->id,
            product: $cake,
            productQuantity: 15,
            unitCost: 20000,
            orderId: (string) Str::uuid(),
            orderNumber: 'ORD-SPLIT-01',
            userId: $this->user->id
        );

        // 1. Finished goods Cake stock must be 0 (10 units consumed, no ghost stock left):
        $cakeStock = (float) InventoryStock::where('business_id', $this->business->id)->where('location_id', $this->fgWarehouse->id)->where('product_id', $cake->id)->value('quantity');
        $this->assertEquals(0.0, $cakeStock);

        // 2. Eggs consumed should ONLY be for the remaining 5 units (5 * 2 = 10 eggs):
        // Telur initial = 100 - 10 = 90 pcs (NOT 100 - 30 = 70 pcs!)
        $telurStock = (float) InventoryStock::where('business_id', $this->business->id)->where('location_id', $this->fgWarehouse->id)->where('material_id', $telur->id)->value('quantity');
        $this->assertEquals(90.0, $telurStock, 'Eggs consumed must only be for the 5 deficit units (10 eggs), preserving 90 eggs in stock.');
    }
}
