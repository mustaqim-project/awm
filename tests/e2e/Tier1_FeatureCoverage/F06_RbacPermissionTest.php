<?php

declare(strict_types=1);

namespace Tests\E2E\Tier1_FeatureCoverage;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\E2E\Support\BaseTestCase;

/**
 * Feature F06: RBAC & Security Permission Matrix Test Suite
 *
 * Validates the Spatie-compatible RBAC tables, permissions catalog, workspace roles,
 * Superadmin platform guard mapping, and user role assignments.
 */
final class F06_RbacPermissionTest extends BaseTestCase
{
    /**
     * 1. Assert RBAC tables (roles, permissions, and assignment pivots) exist in database schema.
     */
    public function test_spatie_roles_and_permissions_tables_exist(): void
    {
        $this->assertTrue(Schema::hasTable('roles'), "Database table [roles] must exist.");
        $this->assertTrue(Schema::hasTable('permissions'), "Database table [permissions] must exist.");

        $hasPivot = Schema::hasTable('role_permissions')
            || Schema::hasTable('role_has_permissions')
            || Schema::hasTable('model_has_roles');

        $this->assertTrue($hasPivot, "Database must have an RBAC role-permission pivot table.");
    }

    /**
     * 2. Assert permissions catalog is populated with comprehensive multi-module permissions (> 100).
     */
    public function test_permissions_catalog_populated(): void
    {
        $count = DB::table('permissions')->count();
        $this->assertGreaterThan(
            100,
            $count,
            "Expected at least 100 distinct security permissions in catalog, found {$count}."
        );

        $permissionNames = DB::table('permissions')->pluck('name')->all();
        $joined = strtolower(implode(' ', $permissionNames));

        $domains = [
            ['pos', 'kasir', 'transaksi', 'pesanan'],
            ['inventory', 'gudang', 'bahan', 'stok'],
            ['user', 'pengguna', 'role', 'akses'],
            ['report', 'laporan', 'faktur', 'keuangan'],
        ];

        foreach ($domains as $domainGroup) {
            $hasMatch = false;
            foreach ($domainGroup as $keyword) {
                if (str_contains($joined, $keyword)) {
                    $hasMatch = true;
                    break;
                }
            }
            $this->assertTrue(
                $hasMatch,
                "Permissions catalog must include security permissions for domain group [" . implode('/', $domainGroup) . "]."
            );
        }
    }

    /**
     * 3. Assert standard workspace operational roles are cataloged and available.
     */
    public function test_standard_workspace_roles_exist(): void
    {
        $roleNames = DB::table('roles')->pluck('name')->map(fn ($r) => strtolower((string) $r))->all();
        $workspaceRoles = DB::table('business_users')->pluck('role')->unique()->all();

        $allRoles = array_merge($roleNames, $workspaceRoles);
        $roleString = implode(' ', $allRoles);

        $expectedKeywords = ['owner', 'cashier', 'kasir', 'manager', 'staff', 'warehouse', 'gudang'];
        $matchedCount = 0;

        foreach ($expectedKeywords as $kw) {
            if (str_contains(strtolower($roleString), $kw)) {
                $matchedCount++;
            }
        }

        $this->assertGreaterThanOrEqual(
            4,
            $matchedCount,
            "Workspace must define standard roles (owner, manager, cashier, warehouse, staff)."
        );
    }

    /**
     * 4. Assert Superadmin platform governance role is defined.
     */
    public function test_superadmin_guard_or_role_defined(): void
    {
        $hasSuperadmin = DB::table('roles')
            ->where(function ($q) {
                $q->where('name', 'LIKE', '%super%')
                    ->orWhere('name', 'LIKE', '%admin%');
            })
            ->exists();

        $this->assertTrue(
            $hasSuperadmin,
            "Platform must define a Super Administrator or Admin governance role."
        );
    }

    /**
     * 5. Assert user role assignments in business_users table connect users to businesses.
     */
    public function test_user_role_assignment(): void
    {
        $membershipCount = DB::table('business_users')->count();
        $this->assertGreaterThan(
            0,
            $membershipCount,
            "Expected business memberships to exist linking users to businesses."
        );

        $validAssignment = DB::table('business_users')
            ->whereNotNull('business_id')
            ->whereNotNull('user_id')
            ->whereNotNull('role')
            ->exists();

        $this->assertTrue(
            $validAssignment,
            "business_users table must contain valid user_id, business_id, and role bindings."
        );
    }
}
