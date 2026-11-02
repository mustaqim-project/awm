<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Domain\Storage\TenantStorage;
use App\Models\ApprovalRequest;
use App\Models\ApprovalRule;
use App\Models\AttendanceCorrection;
use App\Models\Business;
use App\Models\CashAccount;
use App\Models\CashTransaction;
use App\Models\Expense;
use App\Models\Location;
use App\Models\PosApprovalRequest;
use App\Models\PosCashMovement;
use App\Models\PosOrder;
use App\Models\PosShift;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Adversarial and Stress Test Suite for Phase 1 Backend.
 *
 * Verifies:
 * 1. Closed shift boundaries (422, zero balance mutation)
 * 2. RBAC authorization (401/403) and multi-tenant isolation
 * 3. Idempotency & duplicate action prevention
 * 4. Cash ledger & database integrity
 */
final class BackendPhase1AdversarialChallengeTest extends TestCase
{
    use RefreshDatabase;

    private Business $businessA;
    private Business $businessB;
    private User $ownerA;
    private User $cashierA;
    private User $staffA;
    private User $ownerB;
    private Location $locationA;
    private PosShift $openShiftA;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();
        Storage::fake(TenantStorage::DISK_PUBLIC);

        // Tenant A
        $this->businessA = Business::create([
            'id' => (string) Str::uuid(),
            'name' => 'Adversarial Business A',
            'slug' => 'adv-business-a',
            'is_active' => true,
        ]);

        $this->locationA = Location::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->businessA->id,
            'name' => 'Store A Main',
            'is_primary' => true,
            'is_active' => true,
        ]);

        // Owner A
        $this->ownerA = User::create([
            'name' => 'Owner A',
            'email' => 'owner.a@example.com',
            'password' => bcrypt('password123'),
        ]);
        $this->businessA->users()->attach($this->ownerA->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);
        $this->ownerA->update(['active_business_id' => $this->businessA->id]);

        // Roles & Permissions setup
        $roleCashier = \App\Models\Role::create([
            'id' => (string) Str::uuid(),
            'name' => 'Cashier',
            'slug' => 'cashier',
        ]);
        $permTerminal = \App\Models\Permission::create([
            'id' => (string) Str::uuid(),
            'name' => 'POS Terminal',
            'slug' => 'pos.terminal',
            'category' => 'pos',
        ]);
        $roleCashier->permissions()->attach($permTerminal->id, ['id' => (string) Str::uuid()]);

        // Cashier A
        $this->cashierA = User::create([
            'name' => 'Cashier A',
            'email' => 'cashier.a@example.com',
            'password' => bcrypt('password123'),
        ]);
        $this->businessA->users()->attach($this->cashierA->id, [
            'id' => (string) Str::uuid(),
            'role' => 'cashier',
            'is_active' => true,
        ]);
        $this->cashierA->update(['active_business_id' => $this->businessA->id]);

        // Staff A
        $this->staffA = User::create([
            'name' => 'Staff A',
            'email' => 'staff.a@example.com',
            'password' => bcrypt('password123'),
        ]);
        $this->businessA->users()->attach($this->staffA->id, [
            'id' => (string) Str::uuid(),
            'role' => 'staff',
            'is_active' => true,
        ]);
        $this->staffA->update(['active_business_id' => $this->businessA->id]);

        // Open Shift A
        $this->openShiftA = PosShift::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->businessA->id,
            'location_id' => $this->locationA->id,
            'user_id' => $this->cashierA->id,
            'opened_at' => now(),
            'opening_cash' => 250000.0,
            'total_cash_out' => 0.0,
            'status' => PosShift::STATUS_OPEN,
        ]);

        // Tenant B
        $this->businessB = Business::create([
            'id' => (string) Str::uuid(),
            'name' => 'Adversarial Business B',
            'slug' => 'adv-business-b',
            'is_active' => true,
        ]);

        $this->ownerB = User::create([
            'name' => 'Owner B',
            'email' => 'owner.b@example.com',
            'password' => bcrypt('password123'),
        ]);
        $this->businessB->users()->attach($this->ownerB->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);
        $this->ownerB->update(['active_business_id' => $this->businessB->id]);
    }

    // =========================================================================
    // 1. CLOSED SHIFT & BOUNDARY CONDITIONS
    // =========================================================================

    public function test_expense_on_closed_shift_strictly_fails_with_422_and_mutates_no_balances(): void
    {
        $closedShift = PosShift::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->businessA->id,
            'location_id' => $this->locationA->id,
            'user_id' => $this->cashierA->id,
            'opened_at' => now()->subHours(10),
            'closed_at' => now()->subHours(2),
            'opening_cash' => 300000.0,
            'total_cash_out' => 0.0,
            'status' => PosShift::STATUS_CLOSED,
        ]);

        Sanctum::actingAs($this->ownerA);

        $response = $this->withHeaders(['X-Business-Id' => $this->businessA->id])
            ->postJson('/api/v1/pos/expenses', [
                'pos_shift_id' => $closedShift->id,
                'amount' => 50000,
                'category' => 'operational',
                'notes' => 'Attempting expense on closed shift',
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Shift kasir telah ditutup atau tidak aktif.',
            ]);

        // EMPIRICAL ASSERTIONS: zero mutation
        $this->assertSame(0.0, (float) $closedShift->fresh()->total_cash_out);
        $this->assertSame(0, PosCashMovement::where('pos_shift_id', $closedShift->id)->count());
        $this->assertSame(0, CashTransaction::where('business_id', $this->businessA->id)->where('reference_type', 'pos_shift_expense')->count());
        $this->assertSame(0, Expense::where('business_id', $this->businessA->id)->count());
    }

    public function test_expense_on_non_existent_shift_strictly_fails_with_422_and_creates_no_records(): void
    {
        Sanctum::actingAs($this->ownerA);

        $fakeShiftId = (string) Str::uuid();

        $response = $this->withHeaders(['X-Business-Id' => $this->businessA->id])
            ->postJson('/api/v1/pos/expenses', [
                'pos_shift_id' => $fakeShiftId,
                'amount' => 35000,
                'category' => 'supplies',
                'notes' => 'Fake shift attempt',
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Shift kasir telah ditutup atau tidak aktif.',
            ]);

        $this->assertSame(0, PosCashMovement::count());
        $this->assertSame(0, CashTransaction::count());
        $this->assertSame(0, Expense::count());
    }

    public function test_cross_tenant_shift_injection_strictly_fails_with_422_and_modifies_neither_tenant(): void
    {
        // Create open shift belonging to Business B
        $locationB = Location::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->businessB->id,
            'name' => 'Store B Main',
            'is_primary' => true,
            'is_active' => true,
        ]);

        $shiftB = PosShift::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->businessB->id,
            'location_id' => $locationB->id,
            'user_id' => $this->ownerB->id,
            'opened_at' => now(),
            'opening_cash' => 150000.0,
            'total_cash_out' => 0.0,
            'status' => PosShift::STATUS_OPEN,
        ]);

        // User A attempts to record expense against shift B within Business A context
        Sanctum::actingAs($this->ownerA);

        $response = $this->withHeaders(['X-Business-Id' => $this->businessA->id])
            ->postJson('/api/v1/pos/expenses', [
                'pos_shift_id' => $shiftB->id,
                'amount' => 40000,
                'category' => 'meal',
                'notes' => 'Cross-tenant shift injection',
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Shift kasir telah ditutup atau tidak aktif.',
            ]);

        // Shift B remains untouched
        $this->assertSame(0.0, (float) $shiftB->fresh()->total_cash_out);
        $this->assertSame(0, PosCashMovement::count());
        $this->assertSame(0, CashTransaction::count());
        $this->assertSame(0, Expense::count());
    }

    // =========================================================================
    // 2. AUTHORIZATION & TENANT ISOLATION
    // =========================================================================

    public function test_owner_approvals_pending_rejects_unauthenticated_request(): void
    {
        $this->getJson('/api/v1/owner/approvals/pending')
            ->assertStatus(401);
    }

    public function test_owner_approvals_action_rejects_unauthenticated_request(): void
    {
        $this->postJson('/api/v1/owner/approvals/item-123/action', [
            'action' => 'approve',
        ])->assertStatus(401);
    }

    public function test_owner_approvals_rejects_cashier_and_staff_roles_with_403(): void
    {
        // 1. Cashier Role
        Sanctum::actingAs($this->cashierA);

        $this->withHeaders(['X-Business-Id' => $this->businessA->id])
            ->getJson('/api/v1/owner/approvals/pending')
            ->assertStatus(403)
            ->assertJson(['error_code' => 'FORBIDDEN_ROLE']);

        $this->withHeaders(['X-Business-Id' => $this->businessA->id])
            ->postJson('/api/v1/owner/approvals/pos_appr_1/action', [
                'action' => 'approve',
            ])->assertStatus(403)
            ->assertJson(['error_code' => 'FORBIDDEN_ROLE']);

        // 2. Staff Role
        Sanctum::actingAs($this->staffA);

        $this->withHeaders(['X-Business-Id' => $this->businessA->id])
            ->getJson('/api/v1/owner/approvals/pending')
            ->assertStatus(403)
            ->assertJson(['error_code' => 'FORBIDDEN_ROLE']);

        $this->withHeaders(['X-Business-Id' => $this->businessA->id])
            ->postJson('/api/v1/owner/approvals/pos_appr_1/action', [
                'action' => 'approve',
            ])->assertStatus(403)
            ->assertJson(['error_code' => 'FORBIDDEN_ROLE']);
    }

    public function test_pos_expenses_rejects_staff_role_lacking_permission_with_403(): void
    {
        Sanctum::actingAs($this->staffA);

        $this->withHeaders(['X-Business-Id' => $this->businessA->id])
            ->postJson('/api/v1/pos/expenses', [
                'pos_shift_id' => $this->openShiftA->id,
                'amount' => 15000,
                'category' => 'supplies',
                'notes' => 'Unauthorized staff expense',
            ])
            ->assertStatus(403)
            ->assertJson(['error_code' => 'FORBIDDEN_PERMISSION']);
    }

    public function test_tenant_isolation_business_b_cannot_see_business_a_approvals(): void
    {
        // Create pending approvals in Business A
        $posApprovalA = PosApprovalRequest::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->businessA->id,
            'requested_by' => $this->cashierA->id,
            'action_type' => PosApprovalRequest::ACTION_VOID,
            'reference_id' => '100',
            'payload' => ['order_number' => 'ORD-AAA', 'reason' => 'Void order A'],
            'status' => PosApprovalRequest::STATUS_PENDING,
        ]);

        $attA = AttendanceCorrection::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->businessA->id,
            'user_id' => $this->staffA->id,
            'correction_number' => 'CORR-AAA-1',
            'target_date' => now()->toDateString(),
            'correction_type' => AttendanceCorrection::TYPE_CLOCK_IN_ONLY,
            'reason' => 'Lupa presensi A',
            'status' => AttendanceCorrection::STATUS_PENDING,
        ]);

        // Owner B queries pending approvals for Business B
        Sanctum::actingAs($this->ownerB);

        $response = $this->withHeaders(['X-Business-Id' => $this->businessB->id])
            ->getJson('/api/v1/owner/approvals/pending');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'count' => 0,
                'data' => [],
            ]);
    }

    public function test_tenant_isolation_business_b_cannot_action_business_a_pos_approval(): void
    {
        $orderA = PosOrder::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->businessA->id,
            'user_id' => $this->cashierA->id,
            'order_number' => 'ORD-SEC-001',
            'order_date' => now()->toDateString(),
            'status' => PosOrder::STATUS_COMPLETED,
            'subtotal' => 75000.0,
            'total_amount' => 75000.0,
        ]);

        $posApprovalA = PosApprovalRequest::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->businessA->id,
            'requested_by' => $this->cashierA->id,
            'action_type' => PosApprovalRequest::ACTION_VOID,
            'reference_id' => $orderA->id,
            'payload' => ['order_number' => $orderA->order_number, 'reason' => 'Tenant isolation test'],
            'status' => PosApprovalRequest::STATUS_PENDING,
        ]);

        // Owner B attempts to approve Business A's approval
        Sanctum::actingAs($this->ownerB);

        $response = $this->withHeaders(['X-Business-Id' => $this->businessB->id])
            ->postJson("/api/v1/owner/approvals/pos_approval_{$posApprovalA->id}/action", [
                'action' => 'approve',
            ]);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Permintaan persetujuan tidak ditemukan.',
            ]);

        // Assert no state changes on Business A's records
        $this->assertSame(PosApprovalRequest::STATUS_PENDING, $posApprovalA->fresh()->status);
        $this->assertSame(PosOrder::STATUS_COMPLETED, $orderA->fresh()->status);
    }

    public function test_tenant_isolation_business_b_cannot_action_business_a_attendance_correction(): void
    {
        $attA = AttendanceCorrection::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->businessA->id,
            'user_id' => $this->staffA->id,
            'correction_number' => 'CORR-SEC-002',
            'target_date' => now()->toDateString(),
            'correction_type' => AttendanceCorrection::TYPE_CLOCK_IN_ONLY,
            'reason' => 'Tenant isolation test HRM',
            'status' => AttendanceCorrection::STATUS_PENDING,
        ]);

        Sanctum::actingAs($this->ownerB);

        $response = $this->withHeaders(['X-Business-Id' => $this->businessB->id])
            ->postJson("/api/v1/owner/approvals/attendance_correction_{$attA->id}/action", [
                'action' => 'approve',
            ]);

        $response->assertStatus(404);
        $this->assertSame(AttendanceCorrection::STATUS_PENDING, $attA->fresh()->status);
    }

    // =========================================================================
    // 3. IDEMPOTENCY & DUPLICATE ACTIONS
    // =========================================================================

    public function test_duplicate_action_on_pos_approval_returns_422_and_cannot_double_void(): void
    {
        $order = PosOrder::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->businessA->id,
            'user_id' => $this->cashierA->id,
            'order_number' => 'ORD-IDEM-001',
            'order_date' => now()->toDateString(),
            'status' => PosOrder::STATUS_COMPLETED,
            'subtotal' => 88000.0,
            'total_amount' => 88000.0,
        ]);

        $posApproval = PosApprovalRequest::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->businessA->id,
            'requested_by' => $this->cashierA->id,
            'action_type' => PosApprovalRequest::ACTION_VOID,
            'reference_id' => $order->id,
            'payload' => ['order_number' => $order->order_number, 'reason' => 'Idempotency test void'],
            'status' => PosApprovalRequest::STATUS_PENDING,
        ]);

        Sanctum::actingAs($this->ownerA);

        // Action 1: Approve -> HTTP 200
        $firstResponse = $this->withHeaders(['X-Business-Id' => $this->businessA->id])
            ->postJson("/api/v1/owner/approvals/pos_approval_{$posApproval->id}/action", [
                'action' => 'approve',
            ]);
        $firstResponse->assertStatus(200);

        $this->assertSame(PosApprovalRequest::STATUS_APPROVED, $posApproval->fresh()->status);
        $this->assertSame(PosOrder::STATUS_VOIDED, $order->fresh()->status);

        // Action 2: Duplicate Approve -> HTTP 422
        $secondResponse = $this->withHeaders(['X-Business-Id' => $this->businessA->id])
            ->postJson("/api/v1/owner/approvals/pos_approval_{$posApproval->id}/action", [
                'action' => 'approve',
            ]);
        $secondResponse->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Permintaan persetujuan telah diproses sebelumnya.',
            ]);

        // Action 3: Attempting Reject on Approved item -> HTTP 422
        $thirdResponse = $this->withHeaders(['X-Business-Id' => $this->businessA->id])
            ->postJson("/api/v1/owner/approvals/pos_approval_{$posApproval->id}/action", [
                'action' => 'reject',
                'reason' => 'Attempting flip after approve',
            ]);
        $thirdResponse->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Permintaan persetujuan telah diproses sebelumnya.',
            ]);

        // Status remains APPROVED
        $this->assertSame(PosApprovalRequest::STATUS_APPROVED, $posApproval->fresh()->status);
    }

    public function test_duplicate_action_on_attendance_correction_strictly_fails(): void
    {
        $correction = AttendanceCorrection::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->businessA->id,
            'user_id' => $this->staffA->id,
            'correction_number' => 'CORR-IDEM-002',
            'target_date' => now()->toDateString(),
            'correction_type' => AttendanceCorrection::TYPE_CLOCK_OUT_ONLY,
            'proposed_clock_out' => '17:30:00',
            'reason' => 'Idempotency attendance',
            'status' => AttendanceCorrection::STATUS_PENDING,
        ]);

        Sanctum::actingAs($this->ownerA);

        // First Action
        $this->withHeaders(['X-Business-Id' => $this->businessA->id])
            ->postJson("/api/v1/owner/approvals/attendance_correction_{$correction->id}/action", [
                'action' => 'approve',
            ])->assertStatus(200);

        $this->assertSame(AttendanceCorrection::STATUS_APPROVED, $correction->fresh()->status);

        // Second Action
        $this->withHeaders(['X-Business-Id' => $this->businessA->id])
            ->postJson("/api/v1/owner/approvals/attendance_correction_{$correction->id}/action", [
                'action' => 'approve',
            ])->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Permintaan persetujuan telah diproses sebelumnya.',
            ]);
    }

    // =========================================================================
    // 4. CASH LEDGER & DATABASE INTEGRITY
    // =========================================================================

    public function test_cash_expense_creates_exact_matching_records_and_updates_balances(): void
    {
        Sanctum::actingAs($this->cashierA);

        $expenseAmount = 73500.0;

        $response = $this->withHeaders(['X-Business-Id' => $this->businessA->id])
            ->postJson('/api/v1/pos/expenses', [
                'pos_shift_id' => $this->openShiftA->id,
                'amount' => $expenseAmount,
                'category' => 'supplies',
                'notes' => 'Beli perlengkapan kebersihan dan sabun',
            ]);

        $response->assertStatus(201);
        $json = $response->json('data');

        $movementId = $json['cash_movement_id'];
        $transactionId = $json['cash_transaction_id'];
        $expenseId = $json['id'];

        // 1. Verify PosCashMovement
        $movement = PosCashMovement::find($movementId);
        $this->assertNotNull($movement);
        $this->assertSame($this->businessA->id, $movement->business_id);
        $this->assertSame($this->openShiftA->id, $movement->pos_shift_id);
        $this->assertSame(PosCashMovement::TYPE_CASH_OUT, $movement->type);
        $this->assertSame($expenseAmount, (float) $movement->amount);
        $this->assertSame($this->cashierA->id, $movement->user_id);

        // 2. Verify CashTransaction in CashLedger
        $transaction = CashTransaction::find($transactionId);
        $this->assertNotNull($transaction);
        $this->assertSame($this->businessA->id, $transaction->business_id);
        $this->assertSame(CashTransaction::TYPE_OUT, $transaction->type);
        $this->assertSame('pos_shift_expense', $transaction->reference_type);
        $this->assertSame($expenseAmount, (float) $transaction->amount);

        // 3. Verify Expense
        $expense = Expense::find($expenseId);
        $this->assertNotNull($expense);
        $this->assertSame($this->businessA->id, $expense->business_id);
        $this->assertSame('supplies', $expense->category);
        $this->assertSame($expenseAmount, (float) $expense->amount);
        $this->assertSame('cash', $expense->payment_method);

        // 4. Verify Shift cash out accumulation
        $this->assertSame($expenseAmount, (float) $this->openShiftA->fresh()->total_cash_out);
    }

    public function test_consecutive_expenses_create_distinct_cash_transactions_without_collision(): void
    {
        Sanctum::actingAs($this->cashierA);

        $amounts = [15000.0, 25000.0, 35000.0];
        $totalExpected = array_sum($amounts);

        foreach ($amounts as $idx => $amt) {
            $response = $this->withHeaders(['X-Business-Id' => $this->businessA->id])
                ->postJson('/api/v1/pos/expenses', [
                    'pos_shift_id' => $this->openShiftA->id,
                    'amount' => $amt,
                    'category' => 'operational',
                    'notes' => "Consecutive expense #{$idx}",
                ]);
            $response->assertStatus(201);
        }

        // Assert 3 distinct movements
        $movements = PosCashMovement::where('pos_shift_id', $this->openShiftA->id)->get();
        $this->assertCount(3, $movements);
        $this->assertSame($totalExpected, (float) $movements->sum('amount'));

        // Assert 3 distinct cash transactions
        $transactions = CashTransaction::where('business_id', $this->businessA->id)
            ->where('reference_type', 'pos_shift_expense')
            ->get();
        $this->assertCount(3, $transactions);
        $this->assertSame($totalExpected, (float) $transactions->sum('amount'));

        // Assert total_cash_out on shift
        $this->assertSame($totalExpected, (float) $this->openShiftA->fresh()->total_cash_out);
    }
}
