<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Domain\Template\FeatureRegistry;
use App\Models\Business;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Str;
use Tests\TestCase;

final class BusinessFeatureParameterizationTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Business $business;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->owner = User::create([
            'name'              => 'Owner Scaling Test',
            'email'             => 'owner_scale_' . Str::random(6) . '@test.local',
            'phone'             => '62812' . rand(10000000, 99999999),
            'password'          => 'password',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);

        $this->business = Business::create([
            'name'           => 'Bisnis Parameterization Test',
            'currency'       => 'IDR',
            'business_scale' => Business::SCALE_UMKM,
        ]);

        $this->business->users()->attach($this->owner->id, [
            'id'   => (string) Str::uuid(),
            'role' => 'owner',
        ]);

        $this->owner->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);
    }

    protected function tearDown(): void
    {
        Context::flush();
        parent::tearDown();
    }

    public function test_feature_registry_contains_all_24_parameters_and_valid_categories(): void
    {
        $definitions = FeatureRegistry::definitions();
        $this->assertCount(24, $definitions, 'FeatureRegistry must define exactly 24 granular parameters.');

        $categories = FeatureRegistry::getCategories();
        $this->assertCount(4, $categories, 'FeatureRegistry must define 4 main clusters.');
        $this->assertArrayHasKey(FeatureRegistry::CAT_POS, $categories);
        $this->assertArrayHasKey(FeatureRegistry::CAT_VERTICAL, $categories);
        $this->assertArrayHasKey(FeatureRegistry::CAT_LOGISTICS_B2B, $categories);
        $this->assertArrayHasKey(FeatureRegistry::CAT_GOVERNANCE_MFG, $categories);

        $allKeys = FeatureRegistry::allKeys();
        $this->assertCount(24, $allKeys);
        $this->assertTrue(FeatureRegistry::isValidKey(FeatureRegistry::POS_ALLOW_NEGATIVE_STOCK));
        $this->assertTrue(FeatureRegistry::isValidKey(FeatureRegistry::LOGISTICS_DELIVERY_ORDER));
        $this->assertFalse(FeatureRegistry::isValidKey('invalid.feature.key'));
    }

    public function test_feature_registry_presets_for_tiers(): void
    {
        // Starter Tier: Lightweight, allow negative stock, no supervisor PIN, no delivery orders
        $starter = FeatureRegistry::getPresetForTier(FeatureRegistry::TIER_STARTER);
        $this->assertTrue($starter[FeatureRegistry::POS_ALLOW_NEGATIVE_STOCK]);
        $this->assertFalse($starter[FeatureRegistry::POS_REQUIRE_SUPERVISOR_PIN]);
        $this->assertFalse($starter[FeatureRegistry::LOGISTICS_DELIVERY_ORDER]);
        $this->assertFalse($starter[FeatureRegistry::INVENTORY_MULTI_WAREHOUSE]);

        // Growth Tier: Tightened cash control, wholesale pricing, no negative stock
        $growth = FeatureRegistry::getPresetForTier(FeatureRegistry::TIER_GROWTH);
        $this->assertFalse($growth[FeatureRegistry::POS_ALLOW_NEGATIVE_STOCK]);
        $this->assertTrue($growth[FeatureRegistry::POS_REQUIRE_SUPERVISOR_PIN]);
        $this->assertTrue($growth[FeatureRegistry::POS_BLIND_CLOSE_SHIFT]);
        $this->assertTrue($growth[FeatureRegistry::POS_WHOLESALE_PRICING]);
        $this->assertFalse($growth[FeatureRegistry::LOGISTICS_DELIVERY_ORDER]);

        // Corporate Tier: Heavy supply chain, delivery order, credit limit check, multi-warehouse
        $corporate = FeatureRegistry::getPresetForTier(FeatureRegistry::TIER_CORPORATE);
        $this->assertTrue($corporate[FeatureRegistry::LOGISTICS_DELIVERY_ORDER]);
        $this->assertTrue($corporate[FeatureRegistry::B2B_CREDIT_LIMIT]);
        $this->assertTrue($corporate[FeatureRegistry::INVENTORY_MULTI_WAREHOUSE]);
        $this->assertTrue($corporate[FeatureRegistry::PROCUREMENT_MULTI_APPROVAL]);
        $this->assertFalse($corporate[FeatureRegistry::POS_SHOW_IMAGES]);
    }

    public function test_business_default_feature_resolution_without_explicit_settings(): void
    {
        $this->assertTrue($this->business->isUmkm());
        $this->assertFalse($this->business->isGrowth());
        $this->assertFalse($this->business->isCorporate());

        // Default resolution for UMKM/Starter
        $this->assertTrue($this->business->isFeatureEnabled(FeatureRegistry::POS_ALLOW_NEGATIVE_STOCK));
        $this->assertFalse($this->business->isFeatureEnabled(FeatureRegistry::POS_REQUIRE_SUPERVISOR_PIN));
        $this->assertFalse($this->business->isFeatureEnabled(FeatureRegistry::LOGISTICS_DELIVERY_ORDER));

        // Default fallback parameter when key is missing or undefined
        $this->assertTrue($this->business->isFeatureEnabled('non.existent.key', true));
        $this->assertFalse($this->business->isFeatureEnabled('non.existent.key', false));
    }

    public function test_business_explicit_feature_toggle_and_persistence(): void
    {
        // Explicitly toggle negative stock to false
        $this->business->setFeatureEnabled(FeatureRegistry::POS_ALLOW_NEGATIVE_STOCK, false);
        $this->assertFalse($this->business->isFeatureEnabled(FeatureRegistry::POS_ALLOW_NEGATIVE_STOCK));

        // Explicitly enable Delivery Order
        $this->business->setFeatureEnabled(FeatureRegistry::LOGISTICS_DELIVERY_ORDER, true);
        $this->assertTrue($this->business->isFeatureEnabled(FeatureRegistry::LOGISTICS_DELIVERY_ORDER));

        // Reload from database to ensure persistence
        $fresh = $this->business->fresh();
        $this->assertFalse($fresh->isFeatureEnabled(FeatureRegistry::POS_ALLOW_NEGATIVE_STOCK));
        $this->assertTrue($fresh->isFeatureEnabled(FeatureRegistry::LOGISTICS_DELIVERY_ORDER));
    }

    public function test_apply_scaling_preset_transitions(): void
    {
        // 1. Transition to Growth
        $this->business->applyScalingPreset(FeatureRegistry::TIER_GROWTH);
        $this->assertTrue($this->business->isGrowth());
        $this->assertSame('growth', $this->business->getScalingTier());
        $this->assertTrue($this->business->isFeatureEnabled(FeatureRegistry::POS_REQUIRE_SUPERVISOR_PIN));
        $this->assertTrue($this->business->isFeatureEnabled(FeatureRegistry::POS_WHOLESALE_PRICING));
        $this->assertFalse($this->business->isFeatureEnabled(FeatureRegistry::LOGISTICS_DELIVERY_ORDER));

        // 2. Transition to Corporate
        $this->business->applyScalingPreset(FeatureRegistry::TIER_CORPORATE);
        $this->assertTrue($this->business->isCorporate());
        $this->assertSame('corporate', $this->business->getScalingTier());
        $this->assertTrue($this->business->isFeatureEnabled(FeatureRegistry::LOGISTICS_DELIVERY_ORDER));
        $this->assertTrue($this->business->isFeatureEnabled(FeatureRegistry::B2B_CREDIT_LIMIT));
        $this->assertTrue($this->business->isFeatureEnabled(FeatureRegistry::INVENTORY_MULTI_WAREHOUSE));

        // 3. Transition back to Starter (Zero Data Loss)
        $this->business->applyScalingPreset(FeatureRegistry::TIER_STARTER);
        $this->assertTrue($this->business->isUmkm());
        $this->assertSame('starter', $this->business->getScalingTier());
        $this->assertTrue($this->business->isFeatureEnabled(FeatureRegistry::POS_ALLOW_NEGATIVE_STOCK));
        $this->assertFalse($this->business->isFeatureEnabled(FeatureRegistry::LOGISTICS_DELIVERY_ORDER));
    }

    public function test_blade_directive_feature(): void
    {
        // Set business to Starter (Delivery order disabled, negative stock enabled)
        $this->business->applyScalingPreset(FeatureRegistry::TIER_STARTER);

        $template = '@feature(\'pos.allow_negative_stock\') ALLOWED @else DENIED @endfeature';
        $rendered = trim(Blade::render($template));
        $this->assertSame('ALLOWED', $rendered);

        $templateDo = '@feature(\'logistics.delivery_order\') DO_ENABLED @else DO_DISABLED @endfeature';
        $renderedDo = trim(Blade::render($templateDo));
        $this->assertSame('DO_DISABLED', $renderedDo);

        // Turn on delivery order
        $this->business->setFeatureEnabled(FeatureRegistry::LOGISTICS_DELIVERY_ORDER, true);
        $renderedDoAfter = trim(Blade::render($templateDo));
        $this->assertSame('DO_ENABLED', $renderedDoAfter);
    }
}
