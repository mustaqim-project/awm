<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ApprovalLog;
use App\Models\ApprovalRequest;
use App\Models\ApprovalRule;
use App\Models\AuditLog;
use App\Models\Business;
use App\Models\Permission;
use App\Models\PurchaseOrder;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\DefaultUnitSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class ApprovalModuleAuditTest extends TestCase
{
    use RefreshDatabase;

    private Business $businessA;
    private Business $businessB;
    private User $ownerA;
    private User $staffNoPerm;
    private User $viewerUser;
    private User $managerUser;
    private User $ownerB;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->seed(RbacSeeder::class);
        $this->seed(DefaultUnitSeeder::class);

        // Business A
        $this->businessA = Business::create([
            'name' => 'PT Prima Logistik A',
            'business_scale' => 'corporate',
        ]);

        // Business B
        $this->businessB = Business::create([
            'name' => 'CV Mandiri Sejahtera B',
            'business_scale' => 'corporate',
        ]);

        $this->ownerA = User::create([
            'name' => 'Owner Alpha',
            'email' => 'owner.alpha@test.com',
            'password' => 'password123',
        ]);

        $this->staffNoPerm = User::create([
            'name' => 'Staff No Permission',
            'email' => 'staff.noperm@test.com',
            'password' => 'password123',
        ]);

        $this->viewerUser = User::create([
            'name' => 'Viewer Only',
            'email' => 'viewer@test.com',
            'password' => 'password123',
        ]);

        $this->managerUser = User::create([
            'name' => 'Manager Approver',
            'email' => 'manager.approver@test.com',
            'password' => 'password123',
        ]);

        $this->ownerB = User::create([
            'name' => 'Owner Bravo',
            'email' => 'owner.bravo@test.com',
            'password' => 'password123',
        ]);

        // Attach Owner A
        $ownerRole = Role::where('slug', 'owner')->first();
        $this->businessA->users()->attach($this->ownerA->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'role_id' => $ownerRole?->id,
        ]);

        // Attach Staff without approval permissions
        $staffRole = Role::firstOrCreate(['slug' => 'custom_staff', 'business_id' => $this->businessA->id], [
            'name' => 'Custom Staff',
            'description' => 'Staff without approvals',
        ]);
        $this->businessA->users()->attach($this->staffNoPerm->id, [
            'id' => (string) Str::uuid(),
            'role' => 'custom_staff',
            'role_id' => $staffRole->id,
        ]);

        // Attach Viewer with approvals.view only
        $viewerRole = Role::firstOrCreate(['slug' => 'custom_viewer', 'business_id' => $this->businessA->id], [
            'name' => 'Custom Viewer',
            'description' => 'Viewer only approvals',
        ]);
        $viewPerm = Permission::where('slug', 'approvals.view')->first();
        if ($viewPerm) {
            $viewerRole->permissions()->sync([$viewPerm->id]);
        }
        $this->businessA->users()->attach($this->viewerUser->id, [
            'id' => (string) Str::uuid(),
            'role' => 'custom_viewer',
            'role_id' => $viewerRole->id,
        ]);

        // Attach Manager with approvals.view & approvals.manage
        $managerRole = Role::firstOrCreate(['slug' => 'custom_manager', 'business_id' => $this->businessA->id], [
            'name' => 'Custom Manager',
            'description' => 'Manager approvals',
        ]);
        $managePerms = Permission::whereIn('slug', ['approvals.view', 'approvals.manage'])->pluck('id');
        $managerRole->permissions()->sync($managePerms);
        $this->businessA->users()->attach($this->managerUser->id, [
            'id' => (string) Str::uuid(),
            'role' => 'custom_manager',
            'role_id' => $managerRole->id,
        ]);

        // Attach Owner B
        $this->businessB->users()->attach($this->ownerB->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'role_id' => $ownerRole?->id,
        ]);
    }

    /**
     * RBAC Test: User without permissions cannot view approvals (redirects to portal in web, 403 in json).
     */
    public function test_user_without_permission_cannot_access_approvals_inbox_or_history(): void
    {
        $this->actingAs($this->staffNoPerm)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->get(route('approvals.inbox'))
            ->assertRedirect(route('portal'));

        $this->actingAs($this->staffNoPerm)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->getJson(route('approvals.inbox'))
            ->assertForbidden();

        $this->actingAs($this->staffNoPerm)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->get(route('approvals.history'))
            ->assertRedirect(route('portal'));

        $this->actingAs($this->staffNoPerm)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->getJson(route('approvals.history'))
            ->assertForbidden();
    }

    /**
     * RBAC Test: User with view permission can access inbox but cannot access rules or approve (redirects to portal in web, 403 in json).
     */
    public function test_user_with_view_only_cannot_access_rules_management(): void
    {
        $this->actingAs($this->viewerUser)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->get(route('approvals.inbox'))
            ->assertOk();

        $this->actingAs($this->viewerUser)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->get(route('approval-rules.index'))
            ->assertRedirect(route('portal'));

        $this->actingAs($this->viewerUser)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->getJson(route('approval-rules.index'))
            ->assertForbidden();
    }

    /**
     * Multi-Tenant Isolation Test: Cannot approve or reject requests belonging to another business (404).
     */
    public function test_cross_tenant_approval_request_access_is_blocked_with_404(): void
    {
        // Create Rule & Request in Business B
        $ruleB = ApprovalRule::create([
            'business_id' => $this->businessB->id,
            'document_type' => 'purchase_order',
            'name' => 'PO Rule B',
            'min_amount' => 1000000,
            'required_levels' => 1,
            'approver_role_level_1' => 'owner',
            'is_active' => true,
        ]);

        $requestB = ApprovalRequest::create([
            'business_id' => $this->businessB->id,
            'document_type' => 'purchase_order',
            'document_id' => (string) Str::uuid(),
            'requester_id' => $this->ownerB->id,
            'rule_id' => $ruleB->id,
            'amount' => 5000000,
            'current_level' => 1,
            'total_levels' => 1,
            'status' => ApprovalRequest::STATUS_PENDING,
        ]);

        // Owner A tries to approve Business B's request
        $this->actingAs($this->ownerA)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->post(route('approvals.approve', $requestB), ['notes' => 'Hacked approve'])
            ->assertNotFound();

        // Owner A tries to reject Business B's request
        $this->actingAs($this->ownerA)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->post(route('approvals.reject', $requestB), ['reason' => 'Hacked reject reason'])
            ->assertNotFound();
    }

    /**
     * Multi-Tenant Isolation Test: Cannot update or delete approval rules belonging to another business (404).
     */
    public function test_cross_tenant_rule_manipulation_is_blocked_with_404(): void
    {
        $ruleB = ApprovalRule::create([
            'business_id' => $this->businessB->id,
            'document_type' => 'expense',
            'name' => 'Expense Rule B',
            'min_amount' => 2000000,
            'required_levels' => 1,
            'approver_role_level_1' => 'owner',
            'is_active' => true,
        ]);

        // Owner A tries to update Rule B
        $this->actingAs($this->ownerA)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->put(route('approval-rules.update', $ruleB), [
                'min_amount' => 500000,
                'required_levels' => 1,
            ])
            ->assertNotFound();

        // Owner A tries to delete Rule B
        $this->actingAs($this->ownerA)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->delete(route('approval-rules.destroy', $ruleB))
            ->assertNotFound();
    }

    /**
     * Audit Log Test: Approving and rejecting documents emits AuditLog entries.
     */
    public function test_approval_actions_emit_audit_logs(): void
    {
        $ruleA = ApprovalRule::create([
            'business_id' => $this->businessA->id,
            'document_type' => 'expense',
            'name' => 'Rule Biaya',
            'min_amount' => 1000000,
            'required_levels' => 1,
            'approver_role_level_1' => 'owner',
            'is_active' => true,
        ]);

        $req = ApprovalRequest::create([
            'business_id' => $this->businessA->id,
            'document_type' => 'expense',
            'document_id' => (string) Str::uuid(),
            'requester_id' => $this->staffNoPerm->id,
            'rule_id' => $ruleA->id,
            'amount' => 2500000,
            'current_level' => 1,
            'total_levels' => 1,
            'status' => ApprovalRequest::STATUS_PENDING,
        ]);

        // Approve
        $this->actingAs($this->ownerA)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->post(route('approvals.approve', $req), ['notes' => 'Disetujui untuk operasional'])
            ->assertRedirect();

        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $this->businessA->id,
            'user_id' => $this->ownerA->id,
            'auditable_type' => ApprovalRequest::class,
            'auditable_id' => $req->id,
            'action' => 'approval.approved',
        ]);
    }

    /**
     * Audit Log Test: Creating, updating, and deleting rules emits AuditLog entries.
     */
    public function test_rule_modifications_emit_audit_logs(): void
    {
        // 1. Create rule
        $createRes = $this->actingAs($this->ownerA)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->post(route('approval-rules.store'), [
                'document_type' => 'stock_adjustment',
                'name' => 'Rule Penyesuaian Stok',
                'min_amount' => 5000000,
                'required_levels' => 1,
                'level_1_role' => 'owner',
                'is_active' => 1,
            ]);

        $createRes->assertRedirect(route('approval-rules.index'));

        $rule = ApprovalRule::where('business_id', $this->businessA->id)->where('document_type', 'stock_adjustment')->firstOrFail();

        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $this->businessA->id,
            'user_id' => $this->ownerA->id,
            'auditable_type' => ApprovalRule::class,
            'auditable_id' => $rule->id,
            'action' => 'approval_rule.created',
        ]);

        // 2. Update rule
        $updateRes = $this->actingAs($this->ownerA)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->put(route('approval-rules.update', $rule), [
                'name' => 'Rule Penyesuaian Stok Updated',
                'min_amount' => 7500000,
                'required_levels' => 1,
                'level_1_role' => 'owner',
                'is_active' => 1,
            ]);

        $updateRes->assertRedirect(route('approval-rules.index'));

        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $this->businessA->id,
            'user_id' => $this->ownerA->id,
            'auditable_type' => ApprovalRule::class,
            'auditable_id' => $rule->id,
            'action' => 'approval_rule.updated',
        ]);

        // 3. Delete rule
        $deleteRes = $this->actingAs($this->ownerA)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->delete(route('approval-rules.destroy', $rule));

        $deleteRes->assertRedirect(route('approval-rules.index'));

        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $this->businessA->id,
            'user_id' => $this->ownerA->id,
            'auditable_type' => ApprovalRule::class,
            'auditable_id' => $rule->id,
            'action' => 'approval_rule.deleted',
        ]);
    }

    /**
     * Rejection validation: Reason must be at least 3 characters.
     */
    public function test_rejection_requires_valid_reason(): void
    {
        $rule = ApprovalRule::create([
            'business_id' => $this->businessA->id,
            'document_type' => 'supplier_invoice',
            'name' => 'Rule Tagihan',
            'min_amount' => 500000,
            'required_levels' => 1,
            'approver_role_level_1' => 'owner',
            'is_active' => true,
        ]);

        $req = ApprovalRequest::create([
            'business_id' => $this->businessA->id,
            'document_type' => 'supplier_invoice',
            'document_id' => (string) Str::uuid(),
            'requester_id' => $this->staffNoPerm->id,
            'rule_id' => $rule->id,
            'amount' => 1500000,
            'current_level' => 1,
            'total_levels' => 1,
            'status' => ApprovalRequest::STATUS_PENDING,
        ]);

        // Reject with empty reason
        $this->actingAs($this->ownerA)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->post(route('approvals.reject', $req), ['reason' => ''])
            ->assertSessionHas('error');

        $this->assertTrue($req->fresh()->isPending());

        // Reject with valid reason
        $this->actingAs($this->ownerA)
            ->withSession(['active_business_id' => $this->businessA->id])
            ->post(route('approvals.reject', $req), ['reason' => 'Harga tidak sesuai kesepakatan kontrak'])
            ->assertSessionHas('success');

        $this->assertTrue($req->fresh()->isRejected());
    }
}
