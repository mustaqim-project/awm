<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Ai\Studio\Billing\CostQuoteService;
use App\Domain\Ai\Studio\Billing\CreditLedgerService;
use App\Http\Controllers\Controller;
use App\Models\AiStudio\AiCreditTransaction;
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
            ]),
        ];
    }

    /**
     * Dashboard Penyesuaian Harga & Ledger Kredit AI Studio Platform.
     */
    public function index(Request $request): View|JsonResponse
    {
        $pricingMatrix = CostQuoteService::MODEL_PRICING;

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
                'stats' => $stats,
                'businesses' => $businesses,
                'recent_transactions' => $recentTransactions,
            ]);
        }

        return view('admin.ai-studio.pricing', compact('pricingMatrix', 'recentTransactions', 'businesses', 'stats'));
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
