<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\AuditLog;
use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\SubscriptionPayment;
use App\Models\User;
use App\Services\Google2FAService;
use App\Support\Context;
use Database\Seeders\AdminRbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class AdminBusinessPlanGrantTest extends TestCase
{
    use RefreshDatabase;

    private Google2FAService $google2FA;
    private string $twoFactorSecret;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->seed(AdminRbacSeeder::class);
        $this->google2FA = new Google2FAService();
        $this->twoFactorSecret = $this->google2FA->generateSecret();
    }

    private function createAdminWith2FA(string $role = 'super_admin'): Admin
    {
        return Admin::create([
            'name' => 'Platform Superadmin',
            'email' => 'superadmin@cooca.id',
            'password' => Hash::make('password123'),
            'role' => $role,
            'two_factor_secret' => $this->twoFactorSecret,
            'two_factor_confirmed_at' => now(),
            'is_active' => true,
        ]);
    }

    private function createAdminWithout2FA(): Admin
    {
        return Admin::create([
            'name' => 'Admin Insecure',
            'email' => 'insecure@cooca.id',
            'password' => Hash::make('password123'),
            'role' => 'super_admin',
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
            'is_active' => true,
        ]);
    }

    public function test_admin_without_2fa_cannot_add_business(): void
    {
        $admin = $this->createAdminWithout2FA();

        $response = $this->actingAs($admin, 'admin')->post(route('admin.businesses.store'), [
            'name' => 'Toko Tanpa 2FA',
            'owner_mode' => 'new_user',
            'owner_name' => 'Pemilik Demo',
            'owner_email' => 'demo@tokotanpa2fa.com',
            'plan_code' => 'standard_monthly',
            'duration_type' => '30',
            'totp_code' => '123456',
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('businesses', ['name' => 'Toko Tanpa 2FA']);
    }

    public function test_admin_with_invalid_totp_cannot_add_business(): void
    {
        $admin = $this->createAdminWith2FA();

        $response = $this->actingAs($admin, 'admin')->post(route('admin.businesses.store'), [
            'name' => 'Toko Invalid TOTP',
            'owner_mode' => 'new_user',
            'owner_name' => 'Pemilik Demo',
            'owner_email' => 'demo@invalidtotp.com',
            'plan_code' => 'standard_monthly',
            'duration_type' => '30',
            'totp_code' => '000000',
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('businesses', ['name' => 'Toko Invalid TOTP']);
    }

    public function test_admin_with_valid_2fa_can_add_business_with_plan_and_duration_without_payment(): void
    {
        $admin = $this->createAdminWith2FA();
        $validCode = $this->google2FA->getCode($this->twoFactorSecret);

        $response = $this->actingAs($admin, 'admin')->post(route('admin.businesses.store'), [
            'name' => 'Kopi Sejahtera UMKM',
            'business_scale' => 'umkm',
            'owner_mode' => 'new_user',
            'owner_name' => 'Agus Salim',
            'owner_email' => 'agus@kopisejahtera.id',
            'owner_phone' => '081234567890',
            'plan_code' => 'premium_monthly',
            'duration_type' => '90',
            'admin_notes' => 'Pemberian paket complimentary binaan UMKM',
            'totp_code' => $validCode,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        /** @var Business $business */
        $business = Business::where('name', 'Kopi Sejahtera UMKM')->first();
        $this->assertNotNull($business);
        $this->assertTrue($business->is_active);

        // Check owner created and linked
        /** @var User $owner */
        $owner = User::where('email', 'agus@kopisejahtera.id')->first();
        $this->assertNotNull($owner);
        $this->assertTrue($business->users()->where('users.id', $owner->id)->exists());
        $this->assertEquals($business->id, $owner->active_business_id);

        // Check BusinessSubscription: active, price 0 (tanpa bayar), plan_code, ends_at approx 90 days
        /** @var BusinessSubscription $subscription */
        $subscription = $business->subscription;
        $this->assertNotNull($subscription);
        $this->assertEquals('premium_monthly', $subscription->plan_code);
        $this->assertEquals(0.0, (float) $subscription->price);
        $this->assertEquals(BusinessSubscription::STATUS_ACTIVE, $subscription->status);
        $this->assertNotNull($subscription->ends_at);
        $this->assertEquals(now()->addDays(90)->toDateString(), $subscription->ends_at->toDateString());

        // Check Complimentary SubscriptionPayment created
        $payment = SubscriptionPayment::where('business_id', $business->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals(SubscriptionPayment::STATUS_APPROVED, $payment->status);
        $this->assertEquals(0.0, (float) $payment->amount);
        $this->assertEquals(0.0, (float) $payment->total_payable);
        $this->assertEquals('admin_grant', $payment->payment_method);
        $this->assertEquals($admin->id, $payment->approved_by);

        // Check AuditLog recorded
        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $business->id,
            'action' => 'admin_business_create_with_plan',
        ]);
    }

    public function test_admin_can_add_business_with_lifetime_duration(): void
    {
        $admin = $this->createAdminWith2FA();
        $validCode = $this->google2FA->getCode($this->twoFactorSecret);

        $response = $this->actingAs($admin, 'admin')->post(route('admin.businesses.store'), [
            'name' => 'Resto Abadi Jaya',
            'business_scale' => 'umkm',
            'owner_mode' => 'new_user',
            'owner_name' => 'Siti Nurhaliza',
            'owner_email' => 'siti@abadi.com',
            'plan_code' => 'prestige_annual',
            'duration_type' => 'lifetime',
            'totp_code' => $validCode,
        ]);

        $response->assertSessionHas('success');

        /** @var Business $business */
        $business = Business::where('name', 'Resto Abadi Jaya')->first();
        $this->assertNotNull($business);

        $subscription = $business->subscription;
        $this->assertNotNull($subscription);
        $this->assertEquals('prestige_annual', $subscription->plan_code);
        $this->assertEquals(0.0, (float) $subscription->price);
        $this->assertNull($subscription->ends_at); // Lifetime has null ends_at
    }

    public function test_admin_without_2fa_cannot_grant_subscription(): void
    {
        $admin = $this->createAdminWithout2FA();
        $business = Business::create([
            'name' => 'Toko Existing',
            'currency' => 'IDR',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin, 'admin')->post(route('admin.businesses.grant-subscription', $business->id), [
            'plan_code' => 'core_monthly',
            'duration_type' => '30',
            'calculation_mode' => 'from_now',
            'totp_code' => '123456',
        ]);

        $response->assertSessionHas('error');
    }

    public function test_admin_with_invalid_totp_cannot_grant_subscription(): void
    {
        $admin = $this->createAdminWith2FA();
        $business = Business::create([
            'name' => 'Toko Existing 2',
            'currency' => 'IDR',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin, 'admin')->post(route('admin.businesses.grant-subscription', $business->id), [
            'plan_code' => 'core_monthly',
            'duration_type' => '30',
            'calculation_mode' => 'from_now',
            'totp_code' => '000000',
        ]);

        $response->assertSessionHas('error');
    }

    public function test_admin_with_valid_2fa_can_grant_subscription_with_custom_days_without_payment(): void
    {
        $admin = $this->createAdminWith2FA();
        $validCode = $this->google2FA->getCode($this->twoFactorSecret);

        $business = Business::create([
            'name' => 'Klinik Sentosa',
            'currency' => 'IDR',
            'is_active' => true,
        ]);

        $owner = User::create([
            'name' => 'Dokter Andi',
            'email' => 'andi@kliniksentosa.id',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);
        $business->users()->attach($owner->id, [
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'role' => 'owner',
        ]);

        // Initially on free plan
        BusinessSubscription::create([
            'business_id' => $business->id,
            'plan_code' => BusinessSubscription::PLAN_FREE,
            'price' => 0,
            'status' => BusinessSubscription::STATUS_ACTIVE,
            'starts_at' => now(),
            'ends_at' => null,
        ]);

        $response = $this->actingAs($admin, 'admin')->post(route('admin.businesses.grant-subscription', $business->id), [
            'plan_code' => 'prestige_annual',
            'duration_type' => 'custom',
            'custom_days' => 45,
            'calculation_mode' => 'from_now',
            'admin_notes' => 'Grant paket prestige 45 hari gratis untuk klinik binaan',
            'totp_code' => $validCode,
        ]);

        $response->assertRedirect(route('admin.businesses.show', $business->id));
        $response->assertSessionHas('success');

        /** @var BusinessSubscription $sub */
        $sub = $business->fresh()->subscription;
        $this->assertEquals('prestige_annual', $sub->plan_code);
        $this->assertEquals(0.0, (float) $sub->price);
        $this->assertEquals(BusinessSubscription::STATUS_ACTIVE, $sub->status);
        $this->assertEquals(now()->addDays(45)->toDateString(), $sub->ends_at->toDateString());

        // Check Complimentary SubscriptionPayment created
        $payment = SubscriptionPayment::where('business_id', $business->id)->latest()->first();
        $this->assertNotNull($payment);
        $this->assertEquals(SubscriptionPayment::STATUS_APPROVED, $payment->status);
        $this->assertEquals('admin_grant', $payment->payment_method);
        $this->assertEquals(0.0, (float) $payment->amount);
        $this->assertEquals($admin->id, $payment->approved_by);

        // Check AuditLog recorded
        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $business->id,
            'action' => 'admin_grant_subscription',
        ]);
    }
}
