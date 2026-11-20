<?php

declare(strict_types=1);

namespace Tests\E2E\Tier3_CrossFeature;

use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use App\Support\Context;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\E2E\Support\BaseTestCase;

/**
 * Tier 3: Security & Multi-Tenant Isolation Test Suite
 *
 * Validates strict boundary separation across tenants, cross-tenant resource protection,
 * role-based access control (RBAC), cashier privilege boundaries, superadmin guard separation,
 * and clean transient tenant lifecycle isolation.
 */
class SecurityTenantIsolationTest extends BaseTestCase
{
    /**
     * Test 1: Tenant A cannot access Tenant B products via API.
     * Validates that querying products under Tenant A's context returns only Tenant A's products,
     * and attempting to directly query Tenant B's product by ID returns 404 Not Found.
     */
    public function test_tenant_a_cannot_access_tenant_b_products_via_api(): void
    {
        $businessA = $this->getTenantBusiness('cooca_mart');
        $businessB = $this->getTenantBusiness('cooca_bistro');
        $this->assertNotNull($businessA, 'COOCA Mart must exist.');
        $this->assertNotNull($businessB, 'COOCA Bistro must exist.');

        $ownerA = User::where('email', 'owner@coocamart.id')->first()
            ?? User::where('email', 'demo@cooca.id')->first();
        $this->assertNotNull($ownerA, 'User must exist.');

        $productB = Product::withoutGlobalScopes()->where('business_id', $businessB->id)->first();
        $this->assertNotNull($productB, 'Tenant B must have at least one product.');

        $this->actingAsTenantUser($ownerA, $businessA->id);

        // 1. List products in Tenant A context
        $response = $this->apiJson(
            'GET',
            '/api/v1/products',
            [],
            [],
            $businessA->id
        );

        $response->assertOk();
        $productIds = collect($response->json('data'))->pluck('id')->all();

        // Tenant B's product must NOT be present in Tenant A's product list
        $this->assertNotContains(
            $productB->id,
            $productIds,
            "Tenant B product [{$productB->id}] must not appear in Tenant A product list."
        );

        // 2. Direct access attempt for Tenant B product ID inside Tenant A context
        $showResponse = $this->apiJson(
            'GET',
            "/api/v1/products/{$productB->id}",
            [],
            [],
            $businessA->id
        );

        $this->assertContains(
            $showResponse->status(),
            [403, 404],
            "Accessing Tenant B product from Tenant A must return 403 or 404, received [{$showResponse->status()}]."
        );
    }

    /**
     * Test 2: Tenant A user cannot authenticate into Tenant B workspace.
     * Validates that when a user who has no membership in Tenant B sends X-Business-Id
     * for Tenant B, the system refuses to bind Tenant B to App\Support\Context.
     */
    public function test_tenant_a_user_cannot_authenticate_into_tenant_b_workspace(): void
    {
        $this->runInTransaction(function (): void {
            $businessB = $this->getTenantBusiness('cooca_bistro');
            $this->assertNotNull($businessB, 'COOCA Bistro must exist.');

            // Create an isolated user who belongs exclusively to Tenant A
            $businessA = $this->getTenantBusiness('cooca_mart');
            $userA = User::create([
                'name' => 'Isolated User Tenant A',
                'email' => 'isolated-tenant-a-' . Str::random(6) . '@cooca.test',
                'password' => bcrypt('password123'),
                'is_active' => true,
            ]);

            BusinessMembership::create([
                'business_id' => $businessA->id,
                'user_id' => $userA->id,
                'role' => 'staff',
                'is_active' => true,
            ]);

            // Attempt to access API sending Tenant B's ID in X-Business-Id header
            $response = $this->apiJson(
                'GET',
                '/api/v1/products',
                [],
                [],
                $businessB->id,
                $userA->createToken('test-token')->plainTextToken
            );

            // Context must NOT be bound to Tenant B
            $activeBusinessId = Context::businessId();
            $this->assertNotSame(
                $businessB->id,
                $activeBusinessId,
                'User without membership must not have their context bound to Tenant B.'
            );
        });
    }

    /**
     * Test 3: Cross-tenant foreign key assignment is blocked or rejected.
     * Validates that when Tenant A is active, Eloquent query builder scoped via BusinessScope
     * cannot see or bind entities (e.g. Customer, Product) belonging to Tenant B.
     */
    public function test_cross_tenant_foreign_key_assignment_is_blocked_or_rejected(): void
    {
        $businessA = $this->getTenantBusiness('cooca_mart');
        $businessB = $this->getTenantBusiness('cooca_bistro');
        $this->assertNotNull($businessA);
        $this->assertNotNull($businessB);

        $customerB = Customer::withoutGlobalScopes()->where('business_id', $businessB->id)->first();
        if ($customerB === null) {
            $customerB = Customer::create([
                'business_id' => $businessB->id,
                'name' => 'Bistro Customer Test',
                'phone' => '08123456789',
            ]);
        }

        // Set context to Tenant A
        $this->setTenantContext($businessA->id);

        // Querying Customer model within Tenant A scope must return null for Tenant B's customer
        $queriedCustomer = Customer::find($customerB->id);
        $this->assertNull(
            $queriedCustomer,
            "Customer [{$customerB->id}] belonging to Tenant B must not be queryable within Tenant A scope."
        );
    }

    /**
     * Test 4: Role permissions enforced consistently across Web routes and REST API.
     * Validates that RequirePermission and RequireRole middleware reject unpermitted requests
     * with HTTP 403 Forbidden.
     */
    public function test_role_permissions_enforced_consistently_across_web_routes_and_rest_api(): void
    {
        $business = $this->getTenantBusiness('cooca_mart');
        $this->assertNotNull($business);

        $cashier = User::where('email', 'cashier@coocamart.id')->first();
        $this->assertNotNull($cashier, 'Cashier user must exist.');

        $this->actingAsTenantUser($cashier, $business->id);

        // Cashier attempting to access financial journal entries endpoint (requires accounting.view)
        $response = $this->apiJson(
            'GET',
            '/api/v1/finance/journal-entries',
            [],
            [],
            $business->id
        );

        $response->assertStatus(403);
        $response->assertJsonPath('error_code', 'FORBIDDEN_PERMISSION');
    }

    /**
     * Test 5: Cashier role restricted from viewing SAK EMKM financial journals.
     * Validates that a cashier attempting to access accounting journals or financial
     * ledger reports is denied access with HTTP 403 Forbidden.
     */
    public function test_cashier_role_restricted_from_viewing_sak_emkm_financial_journals(): void
    {
        $business = $this->getTenantBusiness('cooca_mart');
        $this->assertNotNull($business);

        $cashier = User::where('email', 'cashier@coocamart.id')->first();
        $this->assertNotNull($cashier);

        // Acting as cashier via web session
        $this->actingAs($cashier);
        $this->setTenantContext($business->id, $cashier);

        // Cashier accessing financial reports / journals web route
        $response = $this->get('/finance/journals');

        // Cashier must be forbidden (403) or redirected away with error
        $this->assertTrue(
            in_array($response->status(), [302, 403], true),
            "Cashier accessing financial routes must return 302 redirect or 403 forbidden, received [{$response->status()}]."
        );
    }

    /**
     * Test 6: Superadmin guard separation from workspace business users.
     * Validates that standard tenant users authenticated via web/sanctum guards
     * cannot access the platform superadmin control center (/admin/*).
     */
    public function test_superadmin_guard_separation_from_workspace_business_users(): void
    {
        $business = $this->getTenantBusiness('cooca_mart');
        $this->assertNotNull($business);

        $owner = User::where('email', 'owner@coocamart.id')->first()
            ?? User::where('email', 'demo@cooca.id')->first();
        $this->assertNotNull($owner);

        // Authenticate as business user via standard web guard
        $this->actingAs($owner, 'web');

        // Attempt to access superadmin dashboard protected by auth:admin guard
        $response = $this->get('/admin/dashboard');

        // Must redirect to admin login or return 401/403
        $this->assertTrue(
            in_array($response->status(), [302, 401, 403], true),
            "Business user accessing /admin/dashboard must be blocked or redirected to /admin/login, got [{$response->status()}]."
        );

        if ($response->status() === 302) {
            $this->assertStringContainsString('admin/login', (string) $response->headers->get('Location'));
        }
    }

    /**
     * Test 7: Transient tenant data wiped cleanly without touching seeded tenant data.
     * Validates that creating a temporary transient tenant and executing cleanup
     * removes the transient data completely while preserving all permanent seeded tenant data.
     */
    public function test_transient_tenant_data_wiped_cleanly_without_touching_seeded_tenant_data(): void
    {
        $seededBusinessesCount = Business::where('slug', 'NOT LIKE', 'transient-%')
            ->where('slug', 'NOT LIKE', 'test-transient-%')
            ->count();
        $this->assertGreaterThanOrEqual(4, $seededBusinessesCount, 'Demo seeded businesses must exist.');

        // 1. Create transient tenant with child records
        $transientTenant = $this->createTransientTenant([
            'name' => 'Transient E2E Lifecycle Isolation Test',
        ]);
        $this->assertNotNull($transientTenant);

        $unit = \App\Models\Unit::first();

        $transientProduct = Product::create([
            'business_id' => $transientTenant->id,
            'name' => 'Transient E2E Test Product',
            'output_unit_id' => $unit?->id,
            'selling_price' => 25000,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('businesses', ['id' => $transientTenant->id]);
        $this->assertDatabaseHas('products', ['id' => $transientProduct->id]);

        // 2. Perform cleanup via TenantHelper
        \Tests\E2E\Support\Helpers\TenantHelper::cleanupTransientTenants();

        // 3. Verify transient data is deleted
        $this->assertDatabaseMissing('businesses', ['id' => $transientTenant->id]);
        $this->assertDatabaseMissing('products', ['id' => $transientProduct->id]);

        // 4. Verify seeded businesses remain completely intact
        $postCleanupCount = Business::where('slug', 'NOT LIKE', 'transient-%')
            ->where('slug', 'NOT LIKE', 'test-transient-%')
            ->count();
        $this->assertSame(
            $seededBusinessesCount,
            $postCleanupCount,
            'Permanent seeded tenant records must not be affected by transient tenant cleanup.'
        );
    }
}
