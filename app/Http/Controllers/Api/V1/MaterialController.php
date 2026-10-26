<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Material\MaterialCostService;
use App\Http\Controllers\Controller;
use App\Http\Resources\MaterialPriceResource;
use App\Http\Resources\MaterialResource;
use App\Http\Resources\UnitResource;
use App\Models\Material;
use App\Models\MaterialPrice;
use App\Models\Unit;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class MaterialController extends Controller
{
    public function __construct(
        private readonly MaterialCostService $costService = new MaterialCostService
    ) {}

    /**
     * List materials with filtering by category and keyword search.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Material::with(['unit', 'category', 'supplier', 'latestPrice']);

        if (Context::hasBusiness()) {
            $query->where('business_id', Context::businessId());
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

        if ($request->boolean('active_only', false)) {
            $query->whereNull('discontinued_at');
        }

        $materials = $query->latest()->paginate((int) $request->query('per_page', 25));

        return response()->json([
            'success' => true,
            'materials' => MaterialResource::collection($materials),
            'data' => MaterialResource::collection($materials),
            'pagination' => [
                'current_page' => $materials->currentPage(),
                'last_page' => $materials->lastPage(),
                'per_page' => $materials->perPage(),
                'total' => $materials->total(),
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Store a new material.
     */
    public function store(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        // Graceful unit resolution if client sends string name or abbreviation
        $unitId = $request->input('unit_id');
        if (empty($unitId) && $request->filled('unit')) {
            $unitInput = strtolower(trim((string) $request->input('unit')));
            $unit = Unit::where(function ($q) use ($business) {
                $q->where('business_id', $business->id)->orWhereNull('business_id');
            })->where(function ($q) use ($unitInput) {
                $q->where('code', $unitInput)->orWhere('name', 'like', "%{$unitInput}%");
            })->first();

            $unitId = $unit?->id;
        }

        if (empty($unitId)) {
            $unitId = Unit::whereIn('code', ['kg', 'g', 'pcs', 'ml', 'l'])->first()?->id ?? Unit::first()?->id;
        }

        $request->merge(['unit_id' => $unitId]);

        // Auto find or create supplier if supplier_name provided without supplier_id
        if (! $request->filled('supplier_id') && $request->filled('supplier_name')) {
            $supplierName = trim((string) $request->input('supplier_name'));
            if ($supplierName !== '') {
                $supplier = \App\Models\Supplier::firstOrCreate(
                    ['business_id' => $business->id, 'name' => $supplierName],
                    ['is_active' => true]
                );
                $request->merge(['supplier_id' => $supplier->id]);
            }
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50'],
            'unit_id' => ['required', 'string', 'exists:units,id'],
            'category_id' => ['nullable', 'string', 'exists:material_categories,id'],
            'supplier_id' => ['nullable', 'string', 'exists:suppliers,id'],
            'description' => ['nullable', 'string'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'yield_percentage' => ['nullable', 'numeric', 'min:0'],
            'waste_percentage' => ['nullable', 'numeric', 'min:0'],
            'shipping_cost' => ['nullable', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $validated['business_id'] = $business->id;

        /** @var Material $material */
        $material = Material::create([
            'business_id' => $business->id,
            'name' => $validated['name'],
            'code' => $validated['code'] ?? null,
            'unit_id' => $validated['unit_id'],
            'category_id' => $validated['category_id'] ?? null,
            'supplier_id' => $validated['supplier_id'] ?? null,
            'description' => $validated['description'] ?? null,
        ]);

        // Create initial price if purchase_price provided
        if (isset($validated['purchase_price']) && (float) $validated['purchase_price'] > 0) {
            $purchasePrice = (float) $validated['purchase_price'];
            $shipping = (float) ($validated['shipping_cost'] ?? 0.0);
            $discount = (float) ($validated['discount_amount'] ?? 0.0);

            /** @var MaterialPrice $price */
            $price = MaterialPrice::create([
                'business_id' => $business->id,
                'material_id' => $material->id,
                'supplier_id' => $validated['supplier_id'] ?? null,
                'purchase_price' => $purchasePrice,
                'shipping_cost' => $shipping,
                'handling_cost' => 0.0,
                'import_cost' => 0.0,
                'discount_amount' => $discount,
                'yield_percentage' => (float) ($validated['yield_percentage'] ?? 100.0),
                'waste_percentage' => (float) ($validated['waste_percentage'] ?? 0.0),
                'purchase_unit_id' => $material->unit_id,
                'effective_date' => now()->toDateString(),
                'notes' => 'Penerimaan harga bahan awal (Mobile/API).',
            ]);

            try {
                $price->update([
                    'effective_cost' => $this->costService->calculateEffectiveAcquisitionCost($price),
                ]);
            } catch (\Throwable) {
                $price->update([
                    'effective_cost' => $purchasePrice + $shipping - $discount,
                ]);
            }
        }

        $material->load(['unit', 'category', 'supplier', 'latestPrice']);

        return response()->json([
            'success' => true,
            'message' => 'Bahan baku berhasil dibuat.',
            'material' => new MaterialResource($material),
            'data' => new MaterialResource($material),
        ], Response::HTTP_CREATED);
    }

    /**
     * Show material details with latest price.
     */
    public function show(Request $request, Material $material): JsonResponse
    {
        $material->load(['unit', 'category', 'supplier', 'latestPrice.purchaseUnit', 'latestPrice.supplier', 'latestPrice.currency']);

        return response()->json([
            'material' => new MaterialResource($material),
        ], Response::HTTP_OK);
    }

    /**
     * Update material.
     */
    public function update(Request $request, Material $material): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50'],
            'unit_id' => ['string', 'exists:units,id'],
            'category_id' => ['nullable', 'string', 'exists:material_categories,id'],
            'supplier_id' => ['nullable', 'string', 'exists:suppliers,id'],
            'description' => ['nullable', 'string'],
            'is_discontinued' => ['boolean'],
        ]);

        if (array_key_exists('is_discontinued', $validated)) {
            $validated['discontinued_at'] = $validated['is_discontinued'] ? now() : null;
            unset($validated['is_discontinued']);
        }

        $material->update($validated);
        $material->load(['unit', 'category', 'supplier', 'latestPrice']);

        return response()->json([
            'message' => 'Material updated successfully.',
            'material' => new MaterialResource($material),
        ], Response::HTTP_OK);
    }

    /**
     * Delete material.
     */
    public function destroy(Request $request, Material $material): JsonResponse
    {
        $material->delete();

        return response()->json([
            'message' => 'Material deleted successfully.',
        ], Response::HTTP_OK);
    }

    /**
     * Add a historical purchase price entry for material.
     */
    public function storePrice(Request $request, Material $material): JsonResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'purchase_price' => ['required', 'numeric', 'gte:0'],
            'shipping_cost' => ['nullable', 'numeric', 'gte:0'],
            'handling_cost' => ['nullable', 'numeric', 'gte:0'],
            'import_cost' => ['nullable', 'numeric', 'gte:0'],
            'discount_amount' => ['nullable', 'numeric', 'gte:0'],
            'purchase_unit_id' => ['required', 'string', 'exists:units,id'],
            'supplier_id' => ['nullable', 'string', 'exists:suppliers,id'],
            'currency_id' => ['nullable', 'string', 'exists:currencies,id'],
            'yield_percentage' => ['nullable', 'numeric', 'gt:0'],
            'waste_percentage' => ['nullable', 'numeric', 'gte:0', 'lt:100'],
            'allow_yield_over_100' => ['boolean'],
            'effective_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $yield = (float) ($validated['yield_percentage'] ?? 100.0);
        $allowOver100 = (bool) ($validated['allow_yield_over_100'] ?? false);

        if ($yield > 100.0 && ! $allowOver100) {
            return response()->json([
                'message' => 'Yield percentage is greater than 100%. Please confirm by setting allow_yield_over_100 flag to true.',
                'error_code' => 'YIELD_OVER_100_CONFIRMATION_REQUIRED',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        /** @var MaterialPrice $price */
        $price = MaterialPrice::create([
            'business_id' => $business->id,
            'material_id' => $material->id,
            'supplier_id' => $validated['supplier_id'] ?? $material->supplier_id,
            'currency_id' => $validated['currency_id'] ?? null,
            'purchase_unit_id' => $validated['purchase_unit_id'],
            'purchase_price' => $validated['purchase_price'],
            'shipping_cost' => $validated['shipping_cost'] ?? 0,
            'handling_cost' => $validated['handling_cost'] ?? 0,
            'import_cost' => $validated['import_cost'] ?? 0,
            'discount_amount' => $validated['discount_amount'] ?? 0,
            'yield_percentage' => $yield,
            'waste_percentage' => $validated['waste_percentage'] ?? 0,
            'effective_date' => $validated['effective_date'],
            'notes' => $validated['notes'] ?? null,
        ]);

        $price->load(['purchaseUnit', 'supplier', 'currency']);

        return response()->json([
            'message' => 'Material price recorded successfully.',
            'price' => new MaterialPriceResource($price),
        ], Response::HTTP_CREATED);
    }

    /**
     * Calculate effective acquisition cost and unit cost for a material.
     */
    public function effectiveCost(Request $request, Material $material, MaterialCostService $costService): JsonResponse
    {
        /** @var MaterialPrice|null $price */
        $price = $material->latestPrice()->with(['purchaseUnit', 'currency'])->first();

        if ($price === null) {
            return response()->json([
                'message' => 'No price record found for this material.',
            ], Response::HTTP_NOT_FOUND);
        }

        $targetUnitId = $request->query('target_unit_id');
        $targetUnit = $targetUnitId ? Unit::available()->find($targetUnitId) : $material->unit;

        $breakdown = $costService->getMaterialCostBreakdown($price, $targetUnit);

        return response()->json([
            'material' => [
                'id' => $material->id,
                'name' => $material->name,
                'slug' => $material->slug,
                'base_unit' => new UnitResource($material->unit),
            ],
            'effective_cost' => $breakdown,
        ], Response::HTTP_OK);
    }
}
