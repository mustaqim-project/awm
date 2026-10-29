<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Template\FeatureRegistry;
use App\Models\Business;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Role;
use App\Models\SalesOrder;
use App\Models\Unit;
use App\Models\User;
use Database\Seeders\DefaultUnitSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Str;
use Tests\TestCase;

class BusinessScalingAndParameterizationTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $ownerUser;

    protected function setUp(): void
    {
        parent::setUp();
        \App\Support\Context::flush();

        $this->seed(DefaultUnitSeeder::class);
        $this->seed(RbacSeeder::class);

        $this->business = Business::create([
            'name' => 'ScaleUp Enterprise PT',
            'slug' => 'scaleup-enterprise',
            'currency' => 'IDR',
            'currency_symbol' => 'Rp',
            'business_type' => 'retail',
            'is_active' => true,
        ]);

        $this->ownerUser = User::create([
            'name' => 'Owner Scaleup',
            'email' => 'owner.scaleup@example.com',
            'phone' => '6281298765499',
            'password' => 'password',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);

        $ownerRole = Role::where('slug', 'owner')->first();
        $this->business->users()->attach($this->ownerUser->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'role_id' => $ownerRole?->id,
        ]);

        $this->ownerUser->update(['active_business_id' => $this->business->id]);
        \App\Support\Context::setBusiness($this->business);
    }

    protected function tearDown(): void
    {
        \App\Support\Context::flush();
        parent::tearDown();
    }

    public function test_can_apply_starter_growth_and_corporate_presets(): void
    {
        // 1. Apply Starter Tier
        $this->business->applyScalingPreset(Business::SCALE_STARTER);
        $this->assertEquals(Business::SCALE_STARTER, $this->business->getScalingTier());
        $this->assertTrue($this->business->isUmkm());
        $this->assertFalse($this->business->isGrowth());
        $this->assertFalse($this->business->isCorporate());

        // Starter defaults: wholesale pricing false, negative stock allowed
        $this->assertFalse($this->business->isFeatureEnabled(FeatureRegistry::POS_WHOLESALE_TIERED_PRICING));
        $this->assertFalse($this->business->isFeatureEnabled(FeatureRegistry::LOGISTICS_DELIVERY_ORDER));
        $this->assertTrue($this->business->isFeatureEnabled(FeatureRegistry::POS_ALLOW_NEGATIVE_STOCK));

        // 2. Scale Up to Growth Tier
        $this->business->applyScalingPreset(Business::SCALE_GROWTH);
        $this->assertEquals(Business::SCALE_GROWTH, $this->business->getScalingTier());
        $this->assertFalse($this->business->isUmkm());
        $this->assertTrue($this->business->isGrowth());
        $this->assertFalse($this->business->isCorporate());

        // Growth defaults: wholesale pricing enabled, strict stock, delivery order false by default
        $this->assertTrue($this->business->isFeatureEnabled(FeatureRegistry::POS_WHOLESALE_TIERED_PRICING));
        $this->assertFalse($this->business->isFeatureEnabled(FeatureRegistry::POS_ALLOW_NEGATIVE_STOCK));
        $this->assertFalse($this->business->isFeatureEnabled(FeatureRegistry::LOGISTICS_DELIVERY_ORDER));

        // 3. Scale Up to Corporate Tier
        $this->business->applyScalingPreset(Business::SCALE_CORPORATE_TIER);
        $this->assertEquals(Business::SCALE_CORPORATE_TIER, $this->business->getScalingTier());
        $this->assertTrue($this->business->isCorporate());
        $this->assertTrue($this->business->isFeatureEnabled(FeatureRegistry::INVENTORY_MULTI_WAREHOUSE));
        $this->assertTrue($this->business->isFeatureEnabled(FeatureRegistry::PROCUREMENT_MULTI_APPROVAL));
        $this->assertTrue($this->business->isFeatureEnabled(FeatureRegistry::LOGISTICS_DELIVERY_ORDER));

        // 4. Downgrade Back to Starter (Zero Data Loss)
        $this->business->applyScalingPreset(Business::SCALE_STARTER);
        $this->assertEquals(Business::SCALE_STARTER, $this->business->getScalingTier());
        $this->assertTrue($this->business->isUmkm());
    }

    public function test_blade_feature_directive_reflects_active_tier_features(): void
    {
        \App\Support\Context::setBusiness($this->business);

        // Turn OFF feature
        $this->business->setFeatureEnabled(FeatureRegistry::POS_WHOLESALE_TIERED_PRICING, false);
        $template = "@feature('pos.wholesale_tiered_pricing') SHOWN @else HIDDEN @endfeature";
        $rendered = Blade::render($template);
        $this->assertStringContainsString('HIDDEN', $rendered);
        $this->assertStringNotContainsString('SHOWN', $rendered);

        // Turn ON feature
        $this->business->setFeatureEnabled(FeatureRegistry::POS_WHOLESALE_TIERED_PRICING, true);
        $renderedOn = Blade::render($template);
        $this->assertStringContainsString('SHOWN', $renderedOn);
        $this->assertStringNotContainsString('HIDDEN', $renderedOn);
    }

    public function test_product_wholesale_pricing_effective_calculation_respects_feature_toggle(): void
    {
        $unit = Unit::first() ?? Unit::create([
            'business_id' => $this->business->id,
            'name' => 'Pieces',
            'code' => 'PCS',
            'symbol' => 'PCS',
            'category' => 'count',
            'type' => 'unit',
        ]);

        $product = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Minyak Goreng 2L',
            'output_unit_id' => $unit->id,
            'selling_price' => 35000,
            'wholesale_min_qty' => 10,
            'wholesale_price' => 31000,
        ]);

        \App\Support\Context::setBusiness($this->business);

        // Case A: Feature is DISABLED (e.g. Starter tier)
        $this->business->setFeatureEnabled(FeatureRegistry::POS_WHOLESALE_TIERED_PRICING, false);
        // Even buying 15 pcs (>= 10), price remains regular selling price
        $this->assertEquals(35000.0, $product->getEffectivePriceForQuantity(15.0));
        $this->assertEquals(35000.0, $product->getEffectivePriceForQuantity(5.0));

        // Case B: Feature is ENABLED (e.g. Growth or Corporate tier)
        $this->business->setFeatureEnabled(FeatureRegistry::POS_WHOLESALE_TIERED_PRICING, true);
        // Buying below threshold (5 pcs < 10) -> normal selling price
        $this->assertEquals(35000.0, $product->getEffectivePriceForQuantity(5.0));
        // Buying at or above threshold (10 pcs or 15 pcs) -> wholesale price
        $this->assertEquals(31000.0, $product->getEffectivePriceForQuantity(10.0));
        $this->assertEquals(31000.0, $product->getEffectivePriceForQuantity(25.0));
    }

    public function test_delivery_order_pdf_endpoint_is_guarded_by_feature_parameter(): void
    {
        $customer = Customer::create([
            'business_id' => $this->business->id,
            'name' => 'Toko Mitra Berkah',
            'address' => 'Jl. Niaga Raya No. 45 Jakarta',
            'phone' => '081234567890',
        ]);

        $salesOrder = SalesOrder::create([
            'business_id' => $this->business->id,
            'customer_id' => $customer->id,
            'so_number' => 'SO-2026-TEST-001',
            'order_date' => now()->toDateString(),
            'status' => 'confirmed',
            'total_amount' => 500000,
        ]);

        // When feature is DISABLED -> 403 Forbidden
        $this->business->setFeatureEnabled(FeatureRegistry::LOGISTICS_DELIVERY_ORDER, false);
        $responseDisabled = $this->actingAs($this->ownerUser)
            ->withSession(['current_business_id' => $this->business->id])
            ->get(route('sales.orders.delivery-order', $salesOrder));

        $responseDisabled->assertStatus(403);

        // When feature is ENABLED -> 200 OK (PDF stream)
        $this->business->setFeatureEnabled(FeatureRegistry::LOGISTICS_DELIVERY_ORDER, true);
        $responseEnabled = $this->actingAs($this->ownerUser)
            ->withSession(['current_business_id' => $this->business->id])
            ->get(route('sales.orders.delivery-order', $salesOrder));

        $responseEnabled->assertStatus(200);
        $this->assertStringContainsString('application/pdf', (string) $responseEnabled->headers->get('Content-Type'));
    }

    public function test_quick_track_redirects_to_storefront_order_track(): void
    {
        $commerceOrder = \App\Models\CommerceOrder::create([
            'business_id' => $this->business->id,
            'order_number' => 'ORD-TRACK-001',
            'tracking_token' => 'TRK-RANDOM-TOKEN-777',
            'customer_name' => 'Ibu Rahmawati',
            'customer_phone' => '081987654321',
            'total_amount' => 125000,
            'status' => 'processing',
        ]);

        $response = $this->get('/track/TRK-RANDOM-TOKEN-777');
        $response->assertRedirect(route('public.storefront.order.track', [$this->business->slug, 'TRK-RANDOM-TOKEN-777']));
    }
}
