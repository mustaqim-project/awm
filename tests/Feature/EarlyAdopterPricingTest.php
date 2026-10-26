<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Billing\EarlyAdopterPricingService;
use App\Domain\Billing\EntitlementService;
use App\Models\Admin;
use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\SubscriptionPriceHistory;
use App\Models\SubscriptionPricingBatch;
use App\Models\SubscriptionPricingProgram;
use Carbon\Carbon;
use Database\Seeders\AdminRbacSeeder;
use Database\Seeders\DefaultCostCategorySeeder;
use Database\Seeders\DefaultUnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class EarlyAdopterPricingTest extends TestCase
{
    use RefreshDatabase;

    private EarlyAdopterPricingService $pricingService;
    private EntitlementService $entitlementService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DefaultUnitSeeder::class);
        $this->seed(DefaultCostCategorySeeder::class);
        $this->seed(AdminRbacSeeder::class);

        $this->pricingService = app(EarlyAdopterPricingService::class);
        $this->entitlementService = app(EntitlementService::class);
    }

    /**
     * Test 1: Base Pricing Normal & Promo Badge Hemat 2 Bulan.
     */
    public function test_base_pricing_defaults_and_annual_discount_badge(): void
    {
        // 1. Standard Plan
        $this->assertEquals(39000.0, $this->pricingService->getBasePrice('standard', 'monthly'));
        $this->assertEquals(390000.0, $this->pricingService->getBasePrice('standard', 'annual'));

        // 2. Premium Plan
        $this->assertEquals(89000.0, $this->pricingService->getBasePrice('premium', 'monthly'));
        $this->assertEquals(890000.0, $this->pricingService->getBasePrice('premium', 'annual'));

        // 3. Prestige Plan
        $this->assertEquals(199000.0, $this->pricingService->getBasePrice('prestige', 'monthly'));
        $this->assertEquals(1990000.0, $this->pricingService->getBasePrice('prestige', 'annual'));

        // 4. Annual Promo Badge
        $badge = $this->entitlementService->getAnnualDiscountBadge();
        $this->assertEquals('Hemat 2 Bulan', $badge);

        // 5. EntitlementService returns base pricing when no batch is active
        $this->assertEquals(39000.0, $this->entitlementService->getTierPrice('standard', 'monthly'));
        $this->assertEquals(390000.0, $this->entitlementService->getTierPrice('standard', 'annual'));
    }

    /**
     * Test 2: Early Adopter Batch Pricing & Atomic Quota Reservation.
     */
    public function test_early_adopter_batch_pricing_calculation_and_atomic_quota_reservation(): void
    {
        $program = SubscriptionPricingProgram::create([
            'name' => 'Early Adopter 2026',
            'code' => 'ea-2026',
            'is_active' => true,
        ]);

        $batch = SubscriptionPricingBatch::create([
            'program_id' => $program->id,
            'name' => 'Batch 1 Perdana',
            'batch_number' => 1,
            'status' => SubscriptionPricingBatch::STATUS_ACTIVE,
            'trigger_mode' => SubscriptionPricingBatch::MODE_HYBRID,
            'quota_limit' => 100,
            'quota_used' => 0,
            'standard_monthly_price' => 29000,
            'standard_annual_price' => 290000,
            'premium_monthly_price' => 69000,
            'premium_annual_price' => 690000,
            'prestige_monthly_price' => 159000,
            'prestige_annual_price' => 1590000,
            'annual_increase_percentage' => 10.00,
            'annual_increase_basis' => 'subscription_anniversary',
            'rounding_mode' => 'none',
            'fallback_behavior' => 'base_pricing',
        ]);

        // Pricing calculation should fetch batch prices
        $pricing = $this->pricingService->calculatePrice('standard', 'monthly');
        $this->assertTrue($pricing['is_early_adopter']);
        $this->assertEquals(29000.0, $pricing['price']);
        $this->assertEquals(39000.0, $pricing['normal_price']);
        $this->assertEquals(10000.0, $pricing['savings']);
        $this->assertEquals($batch->id, $pricing['batch_id']);

        // Atomic quota reservation
        $reserved = $this->pricingService->reserveQuotaAtomically($batch);
        $this->assertTrue($reserved);
        $batch->refresh();
        $this->assertEquals(1, $batch->quota_used);
    }

    /**
     * Test 3: Backend & Controller strictly reject annual increase above 10.00%.
     */
    public function test_backend_and_controller_strictly_reject_annual_increase_above_ten_percent(): void
    {
        // 1. Backend Service throws InvalidArgumentException
        $this->expectException(InvalidArgumentException::class);
        $this->pricingService->validateAnnualIncreasePercentage(10.5);

        // 2. Controller Validation Test
        $admin = Admin::factory()->create([
            'email' => 'superadmin@cooca.test',
            'role' => 'super_admin',
        ]);

        $program = SubscriptionPricingProgram::create([
            'name' => 'Program Uji Validasi',
            'code' => 'prog-val-test',
            'is_active' => true,
        ]);

        $invalidPayload = [
            'name' => 'Batch Kenaikan Berlebih',
            'status' => 'draft',
            'trigger_mode' => 'quota',
            'quota_limit' => 50,
            'fallback_behavior' => 'base_pricing',
            'standard_monthly_price' => 29000,
            'standard_annual_price' => 290000,
            'annual_increase_percentage' => 15.00, // VIOLATION: > 10%
            'annual_increase_basis' => 'subscription_anniversary',
            'rounding_mode' => 'none',
            'resubscribe_policy' => 'grace_period',
            'resubscribe_grace_days' => 7,
            'upgrade_downgrade_policy' => 'inherit_active_batch',
        ];

        $response = $this->actingAs($admin, 'admin')
            ->post(route('admin.pricing-programs.batches.store', $program), $invalidPayload);

        $response->assertSessionHasErrors(['annual_increase_percentage']);
    }

    /**
     * Test 4: Automatic Batch Transition When Quota Exhausted.
     */
    public function test_automatic_batch_transition_when_quota_exhausted(): void
    {
        $program = SubscriptionPricingProgram::create([
            'name' => 'Program Multi Batch',
            'code' => 'prog-multi-batch',
            'is_active' => true,
        ]);

        // Batch 2 (Next batch)
        $batch2 = SubscriptionPricingBatch::create([
            'program_id' => $program->id,
            'name' => 'Batch 2 Lanjutan',
            'batch_number' => 2,
            'status' => SubscriptionPricingBatch::STATUS_DRAFT,
            'trigger_mode' => SubscriptionPricingBatch::MODE_QUOTA,
            'quota_limit' => 500,
            'quota_used' => 0,
            'standard_monthly_price' => 35000,
            'standard_annual_price' => 350000,
            'annual_increase_percentage' => 10.00,
            'annual_increase_basis' => 'subscription_anniversary',
            'rounding_mode' => 'none',
            'fallback_behavior' => 'base_pricing',
        ]);

        // Batch 1 (Active, quota limit = 2)
        $batch1 = SubscriptionPricingBatch::create([
            'program_id' => $program->id,
            'name' => 'Batch 1 Terbatas',
            'batch_number' => 1,
            'status' => SubscriptionPricingBatch::STATUS_ACTIVE,
            'trigger_mode' => SubscriptionPricingBatch::MODE_QUOTA,
            'quota_limit' => 2,
            'quota_used' => 1,
            'next_batch_id' => $batch2->id,
            'standard_monthly_price' => 29000,
            'standard_annual_price' => 290000,
            'annual_increase_percentage' => 10.00,
            'annual_increase_basis' => 'subscription_anniversary',
            'rounding_mode' => 'none',
            'fallback_behavior' => 'next_batch',
        ]);

        // Fill the last slot of Batch 1
        $this->pricingService->reserveQuotaAtomically($batch1);
        $batch1->refresh();
        $this->assertEquals(2, $batch1->quota_used);
        $this->assertTrue($batch1->isQuotaExceeded());

        // Trigger transition
        $activatedBatch = $this->pricingService->transitionBatch($batch1);
        $batch1->refresh();
        $batch2->refresh();

        $this->assertEquals(SubscriptionPricingBatch::STATUS_COMPLETED, $batch1->status);
        $this->assertEquals(SubscriptionPricingBatch::STATUS_ACTIVE, $batch2->status);
        $this->assertNotNull($activatedBatch);
        $this->assertEquals($batch2->id, $activatedBatch->id);

        // Next price calculation should now use Batch 2 rate (Rp 35.000)
        $pricing = $this->pricingService->calculatePrice('standard', 'monthly');
        $this->assertEquals(35000.0, $pricing['price']);
        $this->assertEquals($batch2->id, $pricing['batch_id']);
    }

    /**
     * Test 5: Customer Price Locking and Grandfathering Rate Protection.
     */
    public function test_customer_price_locking_and_grandfathering_rate_protection(): void
    {
        $business = Business::create([
            'name' => 'Toko Kopi Senja',
            'slug' => 'toko-kopi-senja-' . uniqid(),
            'is_active' => true,
        ]);

        $program = SubscriptionPricingProgram::create([
            'name' => 'Program Pelanggan Setia',
            'code' => 'prog-setia',
            'is_active' => true,
        ]);

        $batch1 = SubscriptionPricingBatch::create([
            'program_id' => $program->id,
            'name' => 'Batch 1 Awal',
            'batch_number' => 1,
            'status' => SubscriptionPricingBatch::STATUS_COMPLETED, // Batch has ended
            'trigger_mode' => SubscriptionPricingBatch::MODE_QUOTA,
            'quota_limit' => 10,
            'quota_used' => 10,
            'standard_monthly_price' => 29000,
            'standard_annual_price' => 290000,
            'annual_increase_percentage' => 10.00,
            'annual_increase_basis' => 'subscription_anniversary',
            'rounding_mode' => 'none',
            'fallback_behavior' => 'base_pricing',
        ]);

        // Customer already locked into Batch 1 rate
        $subscription = BusinessSubscription::create([
            'business_id' => $business->id,
            'plan_code' => BusinessSubscription::TIER_STANDARD,
            'price' => 29000,
            'billing_cycle' => 'monthly',
            'status' => BusinessSubscription::STATUS_ACTIVE,
            'starts_at' => Carbon::now()->subMonths(6),
            'ends_at' => Carbon::now()->addDays(5),
            'pricing_program_id' => $program->id,
            'pricing_batch_id' => $batch1->id,
            'locked_price_monthly' => 29000,
            'locked_price_annual' => 290000,
            'is_price_lock_eligible' => true,
            'annual_increase_percentage' => 10.00,
            'annual_increase_basis' => 'subscription_anniversary',
            'price_locked_at' => Carbon::now()->subMonths(6),
        ]);

        // Even though Batch 1 is completed and public price is base normal (Rp 39.000),
        // calculatePrice for this existing business MUST return locked price Rp 29.000
        $pricing = $this->pricingService->calculatePrice('standard', 'monthly', $business);

        $this->assertTrue($pricing['is_price_locked']);
        $this->assertEquals(29000.0, $pricing['price']);
        $this->assertEquals('locked_price', $pricing['source']);
        $this->assertEquals(10000.0, $pricing['savings']);
    }

    /**
     * Test 6: Annual Price Escalation is accurate, idempotent, and recorded in audit log.
     */
    public function test_annual_price_escalation_command_is_accurate_and_idempotent(): void
    {
        Carbon::setTestNow('2026-10-26 10:00:00');

        $business = Business::create([
            'name' => 'Resto Berkah',
            'slug' => 'resto-berkah-' . uniqid(),
            'is_active' => true,
        ]);

        $program = SubscriptionPricingProgram::create([
            'name' => 'Program Eskalasi',
            'code' => 'prog-eskalasi',
            'is_active' => true,
        ]);

        $batch = SubscriptionPricingBatch::create([
            'program_id' => $program->id,
            'name' => 'Batch 10 Persen',
            'batch_number' => 1,
            'status' => SubscriptionPricingBatch::STATUS_ACTIVE,
            'trigger_mode' => SubscriptionPricingBatch::MODE_QUOTA,
            'quota_limit' => 100,
            'quota_used' => 1,
            'standard_monthly_price' => 29000,
            'standard_annual_price' => 290000,
            'annual_increase_percentage' => 10.00, // 10%
            'annual_increase_basis' => 'subscription_anniversary',
            'rounding_mode' => 'none',
            'fallback_behavior' => 'base_pricing',
        ]);

        // Customer due for anniversary increase today
        $subscription = BusinessSubscription::create([
            'business_id' => $business->id,
            'plan_code' => BusinessSubscription::TIER_STANDARD,
            'price' => 29000,
            'billing_cycle' => 'monthly',
            'status' => BusinessSubscription::STATUS_ACTIVE,
            'starts_at' => Carbon::parse('2025-10-26'),
            'ends_at' => Carbon::parse('2026-11-26'),
            'pricing_program_id' => $program->id,
            'pricing_batch_id' => $batch->id,
            'locked_price_monthly' => 29000,
            'locked_price_annual' => 290000,
            'is_price_lock_eligible' => true,
            'annual_increase_percentage' => 10.00,
            'annual_increase_basis' => 'subscription_anniversary',
            'next_price_increase_at' => Carbon::parse('2026-10-26'),
            'last_price_increase_at' => null,
        ]);

        // 1. Run escalation command
        $this->artisan('cooca:apply-annual-price-escalation')
            ->expectsOutputToContain('Eskalasi berhasil! 1 langganan telah diperbarui harganya.')
            ->assertSuccessful();

        $subscription->refresh();

        // 29.000 + 10% = 31.900
        $this->assertEquals(31900.0, (float) $subscription->price);
        $this->assertEquals('2026-10-26', $subscription->last_price_increase_at->toDateString());
        $this->assertEquals('2027-10-26', $subscription->next_price_increase_at->toDateString());

        // Verify audit history entry
        $history = SubscriptionPriceHistory::where('business_subscription_id', $subscription->id)->first();
        $this->assertNotNull($history);
        $this->assertEquals(SubscriptionPriceHistory::EVENT_ANNUAL_ESCALATION, $history->event_type);
        $this->assertEquals(29000.0, (float) $history->previous_price);
        $this->assertEquals(31900.0, (float) $history->new_price);
        $this->assertEquals(10.00, (float) $history->increase_percentage);

        // 2. Run escalation command a SECOND time on same day (Testing Idempotency)
        $this->artisan('cooca:apply-annual-price-escalation')
            ->expectsOutputToContain('Tidak ada langganan yang jatuh tempo eskalasi harga tahunan hari ini.')
            ->assertSuccessful();

        // Price MUST NOT increase again!
        $subscription->refresh();
        $this->assertEquals(31900.0, (float) $subscription->price);

        Carbon::setTestNow();
    }

    /**
     * Test 7: Admin RBAC Permission Protection.
     */
    public function test_admin_rbac_permissions_for_pricing_programs(): void
    {
        // 1. Unprivileged Admin gets 403 Forbidden
        $unprivilegedAdmin = Admin::factory()->create([
            'email' => 'staff@cooca.test',
            'role' => 'support_agent',
        ]);

        $response = $this->actingAs($unprivilegedAdmin, 'admin')
            ->get(route('admin.pricing-programs.index'));
        $response->assertForbidden();

        // 2. Finance Admin or Super Admin gets 200 OK
        $financeAdmin = Admin::factory()->create([
            'email' => 'finance@cooca.test',
            'role' => 'finance_admin',
        ]);

        $response = $this->actingAs($financeAdmin, 'admin')
            ->get(route('admin.pricing-programs.index'));
        $response->assertOk();
        $response->assertSee('Early Adopter');
    }
}
