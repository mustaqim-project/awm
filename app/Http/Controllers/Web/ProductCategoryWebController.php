<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\ProductCategory;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Str;

final class ProductCategoryWebController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('require.permission:master_data.product_categories.manage', only: ['store', 'update', 'destroy']),
        ];
    }

    /**
     * Store a new product category (via regular form or AJAX modal).
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'name'                      => ['required', 'string', 'max:150'],
            'description'               => ['nullable', 'string', 'max:500'],
            'marketplace_category_id'   => ['nullable', 'string', 'max:50'],
            'marketplace_category_name' => ['nullable', 'string', 'max:100'],
            'cascade_to_products'       => ['nullable', 'boolean'],
        ]);

        $marketplaceCategoryName = $validated['marketplace_category_name'] ?? null;
        if (! empty($validated['marketplace_category_id']) && empty($marketplaceCategoryName)) {
            $matched = \App\Domain\Marketplace\MarketplaceCategoryRegistry::find($validated['marketplace_category_id']);
            $marketplaceCategoryName = $matched['name'] ?? null;
        }

        $category = ProductCategory::create([
            'business_id'               => $business->id,
            'name'                      => $validated['name'],
            'description'               => $validated['description'] ?? null,
            'marketplace_category_id'   => $validated['marketplace_category_id'] ?? null,
            'marketplace_category_name' => $marketplaceCategoryName,
        ]);

        AuditLog::create([
            'business_id' => $business->id,
            'user_id' => $request->user()?->id,
            'auditable_type' => ProductCategory::class,
            'auditable_id' => $category->id,
            'action' => 'product_category.created',
            'risk_level' => AuditLog::RISK_LOW,
            'new_values' => [
                'name' => $category->name,
                'marketplace_category_id' => $category->marketplace_category_id,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'  => true,
                'message'  => __('products.category_created'),
                'category' => $category,
            ], 201);
        }

        return back()->with('success', __('products.category_created'));
    }

    /**
     * Update an existing product category.
     */
    public function update(Request $request, ProductCategory $category): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();
        abort_unless($category->business_id === $business->id, 403);

        $validated = $request->validate([
            'name'                      => ['required', 'string', 'max:150'],
            'description'               => ['nullable', 'string', 'max:500'],
            'marketplace_category_id'   => ['nullable', 'string', 'max:50'],
            'marketplace_category_name' => ['nullable', 'string', 'max:100'],
            'cascade_to_products'       => ['nullable', 'boolean'],
        ]);

        $marketplaceCategoryName = $validated['marketplace_category_name'] ?? null;
        if (! empty($validated['marketplace_category_id']) && empty($marketplaceCategoryName)) {
            $matched = \App\Domain\Marketplace\MarketplaceCategoryRegistry::find($validated['marketplace_category_id']);
            $marketplaceCategoryName = $matched['name'] ?? null;
        }

        $oldValues = [
            'name' => $category->name,
            'description' => $category->description,
            'marketplace_category_id' => $category->marketplace_category_id,
        ];

        $category->update([
            'name'                      => $validated['name'],
            'description'               => $validated['description'] ?? null,
            'marketplace_category_id'   => $validated['marketplace_category_id'] ?? null,
            'marketplace_category_name' => $marketplaceCategoryName,
        ]);

        // Cascade category update to all products and their marketplace mappings
        if ($request->boolean('cascade_to_products', true) && ! empty($category->marketplace_category_id)) {
            $productIds = $category->products()->pluck('id');
            if ($productIds->isNotEmpty()) {
                $mappings = \App\Models\MarketplaceProductMapping::whereIn('product_id', $productIds)->get();
                foreach ($mappings as $mapping) {
                    $raw = (array) ($mapping->raw_metadata ?? []);
                    $raw['category_id']   = $category->marketplace_category_id;
                    $raw['category_name'] = $category->marketplace_category_name;
                    $mapping->update(['raw_metadata' => $raw]);
                }
            }
        }

        AuditLog::create([
            'business_id' => $business->id,
            'user_id' => $request->user()?->id,
            'auditable_type' => ProductCategory::class,
            'auditable_id' => $category->id,
            'action' => 'product_category.updated',
            'risk_level' => AuditLog::RISK_LOW,
            'old_values' => $oldValues,
            'new_values' => [
                'name' => $category->name,
                'description' => $category->description,
                'marketplace_category_id' => $category->marketplace_category_id,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'  => true,
                'message'  => __('products.category_updated'),
                'category' => $category,
            ]);
        }

        return back()->with('success', __('products.category_updated'));
    }

    /**
     * Delete a product category.
     */
    public function destroy(ProductCategory $category): RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless($category->business_id === $business->id, 403);

        $oldValues = [
            'name' => $category->name,
            'marketplace_category_id' => $category->marketplace_category_id,
        ];

        $categoryId = $category->id;
        $category->delete();

        AuditLog::create([
            'business_id' => $business->id,
            'user_id' => request()->user()?->id,
            'auditable_type' => ProductCategory::class,
            'auditable_id' => $categoryId,
            'action' => 'product_category.deleted',
            'risk_level' => AuditLog::RISK_LOW,
            'old_values' => $oldValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);

        return back()->with('success', __('products.category_deleted'));
    }
}
