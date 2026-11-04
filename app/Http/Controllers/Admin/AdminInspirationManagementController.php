<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Ai\Inspiration\Adapters\PexelsAdapter;
use App\Domain\Ai\Inspiration\Adapters\PixabayAdapter;
use App\Domain\Ai\Inspiration\Adapters\PinterestAdapter;
use App\Domain\Ai\Inspiration\Adapters\UnsplashAdapter;
use App\Domain\Ai\Inspiration\Services\ExternalInspirationSearchService;
use App\Http\Controllers\Controller;
use App\Models\AiStudio\InspirationAsset;
use App\Models\AiStudio\InspirationCategory;
use App\Models\AiStudio\InspirationCollection;
use App\Models\AiStudio\InspirationItem;
use App\Models\AiStudio\InspirationSource;
use App\Models\AiStudio\InspirationSourceCredential;
use App\Models\AiStudio\InspirationSyncLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

final class AdminInspirationManagementController extends Controller implements HasMiddleware
{
    public function __construct(
        private ExternalInspirationSearchService $searchService,
        private PexelsAdapter $pexelsAdapter,
        private PixabayAdapter $pixabayAdapter,
        private UnsplashAdapter $unsplashAdapter,
        private PinterestAdapter $pinterestAdapter
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('require.admin.permission:admin.ai_studio.sources', only: [
                'index',
                'providers',
                'saveProviderCredentials',
                'testProviderConnection',
                'categories',
                'storeCategory',
                'updateCategory',
                'destroyCategory',
                'collections',
                'storeCollection',
                'updateCollection',
                'destroyCollection',
                'moderateAsset',
            ]),
        ];
    }

    /**
     * 1. Layar Moderasi & Katalog Global Admin.
     */
    public function index(Request $request): View|JsonResponse
    {
        $status = (string) $request->input('status', 'all');
        $source = (string) $request->input('source', 'all');
        $query = trim((string) $request->input('q', ''));

        $itemsQuery = InspirationItem::query()
            ->with(['source', 'category', 'visualAnalysis']);

        if ($status !== 'all') {
            $itemsQuery->where('status', $status);
        }

        if ($source !== 'all') {
            $itemsQuery->whereHas('source', fn($q) => $q->where('code', $source));
        }

        if ($query !== '') {
            $itemsQuery->where(function ($q) use ($query): void {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('caption', 'like', "%{$query}%");
            });
        }

        $items = $itemsQuery->orderByDesc('created_at')->paginate(24)->withQueryString();

        $stats = [
            'total_items' => InspirationItem::count(),
            'curated_items' => InspirationItem::where('is_curated', true)->count(),
            'total_collections' => InspirationCollection::where('owner_type', 'global')->count(),
            'total_categories' => InspirationCategory::count(),
        ];

        $sources = InspirationSource::where('is_active', true)->orderBy('name')->get();
        $categories = InspirationCategory::orderBy('sort_order')->get();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'items' => $items, 'stats' => $stats]);
        }

        return view('admin.ai-studio.inspiration.index', compact(
            'items',
            'stats',
            'sources',
            'categories',
            'status',
            'source',
            'query'
        ));
    }

    /**
     * 2. Manajemen Provider Gambar Eksternal & Uji Koneksi API.
     */
    public function providers(Request $request): View|JsonResponse
    {
        $registered = [
            'pexels' => [
                'name' => 'Pexels Official API',
                'adapter' => $this->pexelsAdapter,
                'endpoint' => 'https://api.pexels.com/v1',
                'commercial' => 'Diizinkan (Pexels License)',
                'attribution' => 'Wajib dicantumkan',
                'rate_limit' => '200 req/jam (Starter) s/d 20.000 req/bulan',
                'cache_rule' => 'Standar memory/file cache',
            ],
            'pixabay' => [
                'name' => 'Pixabay Official API',
                'adapter' => $this->pixabayAdapter,
                'endpoint' => 'https://pixabay.com/api/',
                'commercial' => 'Diizinkan (Pixabay Content License)',
                'attribution' => 'Wajib dicantumkan',
                'rate_limit' => '100 req/menit (5.000 req/jam)',
                'cache_rule' => 'Wajib cache minimal 24 jam (Enforced)',
            ],
            'unsplash' => [
                'name' => 'Unsplash Official API',
                'adapter' => $this->unsplashAdapter,
                'endpoint' => 'https://api.unsplash.com',
                'commercial' => 'Diizinkan (Unsplash License, Non-Harvesting)',
                'attribution' => 'Wajib tautan profil fotografer + UTM Unsplash',
                'rate_limit' => '50 req/jam (Demo) s/d 5.000 req/jam (Production)',
                'cache_rule' => 'Hotlink API URL langsung + tracking unduhan',
            ],
            'pinterest' => [
                'name' => 'Pinterest Official API v5',
                'adapter' => $this->pinterestAdapter,
                'endpoint' => 'https://api.pinterest.com/v5',
                'commercial' => 'Referensi Visual Saja (Dilarang komersialisasi langsung)',
                'attribution' => 'Wajib tautan Pin sumber asli',
                'rate_limit' => '1.000 req/menit (OAuth)',
                'cache_rule' => 'Zero Scraping Enforced; cache metadata resmi',
            ],
        ];

        $providersData = [];
        foreach ($registered as $code => $info) {
            /** @var \App\Domain\Ai\Inspiration\Contracts\ExternalProviderAdapterInterface $ad */
            $ad = $info['adapter'];
            $sourceModel = InspirationSource::where('code', $code)->first();

            $hasActiveCred = false;
            if ($sourceModel !== null) {
                $hasActiveCred = $sourceModel->credentials()->where('status', 'active')->exists();
            }

            $providersData[$code] = [
                'code' => $code,
                'name' => $info['name'],
                'endpoint' => $info['endpoint'],
                'commercial' => $info['commercial'],
                'attribution' => $info['attribution'],
                'rate_limit' => $info['rate_limit'],
                'cache_rule' => $info['cache_rule'],
                'is_configured' => $ad->isConfigured(),
                'has_credentials' => $hasActiveCred,
                'is_active' => $sourceModel?->is_active ?? true,
                'last_synced_at' => $sourceModel?->last_synced_at?->format('d M Y, H:i') ?? 'Belum pernah',
            ];
        }

        $syncLogs = InspirationSyncLog::latest()->take(10)->get();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'providers' => $providersData, 'sync_logs' => $syncLogs]);
        }

        return view('admin.ai-studio.inspiration.providers', compact('providersData', 'syncLogs'));
    }

    /**
     * Simpan Kredensial API Provider Secara Terenkripsi (Backend Only).
     */
    public function saveProviderCredentials(Request $request, string $provider): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'api_key' => ['required', 'string', 'max:500'],
        ]);

        $source = InspirationSource::firstOrCreate(
            ['code' => $provider],
            [
                'name' => ucfirst($provider) . ' Official API',
                'driver_class' => 'App\\Domain\\Ai\\Inspiration\\Adapters\\' . ucfirst($provider) . 'Adapter',
                'is_active' => true,
            ]
        );

        $encrypted = Crypt::encryptString(trim($validated['api_key']));

        // Simpan atau perbarui kredensial
        InspirationSourceCredential::updateOrCreate(
            [
                'source_id' => $source->id,
                'credential_type' => 'api_key',
            ],
            [
                'credentials_encrypted' => $encrypted,
                'status' => 'active',
                'created_by' => $request->user()?->id,
            ]
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Kredensial untuk provider '{$provider}' berhasil disimpan dengan enkripsi AES-256.",
            ]);
        }

        return back()->with('success', "Kredensial untuk provider '{$provider}' berhasil diperbarui.");
    }

    /**
     * Uji Koneksi Live ke Provider.
     */
    public function testProviderConnection(string $provider): JsonResponse
    {
        $adapter = match ($provider) {
            'pexels' => $this->pexelsAdapter,
            'pixabay' => $this->pixabayAdapter,
            'unsplash' => $this->unsplashAdapter,
            'pinterest' => $this->pinterestAdapter,
            default => null,
        };

        if ($adapter === null) {
            return response()->json(['success' => false, 'message' => 'Provider tidak dikenal.'], 404);
        }

        $result = $adapter->validateConnection();

        // Catat log sinkronisasi
        InspirationSyncLog::create([
            'provider_code' => $provider,
            'operation' => 'connection_test',
            'status' => $result['success'] ? 'success' : 'failed',
            'request_count' => 1,
            'result_count' => $result['success'] ? 1 : 0,
            'error_message' => $result['success'] ? null : $result['message'],
            'started_at' => now(),
            'finished_at' => now(),
        ]);

        return response()->json($result);
    }

    /**
     * 3. Manajemen Kategori & Taksonomi Industri.
     */
    public function categories(Request $request): View|JsonResponse
    {
        $categories = InspirationCategory::query()
            ->with('parent')
            ->withCount('items')
            ->orderBy('sort_order')
            ->get();

        $parentOptions = InspirationCategory::whereNull('parent_id')->orderBy('name')->get();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'categories' => $categories]);
        }

        return view('admin.ai-studio.inspiration.categories', compact('categories', 'parentOptions'));
    }

    public function storeCategory(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'industry' => ['nullable', 'string', 'max:80'],
            'parent_id' => ['nullable', 'uuid', 'exists:inspiration_categories,id'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $slug = Str::slug($validated['name']);
        if (InspirationCategory::where('slug', $slug)->exists()) {
            $slug .= '-' . Str::lower(Str::random(4));
        }

        $cat = InspirationCategory::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'industry' => $validated['industry'] ?? null,
            'parent_id' => $validated['parent_id'] ?? null,
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'category' => $cat], 201);
        }

        return back()->with('success', "Kategori '{$cat->name}' berhasil ditambahkan.");
    }

    public function updateCategory(Request $request, string $id): JsonResponse|RedirectResponse
    {
        /** @var InspirationCategory $category */
        $category = InspirationCategory::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'industry' => ['nullable', 'string', 'max:80'],
            'parent_id' => ['nullable', 'uuid', 'exists:inspiration_categories,id'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $category->update([
            'name' => $validated['name'],
            'industry' => $validated['industry'] ?? null,
            'parent_id' => $validated['parent_id'] ?? null,
            'sort_order' => (int) ($validated['sort_order'] ?? $category->sort_order),
            'is_active' => (bool) ($validated['is_active'] ?? $category->is_active),
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'category' => $category]);
        }

        return back()->with('success', "Kategori '{$category->name}' berhasil diperbarui.");
    }

    public function destroyCategory(string $id): JsonResponse|RedirectResponse
    {
        $category = InspirationCategory::findOrFail($id);
        $name = $category->name;
        $category->delete();

        return back()->with('success', "Kategori '{$name}' telah dihapus.");
    }

    /**
     * 4. Manajemen Koleksi Global Kurasi Admin.
     */
    public function collections(Request $request): View|JsonResponse
    {
        $collections = InspirationCollection::query()
            ->where('owner_type', InspirationCollection::OWNER_GLOBAL)
            ->withCount('items')
            ->orderByDesc('is_featured')
            ->orderBy('title')
            ->get();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'collections' => $collections]);
        }

        return view('admin.ai-studio.inspiration.collections', compact('collections'));
    }

    public function storeCollection(Request $request): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_featured' => ['nullable', 'boolean'],
            'cover_image_url' => ['nullable', 'string', 'max:1000'],
        ]);

        $coll = InspirationCollection::create([
            'owner_type' => InspirationCollection::OWNER_GLOBAL,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'is_featured' => (bool) ($validated['is_featured'] ?? false),
            'cover_image_url' => $validated['cover_image_url'] ?? null,
            'visibility' => InspirationCollection::VISIBILITY_PUBLIC,
            'status' => InspirationCollection::STATUS_ACTIVE,
            'created_by' => $user?->id,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'collection' => $coll], 201);
        }

        return back()->with('success', "Koleksi global '{$coll->title}' berhasil dibuat.");
    }

    public function updateCollection(Request $request, string $id): JsonResponse|RedirectResponse
    {
        $coll = InspirationCollection::findOrFail($id);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_featured' => ['nullable', 'boolean'],
            'cover_image_url' => ['nullable', 'string', 'max:1000'],
            'status' => ['nullable', 'in:active,archived'],
        ]);

        $coll->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'is_featured' => (bool) ($validated['is_featured'] ?? false),
            'cover_image_url' => $validated['cover_image_url'] ?? $coll->cover_image_url,
            'status' => $validated['status'] ?? $coll->status,
        ]);

        return back()->with('success', "Koleksi '{$coll->title}' berhasil diperbarui.");
    }

    public function destroyCollection(string $id): JsonResponse|RedirectResponse
    {
        $coll = InspirationCollection::findOrFail($id);
        $name = $coll->title;
        $coll->delete();

        return back()->with('success', "Koleksi '{$name}' telah dihapus.");
    }

    /**
     * Moderasi Aset Kurasi (Approve / Reject / Feature).
     */
    public function moderateAsset(Request $request, string $id): JsonResponse
    {
        $status = (string) $request->input('status', $request->input('action', 'approved'));

        $asset = InspirationAsset::find($id);
        if ($asset !== null) {
            $normalizedStatus = match ($status) {
                'approve', 'approved' => InspirationAsset::MODERATION_APPROVED,
                'reject', 'rejected' => InspirationAsset::MODERATION_REJECTED,
                'flag', 'flagged' => InspirationAsset::MODERATION_FLAGGED,
                default => InspirationAsset::MODERATION_PENDING,
            };
            $asset->update(['moderation_status' => $normalizedStatus]);

            return response()->json([
                'success' => true,
                'message' => "Aset '{$asset->title}' berhasil dimoderasi ({$normalizedStatus}).",
                'asset' => $asset,
            ]);
        }

        /** @var InspirationItem $item */
        $item = InspirationItem::findOrFail($id);

        match ($status) {
            'approve', 'approved' => $item->update(['status' => 'active']),
            'reject', 'rejected' => $item->update(['status' => 'archived']),
            'feature' => $item->update(['is_curated' => true]),
            'unfeature' => $item->update(['is_curated' => false]),
            default => null,
        };

        return response()->json([
            'success' => true,
            'message' => "Aset '{$item->title}' berhasil dimoderasi ({$status}).",
            'item' => $item,
        ]);
    }
}
