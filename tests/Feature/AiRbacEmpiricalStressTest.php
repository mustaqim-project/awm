<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AiActionProposal;
use App\Models\AiGuardrail;
use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class AiRbacEmpiricalStressTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $ownerUser;
    private User $adminUser;
    private User $managerUser;
    private User $cashierUser;
    private User $warehouseUser;
    private User $viewerUser;
    private AiActionProposal $proposal;
    private AiGuardrail $guardrail;

    protected function setUp(): void
    {
        parent::setUp();
        app()->setLocale('id');
        Context::flush();

        // 1. Seed complete RBAC permission & role matrix
        $this->seed(RbacSeeder::class);

        // 2. Setup Primary Test Business with Entitled AI Subscription
        $this->business = Business::create([
            'name' => 'PT Empirik Challenge Corp',
        ]);

        BusinessSubscription::create([
            'business_id' => $this->business->id,
            'plan_code' => BusinessSubscription::PLAN_CORE_MONTHLY,
            'status' => BusinessSubscription::STATUS_ACTIVE,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
            'ai_tokens_monthly_allowance' => 10000,
            'ai_tokens_remaining' => 10000,
        ]);

        // 3. Create users for all distinct roles
        $this->ownerUser = $this->createUserWithRole('owner', 'owner@example.com');
        $this->adminUser = $this->createUserWithRole('admin', 'admin@example.com');
        $this->managerUser = $this->createUserWithRole('manager', 'manager@example.com');
        $this->cashierUser = $this->createUserWithRole('cashier', 'cashier@example.com');
        $this->warehouseUser = $this->createUserWithRole('warehouse', 'warehouse@example.com');
        $this->viewerUser = $this->createUserWithRole('viewer', 'viewer@example.com');

        // 4. Create pending action proposal
        $this->proposal = AiActionProposal::create([
            'business_id' => $this->business->id,
            'executive_role' => 'operations',
            'department' => 'inventory',
            'agent' => 'InventoryAgent',
            'tool' => 'inventory_adjust',
            'action_type' => 'adjust_stock_buffer',
            'title' => 'Proposal Safety Stock Stress Test',
            'description' => 'Mencegah kehabisan stok under heavy load.',
            'reason' => 'Empirical test verification reason.',
            'idempotency_key' => 'idemp-stress-' . Str::random(8),
            'risk_level' => AiActionProposal::RISK_LOW,
            'status' => AiActionProposal::STATUS_PENDING,
            'payload' => ['quantity' => 10, 'unit_price' => 50000],
        ]);

        // 5. Create AI Guardrail
        $this->guardrail = AiGuardrail::create([
            'business_id' => $this->business->id,
            'domain' => 'pos',
            'type' => 'dont',
            'instruction' => 'Jangan ubah harga tanpa persetujuan.',
            'is_active' => true,
        ]);
    }

    private function createUserWithRole(string $role, string $email): User
    {
        $user = User::create([
            'name' => 'User ' . ucfirst($role),
            'email' => $email,
            'password' => 'password123',
            'active_business_id' => $this->business->id,
        ]);

        $this->business->users()->attach($user->id, [
            'id' => (string) Str::uuid(),
            'role' => $role,
        ]);

        return $user;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GROUP 1: Proposal Approval Endpoints (ai.approve required)
    // ─────────────────────────────────────────────────────────────────────────

    public function test_cashier_lacking_ai_approve_cannot_approve_action_proposals(): void
    {
        $response = $this->actingAs($this->cashierUser)
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('cooca-ai.actions.approve', $this->proposal->id));

        $response->assertStatus(403);
        $response->assertJsonPath('error_code', 'FORBIDDEN_PERMISSION');
        $response->assertJsonPath('required_permissions', ['ai.approve']);
        $this->assertEquals(AiActionProposal::STATUS_PENDING, $this->proposal->fresh()->status);
    }

    public function test_warehouse_lacking_ai_approve_cannot_approve_action_proposals(): void
    {
        $response = $this->actingAs($this->warehouseUser)
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('cooca-ai.actions.approve', $this->proposal->id));

        $response->assertStatus(403);
        $response->assertJsonPath('error_code', 'FORBIDDEN_PERMISSION');
        $this->assertEquals(AiActionProposal::STATUS_PENDING, $this->proposal->fresh()->status);
    }

    public function test_cashier_lacking_ai_approve_cannot_revise_or_reject_proposals(): void
    {
        $reviseRes = $this->actingAs($this->cashierUser)
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('cooca-ai.actions.revise', $this->proposal->id), [
                'payload' => ['quantity' => 999],
            ]);

        $reviseRes->assertStatus(403);
        $reviseRes->assertJsonPath('error_code', 'FORBIDDEN_PERMISSION');

        $rejectRes = $this->actingAs($this->cashierUser)
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('cooca-ai.actions.reject', $this->proposal->id), [
                'reason' => 'Unauthorized rejection attempt',
            ]);

        $rejectRes->assertStatus(403);
        $rejectRes->assertJsonPath('error_code', 'FORBIDDEN_PERMISSION');
    }

    public function test_cashier_cannot_execute_pos_ai_action_even_with_pos_orders_permissions(): void
    {
        // Adversarial attack: Cashier has 'pos.orders' & 'invoices.create'
        // In ExecuteAiPosActionRequest, pos.orders previously passed authorize()
        // But PosAiWebController middleware MUST block it with 403 Forbidden!
        $response = $this->actingAs($this->cashierUser)
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('pos.ai.execute-action'), [
                'action_type' => 'create_invoice',
                'payload' => [
                    'total_amount' => 50000,
                    'customer_name' => 'Adversarial Client',
                ],
            ]);

        $response->assertStatus(403);
        $response->assertJsonPath('error_code', 'FORBIDDEN_PERMISSION');
        $response->assertJsonPath('required_permissions', ['ai.approve']);
    }

    public function test_warehouse_cannot_execute_pos_ai_action(): void
    {
        $response = $this->actingAs($this->warehouseUser)
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('pos.ai.execute-action'), [
                'action_type' => 'create_purchase_order',
                'payload' => [
                    'total_amount' => 100000,
                ],
            ]);

        $response->assertStatus(403);
        $response->assertJsonPath('error_code', 'FORBIDDEN_PERMISSION');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GROUP 2: AI Provider & Rules Management (ai.manage required)
    // ─────────────────────────────────────────────────────────────────────────

    public function test_manager_lacking_ai_manage_cannot_store_provider(): void
    {
        $response = $this->actingAs($this->managerUser)
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('cooca-ai.providers.store'), [
                'provider' => 'openai',
                'api_key' => 'sk-adversarial-fake-key-12345',
                'model' => 'gpt-4o',
            ]);

        $response->assertStatus(403);
        $response->assertJsonPath('error_code', 'FORBIDDEN_PERMISSION');
        $response->assertJsonPath('required_permissions', ['ai.manage']);
    }

    public function test_manager_lacking_ai_manage_cannot_alter_guardrail_rules(): void
    {
        // Create rule attempt
        $storeRes = $this->actingAs($this->managerUser)
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('cooca-ai.rules.store'), [
                'domain' => 'pos',
                'type' => 'dont',
                'instruction' => 'Manager unauthorized rule modification.',
            ]);

        $storeRes->assertStatus(403);
        $storeRes->assertJsonPath('error_code', 'FORBIDDEN_PERMISSION');

        // Toggle rule attempt
        $toggleRes = $this->actingAs($this->managerUser)
            ->withSession(['active_business_id' => $this->business->id])
            ->patchJson(route('cooca-ai.rules.toggle', $this->guardrail->id));

        $toggleRes->assertStatus(403);
        $toggleRes->assertJsonPath('error_code', 'FORBIDDEN_PERMISSION');

        // Delete rule attempt
        $deleteRes = $this->actingAs($this->managerUser)
            ->withSession(['active_business_id' => $this->business->id])
            ->deleteJson(route('cooca-ai.rules.destroy', $this->guardrail->id));

        $deleteRes->assertStatus(403);
        $deleteRes->assertJsonPath('error_code', 'FORBIDDEN_PERMISSION');
    }

    public function test_manager_lacking_ai_manage_cannot_view_providers_page_via_json(): void
    {
        $response = $this->actingAs($this->managerUser)
            ->withSession(['active_business_id' => $this->business->id])
            ->getJson(route('cooca-ai.providers'));

        $response->assertStatus(403);
        $response->assertJsonPath('error_code', 'FORBIDDEN_PERMISSION');
        $response->assertJsonPath('required_permissions', ['ai.manage']);
    }

    public function test_cashier_lacking_ai_manage_cannot_store_provider_or_rules(): void
    {
        $providerRes = $this->actingAs($this->cashierUser)
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('cooca-ai.providers.store'), [
                'provider' => 'openai',
                'api_key' => 'sk-cashier-fake-key',
                'model' => 'gpt-4o',
            ]);

        $providerRes->assertStatus(403);

        $ruleRes = $this->actingAs($this->cashierUser)
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('cooca-ai.rules.store'), [
                'domain' => 'pos',
                'type' => 'dont',
                'instruction' => 'Cashier fake rule.',
            ]);

        $ruleRes->assertStatus(403);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GROUP 3: Authorized Access (owner, admin, manager permissions)
    // ─────────────────────────────────────────────────────────────────────────

    public function test_manager_with_ai_approve_can_revise_and_reject_proposals(): void
    {
        // Manager has 'ai.approve' permission
        $reviseRes = $this->actingAs($this->managerUser)
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('cooca-ai.actions.revise', $this->proposal->id), [
                'payload' => ['quantity' => 25, 'unit_price' => 50000],
            ]);

        $reviseRes->assertStatus(200);
        $reviseRes->assertJsonPath('success', true);
        $this->assertEquals(25, $this->proposal->fresh()->payload['quantity']);

        $rejectRes = $this->actingAs($this->managerUser)
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('cooca-ai.actions.reject', $this->proposal->id), [
                'reason' => 'Manager rejected for testing purposes.',
            ]);

        $rejectRes->assertStatus(200);
        $rejectRes->assertJsonPath('success', true);
        $this->assertEquals(AiActionProposal::STATUS_REJECTED, $this->proposal->fresh()->status);
    }

    public function test_admin_with_ai_manage_can_store_guardrail_rule(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('cooca-ai.rules.store'), [
                'domain' => 'pos',
                'type' => 'do',
                'instruction' => 'Selalu tawarkan struk digital ke pelanggan.',
            ]);

        $response->assertStatus(201);
        $response->assertJsonPath('success', true);
        $this->assertDatabaseHas('ai_guardrails', [
            'business_id' => $this->business->id,
            'type' => 'do',
            'instruction' => 'Selalu tawarkan struk digital ke pelanggan.',
        ]);
    }

    public function test_owner_can_access_provider_management_and_proposals(): void
    {
        // Owner has absolute access via Context::isAdminOrOwner()
        $viewRes = $this->actingAs($this->ownerUser)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('cooca-ai.providers'));

        $viewRes->assertStatus(200);

        $ruleRes = $this->actingAs($this->ownerUser)
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('cooca-ai.rules.store'), [
                'domain' => 'general',
                'type' => 'dont',
                'instruction' => 'Owner created rule test.',
            ]);

        $ruleRes->assertStatus(201);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GROUP 4: Viewing & Base AI Access (ai.access)
    // ─────────────────────────────────────────────────────────────────────────

    public function test_cashier_with_ai_access_can_view_ai_lobby_and_pos_ai(): void
    {
        $lobbyRes = $this->actingAs($this->cashierUser)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('cooca-ai.index'));

        $lobbyRes->assertStatus(200);

        $posAiRes = $this->actingAs($this->cashierUser)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('pos.ai.index'));

        $posAiRes->assertStatus(200);
    }

    public function test_viewer_without_ai_access_receives_forbidden(): void
    {
        $lobbyRes = $this->actingAs($this->viewerUser)
            ->withSession(['active_business_id' => $this->business->id])
            ->getJson(route('cooca-ai.index'));

        $lobbyRes->assertStatus(403);
        $lobbyRes->assertJsonPath('error_code', 'FORBIDDEN_PERMISSION');
        $lobbyRes->assertJsonPath('required_permissions', ['ai.access']);

        $posAiRes = $this->actingAs($this->viewerUser)
            ->withSession(['active_business_id' => $this->business->id])
            ->getJson(route('pos.ai.index'));

        $posAiRes->assertStatus(403);
        $posAiRes->assertJsonPath('error_code', 'FORBIDDEN_PERMISSION');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GROUP 5: Edge Cases, Security Boundaries & Legacy Route Fallbacks
    // ─────────────────────────────────────────────────────────────────────────

    public function test_unauthenticated_requests_are_rejected(): void
    {
        $response = $this->postJson(route('cooca-ai.actions.approve', $this->proposal->id));
        $this->assertContains($response->status(), [401, 302]);
    }

    public function test_cross_business_tenant_isolation_prevents_unauthorized_approval(): void
    {
        $otherBusiness = Business::create(['name' => 'Other Tenant Corp']);
        $otherOwner = User::create([
            'name' => 'Other Owner',
            'email' => 'other_owner@example.com',
            'password' => 'password123',
            'active_business_id' => $otherBusiness->id,
        ]);
        $otherBusiness->users()->attach($otherOwner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
        ]);
        BusinessSubscription::create([
            'business_id' => $otherBusiness->id,
            'plan_code' => BusinessSubscription::PLAN_CORE_MONTHLY,
            'status' => BusinessSubscription::STATUS_ACTIVE,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        // Other owner attempts to approve this business's proposal
        $response = $this->actingAs($otherOwner)
            ->withSession(['active_business_id' => $otherBusiness->id])
            ->postJson(route('cooca-ai.actions.approve', $this->proposal->id));

        $this->assertContains($response->status(), [404, 403]);
    }

    public function test_legacy_ai_routes_enforce_controller_level_rbac(): void
    {
        // Legacy route /ai/actions/{proposal}/approve dispatched to AiCompanyWebController::approveAction
        // Controller middleware must intercept and reject cashier
        $response = $this->actingAs($this->cashierUser)
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson("/ai/actions/{$this->proposal->id}/approve");

        $response->assertStatus(403);
        $response->assertJsonPath('error_code', 'FORBIDDEN_PERMISSION');
        $response->assertJsonPath('required_permissions', ['ai.approve']);

        // Legacy route /ai/providers dispatched to AiCompanyWebController::storeProvider
        // Controller middleware must intercept and reject manager
        $legacyProviderRes = $this->actingAs($this->managerUser)
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson('/ai/providers', [
                'provider' => 'openai',
                'api_key' => 'sk-fake-legacy-key',
                'model' => 'gpt-4o',
            ]);

        $legacyProviderRes->assertStatus(403);
        $legacyProviderRes->assertJsonPath('error_code', 'FORBIDDEN_PERMISSION');
        $legacyProviderRes->assertJsonPath('required_permissions', ['ai.manage']);
    }

    public function test_non_json_web_requests_redirect_to_portal_with_error_for_unauthorized_users(): void
    {
        // Standard browser form submit (no Accept: application/json)
        $response = $this->actingAs($this->cashierUser)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('cooca-ai.providers'));

        // RequirePermission redirects unauthorized web request to portal with flash error
        $response->assertStatus(302);
        $response->assertRedirect(route('portal'));
        $response->assertSessionHas('error');
    }
}
