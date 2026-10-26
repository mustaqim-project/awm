<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\Unit;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class ProductController extends Controller
{
    /**
     * List products with filters and search.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['outputUnit', 'category', 'costModels']);

        if (Context::hasBusiness()) {
            $query->where('business_id', Context::businessId());
        }

        if ($request->filled('type')) {
            $query->where('type', $request->query('type'));
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->query('category_id'));
        }

        if ($request->filled('search')) {
            $search = (string) $request->query('search');
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $products = $query->latest()->paginate((int) $request->query('per_page', 25));

        return response()->json([
            'success' => true,
            'products' => ProductResource::collection($products),
            'data' => ProductResource::collection($products),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Store a new product.
     */
    public function store(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        // Graceful unit resolution if client sends string name or abbreviation
        $outputUnitId = $request->input('output_unit_id');
        if (empty($outputUnitId) && $request->filled('unit')) {
            $unitInput = strtolower(trim((string) $request->input('unit')));
            $unit = Unit::where(function ($q) use ($business) {
                $q->where('business_id', $business->id)->orWhereNull('business_id');
            })->where(function ($q) use ($unitInput) {
                $q->where('code', $unitInput)->orWhere('name', 'like', "%{$unitInput}%");
            })->first();

            $outputUnitId = $unit?->id;
        }

        if (empty($outputUnitId)) {
            $outputUnitId = Unit::whereIn('code', ['pcs', 'unit', 'jasa', 'porsi'])->first()?->id ?? Unit::first()?->id;
        }

        $request->merge(['output_unit_id' => $outputUnitId]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50'],
            'output_unit_id' => ['required', 'string', 'exists:units,id'],
            'category_id' => ['nullable', 'string', 'exists:product_categories,id'],
            'description' => ['nullable', 'string'],
            'business_type_hint' => ['nullable', 'string', 'in:fnb,retail,manufacturing,service,workshop,project,general'],
            'selling_price' => ['nullable', 'numeric', 'min:0'],
            'base_cost' => ['nullable', 'numeric', 'min:0'],
            'min_stock' => ['nullable', 'numeric', 'min:0'],
            'type' => ['nullable', 'string', 'in:goods,service'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['business_id'] = $business->id;
        $validated['selling_price'] = (float) ($validated['selling_price'] ?? 0);
        $validated['base_cost'] = (float) ($validated['base_cost'] ?? 0);
        $validated['min_stock'] = (float) ($validated['min_stock'] ?? 0);
        $validated['type'] = $validated['type'] ?? Product::TYPE_GOODS;
        $validated['is_active'] = $validated['is_active'] ?? true;

        /** @var Product $product */
        $product = Product::create($validated);
        $product->load(['outputUnit', 'category', 'costModels']);

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil dibuat.',
            'product' => new ProductResource($product),
            'data' => new ProductResource($product),
        ], Response::HTTP_CREATED);
    }

    /**
     * Show product details.
     */
    public function show(Request $request, Product $product): JsonResponse
    {
        if (Context::hasBusiness() && $product->business_id !== Context::businessId()) {
            return response()->json(['message' => 'Produk tidak ditemukan.'], Response::HTTP_NOT_FOUND);
        }

        $product->load(['outputUnit', 'category', 'costModels.components', 'costModels.bomHeader.items']);

        return response()->json([
            'success' => true,
            'product' => new ProductResource($product),
            'data' => new ProductResource($product),
        ], Response::HTTP_OK);
    }

    /**
     * Update product.
     */
    public function update(Request $request, Product $product): JsonResponse
    {
        if (Context::hasBusiness() && $product->business_id !== Context::businessId()) {
            return response()->json(['message' => 'Produk tidak ditemukan.'], Response::HTTP_NOT_FOUND);
        }

        if (! $request->filled('output_unit_id') && $request->filled('unit')) {
            $unitInput = strtolower(trim((string) $request->input('unit')));
            $unit = Unit::where(function ($q) use ($product) {
                $q->where('business_id', $product->business_id)->orWhereNull('business_id');
            })->where(function ($q) use ($unitInput) {
                $q->where('code', $unitInput)->orWhere('name', 'like', "%{$unitInput}%");
            })->first();

            if ($unit) {
                $request->merge(['output_unit_id' => $unit->id]);
            }
        }

        $validated = $request->validate([
            'name' => ['string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50'],
            'output_unit_id' => ['string', 'exists:units,id'],
            'category_id' => ['nullable', 'string', 'exists:product_categories,id'],
            'description' => ['nullable', 'string'],
            'business_type_hint' => ['nullable', 'string', 'in:fnb,retail,manufacturing,service,workshop,project,general'],
            'selling_price' => ['nullable', 'numeric', 'min:0'],
            'base_cost' => ['nullable', 'numeric', 'min:0'],
            'min_stock' => ['nullable', 'numeric', 'min:0'],
            'type' => ['nullable', 'string', 'in:goods,service'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $product->update($validated);
        $product->load(['outputUnit', 'category', 'costModels']);

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil diperbarui.',
            'product' => new ProductResource($product),
            'data' => new ProductResource($product),
        ], Response::HTTP_OK);
    }

    /**
     * Delete product.
     */
    public function destroy(Request $request, Product $product): JsonResponse
    {
        if (Context::hasBusiness() && $product->business_id !== Context::businessId()) {
            return response()->json(['message' => 'Produk tidak ditemukan.'], Response::HTTP_NOT_FOUND);
        }

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil dihapus.',
        ], Response::HTTP_OK);
    }
}
