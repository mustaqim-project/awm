<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Pos;

use App\Domain\Pos\ModifierService;
use App\Http\Controllers\Controller;
use App\Models\ModifierGroup;
use App\Models\ModifierOption;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class PosModifierApiController extends Controller
{
    public function __construct(
        private readonly ModifierService $modifierService = new ModifierService
    ) {}

    /**
     * List all modifier groups with options.
     * GET /api/v1/pos/modifiers
     */
    public function index(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $groups = $this->modifierService->getGroups($business);

        return response()->json([
            'success' => true,
            'data' => $groups,
            'groups' => $groups,
        ], Response::HTTP_OK);
    }

    /**
     * Store new modifier group.
     * POST /api/v1/pos/modifiers/groups
     */
    public function storeGroup(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:255'],
            'selection_type' => ['nullable', 'string', 'in:single,multiple'],
            'min_selection' => ['nullable', 'integer', 'min:0'],
            'max_selection' => ['nullable', 'integer', 'min:1'],
            'is_required' => ['nullable', 'boolean'],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => ['string', 'exists:products,id'],
        ]);

        $group = ModifierGroup::create([
            'business_id' => $business->id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'selection_type' => $validated['selection_type'] ?? ModifierGroup::SELECTION_SINGLE,
            'min_selection' => (int) ($validated['min_selection'] ?? 0),
            'max_selection' => (int) ($validated['max_selection'] ?? 1),
            'is_required' => (bool) ($validated['is_required'] ?? false),
            'is_active' => true,
        ]);

        if (! empty($validated['product_ids'])) {
            $group->products()->sync($validated['product_ids']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Grup modifier berhasil ditambahkan.',
            'data' => $group->load(['options', 'products:id,name']),
            'group' => $group->load(['options', 'products:id,name']),
        ], Response::HTTP_CREATED);
    }

    /**
     * Update modifier group.
     * PUT /api/v1/pos/modifiers/groups/{group}
     */
    public function updateGroup(Request $request, ModifierGroup $group): JsonResponse
    {
        $business = Context::requireBusiness();
        if ($group->business_id !== $business->id) {
            return response()->json(['message' => 'Grup modifier tidak ditemukan.'], Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validate([
            'name' => ['string', 'max:150'],
            'description' => ['nullable', 'string', 'max:255'],
            'selection_type' => ['nullable', 'string', 'in:single,multiple'],
            'min_selection' => ['nullable', 'integer', 'min:0'],
            'max_selection' => ['nullable', 'integer', 'min:1'],
            'is_required' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => ['string', 'exists:products,id'],
        ]);

        $group->update($validated);

        if (array_key_exists('product_ids', $validated)) {
            $group->products()->sync($validated['product_ids'] ?? []);
        }

        return response()->json([
            'success' => true,
            'message' => 'Grup modifier berhasil diperbarui.',
            'group' => $group->load(['options', 'products:id,name']),
        ], Response::HTTP_OK);
    }

    /**
     * Delete modifier group.
     * DELETE /api/v1/pos/modifiers/groups/{group}
     */
    public function destroyGroup(Request $request, ModifierGroup $group): JsonResponse
    {
        $business = Context::requireBusiness();
        if ($group->business_id !== $business->id) {
            return response()->json(['message' => 'Grup modifier tidak ditemukan.'], Response::HTTP_NOT_FOUND);
        }

        $group->delete();

        return response()->json([
            'success' => true,
            'message' => 'Grup modifier berhasil dihapus.',
        ], Response::HTTP_OK);
    }

    /**
     * Store option in modifier group.
     * POST /api/v1/pos/modifiers/options
     */
    public function storeOption(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'modifier_group_id' => ['required', 'string', 'exists:modifier_groups,id'],
            'name' => ['required', 'string', 'max:150'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'price_delta' => ['nullable', 'numeric', 'min:0'],
            'price_adjustment' => ['nullable', 'numeric', 'min:0'],
            'material_id' => ['nullable', 'string', 'exists:materials,id'],
            'quantity_used' => ['nullable', 'numeric', 'min:0'],
        ]);

        $group = ModifierGroup::where('id', $validated['modifier_group_id'])
            ->where('business_id', $business->id)
            ->firstOrFail();

        $priceDelta = (float) ($validated['price_delta'] ?? $validated['price_adjustment'] ?? $validated['price'] ?? 0);

        $option = ModifierOption::create([
            'modifier_group_id' => $group->id,
            'name' => $validated['name'],
            'price_delta' => $priceDelta,
            'affects_material' => ! empty($validated['material_id']),
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Opsi modifier berhasil ditambahkan.',
            'data' => $option,
            'option' => $option,
        ], Response::HTTP_CREATED);
    }

    /**
     * Update modifier option.
     * PUT /api/v1/pos/modifiers/options/{option}
     */
    public function updateOption(Request $request, ModifierOption $option): JsonResponse
    {
        $business = Context::requireBusiness();
        if ($option->group?->business_id !== $business->id) {
            return response()->json(['message' => 'Opsi modifier tidak ditemukan.'], Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validate([
            'name' => ['string', 'max:150'],
            'price_delta' => ['nullable', 'numeric', 'min:0'],
            'price_adjustment' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if (isset($validated['price_adjustment']) && ! isset($validated['price_delta'])) {
            $validated['price_delta'] = $validated['price_adjustment'];
        }

        $option->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Opsi modifier berhasil diperbarui.',
            'option' => $option,
        ], Response::HTTP_OK);
    }

    /**
     * Delete modifier option.
     * DELETE /api/v1/pos/modifiers/options/{option}
     */
    public function destroyOption(Request $request, ModifierOption $option): JsonResponse
    {
        $business = Context::requireBusiness();
        if ($option->group?->business_id !== $business->id) {
            return response()->json(['message' => 'Opsi modifier tidak ditemukan.'], Response::HTTP_NOT_FOUND);
        }

        $option->delete();

        return response()->json([
            'success' => true,
            'message' => 'Opsi modifier berhasil dihapus.',
        ], Response::HTTP_OK);
    }
}
