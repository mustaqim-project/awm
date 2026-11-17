<?php

declare(strict_types=1);

namespace App\Domain\Mcp\Tools;

use App\Domain\Approval\ApprovalWorkflowService;
use App\Domain\Mcp\Tools\Traits\HandlesCrudPermissions;
use App\Models\ApprovalRequest;
use App\Models\AuditLog;
use App\Models\Business;
use App\Models\User;
use InvalidArgumentException;

final class ApprovalManageWorkflowTool implements McpToolInterface
{
    use HandlesCrudPermissions;

    public function __construct(
        private readonly ApprovalWorkflowService $approvalService = new ApprovalWorkflowService()
    ) {}

    public function getName(): string
    {
        return 'approval_manage_workflow';
    }

    public function getDescription(): string
    {
        return 'Mengelola alur persetujuan dokumen berjenjang (Approvals) untuk Purchase Order, Sales Order, dan Pengeluaran Biaya: melihat dokumen pending approval, menyetujui dokumen (approve), atau menolak dokumen (reject).';
    }

    public function getRequiredAbility(): string
    {
        return 'mcp:approvals:read';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'action' => [
                    'type' => 'string',
                    'enum' => ['list', 'approve', 'reject'],
                    'default' => 'list',
                    'description' => 'Aksi: list (daftar dokumen menunggu approval), approve (setujui dokumen), reject (tolak dokumen).',
                ],
                'approval_request_id' => [
                    'type' => 'string',
                    'description' => 'UUID permohonan persetujuan (wajib untuk action approve dan reject).',
                ],
                'notes' => [
                    'type' => 'string',
                    'description' => 'Catatan otorisasi saat menyetujui dokumen.',
                ],
                'reason' => [
                    'type' => 'string',
                    'description' => 'Alasan penolakan dokumen (wajib untuk action reject).',
                ],
                'document_type' => [
                    'type' => 'string',
                    'enum' => ['purchase_order', 'sales_order', 'expense'],
                    'description' => 'Filter tipe dokumen.',
                ],
            ],
        ];
    }

    public function execute(Business $business, ?User $user, array $arguments = []): array
    {
        $action = trim((string) ($arguments['action'] ?? 'list'));

        return match ($action) {
            'approve' => $this->approveRequest($business, $user, $arguments),
            'reject'  => $this->rejectRequest($business, $user, $arguments),
            default   => $this->listRequests($business, $user, $arguments),
        };
    }

    private function listRequests(Business $business, ?User $user, array $args): array
    {
        $this->assertCan('approvals', 'read', $business, $user);

        $query = ApprovalRequest::where('business_id', $business->id)
            ->where('status', ApprovalRequest::STATUS_PENDING)
            ->with(['rule', 'requester:id,name,email'])
            ->latest();

        if (! empty($args['document_type'])) {
            $query->where('document_type', $args['document_type']);
        }

        $requests = $query->take(20)->get();

        return [
            'success' => true,
            'message' => 'Berhasil mengambil daftar permohonan approval yang menunggu persetujuan.',
            'count' => $requests->count(),
            'pending_approvals' => $requests->map(fn (ApprovalRequest $r) => [
                'id' => $r->id,
                'document_type' => $r->document_type,
                'document_id' => $r->document_id,
                'document_label' => $r->document_label,
                'total_amount' => (float) $r->total_amount,
                'current_level' => $r->current_level,
                'total_levels' => $r->total_levels,
                'requester' => $r->requester?->name,
                'created_at' => $r->created_at?->toDateTimeString(),
            ])->all(),
        ];
    }

    private function approveRequest(Business $business, ?User $user, array $args): array
    {
        $this->assertCan('approvals', 'update', $business, $user);

        $id = $args['approval_request_id'] ?? null;
        if (! $id) {
            throw new InvalidArgumentException('approval_request_id wajib diisi untuk approve.');
        }

        $request = ApprovalRequest::where('business_id', $business->id)->findOrFail($id);
        $approver = $user ?? User::where('active_business_id', $business->id)->firstOrFail();

        $updated = $this->approvalService->approve($request, $approver, $args['notes'] ?? 'Disetujui via MCP AI Agent');

        AuditLog::create([
            'business_id' => $business->id,
            'user_id' => $approver->id,
            'action' => 'mcp_approval_approved',
            'auditable_type' => ApprovalRequest::class,
            'auditable_id' => $request->id,
            'new_values' => ['status' => $updated->status, 'current_level' => $updated->current_level],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'COOCA-MCP-Agent/1.0',
        ]);

        return [
            'success' => true,
            'message' => "Permohonan persetujuan untuk [{$request->document_label}] berhasil disetujui (Status sekarang: {$updated->status}).",
            'approval' => [
                'id' => $updated->id,
                'status' => $updated->status,
                'current_level' => $updated->current_level,
                'total_levels' => $updated->total_levels,
            ],
        ];
    }

    private function rejectRequest(Business $business, ?User $user, array $args): array
    {
        $this->assertCan('approvals', 'update', $business, $user);

        $id = $args['approval_request_id'] ?? null;
        $reason = trim((string) ($args['reason'] ?? ''));

        if (! $id || empty($reason)) {
            throw new InvalidArgumentException('approval_request_id dan reason (alasan penolakan) wajib diisi.');
        }

        $request = ApprovalRequest::where('business_id', $business->id)->findOrFail($id);
        $approver = $user ?? User::where('active_business_id', $business->id)->firstOrFail();

        $updated = $this->approvalService->reject($request, $approver, $reason);

        AuditLog::create([
            'business_id' => $business->id,
            'user_id' => $approver->id,
            'action' => 'mcp_approval_rejected',
            'auditable_type' => ApprovalRequest::class,
            'auditable_id' => $request->id,
            'new_values' => ['status' => $updated->status, 'rejection_reason' => $reason],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'COOCA-MCP-Agent/1.0',
        ]);

        return [
            'success' => true,
            'message' => "Permohonan persetujuan untuk [{$request->document_label}] ditolak dengan alasan: \"{$reason}\".",
            'approval' => [
                'id' => $updated->id,
                'status' => $updated->status,
                'rejection_reason' => $updated->rejection_reason,
            ],
        ];
    }
}
