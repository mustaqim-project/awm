<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Mcp\Tools\MasterDataManageUnitConversionTool;
use App\Domain\Mcp\Tools\McpToolRegistry;
use App\Domain\Mcp\Tools\PurchasingManageOrderTool;
use App\Domain\Mcp\Tools\WarehouseManageLocationTool;
use App\Models\Business;
use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\Material;
use App\Models\MaterialCategory;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\UnitConversion;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class McpWarehouseAndUnitConversionTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $user;
    private Location $outlet;
    private Supplier $supplier;
    private Unit $unitPcs;
    private Unit $unitDus;
    private Material $material;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->user = User::create([
            'name' => 'Owner MCP User',
            'email' => 'mcp_owner@example.com',
            'password' => 'secret123',
        ]);

        $this->business = Business::create([
            'name' => 'Bisnis Retail MCP',
        ]);
        $this->business->users()->attach($this->user->id, ['id' => Str::uuid(), 'role' => 'owner', 'is_active' => true]);
        $this->user->update(['active_business_id' => $this->business->id]);

        $this->actingAs($this->user);
        session(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);

        $this->outlet = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Outlet Sudirman',
            'slug' => 'outlet-sudirman',
            'type' => 'outlet',
            'code' => 'OUT-SDR',
            'is_primary' => true,
            'is_active' => true,
        ]);

        $this->supplier = Supplier::create([
            'business_id' => $this->business->id,
            'name' => 'PT Pemasok Bahan Utama',
        ]);

        $this->unitPcs = Unit::create([
            'business_id' => $this->business->id,
            'code' => 'PCS',
            'name' => 'Pieces',
            'category' => Unit::CATEGORY_QUANTITY,
        ]);

        $this->unitDus = Unit::create([
            'business_id' => $this->business->id,
            'code' => 'DUS',
            'name' => 'Dus Karton',
            'category' => Unit::CATEGORY_QUANTITY,
        ]);

        $category = MaterialCategory::create([
            'business_id' => $this->business->id,
            'name' => 'Bahan Baku',
            'slug' => 'bahan-baku',
        ]);

        $this->material = Material::create([
            'business_id' => $this->business->id,
            'category_id' => $category->id,
            'unit_id' => $this->unitPcs->id, // Base unit is PCS
            'code' => 'MAT-INDOMIE-01',
            'name' => 'Indomie Goreng Satuan',
            'is_active' => true,
        ]);
    }

    public function test_mcp_registry_contains_new_tools(): void
    {
        $registry = new McpToolRegistry();

        $this->assertTrue($registry->hasTool('warehouse_manage_location'));
        $this->assertTrue($registry->hasTool('master_data_manage_unit_conversion'));
        $this->assertInstanceOf(WarehouseManageLocationTool::class, $registry->getTool('warehouse_manage_location'));
        $this->assertInstanceOf(MasterDataManageUnitConversionTool::class, $registry->getTool('master_data_manage_unit_conversion'));
    }

    public function test_mcp_can_create_sub_warehouse_within_single_outlet_location(): void
    {
        $tool = new WarehouseManageLocationTool();

        // 1. Create Sub-Warehouse with parent_id
        $result = $tool->execute($this->business, $this->user, [
            'action' => 'create',
            'name' => 'Gudang Belakang - Sudirman',
            'type' => 'warehouse',
            'parent_id' => $this->outlet->id,
            'code' => 'WH-SDR-BELAKANG',
            'address' => 'Jl. Jend Sudirman No 45 (Lantai 2)',
        ]);

        $this->assertTrue($result['success']);
        $this->assertTrue($result['location']['is_sub_warehouse']);
        $this->assertEquals($this->outlet->id, $result['location']['parent_id']);
        $this->assertEquals('Outlet Sudirman', $result['location']['parent_name']);

        // 2. List locations and verify hierarchy
        $listResult = $tool->execute($this->business, $this->user, [
            'action' => 'list',
        ]);

        $this->assertTrue($listResult['success']);
        $this->assertCount(2, $listResult['locations']);

        $outletRow = collect($listResult['locations'])->firstWhere('id', $this->outlet->id);
        $this->assertNotNull($outletRow);
        $this->assertEquals(1, $outletRow['child_warehouses_count']);
        $this->assertEquals('Gudang Belakang - Sudirman', $outletRow['child_warehouses'][0]['name']);
    }

    public function test_mcp_can_create_unit_conversion_and_convert_live(): void
    {
        $tool = new MasterDataManageUnitConversionTool();

        // 1. Create global unit conversion: 1 DUS = 30 PCS
        $convResult = $tool->execute($this->business, $this->user, [
            'action' => 'create_conversion',
            'from_unit' => 'DUS',
            'to_unit' => 'PCS',
            'factor' => 30,
        ]);

        $this->assertTrue($convResult['success']);
        $this->assertEquals('1 DUS = 30 PCS', $convResult['conversion']['formula']);
        $this->assertEquals(30.0, $convResult['conversion']['factor']);

        // 2. Convert live via BFS Conversion Engine: 3 DUS -> 90 PCS
        $convertLiveResult = $tool->execute($this->business, $this->user, [
            'action' => 'convert',
            'from_unit' => 'DUS',
            'to_unit' => 'PCS',
            'quantity' => 3,
        ]);

        $this->assertTrue($convertLiveResult['success']);
        $this->assertEquals(90.0, $convertLiveResult['converted_quantity']);
        $this->assertEquals('PCS', $convertLiveResult['target_unit']);
    }

    public function test_end_to_end_mcp_po_3_dus_records_90_pcs_in_sub_warehouse_stock(): void
    {
        // 1. Setup Sub-Warehouse via MCP
        $whTool = new WarehouseManageLocationTool();
        $subWarehouseResult = $whTool->execute($this->business, $this->user, [
            'action' => 'create',
            'name' => 'Gudang Stok Indomie',
            'type' => 'warehouse',
            'parent_id' => $this->outlet->id,
            'code' => 'WH-INDOMIE',
        ]);
        $subWarehouseId = $subWarehouseResult['location']['id'];

        // 2. Setup Unit Conversion 1 DUS = 30 PCS via MCP
        $convTool = new MasterDataManageUnitConversionTool();
        $convTool->execute($this->business, $this->user, [
            'action' => 'create_conversion',
            'from_unit' => 'DUS',
            'to_unit' => 'PCS',
            'factor' => 30,
        ]);

        // 3. Create PO for 3 Dus via Purchasing MCP Tool
        $poTool = new PurchasingManageOrderTool();
        $createPoResult = $poTool->execute($this->business, $this->user, [
            'action' => 'create',
            'supplier_id' => $this->supplier->id,
            'po_type' => 'supplier',
            'notes' => 'PO Stok 3 Dus Indomie Goreng (@30 pcs)',
            'items' => [
                [
                    'item_type' => 'material',
                    'material_id' => $this->material->id,
                    'item_name' => $this->material->name,
                    'quantity' => 3,
                    'unit' => 'DUS',
                    'unit_price' => 300000, // Rp 300.000 / dus (total Rp 900.000)
                ],
            ],
        ]);

        $this->assertTrue($createPoResult['success']);
        $poId = $createPoResult['purchase_order']['id'];
        $po = PurchaseOrder::with('items')->findOrFail($poId);

        $this->assertEquals(PurchaseOrder::STATUS_CONFIRMED, $po->status);
        $this->assertEquals(3.0, (float) $po->items[0]->quantity);
        $this->assertEquals($this->unitDus->id, $po->items[0]->unit_id);
        $this->assertEquals(900000.0, (float) $po->total_amount);

        // 4. Receive goods into Sub-Warehouse via MCP: caller passes quantity = 90 (base unit)
        $receiveResult = $poTool->execute($this->business, $this->user, [
            'action' => 'receive_goods',
            'purchase_order_id' => $poId,
            'location_id' => $subWarehouseId,
            'items' => [
                [
                    'material_id' => $this->material->id,
                    'quantity' => 90, // 90 Pcs received into stock!
                ],
            ],
        ]);

        $this->assertTrue($receiveResult['success']);

        // 5. Verify PO is completed and stock in Sub-Warehouse is exactly 90 PCS
        $po->refresh();
        $this->assertEquals(PurchaseOrder::STATUS_COMPLETED, $po->status);

        $stock = InventoryStock::where('business_id', $this->business->id)
            ->where('location_id', $subWarehouseId)
            ->where('material_id', $this->material->id)
            ->first();

        $this->assertNotNull($stock);
        $this->assertEquals(90.0, (float) $stock->quantity);
        $this->assertEquals(10000.0, (float) $stock->avg_purchase_cost); // Rp 900.000 / 90 pcs = Rp 10.000 / pcs
    }

    public function test_end_to_end_mcp_po_receive_in_po_unit_with_auto_conversion(): void
    {
        // Setup Sub-Warehouse
        $subWarehouse = Location::create([
            'business_id' => $this->business->id,
            'parent_id' => $this->outlet->id,
            'name' => 'Gudang Belakang 2',
            'type' => 'warehouse',
            'code' => 'WH-BLKG-2',
            'is_active' => true,
        ]);

        // Register 1 DUS = 30 PCS
        UnitConversion::create([
            'business_id' => $this->business->id,
            'from_unit_id' => $this->unitDus->id,
            'to_unit_id' => $this->unitPcs->id,
            'factor' => 30,
        ]);

        // Create PO 2 Dus
        $poTool = new PurchasingManageOrderTool();
        $createResult = $poTool->execute($this->business, $this->user, [
            'action' => 'create',
            'supplier_id' => $this->supplier->id,
            'items' => [
                [
                    'item_type' => 'material',
                    'material_id' => $this->material->id,
                    'quantity' => 2,
                    'unit' => 'DUS',
                    'unit_price' => 300000,
                ],
            ],
        ]);

        $poId = $createResult['purchase_order']['id'];

        // Receive with quantity = 2 (in PO unit DUS) -> should auto-convert to 60 PCS in stock!
        $receiveResult = $poTool->execute($this->business, $this->user, [
            'action' => 'receive_goods',
            'purchase_order_id' => $poId,
            'location_id' => $subWarehouse->id,
            'items' => [
                [
                    'material_id' => $this->material->id,
                    'quantity' => 2, // 2 Dus
                ],
            ],
        ]);

        $this->assertTrue($receiveResult['success']);

        $stock = InventoryStock::where('business_id', $this->business->id)
            ->where('location_id', $subWarehouse->id)
            ->where('material_id', $this->material->id)
            ->first();

        $this->assertNotNull($stock);
        // 2 Dus * 30 factor = 60 PCS in inventory stock!
        $this->assertEquals(60.0, (float) $stock->quantity);
        $this->assertEquals(10000.0, (float) $stock->avg_purchase_cost);
    }
}
