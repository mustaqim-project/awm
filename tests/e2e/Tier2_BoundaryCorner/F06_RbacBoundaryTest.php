<?php

declare(strict_types=1);

namespace Tests\E2E\Tier2_BoundaryCorner;

use App\Models\BusinessMembership;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Tests\E2E\Support\BaseTestCase;

/**
 * Tier 2 Boundary & Corner Cases: F06 RBAC & Security Permission Matrix
 *
 * Verifies RBAC security boundaries: cashier privilege escalation blocking,
 * owner universal permissions, cross-business role boundary isolation,
 * 403 unauthorized action blocking, and admin vs web guard isolation.
 */
final class F06_RbacBoundaryTest extends BaseTestCase
{
    /**
     * 1. Cashier role cannot escalate privileges to administrative or accounting actions.
     */
    public function test_cashier_privilege_escalation_to_admin_permissions_blocked(): void
    {
        $membership = new BusinessMembership([
            'role' => 'cashier',
            'business_id' => '00000000-0000-0000-0000-000000000001',
        ]);

        $restrictedPermissions = [
            'settings.edit',
            'settings.view',
            'accounting.manage',
            'accounting.close_period',
            'hrm.payroll.approve',
            'roles.manage',
        ];

        foreach ($restrictedPermissions as $permission) {
            $this->assertFalse(
                $membership->hasPermission($permission),
                "Cashier role must NOT have permission [{$permission}]."
            );
        }
    }

    /**
     * 2. Owner role retains universal access and returns true for all domain permissions.
     */
    public function test_owner_role_has_universal_permissions_within_business(): void
    {
        $membership = new BusinessMembership([
            'role' => 'owner',
            'business_id' => '00000000-0000-0000-0000-000000000001',
        ]);

        $testPermissions = [
            'settings.edit',
            'accounting.manage',
            'pos.checkout',
            'inventory.view',
            'roles.manage',
        ];

        foreach ($testPermissions as $permission) {
            $this->assertTrue(
                $membership->hasPermission($permission),
                "Owner role must have universal permission [{$permission}]."
            );
        }
    }

    /**
     * 3. Role permissions do not leak across business boundaries.
     */
    public function test_role_permissions_isolated_within_business_boundary(): void
    {
        $this->runInTransaction(function (): void {
            $tenantA = $this->createTransientTenant(['name' => 'RBAC Tenant A ' . uniqid()]);
            $tenantB = $this->createTransientTenant(['name' => 'RBAC Tenant B ' . uniqid()]);
            $user = User::first() ?? User::factory()->create();

            $membershipA = BusinessMembership::create([
                'business_id' => $tenantA->id,
                'user_id' => $user->id,
                'role' => 'cashier',
            ]);

            // Membership A belongs strictly to Tenant A
            $this->assertSame($tenantA->id, $membershipA->business_id);
            $this->assertNotSame($tenantB->id, $membershipA->business_id);

            // User has no membership in Tenant B
            $membershipB = BusinessMembership::where('business_id', $tenantB->id)
                ->where('user_id', $user->id)
                ->first();

            $this->assertNull($membershipB, 'User must not possess automatic membership in Tenant B.');
        });
    }

    /**
     * 4. Unauthorized guest or non-admin user blocked when attempting administrative operations.
     */
    public function test_unauthenticated_or_unauthorized_user_denied_on_role_management_api(): void
    {
        $response = $this->postJson('/settings/apply-template', [
            'template' => 'retail_reseller',
        ]);

        $this->assertContains(
            $response->getStatusCode(),
            [401, 403, 302, 419],
            "Unauthorized template application should return 401, 403, 302, or 419, got {$response->getStatusCode()}."
        );
    }

    /**
     * 5. Superadmin admin guard session is strictly isolated from regular tenant web guard.
     */
    public function test_admin_superadmin_guard_complete_isolation_from_web_guard(): void
    {
        // When unauthenticated, both guards are inactive
        $this->assertFalse(Auth::guard('web')->check());
        $this->assertFalse(Auth::guard('admin')->check());

        // Authenticating a regular tenant user on web guard must not authenticate admin guard
        $user = User::first();
        if ($user !== null) {
            Auth::guard('web')->login($user);
            $this->assertTrue(Auth::guard('web')->check());
            $this->assertFalse(
                Auth::guard('admin')->check(),
                'Authenticating on web guard must never authenticate the admin guard.'
            );
            Auth::guard('web')->logout();
        }
    }
}
