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
}
