<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\AuditLog;
use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\AdminRbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminBusinessImpersonationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->seed(AdminRbacSeeder::class);
    }

    private function createAdmin(string $role = 'super_admin'): Admin
    {
        return Admin::create([
            'name' => 'Platform Superadmin',
            'email' => 'superadmin@cooca.id',
            'password' => Hash::make('password123'),
            'role' => $role,
            'is_active' => true,
        ]);
    }

    private function createBusinessWithUser(): array
    {
        $business = Business::create([
            'name' => 'Kopi Kenangan Senja',
            'slug' => 'kopi-kenangan-senja',
            'currency' => 'IDR',
            'is_active' => true,
        ]);

        $owner = User::factory()->create([
            'name' => 'Budi Santoso',
            'email' => 'budi@kopikenangan.id',
            'active_business_id' => $business->id,
            'phone' => '6281234567890',
            'phone_verified_at' => now(),
        ]);

        BusinessMembership::create([
            'business_id' => $business->id,
            'user_id' => $owner->id,
            'role' => 'owner',
        ]);

        return [$business, $owner];
    }

    public function test_guest_cannot_impersonate_business(): void
    {
        [$business] = $this->createBusinessWithUser();

        $response = $this->post(route('admin.businesses.impersonate', $business->id));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_impersonate_business_workspace_and_audit_is_recorded(): void
    {
        $admin = $this->createAdmin();
        [$business, $owner] = $this->createBusinessWithUser();

        $response = $this->actingAs($admin, 'admin')
            ->post(route('admin.businesses.impersonate', $business->id));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('info');

        // Verify session flags
        $response->assertSessionHas('admin_impersonator_id', $admin->id);
        $response->assertSessionHas('admin_impersonator_name', $admin->name);
        $response->assertSessionHas('admin_impersonated_business_id', $business->id);
        $response->assertSessionHas('active_business_id', $business->id);
        $response->assertSessionHas('auth_wa_otp_verified_user_id', $owner->id);

        // Verify web guard is authenticated as owner
        $this->assertAuthenticatedAs($owner, 'web');

        // Verify AuditLog entry
        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $business->id,
            'user_id' => $owner->id,
            'action' => 'admin_impersonate_start',
            'auditable_type' => Business::class,
            'auditable_id' => $business->id,
        ]);
    }

    public function test_admin_impersonation_renders_investigation_banner_on_tenant_pages(): void
    {
        $admin = $this->createAdmin();
        [$business, $owner] = $this->createBusinessWithUser();

        // Perform impersonation
        $this->actingAs($admin, 'admin')
            ->post(route('admin.businesses.impersonate', $business->id));

        // Visit tenant dashboard with the session
        $response = $this->actingAs($owner, 'web')
            ->withSession([
                'admin_impersonator_id' => $admin->id,
                'admin_impersonator_name' => $admin->name,
                'admin_impersonated_business_id' => $business->id,
                'admin_impersonated_business_name' => $business->name,
                'active_business_id' => $business->id,
            ])
            ->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Mode Investigasi Bug');
        $response->assertSee('Kembali ke Admin Console');
        $response->assertSee($business->name);
    }

    public function test_admin_can_leave_impersonation_and_return_to_admin_console(): void
    {
        $admin = $this->createAdmin();
        [$business, $owner] = $this->createBusinessWithUser();

        // Impersonate
        $this->actingAs($admin, 'admin')
            ->post(route('admin.businesses.impersonate', $business->id));

        // Now leave impersonation
        $response = $this->actingAs($admin, 'admin')
            ->actingAs($owner, 'web')
            ->withSession([
                'admin_impersonator_id' => $admin->id,
                'admin_impersonator_name' => $admin->name,
                'admin_impersonated_business_id' => $business->id,
                'active_business_id' => $business->id,
                'auth_wa_otp_verified_user_id' => $owner->id,
            ])
            ->post(route('admin.businesses.leave-impersonation'));

        $response->assertRedirect(route('admin.businesses.show', $business->id));
        $response->assertSessionHas('success');

        // Web session should be terminated
        $this->assertGuest('web');

        // Session flags should be removed
        $response->assertSessionMissing('admin_impersonator_id');
        $response->assertSessionMissing('admin_impersonated_business_id');

        // AuditLog entry for leaving impersonation
        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $business->id,
            'user_id' => $owner->id,
            'action' => 'admin_impersonate_end',
        ]);
    }

    public function test_tenant_logout_while_impersonating_redirects_to_leave_impersonation(): void
    {
        $admin = $this->createAdmin();
        [$business, $owner] = $this->createBusinessWithUser();

        $response = $this->actingAs($admin, 'admin')
            ->actingAs($owner, 'web')
            ->withSession([
                'admin_impersonator_id' => $admin->id,
                'admin_impersonator_name' => $admin->name,
                'admin_impersonated_business_id' => $business->id,
                'active_business_id' => $business->id,
            ])
            ->post(route('logout'));

        $response->assertRedirect(route('admin.businesses.show', $business->id));
        $this->assertGuest('web');
    }

    public function test_impersonate_fails_gracefully_if_business_has_no_users(): void
    {
        $admin = $this->createAdmin();
        $business = Business::create([
            'name' => 'Bisnis Tanpa User',
            'slug' => 'bisnis-tanpa-user',
            'currency' => 'IDR',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->post(route('admin.businesses.impersonate', $business->id));

        $response->assertSessionHas('error');
        $this->assertGuest('web');
    }

    public function test_admin_business_index_and_show_contain_masuk_button(): void
    {
        $admin = $this->createAdmin();
        [$business] = $this->createBusinessWithUser();

        // Check index page
        $indexResponse = $this->actingAs($admin, 'admin')
            ->get(route('admin.businesses.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee(route('admin.businesses.impersonate', $business->id));
        $indexResponse->assertSee('Masuk');

        // Check show page
        $showResponse = $this->actingAs($admin, 'admin')
            ->get(route('admin.businesses.show', $business->id));
        $showResponse->assertStatus(200);
        $showResponse->assertSee(route('admin.businesses.impersonate', $business->id));
        $showResponse->assertSee('Masuk ke Bisnis (Cek Bug)');
    }
}
