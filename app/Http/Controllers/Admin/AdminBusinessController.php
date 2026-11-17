<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Billing\EntitlementService;
use App\Domain\Business\Services\BusinessPurgeService;
use App\Domain\OperatingMode\OperatingModeResolver;
use App\Domain\Template\BusinessTemplateService;
use App\Domain\Template\ModuleRegistry;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AuditLog;
use App\Mail\BusinessReactivatedMail;
use App\Mail\BusinessSuspendedMail;
use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\BusinessSubscription;
use App\Models\BusinessTypeTemplate;
use App\Models\SubscriptionPayment;
use App\Models\User;
use App\Services\Google2FAService;
use App\Support\Context;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
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
            new Middleware('require.admin.permission:admin.businesses.create,admin.businesses.view', only: ['store']),
            new Middleware('require.admin.permission:admin.businesses.grant_subscription,admin.businesses.view', only: ['grantSubscription']),
            new Middleware('require.admin.permission:admin.businesses.toggle_status', only: ['toggleStatus', 'bulkStatus']),
            new Middleware('require.admin.permission:admin.businesses.delete', only: ['destroy', 'bulkDestroy']),
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

        $perPage = \App\Support\PaginationHelper::perPage($request, 25);
        $businesses = $query->paginate($perPage)->withQueryString();

        $totalBusinesses = Business::count();
        $activeBusinesses = Business::where('is_active', true)->count();
        $suspendedBusinesses = Business::where('is_active', false)->count();
        $coreBusinesses = BusinessSubscription::whereIn('plan_code', [BusinessSubscription::PLAN_CORE_MONTHLY, BusinessSubscription::PLAN_CORE_ANNUAL])
            ->where('status', BusinessSubscription::STATUS_ACTIVE)
            ->distinct('business_id')
            ->count('business_id');

        $templates = BusinessTypeTemplate::orderBy('name')->get(['code', 'name', 'industry_category']);
        $existingUsers = User::orderBy('name')->take(200)->get(['id', 'name', 'email', 'phone']);

        return view('admin.businesses.index', compact(
            'businesses',
            'totalBusinesses',
            'activeBusinesses',
            'suspendedBusinesses',
            'coreBusinesses',
            'templates',
            'existingUsers'
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
     * Store a newly created business with custom subscription plan and duration without payment (2FA verified).
     */
    public function store(
        Request $request,
        BusinessTemplateService $templateService,
        Google2FAService $google2FAService
    ): RedirectResponse {
        /** @var Admin|null $admin */
        $admin = Auth::guard('admin')->user();

        if (! $admin || ! $admin->hasTwoFactorEnabled()) {
            return back()->withInput()->with(
                'error',
                'Penambahan bisnis ditolak: Akun administrator Anda belum mengaktifkan Google Authenticator (2FA). Silakan aktifkan 2FA di profil Anda terlebih dahulu.'
            );
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:businesses,name'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:businesses,slug'],
            'currency' => ['nullable', 'string', 'max:3'],
            'business_scale' => ['nullable', 'string', 'in:umkm,corporate'],
            'template_code' => ['nullable', 'string', 'exists:business_type_templates,code'],
            'owner_mode' => ['required', 'string', 'in:new_user,existing_user'],
            'existing_user_id' => ['required_if:owner_mode,existing_user', 'nullable', 'string', 'exists:users,id'],
            'owner_name' => ['required_if:owner_mode,new_user', 'nullable', 'string', 'max:255'],
            'owner_email' => ['required_if:owner_mode,new_user', 'nullable', 'email', 'max:255', 'unique:users,email'],
            'owner_phone' => ['nullable', 'string', 'max:30'],
            'owner_password' => ['nullable', 'string', 'min:6', 'max:100'],
            'plan_code' => ['required', 'string', 'max:60'],
            'custom_plan_code' => ['nullable', 'string', 'max:60'],
            'duration_type' => ['required', 'string', 'in:30,90,180,365,730,custom,lifetime'],
            'custom_days' => ['required_if:duration_type,custom', 'nullable', 'integer', 'min:1', 'max:36500'],
            'admin_notes' => ['nullable', 'string', 'max:1000'],
            'totp_code' => ['required', 'string'],
        ], [
            'name.required' => 'Nama bisnis wajib diisi.',
            'name.unique' => 'Nama bisnis sudah terdaftar.',
            'slug.unique' => 'Slug bisnis sudah digunakan.',
            'owner_name.required_if' => 'Nama pemilik akun baru wajib diisi.',
            'owner_email.required_if' => 'Email pemilik akun baru wajib diisi.',
            'owner_email.unique' => 'Email pemilik sudah digunakan oleh akun lain.',
            'existing_user_id.required_if' => 'Pilih pengguna terdaftar sebagai pemilik bisnis.',
            'plan_code.required' => 'Pilih paket langganan bisnis.',
            'duration_type.required' => 'Pilih durasi paket langganan.',
            'custom_days.required_if' => 'Jumlah hari kustom wajib diisi.',
            'totp_code.required' => 'Kode 6 digit Google Authenticator wajib diisi.',
        ]);

        if (! $google2FAService->verifyCode((string) $admin->two_factor_secret, (string) $validated['totp_code'])) {
            return back()->withInput()->with(
                'error',
                'Kode verifikasi Google Authenticator tidak valid atau telah kedaluwarsa. Operasi dibatalkan demi keamanan data platform.'
            );
        }

        $planCode = ($validated['plan_code'] === 'custom' && ! empty($validated['custom_plan_code']))
            ? trim((string) $validated['custom_plan_code'])
            : $validated['plan_code'];

        $durationType = $validated['duration_type'];
        $durationDays = 0;
        $endsAt = null;

        if ($durationType === 'lifetime') {
            $endsAt = null;
            $durationDays = 0;
        } elseif ($durationType === 'custom') {
            $durationDays = max(1, (int) $validated['custom_days']);
            $endsAt = now()->addDays($durationDays);
        } else {
            $durationDays = max(1, (int) $durationType);
            $endsAt = now()->addDays($durationDays);
        }

        $createdData = DB::transaction(function () use ($validated, $admin, $planCode, $endsAt, $durationDays, $templateService) {
            // 1. Resolve or Create Owner User
            $owner = null;
            $generatedPassword = null;

            if ($validated['owner_mode'] === 'existing_user') {
                $owner = User::findOrFail($validated['existing_user_id']);
            } else {
                $rawPassword = ! empty($validated['owner_password'])
                    ? (string) $validated['owner_password']
                    : 'Cooca@' . random_int(100000, 999999);

                $generatedPassword = empty($validated['owner_password']) ? $rawPassword : null;

                $owner = User::create([
                    'name' => trim((string) $validated['owner_name']),
                    'email' => strtolower(trim((string) $validated['owner_email'])),
                    'phone' => ! empty($validated['owner_phone']) ? trim((string) $validated['owner_phone']) : null,
                    'password' => Hash::make($rawPassword),
                    'email_verified_at' => now(),
                    'phone_verified_at' => ! empty($validated['owner_phone']) ? now() : null,
                    'onboarding_completed' => true,
                    'onboarding_completed_at' => now(),
                ]);
            }

            // 2. Resolve Template & disabled modules
            $templateCode = $validated['template_code'] ?? null;
            $tmpl = ! empty($templateCode) ? BusinessTypeTemplate::where('code', $templateCode)->first() : null;
            $disabledModules = $tmpl ? ModuleRegistry::getDisabledModulesForTemplate($tmpl->code) : [];

            $businessScale = ($validated['business_scale'] ?? Business::SCALE_UMKM) === Business::SCALE_CORPORATE
                ? Business::SCALE_CORPORATE
                : Business::SCALE_UMKM;

            if ($businessScale === Business::SCALE_UMKM) {
                $corporateModules = [
                    ModuleRegistry::MODULE_B2B_SALES,
                    ModuleRegistry::MODULE_LABOR_MACHINES,
                    ModuleRegistry::MODULE_INVENTORY_WAREHOUSE,
                    ModuleRegistry::MODULE_CUSTOMER_PO,
                    ModuleRegistry::MODULE_ACCOUNTING_CORPORATE,
                ];
                $disabledModules = array_values(array_unique(array_merge($disabledModules, $corporateModules)));
            }

            // 3. Create Business
            $customSlug = ! empty($validated['slug']) ? Str::slug($validated['slug']) : null;

            $business = Business::create([
                'name' => trim((string) $validated['name']),
                'slug' => $customSlug,
                'email' => $owner->email,
                'phone' => $owner->phone,
                'currency' => $validated['currency'] ?? 'IDR',
                'currency_precision' => 0,
                'rounding_strategy' => Business::ROUNDING_ROUND_100,
                'industry_category' => $tmpl?->industry_category,
                'template_code' => $tmpl?->code,
                'business_scale' => $businessScale,
                'disabled_modules' => $disabledModules,
                'is_active' => true,
            ]);

            // 4. Attach Owner to Business
            $business->users()->attach($owner->id, [
                'id' => (string) Str::uuid(),
                'role' => 'owner',
            ]);

            if (empty($owner->active_business_id)) {
                $owner->update(['active_business_id' => $business->id]);
            }

            // 5. Apply Template if selected
            if ($tmpl) {
                try {
                    $templateService->apply($business, $tmpl, seedProducts: false);
                } catch (\Throwable $e) {
                    Log::warning("Failed to apply template {$tmpl->code} to business {$business->id}: " . $e->getMessage());
                }
            }

            // 6. Create BusinessSubscription
            BusinessSubscription::create([
                'business_id' => $business->id,
                'plan_code' => $planCode,
                'price' => 0, // Tanpa Bayar
                'status' => BusinessSubscription::STATUS_ACTIVE,
                'starts_at' => now(),
                'ends_at' => $endsAt,
                'ai_tokens_monthly_allowance' => 0,
                'ai_tokens_remaining' => 0,
                'last_token_reset_at' => Carbon::today()->toDateString(),
            ]);

            // 7. Create Complimentary SubscriptionPayment Record
            $adminNotes = ! empty($validated['admin_notes']) ? trim((string) $validated['admin_notes']) : null;
            $durationLabel = $durationDays > 0 ? "{$durationDays} Hari" : "Lifetime (Tanpa Batas)";

            SubscriptionPayment::create([
                'business_id' => $business->id,
                'user_id' => $owner->id,
                'payment_type' => 'subscription',
                'package_name' => "Admin Grant: " . strtoupper($planCode),
                'package_duration_days' => $durationDays,
                'order_number' => 'ADM-SUB-' . strtoupper(Str::random(8)),
                'plan_code' => $planCode,
                'cycle' => ($durationDays >= 365) ? 'annual' : 'monthly',
                'amount' => 0,
                'subtotal' => 0,
                'total_payable' => 0,
                'payment_method' => 'admin_grant',
                'payment_gateway' => 'manual',
                'status' => SubscriptionPayment::STATUS_APPROVED,
                'approved_by' => $admin->id,
                'approved_at' => now(),
                'admin_notes' => $adminNotes ?: "Bisnis dan paket dibuat langsung oleh Administrator {$admin->name} tanpa biaya (durasi: {$durationLabel}).",
            ]);

            // 8. Log Audit
            try {
                AuditLog::create([
                    'business_id' => $business->id,
                    'user_id' => $owner->id,
                    'auditable_type' => Business::class,
                    'auditable_id' => $business->id,
                    'action' => 'admin_business_create_with_plan',
                    'risk_level' => AuditLog::RISK_MEDIUM,
                    'risk_reason' => 'Admin business creation with complimentary subscription',
                    'notes' => "Administrator {$admin->name} ({$admin->email}) menambahkan bisnis \"{$business->name}\" dengan paket {$planCode} durasi {$durationLabel} tanpa biaya (2FA verified).",
                    'new_values' => [
                        'admin_id' => $admin->id,
                        'business_id' => $business->id,
                        'owner_id' => $owner->id,
                        'owner_email' => $owner->email,
                        'plan_code' => $planCode,
                        'duration' => $durationLabel,
                        'ends_at' => $endsAt?->toIso8601String(),
                    ],
                    'ip_address' => request()->ip(),
                    'user_agent' => substr((string) request()->userAgent(), 0, 255),
                    'created_at' => now(),
                ]);
            } catch (\Throwable $e) {
                Log::warning('AuditLog creation error on admin create business: ' . $e->getMessage());
            }

            return [
                'business' => $business,
                'owner' => $owner,
                'password' => $generatedPassword,
                'duration_label' => $durationLabel,
            ];
        });

        $successMsg = "Bisnis \"{$createdData['business']->name}\" berhasil ditambahkan dengan paket {$planCode} ({$createdData['duration_label']}) tanpa biaya.";
        if ($createdData['password']) {
            $successMsg .= " Akun pemilik baru: {$createdData['owner']->email} (Password: {$createdData['password']}).";
        }

        return redirect()->route('admin.businesses.show', $createdData['business']->id)
            ->with('success', $successMsg);
    }

    /**
     * Grant or update a business subscription with any package and duration without payment (2FA verified).
     */
    public function grantSubscription(
        Request $request,
        Business $business,
        Google2FAService $google2FAService,
        EntitlementService $entitlementService
    ): RedirectResponse {
        /** @var Admin|null $admin */
        $admin = Auth::guard('admin')->user();

        if (! $admin || ! $admin->hasTwoFactorEnabled()) {
            return back()->with(
                'error',
                'Pemberian paket langganan ditolak: Akun administrator Anda belum mengaktifkan Google Authenticator (2FA). Silakan aktifkan 2FA di profil Anda terlebih dahulu.'
            );
        }

        $validated = $request->validate([
            'plan_code' => ['required', 'string', 'max:60'],
            'custom_plan_code' => ['nullable', 'string', 'max:60'],
            'duration_type' => ['required', 'string', 'in:30,90,180,365,730,custom,lifetime'],
            'custom_days' => ['required_if:duration_type,custom', 'nullable', 'integer', 'min:1', 'max:36500'],
            'calculation_mode' => ['required', 'string', 'in:extend,from_now'],
            'admin_notes' => ['nullable', 'string', 'max:1000'],
            'totp_code' => ['required', 'string'],
        ], [
            'plan_code.required' => 'Pilih paket langganan.',
            'duration_type.required' => 'Pilih durasi paket langganan.',
            'custom_days.required_if' => 'Jumlah hari kustom wajib diisi.',
            'calculation_mode.required' => 'Pilih metode perhitungan tanggal mulai.',
            'totp_code.required' => 'Kode 6 digit Google Authenticator wajib diisi.',
        ]);

        if (! $google2FAService->verifyCode((string) $admin->two_factor_secret, (string) $validated['totp_code'])) {
            return back()->with(
                'error',
                'Kode verifikasi Google Authenticator tidak valid atau telah kedaluwarsa. Operasi dibatalkan demi keamanan data platform.'
            );
        }

        $planCode = ($validated['plan_code'] === 'custom' && ! empty($validated['custom_plan_code']))
            ? trim((string) $validated['custom_plan_code'])
            : $validated['plan_code'];

        $durationType = $validated['duration_type'];
        $durationDays = 0;
        $newEndsAt = null;

        $subscription = $entitlementService->getSubscription($business);

        if ($durationType === 'lifetime') {
            $newEndsAt = null;
            $durationDays = 0;
        } else {
            $durationDays = $durationType === 'custom'
                ? max(1, (int) $validated['custom_days'])
                : max(1, (int) $durationType);

            $calculationMode = $validated['calculation_mode'];
            $baseDate = ($calculationMode === 'extend' && $subscription->isActive() && $subscription->ends_at && $subscription->ends_at->isFuture())
                ? $subscription->ends_at
                : now();

            $newEndsAt = $baseDate->copy()->addDays($durationDays);
        }

        $durationLabel = $durationDays > 0 ? "{$durationDays} Hari" : "Lifetime (Tanpa Batas)";
        $adminNotes = ! empty($validated['admin_notes']) ? trim((string) $validated['admin_notes']) : null;

        DB::transaction(function () use ($business, $subscription, $planCode, $newEndsAt, $durationDays, $durationLabel, $admin, $adminNotes, $entitlementService) {
            // 1. Update subscription
            $subscription->update([
                'plan_code' => $planCode,
                'price' => 0, // Tanpa Bayar
                'status' => BusinessSubscription::STATUS_ACTIVE,
                'starts_at' => now(),
                'ends_at' => $newEndsAt,
                'last_token_reset_at' => Carbon::today()->toDateString(),
            ]);

            // 2. Clear entitlement cache
            $entitlementService->clearUsageCache($business);

            // 3. Create Complimentary SubscriptionPayment
            $targetUser = $business->owner ?: $business->users()->first();

            SubscriptionPayment::create([
                'business_id' => $business->id,
                'user_id' => $targetUser?->id,
                'payment_type' => 'subscription',
                'package_name' => "Admin Grant: " . strtoupper($planCode),
                'package_duration_days' => $durationDays,
                'order_number' => 'ADM-GRNT-' . strtoupper(Str::random(8)),
                'plan_code' => $planCode,
                'cycle' => ($durationDays >= 365) ? 'annual' : 'monthly',
                'amount' => 0,
                'subtotal' => 0,
                'total_payable' => 0,
                'payment_method' => 'admin_grant',
                'payment_gateway' => 'manual',
                'status' => SubscriptionPayment::STATUS_APPROVED,
                'approved_by' => $admin->id,
                'approved_at' => now(),
                'admin_notes' => $adminNotes ?: "Paket langganan diberikan langsung oleh Administrator {$admin->name} tanpa biaya (durasi: {$durationLabel}).",
            ]);

            // 4. Log Audit
            try {
                AuditLog::create([
                    'business_id' => $business->id,
                    'user_id' => $targetUser?->id,
                    'auditable_type' => Business::class,
                    'auditable_id' => $business->id,
                    'action' => 'admin_grant_subscription',
                    'risk_level' => AuditLog::RISK_MEDIUM,
                    'risk_reason' => 'Admin manual complimentary subscription grant',
                    'notes' => "Administrator {$admin->name} ({$admin->email}) memberikan paket {$planCode} untuk bisnis \"{$business->name}\" durasi {$durationLabel} tanpa biaya (2FA verified).",
                    'new_values' => [
                        'admin_id' => $admin->id,
                        'business_id' => $business->id,
                        'plan_code' => $planCode,
                        'duration' => $durationLabel,
                        'ends_at' => $newEndsAt?->toIso8601String(),
                    ],
                    'ip_address' => request()->ip(),
                    'user_agent' => substr((string) request()->userAgent(), 0, 255),
                    'created_at' => now(),
                ]);
            } catch (\Throwable $e) {
                Log::warning('AuditLog creation error on grant subscription: ' . $e->getMessage());
            }
        });

        return redirect()->route('admin.businesses.show', $business->id)
            ->with('success', "Paket {$planCode} ({$durationLabel}) berhasil diberikan ke bisnis \"{$business->name}\" tanpa biaya.");
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

    /**
     * Completely and permanently hard delete a tenant business with 2FA verification.
     */
    public function destroy(
        Request $request,
        Business $business,
        BusinessPurgeService $purgeService,
        Google2FAService $google2FAService
    ): RedirectResponse {
        /** @var Admin|null $admin */
        $admin = Auth::guard('admin')->user();

        if (! $admin || ! $admin->hasTwoFactorEnabled()) {
            return back()->with(
                'error',
                'Penghapusan permanen ditolak: Akun administrator Anda belum mengaktifkan Google Authenticator (2FA). Silakan aktifkan 2FA di profil Anda terlebih dahulu.'
            );
        }

        $validated = $request->validate([
            'confirm_name' => ['required', 'string'],
            'totp_code' => ['required', 'string'],
        ], [
            'confirm_name.required' => 'Konfirmasi nama bisnis wajib diisi.',
            'totp_code.required' => 'Kode 6 digit Google Authenticator wajib diisi.',
        ]);

        $expectedName = trim((string) $business->name);
        $expectedSlug = trim((string) $business->slug);
        $enteredConfirm = trim((string) $validated['confirm_name']);

        if (strcasecmp($enteredConfirm, $expectedName) !== 0 && strcasecmp($enteredConfirm, $expectedSlug) !== 0) {
            return back()->with(
                'error',
                "Nama atau slug bisnis konfirmasi tidak sesuai. Ketik persis \"{$business->name}\" atau \"{$business->slug}\" untuk melanjutkan."
            );
        }

        if (! $google2FAService->verifyCode((string) $admin->two_factor_secret, (string) $validated['totp_code'])) {
            return back()->with(
                'error',
                'Kode verifikasi Google Authenticator tidak valid atau telah kedaluwarsa. Operasi dibatalkan demi keamanan data platform.'
            );
        }

        try {
            $purgeResult = $purgeService->purge($business, $admin);

            return redirect()->route('admin.businesses.index')->with(
                'success',
                "Tenant bisnis \"{$purgeResult['name']}\" beserta seluruh database relasi dan aset penyimpanan telah dihapus secara permanen dari sistem."
            );
        } catch (\Throwable $e) {
            Log::error('Failed to execute hard delete on business: ' . $e->getMessage(), [
                'business_id' => $business->id,
                'admin_id' => $admin->id,
            ]);

            return back()->with(
                'error',
                'Terjadi kesalahan saat memproses penghapusan permanen bisnis: ' . $e->getMessage()
            );
        }
    }

    /**
     * Completely and permanently hard delete multiple tenant businesses with 2FA verification.
     */
    public function bulkDestroy(
        Request $request,
        BusinessPurgeService $purgeService,
        Google2FAService $google2FAService
    ): RedirectResponse {
        /** @var Admin|null $admin */
        $admin = Auth::guard('admin')->user();

        if (! $admin || ! $admin->hasTwoFactorEnabled()) {
            return back()->with(
                'error',
                'Penghapusan masal ditolak: Akun administrator Anda belum mengaktifkan Google Authenticator (2FA). Silakan aktifkan 2FA di profil Anda terlebih dahulu.'
            );
        }

        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'string', 'exists:businesses,id'],
            'confirm_text' => ['required', 'string'],
            'totp_code' => ['required', 'string'],
        ], [
            'ids.required' => 'Pilih minimal satu bisnis untuk dihapus.',
            'ids.min' => 'Pilih minimal satu bisnis untuk dihapus.',
            'confirm_text.required' => 'Ketik teks konfirmasi "HAPUS MASAL" untuk melanjutkan.',
            'totp_code.required' => 'Kode 6 digit Google Authenticator wajib diisi.',
        ]);

        if (trim(strtoupper((string) $validated['confirm_text'])) !== 'HAPUS MASAL') {
            return back()->with(
                'error',
                'Teks konfirmasi salah. Anda wajib mengetik "HAPUS MASAL" dengan tepat untuk mengonfirmasi penghapusan permanen.'
            );
        }

        if (! $google2FAService->verifyCode((string) $admin->two_factor_secret, (string) $validated['totp_code'])) {
            return back()->with(
                'error',
                'Kode verifikasi Google Authenticator tidak valid atau telah kedaluwarsa. Operasi dibatalkan demi keamanan data platform.'
            );
        }

        $ids = $validated['ids'];
        $businesses = Business::whereIn('id', $ids)->get();

        $successCount = 0;
        $failedCount = 0;

        foreach ($businesses as $business) {
            try {
                $purgeService->purge($business, $admin);
                $successCount++;
            } catch (\Throwable $e) {
                $failedCount++;
                Log::error("Failed to purge business ID {$business->id} in bulk delete: " . $e->getMessage(), [
                    'business_id' => $business->id,
                    'admin_id' => $admin->id,
                ]);
            }
        }

        if ($failedCount > 0) {
            return redirect()->route('admin.businesses.index')->with(
                'warning',
                "Sebanyak {$successCount} bisnis berhasil dihapus secara permanen, namun terdapat {$failedCount} bisnis yang gagal diproses."
            );
        }

        return redirect()->route('admin.businesses.index')->with(
            'success',
            "Sebanyak {$successCount} bisnis terpilih beserta seluruh data dan asetnya telah dihapus secara permanen dari sistem."
        );
    }
}
