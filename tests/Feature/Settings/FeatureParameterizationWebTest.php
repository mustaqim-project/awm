<?php

declare(strict_types=1);

namespace Tests\Feature\Settings;

use App\Domain\Template\FeatureRegistry;
use App\Models\AuditLog;
use App\Models\Business;
use App\Models\Role;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\BusinessTemplateSeeder;
use Database\Seeders\DefaultCostCategorySeeder;
use Database\Seeders\DefaultUnitSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class FeatureParameterizationWebTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private User $staff;
    private Business $business;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->seed(DefaultUnitSeeder::class);
        $this->seed(DefaultCostCategorySeeder::class);
        $this->seed(RbacSeeder::class);
        $this->seed(BusinessTemplateSeeder::class);

        $this->owner = User::create([
            'name'              => 'Owner Feature Test',
            'email'             => 'owner.features@test.local',
            'phone'             => '6281298765401',
            'password'          => 'password',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);

        $this->staff = User::create([
            'name'              => 'Staff Kasir',
            'email'             => 'staff.features@test.local',
            'phone'             => '6281298765402',
            'password'          => 'password',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);

        $this->business = Business::create([
            'name'           => 'Bisnis Parameter Web Test',
            'currency'       => 'IDR',
            'business_scale' => Business::SCALE_UMKM,
        ]);

        $ownerRole = Role::where('slug', 'owner')->first();
        $this->business->users()->attach($this->owner->id, [
            'id'      => (string) Str::uuid(),
            'role'    => 'owner',
            'role_id' => $ownerRole?->id,
        ]);

        $staffRole = Role::where('slug', 'cashier')->first();
        $this->business->users()->attach($this->staff->id, [
            'id'      => (string) Str::uuid(),
            'role'    => 'staff',
            'role_id' => $staffRole?->id,
        ]);

        $this->owner->update(['active_business_id' => $this->business->id]);
        $this->staff->update(['active_business_id' => $this->business->id]);

        Context::setBusiness($this->business);
    }

    protected function tearDown(): void
    {
        Context::flush();
        parent::tearDown();
    }

    public function test_owner_can_view_settings_modules_tab_with_feature_parameters(): void
    {
        $response = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('settings.index', ['tab' => 'modules']));

        $response->assertOk();
        $response->assertSee('Profil Skala Operasional (1-Click Preset)');
        $response->assertSee('24 Parameter Granular Mandiri');
        $response->assertSee('Starter (Rintisan)');
        $response->assertSee('Growth (1-3 Cabang)');
        $response->assertSee('Corporate (Scale-Up)');
        $response->assertSee('Izinkan Transaksi Saat Stok Habis');
        $response->assertSee('Surat Jalan Resmi Supir (Delivery Order)');
    }

    public function test_owner_can_apply_scaling_tier_via_ajax(): void
    {
        $response = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('settings.features.apply-tier'), [
                'tier' => 'growth',
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'tier'    => 'growth',
        ]);

        $this->business->refresh();
        $this->assertTrue($this->business->isGrowth());
        $this->assertSame('growth', $this->business->getScalingTier());
        $this->assertTrue($this->business->isFeatureEnabled(FeatureRegistry::POS_REQUIRE_SUPERVISOR_PIN));
        $this->assertFalse($this->business->isFeatureEnabled(FeatureRegistry::LOGISTICS_DELIVERY_ORDER));

        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $this->business->id,
            'user_id'     => $this->owner->id,
            'action'      => 'scaling_tier_applied',
        ]);
    }

    public function test_owner_can_apply_corporate_scaling_tier_and_switch_back(): void
    {
        // 1. Upgrade to Corporate
        $responseCorp = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('settings.features.apply-tier'), [
                'tier' => 'corporate',
            ]);

        $responseCorp->assertOk();
        $this->business->refresh();
        $this->assertTrue($this->business->isCorporate());
        $this->assertTrue($this->business->isFeatureEnabled(FeatureRegistry::LOGISTICS_DELIVERY_ORDER));
        $this->assertTrue($this->business->isFeatureEnabled(FeatureRegistry::INVENTORY_MULTI_WAREHOUSE));

        // 2. Downgrade to Starter (Data must stay intact)
        $responseStarter = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('settings.features.apply-tier'), [
                'tier' => 'starter',
            ]);

        $responseStarter->assertOk();
        $this->business->refresh();
        $this->assertTrue($this->business->isUmkm());
        $this->assertTrue($this->business->isFeatureEnabled(FeatureRegistry::POS_ALLOW_NEGATIVE_STOCK));
        $this->assertFalse($this->business->isFeatureEnabled(FeatureRegistry::LOGISTICS_DELIVERY_ORDER));
    }

    public function test_owner_can_toggle_granular_feature_via_ajax(): void
    {
        $response = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('settings.features.toggle'), [
                'feature_key' => FeatureRegistry::VERTICAL_WORKSHOP_HISTORY,
                'enabled'     => true,
            ]);

        $response->assertOk();
        $response->assertJson([
            'success'     => true,
            'feature_key' => FeatureRegistry::VERTICAL_WORKSHOP_HISTORY,
            'enabled'     => true,
        ]);

        $this->business->refresh();
        $this->assertTrue($this->business->isFeatureEnabled(FeatureRegistry::VERTICAL_WORKSHOP_HISTORY));

        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $this->business->id,
            'user_id'     => $this->owner->id,
            'action'      => 'feature_parameter_toggled',
        ]);
    }

    public function test_non_owner_is_forbidden_from_applying_tier_or_toggling_features(): void
    {
        // 1. Attempt applying tier as staff
        $responseTier = $this->actingAs($this->staff)
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('settings.features.apply-tier'), [
                'tier' => 'corporate',
            ]);

        $responseTier->assertForbidden();

        // 2. Attempt toggling feature as staff
        $responseToggle = $this->actingAs($this->staff)
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('settings.features.toggle'), [
                'feature_key' => FeatureRegistry::LOGISTICS_DELIVERY_ORDER,
                'enabled'     => true,
            ]);

        $responseToggle->assertForbidden();
    }

    public function test_validation_errors_for_invalid_tier_or_feature_key(): void
    {
        $responseInvalidTier = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('settings.features.apply-tier'), [
                'tier' => 'invalid_universe_tier',
            ]);

        $responseInvalidTier->assertUnprocessable();
        $responseInvalidTier->assertJsonValidationErrors(['tier']);

        $responseInvalidKey = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('settings.features.toggle'), [
                'feature_key' => 'totally.unregistered.parameter',
                'enabled'     => true,
            ]);

        $responseInvalidKey->assertUnprocessable();
        $responseInvalidKey->assertJsonValidationErrors(['feature_key']);
    }
}

