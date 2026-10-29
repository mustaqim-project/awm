<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\OperatingMode\OperatingModeResolver;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Mail\BusinessReactivatedMail;
use App\Mail\BusinessSuspendedMail;
use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\BusinessSubscription;
use App\Support\Context;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

final class AdminBusinessController extends Controller implements HasMiddleware
{
    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('require.admin.permission:admin.businesses.view', only: ['index', 'show', 'impersonate']),
            new Middleware('require.admin.permission:admin.businesses.toggle_status', only: ['toggleStatus', 'bulkStatus']),
        ];
    }
    /**
     * Display a listing of all tenant businesses.
     */
    public function index(Request $request): View
    {
        $query = Business::with(['subscription', 'users'])
            ->withCount(['products', 'bomHeaders'])
            ->latest();

        if ($request->filled('search')) {
            $search = (string) $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $status = (string) $request->get('status');
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'suspended') {
                $query->where('is_active', false);
            }
        }

        if ($request->filled('plan')) {
            $plan = (string) $request->get('plan');
            if ($plan === 'core') {
                $query->whereHas('subscription', function ($q) {
                    $q->whereIn('plan_code', [BusinessSubscription::PLAN_CORE_MONTHLY, BusinessSubscription::PLAN_CORE_ANNUAL])
                        ->where('status', BusinessSubscription::STATUS_ACTIVE);
                });
            } elseif ($plan === 'free') {
                $query->where(function ($q) {
                    $q->whereDoesntHave('subscription')
                        ->orWhereHas('subscription', function ($sq) {
                            $sq->where('plan_code', BusinessSubscription::PLAN_FREE)
                                ->orWhere('status', '!=', BusinessSubscription::STATUS_ACTIVE);
                        });
                });
            }
        }

        $businesses = $query->paginate(15)->withQueryString();

        $totalBusinesses = Business::count();
        $activeBusinesses = Business::where('is_active', true)->count();
        $suspendedBusinesses = Business::where('is_active', false)->count();
        $coreBusinesses = BusinessSubscription::whereIn('plan_code', [BusinessSubscription::PLAN_CORE_MONTHLY, BusinessSubscription::PLAN_CORE_ANNUAL])
            ->where('status', BusinessSubscription::STATUS_ACTIVE)
            ->distinct('business_id')
            ->count('business_id');

        return view('admin.businesses.index', compact(
            'businesses',
            'totalBusinesses',
            'activeBusinesses',
            'suspendedBusinesses',
            'coreBusinesses'
        ));
    }

    /**
     * Display detailed workspace view for a single business.
     */
    public function show(Business $business): View
    {
        $business->load([
            'subscription',
            'subscriptions' => fn ($q) => $q->latest(),
            'subscriptionPayments' => fn ($q) => $q->latest()->take(10),
            'users',
        ]);

        $isSoloMode = OperatingModeResolver::isSoloMode($business);

        $productCount = $business->products()->count();
        $bomCount = $business->bomHeaders()->count();
        $invoiceCount = $business->invoices()->count();
        $posOrderCount = $business->posOrders()->count();
        $totalAiTokensUsed = (int) $business->aiTokenUsages()->sum('total_tokens');

        return view('admin.businesses.show', compact(
            'business',
            'isSoloMode',
            'productCount',
            'bomCount',
            'invoiceCount',
            'posOrderCount',
            'totalAiTokensUsed'
        ));
    }

    /**
     * Toggle the active/suspended status of a business workspace.
     */
    public function toggleStatus(Request $request, Business $business): RedirectResponse
    {
        if ($business->is_active) {
            $validated = $request->validate([
                'reason' => ['required', 'string', 'min:5', 'max:1000'],
            ], [
                'reason.required' => 'Alasan penangguhan wajib diisi.',
                'reason.min' => 'Alasan penangguhan minimal 5 karakter.',
                'reason.max' => 'Alasan penangguhan maksimal 1000 karakter.',
            ]);

            $reason = trim($validated['reason']);

            $business->update([
                'is_active' => false,
                'suspended_reason' => $reason,
                'suspended_at' => now(),
            ]);

            $owner = $business->owner
                ?: $business->users()->wherePivot('role', 'owner')->first()
                ?: $business->users()->first();

            $ownerEmail = $owner?->email ?: $business->email;
            $ownerName = $owner?->name ?: ($business->name . ' Owner');

            if (! empty($ownerEmail)) {
                try {
                    Mail::to($ownerEmail)->send(new BusinessSuspendedMail($business, $reason, $ownerName));
                } catch (\Throwable $e) {
                    Log::error('Failed sending business suspension notification email: ' . $e->getMessage(), [
                        'business_id' => $business->id,
                        'email' => $ownerEmail,
                    ]);
                }
            }

            return redirect()->back()
                ->with('success', "Workspace bisnis {$business->name} berhasil ditangguhkan.");
        }

        $business->update([
            'is_active' => true,
            'suspended_reason' => null,
            'suspended_at' => null,
        ]);

        $owner = $business->owner
            ?: $business->users()->wherePivot('role', 'owner')->first()
            ?: $business->users()->first();

        $ownerEmail = $owner?->email ?: $business->email;
        $ownerName = $owner?->name ?: ($business->name . ' Owner');

        if (! empty($ownerEmail)) {
            try {
                Mail::to($ownerEmail)->send(new BusinessReactivatedMail($business, $ownerName));
            } catch (\Throwable $e) {
                Log::error('Failed sending business reactivation notification email: ' . $e->getMessage(), [
                    'business_id' => $business->id,
                    'email' => $ownerEmail,
                ]);
            }
        }

        return redirect()->back()
            ->with('success', "Workspace bisnis {$business->name} berhasil diaktifkan kembali.");
    }

    /**
     * Bulk suspend or activate tenant businesses.
     */
    public function bulkStatus(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'string', 'exists:businesses,id'],
            'action' => ['required', 'string', 'in:activate,suspend'],
            'reason' => ['nullable', 'string', 'min:5', 'max:1000'],
        ], [
            'ids.required' => 'Pilih minimal satu bisnis untuk diproses.',
            'ids.min' => 'Pilih minimal satu bisnis untuk diproses.',
            'action.in' => 'Aksi bulk status tidak valid.',
            'reason.min' => 'Alasan penangguhan minimal 5 karakter.',
            'reason.max' => 'Alasan penangguhan maksimal 1000 karakter.',
        ]);

        $ids = $validated['ids'];
        $action = $validated['action'];

        if ($action === 'suspend') {
            $reason = trim((string) ($validated['reason'] ?? 'Ditangguhkan secara masal oleh administrator.'));
            if (strlen($reason) < 5) {
                $reason = 'Ditangguhkan secara masal oleh administrator platform.';
            }

            $count = Business::whereIn('id', $ids)->update([
                'is_active' => false,
                'suspended_reason' => $reason,
                'suspended_at' => now(),
            ]);

            return redirect()->back()
                ->with('success', "{$count} workspace bisnis berhasil ditangguhkan.");
        }

        $count = Business::whereIn('id', $ids)->update([
            'is_active' => true,
            'suspended_reason' => null,
            'suspended_at' => null,
        ]);

        return redirect()->back()
            ->with('success', "{$count} workspace bisnis berhasil diaktifkan kembali.");
    }

    /**
     * Masuk ke workspace bisnis tenant (impersonasi) untuk investigasi bug dan diagnosa sistem.
     */
    public function impersonate(Request $request, Business $business): RedirectResponse
    {
        /** @var \App\Models\Admin|null $admin */
        $admin = Auth::guard('admin')->user();
        if (! $admin) {
            abort(403, 'Akses ditolak: Hanya administrator platform yang diizinkan.');
        }

        if (! $admin->isSuperAdmin() && ! $admin->hasPermission('admin.businesses.impersonate') && ! $admin->hasPermission('admin.businesses.view')) {
            abort(403, 'Anda tidak memiliki hak akses untuk masuk ke workspace tenant.');
        }

        // Cari user target: prioritas owner, fallback user pertama dalam tim
        $targetUser = $business->owner ?: $business->users()->first();

        if (! $targetUser) {
            return redirect()->back()
                ->with('error', "Bisnis '{$business->name}' belum memiliki pengguna terdaftar dalam tim.");
        }

        // Sinkronisasi active_business_id pada user target
        $targetUser->update(['active_business_id' => $business->id]);

        // Simpan data sesi impersonasi
        $request->session()->put([
            'admin_impersonator_id' => $admin->id,
            'admin_impersonator_name' => $admin->name,
            'admin_impersonator_email' => $admin->email,
            'admin_impersonated_business_id' => $business->id,
            'admin_impersonated_business_name' => $business->name,
            'active_business_id' => $business->id,
            'auth_wa_otp_verified_user_id' => $targetUser->id,
            'auth_wa_otp_verified_at' => now()->timestamp,
        ]);

        // Login ke tenant web guard sebagai user target
        Auth::guard('web')->login($targetUser);

        // Bind Context tenant secara langsung
        $membership = BusinessMembership::where('business_id', $business->id)
            ->where('user_id', $targetUser->id)
            ->first();
        if ($membership) {
            Context::setBusiness($business, $membership);
        }

        // Catat AuditLog investigasi
        try {
            AuditLog::create([
                'business_id' => $business->id,
                'user_id' => $targetUser->id,
                'auditable_type' => Business::class,
                'auditable_id' => $business->id,
                'action' => 'admin_impersonate_start',
                'risk_level' => AuditLog::RISK_MEDIUM,
                'risk_reason' => 'Admin diagnostic workspace impersonation',
                'notes' => "Administrator {$admin->name} ({$admin->email}) masuk ke workspace {$business->name} untuk diagnosa bug.",
                'new_values' => [
                    'admin_id' => $admin->id,
                    'admin_email' => $admin->email,
                    'target_user_id' => $targetUser->id,
                ],
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 255),
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Impersonate audit log error: ' . $e->getMessage());
        }

        return redirect()->route('dashboard')
            ->with('info', "Anda telah masuk ke workspace {$business->name} dalam Mode Investigasi Admin.");
    }

    /**
     * Mengakhiri sesi impersonasi dan kembali ke Admin Console.
     */
    public function leaveImpersonation(Request $request): RedirectResponse
    {
        $businessId = $request->session()->get('admin_impersonated_business_id');
        $targetUserId = Auth::guard('web')->id();
        $adminName = $request->session()->get('admin_impersonator_name', 'Administrator');

        if ($businessId && $targetUserId) {
            try {
                AuditLog::create([
                    'business_id' => $businessId,
                    'user_id' => $targetUserId,
                    'auditable_type' => Business::class,
                    'auditable_id' => $businessId,
                    'action' => 'admin_impersonate_end',
                    'risk_level' => AuditLog::RISK_LOW,
                    'risk_reason' => 'Admin diagnostic session concluded',
                    'notes' => "Administrator {$adminName} mengakhiri sesi investigasi bugs di workspace.",
                    'ip_address' => $request->ip(),
                    'user_agent' => substr((string) $request->userAgent(), 0, 255),
                    'created_at' => now(),
                ]);
            } catch (\Throwable $e) {
                Log::warning('Leave impersonation audit log error: ' . $e->getMessage());
            }
        }

        // Logout dari tenant web guard
        Auth::guard('web')->logout();

        // Hapus variabel sesi impersonasi
        $request->session()->forget([
            'admin_impersonator_id',
            'admin_impersonator_name',
            'admin_impersonator_email',
            'admin_impersonated_business_id',
            'admin_impersonated_business_name',
            'active_business_id',
            'auth_wa_otp_verified_user_id',
            'auth_wa_otp_verified_at',
        ]);

        if ($businessId && Business::where('id', $businessId)->exists()) {
            return redirect()->route('admin.businesses.show', $businessId)
                ->with('success', 'Sesi investigasi bugs telah diakhiri. Anda kembali ke Admin Console.');
        }

        return redirect()->route('admin.businesses.index')
            ->with('success', 'Sesi investigasi bugs telah diakhiri. Anda kembali ke Admin Console.');
    }
}
