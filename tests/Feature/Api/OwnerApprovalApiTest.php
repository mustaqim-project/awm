<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\ApprovalRequest;
use App\Models\ApprovalRule;
use App\Models\AttendanceCorrection;
use App\Models\Business;
use App\Models\PosApprovalRequest;
use App\Models\PosOrder;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class OwnerApprovalApiTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $owner;
    private User $cashier;
    private User $employee;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        // 1. Business
        $this->business = Business::create([
            'id' => (string) Str::uuid(),
            'name' => 'Resto Sedap Rasa',
            'slug' => 'resto-sedap-rasa',
            'is_active' => true,
        ]);

        // 2. Owner User
        $this->owner = User::create([
            'name' => 'Owner Utama',
            'email' => 'owner@sedaprasa.com',
            'password' => bcrypt('password123'),
        ]);
        $this->business->users()->attach($this->owner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);
        $this->owner->update(['active_business_id' => $this->business->id]);

        // 3. Cashier User
        $this->cashier = User::create([
            'name' => 'Budi Kasir',
            'email' => 'budi@sedaprasa.com',
            'password' => bcrypt('password123'),
        ]);
        $this->business->users()->attach($this->cashier->id, [
            'id' => (string) Str::uuid(),
            'role' => 'cashier',
            'is_active' => true,
        ]);
        $this->cashier->update(['active_business_id' => $this->business->id]);

        // 4. Employee User
        $this->employee = User::create([
            'name' => 'Siti Rahma',
            'email' => 'siti@sedaprasa.com',
            'password' => bcrypt('password123'),
        ]);
        $this->business->users()->attach($this->employee->id, [
            'id' => (string) Str::uuid(),
            'role' => 'staff',
            'is_active' => true,
        ]);
        $this->employee->update(['active_business_id' => $this->business->id]);
    }

    public function test_unauthenticated_request_is_rejected(): void
    {
        $this->getJson('/api/v1/owner/approvals/pending')
            ->assertStatus(401);

        $this->postJson('/api/v1/owner/approvals/item-123/action', [
            'action' => 'approve',
        ])->assertStatus(401);
    }

    public function test_non_owner_role_is_forbidden(): void
    {
        Sanctum::actingAs($this->cashier);

        // Cashier role cannot access owner approval center
        $this->withHeaders(['X-Business-Id' => $this->business->id])
            ->getJson('/api/v1/owner/approvals/pending')
            ->assertStatus(403)
            ->assertJson([
                'error_code' => 'FORBIDDEN_ROLE',
            ]);

        $this->withHeaders(['X-Business-Id' => $this->business->id])
            ->postJson('/api/v1/owner/approvals/item-123/action', [
                'action' => 'approve',
            ])->assertStatus(403)
            ->assertJson([
                'error_code' => 'FORBIDDEN_ROLE',
            ]);
    }

    public function test_owner_can_list_aggregated_pending_approvals(): void
    {
        // 1. POS Void Approval Request
        PosApprovalRequest::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'requested_by' => $this->cashier->id,
            'action_type' => PosApprovalRequest::ACTION_VOID,
            'reference_id' => '12',
            'payload' => [
                'order_id' => '12',
                'order_number' => 'TRX-9821',
                'total_amount' => 145000.0,
                'reason' => 'Pelanggan salah pesan menu',
            ],
            'status' => PosApprovalRequest::STATUS_PENDING,
        ]);

        // 2. POS High Discount Request
        PosApprovalRequest::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'requested_by' => $this->cashier->id,
            'action_type' => PosApprovalRequest::ACTION_HIGH_DISCOUNT,
            'reference_id' => '15',
            'payload' => [
                'order_id' => '15',
                'order_number' => 'TRX-9825',
                'discount_percent' => 25.0,
                'total_amount' => 200000.0,
                'reason' => 'Tamu VIP',
            ],
            'status' => PosApprovalRequest::STATUS_PENDING,
        ]);

        // 3. Attendance Correction Request
        AttendanceCorrection::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'user_id' => $this->employee->id,
            'correction_number' => 'CORR-2026-0004',
            'target_date' => now()->toDateString(),
            'correction_type' => AttendanceCorrection::TYPE_CLOCK_OUT_ONLY,
            'proposed_clock_out' => '17:00:00',
            'reason' => 'Lupa clock-out karena listrik padam',
            'status' => AttendanceCorrection::STATUS_PENDING,
        ]);

        // 4. Document Approval Request
        ApprovalRequest::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'document_type' => ApprovalRule::DOC_EXPENSE,
            'document_id' => (string) Str::uuid(),
            'requester_id' => $this->employee->id,
            'amount' => 500000.0,
            'current_level' => 1,
            'total_levels' => 1,
            'status' => ApprovalRequest::STATUS_PENDING,
        ]);

        Sanctum::actingAs($this->owner);

        $response = $this->withHeaders(['X-Business-Id' => $this->business->id])
            ->getJson('/api/v1/owner/approvals/pending');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'count' => 4,
            ]);

        $data = $response->json('data');
        $this->assertCount(4, $data);

        // Check unified structure
        $first = $data[0];
        $this->assertArrayHasKey('id', $first);
        $this->assertArrayHasKey('type', $first);
        $this->assertArrayHasKey('source', $first);
        $this->assertArrayHasKey('reference_id', $first);
        $this->assertArrayHasKey('title', $first);
        $this->assertArrayHasKey('description', $first);
        $this->assertArrayHasKey('amount', $first);
        $this->assertArrayHasKey('requester', $first);
        $this->assertArrayHasKey('meta', $first);
        $this->assertArrayHasKey('created_at', $first);

        // Check types present
        $types = collect($data)->pluck('type')->all();
        $this->assertContains('void', $types);
        $this->assertContains('discount', $types);
        $this->assertContains('attendance', $types);
        $this->assertContains('document', $types);
    }

    public function test_owner_can_approve_void_pos_approval_and_order_becomes_voided(): void
    {
        $order = PosOrder::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'user_id' => $this->cashier->id,
            'order_number' => 'ORD-202611-0001',
            'order_date' => now()->toDateString(),
            'status' => PosOrder::STATUS_COMPLETED,
            'subtotal' => 125000.0,
            'total_amount' => 125000.0,
        ]);

        $posApproval = PosApprovalRequest::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'requested_by' => $this->cashier->id,
            'action_type' => PosApprovalRequest::ACTION_VOID,
            'reference_id' => $order->id,
            'payload' => [
                'order_number' => $order->order_number,
                'total_amount' => 125000.0,
                'reason' => 'Salah input meja dan item',
            ],
            'status' => PosApprovalRequest::STATUS_PENDING,
        ]);

        Sanctum::actingAs($this->owner);

        $response = $this->withHeaders(['X-Business-Id' => $this->business->id])
            ->postJson("/api/v1/owner/approvals/pos_approval_{$posApproval->id}/action", [
                'action' => 'approve',
                'reason' => 'Disetujui untuk pembatalan pesanan',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Permintaan persetujuan berhasil diproses.',
                'data' => [
                    'id' => "pos_approval_{$posApproval->id}",
                    'status' => 'approved',
                    'action_taken' => 'approve',
                    'reviewed_by' => $this->owner->name,
                ],
            ]);

        // Verify PosApprovalRequest updated
        $this->assertSame(PosApprovalRequest::STATUS_APPROVED, $posApproval->fresh()->status);
        $this->assertSame($this->owner->id, $posApproval->fresh()->approved_by);
        $this->assertNotNull($posApproval->fresh()->approved_at);

        // Verify PosOrder status becomes voided
        $this->assertSame(PosOrder::STATUS_VOIDED, $order->fresh()->status);
    }

    public function test_owner_can_reject_pos_approval_with_mandatory_reason(): void
    {
        $posApproval = PosApprovalRequest::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'requested_by' => $this->cashier->id,
            'action_type' => PosApprovalRequest::ACTION_HIGH_DISCOUNT,
            'reference_id' => '102',
            'payload' => [
                'discount_percent' => 50.0,
                'reason' => 'Diskon teman kasir',
            ],
            'status' => PosApprovalRequest::STATUS_PENDING,
        ]);

        Sanctum::actingAs($this->owner);

        // 1. Rejection without reason fails validation
        $failResponse = $this->withHeaders(['X-Business-Id' => $this->business->id])
            ->postJson("/api/v1/owner/approvals/pos_approval_{$posApproval->id}/action", [
                'action' => 'reject',
                'reason' => '',
            ]);
        $failResponse->assertStatus(422)
            ->assertJsonValidationErrors(['reason']);

        // 2. Rejection with reason succeeds
        $successResponse = $this->withHeaders(['X-Business-Id' => $this->business->id])
            ->postJson("/api/v1/owner/approvals/pos_approval_{$posApproval->id}/action", [
                'action' => 'reject',
                'reason' => 'Diskon 50% terlalu besar, batas maksimal hanya 10%',
            ]);

        $successResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'status' => 'rejected',
                    'action_taken' => 'reject',
                ],
            ]);

        $this->assertSame(PosApprovalRequest::STATUS_REJECTED, $posApproval->fresh()->status);
        $this->assertSame('Diskon 50% terlalu besar, batas maksimal hanya 10%', $posApproval->fresh()->rejection_reason);
    }

    public function test_idempotency_prevents_processing_already_resolved_approval(): void
    {
        $posApproval = PosApprovalRequest::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'requested_by' => $this->cashier->id,
            'action_type' => PosApprovalRequest::ACTION_VOID,
            'reference_id' => '99',
            'status' => PosApprovalRequest::STATUS_APPROVED, // Already approved
        ]);

        Sanctum::actingAs($this->owner);

        $response = $this->withHeaders(['X-Business-Id' => $this->business->id])
            ->postJson("/api/v1/owner/approvals/pos_approval_{$posApproval->id}/action", [
                'action' => 'approve',
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Permintaan persetujuan telah diproses sebelumnya.',
            ]);
    }

    public function test_owner_can_approve_attendance_correction(): void
    {
        $correction = AttendanceCorrection::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'user_id' => $this->employee->id,
            'correction_number' => 'CORR-2026-0008',
            'target_date' => now()->toDateString(),
            'correction_type' => AttendanceCorrection::TYPE_CLOCK_IN_ONLY,
            'proposed_clock_in' => '08:00:00',
            'reason' => 'Lupa clock in',
            'status' => AttendanceCorrection::STATUS_PENDING,
        ]);

        Sanctum::actingAs($this->owner);

        $response = $this->withHeaders(['X-Business-Id' => $this->business->id])
            ->postJson("/api/v1/owner/approvals/attendance_correction_{$correction->id}/action", [
                'action' => 'approve',
                'reason' => 'Data presensi sudah dikonfirmasi ke supervisor',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'status' => 'approved',
                    'action_taken' => 'approve',
                ],
            ]);

        $this->assertSame(AttendanceCorrection::STATUS_APPROVED, $correction->fresh()->status);
        $this->assertSame($this->owner->id, $correction->fresh()->reviewed_by);
    }

    public function test_owner_can_reject_attendance_correction(): void
    {
        $correction = AttendanceCorrection::create([
            'id' => (string) Str::uuid(),
            'business_id' => $this->business->id,
            'user_id' => $this->employee->id,
            'correction_number' => 'CORR-2026-0009',
            'target_date' => now()->toDateString(),
            'correction_type' => AttendanceCorrection::TYPE_CLOCK_IN_ONLY,
            'proposed_clock_in' => '08:00:00',
            'reason' => 'Terlambat tapi minta on-time',
            'status' => AttendanceCorrection::STATUS_PENDING,
        ]);

        Sanctum::actingAs($this->owner);

        $response = $this->withHeaders(['X-Business-Id' => $this->business->id])
            ->postJson("/api/v1/owner/approvals/attendance_correction_{$correction->id}/action", [
                'action' => 'reject',
                'reason' => 'CCTV menunjukkan karyawan baru tiba pukul 09:15',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'status' => 'rejected',
                    'action_taken' => 'reject',
                ],
            ]);

        $this->assertSame(AttendanceCorrection::STATUS_REJECTED, $correction->fresh()->status);
    }

    public function test_cannot_action_non_existent_approval(): void
    {
        Sanctum::actingAs($this->owner);

        $response = $this->withHeaders(['X-Business-Id' => $this->business->id])
            ->postJson('/api/v1/owner/approvals/pos_approval_' . Str::uuid() . '/action', [
                'action' => 'approve',
            ]);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Permintaan persetujuan tidak ditemukan.',
            ]);
    }
}
