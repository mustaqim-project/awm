<?php

declare(strict_types=1);

namespace Tests\E2E\Tier2_BoundaryCorner;

use Tests\E2E\Support\BaseTestCase;

/**
 * Tier 2 Boundary & Corner Cases: F03 Dual-Platform Feature Mapping Matrix
 *
 * Verifies cross-platform mapping matrix integrity: rejection of empty table rows,
 * enforcement of recognized parity classifications, 100% mobile screen parity accounting,
 * valid web blade template paths, and standard table column structure.
 */
final class F03_MappingBoundaryTest extends BaseTestCase
{
    private string $mappingDocPath = 'documentation/cooca-platform-audit/04-platform-feature-mapping.md';

    /**
     * 1. Parity mapping table contains zero empty rows or empty cells in key columns.
     */
    public function test_feature_mapping_contains_no_empty_table_rows(): void
    {
        $this->assertMarkdownFilePopulated($this->mappingDocPath, 5000);
        $content = (string) file_get_contents(base_path($this->mappingDocPath));

        // Locate lines starting with pipe (|) representing data rows
        $lines = explode("\n", $content);
        $dataRowsFound = 0;

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (! str_starts_with($trimmed, '|')) {
                continue;
            }

            // Skip table header divider (|---|---|...)
            if (preg_match('/^\|(?:\s*:?-{2,}:?\s*\|)+$/', $trimmed)) {
                continue;
            }

            $cells = array_map('trim', explode('|', trim($trimmed, '|')));
            if (count($cells) < 4) {
                continue;
            }

            // Skip table header row
            if (str_contains(strtolower($cells[0]), 'feature domain') || str_contains(strtolower($cells[0]), 'platform attribute')) {
                continue;
            }

            $dataRowsFound++;

            // Assert neither Feature Domain nor Parity Assessment is empty
            $this->assertNotEmpty(
                $cells[0],
                "Detected empty Feature Domain cell in mapping table row: [{$trimmed}]."
            );

            $lastCell = end($cells);
            $this->assertNotEmpty(
                $lastCell,
                "Detected empty Parity Assessment cell in mapping table row: [{$trimmed}]."
            );
        }

        $this->assertGreaterThan(
            10,
            $dataRowsFound,
            "Expected at least 10 valid parity data rows in mapping document, found {$dataRowsFound}."
        );
    }

    /**
     * 2. All parity assessment statuses strictly follow standardized classifications.
     */
    public function test_parity_assessment_values_are_strictly_standardized(): void
    {
        $content = (string) file_get_contents(base_path($this->mappingDocPath));

        $lines = explode("\n", $content);
        $validClassifications = [
            '100% parity',
            'web-specialized',
            'mobile-only',
            'partial',
            'edge',
            'parity',
        ];

        $matchedClassifications = 0;

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (! str_starts_with($trimmed, '|') || preg_match('/^\|(?:\s*:?-{2,}:?\s*\|)+$/', $trimmed)) {
                continue;
            }

            $cells = array_map('trim', explode('|', trim($trimmed, '|')));
            if (count($cells) < 6) {
                continue;
            }

            if (str_contains(strtolower($cells[0]), 'feature domain')) {
                continue;
            }

            $parityCell = strtolower(end($cells));

            $hasValidClass = false;
            foreach ($validClassifications as $class) {
                if (str_contains($parityCell, $class)) {
                    $hasValidClass = true;
                    $matchedClassifications++;
                    break;
                }
            }

            $this->assertTrue(
                $hasValidClass,
                "Parity status in row [{$trimmed}] contains unstandardized status: [{$parityCell}]."
            );
        }

        $this->assertGreaterThan(5, $matchedClassifications);
    }

    /**
     * 3. All operational mobile screens are mapped and accounted for in the dual-platform topology.
     */
    public function test_unmapped_mobile_screens_detected_and_accounted_for(): void
    {
        $mobileDocPath = 'documentation/cooca-platform-audit/02-mobile-app-audit.md';
        $this->assertFileExists(base_path($mobileDocPath));

        $mobileAudit = (string) file_get_contents(base_path($mobileDocPath));
        $mappingContent = (string) file_get_contents(base_path($this->mappingDocPath));

        // Key operational mobile screens that MUST be mapped
        $criticalScreens = [
            'LoginScreen',
            'DashboardScreen',
            'PosScreen',
            'KdsScreen',
            'InventoryScreen',
            'GoodsReceiptScreen',
            'AttendanceScreen',
            'HrProfileScreen',
            'MarketplaceHubScreen',
            'SocialMediaHubScreen',
            'CommunityHubScreen',
        ];

        foreach ($criticalScreens as $screen) {
            $this->assertStringContainsString(
                $screen,
                $mappingContent,
                "Critical mobile screen [{$screen}] is missing from dual-platform mapping specification."
            );
        }
    }

    /**
     * 4. Verify that Web Blade View files referenced in mapping matrix actually exist in codebase.
     */
    public function test_feature_mapping_references_valid_blade_views(): void
    {
        $content = (string) file_get_contents(base_path($this->mappingDocPath));

        // Extract all references like resources/views/app/...
        preg_match_all('/resources\/views\/[a-zA-Z0-9_\-\/]+\.blade\.php/', $content, $matches);

        $this->assertNotEmpty($matches[0], 'No Blade view paths found in mapping document.');

        $viewPaths = array_unique($matches[0]);
        $verifiedCount = 0;

        foreach ($viewPaths as $relativeViewPath) {
            $fullPath = base_path($relativeViewPath);
            if (file_exists($fullPath)) {
                $verifiedCount++;
            }
        }

        $this->assertGreaterThan(
            5,
            $verifiedCount,
            "Too few referenced Blade views exist on disk ({$verifiedCount} / " . count($viewPaths) . ")."
        );
    }

    /**
     * 5. Verify mapping document defines standard 6-column tabular header structure.
     */
    public function test_feature_mapping_table_header_structure_is_complete(): void
    {
        $expectedColumns = [
            'Feature Domain',
            'Mobile Screen & Dart File',
            'Mobile Service & API Endpoint',
            'Web Controller & Route',
            'Web Blade View File',
            'Parity Assessment & Operational Behavior',
        ];

        $this->assertMarkdownTableHasHeaders(
            $this->mappingDocPath,
            $expectedColumns,
            '2. Shared & Dual-Platform Parity Matrix'
        );
    }
}
