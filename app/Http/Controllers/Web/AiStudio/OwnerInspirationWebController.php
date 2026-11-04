<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\AiStudio;

use App\Domain\Ai\Inspiration\Adapters\PexelsAdapter;
use App\Domain\Ai\Inspiration\Adapters\PixabayAdapter;
use App\Domain\Ai\Inspiration\Adapters\PinterestAdapter;
use App\Domain\Ai\Inspiration\Adapters\UnspirationAdapter;
use App\Domain\Ai\Inspiration\Adapters\UnsplashAdapter;
use App\Domain\Ai\Inspiration\RightsPolicyService;
use App\Domain\Ai\Inspiration\Services\ExternalInspirationSearchService;
use App\Domain\Storage\TenantStorage;
use App\Http\Controllers\Controller;
use App\Models\AiStudio\BusinessInspirationGenerationAsset;
use App\Models\AiStudio\InspirationAsset;
use App\Models\AiStudio\InspirationCategory;
use App\Models\AiStudio\InspirationCollection;
use App\Models\AiStudio\InspirationCollectionItem;
use App\Models\AiStudio\InspirationFavorite;
use App\Models\AiStudio\InspirationItem;
use App\Models\AiStudio\InspirationUsageLog;
use App\Models\StorageFile;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

final class OwnerInspirationWebController extends Controller implements HasMiddleware
{
    /**
     * Taksonomi Sektor Multi-Industri Lengkap COOCA untuk AI Studio.
     */
    public const INDUSTRY_SECTORS = [
        'fnb' => ['label' => 'Kuliner & F&B', 'icon' => 'utensils'],
        'fashion' => ['label' => 'Fashion & Konveksi', 'icon' => 'shirt'],
        'beauty' => ['label' => 'Kecantikan & Skincare', 'icon' => 'sparkles'],
        'automotive' => ['label' => 'Bengkel & Otomotif', 'icon' => 'wrench'],
        'service' => ['label' => 'Jasa, Salon & Laundry', 'icon' => 'scissors'],
        'retail' => ['label' => 'Ritel & Toko Kelontong', 'icon' => 'shopping-bag'],
        'pharmacy' => ['label' => 'Apotek, Farmasi & Herbal', 'icon' => 'pill'],
        'printing' => ['label' => 'Percetakan & Kemasan', 'icon' => 'printer'],
        'furniture' => ['label' => 'Furniture & Kerajinan', 'icon' => 'armchair'],
        'contractor' => ['label' => 'Kontraktor & Bangunan', 'icon' => 'hard-hat'],
        'event' => ['label' => 'Event & Wedding Organizer', 'icon' => 'calendar-heart'],
        'distributor' => ['label' => 'Distributor FMCG & Grosir', 'icon' => 'truck'],
        'agriculture' => ['label' => 'Pertanian & Peternakan', 'icon' => 'sprout'],
        'general' => ['label' => 'Umum & Multi-Sektor', 'icon' => 'layout-grid'],
    ];

    public function __construct(
        private ExternalInspirationSearchService $searchService,
        private PexelsAdapter $pexelsAdapter,
        private PixabayAdapter $pixabayAdapter,
        private UnsplashAdapter $unsplashAdapter,
        private PinterestAdapter $pinterestAdapter,
        private RightsPolicyService $rightsPolicyService
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
                'search',
                'categories',
                'collections',
                'showCollection',
                'favorites',
                'myLibrary',
            ]),
            new Middleware('require.permission:ai_studio.manage', only: [
                'storeCollection',
                'addItemToCollection',
                'removeItemFromCollection',
                'toggleFavorite',
                'uploadAsset',
                'useAsReference',
            ]),
        ];
    }

    /**
     * 1. Layar Utama Discovery & Pencarian Terpadu (Curated, External, & Personal).
     */
    public function index(Request $request): View|JsonResponse
    {
        $business = Context::requireBusiness();
        $user = $request->user();

        $query = trim((string) $request->input('q', $request->input('search', '')));
        $sourceFilter = (string) $request->input('source', 'all');
        $categoryFilter = (string) $request->input('category', 'all');
        $industryFilter = (string) $request->input('industry', 'all');
        $aspectRatioFilter = (string) $request->input('aspect_ratio', 'all');

        $searchResults = $this->searchService->search(
            query: $query,
            filters: [
                'source' => $sourceFilter,
                'category' => $categoryFilter,
                'industry' => $industryFilter,
                'aspect_ratio' => $aspectRatioFilter,
                'commercial_only' => (bool) $request->boolean('commercial_only', false),
                'page' => max(1, (int) $request->input('page', 1)),
                'per_page' => 24,
            ],
            business: $business,
            user: $user
        );

        $categories = InspirationCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $featuredCollections = InspirationCollection::query()
            ->where(function ($q) use ($business): void {
                $q->where('owner_type', InspirationCollection::OWNER_GLOBAL)
                    ->orWhere('business_id', $business->id);
            })
            ->where('status', 'active')
            ->where('is_featured', true)
            ->withCount('items')
            ->take(4)
            ->get();

        $favoritesCount = InspirationFavorite::where('business_id', $business->id)->count();
        $myCollectionsCount = InspirationCollection::where('business_id', $business->id)->count();

        // Daftar ID favorit milik bisnis aktif untuk mempermudah render tombol hati aktif di frontend
        $favoritedAssetIds = InspirationFavorite::where('business_id', $business->id)
            ->pluck('asset_id')
            ->filter()
            ->toArray();
        $favoritedExternalIds = InspirationFavorite::where('business_id', $business->id)
            ->pluck('external_id')
            ->filter()
            ->toArray();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'search_results' => $searchResults,
                'categories' => $categories,
                'providers' => $this->searchService->getRegisteredProviders(),
                'favorites_count' => $favoritesCount,
            ]);
        }

        $industrySectors = self::INDUSTRY_SECTORS;
        $recommendedIndustry = $business->getAiStudioIndustryTarget();
        $recommendedLabel = $business->getAiStudioIndustryLabel();

        return view('app.ai-studio.inspiration.index', compact(
            'searchResults',
            'categories',
            'featuredCollections',
            'business',
            'industrySectors',
            'recommendedIndustry',
            'recommendedLabel',
            'sourceFilter',
            'categoryFilter',
            'industryFilter',
            'aspectRatioFilter',
            'query',
            'favoritesCount',
            'myCollectionsCount',
            'favoritedAssetIds',
            'favoritedExternalIds'
        ));
    }

    /**
     * 2. Layar Daftar Kategori & Taksonomi Industri UMKM.
     */
    public function categories(Request $request): View|JsonResponse
    {
        $business = Context::requireBusiness();

        $categories = InspirationCategory::query()
            ->withCount(['items' => function ($q): void {
                $q->where('status', 'active');
            }])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $industrySectors = self::INDUSTRY_SECTORS;

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'categories' => $categories,
                'industry_sectors' => $industrySectors,
            ]);
        }

        return view('app.ai-studio.inspiration.categories', compact(
            'categories',
            'industrySectors',
            'business'
        ));
    }

    /**
     * 3. Layar Koleksi Inspirasi (Global Curated + Milik Bisnis Sendiri).
     */
    public function collections(Request $request): View|JsonResponse
    {
        $business = Context::requireBusiness();

        // Koleksi Global COOCA
        $globalCollections = InspirationCollection::query()
            ->global()
            ->active()
            ->withCount('items')
            ->orderByDesc('is_featured')
            ->orderBy('title')
            ->get();

        // Koleksi Pribadi Bisnis Aktif
        $myCollections = InspirationCollection::query()
            ->forBusiness($business->id)
            ->active()
            ->withCount('items')
            ->latest()
            ->get();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'global_collections' => $globalCollections,
                'my_collections' => $myCollections,
            ]);
        }

        return view('app.ai-studio.inspiration.collections', compact(
            'globalCollections',
            'myCollections',
            'business'
        ));
    }

    /**
     * Buat Koleksi Pribadi Baru untuk Bisnis Aktif.
     */
    public function storeCollection(Request $request): JsonResponse|RedirectResponse
    {
        $business = Context::requireBusiness();
        $user = $request->user();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:500'],
            'visibility' => ['nullable', 'in:public,private,unlisted'],
        ]);

        $collection = InspirationCollection::create([
            'owner_type' => InspirationCollection::OWNER_BUSINESS,
            'business_id' => $business->id,
            'user_id' => $user?->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'visibility' => $validated['visibility'] ?? InspirationCollection::VISIBILITY_PUBLIC,
            'status' => InspirationCollection::STATUS_ACTIVE,
            'is_featured' => false,
            'created_by' => $user?->id,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Koleksi inspirasi berhasil dibuat.',
                'collection' => $collection,
            ], 201);
        }

        return redirect()->route('ai-studio.inspiration.collections')
            ->with('success', "Koleksi '{$collection->title}' berhasil dibuat.");
    }

    /**
     * Lihat Rincian & Item dalam Koleksi Tertentu.
     */
    public function showCollection(string $id, Request $request): View|JsonResponse
    {
        $business = Context::requireBusiness();

        /** @var InspirationCollection $collection */
        $collection = InspirationCollection::query()
            ->with(['items', 'business', 'creator'])
            ->findOrFail($id);

        // Validasi Isolasi Tenant: Jika koleksi milik bisnis, harus milik bisnis aktif
        if ($collection->owner_type === InspirationCollection::OWNER_BUSINESS && $collection->business_id !== $business->id) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat koleksi privat bisnis lain.');
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'collection' => $collection,
                'items' => $collection->items,
            ]);
        }

        return view('app.ai-studio.inspiration.collection-detail', compact('collection', 'business'));
    }

    /**
     * Tambahkan Item Aset ke Koleksi Tertentu.
     */
    public function addItemToCollection(string $collectionId, Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        /** @var InspirationCollection $collection */
        $collection = InspirationCollection::query()->findOrFail($collectionId);

        // Hanya pemilik koleksi yang boleh menambahkan item
        if ($collection->owner_type === InspirationCollection::OWNER_BUSINESS && $collection->business_id !== $business->id) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $validated = $request->validate([
            'asset_type' => ['required', 'string', 'in:inspiration_item,external,generation_asset,user_upload,inspiration_asset'],
            'asset_id' => ['nullable', 'string', 'max:100'],
            'external_asset_id' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'image_url' => ['nullable', 'string', 'max:1000'],
            'source_name' => ['nullable', 'string', 'max:100'],
        ]);

        $itemCount = $collection->items()->count();

        $item = $collection->items()->create([
            'asset_type' => $validated['asset_type'],
            'asset_id' => $validated['asset_id'] ?? null,
            'external_asset_id' => $validated['external_asset_id'] ?? null,
            'sort_order' => $itemCount + 1,
            'metadata' => [
                'title' => $validated['title'] ?? 'Item Inspirasi',
                'image_url' => $validated['image_url'] ?? null,
                'source_name' => $validated['source_name'] ?? 'Katalog Visual',
                'added_at' => now()->toIso8601String(),
            ],
        ]);

        // Perbarui cover koleksi jika masih kosong
        if (empty($collection->cover_image_url) && !empty($validated['image_url'])) {
            $collection->update(['cover_image_url' => $validated['image_url']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Item berhasil disimpan ke koleksi.',
            'item' => $item,
        ]);
    }

    /**
     * Hapus Item dari Koleksi.
     */
    public function removeItemFromCollection(string $collectionId, string $itemId, Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        /** @var InspirationCollection $collection */
        $collection = InspirationCollection::query()->findOrFail($collectionId);

        if ($collection->owner_type === InspirationCollection::OWNER_BUSINESS && $collection->business_id !== $business->id) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $item = $collection->items()->findOrFail($itemId);
        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item berhasil dihapus dari koleksi.',
        ]);
    }

    /**
     * 4. Layar Daftar Aset Favorit Bisnis Aktif.
     */
    public function favorites(Request $request): View|JsonResponse
    {
        $business = Context::requireBusiness();

        $favorites = InspirationFavorite::query()
            ->where('business_id', $business->id)
            ->latest()
            ->paginate(24);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'favorites' => $favorites,
            ]);
        }

        return view('app.ai-studio.inspiration.favorites', compact('favorites', 'business'));
    }

    /**
     * Toggle status favorit untuk aset apa pun (Item Kurasi, External Pin, Output AI).
     */
    public function toggleFavorite(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $user = $request->user();

        $validated = $request->validate([
            'asset_type' => ['required', 'string', 'in:inspiration_item,external,generation_asset,user_upload,inspiration_asset'],
            'asset_id' => ['nullable', 'string', 'max:100'],
            'external_id' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'image_url' => ['nullable', 'string', 'max:1000'],
            'source_name' => ['nullable', 'string', 'max:100'],
        ]);

        $query = InspirationFavorite::query()->where('business_id', $business->id);

        if (!empty($validated['asset_id'])) {
            $query->where('asset_id', $validated['asset_id']);
        } elseif (!empty($validated['external_id'])) {
            $query->where('external_id', $validated['external_id']);
        } else {
            return response()->json(['success' => false, 'message' => 'Identifier aset tidak valid.'], 422);
        }

        $existing = $query->first();

        if ($existing) {
            $existing->delete();
            return response()->json([
                'success' => true,
                'is_favorite' => false,
                'message' => 'Dihapus dari daftar favorit.',
            ]);
        }

        InspirationFavorite::create([
            'business_id' => $business->id,
            'user_id' => $user?->id,
            'asset_type' => $validated['asset_type'],
            'asset_id' => $validated['asset_id'] ?? null,
            'external_id' => $validated['external_id'] ?? null,
            'metadata' => [
                'title' => $validated['title'] ?? 'Inspirasi Visual',
                'image_url' => $validated['image_url'] ?? null,
                'source_name' => $validated['source_name'] ?? 'Katalog Visual',
                'favorited_at' => now()->toIso8601String(),
            ],
        ]);

        // Telemetri
        InspirationUsageLog::record(
            businessId: $business->id,
            userId: $user?->id,
            action: InspirationUsageLog::ACTION_FAVORITE,
            source: $validated['asset_type'],
            assetReference: $validated['asset_id'] ?? $validated['external_id']
        );

        return response()->json([
            'success' => true,
            'is_favorite' => true,
            'message' => 'Ditambahkan ke favorit bisnis Anda.',
        ]);
    }

    /**
     * 5. Layar My Library (Koleksi Unggahan Pribadi & Hasil Generasi AI Bisnis).
     */
    public function myLibrary(Request $request): View|JsonResponse
    {
        $business = Context::requireBusiness();

        // Unggahan Personal
        $personalAssets = InspirationAsset::query()
            ->where('business_id', $business->id)
            ->latest()
            ->paginate(12, ['*'], 'assets_page');

        // Hasil Generasi AI Studio Bisnis
        $aiGenerations = BusinessInspirationGenerationAsset::query()
            ->where('business_id', $business->id)
            ->latest()
            ->paginate(12, ['*'], 'gen_page');

        $collections = InspirationCollection::query()
            ->forBusiness($business->id)
            ->active()
            ->latest()
            ->get();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'personal_assets' => $personalAssets,
                'ai_generations' => $aiGenerations,
                'collections' => $collections,
            ]);
        }

        return view('app.ai-studio.inspiration.my-library', compact(
            'personalAssets',
            'aiGenerations',
            'collections',
            'business'
        ));
    }

    /**
     * Unggah Foto Pribadi Bisnis ke Media Library (StorageFile) & InspirationAsset.
     */
    public function uploadAsset(Request $request): JsonResponse|RedirectResponse
    {
        $business = Context::requireBusiness();
        $user = $request->user();

        $request->validate([
            'image' => ['required', 'file', 'image', 'mimes:jpeg,png,webp', 'max:10240'], // Max 10MB
            'title' => ['nullable', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $file = $request->file('image');
        $title = trim((string) $request->input('title', $file->getClientOriginalName()));

        $slug = TenantStorage::slugForBusiness($business);
        $fileName = 'insp_upload_' . Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();
        $folder = "bisnis/{$slug}/inspiration/uploads";
        $path = $file->storeAs($folder, $fileName, 'public');

        $imageInfo = @getimagesize($file->getRealPath());
        $width = $imageInfo ? (int) $imageInfo[0] : 1024;
        $height = $imageInfo ? (int) $imageInfo[1] : 1024;

        // Daftarkan ke StorageFile Media Library COOCA
        $storageFile = StorageFile::create([
            'business_id' => $business->id,
            'user_id' => $user?->id,
            'owner_id' => $user?->id ?? $business->id,
            'file_name' => $fileName,
            'file_path' => $path,
            'disk' => 'public',
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'category' => 'inspiration_upload',
            'module' => 'ai_studio',
            'status' => 'active',
            'metadata' => [
                'width' => $width,
                'height' => $height,
                'original_name' => $file->getClientOriginalName(),
            ],
        ]);

        $asset = InspirationAsset::create([
            'source_type' => InspirationAsset::SOURCE_UPLOADED,
            'media_id' => $storageFile->id,
            'business_id' => $business->id,
            'user_id' => $user?->id,
            'title' => $title,
            'description' => $request->input('description'),
            'image_url' => Storage::disk('public')->url($path),
            'thumbnail_url' => Storage::disk('public')->url($path),
            'width' => $width,
            'height' => $height,
            'aspect_ratio' => $this->calculateAspectRatio($width, $height),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'author_name' => $business->name,
            'source_name' => 'Unggahan Bisnis',
            'license_name' => 'Milik Bisnis Sendiri',
            'commercial_usage_allowed' => true,
            'redistribution_allowed' => true,
            'storage_policy' => 'permanent',
            'moderation_status' => InspirationAsset::MODERATION_APPROVED,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Foto inspirasi berhasil diunggah ke koleksi pribadi Anda.',
                'asset' => $asset,
            ], 201);
        }

        return redirect()->route('ai-studio.inspiration.my-library')
            ->with('success', 'Foto inspirasi berhasil diunggah ke My Library.');
    }

    /**
     * 6. Layar Detail Inspirasi Visual & Ketentuan Lisensi Hak Cipta.
     */
    public function show(string $id, Request $request): View|JsonResponse
    {
        $business = Context::requireBusiness();

        // Kasus 1: External Provider Asset (ID diawali 'pexels_', 'pixabay_', 'unsplash_', 'pinterest_')
        if (str_contains($id, '_')) {
            [$providerCode, $externalId] = explode('_', $id, 2);

            $adapter = match ($providerCode) {
                'pexels' => $this->pexelsAdapter,
                'pixabay' => $this->pixabayAdapter,
                'unsplash' => $this->unsplashAdapter,
                'pinterest' => $this->pinterestAdapter,
                default => null,
            };

            if ($adapter !== null) {
                $details = $adapter->getAssetDetails($externalId);
                if ($details !== null) {
                    $item = (object) array_merge($details, [
                        'id' => $id,
                        'source' => (object) ['code' => $providerCode, 'name' => $adapter->getDisplayName()],
                        'category' => null,
                        'visualAnalysis' => (object) [
                            'aspect_ratio' => $details['aspect_ratio'] ?? '1:1',
                            'composition' => 'lifestyle',
                            'lighting' => 'studio_soft',
                            'color_palette' => [],
                            'extracted_style_prompt' => $details['caption'] ?? $details['title'],
                        ],
                        'is_favorited' => InspirationFavorite::where('business_id', $business->id)->where('external_id', $externalId)->exists(),
                    ]);

                    if ($request->wantsJson()) {
                        return response()->json(['success' => true, 'item' => $item]);
                    }

                    return view('app.ai-studio.inspiration.show', compact('item', 'business'));
                }
            }
        }

        // Kasus 2: Curated Catalog Item (InspirationItem)
        $curatedItem = InspirationItem::query()
            ->with(['source', 'category', 'visualAnalysis', 'trendScore'])
            ->find($id);

        if ($curatedItem !== null) {
            $curatedItem->is_favorited = InspirationFavorite::where('business_id', $business->id)->where('asset_id', $curatedItem->id)->exists();

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'item' => $curatedItem]);
            }

            return view('app.ai-studio.inspiration.show', ['item' => $curatedItem, 'business' => $business]);
        }

        // Kasus 3: Personal Inspiration Asset (InspirationAsset)
        $personalAsset = InspirationAsset::query()
            ->accessibleFor($business->id)
            ->findOrFail($id);

        $itemObj = (object) [
            'id' => $personalAsset->id,
            'title' => $personalAsset->title,
            'caption' => $personalAsset->description,
            'display_image_url' => $personalAsset->resolved_image_url,
            'thumbnail_url' => $personalAsset->thumbnail_url ?: $personalAsset->resolved_image_url,
            'source' => (object) ['code' => 'personal', 'name' => 'Koleksi Pribadi Bisnis'],
            'category' => null,
            'visualAnalysis' => (object) [
                'aspect_ratio' => $personalAsset->aspect_ratio,
                'composition' => 'custom',
                'lighting' => 'natural',
                'color_palette' => [],
                'extracted_style_prompt' => $personalAsset->title,
            ],
            'license_name' => $personalAsset->license_name,
            'commercial_use_allowed' => $personalAsset->commercial_usage_allowed,
            'author_name' => $personalAsset->author_name,
            'source_url' => $personalAsset->source_url,
            'attribution_text' => $personalAsset->attribution_text,
            'is_favorited' => InspirationFavorite::where('business_id', $business->id)->where('asset_id', $personalAsset->id)->exists(),
        ];

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'item' => $itemObj]);
        }

        return view('app.ai-studio.inspiration.show', ['item' => $itemObj, 'business' => $business]);
    }

    /**
     * 7. Alur "Gunakan Sebagai Referensi AI" (AC-002: Validasi Lisensi & Hak Penggunaan).
     */
    public function useAsReference(Request $request): JsonResponse|RedirectResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'image_url' => ['required', 'string', 'max:1000'],
            'title' => ['nullable', 'string', 'max:255'],
            'prompt_hint' => ['nullable', 'string', 'max:1000'],
            'style_prompt' => ['nullable', 'string', 'max:1000'],
            'source' => ['nullable', 'string', 'max:50'],
            'source_code' => ['nullable', 'string', 'max:50'],
            'commercial_allowed' => ['nullable', 'boolean'],
            'external_id' => ['nullable', 'string', 'max:255'],
            'asset_id' => ['nullable', 'string', 'max:255'],
            'aspect_ratio' => ['nullable', 'string', 'max:20'],
        ]);

        $sourceCode = (string) ($validated['source_code'] ?? $validated['source'] ?? 'cooca');
        $commercialAllowed = (bool) ($validated['commercial_allowed'] ?? true);
        $promptHint = (string) ($validated['prompt_hint'] ?? $validated['style_prompt'] ?? '');

        // Jika gambar dari Pinterest atau sumber non-komersial lain
        if ($sourceCode === 'pinterest' || !$commercialAllowed) {
            $msg = 'Gambar dari sumber ini dilindungi hak cipta eksklusif dan hanya boleh dijadikan ide/inspirasi visual manual, bukan dikirimkan langsung ke generator pihak ketiga.';
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        // Jika Unsplash, eksekusi download tracking endpoint wajib
        if ($sourceCode === 'unsplash') {
            $assetId = (string) ($validated['asset_id'] ?? $validated['external_id'] ?? '');
            if ($assetId !== '') {
                $this->unsplashAdapter->recordDownload($assetId);
            }
        }

        // Simpan sesi referensi aktif agar otomatis terpasang saat membuka halaman create proyek AI Studio
        session()->put('ai_studio_reference_preload', [
            'image_url' => $validated['image_url'],
            'title' => $validated['title'] ?? 'Referensi Gaya Visual',
            'prompt_hint' => $promptHint,
            'source_code' => $sourceCode,
            'aspect_ratio' => $validated['aspect_ratio'] ?? '1:1',
        ]);

        // Telemetri
        InspirationUsageLog::record(
            businessId: $business->id,
            userId: $request->user()?->id,
            action: InspirationUsageLog::ACTION_USE_AS_REFERENCE,
            source: $sourceCode,
            assetReference: $validated['image_url']
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'redirect_url' => route('ai-studio.create'),
                'message' => 'Referensi visual berhasil dipasang ke AI Studio.',
            ]);
        }

        return redirect()->route('ai-studio.create')
            ->with('info', 'Referensi visual telah dipasang. Anda dapat mulai menyusun variasi visual produk.');
    }

    /**
     * Endpoint API Autocomplete Realtime untuk Search Bar.
     */
    public function search(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $q = trim((string) $request->input('q', ''));

        $items = InspirationItem::query()
            ->where('status', 'active')
            ->when($q !== '', function ($query) use ($q): void {
                $query->where('title', 'like', "%{$q}%")
                      ->orWhere('caption', 'like', "%{$q}%");
            })
            ->with(['category', 'visualAnalysis'])
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $items,
        ]);
    }

    private function calculateAspectRatio(int $w, int $h): string
    {
        if ($w <= 0 || $h <= 0) return '1:1';
        $ratio = $w / $h;

        if ($ratio > 1.4) return '16:9';
        if ($ratio > 1.1) return '4:3';
        if ($ratio >= 0.9 && $ratio <= 1.1) return '1:1';
        if ($ratio >= 0.7) return '4:5';
        return '9:16';
    }
}
