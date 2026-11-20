<?php

declare(strict_types=1);

namespace Tests\E2E\Support\Assertions;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Assert;

trait DatabaseAssertions
{
    /**
     * Assert that a table contains at least $minCount records scoped to a specific business ID.
     */
    public function assertTenantRecordCount(string $table, string $businessId, int $minCount = 1): void
    {
        Assert::assertTrue(
            Schema::hasTable($table),
            "Database table [{$table}] does not exist."
        );

        $tenantCol = ($table === 'businesses') ? 'id' : 'business_id';
        Assert::assertTrue(
            Schema::hasColumn($table, $tenantCol),
            "Database table [{$table}] does not have a [{$tenantCol}] column for multi-tenant scoping."
        );

        $actualCount = DB::table($table)
            ->where($tenantCol, $businessId)
            ->count();

        Assert::assertGreaterThanOrEqual(
            $minCount,
            $actualCount,
            "Table [{$table}] for tenant [{$businessId}] has {$actualCount} records, expected at least {$minCount}."
        );
    }

    /**
     * Assert that a record exists for a specific tenant matching conditions.
     *
     * @param array<string, mixed> $where
     */
    public function assertTenantRecordExists(string $table, string $businessId, array $where = []): void
    {
        Assert::assertTrue(
            Schema::hasTable($table),
            "Database table [{$table}] does not exist."
        );

        $tenantCol = ($table === 'businesses') ? 'id' : 'business_id';
        Assert::assertTrue(
            Schema::hasColumn($table, $tenantCol),
            "Database table [{$table}] does not have a [{$tenantCol}] column."
        );

        $query = DB::table($table)->where($tenantCol, $businessId);
        foreach ($where as $column => $value) {
            $query->where($column, $value);
        }

        Assert::assertTrue(
            $query->exists(),
            "Expected record matching conditions in table [{$table}] for tenant [{$businessId}] was not found."
        );
    }

    /**
     * Assert that no record exists for a specific tenant matching conditions.
     *
     * @param array<string, mixed> $where
     */
    public function assertTenantRecordMissing(string $table, string $businessId, array $where = []): void
    {
        Assert::assertTrue(
            Schema::hasTable($table),
            "Database table [{$table}] does not exist."
        );

        $tenantCol = ($table === 'businesses') ? 'id' : 'business_id';
        $query = DB::table($table)->where($tenantCol, $businessId);
        foreach ($where as $column => $value) {
            $query->where($column, $value);
        }

        Assert::assertFalse(
            $query->exists(),
            "Unexpected record found in table [{$table}] for tenant [{$businessId}]."
        );
    }

    /**
     * Assert that a table exists and contains the business_id column.
     */
    public function assertTableHasBusinessScope(string $table): void
    {
        Assert::assertTrue(
            Schema::hasTable($table),
            "Database table [{$table}] does not exist."
        );

        $tenantCol = ($table === 'businesses') ? 'id' : 'business_id';
        Assert::assertTrue(
            Schema::hasColumn($table, $tenantCol),
            "Database table [{$table}] does not have a [{$tenantCol}] column for multi-tenant scoping."
        );
    }

    /**
     * Assert that zero records in $table point to a record in $parentTable with a different business_id.
     */
    public function assertZeroCrossTenantForeignKeys(string $table, string $foreignKey, string $parentTable): void
    {
        Assert::assertTrue(
            Schema::hasTable($table),
            "Child table [{$table}] does not exist."
        );

        Assert::assertTrue(
            Schema::hasTable($parentTable),
            "Parent table [{$parentTable}] does not exist."
        );

        $childTenantCol = ($table === 'businesses') ? 'id' : 'business_id';
        $parentTenantCol = ($parentTable === 'businesses') ? 'id' : 'business_id';

        Assert::assertTrue(
            Schema::hasColumn($table, $childTenantCol),
            "Database table [{$table}] does not have a [{$childTenantCol}] column for multi-tenant scoping."
        );

        Assert::assertTrue(
            Schema::hasColumn($parentTable, $parentTenantCol),
            "Database table [{$parentTable}] does not have a [{$parentTenantCol}] column for multi-tenant scoping."
        );

        $leakageCount = DB::table("{$table} as t")
            ->join("{$parentTable} as p", "t.{$foreignKey}", '=', 'p.id')
            ->whereNotNull("t.{$childTenantCol}")
            ->whereNotNull("p.{$parentTenantCol}")
            ->whereColumn("t.{$childTenantCol}", '!=', "p.{$parentTenantCol}")
            ->count();

        Assert::assertSame(
            0,
            $leakageCount,
            "Detected {$leakageCount} cross-tenant foreign key violations between [{$table}.{$foreignKey}] and [{$parentTable}.id]."
        );
    }

    /**
     * Assert that zero orphan records exist in $childTable referencing non-existent records in $parentTable.
     */
    public function assertZeroOrphanRecords(string $childTable, string $foreignKey, string $parentTable): void
    {
        Assert::assertTrue(
            Schema::hasTable($childTable),
            "Child table [{$childTable}] does not exist."
        );

        Assert::assertTrue(
            Schema::hasTable($parentTable),
            "Parent table [{$parentTable}] does not exist."
        );

        $orphanCount = DB::table("{$childTable} as c")
            ->leftJoin("{$parentTable} as p", "c.{$foreignKey}", '=', 'p.id')
            ->whereNotNull("c.{$foreignKey}")
            ->whereNull('p.id')
            ->count();

        Assert::assertSame(
            0,
            $orphanCount,
            "Found {$orphanCount} orphan records in [{$childTable}] referencing non-existent [{$parentTable}.id] via foreign key [{$foreignKey}]."
        );
    }
}
