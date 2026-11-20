<?php

declare(strict_types=1);

namespace Tests\E2E\Tier1_FeatureCoverage;

use Tests\E2E\Support\BaseTestCase;

/**
 * Feature F02: Mobile Screen & Navigation Audit Test Suite
 *
 * Validates the mobile screen catalog, Flutter routes, state management architecture,
 * hardware bridges, and offline synchronization specifications.
 */
final class F02_MobileScreenAuditTest extends BaseTestCase
{
    private string $auditDoc = 'documentation/cooca-platform-audit/02-mobile-app-audit.md';

    /**
     * 1. Assert mobile app technical audit documentation file is populated and structured.
     */
    public function test_mobile_screen_catalog_documentation(): void
    {
        $this->assertMarkdownFilePopulated($this->auditDoc, 1000);
        $this->assertMarkdownContainsHeaders($this->auditDoc, [
            'Mobile Application Technical Audit',
            'Screen & Navigation Audit',
        ]);
    }

    /**
     * 2. Assert Flutter routes and 23 operational screens are systematically cataloged.
     */
    public function test_flutter_routes_catalog_structure(): void
    {
        $this->assertMarkdownTableNonEmpty($this->auditDoc);
        $this->assertMarkdownTableHasHeaders(
            $this->auditDoc,
            ['Screen Name', 'File Path', 'Functional Purpose'],
            'Operational Screens Inventory'
        );

        $content = (string) file_get_contents($this->resolveFilePath($this->auditDoc));
        $requiredScreens = [
            'SplashScreen',
            'LoginScreen',
            'MainNav',
            'DashboardScreen',
            'PosScreen',
            'KdsScreen',
            'AttendanceScreen',
            'InventoryScreen',
        ];

        foreach ($requiredScreens as $screen) {
            $this->assertStringContainsString(
                $screen,
                $content,
                "Mobile audit document must catalog screen: [{$screen}]."
            );
        }
    }

    /**
     * 3. Assert central state management architecture (MerchantState ChangeNotifier) is audited.
     */
    public function test_state_management_architecture(): void
    {
        $content = (string) file_get_contents($this->resolveFilePath($this->auditDoc));

        $this->assertStringContainsString(
            'MerchantState',
            $content,
            "Mobile audit document must document central state class [MerchantState]."
        );
        $this->assertStringContainsString(
            'ChangeNotifier',
            $content,
            "Mobile audit document must document ChangeNotifier reactive pattern."
        );
    }

    /**
     * 4. Assert hardware integration bridges (thermal printer, barcode camera, GPS) are documented.
     */
    public function test_hardware_bridges_catalog(): void
    {
        $content = (string) file_get_contents($this->resolveFilePath($this->auditDoc));

        $this->assertStringContainsString(
            'Thermal',
            $content,
            "Mobile audit must document Thermal Printer ESC/POS hardware bridge."
        );
        $this->assertStringContainsString(
            'Scanner',
            $content,
            "Mobile audit must document Camera Barcode Scanner bridge."
        );
        $this->assertStringContainsString(
            'GPS',
            $content,
            "Mobile audit must document GPS Geofencing radar bridge."
        );
    }

    /**
     * 5. Assert offline queue and transaction synchronization mechanisms are defined.
     */
    public function test_mobile_offline_sync_spec(): void
    {
        $content = (string) file_get_contents($this->resolveFilePath($this->auditDoc));

        $this->assertTrue(
            str_contains($content, 'SQLite') || str_contains($content, 'Offline'),
            "Mobile audit must document offline storage queue or SQLite synchronization."
        );
        $this->assertStringContainsString(
            'UUID',
            $content,
            "Mobile audit must specify UUID idempotency for offline transaction sync."
        );
    }
}
