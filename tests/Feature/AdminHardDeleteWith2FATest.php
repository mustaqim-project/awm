<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Business;
use App\Models\User;
use App\Services\Google2FAService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminHardDeleteWith2FATest extends TestCase
{
    use RefreshDatabase;

    private Google2FAService $google2FA;

    protected function setUp(): void
    {
        parent::setUp();
        $this->google2FA = app(Google2FAService::class);
        Storage::fake('public');
        Storage::fake('local');
    }

    private function createAdmin(bool $with2FA = true): Admin
    {
        $secret = $this->google2FA->generateSecret();

        return Admin::factory()->create([
            'name' => 'Super Administrator',
            'email' => 'superadmin_' . uniqid() . '@cooca.test',
            'password' => Hash::make('password123'),
            'role' => 'super_admin',
            'is_active' => true,
            'two_factor_secret' => $with2FA ? $secret : null,
            'two_factor_confirmed_at' => $with2FA ? now() : null,
        ]);
    }

    private function makeBusiness(array $attributes = []): Business
    {
        return Business::create(array_merge([
            'name' => 'Usaha Test ' . uniqid(),
            'slug' => 'usaha-test-' . uniqid(),
            'is_active' => true,
        ], $attributes));
    }

    public function test_admin_without_2fa_cannot_hard_delete_business(): void
    {
        $admin = $this->createAdmin(with2FA: false);
        $business = $this->makeBusiness([
            'name' => 'Toko Tanpa 2FA',
            'slug' => 'toko-tanpa-2fa',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->from(route('admin.businesses.index'))
            ->delete(route('admin.businesses.destroy', $business), [
                'confirm_name' => 'Toko Tanpa 2FA',
                'totp_code' => '123456',
            ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('businesses', ['id' => $business->id]);
    }

    public function test_business_hard_delete_requires_matching_name_or_slug(): void
    {
        $admin = $this->createAdmin(with2FA: true);
        $business = $this->makeBusiness([
            'name' => 'Kedai Kopi Kenangan',
            'slug' => 'kedai-kopi-kenangan',
        ]);

        $validOtp = $this->google2FA->getCode((string) $admin->two_factor_secret);

        $response = $this->actingAs($admin, 'admin')
            ->from(route('admin.businesses.index'))
            ->delete(route('admin.businesses.destroy', $business), [
                'confirm_name' => 'Salah Nama',
                'totp_code' => $validOtp,
            ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('businesses', ['id' => $business->id]);
    }

    public function test_business_hard_delete_requires_valid_google_authenticator_code(): void
    {
        $admin = $this->createAdmin(with2FA: true);
        $business = $this->makeBusiness([
            'name' => 'Resto Berkah',
            'slug' => 'resto-berkah',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->from(route('admin.businesses.index'))
            ->delete(route('admin.businesses.destroy', $business), [
                'confirm_name' => 'Resto Berkah',
                'totp_code' => '000000', // Invalid code
            ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('businesses', ['id' => $business->id]);
    }

    public function test_successful_business_hard_delete_purges_database_and_storage_with_valid_2fa(): void
    {
        $admin = $this->createAdmin(with2FA: true);
        $business = $this->makeBusiness([
            'name' => 'Usaha Mawar Indah',
            'slug' => 'usaha-mawar-indah',
            'logo_path' => 'logos/usaha-mawar.png',
        ]);

        // Place simulated storage files
        Storage::disk('public')->put("businesses/{$business->id}/sample.txt", 'dummy content');
        Storage::disk('public')->put("bisnis/{$business->slug}/banner.png", 'banner image');
        Storage::disk('public')->put($business->logo_path, 'logo bytes');
        Storage::disk('local')->put("private/bisnis/{$business->slug}/secret.pdf", 'classified');

        $validOtp = $this->google2FA->getCode((string) $admin->two_factor_secret);

        $response = $this->actingAs($admin, 'admin')
            ->from(route('admin.businesses.index'))
            ->delete(route('admin.businesses.destroy', $business), [
                'confirm_name' => 'Usaha Mawar Indah',
                'totp_code' => $validOtp,
            ]);

        $response->assertRedirect(route('admin.businesses.index'));
        $response->assertSessionHas('success');

        // Confirm database record is completely gone (hard deleted, not soft deleted)
        $this->assertDatabaseMissing('businesses', ['id' => $business->id]);

        // Confirm storage directories were purged
        $this->assertFalse(Storage::disk('public')->exists("businesses/{$business->id}"));
        $this->assertFalse(Storage::disk('public')->exists("bisnis/{$business->slug}"));
        $this->assertFalse(Storage::disk('public')->exists($business->logo_path));
        $this->assertFalse(Storage::disk('local')->exists("private/bisnis/{$business->slug}"));
    }

    public function test_admin_without_2fa_cannot_hard_delete_user(): void
    {
        $admin = $this->createAdmin(with2FA: false);
        $user = User::factory()->create([
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->delete(route('admin.users.destroy', $user), [
                'confirm_email' => 'budi@example.com',
                'totp_code' => '123456',
            ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    public function test_user_hard_delete_requires_valid_google_authenticator_code(): void
    {
        $admin = $this->createAdmin(with2FA: true);
        $user = User::factory()->create([
            'name' => 'Siti Aminah',
            'email' => 'siti@example.com',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->delete(route('admin.users.destroy', $user), [
                'confirm_email' => 'siti@example.com',
                'totp_code' => '999999',
            ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    public function test_successful_user_hard_delete_purges_user_with_valid_2fa(): void
    {
        $admin = $this->createAdmin(with2FA: true);
        $user = User::factory()->create([
            'name' => 'Ahmad Dahlan',
            'email' => 'ahmad.dahlan@example.com',
            'avatar' => 'avatars/ahmad.jpg',
        ]);

        Storage::disk('public')->put($user->avatar, 'avatar content');
        Storage::disk('public')->put("users/{$user->id}/doc.txt", 'personal doc');

        $validOtp = $this->google2FA->getCode((string) $admin->two_factor_secret);

        $response = $this->actingAs($admin, 'admin')
            ->from(route('admin.users.index'))
            ->delete(route('admin.users.destroy', $user), [
                'confirm_email' => 'ahmad.dahlan@example.com',
                'totp_code' => $validOtp,
            ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        // Confirm database record is completely hard deleted
        $this->assertDatabaseMissing('users', ['id' => $user->id]);

        // Confirm storage file cleanup
        $this->assertFalse(Storage::disk('public')->exists($user->avatar));
        $this->assertFalse(Storage::disk('public')->exists("users/{$user->id}"));
    }

    public function test_admin_can_enable_2fa_on_profile(): void
    {
        $admin = $this->createAdmin(with2FA: false);
        $secret = $this->google2FA->generateSecret();
        $code = $this->google2FA->getCode($secret);

        $response = $this->actingAs($admin, 'admin')
            ->post(route('admin.profile.2fa.enable'), [
                'secret' => $secret,
                'totp_code' => $code,
            ]);

        $response->assertSessionHas('success');
        $admin->refresh();
        $this->assertTrue($admin->hasTwoFactorEnabled());
        $this->assertEquals($secret, $admin->two_factor_secret);
        $this->assertNotNull($admin->two_factor_confirmed_at);
    }
}
