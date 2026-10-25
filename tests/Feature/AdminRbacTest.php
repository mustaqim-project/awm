<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Business;
use App\Support\Context;
use Database\Seeders\AdminRbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminRbacTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        // Seed roles and permissions into in-memory sqlite
        $this->seed(AdminRbacSeeder::class);
    }

    private function createAdmin(string $role, bool $isActive = true): Admin
    {
        return Admin::factory()->create([
            'name' => "Admin {$role}",
            'email' => "{$role}@example.com",
            'password' => Hash::make('password123'),
            'role' => $role,
            'is_active' => $isActive,
        ]);
    }

    public function test_super_admin_has_unrestricted_access(): void
    {
        $superAdmin = $this->createAdmin('super_admin');

        $this->actingAs($superAdmin, 'admin')
            ->get(route('admin.dashboard'))
            ->assertStatus(200);

        $this->actingAs($superAdmin, 'admin')
            ->get(route('admin.businesses.index'))
            ->assertStatus(200);

        $this->actingAs($superAdmin, 'admin')
            ->get(route('admin.settlements.index'))
            ->assertStatus(200);

        $this->actingAs($superAdmin, 'admin')
            ->get(route('admin.error-logs.index'))
            ->assertStatus(200);

        $this->actingAs($superAdmin, 'admin')
            ->get(route('admin.settings.index'))
            ->assertStatus(200);
    }

    public function test_finance_admin_can_access_financial_modules_but_forbidden_on_system_and_businesses(): void
    {
        $financeAdmin = $this->createAdmin('finance_admin');

        // Allowed financial modules
        $this->actingAs($financeAdmin, 'admin')
            ->get(route('admin.settlements.index'))
            ->assertStatus(200);

        $this->actingAs($financeAdmin, 'admin')
            ->get(route('admin.subscriptions.index'))
            ->assertStatus(200);

        // Forbidden non-financial modules
        $this->actingAs($financeAdmin, 'admin')
            ->get(route('admin.businesses.index'))
            ->assertStatus(403);

        $this->actingAs($financeAdmin, 'admin')
            ->get(route('admin.users.index'))
            ->assertStatus(403);

        $this->actingAs($financeAdmin, 'admin')
            ->get(route('admin.error-logs.index'))
            ->assertStatus(403);

        $this->actingAs($financeAdmin, 'admin')
            ->get(route('admin.settings.index'))
            ->assertStatus(403);
    }

    public function test_operations_admin_can_access_business_and_users_but_forbidden_on_settlements(): void
    {
        $opsAdmin = $this->createAdmin('operations_admin');

        // Allowed operations modules
        $this->actingAs($opsAdmin, 'admin')
            ->get(route('admin.businesses.index'))
            ->assertStatus(200);

        $this->actingAs($opsAdmin, 'admin')
            ->get(route('admin.users.index'))
            ->assertStatus(200);

        // Forbidden financial and sensitive system modules
        $this->actingAs($opsAdmin, 'admin')
            ->get(route('admin.settlements.index'))
            ->assertStatus(403);

        $this->actingAs($opsAdmin, 'admin')
            ->get(route('admin.error-logs.index'))
            ->assertStatus(403);

        $this->actingAs($opsAdmin, 'admin')
            ->get(route('admin.settings.index'))
            ->assertStatus(403);
    }

    public function test_auditor_has_view_access_but_forbidden_on_mutation_actions(): void
    {
        $auditor = $this->createAdmin('auditor');

        // Auditor can view reports and listings
        $this->actingAs($auditor, 'admin')
            ->get(route('admin.businesses.index'))
            ->assertStatus(200);

        $this->actingAs($auditor, 'admin')
            ->get(route('admin.settlements.index'))
            ->assertStatus(200);

        $this->actingAs($auditor, 'admin')
            ->get(route('admin.error-logs.index'))
            ->assertStatus(200);

        // Auditor is blocked from mutating actions (e.g. deleting logs or modifying settings)
        $this->actingAs($auditor, 'admin')
            ->delete(route('admin.error-logs.clear'), ['file' => 'laravel.log'])
            ->assertStatus(403);

        $this->actingAs($auditor, 'admin')
            ->post(route('admin.settings.update'), [])
            ->assertStatus(403);
    }

    public function test_support_admin_can_access_inbox_and_feedback_but_forbidden_on_settlements(): void
    {
        $supportAdmin = $this->createAdmin('support_admin');

        $this->actingAs($supportAdmin, 'admin')
            ->get(route('admin.inbox.index'))
            ->assertStatus(200);

        $this->actingAs($supportAdmin, 'admin')
            ->get(route('admin.feedback.bugs.index'))
            ->assertStatus(200);

        // Forbidden on financial modules
        $this->actingAs($supportAdmin, 'admin')
            ->get(route('admin.settlements.index'))
            ->assertStatus(403);

        $this->actingAs($supportAdmin, 'admin')
            ->get(route('admin.settings.index'))
            ->assertStatus(403);
    }

    public function test_system_admin_can_access_infrastructure_but_forbidden_on_settlements(): void
    {
        $systemAdmin = $this->createAdmin('system_admin');

        $this->actingAs($systemAdmin, 'admin')
            ->get(route('admin.error-logs.index'))
            ->assertStatus(200);

        $this->actingAs($systemAdmin, 'admin')
            ->get(route('admin.settings.index'))
            ->assertStatus(200);

        // Forbidden on settlements
        $this->actingAs($systemAdmin, 'admin')
            ->get(route('admin.settlements.index'))
            ->assertStatus(403);
    }

    public function test_super_admin_and_system_admin_can_manage_roles_and_unauthorized_is_forbidden(): void
    {
        $superAdmin = $this->createAdmin('super_admin');
        $systemAdmin = $this->createAdmin('system_admin');
        $financeAdmin = $this->createAdmin('finance_admin');
        $targetAdmin = $this->createAdmin('operations_admin');

        // 1. Super admin can view roles hub
        $this->actingAs($superAdmin, 'admin')
            ->get(route('admin.roles.index'))
            ->assertStatus(200);

        // 2. Super admin can create a custom role
        $response = $this->actingAs($superAdmin, 'admin')
            ->post(route('admin.roles.store'), [
                'name' => 'Admin Pemasaran Khusus',
                'description' => 'Role custom untuk tim pemasaran',
                'permissions' => ['admin.dashboard.view', 'admin.posts.view'],
            ]);
        $response->assertRedirect(route('admin.roles.index'));

        $this->assertDatabaseHas('roles', [
            'name' => 'Admin Pemasaran Khusus',
            'slug' => 'admin_pemasaran_khusus',
            'business_id' => null,
        ]);

        $customRole = \App\Models\Role::where('slug', 'admin_pemasaran_khusus')->firstOrFail();

        // 3. System admin can also manage roles
        $this->actingAs($systemAdmin, 'admin')
            ->get(route('admin.roles.index'))
            ->assertStatus(200);

        $this->actingAs($systemAdmin, 'admin')
            ->put(route('admin.roles.update', $customRole), [
                'name' => 'Admin Pemasaran & Event',
                'description' => 'Diperbarui oleh system admin',
                'permissions' => ['admin.dashboard.view', 'admin.posts.view', 'admin.events.view'],
            ])->assertRedirect(route('admin.roles.index'));

        // 4. Change admin role
        $this->actingAs($superAdmin, 'admin')
            ->put(route('admin.roles.admins.role', $targetAdmin), [
                'role' => 'admin_pemasaran_khusus',
            ])->assertRedirect(route('admin.roles.index'));

        $this->assertEquals('admin_pemasaran_khusus', $targetAdmin->fresh()->role);

        // 5. Finance admin is forbidden on roles hub
        $this->actingAs($financeAdmin, 'admin')
            ->get(route('admin.roles.index'))
            ->assertStatus(403);

        $this->actingAs($financeAdmin, 'admin')
            ->post(route('admin.roles.store'), [
                'name' => 'Illegal Role',
            ])->assertStatus(403);
    }

    public function test_unauthenticated_visitor_is_redirected_to_admin_login(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.login'));

        $this->get(route('admin.businesses.index'))
            ->assertRedirect(route('admin.login'));
    }
}
