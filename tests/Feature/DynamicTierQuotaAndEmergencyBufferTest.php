<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Billing\EntitlementService;
use App\Models\Admin;
use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\QuotaMonthlyUsage;
use App\Models\SystemSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DynamicTierQuotaAndEmergencyBufferTest extends TestCase
{
    use RefreshDatabase;

    public function test_entitlement_service_uses_code_constant_as_fallback(): void
    {
        $entitlement = app(EntitlementService::class);

        $freeProductLimit = $entitlement->getTierLimit(BusinessSubscription::TIER_FREE, 'product');
        $this->assertEquals(10, $freeProductLimit);

        $standardPosLimit = $entitlement->getTierLimit(BusinessSubscription::TIER_STANDARD, 'pos');
        $this->assertEquals(1000, $standardPosLimit);

        $premiumProductLimit = $entitlement->getTierLimit(BusinessSubscription::TIER_PREMIUM, 'product');
        $this->assertNull($premiumProductLimit);
    }

    public function test_admin_system_setting_dynamically_overrides_tier_limits(): void
    {
        $entitlement = app(EntitlementService::class);

        // Override Free product limit from 10 to 25
        SystemSetting::set('tier_limit_free_product', '25', 'billing');
        $this->assertEquals(25, $entitlement->getTierLimit(BusinessSubscription::TIER_FREE, 'product'));

        // Override Standard POS monthly limit from 1000 to 1500
        SystemSetting::set('tier_limit_standard_pos', '1500', 'billing');
        $this->assertEquals(1500, $entitlement->getTierLimit(BusinessSubscription::TIER_STANDARD, 'pos'));

        // Setting to -1 or 'unlimited' makes it unlimited (null)
        SystemSetting::set('tier_limit_standard_product', '-1', 'billing');
        $this->assertNull($entitlement->getTierLimit(BusinessSubscription::TIER_STANDARD, 'product'));
    }

    public function test_pos_emergency_buffer_allows_5_extra_transactions_before_hard_block(): void
    {
        $user = User::factory()->create();
        $business = Business::create(['name' => 'Toko Kasir Darurat', 'currency' => 'IDR']);
        $business->users()->attach($user->id, ['id' => (string) \Illuminate\Support\Str::uuid(), 'role' => 'owner']);

        // Default Free tier has limit of 30 POS transactions
        $entitlement = app(EntitlementService::class);
        $now = Carbon::today();

        // Set monthly usage to 30 (limit reached)
        QuotaMonthlyUsage::updateOrCreate(
            [
                'business_id' => $business->id,
                'resource_type' => QuotaMonthlyUsage::TYPE_POS,
                'year' => (int) $now->year,
                'month' => (int) $now->month,
            ],
            ['usage_count' => 30]
        );

        // Within emergency buffer: 30 transactions used, limit is 30, buffer allows up to 35
        $this->assertTrue($entitlement->canCreatePosTransactionThisMonth($business));
        $this->assertTrue($entitlement->isPosInEmergencyBuffer($business));
        $this->assertEquals(5, $entitlement->getPosRemainingEmergencyBuffer($business));

        // Use 4 buffer transactions (total 34 used)
        QuotaMonthlyUsage::where([
            'business_id' => $business->id,
            'resource_type' => QuotaMonthlyUsage::TYPE_POS,
            'year' => (int) $now->year,
            'month' => (int) $now->month,
        ])->update(['usage_count' => 34]);

        $this->assertTrue($entitlement->canCreatePosTransactionThisMonth($business));
        $this->assertTrue($entitlement->isPosInEmergencyBuffer($business));
        $this->assertEquals(1, $entitlement->getPosRemainingEmergencyBuffer($business));

        // Buffer fully exhausted (35 used >= 30 + 5)
        QuotaMonthlyUsage::where([
            'business_id' => $business->id,
            'resource_type' => QuotaMonthlyUsage::TYPE_POS,
            'year' => (int) $now->year,
            'month' => (int) $now->month,
        ])->update(['usage_count' => 35]);

        $this->assertFalse($entitlement->canCreatePosTransactionThisMonth($business));
        $this->assertFalse($entitlement->isPosInEmergencyBuffer($business));
        $this->assertEquals(0, $entitlement->getPosRemainingEmergencyBuffer($business));
    }

    public function test_get_all_tier_limits_returns_matrix_for_all_tiers(): void
    {
        $entitlement = app(EntitlementService::class);
        $matrix = $entitlement->getAllTierLimits();

        $this->assertArrayHasKey('free', $matrix);
        $this->assertArrayHasKey('standard', $matrix);
        $this->assertArrayHasKey('premium', $matrix);
        $this->assertArrayHasKey('prestige', $matrix);

        $this->assertArrayHasKey('product', $matrix['free']);
        $this->assertArrayHasKey('pos', $matrix['standard']);
    }

    public function test_admin_billing_packages_page_renders_complete_quota_matrix_for_all_tiers(): void
    {
        $admin = Admin::create([
            'name' => 'Super Admin',
            'email' => 'admin-matrix@test.local',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.billing-packages.index'));

        $response->assertOk();
        $response->assertSee('Konfigurasi Lengkap Batas Kuota Sumber Daya');
        $response->assertSee('name="tier_limit_free_product"', false);
        $response->assertSee('name="tier_limit_standard_pos"', false);
        $response->assertSee('name="tier_limit_premium_customer"', false);
        $response->assertSee('name="tier_limit_prestige_storage_gb"', false);
    }

    public function test_admin_can_update_all_resource_quotas_via_settings_endpoint(): void
    {
        $admin = Admin::create([
            'name' => 'Super Admin',
            'email' => 'admin-updater@test.local',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        $postData = [
            'tier_limit_free_product' => 45,
            'tier_limit_standard_pos' => 2000,
            'tier_limit_premium_pos' => -1,
            'tier_limit_prestige_storage_gb' => 100,
        ];

        $response = $this->actingAs($admin, 'admin')
            ->post(route('admin.settings.billing'), $postData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $entitlement = app(EntitlementService::class);
        $this->assertEquals(45, $entitlement->getTierLimit(BusinessSubscription::TIER_FREE, 'product'));
        $this->assertEquals(2000, $entitlement->getTierLimit(BusinessSubscription::TIER_STANDARD, 'pos'));
        $this->assertNull($entitlement->getTierLimit(BusinessSubscription::TIER_PREMIUM, 'pos'));
        $this->assertEquals(100, $entitlement->getTierLimit(BusinessSubscription::TIER_PRESTIGE, 'storage_gb'));
    }
}
