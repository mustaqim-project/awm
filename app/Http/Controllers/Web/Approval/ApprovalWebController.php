<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Approval;

use App\Domain\Approval\ApprovalWorkflowService;
use App\Http\Controllers\Controller;
use App\Models\ApprovalRequest;
use App\Models\ApprovalRule;
use App\Models\AuditLog;
use App\Models\Business;
use App\Models\Role;
use App\Support\Context;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

final class ApprovalWebController extends Controller implements HasMiddleware
{
    /**
     * Daftarkan Controller-Level RBAC Middleware.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('require.permission:approvals.view', only: ['inbox', 'history']),
            new Middleware('require.permission:approvals.manage', only: [
                'approve',
                'reject',
                'rulesIndex',
                'rulesStore',
                'rulesUpdate',
                'rulesDestroy',
            ]),
        ];
    }

    public function __construct(
        private readonly ApprovalWorkflowService $workflowService
    ) {}

    /**
     * Display pending approvals inbox.
     * Menggunakan single-query collection filter untuk efisiensi N+1.
     */
    public function inbox(Request $request): View
    {
        $business = Context::requireBusiness();
        $user = Context::user();

        $filterType = (string) $request->query('type', '');
        $allPending = $this->workflowService->getPendingRequestsForUser($business, $user);

        $counts = [
            'all' => $allPending->count(),
            'purchase_order' => $allPending->where('document_type', ApprovalRule::DOC_PURCHASE_ORDER)->count(),
            'expense' => $allPending->where('document_type', ApprovalRule::DOC_EXPENSE)->count(),
            'supplier_invoice' => $allPending->where('document_type', ApprovalRule::DOC_SUPPLIER_INVOICE)->count(),
            'stock_adjustment' => $allPending->where('document_type', ApprovalRule::DOC_STOCK_ADJUSTMENT)->count(),
        ];

        $pendingTickets = ! empty($filterType)
            ? $allPending->where('document_type', $filterType)->values()
            : $allPending;

        return view('app.approvals.inbox', compact('business', 'pendingTickets', 'filterType', 'counts'));
    }

    /**
     * Display approval history.
     */
    public function history(Request $request): View
    {
        $business = Context::requireBusiness();
        $filterType = $request->query('type');
        $historyTickets = $this->workflowService->getHistoryRequests($business, $filterType);

        return view('app.approvals.history', compact('business', 'historyTickets', 'filterType'));
    }

    /**
     * Approve a pending ticket at current level.
     */
    public function approve(ApprovalRequest $approvalRequest, Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless($approvalRequest->business_id === $business->id, 404);

        $user = Context::user();
        if (! $this->workflowService->canUserApprove($approvalRequest, $user)) {
            abort(403, 'Anda tidak memiliki hak otorisasi untuk menyetujui dokumen pada level ini.');
        }

        $notes = $request->input('notes');

        try {
            $updated = $this->workflowService->approve($approvalRequest, $user, $notes);

            // Audit log untuk rekam jejak anti-fraud
            AuditLog::create([
                'business_id' => $business->id,
                'user_id' => $user?->id,
                'auditable_type' => ApprovalRequest::class,
                'auditable_id' => $approvalRequest->id,
                'action' => 'approval.approved',
                'notes' => "Disetujui Level {$approvalRequest->current_level} ({$approvalRequest->document_type} #{$approvalRequest->document_id})",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
            ]);

            $statusMsg = $updated->isApproved()
                ? 'Dokumen telah disetujui sepenuhnya pada seluruh tingkat otorisasi (Siap Dicairkan/Dieksekusi).'
                : "Persetujuan Level {$approvalRequest->current_level} berhasil dicatat. Dokumen berlanjut ke Level {$updated->current_level}.";

            return back()->with('success', $statusMsg);
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menyetujui dokumen: ' . $e->getMessage());
        }
    }

    /**
     * Reject a pending ticket with mandatory notes.
     */
    public function reject(ApprovalRequest $approvalRequest, Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless($approvalRequest->business_id === $business->id, 404);

        $user = Context::user();
        if (! $this->workflowService->canUserApprove($approvalRequest, $user)) {
            abort(403, 'Anda tidak memiliki hak otorisasi untuk menolak dokumen pada level ini.');
        }

        $reason = $request->input('reason') ?? $request->input('notes');
        if (empty($reason) || strlen(trim((string) $reason)) < 3) {
            return back()->with('error', 'Catatan alasan penolakan wajib diisi untuk rekam jejak audit pimpinan (minimal 3 karakter).');
        }

        try {
            $this->workflowService->reject($approvalRequest, $user, (string) $reason);

            // Audit log penolakan (Risk Medium)
            AuditLog::create([
                'business_id' => $business->id,
                'user_id' => $user?->id,
                'auditable_type' => ApprovalRequest::class,
                'auditable_id' => $approvalRequest->id,
                'action' => 'approval.rejected',
                'risk_level' => AuditLog::RISK_MEDIUM,
                'risk_reason' => 'Penolakan dokumen transaksi pengadaan/biaya',
                'notes' => "Ditolak: {$reason}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
            ]);

            return back()->with('success', 'Dokumen berhasil ditolak. Pembuat draf (Maker) telah dinotifikasi dengan catatan penolakan.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menolak dokumen: ' . $e->getMessage());
        }
    }

    /**
     * Approval rules management for Owner/Admin.
     */
    public function rulesIndex(): View
    {
        $business = Context::requireBusiness();
        $rules = ApprovalRule::where('business_id', $business->id)
            ->orderBy('document_type')
            ->orderBy('min_amount')
            ->get();

        $availableRoles = Role::where(function ($q) use ($business) {
            $q->where('business_id', $business->id)->orWhereNull('business_id');
        })->get();

        return view('app.approvals.rules', compact('business', 'rules', 'availableRoles'));
    }

    /**
     * Store new approval rule.
     */
    public function rulesStore(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'document_type' => ['required', 'string', 'in:purchase_order,expense,supplier_invoice,stock_adjustment'],
            'name' => ['nullable', 'string', 'max:100'],
            'min_amount' => ['required', 'numeric', 'gte:0'],
            'max_amount' => ['nullable', 'numeric', 'gte:min_amount'],
            'required_levels' => ['required', 'integer', 'between:1,3'],
            'approver_role_level_1' => ['nullable', 'string', 'max:50'],
            'approver_role_level_2' => ['nullable', 'string', 'max:50'],
            'approver_role_level_3' => ['nullable', 'string', 'max:50'],
            'level_1_role' => ['nullable', 'string', 'max:50'],
            'level_2_role' => ['nullable', 'string', 'max:50'],
            'level_3_role' => ['nullable', 'string', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $levels = (int) $validated['required_levels'];
        $docType = (string) $validated['document_type'];

        $validated['name'] = $request->input('name') ?: ('Aturan Plafon ' . ucfirst(str_replace('_', ' ', $docType)));
        $validated['business_id'] = $business->id;
        $validated['approver_role_level_1'] = (string) ($request->input('approver_role_level_1') ?? $request->input('level_1_role') ?? 'supervisor');
        $validated['approver_role_level_2'] = $levels >= 2 ? (string) ($request->input('approver_role_level_2') ?? $request->input('level_2_role') ?? 'manager') : null;
        $validated['approver_role_level_3'] = $levels >= 3 ? (string) ($request->input('approver_role_level_3') ?? $request->input('level_3_role') ?? 'owner') : null;
        $validated['is_active'] = $request->boolean('is_active', true);

        $rule = ApprovalRule::create($validated);

        AuditLog::create([
            'business_id' => $business->id,
            'user_id' => Context::user()?->id,
            'auditable_type' => ApprovalRule::class,
            'auditable_id' => $rule->id,
            'action' => 'approval_rule.created',
            'notes' => "Aturan baru: {$rule->name} ({$rule->document_type})",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);

        return redirect()->route('approval-rules.index')->with('success', 'Aturan otorisasi dokumen berhasil ditambahkan.');
    }

    /**
     * Update existing approval rule.
     */
    public function rulesUpdate(ApprovalRule $approvalRule, Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless($approvalRule->business_id === $business->id, 404);

        $validated = $request->validate([
            'document_type' => ['nullable', 'string', 'in:purchase_order,expense,supplier_invoice,stock_adjustment'],
            'name' => ['nullable', 'string', 'max:100'],
            'min_amount' => ['required', 'numeric', 'gte:0'],
            'max_amount' => ['nullable', 'numeric', 'gte:min_amount'],
            'required_levels' => ['required', 'integer', 'between:1,3'],
            'approver_role_level_1' => ['nullable', 'string', 'max:50'],
            'approver_role_level_2' => ['nullable', 'string', 'max:50'],
            'approver_role_level_3' => ['nullable', 'string', 'max:50'],
            'level_1_role' => ['nullable', 'string', 'max:50'],
            'level_2_role' => ['nullable', 'string', 'max:50'],
            'level_3_role' => ['nullable', 'string', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $levels = (int) $validated['required_levels'];

        $validated['name'] = $request->input('name') ?: $approvalRule->name;
        if (! empty($validated['document_type'])) {
            $approvalRule->document_type = (string) $validated['document_type'];
        }
        $validated['approver_role_level_1'] = (string) ($request->input('approver_role_level_1') ?? $request->input('level_1_role') ?? $approvalRule->approver_role_level_1);
        $validated['approver_role_level_2'] = $levels >= 2 ? (string) ($request->input('approver_role_level_2') ?? $request->input('level_2_role') ?? 'manager') : null;
        $validated['approver_role_level_3'] = $levels >= 3 ? (string) ($request->input('approver_role_level_3') ?? $request->input('level_3_role') ?? 'owner') : null;
        $validated['is_active'] = $request->boolean('is_active', true);

        $approvalRule->update($validated);

        AuditLog::create([
            'business_id' => $business->id,
            'user_id' => Context::user()?->id,
            'auditable_type' => ApprovalRule::class,
            'auditable_id' => $approvalRule->id,
            'action' => 'approval_rule.updated',
            'notes' => "Pembaruan aturan: {$approvalRule->name}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);

        return redirect()->route('approval-rules.index')->with('success', 'Aturan otorisasi dokumen berhasil diperbarui.');
    }

    /**
     * Delete approval rule.
     */
    public function rulesDestroy(ApprovalRule $approvalRule): RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless($approvalRule->business_id === $business->id, 404);

        $ruleId = $approvalRule->id;
        $ruleName = $approvalRule->name;

        $approvalRule->delete();

        AuditLog::create([
            'business_id' => $business->id,
            'user_id' => Context::user()?->id,
            'auditable_type' => ApprovalRule::class,
            'auditable_id' => $ruleId,
            'action' => 'approval_rule.deleted',
            'notes' => "Penghapusan aturan: {$ruleName}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);

        return redirect()->route('approval-rules.index')->with('success', 'Aturan otorisasi dokumen berhasil dihapus.');
    }
}
