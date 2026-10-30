<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Billing\EntitlementService;
use App\Models\Admin;
use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

final class BillingLimitsAndAdminFeatureGatesTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Business $business;
    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::create([
            'name' => 'Owner Test Cooca',
            'email' => 'owner.test@cooca.id',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
            'phone' => '6281234567890',
        ]);

        $this->business = Business::create([
            'name' => 'Warung Test Dynamic Limits',
            'slug' => 'warung-test-dynamic-limits',
            'industry_category' => 'retail',
        ]);

        $this->business->users()->attach($this->owner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->owner->update(['active_business_id' => $this->business->id]);

        $this->admin = Admin::factory()->create([
            'name' => 'Super Administrator Cooca',
            'email' => 'admin.test@cooca.id',
            'password' => Hash::make('secret123'),
            'role' => 'super_admin',
            'is_active' => true,
        ]);
    }

    public function test_limits_page_renders_dynamic_limits_matching_entitlement_service(): void
    {
        $entitlementService = app(EntitlementService::class);
        $allLimits = $entitlementService->getAllTierLimits();

        $freeProduct = (int) ($allLimits['free']['product'] ?? 10);
        $freePos = (int) ($allLimits['free']['pos'] ?? 30);
        $standardProduct = (int) ($allLimits['standard']['product'] ?? 100);

        $response = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('billing.limits'));

        $response->assertOk();
        $response->assertSee("{$freeProduct} Produk");
        $response->assertSee("{$freePos} Transaksi Kasir POS");
        $response->assertSee("{$standardProduct} Produk");

        // Verify Anti-Hardcoding: Modifying SystemSetting directly alters rendered limits
        SystemSetting::set('tier_limit_free_product', '77', 'billing');
        SystemSetting::set('tier_limit_standard_product', '250', 'billing');

        $updatedResponse = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('billing.limits'));

        $updatedResponse->assertOk();
        $updatedResponse->assertSee("77 Produk");
        $updatedResponse->assertSee("250 Produk");
    }

    public function test_admin_billing_packages_renders_feature_lock_matrix_and_quota_inputs(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.billing-packages.index'));

        $response->assertOk();

        // 1. Quota limit inputs
        $response->assertSee('tier_limit_free_product');
        $response->assertSee('tier_limit_standard_pos');
        $response->assertSee('tier_limit_premium_storage_gb');

        // 2. Feature Access & Lock matrix checkboxes and submission flag
        $response->assertSee('features_submitted');
        $response->assertSee('tier_feature_standard_transfer_stock');
        $response->assertSee('tier_feature_premium_kds');
        $response->assertSee('tier_feature_prestige_pph21');
        $response->assertSee('Matriks Hak Akses &amp; Kunci Fitur per Paket', false);
    }

    public function test_admin_can_update_tier_limits_and_it_reflects_in_system_and_limits_page(): void
    {
        $payload = [
            'subscription_price_standard_monthly' => '39000',
            'subscription_price_standard_annual' => '390000',
            'tier_limit_free_product' => '85',
            'tier_limit_standard_pos' => '3500',
            'tier_limit_free_storage_gb' => '2',
        ];

        $postResponse = $this->actingAs($this->admin, 'admin')
            ->from(route('admin.billing-packages.index'))
            ->post(route('admin.settings.billing'), $payload);

        $postResponse->assertRedirect(route('admin.billing-packages.index'));
        $postResponse->assertSessionHas('success');

        // Verify SystemSetting persistence
        $this->assertEquals('85', SystemSetting::get('tier_limit_free_product'));
        $this->assertEquals('3500', SystemSetting::get('tier_limit_standard_pos'));
        $this->assertEquals('2', SystemSetting::get('tier_limit_free_storage_gb'));

        // Verify EntitlementService uses the new values
        $entitlementService = app(EntitlementService::class);
        $this->assertEquals(85, $entitlementService->getTierLimit('free', 'product'));
        $this->assertEquals(3500, $entitlementService->getTierLimit('standard', 'pos'));
        $this->assertEquals(2, $entitlementService->getTierLimit('free', 'storage_gb'));

        // Verify owner sees the new values on limits page
        // Log out admin guard and clear session to avoid cross-guard pollution
        auth()->guard('admin')->logout();
        auth()->guard('web')->logout();
        $this->app['session']->flush();

        $ownerResponse = $this->actingAs($this->owner, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('billing.limits'));

        $ownerResponse->assertOk();
        $ownerResponse->assertSee('85 Produk');
        $ownerResponse->assertSee('3.500 Kasir POS');
        $ownerResponse->assertSee('2 GB');
    }

    public function test_admin_can_toggle_feature_lock_and_it_controls_entitlement_and_middleware(): void
    {
        $entitlementService = app(EntitlementService::class);

        // By default, Standard tier cannot transfer stock
        $standardBusiness = Business::create([
            'name' => 'Cabang Standard',
            'slug' => 'cabang-standard',
            'industry_category' => 'retail',
        ]);
        BusinessSubscription::create([
            'business_id' => $standardBusiness->id,
            'plan_code' => BusinessSubscription::PLAN_STANDARD_MONTHLY,
            'status' => BusinessSubscription::STATUS_ACTIVE,
            'starts_at' => now(),
            'price' => 39000,
        ]);

        $this->assertFalse($entitlementService->canTransferStock($standardBusiness));

        // 1. Admin toggles transfer_stock ON for standard tier
        $payload = [
            'features_submitted' => '1',
            'tier_feature_standard_transfer_stock' => '1',
            'tier_feature_premium_kds' => '1',
        ];

        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.settings.billing'), $payload)
            ->assertRedirect();

        $this->assertEquals('1', SystemSetting::get('tier_feature_standard_transfer_stock'));
        $this->assertTrue($entitlementService->canTransferStock($standardBusiness));

        // 2. Admin locks KDS for premium tier (by omitting checkbox from payload)
        $premiumBusiness = Business::create([
            'name' => 'Resto Premium',
            'slug' => 'resto-premium',
            'industry_category' => 'fnb_resto',
        ]);
        BusinessSubscription::create([
            'business_id' => $premiumBusiness->id,
            'plan_code' => BusinessSubscription::PLAN_PREMIUM_MONTHLY,
            'status' => BusinessSubscription::STATUS_ACTIVE,
            'starts_at' => now(),
            'price' => 89000,
        ]);

        // Submit form without tier_feature_premium_kds -> becomes '0'
        $lockPayload = [
            'features_submitted' => '1',
            'tier_feature_standard_transfer_stock' => '1',
            // tier_feature_premium_kds omitted
        ];

        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.settings.billing'), $lockPayload)
            ->assertRedirect();

        $this->assertEquals('0', SystemSetting::get('tier_feature_premium_kds'));
        $this->assertFalse($entitlementService->canUseKds($premiumBusiness));
    }

    public function test_feature_locks_render_dynamically_in_limits_view(): void
    {
        // 1. Set standard transfer_stock to enabled in SystemSetting
        SystemSetting::set('tier_feature_standard_transfer_stock', '1', 'billing');
        SystemSetting::set('tier_feature_free_ai', '1', 'billing');

        $response = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('billing.limits'));

        $response->assertOk();

        // Standard column should now show Bebas Transfer instead of Terkunci
        $response->assertSee('Akses Fitur AI &amp; Gateway MCP Aktif', false);
        $response->assertSee('Bebas Transfer');
    }
}
