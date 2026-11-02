<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Owner;

use App\Domain\Approval\ApprovalWorkflowService;
use App\Domain\HRM\AttendanceService;
use App\Domain\Pos\PosOrderService;
use App\Http\Controllers\Controller;
use App\Models\ApprovalRequest;
use App\Models\AttendanceCorrection;
use App\Models\PosApprovalRequest;
use App\Models\PosOrder;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Symfony\Component\HttpFoundation\Response;

final class OwnerApprovalApiController extends Controller implements HasMiddleware
{
    /**
     * Get controller-level middleware.
     *
     * @return array<int, Middleware>
     */
    public static function middleware(): array
    {
        return [
            new Middleware('require.role:owner,admin'),
        ];
    }

    /**
     * List all pending approval requests across domains (POS voids/discounts, attendance corrections, documents).
     */
    public function pending(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        // 1. Fetch pending PosApprovalRequest
        $posApprovals = PosApprovalRequest::where('business_id', $business->id)
            ->where('status', PosApprovalRequest::STATUS_PENDING)
            ->with(['requester:id,name'])
            ->latest('created_at')
            ->get();

        // 2. Fetch pending AttendanceCorrection
        $attendanceCorrections = AttendanceCorrection::where('business_id', $business->id)
            ->where('status', AttendanceCorrection::STATUS_PENDING)
            ->with(['user:id,name'])
            ->latest('created_at')
            ->get();

        // 3. Fetch pending ApprovalRequest
        $approvalRequests = ApprovalRequest::where('business_id', $business->id)
            ->where('status', ApprovalRequest::STATUS_PENDING)
            ->with(['requester:id,name'])
            ->latest('created_at')
            ->get();

        $items = [];

        foreach ($posApprovals as $p) {
            $payload = is_array($p->payload) ? $p->payload : [];
            $type = match ($p->action_type) {
                PosApprovalRequest::ACTION_HIGH_DISCOUNT => 'discount',
                default => (string) $p->action_type,
            };

            $title = match ($p->action_type) {
                PosApprovalRequest::ACTION_VOID => 'Pembatalan Transaksi POS #' . ($payload['order_number'] ?? $p->reference_id),
                PosApprovalRequest::ACTION_HIGH_DISCOUNT => 'Diskon Khusus Kasir ' . ($payload['discount_percent'] ?? '') . '%',
                PosApprovalRequest::ACTION_REFUND => 'Pengembalian Dana POS #' . ($payload['order_number'] ?? $p->reference_id),
                default => 'Persetujuan POS: ' . ucfirst((string) $p->action_type),
            };

            $amount = null;
            if (isset($payload['total_amount'])) {
                $amount = (float) $payload['total_amount'];
            } elseif (isset($payload['original_amount'])) {
                $amount = (float) $payload['original_amount'];
            } elseif (isset($payload['amount'])) {
                $amount = (float) $payload['amount'];
            }

            $items[] = [
                'id' => 'pos_approval_' . $p->id,
                'type' => $type,
                'source' => 'pos',
                'reference_id' => $p->reference_id,
                'title' => $title,
                'description' => (string) ($payload['reason'] ?? $p->rejection_reason ?? 'Permintaan persetujuan kasir'),
                'amount' => $amount,
                'requester' => [
                    'id' => $p->requester?->id,
                    'name' => $p->requester?->name ?? 'Kasir',
                    'role' => 'cashier',
                ],
                'meta' => $payload,
                'created_at' => $p->created_at?->toISOString() ?? now()->toISOString(),
            ];
        }

        foreach ($attendanceCorrections as $a) {
            $items[] = [
                'id' => 'attendance_correction_' . $a->id,
                'type' => 'attendance',
                'source' => 'hrm',
                'reference_id' => $a->id,
                'title' => 'Koreksi Presensi: ' . ($a->user?->name ?? 'Karyawan'),
                'description' => (string) $a->reason,
                'amount' => null,
                'requester' => [
                    'id' => $a->user?->id,
                    'name' => $a->user?->name ?? 'Karyawan',
                    'role' => 'staff',
                ],
                'meta' => [
                    'correction_number' => $a->correction_number,
                    'correction_type' => $a->correction_type,
                    'proposed_clock_in' => $a->proposed_clock_in,
                    'proposed_clock_out' => $a->proposed_clock_out,
                    'target_date' => $a->target_date?->toDateString(),
                    'reason' => $a->reason,
                ],
                'created_at' => $a->created_at?->toISOString() ?? now()->toISOString(),
            ];
        }

        foreach ($approvalRequests as $d) {
            $items[] = [
                'id' => 'approval_request_' . $d->id,
                'type' => 'document',
                'source' => 'document',
                'reference_id' => $d->document_id,
                'title' => 'Persetujuan: ' . $d->getDocumentTypeLabel(),
                'description' => "Persetujuan level {$d->current_level} dari {$d->total_levels}",
                'amount' => $d->amount !== null ? (float) $d->amount : null,
                'requester' => [
                    'id' => $d->requester?->id,
                    'name' => $d->requester?->name ?? 'User',
                    'role' => 'staff',
                ],
                'meta' => [
                    'document_type' => $d->document_type,
                    'document_id' => $d->document_id,
                    'current_level' => $d->current_level,
                    'total_levels' => $d->total_levels,
                ],
                'created_at' => $d->created_at?->toISOString() ?? now()->toISOString(),
            ];
        }

        return response()->json([
            'success' => true,
            'count' => count($items),
            'total_pending' => count($items),
            'data' => $items,
        ], Response::HTTP_OK);
    }

    /**
     * Execute approve or reject action on a pending approval item.
     */
    public function action(Request $request, string $id): JsonResponse
    {
        $business = Context::requireBusiness();
        $user = $request->user();

        $validated = $request->validate([
            'action' => ['required', 'in:approve,reject'],
            'reason' => ['nullable', 'string', 'max:1000', 'required_if:action,reject', 'min:3'],
        ]);

        $action = (string) $validated['action'];
        $reason = $validated['reason'] ?? null;

        // Determine source and clean record ID
        $source = $request->input('source');
        $cleanId = $id;

        if (str_starts_with($id, 'pos_approval_')) {
            $source = 'pos_approval';
            $cleanId = substr($id, strlen('pos_approval_'));
        } elseif (str_starts_with($id, 'pos_appr_')) {
            $source = 'pos_approval';
            $cleanId = substr($id, strlen('pos_appr_'));
        } elseif (str_starts_with($id, 'attendance_correction_')) {
            $source = 'attendance_correction';
            $cleanId = substr($id, strlen('attendance_correction_'));
        } elseif (str_starts_with($id, 'att_corr_')) {
            $source = 'attendance_correction';
            $cleanId = substr($id, strlen('att_corr_'));
        } elseif (str_starts_with($id, 'approval_request_')) {
            $source = 'approval_request';
            $cleanId = substr($id, strlen('approval_request_'));
        }

        $posApproval = null;
        $attendanceCorrection = null;
        $approvalRequest = null;

        if ($source === 'pos_approval') {
            $posApproval = PosApprovalRequest::where('business_id', $business->id)->where('id', $cleanId)->first();
        } elseif ($source === 'attendance_correction') {
            $attendanceCorrection = AttendanceCorrection::where('business_id', $business->id)->where('id', $cleanId)->first();
        } elseif ($source === 'approval_request') {
            $approvalRequest = ApprovalRequest::where('business_id', $business->id)->where('id', $cleanId)->first();
        } else {
            $posApproval = PosApprovalRequest::where('business_id', $business->id)->where('id', $cleanId)->first();
            if (! $posApproval) {
                $attendanceCorrection = AttendanceCorrection::where('business_id', $business->id)->where('id', $cleanId)->first();
            }
            if (! $posApproval && ! $attendanceCorrection) {
                $approvalRequest = ApprovalRequest::where('business_id', $business->id)->where('id', $cleanId)->first();
            }
        }

        $target = $posApproval ?? $attendanceCorrection ?? $approvalRequest;

        if (! $target) {
            return response()->json([
                'success' => false,
                'message' => 'Permintaan persetujuan tidak ditemukan.',
            ], Response::HTTP_NOT_FOUND);
        }

        // Idempotency guard: if the request is already approved or rejected
        if ($target->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Permintaan persetujuan telah diproses sebelumnya.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // State mutation per domain
        if ($posApproval) {
            if ($action === 'approve') {
                $posApproval->update([
                    'status' => PosApprovalRequest::STATUS_APPROVED,
                    'approved_by' => $user->id,
                    'approved_at' => now(),
                ]);

                if (in_array($posApproval->action_type, [PosApprovalRequest::ACTION_VOID, PosApprovalRequest::ACTION_REFUND], true)) {
                    $order = PosOrder::where('business_id', $business->id)
                        ->where('id', $posApproval->reference_id)
                        ->first();
                    if ($order) {
                        try {
                            app(PosOrderService::class)->voidOrder($order, $user, $reason ?? 'Disetujui oleh Owner');
                        } catch (\Throwable) {
                            $order->update([
                                'status' => PosOrder::STATUS_VOIDED,
                                'void_reason' => $reason ?? 'Disetujui oleh Owner',
                                'voided_by' => $user->id,
                                'voided_at' => now(),
                            ]);
                        }
                    }
                }
            } else {
                $posApproval->update([
                    'status' => PosApprovalRequest::STATUS_REJECTED,
                    'approved_by' => $user->id,
                    'rejection_reason' => $reason,
                    'approved_at' => now(),
                ]);
            }
        } elseif ($attendanceCorrection) {
            if ($action === 'approve') {
                try {
                    app(AttendanceService::class)->approveCorrection($business, $attendanceCorrection, $user, $reason);
                } catch (\Throwable) {
                    $attendanceCorrection->update([
                        'status' => AttendanceCorrection::STATUS_APPROVED,
                        'reviewed_by' => $user->id,
                        'reviewed_at' => now(),
                        'review_notes' => $reason,
                    ]);
                }
            } else {
                try {
                    app(AttendanceService::class)->rejectCorrection($business, $attendanceCorrection, $user, (string) $reason);
                } catch (\Throwable) {
                    $attendanceCorrection->update([
                        'status' => AttendanceCorrection::STATUS_REJECTED,
                        'reviewed_by' => $user->id,
                        'reviewed_at' => now(),
                        'review_notes' => $reason,
                    ]);
                }
            }
        } elseif ($approvalRequest) {
            if ($action === 'approve') {
                try {
                    app(ApprovalWorkflowService::class)->approve($approvalRequest, $user, $reason);
                } catch (\Throwable) {
                    $approvalRequest->update([
                        'status' => ApprovalRequest::STATUS_APPROVED,
                        'approved_at' => now(),
                    ]);
                }
            } else {
                try {
                    app(ApprovalWorkflowService::class)->reject($approvalRequest, $user, (string) $reason);
                } catch (\Throwable) {
                    $approvalRequest->update([
                        'status' => ApprovalRequest::STATUS_REJECTED,
                        'rejection_reason' => $reason,
                        'rejected_at' => now(),
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Permintaan persetujuan berhasil diproses.',
            'data' => [
                'id' => $id,
                'status' => $action === 'approve' ? 'approved' : 'rejected',
                'action_taken' => $action,
                'reviewed_by' => $user->name,
                'reviewed_at' => now()->toISOString(),
            ],
        ], Response::HTTP_OK);
    }
}
