<?php

declare(strict_types=1);

namespace Tests\Feature\Billing;

use App\Domain\Payment\TripayService;
use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\SubscriptionPayment;
use App\Models\SystemSetting;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\DefaultCostCategorySeeder;
use Database\Seeders\DefaultUnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SubscriptionPaymentExpirationTimezoneTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Business $business;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DefaultUnitSeeder::class);
        $this->seed(DefaultCostCategorySeeder::class);

        $this->user = User::factory()->create([
            'email' => 'owner.payment@cooca.test',
        ]);

        $this->business = Business::create([
            'name' => 'Toko Kopi Sejahtera',
            'slug' => 'toko-kopi-sejahtera',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'is_active' => true,
        ]);

        $this->business->users()->attach($this->user->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);
    }

    public function test_payment_has_twenty_four_hour_expiration_and_does_not_expire_after_fifteen_minutes(): void
    {
        $creationTime = Carbon::create(2026, 10, 30, 10, 0, 0, 'Asia/Jakarta');
        Carbon::setTestNow($creationTime);

        $payment = SubscriptionPayment::create([
            'business_id' => $this->business->id,
            'user_id' => $this->user->id,
            'order_number' => 'ORD-TEST-001',
            'plan_code' => BusinessSubscription::PLAN_STANDARD_MONTHLY,
            'cycle' => 'monthly',
            'amount' => 39000,
            'total_payable' => 39000,
            'payment_method' => SubscriptionPayment::METHOD_QRIS,
            'payment_gateway' => SubscriptionPayment::GATEWAY_TRIPAY,
            'status' => SubscriptionPayment::STATUS_PENDING,
        ]);

        // Default expiration should be 24 hours in Asia/Jakarta (WIB)
        $expiresAt = $payment->getExpiresAt();
        $this->assertNotNull($expiresAt);
        $this->assertEquals('Asia/Jakarta', $expiresAt->getTimezone()->getName());
        $this->assertEquals('2026-10-31 10:00:00', $expiresAt->format('Y-m-d H:i:s'));

        // At 16 minutes after creation: MUST NOT be expired!
        Carbon::setTestNow($creationTime->copy()->addMinutes(16));
        $payment->refresh();
        $this->assertFalse($payment->isExpired(), 'Payment was wrongly marked expired after 16 minutes!');

        // At 23 hours and 59 minutes: still active!
        Carbon::setTestNow($creationTime->copy()->addHours(23)->addMinutes(59));
        $this->assertFalse($payment->isExpired(), 'Payment should still be valid after 23h 59m!');

        // At 24 hours and 1 minute: properly expired!
        Carbon::setTestNow($creationTime->copy()->addHours(24)->addMinute());
        $this->assertTrue($payment->isExpired(), 'Payment should be expired after 24 hours!');

        Carbon::setTestNow();
    }

    public function test_tripay_gateway_expiration_is_honored_without_artificial_fifteen_minute_clamping(): void
    {
        $creationTime = Carbon::create(2026, 10, 30, 10, 0, 0, 'Asia/Jakarta');
        Carbon::setTestNow($creationTime);

        // TriPay returns an expiration timestamp (e.g. 24 hours from now)
        $tripayExpiryTimestamp = $creationTime->copy()->addHours(24)->timestamp;
        $gatewayExpiredAt = Carbon::createFromTimestamp($tripayExpiryTimestamp, 'Asia/Jakarta');

        $payment = SubscriptionPayment::create([
            'business_id' => $this->business->id,
            'user_id' => $this->user->id,
            'order_number' => 'ORD-TRIPAY-VA-002',
            'plan_code' => BusinessSubscription::PLAN_PREMIUM_MONTHLY,
            'cycle' => 'monthly',
            'amount' => 89000,
            'total_payable' => 89000,
            'payment_method' => 'bca_va',
            'payment_gateway' => SubscriptionPayment::GATEWAY_TRIPAY,
            'gateway_reference' => 'DEV-T12345678',
            'gateway_pay_code' => '1234567890123456',
            'gateway_expired_at' => $gatewayExpiredAt,
            'status' => SubscriptionPayment::STATUS_PENDING,
        ]);

        $expiresAt = $payment->getExpiresAt();
        $this->assertNotNull($expiresAt);
        // Ensure it is NOT clamped to 15 minutes!
        $this->assertEquals('2026-10-31 10:00:00', $expiresAt->format('Y-m-d H:i:s'));

        // Check at 1 hour after creation: MUST remain valid!
        Carbon::setTestNow($creationTime->copy()->addHour());
        $this->assertFalse($payment->isExpired(), 'TriPay Virtual Account was wrongly expired after 1 hour!');

        Carbon::setTestNow();
    }

    public function test_payment_show_page_does_not_cancel_pending_order_at_twenty_minutes(): void
    {
        $creationTime = Carbon::create(2026, 10, 30, 10, 0, 0, 'Asia/Jakarta');
        Carbon::setTestNow($creationTime);

        $payment = SubscriptionPayment::create([
            'business_id' => $this->business->id,
            'user_id' => $this->user->id,
            'order_number' => 'ORD-SHOW-003',
            'plan_code' => BusinessSubscription::PLAN_STANDARD_MONTHLY,
            'cycle' => 'monthly',
            'amount' => 39000,
            'total_payable' => 39000,
            'payment_method' => SubscriptionPayment::METHOD_QRIS,
            'payment_gateway' => SubscriptionPayment::GATEWAY_TRIPAY,
            'gateway_qr_url' => 'https://tripay.co.id/qr/test.png',
            'status' => SubscriptionPayment::STATUS_PENDING,
        ]);

        // Advance clock by 20 minutes (which previously triggered auto-cancellation!)
        Carbon::setTestNow($creationTime->copy()->addMinutes(20));

        $response = $this->actingAs($this->user)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('billing.payment.show', $payment));

        $response->assertOk();

        // Must still be pending and active, NOT cancelled or expired
        $payment->refresh();
        $this->assertEquals(SubscriptionPayment::STATUS_PENDING, $payment->status);
        $this->assertFalse($payment->isExpired());
        $this->assertFalse($payment->isCancelled());

        // Verify Alpine state is not expired/cancelled
        $response->assertSee('WIB');
        $response->assertSee('isExpired: false', false);
        $response->assertSee('isCancelled: false', false);
        $response->assertSee("orderStatus: 'pending'", false);

        Carbon::setTestNow();
    }

    public function test_timezone_consistency_between_utc_server_and_wib_comparison(): void
    {
        // Simulate server where now() might be UTC
        $nowWib = Carbon::create(2026, 10, 30, 11, 0, 0, 'Asia/Jakarta');
        Carbon::setTestNow($nowWib);

        $payment = SubscriptionPayment::create([
            'business_id' => $this->business->id,
            'user_id' => $this->user->id,
            'order_number' => 'ORD-TZ-004',
            'plan_code' => BusinessSubscription::PLAN_STANDARD_MONTHLY,
            'status' => SubscriptionPayment::STATUS_PENDING,
            'amount' => 39000,
            'total_payable' => 39000,
            'payment_method' => SubscriptionPayment::METHOD_QRIS,
        ]);

        $this->assertEquals('Asia/Jakarta', $payment->created_at_wib->getTimezone()->getName());
        $this->assertEquals('Asia/Jakarta', $payment->expires_at_wib->getTimezone()->getName());
        $this->assertFalse($payment->isExpired());

        Carbon::setTestNow();
    }
}
