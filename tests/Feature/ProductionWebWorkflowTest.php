<?php

declare(strict_types=1);

namespace Tests\Feature;

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
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProductionWebWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $owner;
    private Location $sourceWarehouse;
    private Location $targetWarehouse;
    private Product $finishedProduct;
    private Material $rawMaterial;
    private Unit $kgUnit;
    private Unit $pcsUnit;
    private BomHeader $bomHeader;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->owner = User::create([
            'name' => 'Factory Owner',
            'email' => 'factory-owner@cooca.test',
            'email_verified_at' => now(),
            'phone' => '081234567890',
            'password' => 'password',
        ]);

        $this->business = Business::create([
            'name' => 'PT Manufaktur Nusantara',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'allow_negative_stock' => true,
        ]);

        $this->business->users()->attach($this->owner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
        ]);

        $this->owner->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);

        config(['app.locale' => 'id']);
        app()->setLocale('id');
        $this->withSession(['locale' => 'id']);

        $this->kgUnit = Unit::create([
            'business_id' => $this->business->id,
            'code' => 'kg',
            'name' => 'Kilogram',
            'category' => Unit::CATEGORY_WEIGHT,
            'is_base' => true,
        ]);

        $this->pcsUnit = Unit::create([
            'business_id' => $this->business->id,
            'code' => 'pcs',
            'name' => 'Pcs',
            'category' => Unit::CATEGORY_QUANTITY,
            'is_base' => true,
        ]);

        $this->sourceWarehouse = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Gudang Bahan Baku Utama',
            'type' => 'warehouse',
            'is_active' => true,
        ]);

        $this->targetWarehouse = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Gudang Barang Jadi',
            'type' => 'warehouse',
            'is_active' => true,
        ]);

        $this->finishedProduct = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Keripik Singkong Pedas 200g',
            'code' => 'KRP-PD-200',
            'type' => Product::TYPE_GOODS,
            'selling_price' => 15000,
            'output_unit_id' => $this->pcsUnit->id,
            'is_active' => true,
        ]);

        $this->rawMaterial = Material::create([
            'business_id' => $this->business->id,
            'name' => 'Singkong Mentah Grade A',
            'code' => 'MAT-SK-01',
            'unit_id' => $this->kgUnit->id,
        ]);

        MaterialPrice::create([
            'business_id' => $this->business->id,
            'material_id' => $this->rawMaterial->id,
            'purchase_unit_id' => $this->kgUnit->id,
            'purchase_price' => 5000,
            'effective_date' => now()->toDateString(),
            'sequence' => 1,
        ]);

        // Stock in source warehouse
        InventoryStock::create([
            'business_id' => $this->business->id,
            'location_id' => $this->sourceWarehouse->id,
            'material_id' => $this->rawMaterial->id,
            'quantity' => 500.0,
        ]);

        $costModel = CostModel::create([
            'business_id' => $this->business->id,
            'product_id' => $this->finishedProduct->id,
            'name' => 'Formula Standar Keripik Singkong',
            'method' => CostModel::METHOD_RECIPE_BOM,
            'is_active' => true,
        ]);

        $this->bomHeader = BomHeader::create([
            'cost_model_id' => $costModel->id,
            'type' => BomHeader::TYPE_RECIPE,
            'name' => 'Resep Standar Batch',
        ]);

        BomItem::create([
            'bom_header_id' => $this->bomHeader->id,
            'material_id' => $this->rawMaterial->id,
            'unit_id' => $this->kgUnit->id,
            'quantity' => 0.5,
            'waste_percentage' => 5.0, // 0.525 kg needed per 1 pcs (52.5 kg per 100 pcs)
        ]);
    }

    public function test_user_can_view_production_index_page(): void
    {
        $response = $this->actingAs($this->owner)
            ->get(route('production.index'));

        $response->assertOk();
        $response->assertSee('Perintah Produksi');
        $response->assertSee('Buat SPKP Baru');
    }

    public function test_user_can_create_and_store_production_order(): void
    {
        $response = $this->actingAs($this->owner)
            ->post(route('production.store'), [
                'product_id' => $this->finishedProduct->id,
                'planned_quantity' => 200,
                'source_location_id' => $this->sourceWarehouse->id,
                'target_location_id' => $this->targetWarehouse->id,
                'planned_start_date' => now()->toDateString(),
                'total_labor_cost' => 100000,
                'total_machine_cost' => 50000,
                'batch_number' => 'BATCH-TEST-001',
                'notes' => 'Instruksi khusus penggorengan suhu 170C',
            ]);

        $order = ProductionOrder::where('business_id', $this->business->id)->first();
        $this->assertNotNull($order);
        $this->assertEquals(200, $order->planned_quantity);
        $this->assertEquals(ProductionOrder::STATUS_DRAFT, $order->status);

        $response->assertRedirect(route('production.show', $order));

        // Check BOM items generated: 50kg * (200/100) * 1.05 = 105 kg
        $item = $order->items()->first();
        $this->assertNotNull($item);
        $this->assertEquals(105.0, (float) $item->planned_quantity);
    }

    public function test_user_can_view_edit_page_and_update_production_order_in_draft(): void
    {
        // 1. Create order
        $createResponse = $this->actingAs($this->owner)
            ->post(route('production.store'), [
                'product_id' => $this->finishedProduct->id,
                'planned_quantity' => 100,
                'source_location_id' => $this->sourceWarehouse->id,
                'target_location_id' => $this->targetWarehouse->id,
                'planned_start_date' => now()->toDateString(),
                'batch_number' => 'BATCH-INITIAL',
            ]);

        $order = ProductionOrder::where('business_id', $this->business->id)->first();

        // 2. View Edit page
        $editResponse = $this->actingAs($this->owner)
            ->get(route('production.edit', $order));

        $editResponse->assertOk();
        $editResponse->assertSee($order->order_number);
        $editResponse->assertSee('Edit Perintah Kerja Produksi');

        // 3. Update order in draft (updating planned quantity and custom item planned quantity)
        $item = $order->items()->first();

        $updateResponse = $this->actingAs($this->owner)
            ->put(route('production.update', $order), [
                'planned_quantity' => 150,
                'source_location_id' => $this->sourceWarehouse->id,
                'target_location_id' => $this->targetWarehouse->id,
                'planned_start_date' => now()->addDay()->toDateString(),
                'total_labor_cost' => 120000,
                'total_machine_cost' => 60000,
                'batch_number' => 'BATCH-UPDATED-002',
                'notes' => 'Catatan revisi formulir SPKP draf',
                'items' => [
                    [
                        'id' => $item->id,
                        'planned_quantity' => 80.0,
                        'notes' => 'Penambahan toleransi kadar air bahan baku',
                    ],
                ],
            ]);

        $updateResponse->assertRedirect(route('production.show', $order));

        $order->refresh();
        $this->assertEquals(150.0, $order->planned_quantity);
        $this->assertEquals('BATCH-UPDATED-002', $order->batch_number);
        $this->assertEquals('Catatan revisi formulir SPKP draf', $order->notes);

        $item->refresh();
        $this->assertEquals(80.0, (float) $item->planned_quantity);
        $this->assertEquals(400000.0, (float) $item->total_cost); // 80 * 5000
    }

    public function test_user_can_view_print_page_with_required_columns_and_product_name(): void
    {
        $this->actingAs($this->owner)
            ->post(route('production.store'), [
                'product_id' => $this->finishedProduct->id,
                'planned_quantity' => 100,
                'source_location_id' => $this->sourceWarehouse->id,
                'target_location_id' => $this->targetWarehouse->id,
                'planned_start_date' => now()->toDateString(),
                'batch_number' => 'BATCH-PRINT-CHECK',
            ]);

        $order = ProductionOrder::where('business_id', $this->business->id)->first();

        $printResponse = $this->actingAs($this->owner)
            ->get(route('production.print', $order));

        $printResponse->assertOk();
        // Product name must be displayed prominently
        $printResponse->assertSee($this->finishedProduct->name);

        // Required columns specified by user:
        // Nama Bahan Baku
        // Rencana Standar (+Waste)
        // Pengeluaran Aktual (WIP)
        $printResponse->assertSee('NAMA BAHAN BAKU');
        $printResponse->assertSee('RENCANA STANDAR (+WASTE)');
        $printResponse->assertSee('PENGELUARAN AKTUAL (WIP)');

        // Check raw material name appears in table
        $printResponse->assertSee($this->rawMaterial->name);
    }

    public function test_user_can_issue_materials_and_edit_actual_consumption_wip(): void
    {
        $this->actingAs($this->owner)
            ->post(route('production.store'), [
                'product_id' => $this->finishedProduct->id,
                'planned_quantity' => 100,
                'source_location_id' => $this->sourceWarehouse->id,
                'target_location_id' => $this->targetWarehouse->id,
                'planned_start_date' => now()->toDateString(),
            ]);

        $order = ProductionOrder::where('business_id', $this->business->id)->first();

        // 1. Issue materials
        $issueResponse = $this->actingAs($this->owner)
            ->post(route('production.issue-materials', $order));

        $issueResponse->assertSessionHas('success');
        $order->refresh();
        $this->assertEquals(ProductionOrder::STATUS_IN_PROGRESS, $order->status);

        // 2. Edit WIP in progress
        $item = $order->items()->first();
        $this->assertEquals(52.5, (float) $item->actual_consumed_quantity);

        // Update actual consumed quantity to 55 kg
        $updateResponse = $this->actingAs($this->owner)
            ->put(route('production.update', $order), [
                'target_location_id' => $this->targetWarehouse->id,
                'items' => [
                    [
                        'id' => $item->id,
                        'actual_consumed_quantity' => 55.0,
                        'notes' => 'Tambahan 2.5 kg karena kulit singkong tebal',
                    ],
                ],
            ]);

        $updateResponse->assertRedirect(route('production.show', $order));

        $item->refresh();
        $this->assertEquals(55.0, (float) $item->actual_consumed_quantity);
    }

    public function test_user_cannot_edit_completed_order(): void
    {
        $this->actingAs($this->owner)
            ->post(route('production.store'), [
                'product_id' => $this->finishedProduct->id,
                'planned_quantity' => 100,
                'source_location_id' => $this->sourceWarehouse->id,
                'target_location_id' => $this->targetWarehouse->id,
                'planned_start_date' => now()->toDateString(),
            ]);

        $order = ProductionOrder::where('business_id', $this->business->id)->first();

        // Issue materials & complete
        $this->actingAs($this->owner)->post(route('production.issue-materials', $order));
        $this->actingAs($this->owner)->post(route('production.complete', $order), [
            'actual_quantity' => 98,
            'rejected_quantity' => 2,
        ]);

        $order->refresh();
        $this->assertEquals(ProductionOrder::STATUS_COMPLETED, $order->status);

        // Try to access edit page
        $editResponse = $this->actingAs($this->owner)
            ->get(route('production.edit', $order));

        $editResponse->assertRedirect(route('production.show', $order));
        $editResponse->assertSessionHas('error');

        // Try to put update
        $updateResponse = $this->actingAs($this->owner)
            ->put(route('production.update', $order), [
                'notes' => 'Attempt illegal edit',
            ]);

        $updateResponse->assertRedirect(route('production.show', $order));
        $updateResponse->assertSessionHas('error');
    }
}
