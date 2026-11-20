<?php

declare(strict_types=1);

namespace Tests\E2E\Tier1_FeatureCoverage;

use App\Models\Business;
use Tests\E2E\Support\BaseTestCase;

/**
 * Feature F07: Retail & Distribution Dataset (COOCA Mart) Test Suite
 *
 * Validates the COOCA Mart retail dataset: 3 branches, 35+ retail SKUs,
 * 20+ customers, 10+ suppliers, and POS order transactions.
 */
final class F07_RetailDatasetTest extends BaseTestCase
{
    /**
     * 1. Assert COOCA Mart retail tenant business exists and has retail industry classification.
     */
    public function test_retail_business_tenant_exists(): void
    {
        $businessId = $this->getTenantBusinessId('cooca_mart');
        $this->assertNotNull($businessId, "COOCA Mart tenant business ID must resolve.");

        $business = Business::find($businessId);
        $this->assertNotNull($business, "COOCA Mart business model must exist in database.");
        $this->assertSame('retail', $business->industry_category, "Business industry_category must be 'retail'.");
        $this->assertTrue((bool) $business->is_active, "Business must be active.");
    }

    /**
     * 2. Assert retail business has 3 physical branch locations configured.
     */
    public function test_retail_locations_branches_exist(): void
    {
        $businessId = $this->getTenantBusinessId('cooca_mart');
        $this->assertNotNull($businessId);

        $this->assertTenantRecordCount('locations', $businessId, 3);
    }

    /**
     * 3. Assert retail product catalog is populated with at least 35 retail SKUs.
     */
    public function test_retail_products_catalog_populated(): void
    {
        $businessId = $this->getTenantBusinessId('cooca_mart');
        $this->assertNotNull($businessId);

        $this->assertTenantRecordCount('products', $businessId, 35);
    }

    /**
     * 4. Assert retail business has at least 20 customers and 10 suppliers.
     */
    public function test_retail_customers_and_suppliers_exist(): void
    {
        $businessId = $this->getTenantBusinessId('cooca_mart');
        $this->assertNotNull($businessId);

        $this->assertTenantRecordCount('customers', $businessId, 20);
        $this->assertTenantRecordCount('suppliers', $businessId, 10);
    }

    /**
     * 5. Assert retail business has seeded POS orders and transactions.
     */
    public function test_retail_pos_orders_exist(): void
    {
        $businessId = $this->getTenantBusinessId('cooca_mart');
        $this->assertNotNull($businessId);

        $this->assertTenantRecordCount('pos_orders', $businessId, 1);
    }
}
