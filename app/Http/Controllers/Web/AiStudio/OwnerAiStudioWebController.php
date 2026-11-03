<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\AiStudio;

use App\Domain\Ai\Inspiration\CommercialUseNotAllowedException;
use App\Domain\Ai\Studio\Billing\CostQuoteService;
use App\Domain\Ai\Studio\Billing\CreditLedgerService;
use App\Domain\Ai\Studio\Compilers\PromptCompilerService;
use App\Domain\Ai\Studio\Exceptions\InsufficientCreditsException;
use App\Domain\Ai\Studio\Orchestration\GenerationOrchestrator;
use App\Domain\Billing\EntitlementService;
use App\Domain\Payment\TripayService;
use App\Http\Controllers\Controller;
use App\Http\Requests\AiStudio\CompilePromptRequest;
use App\Http\Requests\AiStudio\DispatchGenerationRequest;
use App\Models\AiStudio\AiCreditTransaction;
use App\Models\AiStudio\AiModelRegistry;
use App\Models\AiStudio\BusinessInspirationGeneration;
use App\Models\AiStudio\BusinessInspirationGenerationAsset;
use App\Models\AiStudio\BusinessInspirationProject;
use App\Models\AiStudio\BusinessInspirationProjectAsset;
use App\Models\AiStudio\BusinessInspirationPromptVersion;
use App\Models\AiStudio\InspirationItem;
use App\Models\BillingPackage;
use App\Models\Product;
use App\Models\SocialMediaAccount;
use App\Models\SocialMediaPost;
use App\Models\SocialPostMedia;
use App\Models\SocialPostTarget;
use App\Models\SubscriptionPayment;
use App\Models\Unit;
use App\Support\Context;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

final class OwnerAiStudioWebController extends Controller implements HasMiddleware
{
    /**
     * Konfigurasi Preset Paket Top-Up Kredit AI Studio (UMKM Friendly).
     */
    public const TOPUP_TIERS = [
        'starter' => [
            'code' => 'starter',
            'name' => 'Starter Pack',
            'credits' => 50,
            'price' => 25000,
            'formatted_price' => 'Rp 25.000',
            'badge' => null,
            'description' => 'Uji coba 3-5 visual katalog produk & materi promosi sosmed.',
        ],
        'growth' => [
            'code' => 'growth',
            'name' => 'Growth Pack',
            'credits' => 150,
            'price' => 65000,
            'formatted_price' => 'Rp 65.000',
            'badge' => 'Terpopuler',
            'description' => 'Photoshoot katalog mingguan & kampanye visual omnichannel.',
        ],
        'pro' => [
            'code' => 'pro',
            'name' => 'Pro Studio',
            'credits' => 300,
            'price' => 120000,
            'formatted_price' => 'Rp 120.000',
            'badge' => 'Paling Hemat',
            'description' => 'Photoshoot katalog lengkap, multi-variasi rasio, & seasonal campaign.',
        ],
        'sultan' => [
            'code' => 'sultan',
            'name' => 'Sultan Pack',
            'credits' => 1000,
            'price' => 350000,
            'formatted_price' => 'Rp 350.000',
            'badge' => 'Nilai Terbaik',
            'description' => 'Kapasitas maksimal untuk studio foto produk e-commerce & agensi.',
        ],
    ];

    public function __construct(
        private readonly GenerationOrchestrator $orchestrator = new GenerationOrchestrator,
        private readonly PromptCompilerService $promptCompiler = new PromptCompilerService,
        private readonly CostQuoteService $costQuoteService = new CostQuoteService,
        private readonly CreditLedgerService $creditLedgerService = new CreditLedgerService,
        private readonly TripayService $tripayService = new TripayService,
        private readonly EntitlementService $entitlementService = new EntitlementService,
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
                'topupPackages',
                'checkTopupStatus',
            ]),
            new Middleware('require.permission:ai_studio.generate', only: [
                'compilePrompt',
                'dispatchGeneration',
                'orderTopup',
            ]),
            new Middleware('require.permission:ai_studio.manage', only: [
                'store',
                'update',
                'destroy',
                'exportToCatalog',
                'exportToSocialMedia',
                'toggleFavoriteAsset',
                'destroyAsset',
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

        $perPage = \App\Support\PaginationHelper::perPage($request, 25);
        $projects = $query->paginate($perPage)->withQueryString();

        $stats = [
            'total_projects' => BusinessInspirationProject::where('business_id', $business->id)->count(),
            'total_generations' => BusinessInspirationGeneration::where('business_id', $business->id)->count(),
            'completed_generations' => BusinessInspirationGeneration::where('business_id', $business->id)->where('status', 'succeeded')->count(),
            'total_assets' => BusinessInspirationGenerationAsset::where('business_id', $business->id)->count(),
            'credits_balance' => (int) ($business->ai_credits_balance ?? 0),
        ];

        // Katalog Aset Visual Milik Tenant
        $assetsQuery = BusinessInspirationGenerationAsset::where('business_id', $business->id)
            ->with(['generation.project'])
            ->latest();

        $activeTab = (string) $request->query('tab', 'projects');
        $aspectRatioFilter = (string) $request->query('ratio', 'all');
        $onlyFavorite = $request->boolean('favorite', false);

        if ($aspectRatioFilter !== 'all' && in_array($aspectRatioFilter, ['1:1', '9:16', '16:9', '4:5'], true)) {
            $assetsQuery->where('aspect_ratio', $aspectRatioFilter);
        }

        if ($onlyFavorite) {
            $assetsQuery->where('is_selected_favorite', true);
        }

        $aiAssets = $assetsQuery->paginate(16, ['*'], 'assets_page')->withQueryString();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $projects,
                'assets' => $aiAssets,
                'stats' => $stats,
            ]);
        }

        return view('app.ai-studio.index', compact('projects', 'aiAssets', 'stats', 'business', 'activeTab', 'aspectRatioFilter', 'onlyFavorite'));
    }

    /**
     * Form Inisiasi Proyek Generasi AI Baru.
     */
    public function create(Request $request): View
    {
        $business = Context::requireBusiness();

        // Ambil produk dan layanan jasa aktif milik tenant
        $products = Product::where('business_id', $business->id)
            ->where('is_active', true)
            ->select(['id', 'name', 'code', 'type', 'selling_price', 'image_path', 'category_id'])
            ->with('category:id,name')
            ->orderBy('name')
            ->get();

        $selectedProduct = null;
        if ($request->filled('product_id')) {
            $selectedProduct = Product::where('business_id', $business->id)->find($request->input('product_id'));
        }

        $selectedInspiration = null;
        if ($request->filled('inspiration_id')) {
            $selectedInspiration = InspirationItem::with(['source', 'category', 'visualAnalysis'])
                ->find($request->input('inspiration_id'));
        }

        $pricingMatrix = CostQuoteService::MODEL_PRICING;
        $creditsBalance = (int) ($business->ai_credits_balance ?? 0);

        $activeModels = AiModelRegistry::with('activePricing')
            ->where('is_active', true)
            ->orderBy('is_default', 'desc')
            ->get();

        return view('app.ai-studio.create', compact('products', 'selectedProduct', 'selectedInspiration', 'pricingMatrix', 'creditsBalance', 'business', 'activeModels'));
    }

    /**
     * Simpan Proyek Baru & Kaitkan Aset Referensi / Produk (Katalog atau Upload Sendiri).
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
            'custom_product_name' => ['nullable', 'string', 'max:150'],
            'custom_product_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'inspiration_item_id' => ['nullable', 'uuid', 'exists:inspiration_items,id'],
            'custom_reference_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'role' => ['nullable', 'string', 'in:hero_product,packaging,logo,supporting'],
        ]);

        $project = BusinessInspirationProject::create([
            'business_id' => $business->id,
            'user_id' => $request->user()?->id,
            'name' => $validated['name'],
            'target_platform' => $validated['target_channel'] ?? 'instagram',
            'description' => $validated['custom_notes'] ?? null,
            'product_id' => $validated['product_id'] ?? null,
            'inspiration_item_id' => $validated['inspiration_item_id'] ?? null,
            'status' => 'draft',
            'last_activity_at' => now(),
        ]);

        // Resolusi entitas produk & referensi inspirasi untuk sintesis prompt otonom
        $selectedProduct = ! empty($validated['product_id'])
            ? Product::where('business_id', $business->id)->find($validated['product_id'])
            : null;

        $selectedInspiration = ! empty($validated['inspiration_item_id'])
            ? InspirationItem::with(['category', 'visualAnalysis'])->find($validated['inspiration_item_id'])
            : null;

        $briefData = [
            'product_name' => $validated['custom_product_name'] ?? $selectedProduct?->name ?? $validated['name'],
            'target_channel' => $validated['target_channel'] ?? 'instagram',
            'aspect_ratio' => $validated['aspect_ratio'] ?? '1:1',
            'style_preset' => $validated['style_preset'] ?? 'clean_commercial',
            'custom_notes' => $validated['custom_notes'] ?? null,
            'role' => $validated['role'] ?? 'hero_product',
        ];

        try {
            $compiled = $this->promptCompiler->compile(
                business: $business,
                product: $selectedProduct,
                reference: $selectedInspiration,
                brief: $briefData
            );
            $positivePrompt = $compiled['positive_prompt'];
            $negativePrompt = $compiled['negative_prompt'];
            $promptParameters = $compiled['prompt_parameters'];
            $guardrailStatus = $compiled['guardrail_status'];
            $guardrailNotes = $compiled['guardrail_notes'];
        } catch (\Throwable) {
            $positivePrompt = "Professional commercial studio product photography of {$briefData['product_name']}, centered, clean aesthetic, high resolution";
            $negativePrompt = 'blurry, amateur, low quality, distorted text, oversaturated';
            $promptParameters = [
                'target_channel' => $validated['target_channel'],
                'custom_notes' => $validated['custom_notes'] ?? null,
                'custom_product_name' => $validated['custom_product_name'] ?? null,
            ];
            $guardrailStatus = 'passed';
            $guardrailNotes = null;
        }

        // Simpan versi prompt awal yang telah dikompilasi secara cerdas oleh AI Studio
        BusinessInspirationPromptVersion::create([
            'business_id' => $business->id,
            'project_id' => $project->id,
            'version_number' => 1,
            'positive_prompt' => $positivePrompt,
            'negative_prompt' => $negativePrompt,
            'aspect_ratio' => $validated['aspect_ratio'],
            'style_preset' => $validated['style_preset'] ?? 'clean_commercial',
            'prompt_parameters' => $promptParameters,
            'is_approved' => $guardrailStatus === 'passed',
            'guardrail_status' => $guardrailStatus,
            'guardrail_notes' => $guardrailNotes,
        ]);

        // 1. Kaitkan produk katalog tenant jika dipilih
        if (! empty($validated['product_id'])) {
            $product = Product::find($validated['product_id']);
            $filePath = $product?->image_path ?? 'catalog/products/placeholder.jpg';
            BusinessInspirationProjectAsset::create([
                'business_id' => $business->id,
                'project_id' => $project->id,
                'asset_type' => 'original_product',
                'file_path' => $filePath,
                'is_primary' => true,
                'metadata' => [
                    'source' => 'catalog_product',
                    'product_id' => $validated['product_id'],
                    'product_name' => $product?->name,
                    'role' => $validated['role'] ?? 'hero_product',
                ],
            ]);
        } elseif ($request->hasFile('custom_product_image')) {
            // 1b. Atau simpan file foto objek/produk yang diupload manual
            $file = $request->file('custom_product_image');
            $dir = \App\Domain\Storage\TenantStorage::publicDir($business, 'ai-studio/products');
            $filePath = $file->store($dir, 'public');

            BusinessInspirationProjectAsset::create([
                'business_id' => $business->id,
                'project_id' => $project->id,
                'asset_type' => 'original_product',
                'file_path' => $filePath,
                'original_filename' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'file_size_bytes' => (int) $file->getSize(),
                'is_primary' => true,
                'metadata' => [
                    'source' => 'custom_upload',
                    'product_name' => $validated['custom_product_name'] ?? $validated['name'],
                    'role' => $validated['role'] ?? 'hero_product',
                ],
            ]);
        }

        // 2. Kaitkan item inspirasi legal jika dipilih dari galeri
        if (! empty($validated['inspiration_item_id'])) {
            $inspiration = InspirationItem::find($validated['inspiration_item_id']);
            $filePath = $inspiration?->canonical_url ?? $inspiration?->thumbnail_url ?? 'inspiration/reference.jpg';
            BusinessInspirationProjectAsset::create([
                'business_id' => $business->id,
                'project_id' => $project->id,
                'asset_type' => 'reference_style',
                'file_path' => $filePath,
                'is_primary' => false,
                'metadata' => [
                    'source' => 'inspiration_gallery',
                    'inspiration_item_id' => $validated['inspiration_item_id'],
                    'title' => $inspiration?->title,
                    'role' => 'visual_reference',
                ],
            ]);
        } elseif ($request->hasFile('custom_reference_image')) {
            // 2b. Atau simpan file foto referensi visual yang diupload manual
            $refFile = $request->file('custom_reference_image');
            $dir = \App\Domain\Storage\TenantStorage::publicDir($business, 'ai-studio/references');
            $refFilePath = $refFile->store($dir, 'public');

            BusinessInspirationProjectAsset::create([
                'business_id' => $business->id,
                'project_id' => $project->id,
                'asset_type' => 'reference_style',
                'file_path' => $refFilePath,
                'original_filename' => $refFile->getClientOriginalName(),
                'mime_type' => $refFile->getMimeType(),
                'file_size_bytes' => (int) $refFile->getSize(),
                'is_primary' => false,
                'metadata' => [
                    'source' => 'custom_upload',
                    'role' => 'visual_reference',
                ],
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

        $socialAccounts = SocialMediaAccount::where('business_id', $business->id)
            ->where('status', 'active')
            ->orderBy('platform')
            ->get();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'project' => $project,
                'credits_balance' => $creditsBalance,
            ]);
        }

        return view('app.ai-studio.show', compact('project', 'creditsBalance', 'pricingMatrix', 'business', 'socialAccounts'));
    }

    /**
     * Kompilasi Prompt Dinamis & Kuotasi Kredit (Tahap Transparansi Prompt).
     */
    public function compilePrompt(CompilePromptRequest $request): JsonResponse
    {
        $business = Context::requireBusiness();

        try {
            $product = $request->filled('product_id')
                ? Product::where('business_id', $business->id)->find($request->input('product_id'))
                : null;

            $reference = $request->filled('inspiration_item_id')
                ? InspirationItem::find($request->input('inspiration_item_id'))
                : null;

            $brief = $request->validated();
            if (! empty($brief['custom_product_name'])) {
                $brief['product_name'] = $brief['custom_product_name'];
            }

            $compiled = $this->promptCompiler->compile(
                business: $business,
                product: $product,
                reference: $reference,
                brief: $brief
            );

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
                    'business_id' => $business->id,
                    'project_id' => $project->id,
                    'version_number' => $latestVersion + 1,
                    'positive_prompt' => (string) ($compiled['positive_prompt'] ?? $compiled['prompt_text'] ?? ''),
                    'negative_prompt' => $compiled['negative_prompt'] ?? null,
                    'aspect_ratio' => (string) ($request->input('aspect_ratio') ?? '1:1'),
                    'style_preset' => (string) ($request->input('style_preset') ?? 'clean_commercial'),
                    'prompt_parameters' => [
                        'compiled_at' => now()->toIso8601String(),
                        'vision_attributes' => $compiled['vision_attributes'] ?? [],
                        'product_attributes' => $compiled['product_attributes'] ?? [],
                        'quote' => $quote,
                        'estimated_credits' => $quote['credit_cost'],
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
                'url' => $asset->output_image_url ?? $asset->asset_url,
                'thumbnail_url' => $asset->thumbnail_url,
                'width' => $asset->width,
                'height' => $asset->height,
                'is_favorite' => $asset->is_selected_favorite,
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
     * Ekspor atau jadwalkan posting Aset Generasi AI langsung ke Media Sosial (Instagram, Facebook, TikTok).
     */
    public function exportToSocialMedia(Request $request, string $generationAssetId): JsonResponse
    {
        $business = Context::requireBusiness();

        /** @var BusinessInspirationGenerationAsset $asset */
        $asset = BusinessInspirationGenerationAsset::where('business_id', $business->id)
            ->findOrFail($generationAssetId);

        $validated = $request->validate([
            'social_media_account_id' => ['nullable', 'uuid'],
            'platform' => ['required', 'string'],
            'content' => ['required', 'string', 'max:5000'],
            'publish_mode' => ['required', 'string', 'in:now,scheduled,draft'],
            'scheduled_at' => ['nullable', 'date'],
        ]);

        $status = match ($validated['publish_mode']) {
            'now' => SocialMediaPost::STATUS_SCHEDULED,
            'scheduled' => SocialMediaPost::STATUS_SCHEDULED,
            default => SocialMediaPost::STATUS_DRAFT,
        };

        $scheduledAt = null;
        if ($validated['publish_mode'] === 'scheduled' && !empty($validated['scheduled_at'])) {
            $scheduledAt = Carbon::parse($validated['scheduled_at']);
        } elseif ($validated['publish_mode'] === 'now') {
            $scheduledAt = now();
        }

        $imagePath = $asset->storage_path ?? $asset->asset_url;
        $mediaUrl = $asset->output_image_url ?? ($asset->storage_path ? asset('storage/' . ltrim($asset->storage_path, '/')) : null);

        $post = SocialMediaPost::create([
            'business_id' => $business->id,
            'user_id' => auth()->id(),
            'social_media_account_id' => $validated['social_media_account_id'] ?: null,
            'platform' => $validated['platform'],
            'content' => $validated['content'],
            'media_type' => 'image',
            'media_urls' => $mediaUrl ? [$mediaUrl] : [],
            'local_media_paths' => $imagePath ? [$imagePath] : [],
            'status' => $status,
            'approval_status' => 'approved',
            'scheduled_at' => $scheduledAt,
        ]);

        // Simpan lampiran media ke SocialPostMedia
        if ($imagePath || $mediaUrl) {
            SocialPostMedia::create([
                'social_media_post_id' => $post->id,
                'media_type'           => 'image',
                'file_path'            => $imagePath,
                'local_path'           => $imagePath,
                'url'                  => $mediaUrl,
                'sort_order'           => 1,
            ]);
        }

        // Jika terhubung dengan akun spesifik, buat target publish
        if (!empty($validated['social_media_account_id'])) {
            $account = SocialMediaAccount::where('business_id', $business->id)->find($validated['social_media_account_id']);
            if ($account) {
                $target = SocialPostTarget::create([
                    'social_media_post_id'    => $post->id,
                    'social_media_account_id' => $account->id,
                    'provider'                => $account->provider ?? $account->platform,
                    'channel'                 => $account->platform,
                    'content_type'            => 'image',
                    'custom_caption'          => $validated['content'],
                    'status'                  => $status === SocialMediaPost::STATUS_DRAFT ? 'draft' : ($validated['publish_mode'] === 'now' ? 'pending' : 'scheduled'),
                    'scheduled_at'            => $scheduledAt,
                    'retry_count'             => 0,
                ]);

                // Jika publikasikan sekarang, trigger immediate dispatch jika memungkinkan
                if ($validated['publish_mode'] === 'now') {
                    try {
                        \App\Jobs\SocialMedia\PublishSocialMediaTargetJob::dispatch($target->id);
                    } catch (Throwable $e) {
                        Log::warning('Failed dispatching immediate publish job: ' . $e->getMessage());
                    }
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => $validated['publish_mode'] === 'now'
                ? 'Aset visual AI berhasil dijadwalkan dan dikirim untuk publikasi ke Media Sosial!'
                : ($validated['publish_mode'] === 'scheduled'
                    ? 'Aset visual AI berhasil dijadwalkan tayang pada ' . $scheduledAt?->translatedFormat('d M Y H:i')
                    : 'Aset visual AI berhasil disimpan sebagai draf postingan media sosial.'),
            'post' => [
                'id' => $post->id,
                'platform' => $post->platform,
                'status' => $post->status,
                'scheduled_at' => $post->scheduled_at?->toIso8601String(),
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
            'is_selected_favorite' => !$asset->is_selected_favorite,
        ]);

        return response()->json([
            'success' => true,
            'is_favorite' => $asset->is_selected_favorite,
        ]);
    }

    /**
     * Hapus berkas dan data output aset visual individual milik bisnis tenant.
     */
    public function destroyAsset(Request $request, string $assetId): JsonResponse|RedirectResponse
    {
        $business = Context::requireBusiness();

        /** @var BusinessInspirationGenerationAsset $asset */
        $asset = BusinessInspirationGenerationAsset::where('business_id', $business->id)->findOrFail($assetId);

        // Hapus file fisik dari storage jika ada
        if (! empty($asset->storage_path) && \Illuminate\Support\Facades\Storage::disk('public')->exists($asset->storage_path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($asset->storage_path);
        }

        // Hapus thumbnail jika ada
        if (! empty($asset->thumbnail_url) && ! str_starts_with($asset->thumbnail_url, 'http')) {
            $thumbPath = str_replace(\Illuminate\Support\Facades\Storage::disk('public')->url(''), '', $asset->thumbnail_url);
            $thumbPath = ltrim($thumbPath, '/');
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($thumbPath)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($thumbPath);
            }
        }

        // Hapus record storage_files jika terdaftar
        if (! empty($asset->storage_file_id)) {
            \App\Models\StorageFile::where('id', $asset->storage_file_id)->delete();
        }

        $asset->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Aset gambar hasil generate AI berhasil dihapus.',
            ]);
        }

        return back()->with('success', 'Aset gambar berhasil dihapus dari katalog bisnis.');
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

    /**
     * Dapatkan daftar paket top-up kredit AI Studio dan channel pembayaran TriPay aktif.
     */
    public function topupPackages(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $dbPackages = collect();
        try {
            $dbPackages = BillingPackage::where('type', BillingPackage::TYPE_AI_TOKEN)->active()->get();
        } catch (Throwable) {
            $dbPackages = collect();
        }

        $packages = [];
        if ($dbPackages->isNotEmpty()) {
            foreach ($dbPackages as $pkg) {
                $badge = null;
                if (preg_match('/\[(.*?)\]/', $pkg->name, $matches)) {
                    $badge = trim($matches[1]);
                    $displayName = trim(str_replace($matches[0], '', $pkg->name));
                } else {
                    $displayName = $pkg->name;
                    $nameLower = strtolower($pkg->name);
                    $codeLower = strtolower((string) $pkg->code);
                    if (str_contains($nameLower, 'growth') || str_contains($codeLower, 'growth') || str_contains($nameLower, 'populer')) {
                        $badge = 'Terpopuler';
                    } elseif (str_contains($nameLower, 'pro') || str_contains($codeLower, 'pro') || str_contains($nameLower, 'hemat')) {
                        $badge = 'Paling Hemat';
                    } elseif (str_contains($nameLower, 'sultan') || str_contains($codeLower, 'sultan') || str_contains($nameLower, 'terbaik')) {
                        $badge = 'Nilai Terbaik';
                    }
                }

                $packages[] = [
                    'id' => $pkg->id,
                    'code' => $pkg->code,
                    'name' => $displayName,
                    'credits' => (int) ($pkg->token_quantity ?? 50),
                    'price' => (float) $pkg->price,
                    'formatted_price' => 'Rp ' . number_format((float) $pkg->price, 0, ',', '.'),
                    'badge' => $badge,
                    'description' => $pkg->description ?: "Isi ulang {$pkg->token_quantity} kredit visual AI Studio.",
                ];
            }
        } else {
            $packages = array_values(self::TOPUP_TIERS);
        }

        // Ambil channel pembayaran TriPay aktif
        $rawChannels = $this->tripayService->getActiveChannels();
        $paymentChannels = [];
        $seenQris = false;

        foreach ($rawChannels as $ch) {
            $code = strtolower((string) ($ch['code'] ?? ''));
            $isQris = str_contains($code, 'qris') || ($ch['type'] ?? '') === 'qris';

            if ($isQris) {
                if ($seenQris) {
                    continue;
                }
                $seenQris = true;
                $paymentChannels[] = [
                    'code' => 'qris',
                    'name' => 'QRIS Dinamis',
                    'group' => 'E-Wallet & QRIS',
                    'icon_url' => $ch['icon_url'] ?? 'https://tripay.co.id/images/payment-channel/qris.png',
                    'description' => 'GoPay, OVO, ShopeePay, DANA, BCA, Mandiri, BRImo, dll.',
                    'is_default' => true,
                ];
                continue;
            }

            if (in_array(strtoupper($code), ['BCAVA', 'BRIVA', 'MANDIRIVA', 'BNIVA', 'PERMATAVA'], true)) {
                $paymentChannels[] = [
                    'code' => strtolower($code),
                    'name' => $ch['name'] ?? strtoupper($code),
                    'group' => 'Virtual Account',
                    'icon_url' => $ch['icon_url'] ?? null,
                    'description' => $ch['description'] ?? 'Transfer Bank / Virtual Account otomatis',
                    'is_default' => false,
                ];
            }
        }

        if (empty($paymentChannels)) {
            $paymentChannels = [
                [
                    'code' => 'qris',
                    'name' => 'QRIS Dinamis',
                    'group' => 'E-Wallet & QRIS',
                    'icon_url' => 'https://tripay.co.id/images/payment-channel/qris.png',
                    'description' => 'GoPay, OVO, ShopeePay, DANA, BCA, Mandiri, BRImo, dll.',
                    'is_default' => true,
                ],
                [
                    'code' => 'bcava',
                    'name' => 'BCA Virtual Account',
                    'group' => 'Virtual Account',
                    'icon_url' => 'https://tripay.co.id/images/payment-channel/bca-va.png',
                    'description' => 'Transfer via BCA Mobile, KlikBCA, ATM BCA',
                    'is_default' => false,
                ],
                [
                    'code' => 'briva',
                    'name' => 'BRI Virtual Account',
                    'group' => 'Virtual Account',
                    'icon_url' => 'https://tripay.co.id/images/payment-channel/bri-va.png',
                    'description' => 'Transfer via BRImo, ATM BRI, Agen BRILink',
                    'is_default' => false,
                ],
                [
                    'code' => 'mandiriva',
                    'name' => 'Mandiri Virtual Account',
                    'group' => 'Virtual Account',
                    'icon_url' => 'https://tripay.co.id/images/payment-channel/mandiri-va.png',
                    'description' => 'Transfer via Livin by Mandiri, ATM Mandiri',
                    'is_default' => false,
                ],
            ];
        }

        return response()->json([
            'success' => true,
            'credits_balance' => (int) ($business->ai_credits_balance ?? 0),
            'packages' => $packages,
            'payment_channels' => $paymentChannels,
        ]);
    }

    /**
     * Buat pesanan top-up kredit AI Studio dan buat tagihan TriPay otomatis.
     */
    public function orderTopup(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $user = $request->user();

        $validated = $request->validate([
            'tier_code' => ['nullable', 'string', 'max:80'],
            'package_id' => ['nullable', 'string', 'max:80'],
            'payment_method' => ['required', 'string', 'max:50'],
        ]);

        $credits = 0;
        $price = 0.0;
        $packageName = '';
        $billingPackageId = null;

        $pkg = null;
        try {
            if (!empty($validated['package_id'])) {
                $pkg = BillingPackage::where('type', BillingPackage::TYPE_AI_TOKEN)->active()->find($validated['package_id']);
            }

            if (!$pkg && !empty($validated['tier_code'])) {
                $tierCode = trim((string) $validated['tier_code']);
                $pkg = BillingPackage::where('type', BillingPackage::TYPE_AI_TOKEN)
                    ->active()
                    ->where(function ($q) use ($tierCode) {
                        $q->where('code', $tierCode)
                            ->orWhere('id', $tierCode);
                    })
                    ->first();
            }
        } catch (Throwable) {
            $pkg = null;
        }

        if ($pkg) {
            $billingPackageId = $pkg->id;
            $credits = (int) ($pkg->token_quantity ?? 50);
            $price = (float) $pkg->price;
            $packageName = $pkg->name;
        } else {
            $tierCode = strtolower((string) ($validated['tier_code'] ?? 'starter'));
            if (!isset(self::TOPUP_TIERS[$tierCode])) {
                $tierCode = 'starter';
            }
            $tier = self::TOPUP_TIERS[$tierCode];
            $credits = (int) $tier['credits'];
            $price = (float) $tier['price'];
            $packageName = $tier['name'];
        }

        $paymentMethod = strtolower(trim((string) $validated['payment_method']));

        // Buat SubscriptionPayment atomik
        $orderNumber = $this->nextTopupOrderNumber();
        $payment = SubscriptionPayment::create([
            'business_id' => $business->id,
            'user_id' => $user?->id,
            'payment_type' => 'ai_token',
            'billing_package_id' => $billingPackageId,
            'order_number' => $orderNumber,
            'package_name' => "Top Up AI Studio: {$credits} Kredit ({$packageName})",
            'plan_code' => 'topup',
            'cycle' => 'one_time',
            'amount' => $price,
            'discount_amount' => 0,
            'unique_code' => 0,
            'total_payable' => $price,
            'topup_quantity' => $credits,
            'payment_method' => $paymentMethod,
            'payment_gateway' => SubscriptionPayment::GATEWAY_TRIPAY,
            'status' => SubscriptionPayment::STATUS_PENDING,
        ]);

        // Hubungkan ke TriPay Gateway
        $channelCode = $payment->getTripayChannelCode();
        $tripayRes = $this->tripayService->createSubscriptionTransaction($payment, $channelCode);

        if ($tripayRes['success'] ?? false) {
            $payment->update([
                'payment_gateway' => SubscriptionPayment::GATEWAY_TRIPAY,
                'gateway_reference' => $tripayRes['reference'] ?? null,
                'gateway_pay_code' => $tripayRes['pay_code'] ?? null,
                'gateway_pay_url' => $tripayRes['checkout_url'] ?? null,
                'gateway_qr_url' => $tripayRes['qr_url'] ?? null,
                'gateway_qr_string' => $tripayRes['qr_string'] ?? null,
                'gateway_fee' => (float) ($tripayRes['fee'] ?? 0.0),
                'gateway_expired_at' => isset($tripayRes['expired_time']) ? Carbon::createFromTimestamp($tripayRes['expired_time']) : now()->addMinutes(15),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tagihan pembayaran berhasil dibuat.',
                'payment' => [
                    'id' => $payment->id,
                    'order_number' => $payment->order_number,
                    'credits' => $credits,
                    'amount' => $price,
                    'formatted_amount' => 'Rp ' . number_format($price, 0, ',', '.'),
                    'payment_method' => $paymentMethod,
                    'channel_code' => $channelCode,
                    'qr_url' => $payment->gateway_qr_url,
                    'qr_string' => $payment->gateway_qr_string,
                    'pay_code' => $payment->gateway_pay_code,
                    'checkout_url' => $payment->gateway_pay_url,
                    'expired_at' => $payment->gateway_expired_at?->toIso8601String(),
                    'payment_url' => route('billing.payment.show', $payment->id),
                    'status_url' => route('ai-studio.topup.status', $payment->id),
                ],
            ]);
        }

        $errorMsg = $tripayRes['message'] ?? 'Gagal membuat tagihan via TriPay.';
        $payment->update(['admin_notes' => 'TriPay Error: ' . $errorMsg]);

        return response()->json([
            'success' => false,
            'message' => $errorMsg,
        ], 502);
    }

    /**
     * Cek status pembayaran topup secara live (digunakan polling di modal / UI).
     */
    public function checkTopupStatus(SubscriptionPayment $payment): JsonResponse
    {
        $business = Context::requireBusiness();
        abort_unless($payment->business_id === $business->id, 403);

        // Jika belum disetujui, cek apakah sudah dibayar di gateway
        if (!$payment->isPaid() && $payment->status === SubscriptionPayment::STATUS_PENDING && !empty($payment->gateway_reference)) {
            $tripayDetail = $this->tripayService->getTransactionDetail($payment->gateway_reference);
            if ($tripayDetail && strtoupper((string) ($tripayDetail['status'] ?? '')) === 'PAID') {
                $this->entitlementService->approvePayment(
                    payment: $payment,
                    admin: null,
                    adminNotes: 'Auto-verified on live polling check'
                );
                $payment->refresh();
            }
        }

        $business->refresh();

        return response()->json([
            'success' => true,
            'status' => $payment->status,
            'is_paid' => $payment->isPaid(),
            'credits_balance' => (int) ($business->ai_credits_balance ?? 0),
            'order_number' => $payment->order_number,
            'qr_url' => $payment->gateway_qr_url,
            'pay_code' => $payment->gateway_pay_code,
            'message' => $payment->isPaid() ? 'Pembayaran berhasil! Saldo kredit telah ditambahkan.' : 'Menunggu pembayaran...',
        ]);
    }

    /**
     * Generator nomor pesanan topup unik.
     */
    private function nextTopupOrderNumber(): string
    {
        $prefix = 'TOP-' . date('Ym') . '-';
        $latest = DB::table('subscription_payments')
            ->where('order_number', 'LIKE', $prefix . '%')
            ->orderByDesc('order_number')
            ->value('order_number');

        $seq = 1;
        if ($latest && preg_match('/-(\d+)$/', $latest, $matches)) {
            $seq = ((int) $matches[1]) + 1;
        }

        return $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }
}
