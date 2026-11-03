<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Ai\Studio\Billing\CostQuoteService;
use App\Domain\Ai\Studio\Billing\CreditLedgerService;
use App\Domain\Currency\ExchangeRateSyncService;
use App\Http\Controllers\Controller;
use App\Models\AiStudio\AiCreditTransaction;
use App\Models\AiStudio\AiModelPricing;
use App\Models\AiStudio\AiModelRegistry;
use App\Models\Business;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class AdminAiStudioPricingController extends Controller implements HasMiddleware
{
    public function __construct(
        private readonly CreditLedgerService $creditLedgerService = new CreditLedgerService,
        private readonly CostQuoteService $costQuoteService = new CostQuoteService,
        private readonly ExchangeRateSyncService $exchangeRateService = new ExchangeRateSyncService,
    ) {}

    /**
     * Daftarkan middleware RBAC Superadmin.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('require.admin.permission:admin.ai_studio.pricing', only: [
                'index',
                'grantCredits',
                'updatePricing',
                'syncExchangeRate',
                'toggleModel',
                'updateModelRates',
            ]),
        ];
    }

    /**
     * Dashboard Penyesuaian Harga & Ledger Kredit AI Studio Platform.
     */
    public function index(Request $request): View|JsonResponse
    {
        $pricingMatrix = CostQuoteService::MODEL_PRICING;

        $models = AiModelRegistry::with('activePricing')
            ->orderBy('provider')
            ->orderBy('is_default', 'desc')
            ->get();

        $usdToIdrRate = $this->exchangeRateService->getUsdToIdrRate();
        $exchangeRateLastSynced = Setting::get('ai_exchange_rate_last_synced_at') ?? now()->toIso8601String();

        $recentTransactions = AiCreditTransaction::query()
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $businesses = Business::query()
            ->where('is_active', true)
            ->select(['id', 'name', 'slug', 'ai_credits_balance'])
            ->orderByDesc('ai_credits_balance')
            ->limit(50)
            ->get();

        $stats = [
            'total_credits_circulating' => (int) Business::sum('ai_credits_balance'),
            'total_debits' => (int) abs(AiCreditTransaction::where('type', 'usage')->sum('amount')),
            'total_topups' => (int) AiCreditTransaction::where('type', 'topup')->sum('amount'),
            'total_refunds' => (int) AiCreditTransaction::where('type', 'refund')->sum('amount'),
        ];

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'pricing_matrix' => $pricingMatrix,
                'models' => $models,
                'usd_to_idr_rate' => $usdToIdrRate,
                'exchange_rate_last_synced' => $exchangeRateLastSynced,
                'stats' => $stats,
                'businesses' => $businesses,
                'recent_transactions' => $recentTransactions,
            ]);
        }

        return view('admin.ai-studio.pricing', compact(
            'pricingMatrix',
            'models',
            'usdToIdrRate',
            'exchangeRateLastSynced',
            'recentTransactions',
            'businesses',
            'stats'
        ));
    }

    /**
     * Hibah Kredit AI Studio ke Merchant oleh Superadmin.
     */
    public function grantCredits(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'business_id' => ['required', 'uuid', 'exists:businesses,id'],
            'credits' => ['required', 'integer', 'min:1', 'max:500000'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        /** @var Business $business */
        $business = Business::findOrFail($validated['business_id']);

        $idempotencyKey = 'admin_grant_' . Str::uuid();
        $adminId = (string) auth('admin')->id();

        $tx = $this->creditLedgerService->topup(
            business: $business,
            credits: (int) $validated['credits'],
            idempotencyKey: $idempotencyKey,
            userId: null,
            description: $validated['description'] ?? "Hibah {$validated['credits']} kredit AI Studio oleh Superadmin",
            metadata: [
                'admin_id' => $adminId,
                'granted_at' => now()->toIso8601String(),
            ]
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Berhasil menambahkan {$validated['credits']} kredit ke bisnis {$business->name}.",
                'new_balance' => $tx->balance_after,
                'transaction_id' => $tx->id,
            ]);
        }

        return redirect()->route('admin.ai-studio.pricing.index')
            ->with('success', "Berhasil menambahkan {$validated['credits']} kredit ke bisnis {$business->name}.");
    }

    /**
     * Sinkronisasi Real-Time Kurs USD/IDR dari API Eksternal.
     */
    public function syncExchangeRate(Request $request): RedirectResponse|JsonResponse
    {
        $newRate = $this->exchangeRateService->syncLatestRate();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Kurs USD/IDR berhasil disinkronkan: Rp " . number_format($newRate, 0, ',', '.'),
                'rate' => $newRate,
            ]);
        }

        return redirect()->route('admin.ai-studio.pricing.index')
            ->with('success', "Kurs USD/IDR berhasil diperbarui: Rp " . number_format($newRate, 0, ',', '.'));
    }

    /**
     * Toggle status aktif/non-aktif model AI di registry.
     */
    public function toggleModel(string $modelId, Request $request): RedirectResponse|JsonResponse
    {
        /** @var AiModelRegistry $model */
        $model = AiModelRegistry::findOrFail($modelId);
        $model->is_active = ! $model->is_active;
        $model->save();

        $statusStr = $model->is_active ? 'diaktifkan' : 'dinonaktifkan';

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Model {$model->model_name} berhasil {$statusStr}.",
                'is_active' => $model->is_active,
            ]);
        }

        return redirect()->route('admin.ai-studio.pricing.index')
            ->with('success', "Model {$model->model_name} berhasil {$statusStr}.");
    }

    /**
     * Perbarui tarif token per juta untuk model AI tertentu.
     */
    public function updateModelRates(string $modelId, Request $request): RedirectResponse|JsonResponse
    {
        /** @var AiModelRegistry $model */
        $model = AiModelRegistry::with('activePricing')->findOrFail($modelId);

        $validated = $request->validate([
            'text_input_rate' => ['required', 'numeric', 'min:0'],
            'cached_text_rate' => ['nullable', 'numeric', 'min:0'],
            'image_input_rate' => ['required', 'numeric', 'min:0'],
            'image_output_rate' => ['required', 'numeric', 'min:0'],
            'fixed_per_image_cost_usd' => ['nullable', 'numeric', 'min:0'],
        ]);

        $activePricing = $model->activePricing;
        if ($activePricing) {
            $activePricing->update([
                'text_input_rate' => $validated['text_input_rate'],
                'cached_text_rate' => $validated['cached_text_rate'] ?? 0,
                'image_input_rate' => $validated['image_input_rate'],
                'image_output_rate' => $validated['image_output_rate'],
                'fixed_per_image_cost_usd' => $validated['fixed_per_image_cost_usd'] ?? null,
            ]);
        } else {
            AiModelPricing::create([
                'model_id' => $model->id,
                'version' => '1.0',
                'billing_mode' => 'token',
                'currency' => 'USD',
                'text_input_rate' => $validated['text_input_rate'],
                'cached_text_rate' => $validated['cached_text_rate'] ?? 0,
                'image_input_rate' => $validated['image_input_rate'],
                'image_output_rate' => $validated['image_output_rate'],
                'fixed_per_image_cost_usd' => $validated['fixed_per_image_cost_usd'] ?? null,
                'effective_at' => now(),
            ]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Tarif token model {$model->model_name} berhasil diperbarui.",
            ]);
        }

        return redirect()->route('admin.ai-studio.pricing.index')
            ->with('success', "Tarif token model {$model->model_name} berhasil diperbarui.");
    }

    /**
     * Perbarui Matriks Harga & Biaya Kredit Model Generasi AI.
     */
    public function updatePricing(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'pricing_overrides' => ['required', 'array'],
        ]);

        // Simpan konfigurasi matriks harga kustom ke pengaturan sistem
        Setting::set('ai_studio_pricing_overrides', json_encode($validated['pricing_overrides']));

        return response()->json([
            'success' => true,
            'message' => 'Matriks harga kredit AI Studio berhasil diperbarui.',
        ]);
    }
}
