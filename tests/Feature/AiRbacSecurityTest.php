<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AiActionProposal;
use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class AiRbacSecurityTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private AiActionProposal $proposal;

    protected function setUp(): void
    {
        parent::setUp();
        app()->setLocale('id');
        Context::flush();

        // Seed RBAC permissions and roles
        $this->artisan('db:seed', ['--class' => 'RbacSeeder', '--force' => true]);

        $this->business = Business::create([
            'name' => 'PT COOCA RBAC Test',
        ]);

        BusinessSubscription::create([
            'business_id' => $this->business->id,
            'plan_code' => BusinessSubscription::PLAN_CORE_MONTHLY,
            'status' => BusinessSubscription::STATUS_ACTIVE,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
            'ai_tokens_monthly_allowance' => 1000,
            'ai_tokens_remaining' => 1000,
        ]);

        $this->proposal = AiActionProposal::create([
            'business_id' => $this->business->id,
            'executive_role' => 'operations',
            'department' => 'inventory',
            'agent' => 'InventoryAgent',
            'tool' => 'inventory_adjust',
            'action_type' => 'adjust_stock_buffer',
            'title' => 'Proposal Test RBAC',
            'description' => 'Testing approval permissions',
            'reason' => 'Audit requirement',
            'idempotency_key' => 'idemp-rbac-' . Str::random(8),
            'risk_level' => AiActionProposal::RISK_LOW,
            'status' => AiActionProposal::STATUS_PENDING,
            'payload' => ['quantity' => 10],
        ]);
    }

    private function createUserWithRole(string $role): User
    {
        $user = User::create([
            'name' => "User {$role}",
            'email' => "{$role}_" . Str::random(5) . "@example.com",
            'password' => 'password123',
        ]);

        $this->business->users()->attach($user->id, [
            'id' => (string) Str::uuid(),
            'role' => $role,
        ]);

        $user->update(['active_business_id' => $this->business->id]);

        return $user;
    }

    public function test_viewer_without_ai_access_is_forbidden_from_lobby(): void
    {
        $viewer = $this->createUserWithRole('viewer');

        $response = $this->actingAs($viewer)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('cooca-ai.index'));

        // Viewer does not have ai.access -> redirected with error or 403
        $this->assertTrue(
            $response->status() === 403 || $response->isRedirect(route('portal')),
            "Expected 403 or redirect to portal, got status {$response->status()}"
        );
    }

    public function test_cashier_with_ai_access_can_view_lobby_but_cannot_approve_actions(): void
    {
        $cashier = $this->createUserWithRole('cashier');

        // Cashier has ai.access -> 200 OK on lobby
        $responseLobby = $this->actingAs($cashier)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('cooca-ai.index'));

        $responseLobby->assertStatus(200);

        // Cashier lacks ai.approve -> 403 on approve
        $responseApprove = $this->actingAs($cashier)
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('cooca-ai.actions.approve', $this->proposal->id));

        $responseApprove->assertStatus(403);
    }

    public function test_cashier_cannot_access_or_store_ai_providers(): void
    {
        $cashier = $this->createUserWithRole('cashier');

        // Cashier lacks ai.manage -> 403 or redirect from providers page
        $responseProviders = $this->actingAs($cashier)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('cooca-ai.providers'));

        $this->assertTrue(
            $responseProviders->status() === 403 || $responseProviders->isRedirect(route('portal')),
            "Expected 403 or redirect to portal, got {$responseProviders->status()}"
        );

        $responseStore = $this->actingAs($cashier)
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('cooca-ai.providers.store'), [
                'provider' => 'openai',
                'api_key' => 'sk-dummy-key',
            ]);

        $responseStore->assertStatus(403);
    }

    public function test_cashier_cannot_execute_pos_ai_action_draft(): void
    {
        $cashier = $this->createUserWithRole('cashier');

        // Cashier has ai.access -> 200 on pos.ai.index
        $responsePos = $this->actingAs($cashier)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('pos.ai.index'));

        $responsePos->assertStatus(200);

        // Cashier lacks ai.approve -> 403 on execute-action
        $responseExecute = $this->actingAs($cashier)
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('pos.ai.execute-action'), [
                'action_type' => 'create_invoice',
                'payload' => [
                    'customer_id' => 'cust-1',
                    'total_amount' => 50000,
                ],
            ]);

        $responseExecute->assertStatus(403);
    }

    public function test_manager_with_ai_approve_can_approve_action_proposals(): void
    {
        $manager = $this->createUserWithRole('manager');

        $response = $this->actingAs($manager)
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('cooca-ai.actions.approve', $this->proposal->id));

        // Manager has ai.approve -> should NOT be 403
        $this->assertNotEquals(403, $response->status(), 'Manager with ai.approve should not get 403 Forbidden');
    }

    public function test_manager_without_ai_manage_cannot_store_providers(): void
    {
        $manager = $this->createUserWithRole('manager');

        $response = $this->actingAs($manager)
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('cooca-ai.providers.store'), [
                'provider' => 'openai',
                'api_key' => 'sk-dummy-key',
            ]);

        // Manager lacks ai.manage -> 403 Forbidden
        $response->assertStatus(403);
    }

    public function test_admin_and_owner_have_unrestricted_ai_access(): void
    {
        $admin = $this->createUserWithRole('admin');

        $responseLobby = $this->actingAs($admin)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('cooca-ai.index'));
        $responseLobby->assertStatus(200);

        $responseProviders = $this->actingAs($admin)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('cooca-ai.providers'));
        $responseProviders->assertStatus(200);

        $owner = $this->createUserWithRole('owner');

        $responseOwnerLobby = $this->actingAs($owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('cooca-ai.index'));
        $responseOwnerLobby->assertStatus(200);

        $responseOwnerProviders = $this->actingAs($owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('cooca-ai.providers'));
        $responseOwnerProviders->assertStatus(200);
    }
}
