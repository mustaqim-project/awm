<?php

declare(strict_types=1);

namespace Tests\E2E\Tier1_FeatureCoverage;

use App\Domain\Accounting\AutoJournalService;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\E2E\Support\BaseTestCase;

/**
 * Feature F12: Double-Entry Auto-Journaling Generation Test Suite
 *
 * Validates the SAK EMKM Chart of Accounts, journal entries, balanced lines,
 * debit === credit equations, and AutoJournalService architecture.
 */
final class F12_AutoJournalTest extends BaseTestCase
{
    /**
     * 1. Assert standard SAK EMKM Chart of Accounts (COA) is seeded and categorized.
     */
    public function test_chart_of_accounts_sak_emkm_seeded(): void
    {
        $this->assertTrue(Schema::hasTable('chart_of_accounts'), 'Table [chart_of_accounts] must exist.');

        $coaCount = DB::table('chart_of_accounts')->count();
        $this->assertGreaterThan(
            50,
            $coaCount,
            "Expected at least 50 standard SAK EMKM accounts, found {$coaCount}."
        );

        $types = DB::table('chart_of_accounts')->pluck('type')->unique()->all();
        $this->assertGreaterThanOrEqual(
            4,
            count($types),
            'Chart of accounts must contain primary account types (asset, liability, equity, revenue, expense).'
        );
    }

    /**
     * 2. Assert journal entries table is populated with transactional journal headers.
     */
    public function test_journal_entries_table_populated(): void
    {
        $this->assertTrue(Schema::hasTable('journal_entries'), 'Table [journal_entries] must exist.');

        $entryCount = DB::table('journal_entries')->count();
        $this->assertGreaterThanOrEqual(
            5,
            $entryCount,
            "Expected at least 5 seeded journal entries, found {$entryCount}."
        );
    }

    /**
     * 3. Assert journal entry lines exist and represent both debit and credit entries.
     */
    public function test_journal_entry_lines_have_debit_and_credit(): void
    {
        $this->assertTrue(Schema::hasTable('journal_entry_lines'), 'Table [journal_entry_lines] must exist.');

        $lineTypes = DB::table('journal_entry_lines')->pluck('type')->unique()->all();
        $this->assertContains('debit', $lineTypes, "Journal lines must include 'debit' line types.");
        $this->assertContains('credit', $lineTypes, "Journal lines must include 'credit' line types.");
    }

    /**
     * 4. Assert all seeded journal entries are perfectly balanced (debit === credit).
     */
    public function test_all_seeded_journal_entries_balanced(): void
    {
        $entries = DB::table('journal_entries')->get();
        $this->assertNotEmpty($entries, 'Journal entries must exist for balance verification.');

        foreach ($entries as $entry) {
            $this->assertJournalEntryBalanced($entry->id);
        }
    }

    /**
     * 5. Assert AutoJournalService domain service exists and is properly structured.
     */
    public function test_auto_journal_service_class_exists(): void
    {
        $this->assertTrue(
            class_exists(AutoJournalService::class),
            "Service class [App\\Domain\\Accounting\\AutoJournalService] must exist."
        );

        $ref = new \ReflectionClass(AutoJournalService::class);
        $this->assertFalse($ref->isAbstract(), "AutoJournalService must not be abstract.");
    }
}
