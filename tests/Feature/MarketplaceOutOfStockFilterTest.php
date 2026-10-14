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

    public function test_marketplace_product_can_be_deactivated_and_activated_on_channels(): void
    {
        $product = Product::create([
            'business_id'    => $this->business->id,
            'output_unit_id' => $this->unit->id,
            'code'           => 'PRD-MKT',
            'name'           => 'Produk Omnichannel Keren',
            'selling_price'  => 150000,
            'is_active'      => true,
        ]);

        $shopeeAccount = \App\Models\MarketplaceAccount::create([
            'business_id' => $this->business->id,
            'channel'     => 'shopee',
            'shop_id'     => 'shopee_shop_1',
            'shop_name'   => 'Shopee Store Test',
            'status'      => \App\Models\MarketplaceAccount::STATUS_CONNECTED,
            'is_active'   => true,
        ]);

        $tokpedAccount = \App\Models\MarketplaceAccount::create([
            'business_id' => $this->business->id,
            'channel'     => 'tokopedia',
            'shop_id'     => 'tokped_shop_1',
            'shop_name'   => 'Tokopedia Store Test',
            'status'      => \App\Models\MarketplaceAccount::STATUS_CONNECTED,
            'is_active'   => true,
        ]);

        $shopeeMapping = \App\Models\MarketplaceProductMapping::create([
            'business_id'            => $this->business->id,
            'marketplace_account_id' => $shopeeAccount->id,
            'product_id'             => $product->id,
            'channel'                => 'shopee',
            'external_product_id'    => 'shp-999',
            'channel_price'          => 160000,
            'is_active'              => true,
            'sync_status'            => \App\Models\MarketplaceProductMapping::STATUS_SYNCED,
        ]);

        $tokpedMapping = \App\Models\MarketplaceProductMapping::create([
            'business_id'            => $this->business->id,
            'marketplace_account_id' => $tokpedAccount->id,
            'product_id'             => $product->id,
            'channel'                => 'tokopedia',
            'external_product_id'    => 'tkp-888',
            'channel_price'          => 165000,
            'is_active'              => true,
            'sync_status'            => \App\Models\MarketplaceProductMapping::STATUS_SYNCED,
        ]);

        // 1. Deactivate on Shopee only
        $resShopee = $this->actingAs($this->user)
            ->postJson(route('marketplace-hub.products.deactivate', $product->id), [
                'channel' => 'shopee',
            ]);

        $resShopee->assertStatus(200);
        $resShopee->assertJson(['success' => true]);

        $this->assertFalse($shopeeMapping->fresh()->is_active);
        $this->assertTrue($tokpedMapping->fresh()->is_active);

        // 2. Deactivate all channels
        $resAll = $this->actingAs($this->user)
            ->postJson(route('marketplace-hub.products.deactivate', $product->id), [
                'channel' => 'all',
            ]);

        $resAll->assertStatus(200);
        $resAll->assertJson(['success' => true]);

        $this->assertFalse($shopeeMapping->fresh()->is_active);
        $this->assertFalse($tokpedMapping->fresh()->is_active);

        // 3. Status filter verifies inactive mapping is returned under ?status=inactive
        $resInactive = $this->actingAs($this->user)
            ->get(route('marketplace-hub.products', ['status' => 'inactive']));
        $resInactive->assertStatus(200);
        $resInactive->assertSee('Produk Omnichannel Keren');

        // 4. Reactivate all channels
        $resActivate = $this->actingAs($this->user)
            ->postJson(route('marketplace-hub.products.activate', $product->id), [
                'channel' => 'all',
            ]);

        $resActivate->assertStatus(200);
        $resActivate->assertJson(['success' => true]);

        $this->assertTrue($shopeeMapping->fresh()->is_active);
        $this->assertTrue($tokpedMapping->fresh()->is_active);

        // 5. Status filter verifies active mapping is returned under ?status=active
        $resActive = $this->actingAs($this->user)
            ->get(route('marketplace-hub.products', ['status' => 'active']));
        $resActive->assertStatus(200);
        $resActive->assertSee('Produk Omnichannel Keren');
    }
}

