<?php

declare(strict_types=1);

namespace Tests\E2E\Tier1_FeatureCoverage;

use App\Models\Business;
use App\Models\Product;
use App\Support\Context;
use Illuminate\Support\Facades\DB;
use Tests\E2E\Support\BaseTestCase;

/**
 * Feature F19: Database Transactional Integrity Verification Test Suite
 *
 * Validates zero cross-tenant FK leakage, zero orphan records, non-null business_id
 * tenant partitions, transaction rollback guarantees, and tenant isolation scopes.
 */
final class F19_DataIntegrityTest extends BaseTestCase
{
    /**
     * 1. Assert zero cross-tenant foreign key leakage between child and parent tables.
     */
    public function test_zero_cross_tenant_foreign_keys(): void
    {
        $this->assertZeroCrossTenantForeignKeys('pos_orders', 'customer_id', 'customers');
        $this->assertZeroCrossTenantForeignKeys('pos_orders', 'location_id', 'locations');
    }

    /**
     * 2. Assert zero orphan records referencing non-existent parent businesses.
     */
    public function test_zero_orphan_records_across_core_tables(): void
    {
        $this->assertZeroOrphanRecords('products', 'business_id', 'businesses');
        $this->assertZeroOrphanRecords('locations', 'business_id', 'businesses');
        $this->assertZeroOrphanRecords('customers', 'business_id', 'businesses');
        $this->assertZeroOrphanRecords('suppliers', 'business_id', 'businesses');
    }

    /**
     * 3. Assert business_id tenant partitioning column is non-null across all active records.
     */
    public function test_business_id_is_non_null_in_tenant_tables(): void
    {
        $tables = ['products', 'locations', 'customers', 'suppliers'];

        foreach ($tables as $table) {
            $nullCount = DB::table($table)->whereNull('business_id')->count();
            $this->assertSame(
                0,
                $nullCount,
                "Detected {$nullCount} records with NULL business_id in multi-tenant table [{$table}]."
            );
        }
    }

    /**
     * 4. Assert non-destructive execution: mutations inside runInTransaction are guaranteed to roll back.
     */
    public function test_transaction_rollback_guarantee(): void
    {
        $businessId = $this->getTenantBusinessId('cooca_mart');
        $this->assertNotNull($businessId);

        $testCode = 'CUST-ROLLBACK-' . uniqid();

        $this->runInTransaction(function () use ($businessId, $testCode) {
            DB::table('customers')->insert([
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'business_id' => $businessId,
                'name' => 'Temporary Mutation Test Customer',
                'code' => $testCode,
                'slug' => strtolower($testCode),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->assertTrue(
                DB::table('customers')->where('code', $testCode)->exists(),
                "Temporary record must exist inside active transaction."
            );
        });

        $this->assertFalse(
            DB::table('customers')->where('code', $testCode)->exists(),
            "Temporary record must be rolled back completely outside transaction."
        );
    }

    /**
     * 5. Assert strict tenant isolation: queries under one tenant context cannot leak another tenant's records.
     */
    public function test_tenant_isolation_under_concurrent_queries(): void
    {
        $retailId = $this->getTenantBusinessId('cooca_mart');
        $fnbId = $this->getTenantBusinessId('cooca_bistro');

        $this->assertNotNull($retailId);
        $this->assertNotNull($fnbId);
        $this->assertNotEquals($retailId, $fnbId, "Retail and F&B tenant businesses must be distinct.");

        $retailBusiness = Business::find($retailId);
        $this->setTenantContext($retailId);

        // Eloquent query with BelongsToBusiness global scope active
        $visibleProductBusinessIds = Product::query()
            ->pluck('business_id')
            ->unique()
            ->all();

        foreach ($visibleProductBusinessIds as $bId) {
            $this->assertSame(
                $retailId,
                $bId,
                "Tenant isolation leak: product belonging to [{$bId}] appeared under context [{$retailId}]."
            );
        }

        Context::flush();
    }
}
