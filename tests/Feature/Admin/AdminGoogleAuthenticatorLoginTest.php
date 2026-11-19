<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Services\Google2FAService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class AdminGoogleAuthenticatorLoginTest extends TestCase
{
    use RefreshDatabase;

    private Google2FAService $google2FA;

    protected function setUp(): void
    {
        parent::setUp();
        $this->google2FA = new Google2FAService();
    }

    public function test_login_page_renders_futuristic_elements_and_authenticator_field(): void
    {
        $response = $this->get(route('admin.login'));

        $response->assertStatus(200);
        $response->assertSee('Admin Console');
        $response->assertSee('Masuk ke Admin Console');
        $response->assertSee('name="totp_code"', false);
        $response->assertSee('neuralCanvas');
        $response->assertSee('cyber-grid');
    }

    public function test_admin_without_2fa_can_login_with_only_email_and_password(): void
    {
        $admin = Admin::factory()->create([
            'email' => 'standard.admin@cooca.id',
            'password' => Hash::make('AdminPass123!'),
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
            'is_active' => true,
        ]);

        $response = $this->post(route('admin.login.submit'), [
            'email' => $admin->email,
            'password' => 'AdminPass123!',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin, 'admin');
    }

    public function test_admin_with_2fa_fails_if_totp_code_is_missing(): void
    {
        $secret = $this->google2FA->generateSecret();
        $admin = Admin::factory()->create([
            'email' => 'secure.admin@cooca.id',
            'password' => Hash::make('AdminPass123!'),
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => now(),
            'is_active' => true,
        ]);

        $response = $this->post(route('admin.login.submit'), [
            'email' => $admin->email,
            'password' => 'AdminPass123!',
            'totp_code' => '',
        ]);

        $response->assertSessionHasErrors('totp_code');
        $this->assertGuest('admin');
    }

    public function test_admin_with_2fa_fails_if_totp_code_is_invalid(): void
    {
        $secret = $this->google2FA->generateSecret();
        $admin = Admin::factory()->create([
            'email' => 'secure.admin@cooca.id',
            'password' => Hash::make('AdminPass123!'),
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => now(),
            'is_active' => true,
        ]);

        $response = $this->post(route('admin.login.submit'), [
            'email' => $admin->email,
            'password' => 'AdminPass123!',
            'totp_code' => '999999',
        ]);

        $response->assertSessionHasErrors('totp_code');
        $this->assertGuest('admin');
    }

    public function test_admin_with_2fa_succeeds_when_valid_totp_code_provided(): void
    {
        $secret = $this->google2FA->generateSecret();
        $admin = Admin::factory()->create([
            'email' => 'secure.admin@cooca.id',
            'password' => Hash::make('AdminPass123!'),
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => now(),
            'is_active' => true,
        ]);

        $validCode = $this->google2FA->getCode($secret);

        $response = $this->post(route('admin.login.submit'), [
            'email' => $admin->email,
            'password' => 'AdminPass123!',
            'totp_code' => $validCode,
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin, 'admin');
    }

    public function test_artisan_command_can_reset_admin_2fa_in_emergency(): void
    {
        $admin = Admin::factory()->create([
            'email' => 'locked.admin@cooca.id',
            'password' => Hash::make('AdminPass123!'),
            'two_factor_secret' => $this->google2FA->generateSecret(),
            'two_factor_confirmed_at' => now(),
            'is_active' => true,
        ]);

        $this->assertTrue($admin->hasTwoFactorEnabled());

        // Run artisan reset command with --force
        $this->artisan('admin:reset-2fa', [
            'email' => 'locked.admin@cooca.id',
            '--force' => true,
        ])->assertSuccessful();

        $admin->refresh();
        $this->assertFalse($admin->hasTwoFactorEnabled());
        $this->assertNull($admin->two_factor_secret);
        $this->assertNull($admin->two_factor_confirmed_at);
    }
}
