<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Material;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class SyncDoGoProductionDataTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DefaultUnitSeeder::class);
        $this->seed(\Database\Seeders\RbacSeeder::class);
    }

    public function test_sync_dogo_production_data_command_executes_successfully(): void
    {
        $exitCode = Artisan::call('cooca:sync-dogo-data', [
            '--email' => 'srifennikapurba@gmail.com',
            '--force' => true,
        ]);

        $this->assertEquals(0, $exitCode);

        // Verify user and business
        $user = User::where('email', 'srifennikapurba@gmail.com')->first();
        $this->assertNotNull($user);

        $business = Business::where('name', 'like', '%D0%')->first();
        $this->assertNotNull($business);

        // Verify 45 Materials
        $materials = Material::where('business_id', $business->id)->get();
        $this->assertCount(45, $materials);

        // Verify 24 Products
        $products = Product::where('business_id', $business->id)->with('costModels.bomHeaders.items')->get();
        $this->assertCount(24, $products);

        // Verify formula Harga Jual = HPP + 2865
        foreach ($products as $p) {
            $this->assertEquals($p->base_cost + 2865, $p->selling_price, "Product {$p->name} price formula mismatch");
            $this->assertNotEmpty($p->costModels);
            $this->assertNotEmpty($p->costModels->first()->bomHeaders->first()->items);
        }

        // Verify specific recipe, e.g. Tropical Mango (TM)
        $tm = $products->firstWhere('code', 'TM');
        $this->assertNotNull($tm);
        $this->assertEquals(10508, $tm->base_cost);
        $this->assertEquals(13373, $tm->selling_price);

        $tmItems = $tm->costModels->first()->bomHeaders->first()->items;
        $this->assertCount(6, $tmItems);
    }

    public function test_dogo_sync_status_api_endpoint(): void
    {
        Artisan::call('cooca:sync-dogo-data', [
            '--email' => 'srifennikapurba@gmail.com',
            '--force' => true,
        ]);

        $response = $this->getJson('/api/v1/dogo/sync-status');
        $response->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('live_counts.materials', 45)
            ->assertJsonPath('live_counts.products', 24)
            ->assertJsonPath('live_counts.bom_items', 151);
    }

    public function test_pos_sale_automatically_consumes_raw_materials_via_bom(): void
    {
        Artisan::call('cooca:sync-dogo-data', [
            '--email' => 'srifennikapurba@gmail.com',
            '--force' => true,
        ]);

        $business = Business::where('name', 'like', '%D0%')->first();
        $location = \App\Models\Location::where('business_id', $business->id)->first();
        $tm = Product::where('business_id', $business->id)->where('code', 'TM')->first();

        $stockService = app(\App\Domain\Inventory\StockService::class);

        // 1. Berikan stok awal bahan baku
        $mango = Material::where('business_id', $business->id)->where('name', 'Mango')->first();
        $passionJam = Material::where('business_id', $business->id)->where('name', 'Passion Fruit Jam')->first();
        $lemonJuice = Material::where('business_id', $business->id)->where('name', 'Lemon Juice')->first();
        $mangoJuice = Material::where('business_id', $business->id)->where('name', 'Mango Juice')->first();
        $yoghurtPlain = Material::where('business_id', $business->id)->where('name', 'Yoghurt Plain')->first();
        $mangoJam = Material::where('business_id', $business->id)->where('name', 'Mango Jam')->first();

        $stockService->recordMovement(businessId: $business->id, locationId: $location->id, movementType: \App\Models\StockMovement::TYPE_INITIAL, quantityChange: 1000.0, unitCost: 35.0, referenceNumber: 'INIT-01', notes: 'Stok Awal', materialId: $mango->id);
        $stockService->recordMovement(businessId: $business->id, locationId: $location->id, movementType: \App\Models\StockMovement::TYPE_INITIAL, quantityChange: 500.0, unitCost: 59.3, referenceNumber: 'INIT-02', notes: 'Stok Awal', materialId: $passionJam->id);
        $stockService->recordMovement(businessId: $business->id, locationId: $location->id, movementType: \App\Models\StockMovement::TYPE_INITIAL, quantityChange: 500.0, unitCost: 18.1, referenceNumber: 'INIT-03', notes: 'Stok Awal', materialId: $lemonJuice->id);
        $stockService->recordMovement(businessId: $business->id, locationId: $location->id, movementType: \App\Models\StockMovement::TYPE_INITIAL, quantityChange: 1000.0, unitCost: 17.3, referenceNumber: 'INIT-04', notes: 'Stok Awal', materialId: $mangoJuice->id);
        $stockService->recordMovement(businessId: $business->id, locationId: $location->id, movementType: \App\Models\StockMovement::TYPE_INITIAL, quantityChange: 500.0, unitCost: 38.1, referenceNumber: 'INIT-05', notes: 'Stok Awal', materialId: $yoghurtPlain->id);
        $stockService->recordMovement(businessId: $business->id, locationId: $location->id, movementType: \App\Models\StockMovement::TYPE_INITIAL, quantityChange: 500.0, unitCost: 59.3, referenceNumber: 'INIT-06', notes: 'Stok Awal', materialId: $mangoJam->id);

        // 2. Simulasikan Penjualan 2 cup Tropical Mango di POS
        $movements = $stockService->deductForPosSale(
            businessId: $business->id,
            locationId: $location->id,
            product: $tm,
            quantity: 2.0,
            unitCost: $tm->base_cost,
            orderId: 'ORD-TEST-001',
            orderNumber: 'POS-001'
        );

        // 3. Verifikasi tepat 6 movement bahan baku tercipta
        $this->assertCount(6, $movements);

        // Verifikasi pengurangan kuantitas masing-masing bahan sesuai resep:
        // Mango: 50g * 2 = 100g
        $mangoMov = collect($movements)->firstWhere('material_id', $mango->id);
        $this->assertEquals(-100.0, (float) $mangoMov->quantity_change);

        // Passion Fruit Jam: 10g * 2 = 20g
        $passionMov = collect($movements)->firstWhere('material_id', $passionJam->id);
        $this->assertEquals(-20.0, (float) $passionMov->quantity_change);

        // Lemon Juice: 10ml * 2 = 20ml
        $lemonMov = collect($movements)->firstWhere('material_id', $lemonJuice->id);
        $this->assertEquals(-20.0, (float) $lemonMov->quantity_change);

        // Mango Juice: 160ml * 2 = 320ml
        $mangoJuiceMov = collect($movements)->firstWhere('material_id', $mangoJuice->id);
        $this->assertEquals(-320.0, (float) $mangoJuiceMov->quantity_change);

        // Yoghurt Plain: 35g * 2 = 70g
        $yoghurtMov = collect($movements)->firstWhere('material_id', $yoghurtPlain->id);
        $this->assertEquals(-70.0, (float) $yoghurtMov->quantity_change);

        // Mango Jam: 15g * 2 = 30g
        $mangoJamMov = collect($movements)->firstWhere('material_id', $mangoJam->id);
        $this->assertEquals(-30.0, (float) $mangoJamMov->quantity_change);

        // 4. Verifikasi sisa stok akhir di tabel inventory_stocks
        $mangoStock = \App\Models\InventoryStock::where('business_id', $business->id)
            ->where('location_id', $location->id)
            ->where('material_id', $mango->id)
            ->value('quantity');
        $this->assertEquals(900.0, (float) $mangoStock); // 1000 - 100 = 900

        $mangoJuiceStock = \App\Models\InventoryStock::where('business_id', $business->id)
            ->where('location_id', $location->id)
            ->where('material_id', $mangoJuice->id)
            ->value('quantity');
        $this->assertEquals(680.0, (float) $mangoJuiceStock); // 1000 - 320 = 680
    }
}
