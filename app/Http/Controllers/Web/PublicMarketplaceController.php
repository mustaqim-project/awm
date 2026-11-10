<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Public Marketplace Controller - aggregates storefronts into a unified marketplace.
 *
 * All queries are scoped to publicly discoverable businesses only.
 * No tenant-specific data is exposed without proper scope.
 */
final class PublicMarketplaceController extends Controller
{
    /**
     * Marketplace homepage - featured stores, popular products, categories.
     */
    public function index(Request $request): View
    {
        // Featured stores (discoverable, with active web products, non-test)
        $featuredStores = Business::publicListed()
            ->where(function ($sub): void {
                $sub->whereHas('landingPage', fn($lq) => $lq->where('is_published', true))
                    ->orWhereHas('storeSetting', fn($sq) => $sq->where('is_storefront_enabled', true)->where('is_discoverable', true));
            })
            ->whereHas('products', fn($q) => $q->where('is_active', true)->where('show_in_website', true))
            ->with(['landingPage', 'storeSetting'])
            ->withCount(['products' => fn($q) => $q->where('is_active', true)->where('show_in_website', true)])
            ->orderByDesc('created_at')
            ->take(8)
            ->get();

        // Popular products across all discoverable stores (Pure real data schema)
        $popularProducts = Product::where('is_active', true)
            ->where('show_in_website', true)
            ->whereHas('business', function ($q): void {
                $q->publicListed()
                    ->where(function ($sub): void {
                        $sub->whereHas('landingPage', fn($lq) => $lq->where('is_published', true))
                            ->orWhereHas('storeSetting', fn($sq) => $sq->where('is_storefront_enabled', true)->where('is_discoverable', true));
                    });
            })
            ->with(['business.storeSetting', 'business.landingPage', 'category'])
            ->latest()
            ->take(30)
            ->get();

        // Category aggregation
        $categories = $this->buildCategoryData();

        // Popular search keywords dynamically derived from active category sectors (zero dummy data)
        $popularKeywords = collect($categories)->pluck('label')->take(8)->all();

        // Statistics (Only counting public, verified, non-test stores and products)
        $totalStores = Business::publicListed()
            ->where(function ($sub): void {
                $sub->whereHas('landingPage', fn($lq) => $lq->where('is_published', true))
                    ->orWhereHas('storeSetting', fn($sq) => $sq->where('is_storefront_enabled', true)->where('is_discoverable', true));
            })
            ->count();

        $totalProducts = Product::where('is_active', true)
            ->where('show_in_website', true)
            ->whereHas('business', function ($q): void {
                $q->publicListed()
                    ->where(function ($sub): void {
                        $sub->whereHas('landingPage', fn($lq) => $lq->where('is_published', true))
                            ->orWhereHas('storeSetting', fn($sq) => $sq->where('is_storefront_enabled', true)->where('is_discoverable', true));
                    });
            })
            ->count();

        return view('public.marketplace.index', compact(
            'featuredStores',
            'popularProducts',
            'categories',
            'popularKeywords',
            'totalStores',
            'totalProducts'
        ));
    }

    /**
     * Marketplace-wide product search across all discoverable stores.
     */
    public function search(Request $request): View|\Illuminate\Http\JsonResponse
    {
        $search = trim((string) $request->query('q', ''));
        $category = trim((string) $request->query('kategori', ''));
        $priceRange = trim((string) $request->query('harga', ''));
        $minPrice = $request->filled('min_harga') ? (float) str_replace(['.', ','], '', (string) $request->query('min_harga')) : null;
        $maxPrice = $request->filled('max_harga') ? (float) str_replace(['.', ','], '', (string) $request->query('max_harga')) : null;
        $sort = trim((string) $request->query('urut', 'terbaru'));
        $type = trim((string) $request->query('tipe', ''));

        $query = Product::where('is_active', true)
            ->where('show_in_website', true)
            ->whereHas('business', function ($q): void {
                $q->publicListed()
                    ->where(function ($sub): void {
                        $sub->whereHas('landingPage', fn($lq) => $lq->where('is_published', true))
                            ->orWhereHas('storeSetting', fn($sq) => $sq->where('is_storefront_enabled', true)->where('is_discoverable', true));
                    });
            })
            ->with(['business.storeSetting', 'business.landingPage', 'category']);

        // Search with escaped LIKE wildcards (Security fix)
        if ($search !== '') {
            $escapedSearch = str_replace(['%', '_'], ['\%', '\_'], $search);
            $query->where(function ($q) use ($escapedSearch): void {
                $q->where('name', 'like', "%{$escapedSearch}%")
                    ->orWhere('description', 'like', "%{$escapedSearch}%")
                    ->orWhereHas('business', fn($bq) => $bq->where('name', 'like', "%{$escapedSearch}%"));
            });
        }

        // Category filter
        if ($category !== '' && $category !== 'semua') {
            $query->where(function ($q) use ($category): void {
                $q->whereHas('business', function ($bq) use ($category): void {
                    $bq->where('industry_category', $category)
                        ->orWhere('template_code', 'like', "{$category}%");
                })->orWhereHas('category', function ($cq) use ($category): void {
                    $cq->where('name', 'like', "%{$category}%");
                });
            });
        }

        // Custom price range takes precedence if provided, otherwise check preset
        if ($minPrice !== null && $minPrice > 0) {
            $query->where('selling_price', '>=', $minPrice);
        }
        if ($maxPrice !== null && $maxPrice > 0) {
            $query->where('selling_price', '<=', $maxPrice);
        }
        if ($minPrice === null && $maxPrice === null && $priceRange !== '') {
            match ($priceRange) {
                'murah'  => $query->where('selling_price', '<', 50000),
                'sedang' => $query->whereBetween('selling_price', [50000, 200000]),
                'mahal'  => $query->where('selling_price', '>', 200000),
                default  => null,
            };
        }

        // Product Type Filter
        if ($type === 'goods') {
            $query->where('type', Product::TYPE_GOODS);
        } elseif ($type === 'service') {
            $query->where('type', Product::TYPE_SERVICE);
        } elseif ($type === 'preorder') {
            $query->where('is_preorder', true);
        }

        // Sort
        match ($sort) {
            'harga_rendah' => $query->orderBy('selling_price', 'asc'),
            'harga_tinggi' => $query->orderBy('selling_price', 'desc'),
            'nama'         => $query->orderBy('name', 'asc'),
            'terpopuler'   => $query->orderByDesc('selling_price'),
            default        => $query->latest(),
        };

        $products = $query->paginate(24)->withQueryString();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'total' => $products->total(),
                'data' => collect($products->items())->map(function (Product $product): array {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'slug' => $product->slug,
                        'price' => (float) $product->selling_price,
                        'formatted_price' => 'Rp ' . number_format((float) $product->selling_price, 0, ',', '.'),
                        'image_url' => $product->image_url,
                        'category' => $product->category?->name ?? 'Produk',
                        'business_name' => $product->business?->name,
                        'business_slug' => $product->business?->slug,
                        'city' => $product->business?->storeSetting?->city ?? 'Indonesia',
                        'url' => url('/' . ($product->business?->slug ?? 'toko') . '/produk/' . ($product->slug ?: $product->id)),
                    ];
                })->all(),
            ]);
        }

        $categories = $this->buildCategoryData();

        return view('public.marketplace.search', compact(
            'products',
            'search',
            'category',
            'priceRange',
            'minPrice',
            'maxPrice',
            'sort',
            'type',
            'categories'
        ));
    }

    /**
     * Build category count data for filter pills and sidebar tree (20 Shopee-style categories).
     *
     * @return array<string, array<string, mixed>>
     */
    private function buildCategoryData(): array
    {
        $baseScope = fn($q) => $q->publicListed()
            ->where(function ($sub): void {
                $sub->whereHas('landingPage', fn($lq) => $lq->where('is_published', true))
                    ->orWhereHas('storeSetting', fn($sq) => $sq->where('is_storefront_enabled', true)->where('is_discoverable', true));
            });

        return [
            'fnb' => [
                'label' => 'Makanan & Minuman',
                'icon'  => 'utensils',
                'count' => Business::where($baseScope)
                    ->where(fn($q) => $q->where('industry_category', 'fnb')->orWhere('template_code', 'like', 'fnb%'))
                    ->count(),
            ],
            'fashion' => [
                'label' => 'Pakaian & Fashion',
                'icon'  => 'shirt',
                'count' => Business::where($baseScope)
                    ->where(fn($q) => $q->where('industry_category', 'fashion')->orWhere('template_code', 'like', 'fashion%'))
                    ->count(),
            ],
            'beauty' => [
                'label' => 'Perawatan & Kecantikan',
                'icon'  => 'sparkles',
                'count' => Business::where($baseScope)
                    ->where(fn($q) => $q->where('industry_category', 'beauty')->orWhere('template_code', 'like', 'beauty%'))
                    ->count(),
            ],
            'retail' => [
                'label' => 'Ritel & Kelontong',
                'icon'  => 'store',
                'count' => Business::where($baseScope)
                    ->where(fn($q) => $q->where('industry_category', 'retail')->orWhere('template_code', 'like', 'retail%'))
                    ->count(),
            ],
            'electronics' => [
                'label' => 'Elektronik & Gadget',
                'icon'  => 'tv',
                'count' => Business::where($baseScope)
                    ->where(fn($q) => $q->where('industry_category', 'electronics')->orWhere('template_code', 'like', 'elec%'))
                    ->count(),
            ],
            'computer' => [
                'label' => 'Komputer & Aksesoris',
                'icon'  => 'laptop',
                'count' => Business::where($baseScope)
                    ->where(fn($q) => $q->where('industry_category', 'computer')->orWhere('template_code', 'like', 'comp%'))
                    ->count(),
            ],
            'handphone' => [
                'label' => 'Handphone & Gadget',
                'icon'  => 'smartphone',
                'count' => Business::where($baseScope)
                    ->where(fn($q) => $q->where('industry_category', 'gadget')->orWhere('template_code', 'like', 'phone%'))
                    ->count(),
            ],
            'shoes' => [
                'label' => 'Sepatu & Sandal',
                'icon'  => 'footprints',
                'count' => Business::where($baseScope)
                    ->where(fn($q) => $q->where('industry_category', 'shoes')->orWhere('template_code', 'like', 'shoes%'))
                    ->count(),
            ],
            'bags' => [
                'label' => 'Tas & Dompet',
                'icon'  => 'shopping-bag',
                'count' => Business::where($baseScope)
                    ->where(fn($q) => $q->where('industry_category', 'bags')->orWhere('template_code', 'like', 'bag%'))
                    ->count(),
            ],
            'accessories' => [
                'label' => 'Aksesoris & Jam',
                'icon'  => 'watch',
                'count' => Business::where($baseScope)
                    ->where(fn($q) => $q->where('industry_category', 'accessories')->orWhere('template_code', 'like', 'acc%'))
                    ->count(),
            ],
            'health' => [
                'label' => 'Kesehatan & Apotek',
                'icon'  => 'heart-pulse',
                'count' => Business::where($baseScope)
                    ->where(fn($q) => $q->where('industry_category', 'health')->orWhere('template_code', 'like', 'health%')->orWhere('template_code', 'like', 'pharma%'))
                    ->count(),
            ],
            'home' => [
                'label' => 'Perlengkapan Rumah',
                'icon'  => 'home',
                'count' => Business::where($baseScope)
                    ->where(fn($q) => $q->where('industry_category', 'home')->orWhere('template_code', 'like', 'home%'))
                    ->count(),
            ],
            'workshop' => [
                'label' => 'Bengkel & Otomotif',
                'icon'  => 'wrench',
                'count' => Business::where($baseScope)
                    ->where(fn($q) => $q->where('industry_category', 'workshop')->orWhere('template_code', 'like', 'workshop%')->orWhere('template_code', 'like', 'bengkel%'))
                    ->count(),
            ],
            'hobby' => [
                'label' => 'Hobi & Olahraga',
                'icon'  => 'trophy',
                'count' => Business::where($baseScope)
                    ->where(fn($q) => $q->where('industry_category', 'hobby')->orWhere('template_code', 'like', 'hobby%'))
                    ->count(),
            ],
            'baby' => [
                'label' => 'Ibu & Bayi',
                'icon'  => 'baby',
                'count' => Business::where($baseScope)
                    ->where(fn($q) => $q->where('industry_category', 'baby')->orWhere('template_code', 'like', 'baby%'))
                    ->count(),
            ],
            'service' => [
                'label' => 'Jasa & Layanan',
                'icon'  => 'briefcase',
                'count' => Business::where($baseScope)
                    ->where(fn($q) => $q->where('industry_category', 'service')->orWhere('template_code', 'like', 'service%'))
                    ->count(),
            ],
            'agriculture' => [
                'label' => 'Pertanian & Ternak',
                'icon'  => 'wheat',
                'count' => Business::where($baseScope)
                    ->where(fn($q) => $q->where('industry_category', 'agriculture')->orWhere('template_code', 'like', 'agri%'))
                    ->count(),
            ],
            'craft' => [
                'label' => 'Kriya & Kerajinan',
                'icon'  => 'palette',
                'count' => Business::where($baseScope)
                    ->where(fn($q) => $q->where('industry_category', 'craft')->orWhere('template_code', 'like', 'craft%'))
                    ->count(),
            ],
            'laundry' => [
                'label' => 'Laundry & Cuci',
                'icon'  => 'droplets',
                'count' => Business::where($baseScope)
                    ->where(fn($q) => $q->where('industry_category', 'laundry')->orWhere('template_code', 'like', 'laundry%'))
                    ->count(),
            ],
            'manufacture' => [
                'label' => 'Percetakan & Produksi',
                'icon'  => 'printer',
                'count' => Business::where($baseScope)
                    ->where(fn($q) => $q->where('industry_category', 'manufacture')->orWhere('template_code', 'like', 'mfg%')->orWhere('template_code', 'like', 'print%'))
                    ->count(),
            ],
        ];
    }

    /**
     * Dedicated Discoverable Businesses Directory (/marketplace/businesses).
     */
    public function businesses(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $query = Business::publicListed()
            ->whereHas('storeSetting', function ($q): void {
                $q->where('is_storefront_enabled', true)->where('is_discoverable', true);
            })
            ->with(['storeSetting', 'landingPage'])
            ->withCount(['products' => fn($q) => $q->where('is_active', true)]);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $stores = $query->orderByDesc('products_count')->paginate(18)->withQueryString();

        return view('public.marketplace.businesses', compact('stores', 'search'));
    }

    /**
     * Dedicated Discoverable Marketplace Products (/marketplace/products).
     */
    public function products(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $query = Product::where('is_active', true)
            ->where('show_in_website', true)
            ->whereHas('business', function ($q): void {
                $q->publicListed()->whereHas('storeSetting', function ($sq): void {
                    $sq->where('is_storefront_enabled', true)->where('is_discoverable', true);
                });
            })
            ->with(['business.storeSetting', 'category']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $products = $query->latest()->paginate(24)->withQueryString();

        return view('public.marketplace.products', compact('products', 'search'));
    }

    /**
     * Marketplace Categories Explorer (/marketplace/categories).
     */
    public function categories(Request $request): View
    {
        return view('public.marketplace.categories');
    }

    /**
     * Marketplace Locations Directory (/marketplace/locations).
     */
    public function locations(Request $request): View
    {
        return view('public.marketplace.locations');
    }
}
