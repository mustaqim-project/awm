<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Business;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use App\Services\Google2FAService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminBulkHardDeleteWith2FATest extends TestCase
{
    use RefreshDatabase;

    private Google2FAService $google2FA;
    private Unit $unit;

    protected function setUp(): void
    {
        parent::setUp();
        $this->google2FA = app(Google2FAService::class);
        Storage::fake('public');
        Storage::fake('local');

        $this->unit = Unit::firstOrCreate(
            ['code' => 'pcs'],
            [
                'name' => 'Pcs',
                'category' => 'quantity',
                'is_base' => true,
            ]
        );
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

    public function test_admin_without_2fa_cannot_bulk_hard_delete_businesses(): void
    {
        $admin = $this->createAdmin(with2FA: false);
        $b1 = $this->makeBusiness(['name' => 'Toko A', 'slug' => 'toko-a']);
        $b2 = $this->makeBusiness(['name' => 'Toko B', 'slug' => 'toko-b']);

        $response = $this->actingAs($admin, 'admin')
            ->from(route('admin.businesses.index'))
            ->delete(route('admin.businesses.bulk-destroy'), [
                'ids' => [$b1->id, $b2->id],
                'confirm_text' => 'HAPUS MASAL',
                'totp_code' => '123456',
            ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('businesses', ['id' => $b1->id]);
        $this->assertDatabaseHas('businesses', ['id' => $b2->id]);
    }

    public function test_bulk_hard_delete_requires_exact_confirm_text_hapus_masal(): void
    {
        $admin = $this->createAdmin(with2FA: true);
        $b1 = $this->makeBusiness();
        $b2 = $this->makeBusiness();
        $validOtp = $this->google2FA->getCode((string) $admin->two_factor_secret);

        $response = $this->actingAs($admin, 'admin')
            ->from(route('admin.businesses.index'))
            ->delete(route('admin.businesses.bulk-destroy'), [
                'ids' => [$b1->id, $b2->id],
                'confirm_text' => 'SALAH KONFIRMASI',
                'totp_code' => $validOtp,
            ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('businesses', ['id' => $b1->id]);
        $this->assertDatabaseHas('businesses', ['id' => $b2->id]);
    }

    public function test_bulk_hard_delete_requires_valid_google_authenticator_totp_code(): void
    {
        $admin = $this->createAdmin(with2FA: true);
        $b1 = $this->makeBusiness();
        $b2 = $this->makeBusiness();

        $response = $this->actingAs($admin, 'admin')
            ->from(route('admin.businesses.index'))
            ->delete(route('admin.businesses.bulk-destroy'), [
                'ids' => [$b1->id, $b2->id],
                'confirm_text' => 'HAPUS MASAL',
                'totp_code' => '000000', // Invalid OTP
            ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('businesses', ['id' => $b1->id]);
        $this->assertDatabaseHas('businesses', ['id' => $b2->id]);
    }

    public function test_bulk_hard_delete_rejects_empty_ids_selection(): void
    {
        $admin = $this->createAdmin(with2FA: true);
        $validOtp = $this->google2FA->getCode((string) $admin->two_factor_secret);

        $response = $this->actingAs($admin, 'admin')
            ->from(route('admin.businesses.index'))
            ->delete(route('admin.businesses.bulk-destroy'), [
                'ids' => [],
                'confirm_text' => 'HAPUS MASAL',
                'totp_code' => $validOtp,
            ]);

        $response->assertSessionHasErrors('ids');
    }

    public function test_bulk_hard_delete_successfully_purges_multiple_businesses_and_assets_with_valid_2fa(): void
    {
        $admin = $this->createAdmin(with2FA: true);

        // Setup Business 1 with product and storage files
        $b1 = $this->makeBusiness(['name' => 'Bisnis Alpha', 'slug' => 'bisnis-alpha', 'logo_path' => 'logos/alpha.png']);
        Product::create([
            'business_id' => $b1->id,
            'output_unit_id' => $this->unit->id,
            'name' => 'Produk Alpha',
            'selling_price' => 50000,
            'is_active' => true,
        ]);
        Storage::disk('public')->put("businesses/{$b1->id}/avatar.jpg", 'sample-avatar-data');
        Storage::disk('public')->put($b1->logo_path, 'logo-alpha-data');

        // Setup Business 2 with product and storage files
        $b2 = $this->makeBusiness(['name' => 'Bisnis Beta', 'slug' => 'bisnis-beta', 'logo_path' => 'logos/beta.png']);
        Product::create([
            'business_id' => $b2->id,
            'output_unit_id' => $this->unit->id,
            'name' => 'Produk Beta',
            'selling_price' => 75000,
            'is_active' => true,
        ]);
        Storage::disk('public')->put("businesses/{$b2->id}/catalog.pdf", 'sample-pdf-data');
        Storage::disk('public')->put($b2->logo_path, 'logo-beta-data');

        $validOtp = $this->google2FA->getCode((string) $admin->two_factor_secret);

        $response = $this->actingAs($admin, 'admin')
            ->from(route('admin.businesses.index'))
            ->delete(route('admin.businesses.bulk-destroy'), [
                'ids' => [$b1->id, $b2->id],
                'confirm_text' => 'HAPUS MASAL',
                'totp_code' => $validOtp,
            ]);

        $response->assertRedirect(route('admin.businesses.index'));
        $response->assertSessionHas('success');

        // Database records must be completely purged
        $this->assertDatabaseMissing('businesses', ['id' => $b1->id]);
        $this->assertDatabaseMissing('businesses', ['id' => $b2->id]);
        $this->assertDatabaseMissing('products', ['name' => 'Produk Alpha']);
        $this->assertDatabaseMissing('products', ['name' => 'Produk Beta']);

        // Physical storage files must be purged
        Storage::disk('public')->assertMissing("businesses/{$b1->id}/avatar.jpg");
        Storage::disk('public')->assertMissing($b1->logo_path);
        Storage::disk('public')->assertMissing("businesses/{$b2->id}/catalog.pdf");
        Storage::disk('public')->assertMissing($b2->logo_path);
    }

    public function test_admin_without_2fa_cannot_bulk_hard_delete_users(): void
    {
        $admin = $this->createAdmin(with2FA: false);
        $u1 = User::factory()->create();
        $u2 = User::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->from(route('admin.users.index'))
            ->delete(route('admin.users.bulk-destroy'), [
                'ids' => [$u1->id, $u2->id],
                'confirm_text' => 'HAPUS MASAL',
                'totp_code' => '123456',
            ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $u1->id]);
        $this->assertDatabaseHas('users', ['id' => $u2->id]);
    }

    public function test_bulk_hard_delete_users_requires_exact_confirm_text_hapus_masal(): void
    {
        $admin = $this->createAdmin(with2FA: true);
        $u1 = User::factory()->create();
        $u2 = User::factory()->create();
        $validOtp = $this->google2FA->getCode((string) $admin->two_factor_secret);

        $response = $this->actingAs($admin, 'admin')
            ->from(route('admin.users.index'))
            ->delete(route('admin.users.bulk-destroy'), [
                'ids' => [$u1->id, $u2->id],
                'confirm_text' => 'WRONG CONFIRM',
                'totp_code' => $validOtp,
            ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $u1->id]);
        $this->assertDatabaseHas('users', ['id' => $u2->id]);
    }

    public function test_bulk_hard_delete_users_requires_valid_google_authenticator_totp_code(): void
    {
        $admin = $this->createAdmin(with2FA: true);
        $u1 = User::factory()->create();
        $u2 = User::factory()->create();

        $response = $this->actingAs($admin, 'admin')
            ->from(route('admin.users.index'))
            ->delete(route('admin.users.bulk-destroy'), [
                'ids' => [$u1->id, $u2->id],
                'confirm_text' => 'HAPUS MASAL',
                'totp_code' => '000000',
            ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $u1->id]);
        $this->assertDatabaseHas('users', ['id' => $u2->id]);
    }

    public function test_bulk_hard_delete_users_completely_purges_users_and_all_owned_businesses(): void
    {
        $admin = $this->createAdmin(with2FA: true);

        // Owner 1 with owned business and products
        $owner1 = User::factory()->create(['name' => 'Owner Satu', 'email' => 'owner1@test.com']);
        $b1 = $this->makeBusiness(['name' => 'Bisnis Owner Satu', 'slug' => 'bisnis-owner-satu']);
        \Illuminate\Support\Facades\DB::table('business_users')->insert([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'business_id' => $b1->id,
            'user_id' => $owner1->id,
            'role' => 'owner',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        Product::create([
            'business_id' => $b1->id,
            'output_unit_id' => $this->unit->id,
            'name' => 'Produk Bisnis Owner 1',
            'selling_price' => 100000,
            'is_active' => true,
        ]);
        Storage::disk('public')->put("businesses/{$b1->id}/doc.pdf", 'biz1-file-content');
        Storage::disk('public')->put("users/{$owner1->id}/profile.png", 'user1-avatar');

        // Owner 2 with owned business and products
        $owner2 = User::factory()->create(['name' => 'Owner Dua', 'email' => 'owner2@test.com']);
        $b2 = $this->makeBusiness(['name' => 'Bisnis Owner Dua', 'slug' => 'bisnis-owner-dua']);
        \Illuminate\Support\Facades\DB::table('business_users')->insert([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'business_id' => $b2->id,
            'user_id' => $owner2->id,
            'role' => 'owner',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        Product::create([
            'business_id' => $b2->id,
            'output_unit_id' => $this->unit->id,
            'name' => 'Produk Bisnis Owner 2',
            'selling_price' => 200000,
            'is_active' => true,
        ]);
        Storage::disk('public')->put("businesses/{$b2->id}/catalog.pdf", 'biz2-file-content');

        $validOtp = $this->google2FA->getCode((string) $admin->two_factor_secret);

        $response = $this->actingAs($admin, 'admin')
            ->from(route('admin.users.index'))
            ->delete(route('admin.users.bulk-destroy'), [
                'ids' => [$owner1->id, $owner2->id],
                'confirm_text' => 'HAPUS MASAL',
                'totp_code' => $validOtp,
            ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        // Assert users deleted
        $this->assertDatabaseMissing('users', ['id' => $owner1->id]);
        $this->assertDatabaseMissing('users', ['id' => $owner2->id]);

        // Assert owned businesses deleted
        $this->assertDatabaseMissing('businesses', ['id' => $b1->id]);
        $this->assertDatabaseMissing('businesses', ['id' => $b2->id]);
        $this->assertDatabaseMissing('products', ['name' => 'Produk Bisnis Owner 1']);
        $this->assertDatabaseMissing('products', ['name' => 'Produk Bisnis Owner 2']);

        // Assert storage assets purged
        Storage::disk('public')->assertMissing("businesses/{$b1->id}/doc.pdf");
        Storage::disk('public')->assertMissing("businesses/{$b2->id}/catalog.pdf");
        Storage::disk('public')->assertMissing("users/{$owner1->id}/profile.png");
    }

    public function test_single_delete_user_cascades_and_purges_owned_business_completely(): void
    {
        $admin = $this->createAdmin(with2FA: true);

        $owner = User::factory()->create(['name' => 'Single Owner', 'email' => 'singleowner@test.com']);
        $biz = $this->makeBusiness(['name' => 'Bisnis Single Owner', 'slug' => 'bisnis-single-owner']);
        \Illuminate\Support\Facades\DB::table('business_users')->insert([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'business_id' => $biz->id,
            'user_id' => $owner->id,
            'role' => 'owner',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        Product::create([
            'business_id' => $biz->id,
            'output_unit_id' => $this->unit->id,
            'name' => 'Produk Single Owner',
            'selling_price' => 50000,
            'is_active' => true,
        ]);
        Storage::disk('public')->put("businesses/{$biz->id}/invoice.pdf", 'invoice-file-content');

        $validOtp = $this->google2FA->getCode((string) $admin->two_factor_secret);

        $response = $this->actingAs($admin, 'admin')
            ->from(route('admin.users.index'))
            ->delete(route('admin.users.destroy', $owner), [
                'confirm_email' => 'singleowner@test.com',
                'totp_code' => $validOtp,
            ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        // User & Owned Business must be completely purged
        $this->assertDatabaseMissing('users', ['id' => $owner->id]);
        $this->assertDatabaseMissing('businesses', ['id' => $biz->id]);
        $this->assertDatabaseMissing('products', ['name' => 'Produk Single Owner']);
        Storage::disk('public')->assertMissing("businesses/{$biz->id}/invoice.pdf");
    }
}
