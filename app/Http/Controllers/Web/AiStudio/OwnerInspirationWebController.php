<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\AiStudio;

use App\Http\Controllers\Controller;
use App\Models\AiStudio\InspirationCategory;
use App\Models\AiStudio\InspirationItem;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

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

    /**
     * Daftarkan middleware Controller-Level RBAC.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('require.permission:ai_studio.view', only: ['index', 'show', 'search']),
        ];
    }

    /**
     * Layar Katalog Inspirasi Viral & Referensi Visual Legal (Masonry Grid).
     */
    public function index(Request $request): View|JsonResponse
    {
        $business = Context::requireBusiness();

        $query = InspirationItem::query()
            ->with(['source', 'category', 'visualAnalysis', 'trendScore'])
            ->where('status', 'active');

        // Filter Kategori
        if ($request->filled('category')) {
            $categoryParam = (string) $request->input('category');
            $query->whereHas('category', function ($q) use ($categoryParam): void {
                $q->where('slug', $categoryParam)->orWhere('id', $categoryParam);
            });
        }

        // Filter Industri / Target Channel (Adaptive Multi-Industry Keyword & Tag Matcher)
        if ($request->filled('industry')) {
            $industryCode = strtolower(trim((string) $request->input('industry')));
            $industryKeywords = match ($industryCode) {
                'event' => ['event', 'wedding', 'nikah', 'dekorasi', 'undangan', 'hampers', 'souvenir', 'catering', 'pelaminan', 'mahar', 'cincin'],
                'fnb' => ['fnb', 'kuliner', 'makanan', 'minuman', 'cafe', 'kopi', 'restoran', 'coffee', 'food', 'beverage', 'snack', 'cake', 'sambal'],
                'fashion' => ['fashion', 'konveksi', 'baju', 'hijab', 'busana', 'pakaian', 'batik', 'distro', 'apparel', 'totebag', 'tas'],
                'beauty' => ['beauty', 'skincare', 'kosmetik', 'kecantikan', 'serum', 'bodycare', 'makeup', 'sunscreen'],
                'automotive' => ['automotive', 'bengkel', 'otomotif', 'motor', 'mobil', 'oli', 'sparepart', 'detailing'],
                'service' => ['service', 'jasa', 'salon', 'laundry', 'barbershop', 'cuci', 'spa', 'grooming'],
                'retail' => ['retail', 'ritel', 'kelontong', 'sembako', 'toko', 'warung', 'minimarket', 'hampers', 'gift'],
                'pharmacy' => ['pharmacy', 'apotek', 'farmasi', 'herbal', 'obat', 'vitamin', 'madu', 'jamu'],
                'printing' => ['printing', 'percetakan', 'kemasan', 'packaging', 'stiker', 'banner', 'offset', 'box', 'kraft'],
                'furniture' => ['furniture', 'kerajinan', 'kayu', 'mebel', 'craft', 'rotan', 'interior', 'meja', 'kursi'],
                'contractor' => ['contractor', 'kontraktor', 'bangunan', 'konstruksi', 'renovasi', 'material', 'kitchen', 'arsitektur'],
                'distributor' => ['distributor', 'fmcg', 'grosir', 'agen', 'distribusi', 'logistik', 'pallet'],
                'agriculture' => ['agriculture', 'pertanian', 'peternakan', 'agro', 'sayur', 'buah', 'pupuk', 'benih', 'hidroponik'],
                default => [$industryCode],
            };

            $query->where(function ($q) use ($industryCode, $industryKeywords): void {
                $q->where('industry_target', $industryCode)
                    ->orWhere('industry_target', 'like', "%{$industryCode}%");

                foreach ($industryKeywords as $kw) {
                    $q->orWhere('tags', 'like', "%\"{$kw}\"%")
                      ->orWhere('title', 'like', "%{$kw}%")
                      ->orWhere('caption', 'like', "%{$kw}%");
                }
            });
        }

        // Filter Kata Kunci Pencarian
        if ($request->filled('search')) {
            $search = (string) $request->input('search');
            $query->where(function ($q) use ($search): void {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('caption', 'like', "%{$search}%");
            });
        }

        // Pengurutan (Trending vs Terbaru)
        $sort = (string) $request->input('sort', 'trending');
        if ($sort === 'trending') {
            $query->leftJoin('inspiration_trend_scores', 'inspiration_items.id', '=', 'inspiration_trend_scores.inspiration_item_id')
                ->orderByDesc('inspiration_trend_scores.trend_score')
                ->select('inspiration_items.*');
        } else {
            $query->latest('inspiration_items.created_at');
        }

        $perPage = \App\Support\PaginationHelper::perPage($request, 25);
        $items = $query->paginate($perPage)->withQueryString();

        $categories = InspirationCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $items,
                'categories' => $categories,
            ]);
        }

        $industrySectors = self::INDUSTRY_SECTORS;
        $recommendedIndustry = $business->getAiStudioIndustryTarget();
        $recommendedLabel = $business->getAiStudioIndustryLabel();

        return view('app.ai-studio.inspiration.index', compact(
            'items',
            'categories',
            'business',
            'industrySectors',
            'recommendedIndustry',
            'recommendedLabel'
        ));
    }

    /**
     * Detail Item Inspirasi & Lembar Atribusi Hak Cipta.
     */
    public function show(string $id, Request $request): View|JsonResponse
    {
        $business = Context::requireBusiness();

        /** @var InspirationItem $item */
        $item = InspirationItem::query()
            ->with(['source', 'category', 'visualAnalysis', 'trendScore'])
            ->findOrFail($id);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'item' => $item,
            ]);
        }

        return view('app.ai-studio.inspiration.show', compact('item', 'business'));
    }

    /**
     * Endpoint Pencarian Cepat API (Alpine.js Autocomplete).
     */
    public function search(Request $request): JsonResponse
    {
        Context::requireBusiness();

        $search = (string) $request->input('q', '');

        $query = InspirationItem::query()
            ->where('status', 'active')
            ->with(['category', 'visualAnalysis']);

        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('caption', 'like', "%{$search}%");
            });
        }

        $items = $query->limit(15)->get();

        return response()->json([
            'success' => true,
            'data' => $items,
        ]);
    }
}
