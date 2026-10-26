<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Billing\EarlyAdopterPricingService;
use App\Domain\Billing\EntitlementService;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\BusinessSubscription;
use App\Models\SubscriptionPriceHistory;
use App\Models\SubscriptionPricingBatch;
use App\Models\SubscriptionPricingProgram;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class AdminPricingProgramController extends Controller implements HasMiddleware
{
    /**
     * Get the controller-level RBAC middleware for Early Adopter Management.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('require.admin.permission:admin.pricing_programs.view', only: ['index', 'simulateEscalation']),
            new Middleware('require.admin.permission:admin.pricing_programs.manage', only: [
                'store', 'update', 'toggle',
                'storeBatch', 'updateBatch', 'toggleBatchStatus', 'linkNextBatch',
                'runEscalation',
            ]),
        ];
    }

    public function __construct(
        private readonly EarlyAdopterPricingService $pricingService = new EarlyAdopterPricingService(),
        private readonly EntitlementService $entitlementService = new EntitlementService()
    ) {}

    /**
     * Display the Early Adopter Program management hub with 4 Bento tabs:
     * 1. Programs & Batches
     * 2. Grandfathered Customers
     * 3. Escalation Schedule & Simulator
     * 4. Price History & Audit Log
     */
    public function index(Request $request): View
    {
        $tab = $request->get('tab', 'programs');
        $search = $request->get('search');
        $selectedBatchId = $request->get('batch_id');

        // Cockpit Metrics
        $totalPrograms = SubscriptionPricingProgram::count();
        $activePrograms = SubscriptionPricingProgram::where('is_active', true)->count();
        $activeBatches = SubscriptionPricingBatch::where('status', SubscriptionPricingBatch::STATUS_ACTIVE)->count();
        $totalEarlyAdopterCustomers = BusinessSubscription::where('is_price_lock_eligible', true)
            ->whereNotNull('pricing_batch_id')
            ->count();

        // 1. Programs & Batches Tab
        $programs = SubscriptionPricingProgram::with(['batches' => function ($q): void {
            $q->orderBy('batch_number');
        }])->latest()->get();

        $allBatches = SubscriptionPricingBatch::orderBy('batch_number')->get();

        // 2. Customers per Batch Tab
        $customersQuery = BusinessSubscription::with(['business', 'batch.program', 'batch'])
            ->whereNotNull('pricing_batch_id')
            ->latest();

        if ($selectedBatchId) {
            $customersQuery->where('pricing_batch_id', $selectedBatchId);
        }

        if (! empty($search) && $tab === 'customers') {
            $customersQuery->whereHas('business', function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $customers = $customersQuery->paginate(15, ['*'], 'customers_page')->withQueryString();

        // 3. Escalation Schedule Tab
        $today = Carbon::today();
        $upcomingEscalations = BusinessSubscription::with(['business', 'batch'])
            ->where('is_price_lock_eligible', true)
            ->whereNotNull('pricing_batch_id')
            ->where('annual_increase_percentage', '>', 0)
            ->whereNotNull('next_price_increase_at')
            ->where('next_price_increase_at', '>=', $today->toDateString())
            ->where('next_price_increase_at', '<=', $today->copy()->addDays(90)->toDateString())
            ->orderBy('next_price_increase_at')
            ->get();

        $dueEscalationsCount = BusinessSubscription::where('is_price_lock_eligible', true)
            ->whereNotNull('pricing_batch_id')
            ->where('annual_increase_percentage', '>', 0)
            ->whereNotNull('next_price_increase_at')
            ->where('next_price_increase_at', '<=', $today->toDateString())
            ->count();

        // 4. Audit Log & History Tab
        $historyQuery = SubscriptionPriceHistory::with(['business', 'batch'])->latest();
        if (! empty($search) && $tab === 'logs') {
            $historyQuery->whereHas('business', function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%");
            });
        }
        $priceHistories = $historyQuery->paginate(20, ['*'], 'history_page')->withQueryString();

        // Base prices for reference
        $basePrices = [
            'standard_monthly' => $this->pricingService->getBasePrice('standard', 'monthly'),
            'standard_annual' => $this->pricingService->getBasePrice('standard', 'annual'),
            'premium_monthly' => $this->pricingService->getBasePrice('premium', 'monthly'),
            'premium_annual' => $this->pricingService->getBasePrice('premium', 'annual'),
            'prestige_monthly' => $this->pricingService->getBasePrice('prestige', 'monthly'),
            'prestige_annual' => $this->pricingService->getBasePrice('prestige', 'annual'),
        ];

        return view('admin.pricing-programs.index', compact(
            'tab',
            'totalPrograms',
            'activePrograms',
            'activeBatches',
            'totalEarlyAdopterCustomers',
            'programs',
            'allBatches',
            'customers',
            'selectedBatchId',
            'upcomingEscalations',
            'dueEscalationsCount',
            'priceHistories',
            'basePrices'
        ));
    }

    /**
     * Create a new pricing program container.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'code' => ['nullable', 'string', 'max:64', 'alpha_dash', 'unique:subscription_pricing_programs,code'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $code = ! empty($validated['code'])
            ? Str::slug($validated['code'])
            : Str::slug($validated['name']) . '-' . date('Y');

        $baseCode = $code;
        $counter = 2;
        while (SubscriptionPricingProgram::where('code', $code)->exists()) {
            $code = $baseCode . '-' . $counter++;
        }

        $program = SubscriptionPricingProgram::create([
            'name' => $validated['name'],
            'code' => $code,
            'description' => $validated['description'] ?? null,
            'is_active' => (bool) ($request->input('is_active', true)),
        ]);

        AuditLog::create([
            'business_id' => null,
            'user_id' => auth('admin')->id(),
            'action' => 'billing.pricing_program.created',
            'auditable_type' => SubscriptionPricingProgram::class,
            'auditable_id' => $program->id,
            'risk_level' => AuditLog::RISK_LOW,
            'notes' => "Program Early Adopter baru dibuat: {$program->name} ({$program->code})",
        ]);

        return redirect()->route('admin.pricing-programs.index', ['tab' => 'programs'])
            ->with('success', "Program Early Adopter '{$program->name}' berhasil dibuat.");
    }

    /**
     * Update an existing pricing program.
     */
    public function update(Request $request, SubscriptionPricingProgram $program): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $program->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_active' => (bool) ($request->input('is_active', false)),
        ]);

        return redirect()->route('admin.pricing-programs.index', ['tab' => 'programs'])
            ->with('success', "Program '{$program->name}' berhasil diperbarui.");
    }

    /**
     * Toggle program active status.
     */
    public function toggle(SubscriptionPricingProgram $program): RedirectResponse
    {
        $program->update(['is_active' => ! $program->is_active]);

        return back()->with('success', $program->is_active ? 'Program diaktifkan.' : 'Program dinonaktifkan.');
    }

    /**
     * Create a new pricing batch within a program with strict max 10% annual escalation validation.
     */
    public function storeBatch(Request $request, SubscriptionPricingProgram $program): RedirectResponse
    {
        $validated = $this->validateBatchRequest($request);

        $nextBatchNumber = ((int) $program->batches()->max('batch_number')) + 1;

        $batch = $program->batches()->create(array_merge($validated, [
            'batch_number' => $nextBatchNumber,
            'quota_used' => 0,
        ]));

        AuditLog::create([
            'business_id' => null,
            'user_id' => auth('admin')->id(),
            'action' => 'billing.pricing_batch.created',
            'auditable_type' => SubscriptionPricingBatch::class,
            'auditable_id' => $batch->id,
            'risk_level' => AuditLog::RISK_MEDIUM,
            'notes' => "Batch #{$batch->batch_number} ({$batch->name}) dibuat pada program {$program->name}. Kuota: {$batch->quota_limit}, Kenaikan Tahunan: {$batch->annual_increase_percentage}%",
        ]);

        return redirect()->route('admin.pricing-programs.index', ['tab' => 'programs'])
            ->with('success', "Batch #{$batch->batch_number} ({$batch->name}) berhasil dibuat.");
    }

    /**
     * Update an existing pricing batch with strict max 10% annual escalation validation.
     */
    public function updateBatch(Request $request, SubscriptionPricingBatch $batch): RedirectResponse
    {
        $validated = $this->validateBatchRequest($request);

        $batch->update($validated);

        AuditLog::create([
            'business_id' => null,
            'user_id' => auth('admin')->id(),
            'action' => 'billing.pricing_batch.updated',
            'auditable_type' => SubscriptionPricingBatch::class,
            'auditable_id' => $batch->id,
            'risk_level' => AuditLog::RISK_MEDIUM,
            'notes' => "Batch #{$batch->batch_number} ({$batch->name}) diperbarui oleh admin.",
        ]);

        return redirect()->route('admin.pricing-programs.index', ['tab' => 'programs'])
            ->with('success', "Batch #{$batch->batch_number} berhasil diperbarui.");
    }

    /**
     * Change batch status (draft, active, completed, cancelled).
     */
    public function toggleBatchStatus(Request $request, SubscriptionPricingBatch $batch): RedirectResponse
    {
        $status = $request->input('status');
        abort_unless(in_array($status, SubscriptionPricingBatch::STATUSES, true), 422, 'Status batch tidak valid.');

        $batch->update([
            'status' => $status,
            'starts_at' => $status === SubscriptionPricingBatch::STATUS_ACTIVE && empty($batch->starts_at)
                ? Carbon::now()
                : $batch->starts_at,
        ]);

        return back()->with('success', "Status Batch #{$batch->batch_number} diubah menjadi {$status}.");
    }

    /**
     * Link next batch sequence.
     */
    public function linkNextBatch(Request $request, SubscriptionPricingBatch $batch): RedirectResponse
    {
        $nextBatchId = $request->input('next_batch_id');
        if ($nextBatchId === 'none' || empty($nextBatchId)) {
            $nextBatchId = null;
        }

        if ($nextBatchId === $batch->id) {
            return back()->with('error', 'Batch tidak dapat menunjuk ke dirinya sendiri sebagai batch berikutnya.');
        }

        $batch->update(['next_batch_id' => $nextBatchId]);

        return back()->with('success', "Urutan batch berikutnya untuk #{$batch->batch_number} berhasil disimpan.");
    }

    /**
     * Preview / Simulate upcoming annual price escalations without mutating the database.
     */
    public function simulateEscalation(): View
    {
        $preview = $this->pricingService->processAnnualPriceEscalations(dryRun: true);

        return view('admin.pricing-programs.simulate', [
            'count' => $preview['processed_count'],
            'subscriptions' => $preview['updated_subscriptions'],
        ]);
    }

    /**
     * Execute manual annual price escalation runner for all due subscriptions.
     */
    public function runEscalation(): RedirectResponse
    {
        $result = $this->pricingService->processAnnualPriceEscalations(dryRun: false);
        $count = $result['processed_count'];

        if ($count === 0) {
            return back()->with('info', 'Tidak ada langganan yang jatuh tempo eskalasi harga tahunan hari ini.');
        }

        return redirect()->route('admin.pricing-programs.index', ['tab' => 'schedule'])
            ->with('success', "Berhasil menjalankan eskalasi harga tahunan untuk {$count} pelanggan Early Adopter.");
    }

    /**
     * Validate batch form request, enforcing strict max 10.00% annual increase policy.
     *
     * @return array<string, mixed>
     */
    private function validateBatchRequest(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'status' => ['required', 'string', 'in:' . implode(',', SubscriptionPricingBatch::STATUSES)],
            'trigger_mode' => ['required', 'string', 'in:' . implode(',', SubscriptionPricingBatch::MODES)],
            'quota_limit' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'next_batch_id' => ['nullable', 'uuid', 'exists:subscription_pricing_batches,id'],
            'fallback_behavior' => ['required', 'string', 'in:base_pricing,next_batch,stay_active'],

            // Special Pricing per Tier
            'standard_monthly_price' => ['nullable', 'numeric', 'min:0'],
            'standard_annual_price' => ['nullable', 'numeric', 'min:0'],
            'premium_monthly_price' => ['nullable', 'numeric', 'min:0'],
            'premium_annual_price' => ['nullable', 'numeric', 'min:0'],
            'prestige_monthly_price' => ['nullable', 'numeric', 'min:0'],
            'prestige_annual_price' => ['nullable', 'numeric', 'min:0'],

            // Annual Price Escalation Policy (Strict validation: Max 10.00%)
            'annual_increase_percentage' => [
                'required',
                'numeric',
                'min:0',
                'max:' . EarlyAdopterPricingService::MAX_ANNUAL_INCREASE_PERCENTAGE,
            ],
            'annual_increase_basis' => ['required', 'string', 'in:subscription_anniversary,calendar_anniversary,fixed_date'],
            'annual_increase_effective_month' => ['nullable', 'integer', 'between:1,12'],
            'annual_increase_effective_day' => ['nullable', 'integer', 'between:1,31'],
            'rounding_mode' => ['required', 'string', 'in:none,nearest_1000,nearest_100,ceiling_1000'],

            // Resubscribe / Cancellation Policy
            'resubscribe_policy' => ['required', 'string', 'in:grace_period,immediate_forfeit,always_retain'],
            'resubscribe_grace_days' => ['required', 'integer', 'min:0', 'max:365'],
            'upgrade_downgrade_policy' => ['required', 'string', 'in:inherit_active_batch,retain_grandfathered_discount,forfeit_to_normal'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'annual_increase_percentage.max' => 'Sistem menolak persentase kenaikan tahunan di atas 10%. Maksimal yang diperbolehkan adalah 10.00%.',
            'annual_increase_percentage.min' => 'Persentase kenaikan tahunan minimal adalah 0%.',
        ]);
    }
}
