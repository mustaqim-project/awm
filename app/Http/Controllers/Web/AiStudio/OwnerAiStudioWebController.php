<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\AiStudio;

use App\Domain\Ai\Inspiration\CommercialUseNotAllowedException;
use App\Domain\Ai\Studio\Billing\CostQuoteService;
use App\Domain\Ai\Studio\Billing\CreditLedgerService;
use App\Domain\Ai\Studio\Compilers\PromptCompilerService;
use App\Domain\Ai\Studio\Exceptions\InsufficientCreditsException;
use App\Domain\Ai\Studio\Orchestration\GenerationOrchestrator;
use App\Http\Controllers\Controller;
use App\Http\Requests\AiStudio\CompilePromptRequest;
use App\Http\Requests\AiStudio\DispatchGenerationRequest;
use App\Models\AiStudio\AiCreditTransaction;
use App\Models\AiStudio\BusinessInspirationGeneration;
use App\Models\AiStudio\BusinessInspirationGenerationAsset;
use App\Models\AiStudio\BusinessInspirationProject;
use App\Models\AiStudio\BusinessInspirationProjectAsset;
use App\Models\AiStudio\BusinessInspirationPromptVersion;
use App\Models\AiStudio\InspirationItem;
use App\Models\Product;
use App\Models\Unit;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

final class OwnerAiStudioWebController extends Controller implements HasMiddleware
{
    public function __construct(
        private readonly GenerationOrchestrator $orchestrator = new GenerationOrchestrator,
        private readonly PromptCompilerService $promptCompiler = new PromptCompilerService,
        private readonly CostQuoteService $costQuoteService = new CostQuoteService,
        private readonly CreditLedgerService $creditLedgerService = new CreditLedgerService,
    ) {}

    /**
     * Daftarkan middleware Controller-Level RBAC.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('require.permission:ai_studio.view', only: [
                'index',
                'show',
                'create',
                'generationStatus',
                'creditHistory',
            ]),
            new Middleware('require.permission:ai_studio.generate', only: [
                'compilePrompt',
                'dispatchGeneration',
            ]),
            new Middleware('require.permission:ai_studio.manage', only: [
                'store',
                'update',
                'destroy',
                'exportToCatalog',
                'toggleFavoriteAsset',
            ]),
        ];
    }

    /**
     * Dashboard & Daftar Proyek AI Studio Merchant.
     */
    public function index(Request $request): View|JsonResponse
    {
        $business = Context::requireBusiness();

        $query = BusinessInspirationProject::where('business_id', $business->id)
            ->with(['assets.product', 'assets.inspirationItem', 'latestGeneration.assets'])
            ->latest('last_activity_at');

        if ($request->filled('channel')) {
            $query->where('target_channel', $request->input('channel'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = (string) $request->input('search');
            $query->where('name', 'like', "%{$search}%");
        }

        $projects = $query->paginate(12)->withQueryString();

        $stats = [
            'total_projects' => BusinessInspirationProject::where('business_id', $business->id)->count(),
            'total_generations' => BusinessInspirationGeneration::where('business_id', $business->id)->count(),
            'completed_generations' => BusinessInspirationGeneration::where('business_id', $business->id)->where('status', 'succeeded')->count(),
            'credits_balance' => (int) ($business->ai_credits_balance ?? 0),
        ];

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $projects,
                'stats' => $stats,
            ]);
        }

        return view('app.ai-studio.index', compact('projects', 'stats', 'business'));
    }

    /**
     * Form Inisiasi Proyek Generasi AI Baru.
     */
    public function create(Request $request): View
    {
        $business = Context::requireBusiness();

        $products = Product::goods()
            ->where('business_id', $business->id)
            ->where('is_active', true)
            ->select(['id', 'name', 'code', 'selling_price', 'image_path'])
            ->orderBy('name')
            ->get();

        $selectedInspiration = null;
        if ($request->filled('inspiration_id')) {
            $selectedInspiration = InspirationItem::with(['source', 'category', 'visualAnalysis'])
                ->find($request->input('inspiration_id'));
        }

        $pricingMatrix = CostQuoteService::MODEL_PRICING;
        $creditsBalance = (int) ($business->ai_credits_balance ?? 0);

        return view('app.ai-studio.create', compact('products', 'selectedInspiration', 'pricingMatrix', 'creditsBalance', 'business'));
    }

    /**
     * Simpan Proyek Baru & Kaitkan Aset Referensi / Produk.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'target_channel' => ['required', 'string', 'in:instagram,tiktok,shopee,tokopedia,facebook,banner'],
            'aspect_ratio' => ['required', 'string', 'in:1:1,4:5,9:16,16:9'],
            'style_preset' => ['nullable', 'string', 'max:50'],
            'custom_notes' => ['nullable', 'string', 'max:1000'],
            'product_id' => ['nullable', 'uuid', Rule::exists('products', 'id')->where('business_id', $business->id)],
            'inspiration_item_id' => ['nullable', 'uuid', 'exists:inspiration_items,id'],
            'role' => ['nullable', 'string', 'in:hero_product,packaging,logo,supporting'],
        ]);

        $project = BusinessInspirationProject::create([
            'business_id' => $business->id,
            'user_id' => $request->user()?->id,
            'name' => $validated['name'],
            'target_channel' => $validated['target_channel'],
            'aspect_ratio' => $validated['aspect_ratio'],
            'style_preset' => $validated['style_preset'] ?? 'clean_commercial',
            'custom_notes' => $validated['custom_notes'] ?? null,
            'status' => 'draft',
            'last_activity_at' => now(),
        ]);

        // Kaitkan produk katalog tenant jika dipilih
        if (!empty($validated['product_id'])) {
            BusinessInspirationProjectAsset::create([
                'business_id' => $business->id,
                'project_id' => $project->id,
                'asset_type' => 'product',
                'product_id' => $validated['product_id'],
                'role' => $validated['role'] ?? 'hero_product',
            ]);
        }

        // Kaitkan item inspirasi legal jika dipilih
        if (!empty($validated['inspiration_item_id'])) {
            BusinessInspirationProjectAsset::create([
                'business_id' => $business->id,
                'project_id' => $project->id,
                'asset_type' => 'inspiration_item',
                'reference_item_id' => $validated['inspiration_item_id'],
                'role' => 'visual_reference',
            ]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Proyek AI Studio berhasil dibuat.',
                'project' => $project->load('assets'),
            ], 201);
        }

        return redirect()->route('ai-studio.show', $project->id)
            ->with('success', 'Proyek AI Studio berhasil diinisiasi.');
    }

    /**
     * Tampilan Detail Proyek, Canvas & Riwayat Generasi.
     */
    public function show(string $id, Request $request): View|JsonResponse
    {
        $business = Context::requireBusiness();

        /** @var BusinessInspirationProject $project */
        $project = BusinessInspirationProject::where('business_id', $business->id)
            ->with([
                'assets.product',
                'assets.inspirationItem.visualAnalysis',
                'promptVersions' => fn($q) => $q->latest(),
                'generations' => fn($q) => $q->latest()->with('assets'),
            ])
            ->findOrFail($id);

        $creditsBalance = (int) ($business->ai_credits_balance ?? 0);
        $pricingMatrix = CostQuoteService::MODEL_PRICING;

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'project' => $project,
                'credits_balance' => $creditsBalance,
            ]);
        }

        return view('app.ai-studio.show', compact('project', 'creditsBalance', 'pricingMatrix', 'business'));
    }

    /**
     * Kompilasi Prompt Dinamis & Kuotasi Kredit (Tahap Transparansi Prompt).
     */
    public function compilePrompt(CompilePromptRequest $request): JsonResponse
    {
        $business = Context::requireBusiness();

        try {
            $compiled = $this->promptCompiler->compile($business, $request->validated());

            $provider = (string) ($request->input('provider') ?? 'ideogram');
            $modelName = (string) ($request->input('model_name') ?? 'ideogram-v2');
            $aspectRatio = (string) ($request->input('aspect_ratio') ?? '1:1');
            $batchCount = (int) ($request->input('batch_count') ?? 1);
            $enableUpscale = (bool) ($request->input('enable_upscale') ?? false);

            $quote = $this->costQuoteService->quote(
                provider: $provider,
                modelName: $modelName,
                aspectRatio: $aspectRatio,
                batchCount: $batchCount,
                enableUpscale: $enableUpscale
            );

            $promptVersionId = null;

            // Jika project_id diberikan, otomatis buat versi prompt terarsip
            if ($request->filled('project_id')) {
                /** @var BusinessInspirationProject $project */
                $project = BusinessInspirationProject::where('business_id', $business->id)
                    ->findOrFail($request->input('project_id'));

                $latestVersion = (int) BusinessInspirationPromptVersion::where('project_id', $project->id)->max('version_number');

                $version = BusinessInspirationPromptVersion::create([
                    'project_id' => $project->id,
                    'version_number' => $latestVersion + 1,
                    'prompt_text' => $compiled['prompt_text'],
                    'negative_prompt' => $compiled['negative_prompt'] ?? null,
                    'target_channel' => $compiled['target_channel'] ?? $project->target_channel,
                    'aspect_ratio' => $compiled['aspect_ratio'] ?? $project->aspect_ratio,
                    'style_preset' => $compiled['style_preset'] ?? $project->style_preset,
                    'estimated_credits' => $quote['credit_cost'],
                    'compiler_meta' => [
                        'compiled_at' => now()->toIso8601String(),
                        'vision_attributes' => $compiled['vision_attributes'] ?? [],
                        'product_attributes' => $compiled['product_attributes'] ?? [],
                        'quote' => $quote,
                    ],
                ]);

                $promptVersionId = $version->id;
            }

            return response()->json([
                'success' => true,
                'data' => $compiled,
                'quote' => $quote,
                'prompt_version_id' => $promptVersionId,
            ]);
        } catch (CommercialUseNotAllowedException $e) {
            return response()->json([
                'success' => false,
                'error_code' => 'COMMERCIAL_USE_BLOCKED',
                'message' => $e->getMessage(),
            ], 422);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'error_code' => 'COMPILATION_FAILED',
                'message' => 'Gagal mengompilasi prompt: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Dispatch Pemesanan Generasi Gambar (Two-Phase Atomic Credit Hold + Job Queue).
     */
    public function dispatchGeneration(DispatchGenerationRequest $request): JsonResponse
    {
        $business = Context::requireBusiness();

        /** @var BusinessInspirationProject $project */
        $project = BusinessInspirationProject::where('business_id', $business->id)
            ->findOrFail($request->input('project_id'));

        $options = $request->validated();
        $options['user_id'] = $request->user()?->id;

        try {
            $generation = $this->orchestrator->dispatchGeneration($business, $project, $options);

            return response()->json([
                'success' => true,
                'message' => 'Pemesanan generasi gambar berhasil diproses dan dijadwalkan.',
                'generation' => $generation->load('assets'),
            ], 202);
        } catch (InsufficientCreditsException $e) {
            return response()->json([
                'success' => false,
                'error_code' => 'INSUFFICIENT_CREDITS',
                'message' => $e->getMessage(),
                'required' => $e->required,
                'available' => $e->available,
            ], 402);
        } catch (CommercialUseNotAllowedException $e) {
            return response()->json([
                'success' => false,
                'error_code' => 'COMMERCIAL_USE_BLOCKED',
                'message' => $e->getMessage(),
            ], 422);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'error_code' => 'DISPATCH_FAILED',
                'message' => 'Gagal menjadwalkan generasi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Endpoint Polling Status Generasi & Output Gambar (Alpine.js Canvas Poller).
     */
    public function generationStatus(string $projectId, string $generationId): JsonResponse
    {
        $business = Context::requireBusiness();

        /** @var BusinessInspirationGeneration $generation */
        $generation = BusinessInspirationGeneration::where('business_id', $business->id)
            ->where('project_id', $projectId)
            ->with('assets')
            ->findOrFail($generationId);

        return response()->json([
            'success' => true,
            'id' => $generation->id,
            'status' => $generation->status,
            'credit_cost' => $generation->credit_cost,
            'error_message' => $generation->error_message,
            'assets' => $generation->assets->map(fn($asset) => [
                'id' => $asset->id,
                'url' => $asset->asset_url,
                'thumbnail_url' => $asset->thumbnail_url,
                'width' => $asset->width,
                'height' => $asset->height,
                'is_favorite' => $asset->is_favorite,
            ]),
        ]);
    }

    /**
     * Simpan Aset Generasi AI langsung ke Katalog Produk COOCA (AC-010).
     */
    public function exportToCatalog(Request $request, string $generationAssetId): JsonResponse
    {
        $business = Context::requireBusiness();

        /** @var BusinessInspirationGenerationAsset $asset */
        $asset = BusinessInspirationGenerationAsset::where('business_id', $business->id)
            ->findOrFail($generationAssetId);

        $productId = $request->input('product_id');

        if (!empty($productId)) {
            /** @var Product $product */
            $product = Product::where('business_id', $business->id)->findOrFail($productId);
            $product->update([
                'image_path' => $asset->storage_path,
            ]);
        } else {
            // Buat produk baru berdasarkan informasi dari request
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'selling_price' => ['required', 'numeric', 'min:0'],
                'output_unit_id' => ['nullable', 'uuid', 'exists:units,id'],
            ]);

            $unitId = $validated['output_unit_id'] ?? Unit::where('business_id', $business->id)
                ->where('category', Unit::CATEGORY_QUANTITY)
                ->value('id') ?? Unit::where('category', Unit::CATEGORY_QUANTITY)->value('id');

            $product = Product::create([
                'business_id' => $business->id,
                'type' => Product::TYPE_GOODS,
                'name' => $validated['name'],
                'slug' => Str::slug($validated['name']) . '-' . Str::random(5),
                'selling_price' => $validated['selling_price'],
                'output_unit_id' => $unitId,
                'image_path' => $asset->storage_path,
                'is_active' => true,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Aset visual AI berhasil disimpan ke katalog produk.',
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'image_path' => $product->image_path,
            ],
        ]);
    }

    /**
     * Tandai Aset sebagai Favorit / Bintang.
     */
    public function toggleFavoriteAsset(Request $request, string $assetId): JsonResponse
    {
        $business = Context::requireBusiness();

        /** @var BusinessInspirationGenerationAsset $asset */
        $asset = BusinessInspirationGenerationAsset::where('business_id', $business->id)
            ->findOrFail($assetId);

        $asset->update([
            'is_favorite' => !$asset->is_favorite,
        ]);

        return response()->json([
            'success' => true,
            'is_favorite' => $asset->is_favorite,
        ]);
    }

    /**
     * Riwayat Transaksi Ledger Kredit AI Studio Bisnis.
     */
    public function creditHistory(Request $request): View|JsonResponse
    {
        $business = Context::requireBusiness();

        $transactions = AiCreditTransaction::where('business_id', $business->id)
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $creditsBalance = (int) ($business->ai_credits_balance ?? 0);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'credits_balance' => $creditsBalance,
                'transactions' => $transactions,
            ]);
        }

        return view('app.ai-studio.credit-history', compact('transactions', 'creditsBalance', 'business'));
    }

    /**
     * Hapus Proyek AI Studio.
     */
    public function destroy(string $id, Request $request): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();

        /** @var BusinessInspirationProject $project */
        $project = BusinessInspirationProject::where('business_id', $business->id)->findOrFail($id);
        $project->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Proyek AI Studio berhasil dihapus.',
            ]);
        }

        return redirect()->route('ai-studio.index')
            ->with('success', 'Proyek AI Studio berhasil dihapus.');
    }
}
