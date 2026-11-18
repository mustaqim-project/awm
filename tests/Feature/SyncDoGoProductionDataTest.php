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
}
