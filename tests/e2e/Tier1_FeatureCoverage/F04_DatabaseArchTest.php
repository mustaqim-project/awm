<?php

declare(strict_types=1);

namespace Tests\E2E\Tier1_FeatureCoverage;

use App\Models\Business;
use App\Models\Customer;
use App\Models\JournalEntry;
use App\Models\Location;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Traits\BelongsToBusiness;
use App\Support\Context;
use Illuminate\Support\Facades\Schema;
use Tests\E2E\Support\BaseTestCase;

/**
 * Feature F04: Multi-Tenant Database Architecture Spec Test Suite
 *
 * Validates the multi-tenant database documentation, business_id schema scoping,
 * BelongsToBusiness Eloquent trait binding, and runtime context isolation.
 */
final class F04_DatabaseArchTest extends BaseTestCase
{
    private string $archDoc = 'documentation/cooca-platform-audit/05-database-architecture.md';

    /**
     * 1. Assert multi-tenant database architecture specification document exists and is populated.
     */
    public function test_database_architecture_documentation_exists(): void
    {
        $this->assertMarkdownFilePopulated($this->archDoc, 1000);
        $this->assertMarkdownContainsHeaders($this->archDoc, [
            'Multi-Tenant Database Architecture',
            'Database Topology',
        ]);
    }

    /**
     * 2. Assert core multi-tenant entity tables contain business_id scoping column.
     */
    public function test_core_multi_tenant_tables_have_business_id(): void
    {
        $tables = [
            'products',
            'locations',
            'customers',
            'suppliers',
            'pos_orders',
            'journal_entries',
        ];

        foreach ($tables as $table) {
            $this->assertTableHasBusinessScope($table);
        }
    }

    /**
     * 3. Assert businesses table schema definition and mandatory fields.
     */
    public function test_businesses_table_schema(): void
    {
        $this->assertTrue(
            Schema::hasTable('businesses'),
            "Primary [businesses] table must exist in database schema."
        );

        $requiredColumns = [
            'id',
            'name',
            'slug',
            'is_active',
            'currency',
        ];

        foreach ($requiredColumns as $col) {
            $this->assertTrue(
                Schema::hasColumn('businesses', $col),
                "Table [businesses] must contain mandatory column [{$col}]."
            );
        }
    }

    /**
     * 4. Assert domain models use BelongsToBusiness trait and declare business relationship.
     */
    public function test_models_use_belongs_to_business_trait(): void
    {
        $models = [
            Product::class,
            Location::class,
            Customer::class,
            Supplier::class,
            JournalEntry::class,
        ];

        foreach ($models as $modelClass) {
            $traits = class_uses_recursive($modelClass);
            $this->assertArrayHasKey(
                BelongsToBusiness::class,
                $traits,
                "Model [{$modelClass}] must use [App\\Models\\Traits\\BelongsToBusiness] trait."
            );
        }
    }

    /**
     * 5. Assert runtime tenant context manager (Context::setBusiness) binds and resolves correctly.
     */
    public function test_business_context_scoping(): void
    {
        $businessId = $this->getTenantBusinessId('cooca_mart');
        $this->assertNotNull($businessId, 'Tenant business [cooca_mart] must resolve to an ID.');

        $business = Business::find($businessId);
        $this->assertNotNull($business, 'Tenant business must exist in database.');

        Context::flush();
        $this->assertFalse(Context::hasBusiness(), 'Context must have no business after flush.');

        Context::setBusiness($business);
        $this->assertTrue(Context::hasBusiness(), 'Context must have active business after setBusiness.');
        $this->assertSame($business->id, Context::business()->id, 'Context::business() must return current tenant.');

        Context::flush();
    }
}
