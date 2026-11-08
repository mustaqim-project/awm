<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Accounting\AutoJournalService;
use App\Domain\Storage\AdminStorage;
use App\Models\BugReport;
use App\Models\Business;
use App\Models\FeatureRequest;
use App\Models\PaymentSettlement;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

final class FeedbackAndFinanceComprehensiveSecurityTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $owner;
    private User $cashier;
    private User $financeUser;
    private User $feedbackReporterOnly;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->seed(RbacSeeder::class);

        // 1. Primary Tenant Business
        $this->business = Business::create([
            'name' => 'Bisnis Security Audit Prima',
            'currency' => 'IDR',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'business_scale' => Business::SCALE_CORPORATE,
            'disabled_modules' => [],
        ]);

        // Ensure standard Chart of Accounts exist
        (new AutoJournalService())->ensureStandardAccounts($this->business);

        // 2. Owner Persona (Full Access)
        $this->owner = User::create([
            'name' => 'Owner Audit',
            'email' => 'owner.audit@cooca.test',
            'phone' => '6281111110001',
            'password' => 'password123',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);
        $ownerRole = Role::where('slug', 'owner')->first();
        $this->business->users()->attach($this->owner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'role_id' => $ownerRole?->id,
        ]);
        $this->owner->update(['active_business_id' => $this->business->id]);
        $this->owner->business_id = $this->business->id;
        $this->owner->current_business_id = $this->business->id;

        // 3. Cashier Persona (Unauthorized for Feedback & Finance)
        $this->cashier = User::create([
            'name' => 'Kasir Toko',
            'email' => 'kasir.audit@cooca.test',
            'phone' => '6281111110002',
            'password' => 'password123',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);
        $cashierRole = Role::where('slug', 'cashier')->first();
        $this->business->users()->attach($this->cashier->id, [
            'id' => (string) Str::uuid(),
            'role' => 'cashier',
            'role_id' => $cashierRole?->id,
        ]);
        $this->cashier->update(['active_business_id' => $this->business->id]);
        $this->cashier->business_id = $this->business->id;
        $this->cashier->current_business_id = $this->business->id;

        // 4. Finance Staff Persona (Authorized for Finance and Feedback)
        $this->financeUser = User::create([
            'name' => 'Staff Keuangan',
            'email' => 'finance.audit@cooca.test',
            'phone' => '6281111110003',
            'password' => 'password123',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);
        $financeRole = Role::where('slug', 'finance')->first();
        $this->business->users()->attach($this->financeUser->id, [
            'id' => (string) Str::uuid(),
            'role' => 'finance',
            'role_id' => $financeRole?->id,
        ]);
        $this->financeUser->update(['active_business_id' => $this->business->id]);
        $this->financeUser->business_id = $this->business->id;
        $this->financeUser->current_business_id = $this->business->id;

        // 5. Custom Persona with only feedback.create permission (no feedback.view)
        $this->feedbackReporterOnly = User::create([
            'name' => 'Reporter Feedback Only',
            'email' => 'reporter.audit@cooca.test',
            'phone' => '6281111110004',
            'password' => 'password123',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);
        $roleCreateOnly = Role::create([
            'business_id' => $this->business->id,
            'name' => 'Feedback Reporter Only',
            'slug' => 'feedback_reporter_only',
            'description' => 'User that can only submit feedback without viewing listings',
        ]);
        $permCreate = Permission::where('slug', 'feedback.create')->first();
        if ($permCreate) {
            $roleCreateOnly->permissions()->sync([$permCreate->id]);
        }
        $this->business->users()->attach($this->feedbackReporterOnly->id, [
            'id' => (string) Str::uuid(),
            'role' => 'feedback_reporter_only',
            'role_id' => $roleCreateOnly->id,
        ]);
        $this->feedbackReporterOnly->update(['active_business_id' => $this->business->id]);

        Context::setBusiness($this->business);
    }

    /**
     * Resolve documentation route name flexibly (docs.index or documentation.index).
     */
    private function getDocumentationRoute(): string
    {
        return Route::has('documentation.index') ? route('documentation.index') : route('docs.index');
    }

    /**
     * Resolve Chart of Accounts route name.
     */
    private function getCoaRoute(): string
    {
        return Route::has('finance.accounting.coa') ? route('finance.accounting.coa') : route('finance.coa.index');
    }

    /**
     * Resolve Balance Sheet route name.
     */
    private function getBalanceSheetRoute(): string
    {
        return Route::has('finance.accounting.balance-sheet') ? route('finance.accounting.balance-sheet') : route('finance.balance-sheet');
    }

    /**
     * Resolve Trial Balance route name.
     */
    private function getTrialBalanceRoute(): string
    {
        return Route::has('finance.accounting.trial-balance') ? route('finance.accounting.trial-balance') : route('finance.trial-balance');
    }

    /**
     * Resolve General Ledger route name.
     */
    private function getGeneralLedgerRoute(): string
    {
        return Route::has('finance.accounting.general-ledger') ? route('finance.accounting.general-ledger') : route('finance.general-ledger');
    }

    // =========================================================================
    // 1. RBAC 403 Forbidden & Guest Redirection on Feedback Endpoints
    // =========================================================================

    public function test_unauthenticated_guest_is_redirected_to_login_on_feedback_endpoints(): void
    {
        $this->get(route('feedback.bugs.index'))->assertRedirect(route('login'));
        $this->get(route('feedback.bugs.create'))->assertRedirect(route('login'));
        $this->get(route('feedback.features.index'))->assertRedirect(route('login'));
        $this->get(route('feedback.features.create'))->assertRedirect(route('login'));
    }

    public function test_authenticated_tenant_user_without_feedback_view_receives_403_forbidden_on_listings(): void
    {
        // Cashier has no feedback.view permission
        $this->actingAs($this->cashier, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('feedback.bugs.index'))
            ->assertForbidden();

        $this->actingAs($this->cashier, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('feedback.features.index'))
            ->assertForbidden();

        // Also verify JSON requests return 403 Forbidden
        $this->actingAs($this->cashier, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->getJson(route('feedback.bugs.index'))
            ->assertForbidden();

        $this->actingAs($this->cashier, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->getJson(route('feedback.features.index'))
            ->assertForbidden();
    }

    public function test_authenticated_tenant_user_without_feedback_create_receives_403_forbidden_on_create_and_store(): void
    {
        // 1. Cashier lacks feedback.create
        $this->actingAs($this->cashier, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('feedback.bugs.create'))
            ->assertForbidden();

        $this->actingAs($this->cashier, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->post(route('feedback.bugs.store'), [
                'title' => 'Bug report tanpa permission',
                'category' => 'ui',
                'severity' => 'normal',
                'description' => 'Percobaan submit tanpa izin',
            ])
            ->assertForbidden();

        $this->actingAs($this->cashier, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('feedback.features.create'))
            ->assertForbidden();

        $this->actingAs($this->cashier, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->post(route('feedback.features.store'), [
                'title' => 'Feature request tanpa permission',
                'category' => 'pos',
                'priority' => 'low',
                'description' => 'Percobaan submit tanpa izin',
            ])
            ->assertForbidden();

        // 2. Custom user with only feedback.view (created on the fly) is also blocked from create
        $viewerOnly = User::create([
            'name' => 'Viewer Only',
            'email' => 'viewer.only@cooca.test',
            'phone' => '6281111110005',
            'password' => 'password123',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);
        $roleViewOnly = Role::create([
            'business_id' => $this->business->id,
            'name' => 'Feedback Viewer Only',
            'slug' => 'feedback_viewer_only',
        ]);
        $permView = Permission::where('slug', 'feedback.view')->first();
        if ($permView) {
            $roleViewOnly->permissions()->sync([$permView->id]);
        }
        $this->business->users()->attach($viewerOnly->id, [
            'id' => (string) Str::uuid(),
            'role' => 'feedback_viewer_only',
            'role_id' => $roleViewOnly->id,
        ]);
        $viewerOnly->update(['active_business_id' => $this->business->id]);

        $this->actingAs($viewerOnly, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('feedback.bugs.create'))
            ->assertForbidden();

        $this->actingAs($viewerOnly, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->post(route('feedback.bugs.store'), [
                'title' => 'Viewer submit attempt',
                'category' => 'ui',
                'severity' => 'low',
                'description' => 'Should fail with 403',
            ])
            ->assertForbidden();
    }

    // =========================================================================
    // 2. RBAC 200 OK on Feedback Endpoints
    // =========================================================================

    public function test_owner_receives_200_ok_on_all_feedback_endpoints(): void
    {
        $this->actingAs($this->owner, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('feedback.bugs.index'))
            ->assertOk();

        $this->actingAs($this->owner, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('feedback.bugs.create'))
            ->assertOk();

        $this->actingAs($this->owner, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('feedback.features.index'))
            ->assertOk();

        $this->actingAs($this->owner, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('feedback.features.create'))
            ->assertOk();
    }

    public function test_user_with_feedback_permissions_receives_200_ok_on_authorized_views(): void
    {
        // Finance role has both feedback.view and feedback.create permissions
        $this->actingAs($this->financeUser, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('feedback.bugs.index'))
            ->assertOk();

        $this->actingAs($this->financeUser, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('feedback.features.index'))
            ->assertOk();

        $this->actingAs($this->financeUser, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('feedback.bugs.create'))
            ->assertOk();

        $this->actingAs($this->financeUser, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('feedback.features.create'))
            ->assertOk();

        // Reporter Only user can view create form
        $this->actingAs($this->feedbackReporterOnly, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('feedback.bugs.create'))
            ->assertOk();

        $this->actingAs($this->feedbackReporterOnly, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('feedback.features.create'))
            ->assertOk();
    }

    // =========================================================================
    // 3. RBAC 403 Forbidden Enforcement on Finance Endpoints
    // =========================================================================

    public function test_user_without_finance_cash_bank_receives_403_forbidden_on_cash_bank_and_settlements(): void
    {
        // JSON request returns 403 Forbidden via RequirePermission middleware
        $this->actingAs($this->cashier, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->getJson(route('finance.cash-bank.index'))
            ->assertForbidden();

        $this->actingAs($this->cashier, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->getJson(route('finance.settlements.index'))
            ->assertForbidden();

        $this->actingAs($this->cashier, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('finance.settlements.reconcile'), [
                'settlement_id' => (string) Str::uuid(),
            ])
            ->assertForbidden();
    }

    public function test_user_without_finance_payables_receives_403_forbidden_on_payables_actions(): void
    {
        $this->actingAs($this->cashier, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->getJson(route('finance.payables'))
            ->assertForbidden();

        $this->actingAs($this->cashier, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('finance.payables.import-execute'), [])
            ->assertForbidden();
    }

    public function test_user_without_finance_receivables_receives_403_forbidden_on_receivables_actions(): void
    {
        $this->actingAs($this->cashier, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->getJson(route('finance.receivables'))
            ->assertForbidden();

        $this->actingAs($this->cashier, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('finance.receivables.import-execute'), [])
            ->assertForbidden();
    }

    public function test_user_without_accounting_view_receives_403_forbidden_on_accounting_reports_and_coa(): void
    {
        $this->actingAs($this->cashier, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->getJson($this->getCoaRoute())
            ->assertForbidden();

        $this->actingAs($this->cashier, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->getJson($this->getBalanceSheetRoute())
            ->assertForbidden();

        $this->actingAs($this->cashier, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->getJson($this->getTrialBalanceRoute())
            ->assertForbidden();

        $this->actingAs($this->cashier, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->getJson($this->getGeneralLedgerRoute())
            ->assertForbidden();
    }

    public function test_authorized_owner_and_finance_users_can_access_finance_endpoints_with_200_ok(): void
    {
        // 1. Owner access
        $this->actingAs($this->owner, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('finance.cash-bank.index'))
            ->assertOk();

        $this->actingAs($this->owner, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('finance.settlements.index'))
            ->assertOk();

        $this->actingAs($this->owner, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('finance.payables'))
            ->assertOk();

        $this->actingAs($this->owner, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('finance.receivables'))
            ->assertOk();

        $this->actingAs($this->owner, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->get($this->getCoaRoute())
            ->assertOk();

        $this->actingAs($this->owner, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->get($this->getBalanceSheetRoute())
            ->assertOk();

        // 2. Finance Staff access
        $this->actingAs($this->financeUser, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('finance.cash-bank.index'))
            ->assertOk();

        $this->actingAs($this->financeUser, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('finance.settlements.index'))
            ->assertOk();

        $this->actingAs($this->financeUser, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('finance.payables'))
            ->assertOk();

        $this->actingAs($this->financeUser, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('finance.receivables'))
            ->assertOk();
    }

    // =========================================================================
    // 4. SettlementProofController Security Guard
    // =========================================================================

    public function test_staff_without_finance_cash_bank_receives_403_forbidden_on_settlement_proof(): void
    {
        $settlement = PaymentSettlement::create([
            'business_id' => $this->business->id,
            'settlement_number' => 'SET-TEST-PROOF-001',
            'settlement_date' => now()->toDateString(),
            'payment_channel' => 'qris',
            'gross_amount' => 500000,
            'fee_amount' => 3500,
            'net_amount' => 496500,
            'status' => PaymentSettlement::STATUS_COMPLETED,
            'proof_image_path' => 'proofs/settlement_sample.png',
        ]);

        // Cashier has no finance.cash_bank permission -> must receive 403 Forbidden
        $response = $this->actingAs($this->cashier, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('settlements.proof', $settlement));

        $response->assertForbidden();
    }

    public function test_authorized_user_with_finance_cash_bank_or_owner_passes_settlement_proof_authorization(): void
    {
        $tempDir = public_path('proofs_test');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0777, true);
        }
        $dummyRelativePath = 'proofs_test/valid_settlement_proof_' . uniqid() . '.jpg';
        $dummyFullPath = public_path($dummyRelativePath);
        file_put_contents($dummyFullPath, 'fake-binary-proof-content');

        try {
            $settlement = PaymentSettlement::create([
                'business_id' => $this->business->id,
                'settlement_number' => 'SET-TEST-PROOF-002',
                'settlement_date' => now()->toDateString(),
                'payment_channel' => 'qris',
                'gross_amount' => 750000,
                'fee_amount' => 5250,
                'net_amount' => 744750,
                'status' => PaymentSettlement::STATUS_COMPLETED,
                'proof_image_path' => $dummyRelativePath,
            ]);

            // 1. Owner authorization passes -> 200 OK
            $resOwner = $this->actingAs($this->owner, 'web')
                ->withSession(['active_business_id' => $this->business->id])
                ->get(route('settlements.proof', $settlement));

            $resOwner->assertOk();

            // 2. Finance staff authorization passes -> 200 OK
            $resFinance = $this->actingAs($this->financeUser, 'web')
                ->withSession(['active_business_id' => $this->business->id])
                ->get(route('settlements.proof', $settlement));

            $resFinance->assertOk();
        } finally {
            if (file_exists($dummyFullPath)) {
                @unlink($dummyFullPath);
            }
            if (is_dir($tempDir)) {
                @rmdir($tempDir);
            }
        }
    }

    public function test_cross_tenant_idor_user_cannot_view_or_stream_bug_attachment_from_another_business(): void
    {
        // 1. Create Business B and its Owner
        $businessB = Business::create(['name' => 'Bisnis Tenant B']);
        $ownerB = User::create([
            'name' => 'Owner Bisnis B',
            'email' => 'owner.b@cooca.test',
            'phone' => '6281111110099',
            'password' => 'password123',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);
        $ownerRole = Role::where('slug', 'owner')->first();
        $businessB->users()->attach($ownerB->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'role_id' => $ownerRole?->id,
        ]);
        $ownerB->update(['active_business_id' => $businessB->id]);
        $ownerB->business_id = $businessB->id;
        $ownerB->current_business_id = $businessB->id;

        // 2. Create BugReport belonging to Business A with an attachment
        Storage::fake('local');
        $filePath = 'feedback/bugs/sample_attachment_' . uniqid() . '.txt';
        Storage::disk('local')->put($filePath, 'Confidential vulnerability diagnostic report');

        $bugBusinessA = BugReport::create([
            'business_id' => $this->business->id,
            'reporter_id' => $this->owner->id,
            'title' => 'Celah Keamanan Tenant A',
            'category' => 'security',
            'severity' => 'critical',
            'description' => 'Detail masalah sensitif internal Tenant A',
            'status' => 'open',
            'progress_percent' => 0,
            'attachment_path' => $filePath,
        ]);

        // Also create a FeatureRequest belonging to Business A
        $featureBusinessA = FeatureRequest::create([
            'business_id' => $this->business->id,
            'requester_id' => $this->owner->id,
            'title' => 'Strategi Rahasia Tenant A',
            'category' => 'general',
            'priority' => 'high',
            'description' => 'Ide pengembangan khusus Tenant A',
            'business_value' => 'Nilai strategis',
            'use_case' => 'Kasus spesifik Tenant A',
            'status' => 'submitted',
            'progress_percent' => 0,
        ]);

        // 3. User B (Owner of Business B) attempts to view Business A's bug report
        // Protected by BusinessScope (404 Not Found) or Controller abort_unless (403 Forbidden)
        $resBug = $this->actingAs($ownerB, 'web')
            ->withSession(['active_business_id' => $businessB->id])
            ->get(route('feedback.bugs.show', $bugBusinessA));
        $this->assertContains($resBug->status(), [403, 404], 'Cross-tenant access must be rejected with 403 or 404');

        // 4. User B attempts to stream Business A's attachment
        $resAttach = $this->actingAs($ownerB, 'web')
            ->withSession(['active_business_id' => $businessB->id])
            ->get(route('feedback.bugs.attachment', $bugBusinessA));
        $this->assertContains($resAttach->status(), [403, 404], 'Cross-tenant attachment stream must be rejected with 403 or 404');

        // 5. User B attempts to view Business A's feature request
        $resFeature = $this->actingAs($ownerB, 'web')
            ->withSession(['active_business_id' => $businessB->id])
            ->get(route('feedback.features.show', $featureBusinessA));
        $this->assertContains($resFeature->status(), [403, 404], 'Cross-tenant feature request must be rejected with 403 or 404');
    }

    public function test_cross_tenant_idor_merchant_cannot_access_settlement_proof_from_another_business(): void
    {
        // 1. Create Business B and its Owner
        $businessB = Business::create(['name' => 'Bisnis Tenant B IDOR']);
        $ownerB = User::create([
            'name' => 'Owner Bisnis B IDOR',
            'email' => 'owner.b.idor@cooca.test',
            'phone' => '6281111110098',
            'password' => 'password123',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);
        $ownerRole = Role::where('slug', 'owner')->first();
        $businessB->users()->attach($ownerB->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'role_id' => $ownerRole?->id,
        ]);
        $ownerB->update(['active_business_id' => $businessB->id]);
        $ownerB->business_id = $businessB->id;
        $ownerB->current_business_id = $businessB->id;

        // 2. Create Settlement belonging to Business A
        $settlementBusinessA = PaymentSettlement::create([
            'business_id' => $this->business->id,
            'settlement_number' => 'SET-BUSINESS-A-001',
            'settlement_date' => now()->toDateString(),
            'payment_channel' => 'qris',
            'gross_amount' => 12500000,
            'fee_amount' => 87500,
            'net_amount' => 12412500,
            'status' => PaymentSettlement::STATUS_COMPLETED,
            'proof_image_path' => 'proofs/settlement_a.png',
        ]);

        // 3. Owner of Business B attempts to access Business A's settlement proof
        // Rejection via BusinessScope (404) or Controller check (403)
        $resMerchant = $this->actingAs($ownerB, 'web')
            ->withSession(['active_business_id' => $businessB->id])
            ->get(route('settlements.proof', $settlementBusinessA));
        $this->assertContains($resMerchant->status(), [403, 404], 'Merchant B accessing Settlement A must be blocked with 403 or 404');

        // 4. Unauthenticated guest attempts to access Business A's settlement proof -> strictly 403 Forbidden
        $this->flushSession();
        Context::flush();
        $this->get(route('settlements.proof', $settlementBusinessA))
            ->assertForbidden();
    }

    // =========================================================================
    // 5. Documentation Integration (POV Business Owner, 20-Industry Matrix & Route Buttons)
    // =========================================================================

    public function test_documentation_page_returns_200_ok_and_contains_feedback_and_finance_sections(): void
    {
        $docRoute = $this->getDocumentationRoute();

        $response = $this->actingAs($this->owner, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->get($docRoute);

        $response->assertOk();

        // 1. Assert Presence of Section IDs
        $response->assertSee('id="sec-feedback"', false);
        $response->assertSee('id="sec-finance"', false);

        // 2. Assert 20-Industry Practical Matrix in Feedback & Finance docs
        $response->assertSee('20 Jenis Usaha', false);
        $response->assertSee('F&amp;B', false);
        $response->assertSee('Manufaktur', false);
        $response->assertSee('Ritel', false);

        // 3. Assert Verified Interactive Route Action Buttons in Documentation
        $response->assertSee(route('feedback.bugs.create'), false);
        $response->assertSee(route('feedback.features.create'), false);
        $response->assertSee(route('feedback.bugs.index'), false);
        $response->assertSee(route('feedback.features.index'), false);
        $response->assertSee(route('finance.cash-bank.index'), false);
        $response->assertSee(route('finance.expenses.index'), false);
        $response->assertSee(route('finance.settlements.index'), false);
    }

    // =========================================================================
    // 6. View Compilation Test (Programmatic Verification of All 19 Blade Views)
    // =========================================================================

    public function test_all_nineteen_feedback_and_finance_blade_views_compile_cleanly(): void
    {
        $compiler = app('blade.compiler');

        $views = [
            // Feedback Views (3)
            'app.feedback.create' => resource_path('views/app/feedback/create.blade.php'),
            'app.feedback.index' => resource_path('views/app/feedback/index.blade.php'),
            'app.feedback.show' => resource_path('views/app/feedback/show.blade.php'),

            // Finance Views (16)
            'app.finance.expenses' => resource_path('views/app/finance/expenses.blade.php'),
            'app.finance.journals' => resource_path('views/app/finance/journals.blade.php'),
            'app.finance.payables' => resource_path('views/app/finance/payables.blade.php'),
            'app.finance.receivables' => resource_path('views/app/finance/receivables.blade.php'),
            'app.finance.accounting.balance-sheet' => resource_path('views/app/finance/accounting/balance-sheet.blade.php'),
            'app.finance.accounting.coa' => resource_path('views/app/finance/accounting/coa.blade.php'),
            'app.finance.accounting.general-ledger' => resource_path('views/app/finance/accounting/general-ledger.blade.php'),
            'app.finance.accounting.reconciliation' => resource_path('views/app/finance/accounting/reconciliation.blade.php'),
            'app.finance.accounting.trial-balance' => resource_path('views/app/finance/accounting/trial-balance.blade.php'),
            'app.finance.cash-bank.index' => resource_path('views/app/finance/cash-bank/index.blade.php'),
            'app.finance.cash-bank.ledger' => resource_path('views/app/finance/cash-bank/ledger.blade.php'),
            'app.finance.edc-terminals.index' => resource_path('views/app/finance/edc-terminals/index.blade.php'),
            'app.finance.external-reconciliation.index' => resource_path('views/app/finance/external-reconciliation/index.blade.php'),
            'app.finance.payout-accounts.index' => resource_path('views/app/finance/payout-accounts/index.blade.php'),
            'app.finance.settlements.index' => resource_path('views/app/finance/settlements/index.blade.php'),
            'app.finance.settlements.show' => resource_path('views/app/finance/settlements/show.blade.php'),
        ];

        // Programmatically assert exact count
        $this->assertCount(19, $views, 'Total feedback and finance views must be exactly 19.');

        foreach ($views as $viewName => $filePath) {
            $this->assertFileExists($filePath, "Blade view file [{$filePath}] must exist on disk.");
            $this->assertTrue(view()->exists($viewName), "Laravel view engine must resolve view [{$viewName}].");

            $rawBladeContent = file_get_contents($filePath);
            $this->assertNotFalse($rawBladeContent, "Failed to read content of [{$filePath}].");
            $this->assertNotEmpty($rawBladeContent, "Blade view [{$filePath}] must not be empty.");

            // Verify compiler parses and produces valid PHP without throwing SyntaxError or ViewException
            $compiledPhp = $compiler->compileString($rawBladeContent);
            $this->assertIsString($compiledPhp, "Compiled PHP for [{$viewName}] must be a valid string.");
            $this->assertNotEmpty($compiledPhp, "Compiled PHP output for [{$viewName}] must not be empty.");
        }
    }
}
