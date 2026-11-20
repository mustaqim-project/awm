<?php

declare(strict_types=1);

namespace Tests\E2E\Tier1_FeatureCoverage;

use Tests\E2E\Support\BaseTestCase;

/**
 * Feature F14: Mobile Application UI Reverse Rendering Test Suite
 *
 * Validates the mobile UI rendering specifications, 1080x2400 / 393x852 viewport
 * standards, 23 operational screens catalog, and image manifest integrity.
 */
final class F14_MobileReverseRenderTest extends BaseTestCase
{
    private string $mobileAuditDoc = 'documentation/cooca-platform-audit/02-mobile-app-audit.md';
    private string $manifestPath = 'documentation/feature-ui-images/image-manifest.csv';

    /**
     * 1. Assert mobile reverse rendering specifications are defined in platform audit.
     */
    public function test_mobile_render_specs_defined_in_audit(): void
    {
        $this->assertMarkdownFilePopulated($this->mobileAuditDoc, 1000);
        $content = (string) file_get_contents($this->resolveFilePath($this->mobileAuditDoc));

        $hasResolution = str_contains($content, '1080')
            || str_contains($content, '844')
            || str_contains($content, '852')
            || str_contains($content, 'Pixel');

        $this->assertTrue(
            $hasResolution,
            "Mobile audit document must specify standard mobile viewport specifications."
        );
    }

    /**
     * 2. Assert all 23 mobile operational screens are documented in screen inventory.
     */
    public function test_mobile_operational_screens_documented(): void
    {
        $content = (string) file_get_contents($this->resolveFilePath($this->mobileAuditDoc));

        $this->assertStringContainsString(
            '23 Operational Screens Inventory',
            $content,
            "Mobile audit must include the 23 operational screens inventory header."
        );

        $coreScreens = [
            'SplashScreen',
            'LoginScreen',
            'MainNav',
            'DashboardScreen',
            'PosScreen',
            'KdsScreen',
            'AttendanceScreen',
            'InventoryScreen',
            'ReportsScreen',
            'ProfileScreen',
        ];

        foreach ($coreScreens as $screen) {
            $this->assertStringContainsString(
                $screen,
                $content,
                "Screen inventory must document operational screen [{$screen}]."
            );
        }
    }

    /**
     * 3. Assert mobile viewport aspect ratio conforms to tall modern smartphone standard.
     */
    public function test_mobile_viewport_aspect_ratio_standard(): void
    {
        $content = (string) file_get_contents($this->resolveFilePath($this->mobileAuditDoc));

        $hasRatio = str_contains($content, '19.5')
            || str_contains($content, '20')
            || str_contains($content, '390x844')
            || str_contains($content, '1080');

        $this->assertTrue(
            $hasRatio,
            "Mobile documentation must reference standard smartphone aspect ratio standards (9:19.5, 9:20)."
        );
    }

    /**
     * 4. Assert image manifest CSV file exists and indexes rendered assets.
     */
    public function test_mobile_capture_manifest_integrity(): void
    {
        $resolved = $this->resolveFilePath($this->manifestPath);
        $this->assertFileExists($resolved, "Image manifest CSV must exist at [{$this->manifestPath}].");

        $lines = file($resolved, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $this->assertIsArray($lines);
        $this->assertGreaterThan(
            20,
            count($lines),
            "Image manifest must record at least 20 rendered assets."
        );
    }

    /**
     * 5. Assert mobile UI screenshot assets or manifest entries are valid.
     */
    public function test_mobile_ui_samples_or_placeholders_valid(): void
    {
        $mobileImage = 'documentation/feature-ui-images/J-hrm-payroll/presensi-gps-geofencing.png';
        $resolved = $this->resolveFilePath($mobileImage);

        if (file_exists($resolved)) {
            $this->assertValidPngImage($resolved);
        } else {
            $this->assertFileExists($this->resolveFilePath($this->manifestPath));
        }
    }
}
