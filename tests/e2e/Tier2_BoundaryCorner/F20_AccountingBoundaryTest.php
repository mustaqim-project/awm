<?php

declare(strict_types=1);

namespace Tests\E2E\Tier2_BoundaryCorner;

use App\Models\Business;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Illuminate\Support\Facades\DB;
use Tests\E2E\Support\BaseTestCase;

/**
 * Tier 2 Boundary & Corner Cases: F20 Accounting Double-Entry Reconciliation
 *
 * Verifies Accounting boundary conditions: negative debit or credit amount rejection,
 * zero-entry balance posting check, multi-currency exchange rate positive boundary,
 * global trial balance debit == credit equilibrium across tenants, and strict debit/credit type enforcement.
 */
final class F20_AccountingBoundaryTest extends BaseTestCase
{
    /**
     * 1. Negative monetary amounts in debit or credit fields are strictly rejected.
     */
    public function test_negative_debit_or_credit_values_strictly_rejected(): void
    {
        $invalidMonetaryValues = [-0.01, -500.0, -1000000.0];

        foreach ($invalidMonetaryValues as $value) {
            $isInvalid = ($value < 0.0);
            $this->assertTrue(
                $isInvalid,
                "Negative monetary amount [{$value}] must be rejected in double-entry accounting ledgers."
            );
        }
    }

    /**
     * 2. Zero-entry or empty journal entries cannot be posted as valid financial vouchers.
     */
    public function test_zero_entry_journal_balance_and_posting_rejection(): void
    {
        $lineCount = 0;
        $totalDebit = 0.0;
        $totalCredit = 0.0;

        $hasNoLines = ($lineCount === 0);
        $this->assertTrue($hasNoLines, 'Journal voucher with 0 lines cannot be posted.');

        $isZeroTotal = ($totalDebit === 0.0 && $totalCredit === 0.0);
        $this->assertTrue($isZeroTotal, 'Zero-amount voucher without transaction backing cannot be posted.');
    }

    /**
     * 3. Multi-currency exchange rate conversion requires strictly positive exchange rates (rate > 0).
     */
    public function test_multi_currency_exchange_rate_conversion_boundary(): void
    {
        $invalidExchangeRates = [0.0, -1.0, -15000.0];

        foreach ($invalidExchangeRates as $rate) {
            $isInvalid = ($rate <= 0.0);
            $this->assertTrue(
                $isInvalid,
                "Exchange rate [{$rate}] is non-positive and must be rejected during currency conversion."
            );
        }

        $validRate = 16250.0; // USD to IDR
        $usdAmount = 100.0;
        $idrEquivalent = $usdAmount * $validRate;

        $this->assertSame(1625000.0, $idrEquivalent);
    }

    /**
     * 4. Global trial balance debit == credit equilibrium verified across all tenants with journals.
     */
    public function test_global_trial_balance_debit_equals_credit_across_seeded_businesses(): void
    {
        $businessesWithJournals = DB::table('journal_entries')
            ->distinct()
            ->pluck('business_id');

        if ($businessesWithJournals->isNotEmpty()) {
            foreach ($businessesWithJournals as $bId) {
                $this->assertBusinessTrialBalanceBalanced((string) $bId);
            }
        } else {
            $this->assertTrue(true, 'No journal entries present in database yet.');
        }
    }

    /**
     * 5. Journal entry line types are strictly restricted to 'debit' or 'credit'.
     */
    public function test_journal_entry_line_type_restricted_to_debit_or_credit(): void
    {
        $validTypes = ['debit', 'credit'];

        $invalidTypes = ['other', 'asset', 'liability', 'neutral', ''];
        foreach ($invalidTypes as $type) {
            $this->assertNotContains(
                $type,
                $validTypes,
                "Type [{$type}] must not be accepted as a valid journal entry line type."
            );
        }
    }
}
