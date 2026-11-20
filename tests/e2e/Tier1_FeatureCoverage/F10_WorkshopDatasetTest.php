<?php

declare(strict_types=1);

namespace Tests\E2E\Tier1_FeatureCoverage;

use App\Models\Business;
use Illuminate\Support\Facades\DB;
use Tests\E2E\Support\BaseTestCase;

/**
 * Feature F10: Automotive Workshop Dataset (COOCA AutoCare) Test Suite
 *
 * Validates the COOCA AutoCare dataset: 2 workshops, 20+ services,
 * 30+ spare parts, 30+ customers/vehicles, and service work orders (SPK).
 */
final class F10_WorkshopDatasetTest extends BaseTestCase
{
    private function resolveWorkshopId(): string
    {
        $id = $this->getTenantBusinessId('cooca-autocare')
            ?? $this->getTenantBusinessId('cooca_autocare');

        $this->assertNotNull($id, "COOCA AutoCare workshop tenant business ID must resolve.");

        return $id;
    }

    /**
     * 1. Assert COOCA AutoCare business tenant exists and is active.
     */
    public function test_workshop_business_tenant_exists(): void
    {
        $businessId = $this->resolveWorkshopId();
        $business = Business::find($businessId);

        $this->assertNotNull($business, "COOCA AutoCare business model must exist in database.");
        $this->assertTrue((bool) $business->is_active, "Business must be active.");
        $this->assertTrue(
            in_array($business->industry_category, ['automotive', 'workshop', 'services', 'retail'], true),
            "Business category [{$business->industry_category}] must be automotive/workshop compatible."
        );
    }

    /**
     * 2. Assert workshop business has 2 service branch locations configured.
     */
    public function test_workshop_locations_exist(): void
    {
        $businessId = $this->resolveWorkshopId();
        $this->assertTenantRecordCount('locations', $businessId, 2);
    }

    /**
     * 3. Assert workshop services catalog is populated with at least 20 labor/service items.
     */
    public function test_workshop_services_catalog_populated(): void
    {
        $businessId = $this->resolveWorkshopId();

        $serviceCount = DB::table('products')
            ->where('business_id', $businessId)
            ->where('type', 'service')
            ->count();

        $this->assertGreaterThanOrEqual(
            20,
            $serviceCount,
            "Expected at least 20 service items in products table for workshop tenant, found {$serviceCount}."
        );
    }

    /**
     * 4. Assert workshop spare parts catalog is populated with at least 30 parts/goods items.
     */
    public function test_workshop_spare_parts_catalog_populated(): void
    {
        $businessId = $this->resolveWorkshopId();

        $partsCount = DB::table('products')
            ->where('business_id', $businessId)
            ->where('type', 'goods')
            ->count();

        $this->assertGreaterThanOrEqual(
            30,
            $partsCount,
            "Expected at least 30 spare parts (goods) in products table for workshop tenant, found {$partsCount}."
        );
    }

    /**
     * 5. Assert workshop customers and service work orders (SPK) are seeded.
     */
    public function test_workshop_customers_and_spk_orders_exist(): void
    {
        $businessId = $this->resolveWorkshopId();

        $this->assertTenantRecordCount('customers', $businessId, 30);
        $this->assertTenantRecordCount('pos_orders', $businessId, 1);
    }
}
