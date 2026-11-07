<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ApprovalRequest;
use App\Models\ApprovalRule;
use App\Models\Business;
use App\Models\Location;
use App\Models\Permission;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\Role;
use App\Models\StorageFile;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\DefaultUnitSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Empirical Adversarial RBAC Challenge Suite
 *
 * Verifies authorization barriers across Analytics, Approvals, and Billing:
 * 1. Unauthenticated guest rejection.
 * 2. Unauthorized user rejection (no permissions).
 * 3. Privilege escalation barriers for view-only users.
 * 4. Blade view rendering authorization guards & sensitive data masking.
 */
final class AdversarialRbacChallengeTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $userNoPerm;
    private User $viewerApprovals;
    private User $viewerAnalyticsNoFin;
    private User $viewerBilling;
    private User $ownerUser;
    private Location $location;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->seed(RbacSeeder::class);
        $this->seed(DefaultUnitSeeder::class);

        // Setup Primary Business
        $this->business = Business::create([
            'name' => 'PT Bento Nusantara Adversarial',
            'business_scale' => 'corporate',
            'industry_category' => 'fnb_resto',
        ]);

        $this->location = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Outlet Pusat',
            'type' => 'outlet',
            'is_active' => true,
        ]);

        // 1. User with NO permissions
        $this->userNoPerm = User::create([
            'name' => 'Zero Permission User',
            'email' => 'noperm@adversarial.test',
            'password' => 'password123',
            'email_verified_at' => now(),
            'phone' => '081110001',
        ]);
        $roleNoPerm = Role::create([
            'slug' => 'role_noperm',
            'name' => 'No Permissions',
            'business_id' => $this->business->id,
        ]);
        $this->business->users()->attach($this->userNoPerm->id, [
            'id' => (string) Str::uuid(),
            'role' => 'role_noperm',
            'role_id' => $roleNoPerm->id,
        ]);
        $this->userNoPerm->update(['active_business_id' => $this->business->id]);

        // 2. User with approvals.view ONLY
        $this->viewerApprovals = User::create([
            'name' => 'Approvals Viewer Only',
            'email' => 'approvals.viewer@adversarial.test',
            'password' => 'password123',
            'email_verified_at' => now(),
            'phone' => '081110002',
        ]);
        $roleApprovalsView = Role::create([
            'slug' => 'role_approvals_view',
            'name' => 'Approvals Viewer',
            'business_id' => $this->business->id,
        ]);
        $permAppView = Permission::where('slug', 'approvals.view')->firstOrFail();
        $roleApprovalsView->permissions()->sync([$permAppView->id]);
        $this->business->users()->attach($this->viewerApprovals->id, [
            'id' => (string) Str::uuid(),
            'role' => 'role_approvals_view',
            'role_id' => $roleApprovalsView->id,
        ]);
        $this->viewerApprovals->update(['active_business_id' => $this->business->id]);

        // 3. User with analytics.view ONLY (NO reports.financial)
        $this->viewerAnalyticsNoFin = User::create([
            'name' => 'Analytics Viewer No Financial',
            'email' => 'analytics.nofin@adversarial.test',
            'password' => 'password123',
            'email_verified_at' => now(),
            'phone' => '081110003',
        ]);
        $roleAnalyticsView = Role::create([
            'slug' => 'role_analytics_view',
            'name' => 'Analytics Viewer',
            'business_id' => $this->business->id,
        ]);
        $permAnaView = Permission::where('slug', 'analytics.view')->firstOrFail();
        $roleAnalyticsView->permissions()->sync([$permAnaView->id]);
        $this->business->users()->attach($this->viewerAnalyticsNoFin->id, [
            'id' => (string) Str::uuid(),
            'role' => 'role_analytics_view',
            'role_id' => $roleAnalyticsView->id,
        ]);
        $this->viewerAnalyticsNoFin->update(['active_business_id' => $this->business->id]);

        // 4. User with billing.view ONLY (NO billing.manage)
        $this->viewerBilling = User::create([
            'name' => 'Billing Viewer Only',
            'email' => 'billing.viewer@adversarial.test',
            'password' => 'password123',
            'email_verified_at' => now(),
            'phone' => '081110004',
        ]);
        $roleBillingView = Role::create([
            'slug' => 'role_billing_view',
            'name' => 'Billing Viewer',
            'business_id' => $this->business->id,
        ]);
        $permBillView = Permission::where('slug', 'billing.view')->firstOrFail();
        $roleBillingView->permissions()->sync([$permBillView->id]);
        $this->business->users()->attach($this->viewerBilling->id, [
            'id' => (string) Str::uuid(),
            'role' => 'role_billing_view',
            'role_id' => $roleBillingView->id,
        ]);
        $this->viewerBilling->update(['active_business_id' => $this->business->id]);

        // 5. Owner User (Full access)
        $this->ownerUser = User::create([
            'name' => 'Owner Bisnis',
            'email' => 'owner@adversarial.test',
            'password' => 'password123',
            'email_verified_at' => now(),
            'phone' => '081110005',
        ]);
        $ownerRole = Role::where('slug', 'owner')->firstOrFail();
        $this->business->users()->attach($this->ownerUser->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'role_id' => $ownerRole->id,
        ]);
        $this->ownerUser->update(['active_business_id' => $this->business->id]);
    }

    // =========================================================================
    // 1. UNANTICIPATED / UNAUTHENTICATED ACCESS (GUEST)
    // =========================================================================

    public function test_guest_cannot_access_analytics_approvals_or_billing(): void
    {
        $this->get('/analytics')->assertRedirect(route('login'));
        $this->getJson('/analytics')->assertUnauthorized();

        $this->get('/approvals')->assertRedirect(route('login'));
        $this->getJson('/approvals')->assertUnauthorized();

        $this->get('/billing')->assertRedirect(route('login'));
        $this->getJson('/billing')->assertUnauthorized();

        $this->get('/billing/limits')->assertRedirect(route('login'));
        $this->getJson('/billing/limits')->assertUnauthorized();
    }

    // =========================================================================
    // 2. USER WITH NO PERMISSIONS -> REDIRECTED TO PORTAL OR 403
    // =========================================================================

    public function test_user_with_no_permissions_is_rejected_on_all_three_modules(): void
    {
        $session = ['active_business_id' => $this->business->id];

        // Analytics
        $this->actingAs($this->userNoPerm)->withSession($session)
            ->get(route('analytics.index'))
            ->assertRedirect(route('portal'));
        $this->actingAs($this->userNoPerm)->withSession($session)
            ->getJson(route('analytics.index'))
            ->assertForbidden();

        // Approvals
        $this->actingAs($this->userNoPerm)->withSession($session)
            ->get(route('approvals.inbox'))
            ->assertRedirect(route('portal'));
        $this->actingAs($this->userNoPerm)->withSession($session)
            ->getJson(route('approvals.inbox'))
            ->assertForbidden();

        // Billing
        $this->actingAs($this->userNoPerm)->withSession($session)
            ->get(route('billing.limits'))
            ->assertRedirect(route('portal'));
        $this->actingAs($this->userNoPerm)->withSession($session)
            ->getJson(route('billing.limits'))
            ->assertForbidden();
    }

    // =========================================================================
    // 3. APPROVALS: VIEW-ONLY USER ATTEMPTING APPROVE/REJECT -> 403 FORBIDDEN
    // =========================================================================

    public function test_approvals_view_only_user_cannot_approve_or_reject(): void
    {
        $session = ['active_business_id' => $this->business->id];

        $rule = ApprovalRule::create([
            'business_id' => $this->business->id,
            'document_type' => 'expense',
            'name' => 'Rule Pengeluaran Kantor',
            'min_amount' => 500000,
            'required_levels' => 1,
            'approver_role_level_1' => 'owner',
            'is_active' => true,
        ]);

        $request = ApprovalRequest::create([
            'business_id' => $this->business->id,
            'document_type' => 'expense',
            'document_id' => (string) Str::uuid(),
            'requester_id' => $this->userNoPerm->id,
            'rule_id' => $rule->id,
            'amount' => 1250000,
            'current_level' => 1,
            'total_levels' => 1,
            'status' => ApprovalRequest::STATUS_PENDING,
        ]);

        // Attempt POST approve as JSON -> MUST be 403 Forbidden
        $approveJsonRes = $this->actingAs($this->viewerApprovals)->withSession($session)
            ->postJson(route('approvals.approve', $request), [
                'notes' => 'Attempting unauthorized approval by viewer',
            ]);
        $approveJsonRes->assertForbidden();

        // Attempt POST approve as Web form -> MUST redirect to portal
        $approveWebRes = $this->actingAs($this->viewerApprovals)->withSession($session)
            ->post(route('approvals.approve', $request), [
                'notes' => 'Attempting unauthorized approval by viewer',
            ]);
        $approveWebRes->assertRedirect(route('portal'));

        // Verify request was NOT mutated
        $this->assertTrue($request->fresh()->isPending());

        // Attempt POST reject as JSON -> MUST be 403 Forbidden
        $rejectJsonRes = $this->actingAs($this->viewerApprovals)->withSession($session)
            ->postJson(route('approvals.reject', $request), [
                'reason' => 'Unauthorized reject attempt',
            ]);
        $rejectJsonRes->assertForbidden();

        // Attempt POST reject as Web form -> MUST redirect to portal
        $rejectWebRes = $this->actingAs($this->viewerApprovals)->withSession($session)
            ->post(route('approvals.reject', $request), [
                'reason' => 'Unauthorized reject attempt',
            ]);
        $rejectWebRes->assertRedirect(route('portal'));

        // Verify request was still NOT mutated
        $this->assertTrue($request->fresh()->isPending());
    }

    // =========================================================================
    // 4. APPROVAL RULES: VIEW-ONLY USER ATTEMPTING POST/PUT/DELETE -> 403 FORBIDDEN
    // =========================================================================

    public function test_approvals_view_only_user_cannot_mutate_rules(): void
    {
        $session = ['active_business_id' => $this->business->id];

        $rule = ApprovalRule::create([
            'business_id' => $this->business->id,
            'document_type' => 'purchase_order',
            'name' => 'Rule Pembelian Bahan',
            'min_amount' => 2000000,
            'required_levels' => 1,
            'approver_role_level_1' => 'owner',
            'is_active' => true,
        ]);

        // 1. Attempt POST to create rule -> MUST be 403
        $createRes = $this->actingAs($this->viewerApprovals)->withSession($session)
            ->postJson(route('approval-rules.store'), [
                'document_type' => 'expense',
                'name' => 'Hacked Rule',
                'min_amount' => 100000,
                'required_levels' => 1,
                'level_1_role' => 'owner',
            ]);
        $createRes->assertForbidden();

        // 2. Attempt PUT to update rule -> MUST be 403
        $updateRes = $this->actingAs($this->viewerApprovals)->withSession($session)
            ->putJson(route('approval-rules.update', $rule), [
                'name' => 'Hacked Rule Name',
                'min_amount' => 50000,
                'required_levels' => 1,
                'level_1_role' => 'owner',
            ]);
        $updateRes->assertForbidden();

        // 3. Attempt DELETE to delete rule -> MUST be 403
        $deleteRes = $this->actingAs($this->viewerApprovals)->withSession($session)
            ->deleteJson(route('approval-rules.destroy', $rule));
        $deleteRes->assertForbidden();

        // 4. Verify alias routes also strictly reject with 403
        $this->actingAs($this->viewerApprovals)->withSession($session)
            ->postJson(route('settings.approval-rules.store'), [
                'document_type' => 'expense',
                'name' => 'Alias Hacked Rule',
            ])->assertForbidden();

        $this->actingAs($this->viewerApprovals)->withSession($session)
            ->putJson(route('settings.approval-rules.update', $rule), [
                'name' => 'Alias Update',
            ])->assertForbidden();

        $this->actingAs($this->viewerApprovals)->withSession($session)
            ->deleteJson(route('settings.approval-rules.destroy', $rule))
            ->assertForbidden();

        // Verify rule was untouched in DB
        $this->assertDatabaseHas('approval_rules', [
            'id' => $rule->id,
            'name' => 'Rule Pembelian Bahan',
        ]);
    }

    // =========================================================================
    // 5. BILLING: VIEW-ONLY USER ATTEMPTING UPGRADE OR CHECKOUT -> 403 FORBIDDEN
    // =========================================================================

    public function test_billing_view_only_user_cannot_access_checkout_or_upgrade(): void
    {
        $session = ['active_business_id' => $this->business->id];

        // 1. Attempt GET billing.checkout without billing.manage
        $checkoutJson = $this->actingAs($this->viewerBilling)->withSession($session)
            ->getJson(route('billing.checkout'));
        $checkoutJson->assertForbidden();

        $checkoutWeb = $this->actingAs($this->viewerBilling)->withSession($session)
            ->get(route('billing.checkout'));
        $checkoutWeb->assertRedirect(route('portal'));

        // 2. Attempt POST billing.upgrade without billing.manage
        $upgradeJson = $this->actingAs($this->viewerBilling)->withSession($session)
            ->postJson(route('billing.upgrade'), [
                'tier' => 'premium',
                'cycle' => 'monthly',
            ]);
        $upgradeJson->assertForbidden();

        $upgradeWeb = $this->actingAs($this->viewerBilling)->withSession($session)
            ->post(route('billing.upgrade'), [
                'tier' => 'premium',
                'cycle' => 'monthly',
            ]);
        $upgradeWeb->assertRedirect(route('portal'));

        // 3. Attempt POST billing.order.store without billing.manage
        $orderJson = $this->actingAs($this->viewerBilling)->withSession($session)
            ->postJson(route('billing.order.store'), [
                'type' => 'subscription',
                'tier' => 'prestige',
            ]);
        $orderJson->assertForbidden();

        // 4. Attempt DELETE file storage without billing.manage
        $file = StorageFile::create([
            'business_id' => $this->business->id,
            'owner_id' => $this->ownerUser->id,
            'user_id' => $this->ownerUser->id,
            'file_name' => 'nota_dinas.pdf',
            'file_path' => 'uploads/test/nota.pdf',
            'file_size' => 102400,
            'mime_type' => 'application/pdf',
            'category' => 'expense',
        ]);

        $deleteFileJson = $this->actingAs($this->viewerBilling)->withSession($session)
            ->deleteJson(route('billing.storage.files.destroy', $file));
        $deleteFileJson->assertForbidden();

        $deleteFileWeb = $this->actingAs($this->viewerBilling)->withSession($session)
            ->delete(route('billing.storage.files.destroy', $file));
        $deleteFileWeb->assertRedirect(route('portal'));

        // Verify file is still in DB
        $this->assertDatabaseHas('storage_files', ['id' => $file->id]);
    }

    // =========================================================================
    // 6. VIEW RENDERING CHECKS
    // =========================================================================

    public function test_view_rendering_approvals_inbox_hides_action_buttons_for_view_only(): void
    {
        $session = ['active_business_id' => $this->business->id];

        $rule = ApprovalRule::create([
            'business_id' => $this->business->id,
            'document_type' => 'purchase_order',
            'name' => 'Rule PO Card Test',
            'min_amount' => 1000000,
            'required_levels' => 1,
            'approver_role_level_1' => 'owner',
            'is_active' => true,
        ]);

        $ticket = ApprovalRequest::create([
            'business_id' => $this->business->id,
            'document_type' => 'purchase_order',
            'document_id' => (string) Str::uuid(),
            'requester_id' => $this->ownerUser->id,
            'rule_id' => $rule->id,
            'amount' => 4500000,
            'current_level' => 1,
            'total_levels' => 1,
            'status' => ApprovalRequest::STATUS_PENDING,
        ]);

        $response = $this->actingAs($this->viewerApprovals)->withSession($session)
            ->get(route('approvals.inbox'));

        $response->assertOk();

        // 1. The locked status badge MUST be present
        $response->assertSee(__('approvals.locked_status'));

        // 2. The ticket card action buttons MUST NOT be present
        // Specifically, the buttons with @click="openRejectModal" and @click="openApproveModal"
        $response->assertDontSee('@click="openRejectModal');
        $response->assertDontSee('@click="openApproveModal');

        // 3. Header link to approval-rules MUST NOT be present
        $response->assertDontSee(route('approval-rules.index'));
        $response->assertDontSee(__('approvals.btn_rules'));
    }

    public function test_view_rendering_analytics_masks_confidential_margin_and_profit_without_financial_perm(): void
    {
        $session = ['active_business_id' => $this->business->id];

        // Create a completed order with profit data
        PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->ownerUser->id,
            'order_number' => 'POS-ADV-999',
            'order_date' => now()->toDateString(),
            'status' => PosOrder::STATUS_COMPLETED,
            'total_amount' => 2000000,
            'paid_amount' => 2000000,
            'total_hpp_cost' => 1200000,
            'total_gross_profit' => 800000,
            'created_by' => $this->ownerUser->id,
        ]);

        $response = $this->actingAs($this->viewerAnalyticsNoFin)->withSession($session)
            ->get(route('analytics.index'));

        $response->assertOk();

        // 1. General revenue is visible
        $response->assertSee(__('analytics.total_revenue'));

        // 2. Sensitive Gross & Net profit numbers MUST be masked with bullets
        $response->assertSee('••••••••');
        $response->assertSee('••%');

        // 3. The raw profit string "Rp 800.000" MUST NOT appear in the rendered HTML
        $response->assertDontSee('Rp 800.000');

        // 4. Confidential Profit Column Header in Top Products table MUST NOT appear
        // Checked via the specific table header tag wrapped in @can('reports.financial')
        $response->assertDontSee('<th class="py-3 px-3 text-right">' . __('analytics.profit_label') . '</th>', false);
    }

    public function test_view_rendering_billing_limits_locks_upgrade_buttons_and_delete_buttons(): void
    {
        $session = ['active_business_id' => $this->business->id];

        $response = $this->actingAs($this->viewerBilling)->withSession($session)
            ->get(route('billing.limits'));

        $response->assertOk();

        // 1. Upgrade action links to checkout MUST NOT be active anchor tags
        $response->assertDontSee('route(\'billing.checkout\', [\'tier\' => \'standard\'])');
        $response->assertDontSee('route(\'billing.checkout\', [\'tier\' => \'premium\'])');

        // 2. Disabled upgrade button with lock icon MUST be rendered
        $response->assertSee('cursor-not-allowed', false);
        $response->assertSee(__('billing.btn_choose_standard'), false);

        // 3. Storage delete file action button calling confirmDelete MUST NOT be rendered
        $response->assertDontSee('confirmDelete(f)');
    }
}
