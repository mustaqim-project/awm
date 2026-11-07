<?php

declare(strict_types=1);

namespace Tests\Challenger;

use App\Models\Business;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\DefaultUnitSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class EmptyStateVerificationTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->seed(RbacSeeder::class);
        $this->seed(DefaultUnitSeeder::class);

        $this->business = Business::create([
            'name' => 'Empirical Challenger Corp',
            'business_scale' => 'umkm',
            'is_active' => true,
        ]);

        $this->owner = User::create([
            'name' => 'Challenger Owner',
            'email' => 'owner@challenger.test',
            'password' => 'password123',
            'email_verified_at' => now(),
            'phone' => '081234567899',
        ]);

        $ownerRole = Role::firstOrCreate(
            ['slug' => 'owner', 'business_id' => null],
            ['name' => 'Owner', 'description' => 'Owner with full access']
        );

        // Ensure owner has all required permissions
        $permissions = Permission::whereIn('slug', [
            'reports.view',
            'reports.financial',
            'reports.export',
            'approvals.view',
            'approvals.manage',
            'billing.view',
            'billing.manage',
        ])->get();
        $ownerRole->permissions()->syncWithoutDetaching($permissions->pluck('id')->toArray());

        $this->business->users()->attach($this->owner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'role_id' => $ownerRole->id,
        ]);

        $this->owner->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);
    }

    /**
     * Test 1: Analytics with 0 transactions, 0 locations, 0 revenue.
     */
    public function test_analytics_renders_with_zero_transactions_and_empty_metrics(): void
    {
        $response = $this->actingAs($this->owner)->get(route('analytics.index'));

        $response->assertStatus(200);
        $response->assertSee('Analitik Bisnis &amp; Tren Pertumbuhan', false);
        $response->assertSee('Rp 0', false);
        // Verify horizontal snap slider container
        $response->assertSee('flex gap-3 overflow-x-auto snap-x snap-mandatory no-scrollbar', false);
    }

    /**
     * Test 2: Approvals Inbox with 0 pending tickets.
     */
    public function test_approvals_inbox_renders_empty_state_with_zero_tickets(): void
    {
        $response = $this->actingAs($this->owner)->get(route('approvals.inbox'));

        $response->assertStatus(200);
        $response->assertSee(__('approvals.inbox_header_title'));
        $response->assertSee(__('approvals.empty_inbox_title'));
        $response->assertSee(__('approvals.empty_inbox_desc'));
    }

    /**
     * Test 3: Approvals History with 0 archived tickets.
     */
    public function test_approvals_history_renders_empty_state_with_zero_history(): void
    {
        $response = $this->actingAs($this->owner)->get(route('approvals.history'));

        $response->assertStatus(200);
        $response->assertSee(__('approvals.history_title'));
        $response->assertSee(__('approvals.empty_history'));
    }

    /**
     * Test 4: Approvals Rules with 0 rules.
     */
    public function test_approvals_rules_renders_empty_state_with_zero_rules(): void
    {
        $response = $this->actingAs($this->owner)->get(route('approval-rules.index'));

        $response->assertStatus(200);
        $response->assertSee(__('approvals.empty_rules_title'));
        $response->assertSee(__('approvals.empty_rules_desc'));
        $response->assertSee(__('approvals.btn_create_first_rule'));
    }

    /**
     * Test 5: Billing & Limits renders properly with default/free subscription.
     */
    public function test_billing_limits_renders_properly(): void
    {
        $response = $this->actingAs($this->owner)->get(route('billing.limits'));

        $response->assertStatus(200);
        $response->assertSee('data-lucide="database"', false); // Semantic storage icon
        $response->assertSee('Batas Kuota', false);
    }

    /**
     * Test 6: Docs Index renders with all 3 new module documentation partials.
     */
    public function test_docs_index_renders_with_analytics_approvals_and_billing_sections(): void
    {
        $response = $this->actingAs($this->owner)->get(route('docs.index'));

        $response->assertStatus(200);
        $response->assertSee('sec-analytics', false);
        $response->assertSee('sec-approvals', false);
        $response->assertSee('sec-billing', false);
        $response->assertSee('Modul Analitik &amp; Tren Pertumbuhan', false);
        $response->assertSee('Modul Otorisasi &amp; Persetujuan Dokumen (MAR)', false);
        $response->assertSee('Modul Paket Langganan, Kuota &amp; Billing', false);
    }
}
