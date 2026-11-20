<?php

declare(strict_types=1);

namespace Tests\E2E\Support\Helpers;

use Illuminate\Support\Facades\DB;
use Throwable;

final class TransactionHelper
{
    /**
     * Execute a callback inside an isolated database transaction that is guaranteed
     * to be rolled back, preventing any state persistence.
     *
     * @template T
     * @param callable(): T $callback
     * @return T
     * @throws Throwable
     */
    public static function runIsolated(callable $callback): mixed
    {
        DB::beginTransaction();

        try {
            return $callback();
        } finally {
            try {
                DB::rollBack();
            } catch (Throwable) {
                // If already rolled back or broken, ensure level check
                while (DB::transactionLevel() > 0) {
                    try {
                        DB::rollBack();
                    } catch (Throwable) {
                        break;
                    }
                }
            }
        }
    }

    /**
     * Determine if a database transaction is currently open.
     */
    public static function isTransactionActive(): bool
    {
        return DB::transactionLevel() > 0;
    }

    /**
     * Ensure all open transactions are completely rolled back.
     */
    public static function rollbackAll(): void
    {
        while (DB::transactionLevel() > 0) {
            try {
                DB::rollBack();
            } catch (Throwable) {
                break;
            }
        }
    }
}
