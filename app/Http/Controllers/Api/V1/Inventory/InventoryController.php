<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Inventory;

use App\Domain\Inventory\StockService;
use App\Http\Controllers\Controller;
use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\Product;
use App\Models\StockMovement;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class InventoryController extends Controller
{
    public function __construct(
        private readonly StockService $stockService = new StockService
    ) {}

    /**
     * List stock levels across locations/warehouses with low-stock filtering.
     */
    public function stocks(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $locations = Location::where('business_id', $business->id)
            ->where('is_active', true)
            ->get(['id', 'name', 'type', 'is_primary']);

        $selectedLocationId = $request->get('location_id');
        $targetLocation = $selectedLocationId
            ? $locations->firstWhere('id', $selectedLocationId)
            : ($locations->firstWhere('is_primary', true) ?? $locations->first());

        $query = InventoryStock::where('inventory_stocks.business_id', $business->id)
            ->whereHas('product', fn ($q) => $q->goods())
            ->with(['product.outputUnit', 'product.category', 'location']);

        if ($request->filled('location_id')) {
            $query->where('inventory_stocks.location_id', $request->get('location_id'));
        }

        $existingStocks = $query->get();
        $coveredProductIds = $existingStocks->pluck('product_id')->filter()->unique()->toArray();

        // Include any active goods products that do not have an inventory_stocks record yet for target location
        $missingGoodsQuery = Product::goods()
            ->where('business_id', $business->id)
            ->where('is_active', true)
            ->whereNotIn('id', $coveredProductIds)
            ->with(['outputUnit', 'category']);

        $missingGoods = $missingGoodsQuery->get();

        $stockItems = $existingStocks->map(function (InventoryStock $st) {
            $unitCost = (float) ($st->last_cost > 0 ? $st->last_cost : ($st->product?->base_cost ?? 0));
            $physicalQty = (float) $st->quantity;
            $effStock = $st->product?->calculateEffectiveStock($st->location_id);
            $qty = $effStock !== null ? (float) $effStock : $physicalQty;
            $minStock = (float) ($st->product?->min_stock ?? 0);

            return [
                'id' => $st->id,
                'product_id' => $st->product_id,
                'location_id' => $st->location_id,
                'quantity' => $qty,
                'physical_quantity' => $physicalQty,
                'effective_stock' => $effStock,
                'last_cost' => (float) $st->last_cost,
                'unit_cost' => $unitCost,
                'total_valuation' => $qty * $unitCost,
                'is_low_stock' => $qty <= $minStock,
                'is_out_of_stock' => $qty <= 0,
                'product' => $st->product ? [
                    'id' => $st->product->id,
                    'name' => $st->product->name,
                    'code' => $st->product->code,
                    'selling_price' => (float) $st->product->selling_price,
                    'base_cost' => (float) $st->product->base_cost,
                    'min_stock' => $minStock,
                    'category_name' => $st->product->category?->name ?? 'Umum',
                    'unit_code' => $st->product->outputUnit?->code ?? 'pcs',
                    'image_url' => ($st->product->image_url && !str_starts_with($st->product->image_url, 'http://') && !str_starts_with($st->product->image_url, 'https://'))
                        ? url($st->product->image_url)
                        : $st->product->image_url,
                ] : null,
                'location' => $st->location ? [
                    'id' => $st->location->id,
                    'name' => $st->location->name,
                    'type' => $st->location->type ?? 'Outlet',
                    'is_primary' => (bool) $st->location->is_primary,
                ] : null,
            ];
        });

        $missingItems = $missingGoods->map(function (Product $prod) use ($targetLocation) {
            $unitCost = (float) $prod->base_cost;
            $effStock = $prod->calculateEffectiveStock($targetLocation?->id);
            $qty = (float) ($effStock ?? 0);
            $minStock = (float) ($prod->min_stock ?? 0);

            return [
                'id' => 'virt_' . $prod->id . '_' . ($targetLocation?->id ?? 'default'),
                'product_id' => $prod->id,
                'location_id' => $targetLocation?->id,
                'quantity' => $qty,
                'physical_quantity' => 0.0,
                'effective_stock' => $effStock,
                'last_cost' => $unitCost,
                'unit_cost' => $unitCost,
                'total_valuation' => $qty * $unitCost,
                'is_low_stock' => $qty <= $minStock,
                'is_out_of_stock' => $qty <= 0,
                'product' => [
                    'id' => $prod->id,
                    'name' => $prod->name,
                    'code' => $prod->code,
                    'selling_price' => (float) $prod->selling_price,
                    'base_cost' => (float) $prod->base_cost,
                    'min_stock' => $minStock,
                    'category_name' => $prod->category?->name ?? 'Umum',
                    'unit_code' => $prod->outputUnit?->code ?? 'pcs',
                    'image_url' => ($prod->image_url && !str_starts_with($prod->image_url, 'http://') && !str_starts_with($prod->image_url, 'https://'))
                        ? url($prod->image_url)
                        : $prod->image_url,
                ],
                'location' => $targetLocation ? [
                    'id' => $targetLocation->id,
                    'name' => $targetLocation->name,
                    'type' => $targetLocation->type ?? 'Outlet',
                    'is_primary' => (bool) $targetLocation->is_primary,
                ] : null,
            ];
        });

        $allItems = $stockItems->concat($missingItems);

        // Apply filters: search and low_stock_only
        if ($request->filled('search')) {
            $search = strtolower((string) $request->get('search'));
            $allItems = $allItems->filter(function ($item) use ($search) {
                $name = strtolower($item['product']['name'] ?? '');
                $code = strtolower($item['product']['code'] ?? '');
                $cat = strtolower($item['product']['category_name'] ?? '');
                return str_contains($name, $search) || str_contains($code, $search) || str_contains($cat, $search);
            });
        }

        if ($request->boolean('low_stock_only')) {
            $allItems = $allItems->filter(fn ($item) => $item['is_low_stock']);
        }

        $lowStockCount = $allItems->where('is_low_stock', true)->count();
        $totalValuation = (float) $allItems->sum('total_valuation');

        $perPage = (int) $request->get('per_page', 50);
        $page = (int) $request->get('page', 1);
        $total = $allItems->count();
        $lastPage = max(1, (int) ceil($total / $perPage));
        $pagedItems = $allItems->forPage($page, $perPage)->values();

        return response()->json([
            'success' => true,
            'data' => $pagedItems,
            'stocks' => $pagedItems,
            'locations' => $locations,
            'meta' => [
                'low_stock_count' => $lowStockCount,
                'total_valuation' => $totalValuation,
                'locations_count' => $locations->count(),
                'selected_location_id' => $targetLocation?->id,
            ],
            'pagination' => [
                'current_page' => $page,
                'last_page' => $lastPage,
                'per_page' => $perPage,
                'total' => $total,
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Quick stock adjustment.
     */
    public function adjust(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $user = $request->user();

        // Graceful location resolution if client sends dummy or empty location ID
        $locInput = $request->input('location_id');
        if (empty($locInput) || $locInput === 'main_warehouse' || $locInput === 'default') {
            $defaultLocId = Location::where('business_id', $business->id)->where('is_primary', true)->value('id')
                ?? Location::where('business_id', $business->id)->value('id');
            if ($defaultLocId) {
                $request->merge(['location_id' => $defaultLocId]);
            }
        }

        // Support actual_quantity as an alias for new_quantity
        if ($request->filled('actual_quantity') && ! $request->filled('new_quantity')) {
            $request->merge(['new_quantity' => $request->input('actual_quantity')]);
        }

        $validated = $request->validate([
            'location_id' => ['required', 'string', 'exists:locations,id'],
            'product_id' => ['nullable', 'string', 'exists:products,id', 'required_without:material_id'],
            'material_id' => ['nullable', 'string', 'exists:materials,id', 'required_without:product_id'],
            'new_quantity' => ['nullable', 'numeric', 'min:0'],
            'actual_quantity' => ['nullable', 'numeric', 'min:0'],
            'type' => ['nullable', 'string', 'in:in,out,set'],
            'quantity' => ['nullable', 'numeric', 'min:0'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'reason' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $stock = $this->stockService->getOrCreateStock(
            $business->id,
            $validated['location_id'],
            $validated['product_id'] ?? null,
            false,
            $validated['material_id'] ?? null
        );

        $quantity = (float) ($validated['quantity'] ?? 0);
        $newQuantity = array_key_exists('new_quantity', $validated) && $validated['new_quantity'] !== null
            ? (float) $validated['new_quantity']
            : match ($validated['type'] ?? 'set') {
                'in' => (float) $stock->quantity + $quantity,
                'out' => max(0, (float) $stock->quantity - $quantity),
                default => $quantity,
            };
        $diff = $newQuantity - (float) $stock->quantity;
        $unitCost = (float) ($validated['unit_cost'] ?? $stock->last_cost ?? 0);

        if ($diff != 0) {
            $this->stockService->recordMovement(
                businessId: $business->id,
                locationId: $validated['location_id'],
                productId: $validated['product_id'] ?? null,
                movementType: StockMovement::TYPE_ADJUSTMENT,
                quantityChange: $diff,
                unitCost: $unitCost,
                notes: $validated['notes'] ?? $validated['reason'] ?? 'Penyesuaian stok via mobile API',
                userId: $user->id,
                materialId: $validated['material_id'] ?? null
            );
        }

        $stock->refresh()->load(['product.outputUnit', 'location']);

        return response()->json([
            'success' => true,
            'message' => 'Stok produk berhasil disesuaikan.',
            'stock' => $stock,
            'new_quantity' => (float) $stock->quantity,
        ], Response::HTTP_OK);
    }

    /**
     * Stock movements history / Stock Card (Kartu Stok).
     */
    public function movements(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $query = StockMovement::where('business_id', $business->id)
            ->with(['product.outputUnit', 'location:id,name', 'creator:id,name'])
            ->latest('created_at');

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->get('product_id'));
        }

        if ($request->filled('location_id')) {
            $query->where('location_id', $request->get('location_id'));
        }

        if ($request->filled('movement_type')) {
            $query->where('movement_type', $request->get('movement_type'));
        }

        $movements = $query->paginate((int) $request->get('per_page', 30));

        $items = collect($movements->items())->map(function (StockMovement $m) {
            return [
                'id' => $m->id,
                'movement_type' => $m->movement_type,
                'quantity_change' => (float) $m->quantity_change,
                'unit_cost' => (float) $m->unit_cost,
                'notes' => $m->notes ?? '-',
                'created_at' => $m->created_at?->toIso8601String(),
                'product_name' => $m->product?->name ?? 'Produk',
                'product_code' => $m->product?->code ?? '-',
                'unit_code' => $m->product?->outputUnit?->code ?? 'pcs',
                'location_name' => $m->location?->name ?? 'Outlet',
                'creator_name' => $m->creator?->name ?? 'Sistem',
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $items,
            'movements' => $items,
            'pagination' => [
                'current_page' => $movements->currentPage(),
                'last_page' => $movements->lastPage(),
                'per_page' => $movements->perPage(),
                'total' => $movements->total(),
            ],
        ], Response::HTTP_OK);
    }
}
