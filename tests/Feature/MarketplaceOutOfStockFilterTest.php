<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class MarketplaceOutOfStockFilterTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $user;
    private Unit $unit;
    private Location $location;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->user = User::create([
            'name'     => 'Owner Toko',
            'email'    => 'owner@cooca.id',
            'password' => bcrypt('password123'),
        ]);

        $this->business = Business::create([
            'name'            => 'Toko Utama COOCA',
            'currency_code'   => 'IDR',
            'currency_symbol' => 'Rp',
        ]);

        $this->business->users()->attach($this->user->id, [
            'id'        => (string) Str::uuid(),
            'role'      => 'owner',
            'is_active' => true,
        ]);

        $this->user->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);

        $this->unit = Unit::create([
            'business_id' => $this->business->id,
            'code'        => 'PCS',
            'name'        => 'Pieces',
            'category'    => Unit::CATEGORY_QUANTITY,
            'is_base'     => true,
        ]);

        $this->location = Location::create([
            'business_id' => $this->business->id,
            'name'        => 'Gudang Utama',
            'code'        => 'GD-01',
            'type'        => 'warehouse',
            'is_active'   => true,
        ]);
    }

    public function test_marketplace_products_risk_filter_out_of_stock_returns_200_without_sql_error(): void
    {
        $inStockProduct = Product::create([
            'business_id'    => $this->business->id,
            'output_unit_id' => $this->unit->id,
            'code'           => 'PRD-READY',
            'name'           => 'Sepatu Lari Ready',
            'selling_price'  => 350000,
            'is_active'      => true,
        ]);
        InventoryStock::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'product_id'  => $inStockProduct->id,
            'quantity'    => 10,
        ]);

        $outOfStockProduct = Product::create([
            'business_id'    => $this->business->id,
            'output_unit_id' => $this->unit->id,
            'code'           => 'PRD-HABIS',
            'name'           => 'Kemeja Batik Habis',
            'selling_price'  => 180000,
            'is_active'      => true,
        ]);

        // Request with risk=out_of_stock
        $response = $this->actingAs($this->user)
            ->get(route('marketplace-hub.products', ['risk' => 'out_of_stock']));

        $response->assertStatus(200);
        $response->assertSee('Kemeja Batik Habis');
        $response->assertDontSee('Sepatu Lari Ready');
    }
}
