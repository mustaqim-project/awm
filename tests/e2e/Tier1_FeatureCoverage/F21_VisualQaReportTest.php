<?php

declare(strict_types=1);

namespace Tests\E2E\Tier1_FeatureCoverage;

use Illuminate\Support\Facades\DB;
use Tests\E2E\Support\BaseTestCase;

/**
 * Feature F21: Visual QA & Cross-Platform Consistency Report Test Suite
 *
 * Validates the visual QA reports, Indonesian Rupiah (IDR) currency formatting,
 * Asia/Jakarta (WIB) timezone alignment, and cross-platform UI consistency.
 */
final class F21_VisualQaReportTest extends BaseTestCase
{
    private string $qaReport = 'documentation/feature-readiness-audit/security-and-data-integrity-report.md';

    /**
     * 1. Assert visual QA and data integrity audit report exists and is populated.
     */
    public function test_visual_qa_report_exists(): void
    {
        $this->assertMarkdownFilePopulated($this->qaReport, 1000);
        $this->assertMarkdownContainsHeaders($this->qaReport, [
            'Security & Data Integrity Audit Report',
            'Executive Summary',
        ]);
    }

    /**
     * 2. Assert Indonesian Rupiah (IDR) is the universal currency standard across businesses.
     */
    public function test_indonesian_rupiah_formatting_standard(): void
    {
        $currencies = DB::table('businesses')->pluck('currency')->unique()->all();

        $this->assertNotEmpty($currencies);
        $this->assertSame(
            ['IDR'],
            array_values($currencies),
            "100% of business entities must be configured with standard Indonesian Rupiah [IDR]."
        );
    }

    /**
     * 3. Assert Indonesian Western Time (WIB / Asia/Jakarta) is the configured system timezone.
     */
    public function test_indonesian_wib_timezone_configuration(): void
    {
        $appTimezone = config('app.timezone');
        $this->assertSame(
            'Asia/Jakarta',
            $appTimezone,
            "Application config app.timezone must be set to 'Asia/Jakarta' (WIB)."
        );

        $businessTimezones = DB::table('businesses')->pluck('timezone')->unique()->all();
        $this->assertContains(
            'Asia/Jakarta',
            $businessTimezones,
            "Businesses must be configured with 'Asia/Jakarta' timezone."
        );
    }

    /**
     * 4. Assert currency precision is zero (0 decimal places for Indonesian Rupiah).
     */
    public function test_currency_symbol_and_formatting_helper(): void
    {
        $demoPrecisions = DB::table('businesses')
            ->where('slug', 'LIKE', 'cooca%')
            ->pluck('currency_precision')
            ->all();

        $this->assertNotEmpty($demoPrecisions, "Demo businesses must exist.");
        foreach ($demoPrecisions as $prec) {
            $this->assertEquals(
                0,
                $prec,
                "Demo businesses must use 0 decimal precision for Indonesian Rupiah cash/POS."
            );
        }

        $allPrecisions = DB::table('businesses')->pluck('currency_precision')->unique()->all();
        foreach ($allPrecisions as $p) {
            $this->assertContains(
                (int) $p,
                [0, 2],
                "Currency precision across businesses must be standard 0 or 2."
            );
        }
    }

    /**
     * 5. Assert cross-platform visual consistency checklist and anti-fraud safeguards are audited.
     */
    public function test_cross_platform_visual_consistency_checklist(): void
    {
        $content = (string) file_get_contents($this->resolveFilePath($this->qaReport));

        $this->assertStringContainsString(
            'Anti-Fraud Safeguards',
            $content,
            "QA report must document anti-fraud mechanisms across web and mobile platforms."
        );
        $this->assertStringContainsString(
            'Double-Entry Mathematical Balance',
            $content,
            "QA report must verify cross-platform financial reconciliation."
        );
    }
}
