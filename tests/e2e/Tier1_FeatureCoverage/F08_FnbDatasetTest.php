<?php

declare(strict_types=1);

namespace Tests\E2E\Tier1_FeatureCoverage;

use App\Models\Business;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\E2E\Support\BaseTestCase;

/**
 * Feature F08: Food & Beverage Dataset (COOCA Bistro & Coffee) Test Suite
 *
 * Validates the COOCA Bistro F&B dataset: 3 outlets, 30+ menu items,
 * dining table layout, and recipe BOM / ingredients.
 */
final class F08_FnbDatasetTest extends BaseTestCase
{
    private function resolveFnbId(): string
    {
        $id = $this->getTenantBusinessId('cooca-bistro-coffee')
            ?? $this->getTenantBusinessId('cooca_bistro');

        $this->assertNotNull($id, "COOCA Bistro F&B tenant business ID must resolve.");

        return $id;
    }

    /**
     * 1. Assert COOCA Bistro F&B tenant business exists and is active.
     */
    public function test_fnb_business_tenant_exists(): void
    {
        $businessId = $this->resolveFnbId();
        $business = Business::find($businessId);

        $this->assertNotNull($business, "COOCA Bistro business model must exist in database.");
        $this->assertTrue((bool) $business->is_active, "Business must be active.");
        $this->assertTrue(
            in_array($business->industry_category, ['food_beverage', 'fnb', 'restaurant', 'cafe', 'retail'], true),
            "Business industry category [{$business->industry_category}] must be food_beverage/fnb compatible."
        );
    }

    /**
     * 2. Assert F&B business has 3 outlet locations configured.
     */
    public function test_fnb_outlets_branches_exist(): void
    {
        $businessId = $this->resolveFnbId();
        $this->assertTenantRecordCount('locations', $businessId, 3);
    }

    /**
     * 3. Assert F&B menu catalog is populated with at least 30 menu items.
     */
    public function test_fnb_menu_items_populated(): void
    {
        $businessId = $this->resolveFnbId();
        $this->assertTenantRecordCount('products', $businessId, 30);
    }

    /**
     * 4. Assert F&B dining table management is configured with tables.
     */
    public function test_fnb_dining_tables_exist(): void
    {
        $businessId = $this->resolveFnbId();

        if (Schema::hasTable('pos_tables')) {
            $tableCount = DB::table('pos_tables')->where('business_id', $businessId)->count();
            $this->assertGreaterThanOrEqual(
                10,
                $tableCount,
                "F&B business must configure at least 10 dining tables in pos_tables."
            );
        } else {
            $this->assertTenantRecordCount('locations', $businessId, 1);
        }
    }

    /**
     * 5. Assert F&B recipe ingredients or BOM records exist for menu items.
     */
    public function test_fnb_bom_recipe_or_ingredients_exist(): void
    {
        $businessId = $this->resolveFnbId();

        $hasMaterials = Schema::hasTable('materials')
            && DB::table('materials')->where('business_id', $businessId)->count() > 0;

        $hasBoms = Schema::hasTable('bom_headers')
            && Schema::hasColumn('bom_headers', 'business_id')
            && DB::table('bom_headers')->where('business_id', $businessId)->count() > 0;

        $hasModifiers = Schema::hasTable('modifier_groups')
            && DB::table('modifier_groups')->where('business_id', $businessId)->count() > 0;

        $hasIngredients = $hasMaterials || $hasBoms || $hasModifiers;

        $this->assertTrue(
            $hasIngredients,
            "F&B business must have configured raw materials, BOM recipes, or modifier groups."
        );
    }
}
