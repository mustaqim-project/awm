<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\BusinessSubscription;
use App\Models\EmployeeLoan;
use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\MaterialCategory;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Role;
use App\Models\StockAdjustment;
use App\Models\Unit;
use App\Models\User;
use App\Models\WorkShift;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

final class AdversarialSecurityChallengerTest extends TestCase
{
    use RefreshDatabase;

    private Business $tenantA;
    private Business $tenantB;
    private User $tenantAOwner;
    private User $tenantBOwner;
    private User $tenantAManager;
    private User $tenantBStaff;

    private User $viewerMaterials;
    private User $viewerProducts;
    private User $viewerInventory;
    private User $viewerImport;
    private User $viewerHrm;
    private User $unauthorizedUser;

    private Location $locationA;
    private Location $locationB;
    private Product $productA;
    private Product $productB;
    private BusinessMembership $membershipB;
    private AttendanceCorrection $correctionB;
    private StockAdjustment $adjustmentB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RbacSeeder::class);

        // Setup Tenant A
        $this->tenantA = Business::create([
            'name' => 'Adversarial Probe Tenant A',
            'currency' => 'IDR',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'business_scale' => Business::SCALE_CORPORATE,
            'pos_supervisor_pin' => Hash::make('123456'),
        ]);

        BusinessSubscription::updateOrCreate(
            ['business_id' => $this->tenantA->id],
            [
                'plan_code' => BusinessSubscription::PLAN_CORE_MONTHLY,
                'status' => BusinessSubscription::STATUS_ACTIVE,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addMonth(),
            ]
        );

        // Setup Tenant B
        $this->tenantB = Business::create([
            'name' => 'Adversarial Probe Tenant B',
            'currency' => 'IDR',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'business_scale' => Business::SCALE_CORPORATE,
            'pos_supervisor_pin' => Hash::make('654321'),
        ]);

        BusinessSubscription::updateOrCreate(
            ['business_id' => $this->tenantB->id],
            [
                'plan_code' => BusinessSubscription::PLAN_CORE_MONTHLY,
                'status' => BusinessSubscription::STATUS_ACTIVE,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addMonth(),
            ]
        );

        // Locations
        $this->locationA = Location::create([
            'business_id' => $this->tenantA->id,
            'name' => 'Warehouse A',
            'slug' => 'warehouse-a',
            'code' => 'WHA',
            'type' => 'warehouse',
            'is_active' => true,
        ]);

        $this->locationB = Location::create([
            'business_id' => $this->tenantB->id,
            'name' => 'Warehouse B',
            'slug' => 'warehouse-b',
            'code' => 'WHB',
            'type' => 'warehouse',
            'is_active' => true,
        ]);

        // Units
        $unitA = Unit::firstOrCreate(['code' => 'pcs'], ['name' => 'Pieces', 'category' => 'quantity', 'is_base' => true]);

        // Products
        $this->productA = Product::create([
            'business_id' => $this->tenantA->id,
            'output_unit_id' => $unitA->id,
            'name' => 'Product Tenant A',
            'code' => 'PRD-A-01',
            'type' => Product::TYPE_GOODS,
            'selling_price' => 50000,
            'base_cost' => 30000,
            'is_active' => true,
        ]);

        $this->productB = Product::create([
            'business_id' => $this->tenantB->id,
            'output_unit_id' => $unitA->id,
            'name' => 'Product Tenant B',
            'code' => 'PRD-B-01',
            'type' => Product::TYPE_GOODS,
            'selling_price' => 60000,
            'base_cost' => 35000,
            'is_active' => true,
        ]);

        // Tenant A Owner & Manager
        $this->tenantAOwner = $this->createTenantUser($this->tenantA, 'owner', 'Owner Tenant A');
        $this->tenantAManager = $this->createTenantUserWithPermissions($this->tenantA, [
            'hrm.view', 'hrm.manage', 'hrm.payroll.manage', 'hrm.attendance.manage',
            'import.view', 'import.manage',
            'inventory.view', 'inventory.manage',
            'products.view', 'products.manage',
            'materials.view', 'materials.create',
        ], 'Manager Tenant A');

        // Tenant B Owner & Staff
        $this->tenantBOwner = $this->createTenantUser($this->tenantB, 'owner', 'Owner Tenant B');
        $this->tenantBStaff = $this->createTenantUser($this->tenantB, 'warehouse', 'Warehouse Staff Tenant B');

        $this->membershipB = BusinessMembership::where('business_id', $this->tenantB->id)
            ->where('user_id', $this->tenantBStaff->id)
            ->firstOrFail();

        // Tenant B correction
        $this->correctionB = AttendanceCorrection::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->tenantB->id,
            'user_id' => $this->tenantBStaff->id,
            'correction_number' => 'COR-B-001',
            'target_date' => now()->toDateString(),
            'correction_type' => AttendanceCorrection::TYPE_CLOCK_IN_ONLY,
            'proposed_clock_in' => '08:00',
            'reason' => 'Fingerprint error at Tenant B',
            'status' => AttendanceCorrection::STATUS_PENDING,
        ]);

        // Tenant B pending stock adjustment
        $this->adjustmentB = StockAdjustment::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->tenantB->id,
            'location_id' => $this->locationB->id,
            'adjustment_number' => 'ADJ-B-001',
            'adjustment_date' => now()->toDateString(),
            'reason' => 'damaged',
            'status' => 'pending_approval',
            'total_loss_cost' => 1500000,
            'created_by' => $this->tenantBStaff->id,
        ]);

        // Read-only viewers in Tenant A
        $this->viewerMaterials = $this->createTenantUserWithPermissions($this->tenantA, ['materials.view'], 'Viewer Materials');
        $this->viewerProducts = $this->createTenantUserWithPermissions($this->tenantA, ['products.view'], 'Viewer Products');
        $this->viewerInventory = $this->createTenantUserWithPermissions($this->tenantA, ['inventory.view'], 'Viewer Inventory');
        $this->viewerImport = $this->createTenantUserWithPermissions($this->tenantA, ['import.view'], 'Viewer Import');
        $this->viewerHrm = $this->createTenantUserWithPermissions($this->tenantA, ['hrm.view'], 'Viewer HRM');

        // Cashier in Tenant A with zero management permissions
        $this->unauthorizedUser = $this->createTenantUser($this->tenantA, 'cashier', 'Cashier A');
    }

    private function createTenantUser(Business $business, string $roleSlug, string $name): User
    {
        $user = User::create([
            'name' => $name,
            'email' => Str::slug($name) . '@challenger.test',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);

        $business->users()->attach($user->id, [
            'id' => (string) Str::uuid(),
            'role' => $roleSlug,
            'is_active' => true,
        ]);

        $user->update(['active_business_id' => $business->id]);
        return $user;
    }

    private function createTenantUserWithPermissions(Business $business, array $permissionSlugs, string $name): User
    {
        $user = User::create([
            'name' => $name,
            'email' => Str::slug($name) . '@challenger.test',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);

        $role = Role::create([
            'name' => $name . ' Role',
            'slug' => 'role-' . Str::slug($name) . '-' . Str::random(5),
            'business_id' => $business->id,
        ]);

        $permissionIds = Permission::whereIn('slug', $permissionSlugs)->pluck('id');
        $role->permissions()->attach($permissionIds);

        $business->users()->attach($user->id, [
            'id' => (string) Str::uuid(),
            'role' => $role->slug,
            'role_id' => $role->id,
            'is_active' => true,
        ]);

        $user->update(['active_business_id' => $business->id]);
        return $user;
    }

    /**
     * CHALLENGE 1: Read-only viewers MUST be rejected with HTTP 403 on ALL import execute routes.
     */
    public function test_read_only_viewers_cannot_execute_import_mutations(): void
    {
        $payload = [
            'rows' => json_encode([
                ['row_number' => 2, 'status' => 'valid', 'name' => 'Penyusup', 'unit' => 'pcs', 'price' => 1000],
            ]),
            'duplicate_strategy' => 'skip',
        ];

        $viewers = [
            'materials.view only' => $this->viewerMaterials,
            'products.view only' => $this->viewerProducts,
            'inventory.view only' => $this->viewerInventory,
            'import.view only' => $this->viewerImport,
        ];

        $endpoints = [
            'import.materials.execute',
            'import.products.execute',
            'import.recipes.execute',
            'import.inventory.execute',
        ];

        foreach ($viewers as $viewerLabel => $viewerUser) {
            foreach ($endpoints as $endpoint) {
                $response = $this->actingAs($viewerUser)->postJson(route($endpoint), $payload);
                $this->assertEquals(
                    403,
                    $response->status(),
                    "Adversarial breach detected: {$viewerLabel} was able to execute {$endpoint} with status {$response->status()}!"
                );
                $this->assertEquals('FORBIDDEN_PERMISSION', $response->json('error_code'));
            }
        }
    }

    /**
     * CHALLENGE 2: HRM API endpoints strictly reject unauthorized members with 403.
     */
    public function test_hrm_api_rejects_unauthorized_and_viewer_only_members_from_mutations(): void
    {
        // 1. Shift creation by cashier -> 403
        $shiftPayload = [
            'name' => 'Shift Ilegal',
            'start_time' => '07:00',
            'end_time' => '15:00',
        ];
        $resShift = $this->actingAs($this->unauthorizedUser)->postJson('/api/v1/hrm/shifts', $shiftPayload);
        $resShift->assertStatus(403)->assertJsonPath('error_code', 'FORBIDDEN_PERMISSION');

        // 2. Shift creation by hrm.view only -> 403
        $resShiftViewer = $this->actingAs($this->viewerHrm)->postJson('/api/v1/hrm/shifts', $shiftPayload);
        $resShiftViewer->assertStatus(403)->assertJsonPath('error_code', 'FORBIDDEN_PERMISSION');

        // 3. Employee creation by hrm.view only -> 403
        $resEmpViewer = $this->actingAs($this->viewerHrm)->postJson('/api/v1/hrm/employees', [
            'name' => 'Fake Employee',
            'email' => 'fake@challenger.test',
        ]);
        $resEmpViewer->assertStatus(403)->assertJsonPath('error_code', 'FORBIDDEN_PERMISSION');

        // 4. Employee update by hrm.view only -> 403
        $membershipA = BusinessMembership::where('business_id', $this->tenantA->id)
            ->where('user_id', $this->unauthorizedUser->id)
            ->firstOrFail();
        $resUpdateViewer = $this->actingAs($this->viewerHrm)->putJson('/api/v1/hrm/employees/' . $membershipA->id, [
            'base_salary' => 99000000,
        ]);
        $resUpdateViewer->assertStatus(403)->assertJsonPath('error_code', 'FORBIDDEN_PERMISSION');

        // 5. Loan creation by hrm.view only -> 403
        $resLoanViewer = $this->actingAs($this->viewerHrm)->postJson('/api/v1/hrm/loans', [
            'user_id' => $this->unauthorizedUser->id,
            'amount' => 5000000,
            'tenor_months' => 5,
        ]);
        $resLoanViewer->assertStatus(403)->assertJsonPath('error_code', 'FORBIDDEN_PERMISSION');

        // 6. Correction approval by hrm.view only -> 403
        $correctionA = AttendanceCorrection::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->tenantA->id,
            'user_id' => $this->unauthorizedUser->id,
            'correction_number' => 'COR-A-001',
            'reason' => 'Attendance error',
            'target_date' => now()->toDateString(),
            'correction_type' => AttendanceCorrection::TYPE_CLOCK_IN_ONLY,
            'status' => AttendanceCorrection::STATUS_PENDING,
        ]);
        $resApproveViewer = $this->actingAs($this->viewerHrm)->postJson('/api/v1/hrm/corrections/' . $correctionA->id . '/approve');
        $resApproveViewer->assertStatus(403)->assertJsonPath('error_code', 'FORBIDDEN_PERMISSION');

        // 7. Correction rejection by unauthorized user -> 403
        $resRejectUnauth = $this->actingAs($this->unauthorizedUser)->postJson('/api/v1/hrm/corrections/' . $correctionA->id . '/reject');
        $resRejectUnauth->assertStatus(403)->assertJsonPath('error_code', 'FORBIDDEN_PERMISSION');
    }

    /**
     * CHALLENGE 3: Multi-tenant boundaries cannot be crossed.
     */
    public function test_multi_tenant_isolation_strictly_prevents_cross_tenant_access_and_mutations(): void
    {
        // 1. Cross-tenant employee mutation: Tenant A Manager attempts to mutate Tenant B employee
        $resMutateEmp = $this->actingAs($this->tenantAManager)
            ->putJson('/api/v1/hrm/employees/' . $this->membershipB->id, [
                'job_title' => 'Compromised Staff',
                'base_salary' => 99999999,
            ]);
        $this->assertEquals(404, $resMutateEmp->status(), 'Cross-tenant employee mutation did not abort 404!');

        // Verify Tenant B employee untouched
        $this->assertNotEquals(99999999, (float) $this->membershipB->fresh()->base_salary);

        // 2. Cross-tenant attendance correction approval: Tenant A Manager attempts to approve Tenant B correction
        $resApproveCorr = $this->actingAs($this->tenantAManager)
            ->postJson('/api/v1/hrm/corrections/' . $this->correctionB->id . '/approve');
        $this->assertEquals(404, $resApproveCorr->status(), 'Cross-tenant attendance approval did not abort 404!');
        $this->assertEquals(AttendanceCorrection::STATUS_PENDING, $this->correctionB->fresh()->status);

        // 3. Cross-tenant inventory adjustment approval: Tenant A Manager attempts to approve Tenant B adjustment
        $resApproveAdj = $this->actingAs($this->tenantAManager)
            ->postJson(route('inventory.adjustments.approve', $this->adjustmentB->id));
        $this->assertEquals(404, $resApproveAdj->status(), 'Cross-tenant stock adjustment approval did not abort 404!');
        $this->assertEquals('pending_approval', $this->adjustmentB->fresh()->status);

        // 4. Cross-tenant stock adjustment reject: Tenant A Manager attempts to reject Tenant B adjustment
        $resRejectAdj = $this->actingAs($this->tenantAManager)
            ->postJson(route('inventory.adjustments.reject', $this->adjustmentB->id), [
                'reason' => 'Tenant A attack',
            ]);
        $this->assertEquals(404, $resRejectAdj->status(), 'Cross-tenant stock adjustment rejection did not abort 404!');
        $this->assertEquals('pending_approval', $this->adjustmentB->fresh()->status);

        // 5. Cross-tenant quick stock adjust: Tenant A Manager attempts to adjust stock at Tenant B's location
        $resQuickAdj = $this->actingAs($this->tenantAManager)
            ->withSession([
                'auth_wa_otp_verified_user_id' => $this->tenantAManager->id,
                'active_business_id' => $this->tenantA->id,
            ])
            ->postJson(route('inventory.stocks.adjust'), [
                'location_id' => $this->locationB->id, // Tenant B location!
                'product_id' => $this->productA->id,
                'new_quantity' => 100,
                'unit_cost' => 30000,
                'reason_code' => 'initial_balance',
            ]);
        $this->assertEquals(422, $resQuickAdj->status(), 'Cross-tenant location injection was not blocked by validation!');
        $resQuickAdj->assertJsonValidationErrors(['location_id']);

        // 6. Cross-tenant quick stock adjust: Tenant A Manager attempts to adjust Tenant B's product
        $resQuickAdjProd = $this->actingAs($this->tenantAManager)
            ->withSession([
                'auth_wa_otp_verified_user_id' => $this->tenantAManager->id,
                'active_business_id' => $this->tenantA->id,
            ])
            ->postJson(route('inventory.stocks.adjust'), [
                'location_id' => $this->locationA->id,
                'product_id' => $this->productB->id, // Tenant B product!
                'new_quantity' => 100,
                'unit_cost' => 30000,
                'reason_code' => 'initial_balance',
            ]);
        $this->assertEquals(422, $resQuickAdjProd->status(), 'Cross-tenant product injection was not blocked by validation!');
        $resQuickAdjProd->assertJsonValidationErrors(['product_id']);
    }

    /**
     * CHALLENGE 4: Inventory Maker-Checker tampering and self-approval attacks.
     */
    public function test_inventory_maker_checker_prevents_privilege_escalation_and_tampering(): void
    {
        // 0. Unauthorized cashier without inventory.manage -> 403 Forbidden
        $resUnauthAdjust = $this->actingAs($this->unauthorizedUser)
            ->withSession([
                'auth_wa_otp_verified_user_id' => $this->unauthorizedUser->id,
                'active_business_id' => $this->tenantA->id,
            ])
            ->postJson(route('inventory.stocks.adjust'), [
                'location_id' => $this->locationA->id,
                'product_id' => $this->productA->id,
                'new_quantity' => 10,
            ]);
        $resUnauthAdjust->assertStatus(403);

        // 1. Staff attempts to set negative unit_cost -> 422 Unprocessable
        $resNegativeCost = $this->actingAs($this->tenantBStaff)
            ->withSession([
                'auth_wa_otp_verified_user_id' => $this->tenantBStaff->id,
                'active_business_id' => $this->tenantB->id,
            ])
            ->postJson(route('inventory.stocks.adjust'), [
                'location_id' => $this->locationB->id,
                'product_id' => $this->productB->id,
                'new_quantity' => 5,
                'unit_cost' => -50000,
                'reason_code' => 'damaged',
            ]);
        $resNegativeCost->assertStatus(422);
        $resNegativeCost->assertJsonValidationErrors(['unit_cost']);

        // 2. Staff attempts to approve their own pending adjustment -> 403 Forbidden
        $resSelfApprove = $this->actingAs($this->tenantBStaff)
            ->withSession([
                'auth_wa_otp_verified_user_id' => $this->tenantBStaff->id,
                'active_business_id' => $this->tenantB->id,
            ])
            ->postJson(route('inventory.adjustments.approve', $this->adjustmentB->id));
        $resSelfApprove->assertStatus(403);
        $this->assertEquals('pending_approval', $this->adjustmentB->fresh()->status);

        // 3. Staff attempts to reject their own pending adjustment -> 403 Forbidden
        $resSelfReject = $this->actingAs($this->tenantBStaff)
            ->withSession([
                'auth_wa_otp_verified_user_id' => $this->tenantBStaff->id,
                'active_business_id' => $this->tenantB->id,
            ])
            ->postJson(route('inventory.adjustments.reject', $this->adjustmentB->id), [
                'reason' => 'Staff self rejection',
            ]);
        $resSelfReject->assertStatus(403);
        $this->assertEquals('pending_approval', $this->adjustmentB->fresh()->status);
    }
}
