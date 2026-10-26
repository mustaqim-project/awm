<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\WhatsApp\AdminWhatsAppService;
use App\Mail\UserOtpMail;
use App\Models\Business;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Mockery;
use Tests\TestCase;

final class OtpWabaEmailFallbackTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->seed(\Database\Seeders\DefaultUnitSeeder::class);
        $this->seed(\Database\Seeders\DefaultCostCategorySeeder::class);
        $this->seed(\Database\Seeders\RbacSeeder::class);
        $this->seed(\Database\Seeders\BusinessTemplateSeeder::class);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_auth_otp_falls_back_to_email_when_waba_fails(): void
    {
        Mail::fake();

        // Mock AdminWhatsAppService to simulate WABA failure
        $mockWa = Mockery::mock(AdminWhatsAppService::class);
        $mockWa->shouldReceive('sendOtp')
            ->once()
            ->andReturn([
                'success' => false,
                'error'   => 'Meta Cloud API 500 Network Error / Rate Limit',
            ]);
        $this->app->instance(AdminWhatsAppService::class, $mockWa);

        $business = Business::create([
            'name' => 'Fallback Test Biz',
            'status' => 'active',
            'business_scale' => 'umkm',
            'template_code' => 'retail_reseller',
        ]);

        $user = User::create([
            'name' => 'Fallback Owner',
            'email' => 'owner.fallback@cooca.test',
            'password' => Hash::make('password123'),
            'phone' => '6281234567890',
            'phone_verified_at' => null,
            'active_business_id' => $business->id,
        ]);
        $business->users()->attach($user->id, ['role' => 'owner']);

        $response = $this->actingAs($user, 'web')
            ->get(route('auth.otp'));

        $response->assertStatus(200);

        // Verify UserOtpMail was sent to the user's email
        Mail::assertSent(UserOtpMail::class, function (UserOtpMail $mail) use ($user) {
            return $mail->hasTo($user->email)
                && $mail->isFallbackFromWaba === true
                && strlen($mail->otpCode) === 6;
        });

        // Verify session challenge stores channel as email
        $challenge = session('auth_wa_otp_challenge');
        $this->assertIsArray($challenge);
        $this->assertSame('email', $challenge['channel']);
        $this->assertNotNull($challenge['otp_hash']);

        // Verify page content shows fallback alert
        $response->assertSee('Pengiriman via WhatsApp sedang mengalami kendala jaringan.');
        $response->assertSee('@cooca.test');
    }

    public function test_auth_otp_resend_falls_back_to_email_when_waba_fails(): void
    {
        Mail::fake();

        $mockWa = Mockery::mock(AdminWhatsAppService::class);
        $mockWa->shouldReceive('sendOtp')
            ->once()
            ->andReturn([
                'success' => false,
                'error'   => 'WhatsApp disconnected',
            ]);
        $this->app->instance(AdminWhatsAppService::class, $mockWa);

        $business = Business::create([
            'name' => 'Resend Test Biz',
            'status' => 'active',
            'business_scale' => 'umkm',
            'template_code' => 'retail_reseller',
        ]);

        $user = User::create([
            'name' => 'Resend Owner',
            'email' => 'resend.owner@cooca.test',
            'password' => Hash::make('password123'),
            'phone' => '6289876543210',
            'phone_verified_at' => null,
            'active_business_id' => $business->id,
        ]);
        $business->users()->attach($user->id, ['role' => 'owner']);

        $existingChallenge = [
            'user_id' => $user->id,
            'phone' => '6289876543210',
            'email' => $user->email,
            'otp_hash' => Hash::make('111111'),
            'expires_at' => now()->addMinutes(10)->timestamp,
            'attempts' => 0,
            'last_sent_at' => now()->subSeconds(70)->timestamp,
        ];

        $response = $this->actingAs($user, 'web')
            ->withSession(['auth_wa_otp_challenge' => $existingChallenge])
            ->post(route('auth.otp.resend'));

        $response->assertSessionHas('status');

        Mail::assertSent(UserOtpMail::class, function (UserOtpMail $mail) use ($user) {
            return $mail->hasTo($user->email)
                && $mail->isFallbackFromWaba === true;
        });

        $challenge = session('auth_wa_otp_challenge');
        $this->assertSame('email', $challenge['channel']);
    }

    public function test_register_falls_back_to_email_when_waba_fails(): void
    {
        Mail::fake();

        $mockWa = Mockery::mock(AdminWhatsAppService::class);
        $mockWa->shouldReceive('sendOtp')
            ->once()
            ->andReturn([
                'success' => false,
                'error'   => 'Meta Template cooca_otp not approved',
            ]);
        $this->app->instance(AdminWhatsAppService::class, $mockWa);

        $payload = [
            'name' => 'Budi Santoso',
            'email' => 'budi.santoso@bisnis.test',
            'phone' => '081233445566',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'business_name' => 'Toko Maju Terus',
            'business_scale' => 'umkm',
            'template_code' => 'fnb_resto',
        ];

        $response = $this->post('/register', $payload);

        $response->assertRedirect(route('register.verify'));

        Mail::assertSent(UserOtpMail::class, function (UserOtpMail $mail) use ($payload) {
            return $mail->hasTo($payload['email'])
                && $mail->isFallbackFromWaba === true
                && strlen($mail->otpCode) === 6;
        });

        $pending = session('pending_registration');
        $this->assertIsArray($pending);
        $this->assertSame('email', $pending['channel']);
        $this->assertNotNull($pending['otp_hash']);
    }

    public function test_register_resend_falls_back_to_email_when_waba_fails(): void
    {
        Mail::fake();

        $mockWa = Mockery::mock(AdminWhatsAppService::class);
        $mockWa->shouldReceive('sendOtp')
            ->once()
            ->andReturn([
                'success' => false,
                'error'   => 'Temporary WABA Outage',
            ]);
        $this->app->instance(AdminWhatsAppService::class, $mockWa);

        $pending = [
            'name' => 'Siti Nurhaliza',
            'email' => 'siti@fashion.test',
            'phone' => '6287711223344',
            'business_name' => 'Siti Fashion Hijab',
            'business_scale' => 'micro',
            'template_code' => 'fashion_apparel',
            'disabled_modules' => [],
            'custom_modules' => false,
            'password' => Hash::make('SecretPass123'),
            'otp_hash' => Hash::make('555555'),
            'expires_at' => now()->addMinutes(10)->timestamp,
            'attempts' => 0,
            'last_sent_at' => now()->subSeconds(70)->timestamp,
        ];

        $response = $this->withSession(['pending_registration' => $pending])
            ->post(route('register.verify.resend'));

        $response->assertSessionHas('status');

        Mail::assertSent(UserOtpMail::class, function (UserOtpMail $mail) use ($pending) {
            return $mail->hasTo($pending['email'])
                && $mail->isFallbackFromWaba === true;
        });

        $updatedPending = session('pending_registration');
        $this->assertSame('email', $updatedPending['channel']);
    }

    public function test_google_registration_falls_back_to_email_when_waba_fails(): void
    {
        Mail::fake();

        $mockWa = Mockery::mock(AdminWhatsAppService::class);
        $mockWa->shouldReceive('sendOtp')
            ->once()
            ->andReturn([
                'success' => false,
                'error'   => 'Meta API unreachable',
            ]);
        $this->app->instance(AdminWhatsAppService::class, $mockWa);

        $googlePending = [
            'google_id' => 'google-user-998877',
            'email' => 'google.owner@company.test',
            'name' => 'Google Owner User',
            'avatar_url' => 'https://example.com/avatar.jpg',
        ];

        $payload = [
            'phone' => '081299887766',
            'business_name' => 'Google Bakery Store',
            'business_scale' => 'umkm',
            'template_code' => 'fnb_bakery',
        ];

        $response = $this->withSession(['pending_google_registration' => $googlePending])
            ->post(route('register.google.submit'), $payload);

        $response->assertRedirect(route('register.verify'));

        Mail::assertSent(UserOtpMail::class, function (UserOtpMail $mail) use ($googlePending) {
            return $mail->hasTo($googlePending['email'])
                && $mail->isFallbackFromWaba === true
                && strlen($mail->otpCode) === 6;
        });

        $pending = session('pending_registration');
        $this->assertIsArray($pending);
        $this->assertSame('email', $pending['channel']);
    }

    public function test_profile_phone_change_falls_back_to_email_when_waba_fails(): void
    {
        Mail::fake();

        $mockWa = Mockery::mock(AdminWhatsAppService::class);
        $mockWa->shouldReceive('sendOtp')
            ->once()
            ->andReturn([
                'success' => false,
                'error'   => 'WABA Gateway Timeout',
            ]);
        $this->app->instance(AdminWhatsAppService::class, $mockWa);

        $business = Business::create([
            'name' => 'Profile Change Test Biz',
            'status' => 'active',
            'business_scale' => 'umkm',
            'template_code' => 'fnb_resto',
        ]);

        $user = User::create([
            'name' => 'Profile Owner',
            'email' => 'profile.owner@cooca.test',
            'password' => Hash::make('password123'),
            'phone' => '6281112223334',
            'phone_verified_at' => now(),
            'active_business_id' => $business->id,
        ]);
        $business->users()->attach($user->id, ['role' => 'owner']);

        $response = $this->actingAs($user, 'web')
            ->post(route('profile.phone.request'), [
                'phone' => '081299998888',
            ]);

        $response->assertRedirect(route('profile.contact.verify'));

        Mail::assertSent(UserOtpMail::class, function (UserOtpMail $mail) use ($user) {
            return $mail->hasTo($user->email)
                && $mail->isFallbackFromWaba === true
                && strlen($mail->otpCode) === 6;
        });

        $pending = session('profile_phone_change');
        $this->assertIsArray($pending);
        $this->assertSame('email', $pending['channel']);
    }

    public function test_customer_api_send_otp_falls_back_to_email_when_waba_fails(): void
    {
        Mail::fake();

        $mockWa = Mockery::mock(AdminWhatsAppService::class);
        $mockWa->shouldReceive('sendOtp')
            ->once()
            ->andReturn([
                'success' => false,
                'error'   => 'Meta WhatsApp Cloud Error',
            ]);
        $this->app->instance(AdminWhatsAppService::class, $mockWa);

        $customer = \App\Models\GlobalCustomer::create([
            'name' => 'Mobile App Customer',
            'email' => 'mobile.customer@shop.test',
            'phone' => '6285566778899',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->actingAs($customer, 'sanctum')
            ->postJson('/api/v1/customer/auth/send-otp', [
                'phone' => '6285566778899',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'channel' => 'email',
        ]);

        Mail::assertSent(UserOtpMail::class, function (UserOtpMail $mail) use ($customer) {
            return $mail->hasTo($customer->email)
                && $mail->isFallbackFromWaba === true;
        });
    }

    public function test_send_whatsapp_otp_job_sends_email_on_final_failure(): void
    {
        Mail::fake();

        $user = User::create([
            'name' => 'Async Queue Owner',
            'email' => 'async.owner@test.id',
            'password' => Hash::make('secret123'),
            'phone' => '6289900112233',
        ]);

        $job = new \App\Jobs\WhatsApp\SendWhatsAppOtpJob(
            phone: '6289900112233',
            otpCode: '887766'
        );

        $job->failed(new \RuntimeException('Meta API persistent failure'));

        Mail::assertSent(UserOtpMail::class, function (UserOtpMail $mail) use ($user) {
            return $mail->hasTo($user->email)
                && $mail->otpCode === '887766'
                && $mail->isFallbackFromWaba === true;
        });
    }
}
