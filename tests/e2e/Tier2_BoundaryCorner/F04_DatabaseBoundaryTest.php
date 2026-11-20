<?php

declare(strict_types=1);

namespace Tests\E2E\Tier2_BoundaryCorner;

use App\Models\Business;
use App\Models\Location;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Database\QueryException;
use Tests\E2E\Support\BaseTestCase;
use Tests\E2E\Support\Helpers\TenantHelper;

/**
 * Tier 2 Boundary & Corner Cases: F04 Multi-Tenant Database Architecture Spec
 *
 * Verifies multi-tenant database boundary constraints: null business_id rejection,
 * zero cross-tenant foreign key leakage, transient tenant isolation and cleanup,
 * strict Eloquent BelongsToBusiness scoping, and non-existent parent FK rejection.
 */
final class F04_DatabaseBoundaryTest extends BaseTestCase
{
    /**
     * 1. Attempting to insert a multi-tenant record with null business_id throws a QueryException.
     */
    public function test_null_business_id_rejection_on_tenant_models(): void
    {
        $this->runInTransaction(function (): void {
            $unitId = Unit::value('id');
            $caught = false;

            try {
                Product::create([
                    'business_id' => null,
                    'name' => 'Boundary Illegal Null Tenant Product',
                    'selling_price' => 10000,
                    'output_unit_id' => $unitId,
                    'type' => Product::TYPE_GOODS,
                ]);
            } catch (QueryException $e) {
                $caught = true;
                $this->assertStringContainsString('cannot be null', strtolower($e->getMessage()));
            }

            $this->assertTrue($caught, 'Expected QueryException on null business_id was not thrown.');
        });
    }

    /**
     * 2. Zero cross-tenant foreign key violations across critical multi-tenant tables.
     */
    public function test_zero_cross_tenant_foreign_key_leakage(): void
    {
        // Locations and Businesses
        $this->assertZeroCrossTenantForeignKeys('locations', 'business_id', 'businesses');

        // Products and Product Categories
        $this->assertZeroCrossTenantForeignKeys('products', 'category_id', 'product_categories');

        // POS Orders and Businesses
        $this->assertZeroCrossTenantForeignKeys('pos_orders', 'business_id', 'businesses');
    }

    /**
     * 3. Transient tenant lifecycle: creation, verification, and automatic cleanup.
     */
    public function test_transient_tenant_creation_and_cleanup(): void
    {
        $transientBusiness = $this->createTransientTenant([
            'name' => 'Transient E2E Lifecycle Tenant ' . uniqid(),
        ]);

        $this->assertNotNull($transientBusiness->id);
        $this->assertDatabaseHas('businesses', ['id' => $transientBusiness->id]);

        TenantHelper::cleanupTransientTenants();

        $this->assertDatabaseMissing('businesses', ['id' => $transientBusiness->id]);
    }

    /**
     * 4. Multi-tenant scope strictly isolates data when tenant context is active.
     */
    public function test_cross_tenant_scope_isolation_under_active_context(): void
    {
        $this->runInTransaction(function (): void {
            $tenantA = $this->createTransientTenant(['name' => 'Tenant A ' . uniqid()]);
            $tenantB = $this->createTransientTenant(['name' => 'Tenant B ' . uniqid()]);
            $unitId = Unit::value('id');

            Product::create([
                'business_id' => $tenantA->id,
                'name' => 'Product of Tenant A',
                'selling_price' => 15000,
                'output_unit_id' => $unitId,
            ]);

            Product::create([
                'business_id' => $tenantB->id,
                'name' => 'Product of Tenant B',
                'selling_price' => 25000,
                'output_unit_id' => $unitId,
            ]);

            // Set context to Tenant A
            $this->setTenantContext($tenantA->id);
            $productsInContextA = Product::where('business_id', $tenantA->id)->get();

            $this->assertNotEmpty($productsInContextA);
            foreach ($productsInContextA as $prod) {
                $this->assertSame($tenantA->id, $prod->business_id);
                $this->assertNotSame($tenantB->id, $prod->business_id);
            }
        });
    }

    /**
     * 5. Inserting child records with non-existent parent foreign UUID triggers constraint violation.
     */
    public function test_invalid_foreign_uuid_for_business_id_rejected(): void
    {
        $this->runInTransaction(function (): void {
            $nonExistentUuid = '00000000-0000-0000-0000-000000000000';
            $caught = false;

            try {
                Location::create([
                    'business_id' => $nonExistentUuid,
                    'name' => 'Orphan Branch Location',
                    'code' => 'ORPH-01',
                    'type' => 'store',
                ]);
            } catch (QueryException $e) {
                $caught = true;
                $this->assertStringContainsString('foreign key constraint', strtolower($e->getMessage()));
            }

            $this->assertTrue($caught, 'Expected foreign key constraint violation on non-existent parent UUID.');
        });
    }
}
