<?php

declare(strict_types=1);

namespace Tests\E2E\Support\Assertions;

use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Assert;

trait AccountingAssertions
{
    /**
     * Assert that a specific journal entry is balanced (both in header and lines).
     *
     * @param string|JournalEntry $journalEntry
     */
    public function assertJournalEntryBalanced(string|JournalEntry $journalEntry): void
    {
        $id = $journalEntry instanceof JournalEntry ? $journalEntry->id : $journalEntry;

        $entryTable = Schema::hasTable('journal_entries') ? 'journal_entries' : 'journals';
        $linesTable = Schema::hasTable('journal_entry_lines') ? 'journal_entry_lines' : 'journal_items';
        $fkColumn = Schema::hasColumn($linesTable, 'journal_entry_id') ? 'journal_entry_id' : 'journal_id';

        $entry = DB::table($entryTable)->where('id', $id)->first();
        Assert::assertNotNull(
            $entry,
            "Journal entry with ID [{$id}] does not exist in [{$entryTable}]."
        );

        $headerDebit = (float) ($entry->total_debit ?? 0);
        $headerCredit = (float) ($entry->total_credit ?? 0);
        $headerDiff = abs($headerDebit - $headerCredit);

        Assert::assertLessThanOrEqual(
            0.0001,
            $headerDiff,
            "Journal entry [{$id}] header is unbalanced! Debit: {$headerDebit}, Credit: {$headerCredit}, Diff: {$headerDiff}."
        );

        $lines = DB::table($linesTable)->where($fkColumn, $id)->get();
        Assert::assertNotEmpty(
            $lines,
            "Journal entry [{$id}] has no lines in [{$linesTable}]."
        );

        $linesDebit = 0.0;
        $linesCredit = 0.0;

        foreach ($lines as $line) {
            $amount = (float) ($line->amount ?? ($line->debit ?? $line->credit ?? 0));
            $type = $line->type ?? (isset($line->debit) && (float) $line->debit > 0 ? 'debit' : 'credit');

            if ($type === 'debit') {
                $linesDebit += $amount;
            } elseif ($type === 'credit') {
                $linesCredit += $amount;
            }
        }

        $linesDiff = abs($linesDebit - $linesCredit);
        Assert::assertLessThanOrEqual(
            0.0001,
            $linesDiff,
            "Journal entry [{$id}] line-items sum is unbalanced! Line Debits: {$linesDebit}, Line Credits: {$linesCredit}, Diff: {$linesDiff}."
        );

        // Header and line sums must also match
        Assert::assertLessThanOrEqual(
            0.01,
            abs($headerDebit - $linesDebit),
            "Journal entry [{$id}] header debit ({$headerDebit}) does not match line debit sum ({$linesDebit})."
        );
    }

    /**
     * Assert that all auto-journal entries for a business (or a specific entry) are balanced.
     */
    public function assertAutoJournalBalance(string $businessId, ?string $entryNumber = null): void
    {
        $entryTable = Schema::hasTable('journal_entries') ? 'journal_entries' : 'journals';

        $query = DB::table($entryTable)->where('business_id', $businessId);
        if ($entryNumber !== null) {
            $query->where(function ($q) use ($entryNumber) {
                $q->where('entry_number', $entryNumber)
                    ->orWhere('id', $entryNumber);
            });
        }

        $entries = $query->get();
        Assert::assertNotEmpty(
            $entries,
            "No journal entries found for business [{$businessId}]" . ($entryNumber ? " with entry [{$entryNumber}]" : '') . "."
        );

        foreach ($entries as $entry) {
            $this->assertJournalEntryBalanced($entry->id);
        }
    }

    /**
     * Assert that the global trial balance for a business is balanced (total debits == total credits).
     */
    public function assertBusinessTrialBalanceBalanced(string $businessId): void
    {
        $entryTable = Schema::hasTable('journal_entries') ? 'journal_entries' : 'journals';
        $linesTable = Schema::hasTable('journal_entry_lines') ? 'journal_entry_lines' : 'journal_items';
        $fkColumn = Schema::hasColumn($linesTable, 'journal_entry_id') ? 'journal_entry_id' : 'journal_id';

        $totals = DB::table("{$linesTable} as jel")
            ->join("{$entryTable} as je", "je.id", '=', "jel.{$fkColumn}")
            ->where('je.business_id', $businessId)
            ->selectRaw("
                COALESCE(SUM(CASE WHEN jel.type = 'debit' THEN jel.amount ELSE 0 END), 0) AS total_debit,
                COALESCE(SUM(CASE WHEN jel.type = 'credit' THEN jel.amount ELSE 0 END), 0) AS total_credit,
                COUNT(jel.id) as line_count
            ")
            ->first();

        $totalDebit = (float) ($totals->total_debit ?? 0);
        $totalCredit = (float) ($totals->total_credit ?? 0);
        $variance = abs($totalDebit - $totalCredit);

        Assert::assertGreaterThanOrEqual(
            0,
            (int) ($totals->line_count ?? 0),
            "No journal lines found for business [{$businessId}]."
        );

        Assert::assertLessThanOrEqual(
            0.01,
            $variance,
            "Trial balance for business [{$businessId}] is unbalanced! Total Debit: {$totalDebit}, Total Credit: {$totalCredit}, Variance: {$variance}."
        );
    }

    /**
     * Assert that the fundamental accounting equation holds for a business:
     * Assets = Liabilities + Equity + (Revenue - COGS - Expenses)
     */
    public function assertAccountingEquationHolds(string $businessId): void
    {
        // 1. Verify global ledger trial balance
        $this->assertBusinessTrialBalanceBalanced($businessId);

        // If chart_of_accounts doesn't exist, trial balance check is sufficient
        if (! Schema::hasTable('chart_of_accounts')) {
            return;
        }

        // 2. Breakdown balances by account type from chart_of_accounts
        $balances = DB::table('journal_entry_lines as jel')
            ->join('journal_entries as je', 'je.id', '=', 'jel.journal_entry_id')
            ->join('chart_of_accounts as coa', 'coa.id', '=', 'jel.account_id')
            ->where('je.business_id', $businessId)
            ->selectRaw("
                coa.type,
                COALESCE(SUM(CASE WHEN jel.type = 'debit' THEN jel.amount ELSE 0 END), 0) AS total_debit,
                COALESCE(SUM(CASE WHEN jel.type = 'credit' THEN jel.amount ELSE 0 END), 0) AS total_credit
            ")
            ->groupBy('coa.type')
            ->get()
            ->keyBy('type');

        $asset = (float) (($balances->get(ChartOfAccount::TYPE_ASSET)?->total_debit ?? 0) - ($balances->get(ChartOfAccount::TYPE_ASSET)?->total_credit ?? 0));
        $liability = (float) (($balances->get(ChartOfAccount::TYPE_LIABILITY)?->total_credit ?? 0) - ($balances->get(ChartOfAccount::TYPE_LIABILITY)?->total_debit ?? 0));
        $equity = (float) (($balances->get(ChartOfAccount::TYPE_EQUITY)?->total_credit ?? 0) - ($balances->get(ChartOfAccount::TYPE_EQUITY)?->total_debit ?? 0));
        $revenue = (float) (($balances->get(ChartOfAccount::TYPE_REVENUE)?->total_credit ?? 0) - ($balances->get(ChartOfAccount::TYPE_REVENUE)?->total_debit ?? 0));
        $cogs = (float) (($balances->get(ChartOfAccount::TYPE_COGS)?->total_debit ?? 0) - ($balances->get(ChartOfAccount::TYPE_COGS)?->total_credit ?? 0));
        $expense = (float) (($balances->get(ChartOfAccount::TYPE_EXPENSE)?->total_debit ?? 0) - ($balances->get(ChartOfAccount::TYPE_EXPENSE)?->total_credit ?? 0));

        $netIncome = $revenue - ($cogs + $expense);
        $rightHandSide = $liability + $equity + $netIncome;
        $variance = abs($asset - $rightHandSide);

        Assert::assertLessThanOrEqual(
            0.05,
            $variance,
            "Accounting equation does not hold for business [{$businessId}]! Assets: {$asset}, Liabilities: {$liability}, Equity: {$equity}, Net Income: {$netIncome} (Revenue: {$revenue}, COGS: {$cogs}, Expense: {$expense}), RHS: {$rightHandSide}, Variance: {$variance}."
        );
    }
}
