<?php

declare(strict_types=1);

namespace Tests\E2E\Tier1_FeatureCoverage;

use Illuminate\Support\Facades\DB;
use Tests\E2E\Support\BaseTestCase;

/**
 * Feature F20: Accounting Double-Entry Reconciliation Test Suite
 *
 * Validates the global trial balance reconciliation, SAK EMKM debit === credit parity,
 * business-level trial balances, and line-level double-entry accounting integrity.
 */
final class F20_AccountingReconcileTest extends BaseTestCase
{
    /**
     * 1. Assert global ledger trial balance is perfectly balanced (SUM(debit) === SUM(credit)).
     */
    public function test_global_trial_balance_debit_equals_credit(): void
    {
        $totals = DB::table('journal_entry_lines')
            ->selectRaw("
                COALESCE(SUM(CASE WHEN type = 'debit' THEN amount ELSE 0 END), 0) AS total_debit,
                COALESCE(SUM(CASE WHEN type = 'credit' THEN amount ELSE 0 END), 0) AS total_credit
            ")
            ->first();

        $debit = (float) ($totals->total_debit ?? 0);
        $credit = (float) ($totals->total_credit ?? 0);
        $diff = abs($debit - $credit);

        $this->assertGreaterThan(0, $debit, "Total global debits must be greater than zero.");
        $this->assertLessThanOrEqual(
            0.01,
            $diff,
            "Global trial balance is unbalanced! Debits: {$debit}, Credits: {$credit}, Diff: {$diff}."
        );
    }

    /**
     * 2. Assert retail business (COOCA Mart) trial balance is balanced.
     */
    public function test_retail_business_accounting_balanced(): void
    {
        $businessId = $this->getTenantBusinessId('cooca_mart');
        $this->assertNotNull($businessId);

        $this->assertBusinessTrialBalanceBalanced($businessId);
    }

    /**
     * 3. Assert all seeded businesses with journal entries maintain balanced ledgers.
     */
    public function test_fnb_or_seeded_businesses_trial_balance_balanced(): void
    {
        $businessesWithJournals = DB::table('journal_entries')
            ->pluck('business_id')
            ->unique()
            ->all();

        $this->assertNotEmpty($businessesWithJournals);

        foreach ($businessesWithJournals as $bId) {
            $this->assertBusinessTrialBalanceBalanced((string) $bId);
        }
    }

    /**
     * 4. Assert individual journal entry headers and lines have zero variance.
     */
    public function test_individual_journal_entries_balanced(): void
    {
        $entries = DB::table('journal_entries')->get();
        $this->assertNotEmpty($entries);

        foreach ($entries as $entry) {
            $this->assertJournalEntryBalanced($entry->id);
        }
    }

    /**
     * 5. Assert the fundamental accounting equation holds for tenant ledgers.
     */
    public function test_accounting_equation_holds_for_active_business(): void
    {
        $businessId = $this->getTenantBusinessId('cooca_mart');
        $this->assertNotNull($businessId);

        $this->assertAccountingEquationHolds($businessId);
    }
}
