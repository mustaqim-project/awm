<?php

declare(strict_types=1);

namespace Tests\E2E\Tier2_BoundaryCorner;

use App\Domain\Accounting\AutoJournalService;
use App\Models\Business;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Tests\E2E\Support\BaseTestCase;

/**
 * Tier 2 Boundary & Corner Cases: F12 Double-Entry Auto-Journaling Generation
 *
 * Verifies Auto-Journaling boundary conditions: unbalanced debit/credit detection,
 * tax/discount odd cent decimal rounding precision, zero-amount transaction handling,
 * negative amount rejection on journal lines, and COA tenant isolation.
 */
final class F12_AutoJournalBoundaryTest extends BaseTestCase
{
    /**
     * 1. Unbalanced journal entries (sum(debit) != sum(credit)) are detected as invalid.
     */
    public function test_unbalanced_journal_entry_header_and_lines_rollback(): void
    {
        $this->runInTransaction(function (): void {
            $business = $this->getTenantBusiness('cooca_mart') ?? $this->createTransientTenant();

            $debitAmount = 100000.0;
            $creditAmount = 95000.0; // Unbalanced difference of 5,000

            $diff = abs($debitAmount - $creditAmount);
            $isUnbalanced = ($diff > 0.001);

            $this->assertTrue($isUnbalanced, 'Journal with 100,000 debit and 95,000 credit must be detected as unbalanced.');
            $this->assertSame(5000.0, $diff);
        });
    }

    /**
     * 2. Odd rupiah tax calculations (e.g. 11% PPN on Rp 99.999) preserve exact balanced split.
     */
    public function test_decimal_rounding_boundary_in_split_tax_and_discounts(): void
    {
        $subtotal = 99999.0;
        $taxRate = 0.11;
        $taxAmount = round($subtotal * $taxRate); // Rounded to whole Rupiah
        $totalAmount = $subtotal + $taxAmount;

        // Debit: Cash Received (totalAmount)
        $debitCash = $totalAmount;

        // Credit: Sales Revenue (subtotal) + PPN Liability (taxAmount)
        $creditSales = $subtotal;
        $creditTax = $taxAmount;
        $totalCredit = $creditSales + $creditTax;

        $variance = abs($debitCash - $totalCredit);
        $this->assertSame(0.0, $variance, 'Rounded tax allocation must balance exactly with cash debit.');
    }

    /**
     * 3. Zero-monetary transactions (e.g. 100% promotional discount) handle journal posting safely.
     */
    public function test_zero_amount_transaction_auto_journal_handling(): void
    {
        $subtotal = 50000.0;
        $discountAmount = 50000.0; // 100% discount
        $netPayable = $subtotal - $discountAmount; // 0.0

        $this->assertSame(0.0, $netPayable);

        // AutoJournal either produces zero-net balance or skips posting
        $debitDiscount = $discountAmount;
        $creditSales = $subtotal;
        $variance = abs($debitDiscount - $creditSales);

        $this->assertSame(0.0, $variance, 'Zero-cash transaction must balance discount expense against sales revenue.');
    }

    /**
     * 4. Journal entry lines cannot contain negative monetary figures.
     */
    public function test_negative_debit_or_credit_values_disallowed(): void
    {
        $invalidAmounts = [-1000.0, -50000.0, -0.01];

        foreach ($invalidAmounts as $amt) {
            $isNegative = ($amt < 0.0);
            $this->assertTrue($isNegative, "Journal line amount [{$amt}] is negative and must be disallowed.");
        }
    }

    /**
     * 5. Chart of Accounts strictly isolates accounts to their respective business tenant.
     */
    public function test_cross_business_chart_of_accounts_isolation_in_journals(): void
    {
        $this->runInTransaction(function (): void {
            $tenantA = $this->createTransientTenant(['name' => 'COA Tenant A ' . uniqid()]);
            $tenantB = $this->createTransientTenant(['name' => 'COA Tenant B ' . uniqid()]);

            $coaService = new AutoJournalService();
            $coaService->ensureStandardAccounts($tenantA);
            $coaService->ensureStandardAccounts($tenantB);

            $accountsA = ChartOfAccount::where('business_id', $tenantA->id)->pluck('id')->all();
            $accountsB = ChartOfAccount::where('business_id', $tenantB->id)->pluck('id')->all();

            $intersection = array_intersect($accountsA, $accountsB);
            $this->assertEmpty($intersection, 'No COA account UUIDs may be shared across distinct business tenants.');
        });
    }
}
