<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Ai\Platform\AiPlatformEngineService;
use App\Http\Controllers\Controller;
use App\Models\AiTokenUsage;
use App\Models\Business;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

final class AdminAiTokenController extends Controller implements HasMiddleware
{
    public function __construct(
        private readonly AiPlatformEngineService $engineService = new AiPlatformEngineService()
    ) {}

    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('require.admin.permission:admin.ai_tokens.view', only: ['index']),
            new Middleware('require.admin.permission:admin.ai_tokens.grant', only: [
                'grant',
                'updateEngine',
                'testEngine',
                'detectModels',
                'addKey',
                'toggleKey',
                'deleteKey',
                'addModel',
                'deleteModel',
                'resetDefaultModels',
            ]),
        ];
    }

    /**
     * Display AI token monitoring dashboard, usage metrics, and Platform AI Engine Command Center.
     */
    public function index(Request $request): View
    {
        $startOfMonth = Carbon::now()->startOfMonth();

        $totalTokensAllTime = (int) AiTokenUsage::sum('total_tokens');
        $totalTokensMonth = (int) AiTokenUsage::where('created_at', '>=', $startOfMonth)->sum('total_tokens');
        $totalQueriesCount = AiTokenUsage::count();
        $activeAiBusinessesCount = AiTokenUsage::where('created_at', '>=', $startOfMonth)
            ->distinct('business_id')
            ->count('business_id');

        // Usage by Intent Breakdown
        $intentBreakdown = AiTokenUsage::select('intent', DB::raw('SUM(total_tokens) as total_tokens'), DB::raw('COUNT(*) as queries_count'))
            ->groupBy('intent')
            ->orderByDesc('total_tokens')
            ->get();

        // Top 10 Consuming Businesses
        $topBusinesses = AiTokenUsage::select('business_id', DB::raw('SUM(total_tokens) as total_tokens'), DB::raw('COUNT(*) as queries_count'))
            ->groupBy('business_id')
            ->orderByDesc('total_tokens')
            ->take(10)
            ->get()
            ->map(function ($item) {
                $business = Business::find($item->business_id);
                return [
                    'business_id' => $item->business_id,
                    'business_name' => $business?->name ?? 'Bisnis Dihapus',
                    'slug' => $business?->slug ?? '',
                    'total_tokens' => (int) $item->total_tokens,
                    'queries_count' => (int) $item->queries_count,
                ];
            });

        // Recent 20 AI Token Activity logs
        $recentUsages = AiTokenUsage::with(['user'])
            ->latest()
            ->take(20)
            ->get()
            ->map(function ($usage) {
                $business = Business::find($usage->business_id);
                $usage->business_name = $business?->name ?? 'Bisnis Dihapus';
                return $usage;
            });

        // All businesses list for grant modal
        $allBusinesses = Business::orderBy('name')->get(['id', 'name', 'slug']);

        // Platform AI Engine Config (Multi-Provider & Multi-Key Pool)
        $platformEngine = $this->engineService->getConfig();
        $activeTab = $request->query('tab', 'engine');

        return view('admin.ai_tokens.index', compact(
            'totalTokensAllTime',
            'totalTokensMonth',
            'totalQueriesCount',
            'activeAiBusinessesCount',
            'intentBreakdown',
            'topBusinesses',
            'recentUsages',
            'allBusinesses',
            'platformEngine',
            'activeTab'
        ));
    }

    /**
     * Update Platform AI Engine settings (Provider, Custom Endpoint, Active Model, Rotation Strategy).
     */
    public function updateEngine(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'provider' => ['required', 'string', 'in:openai_compatible,openai,gemini,anthropic,openrouter'],
            'endpoint' => ['nullable', 'string', 'max:500'],
            'model' => ['required', 'string', 'max:150'],
            'custom_model' => ['nullable', 'string', 'max:150'],
            'rotation_strategy' => ['required', 'string', 'in:round_robin,failover'],
        ]);

        $effectiveModel = trim($validated['model']);
        if ($effectiveModel === 'custom' && ! empty($validated['custom_model'])) {
            $effectiveModel = trim($validated['custom_model']);
        }

        $this->engineService->updateConfig([
            'provider' => $validated['provider'],
            'endpoint' => $validated['endpoint'] ?? '',
            'model' => $effectiveModel,
            'rotation_strategy' => $validated['rotation_strategy'],
        ]);

        $adminName = auth('admin')->user()?->name ?? 'Superadmin';
        Log::info("AUDIT: Admin [{$adminName}] memperbarui konfigurasi Platform AI Engine. Provider: {$validated['provider']}, Model: {$effectiveModel}.");

        return redirect()->route('admin.ai-tokens.index', ['tab' => 'engine'])
            ->with('success', 'Konfigurasi Platform AI Engine berhasil disimpan.');
    }

    /**
     * Test connection to the Platform AI Engine (Live Ping).
     */
    public function testEngine(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'provider' => ['nullable', 'string'],
            'endpoint' => ['nullable', 'string'],
            'api_key' => ['nullable', 'string'],
            'model' => ['nullable', 'string'],
        ]);

        $result = $this->engineService->testConnection(
            $validated['provider'] ?? null,
            $validated['endpoint'] ?? null,
            $validated['api_key'] ?? null,
            $validated['model'] ?? null
        );

        return response()->json($result);
    }

    /**
     * Discover available models from the platform endpoint.
     */
    public function detectModels(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'provider' => ['nullable', 'string'],
            'endpoint' => ['nullable', 'string'],
            'api_key' => ['nullable', 'string'],
        ]);

        $models = $this->engineService->detectModels(
            $validated['provider'] ?? null,
            $validated['endpoint'] ?? null,
            $validated['api_key'] ?? null
        );

        return response()->json([
            'success' => true,
            'models' => $models,
            'count' => count($models),
            'message' => 'Berhasil mendeteksi ' . count($models) . ' model aktif dari endpoint.',
        ]);
    }

    /**
     * Add an API Key to the Platform Multi-Key Pool.
     */
    public function addKey(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'key_name' => ['required', 'string', 'max:100'],
            'api_key' => ['required', 'string', 'min:8', 'max:1000'],
        ], [
            'key_name.required' => 'Label nama API Key wajib diisi.',
            'api_key.required' => 'Token API Key wajib diisi.',
            'api_key.min' => 'Format API Key terlalu pendek.',
        ]);

        $entry = $this->engineService->addKey($validated['key_name'], $validated['api_key']);

        $adminName = auth('admin')->user()?->name ?? 'Superadmin';
        Log::info("AUDIT: Admin [{$adminName}] menambahkan API Key baru [{$validated['key_name']}] ke Platform AI Pool.");

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'API Key berhasil ditambahkan ke Multi-Key Pool.',
                'entry' => $entry,
            ]);
        }

        return redirect()->route('admin.ai-tokens.index', ['tab' => 'engine'])
            ->with('success', "API Key '{$validated['key_name']}' berhasil ditambahkan ke pool.");
    }

    /**
     * Toggle active state of an API key in the pool.
     */
    public function toggleKey(Request $request, string $keyId): RedirectResponse|JsonResponse
    {
        $updated = $this->engineService->toggleKey($keyId);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => $updated,
                'message' => $updated ? 'Status API Key berhasil diubah.' : 'API Key tidak ditemukan.',
            ]);
        }

        return redirect()->route('admin.ai-tokens.index', ['tab' => 'engine'])
            ->with('success', 'Status API Key berhasil diperbarui.');
    }

    /**
     * Delete an API key from the pool.
     */
    public function deleteKey(Request $request, string $keyId): RedirectResponse|JsonResponse
    {
        $deleted = $this->engineService->deleteKey($keyId);

        $adminName = auth('admin')->user()?->name ?? 'Superadmin';
        Log::info("AUDIT: Admin [{$adminName}] menghapus API Key [{$keyId}] dari Platform AI Pool.");

        if ($request->expectsJson()) {
            return response()->json([
                'success' => $deleted,
                'message' => $deleted ? 'API Key berhasil dihapus dari pool.' : 'API Key tidak ditemukan.',
            ]);
        }

        return redirect()->route('admin.ai-tokens.index', ['tab' => 'engine'])
            ->with('success', 'API Key berhasil dihapus dari pool.');
    }

    /**
     * Add or update a model in the dynamic models pool ("bongkar pasang").
     */
    public function addModel(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'model_id' => ['required', 'string', 'max:100'],
            'model_name' => ['nullable', 'string', 'max:150'],
            'tier' => ['nullable', 'string', 'in:reasoning,balanced,fast'],
        ]);

        $models = $this->engineService->addCustomModel(
            $validated['model_id'],
            $validated['model_name'] ?? null,
            $validated['tier'] ?? 'balanced'
        );

        $adminName = auth('admin')->user()?->name ?? 'Superadmin';
        Log::info("AUDIT: Admin [{$adminName}] menambahkan/memperbarui model [{$validated['model_id']}] ke Platform AI Engine.");

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Model '{$validated['model_id']}' berhasil dipasang ke engine.",
                'models' => $models,
            ]);
        }

        return redirect()->route('admin.ai-tokens.index', ['tab' => 'engine'])
            ->with('success', "Model '{$validated['model_id']}' berhasil dipasang ke engine.");
    }

    /**
     * Delete a model from the dynamic models pool ("bongkar pasang").
     */
    public function deleteModel(Request $request, string $modelId): RedirectResponse|JsonResponse
    {
        $decodedModelId = rawurldecode($modelId);
        $models = $this->engineService->deleteCustomModel($decodedModelId);

        $adminName = auth('admin')->user()?->name ?? 'Superadmin';
        Log::info("AUDIT: Admin [{$adminName}] membongkar/menghapus model [{$decodedModelId}] dari Platform AI Engine.");

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Model '{$decodedModelId}' berhasil dibongkar/dihapus.",
                'models' => $models,
            ]);
        }

        return redirect()->route('admin.ai-tokens.index', ['tab' => 'engine'])
            ->with('success', "Model '{$decodedModelId}' berhasil dibongkar/dihapus.");
    }

    /**
     * Reset models pool to the 13 baseline default seeds.
     */
    public function resetDefaultModels(Request $request): RedirectResponse|JsonResponse
    {
        $models = $this->engineService->resetModelsToDefault();

        $adminName = auth('admin')->user()?->name ?? 'Superadmin';
        Log::info("AUDIT: Admin [{$adminName}] me-reset daftar model Platform AI Engine ke 13 model bawaan tunnel.");

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Daftar model berhasil di-reset ke 13 model standar OpenAI-compatible.',
                'models' => $models,
            ]);
        }

        return redirect()->route('admin.ai-tokens.index', ['tab' => 'engine'])
            ->with('success', 'Daftar model berhasil di-reset ke 13 model standar OpenAI-compatible.');
    }

    /**
     * Manually grant / top-up AI tokens to a business with strict upper limit & audit trail.
     */
    public function grant(Request $request, Business $business): RedirectResponse
    {
        $validated = $request->validate([
            'tokens' => ['required', 'integer', 'min:10000', 'max:10000000'],
            'reason' => ['required', 'string', 'min:5', 'max:255'],
        ], [
            'tokens.required' => 'Jumlah token AI wajib ditentukan.',
            'tokens.min'      => 'Pemberian token minimal 10.000 token.',
            'tokens.max'      => 'Pemberian token manual dibatasi maksimal 10.000.000 token per aksi demi keamanan biaya.',
            'reason.required' => 'Wajib mencantumkan alasan pemberian token sebagai jejak audit.',
            'reason.min'      => 'Alasan pemberian token minimal 5 karakter.',
        ]);

        $sub = $business->subscription;
        if (! $sub) {
            return back()->with('error', "Gagal: Bisnis {$business->name} belum memiliki paket langganan aktif.");
        }

        $tokens = (int) $validated['tokens'];
        $sub->increment('ai_tokens_remaining', $tokens);

        $adminName = auth('admin')->user()?->name ?? 'System Superadmin';
        Log::info("AUDIT: Admin [{$adminName}] memberikan {$tokens} token AI ke bisnis [{$business->name}] (ID: {$business->id}). Alasan: {$validated['reason']}");

        return redirect()->route('admin.ai-tokens.index', ['tab' => 'monitoring'])
            ->with('success', "Berhasil menambahkan " . number_format($tokens, 0, ',', '.') . " token AI ke bisnis {$business->name}.");
    }
}
