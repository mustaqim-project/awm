<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessTypeTemplate;
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
                if (str_starts_with($category, 'pcat_')) {
                    $slug = substr($category, 5);
                    $q->whereHas('category', fn($cq) => $cq->where('slug', $slug));
                } else {
                    $q->whereHas('business', function ($bq) use ($category): void {
                        $bq->where('industry_category', $category)
                            ->orWhere('template_code', 'like', "{$category}%");
                    })->orWhereHas('category', function ($cq) use ($category): void {
                        $cq->where('name', 'like', "%{$category}%");
                    });
                }
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
     * Build category count data for filter pills and marketplace grid.
     * Strictly based on COOCA's real database industry templates and active product categories.
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

        $iconMap = [
            'fnb_resto' => 'utensils',
            'fnb_cafe' => 'coffee',
            'fnb_bakery' => 'cake',
            'fnb_frozen_food' => 'snowflake',
            'fnb_catering' => 'soup',
            'fnb_catering_diet' => 'salad',
            'fnb_cloud_kitchen' => 'chef-hat',
            'mfg_garment' => 'shirt',
            'mfg_tailor_custom' => 'scissors',
            'mfg_printing' => 'printer',
            'mfg_furniture' => 'hammer',
            'mfg_craft' => 'palette',
            'mfg_cosmetics' => 'sparkles',
            'mfg_precision' => 'cog',
            'retail_reseller' => 'store',
            'retail_pharmacy' => 'heart-pulse',
            'service_workshop' => 'wrench',
            'service_autodetailing' => 'car',
            'service_barbershop' => 'scissors',
            'service_laundry' => 'droplets',
            'service_agency' => 'briefcase',
            'service_contractor' => 'hard-hat',
            'service_event' => 'calendar',
            'agri_farming' => 'wheat',
            'distributor_fmcg' => 'truck',
        ];

        $categories = [];

        // 1. Ambil dari template resmi COOCA di database (tabel business_type_templates)
        $templates = BusinessTypeTemplate::orderBy('id')->get();
        if ($templates->isNotEmpty()) {
            foreach ($templates as $tmpl) {
                $categories[$tmpl->code] = [
                    'label' => $tmpl->name,
                    'icon'  => $iconMap[$tmpl->code] ?? 'layers',
                    'count' => Business::where($baseScope)
                        ->where(fn($q) => $q->where('template_code', 'like', $tmpl->code . '%')
                            ->orWhere('industry_category', $tmpl->industry_category))
                        ->count(),
                ];
            }
        }

        // 2. Jika ada kategori produk nyata dari merchant dengan produk aktif di website
        $realProductCategories = ProductCategory::whereHas('products', function ($q) use ($baseScope): void {
            $q->where('is_active', true)
                ->where('show_in_website', true)
                ->whereHas('business', $baseScope);
        })
        ->withCount(['products' => function ($q) use ($baseScope): void {
            $q->where('is_active', true)
                ->where('show_in_website', true)
                ->whereHas('business', $baseScope);
        }])
        ->orderByDesc('products_count')
        ->take(10)
        ->get();

        foreach ($realProductCategories as $pCat) {
            $key = 'pcat_' . $pCat->slug;
            if (!isset($categories[$key])) {
                $categories[$key] = [
                    'label' => $pCat->name,
                    'icon'  => 'package',
                    'count' => $pCat->products_count,
                    'is_product_category' => true,
                ];
            }
        }

        // Fallback jika database template belum di-seed
        if (empty($categories)) {
            $defaultSectors = [
                'fnb_resto' => ['label' => 'Restoran & Rumah Makan', 'icon' => 'utensils'],
                'fnb_cafe' => ['label' => 'Coffee Shop & Cafe', 'icon' => 'coffee'],
                'mfg_garment' => ['label' => 'Konveksi & Garment', 'icon' => 'shirt'],
                'retail_reseller' => ['label' => 'Toko Ritel & Kelontong', 'icon' => 'store'],
                'service_workshop' => ['label' => 'Bengkel Mobil & Motor', 'icon' => 'wrench'],
                'service_laundry' => ['label' => 'Laundry Kiloan & Satuan', 'icon' => 'droplets'],
                'service_agency' => ['label' => 'Jasa & Studio Kreatif', 'icon' => 'briefcase'],
                'agri_farming' => ['label' => 'Pertanian & Peternakan', 'icon' => 'wheat'],
            ];

            foreach ($defaultSectors as $code => $data) {
                $categories[$code] = [
                    'label' => $data['label'],
                    'icon'  => $data['icon'],
                    'count' => Business::where($baseScope)->where('template_code', 'like', $code . '%')->count(),
                ];
            }
        }

        return $categories;
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
