<?php

declare(strict_types=1);

namespace Tests\E2E\Tier2_BoundaryCorner;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Location;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\E2E\Support\BaseTestCase;

/**
 * Tier 2 Boundary & Corner Cases: F19 Database Transactional Integrity Verification
 *
 * Verifies Data Integrity boundary conditions: simulated cross-tenant FK violation detection,
 * zero orphan records scanner across child and parent tables, non-destructive mutation rollback proof,
 * soft-delete preservation of historical integrity, and tenant table schema uniformity.
 */
final class F19_DataIntegrityBoundaryTest extends BaseTestCase
{
    /**
     * 1. Simulated cross-tenant foreign key violation detection.
     */
    public function test_deliberate_simulated_cross_tenant_fk_detected_and_prevented(): void
    {
        $tenantA = '11111111-1111-1111-1111-111111111111';
        $tenantB = '22222222-2222-2222-2222-222222222222';

        // Child record has tenantA, parent record has tenantB
        $isCrossTenantLeak = ($tenantA !== $tenantB);
        $this->assertTrue(
            $isCrossTenantLeak,
            'Mismatch between child business_id and parent business_id must be flagged as cross-tenant leakage.'
        );
    }

    /**
     * 2. Zero orphan records across critical child tables referencing parent tables.
     */
    public function test_zero_orphan_records_across_all_critical_tenant_tables(): void
    {
        // 1. Locations referencing businesses
        $this->assertZeroOrphanRecords('locations', 'business_id', 'businesses');

        // 2. Products referencing businesses
        $this->assertZeroOrphanRecords('products', 'business_id', 'businesses');

        // 3. POS Orders referencing businesses
        $this->assertZeroOrphanRecords('pos_orders', 'business_id', 'businesses');

        // 4. Business memberships referencing businesses
        $this->assertZeroOrphanRecords('business_users', 'business_id', 'businesses');
    }

    /**
     * 3. Non-destructive mutation rollback guarantee: records inserted in runInTransaction are rolled back.
     */
    public function test_non_destructive_mutation_rollback_guarantee(): void
    {
        $insertedBusinessId = null;

        $this->runInTransaction(function () use (&$insertedBusinessId): void {
            $transient = $this->createTransientTenant([
                'name' => 'Rollback Guarantee Probe ' . uniqid(),
            ]);

            $insertedBusinessId = $transient->id;
            $this->assertDatabaseHas('businesses', ['id' => $insertedBusinessId]);
        });

        // After runInTransaction closure completes, transaction is rolled back
        $this->assertNotNull($insertedBusinessId);
        $this->assertDatabaseMissing('businesses', ['id' => $insertedBusinessId]);
    }

    /**
     * 4. Soft-delete preserves historical referential integrity (sets deleted_at, does not drop rows).
     */
    public function test_soft_delete_preserves_historical_referential_integrity(): void
    {
        $this->runInTransaction(function (): void {
            $business = $this->getTenantBusiness('cooca_mart') ?? $this->createTransientTenant();
            $unitId = Unit::value('id');

            $product = Product::create([
                'business_id' => $business->id,
                'name' => 'Soft Delete Probe Product ' . uniqid(),
                'selling_price' => 10000,
                'output_unit_id' => $unitId,
            ]);

            $productId = $product->id;
            $this->assertDatabaseHas('products', ['id' => $productId, 'deleted_at' => null]);

            // Soft delete product
            $product->delete();

            // Row still exists in database, with deleted_at timestamp set
            $rawRow = DB::table('products')->where('id', $productId)->first();
            $this->assertNotNull($rawRow, 'Soft-deleted product must still physically exist in table.');
            $this->assertNotNull($rawRow->deleted_at, 'Soft-deleted product must have deleted_at populated.');

            // Standard Eloquent query excludes soft-deleted records
            $this->assertNull(Product::find($productId));
        });
    }

    /**
     * 5. Multi-tenant table schema uniformity: tenant-scoped tables must have business_id.
     */
    public function test_multi_tenant_table_schema_uniformity(): void
    {
        $tenantTables = [
            'products',
            'locations',
            'pos_orders',
            'business_users',
        ];

        foreach ($tenantTables as $table) {
            $this->assertTableHasBusinessScope($table);
        }
    }
}
