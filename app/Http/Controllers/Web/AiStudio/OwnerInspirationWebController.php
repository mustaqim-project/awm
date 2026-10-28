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

        // Filter Industri / Target Channel
        if ($request->filled('industry')) {
            $query->where('industry_target', $request->input('industry'));
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

        $items = $query->paginate(24)->withQueryString();

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

        return view('app.ai-studio.inspiration.index', compact('items', 'categories', 'business'));
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
