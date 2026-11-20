<?php

declare(strict_types=1);

namespace Tests\E2E\Tier1_FeatureCoverage;

use App\Models\Business;
use Tests\E2E\Support\BaseTestCase;

/**
 * Feature F09: Manufacturing Dataset (COOCA Mfg Indonesia) Test Suite
 *
 * Validates the COOCA Mfg dataset: 2 sites, 15+ finished goods,
 * 30+ raw materials, and production work orders (SPKP).
 */
final class F09_MfgDatasetTest extends BaseTestCase
{
    private function resolveMfgId(): string
    {
        $id = $this->getTenantBusinessId('cooca-manufacturing-indonesia')
            ?? $this->getTenantBusinessId('cooca_mfg');

        $this->assertNotNull($id, "COOCA Manufacturing tenant business ID must resolve.");

        return $id;
    }

    /**
     * 1. Assert COOCA Manufacturing business tenant exists and is active.
     */
    public function test_mfg_business_tenant_exists(): void
    {
        $businessId = $this->resolveMfgId();
        $business = Business::find($businessId);

        $this->assertNotNull($business, "COOCA Manufacturing business model must exist in database.");
        $this->assertTrue((bool) $business->is_active, "Business must be active.");
        $this->assertSame('manufacturing', $business->industry_category, "Industry category must be 'manufacturing'.");
    }

    /**
     * 2. Assert manufacturing business has 2 operational sites (factory / warehouse).
     */
    public function test_mfg_sites_locations_exist(): void
    {
        $businessId = $this->resolveMfgId();
        $this->assertTenantRecordCount('locations', $businessId, 2);
    }

    /**
     * 3. Assert manufacturing product catalog is populated with at least 15 finished goods.
     */
    public function test_mfg_finished_goods_products_exist(): void
    {
        $businessId = $this->resolveMfgId();
        $this->assertTenantRecordCount('products', $businessId, 15);
    }

    /**
     * 4. Assert manufacturing business has at least 30 raw materials in inventory catalog.
     */
    public function test_mfg_raw_materials_exist(): void
    {
        $businessId = $this->resolveMfgId();
        $this->assertTenantRecordCount('materials', $businessId, 30);
    }

    /**
     * 5. Assert production work orders (SPKP) are seeded in production_orders table.
     */
    public function test_mfg_work_orders_or_batches_exist(): void
    {
        $businessId = $this->resolveMfgId();
        $this->assertTenantRecordCount('production_orders', $businessId, 1);
    }
}
