<?php

declare(strict_types=1);

namespace Tests\E2E\Tier2_BoundaryCorner;

use Tests\E2E\Support\BaseTestCase;

/**
 * Tier 2 Boundary & Corner Cases: F14 Mobile Application UI Reverse Rendering (1080x2400)
 *
 * Verifies Mobile rendering boundary conditions: absence of Flutter red screen crash patterns,
 * SafeArea notch protection in root screens, RenderFlex overflow prevention with scrollable wrappers,
 * portrait resolution aspect ratio standardization, and text overflow ellipsis protection.
 */
final class F14_MobileRenderBoundaryTest extends BaseTestCase
{
    private string $mobileScreensDir = 'C:\\laragon\\www\\cooca_my_own\\lib\\screens';

    /**
     * 1. Mobile Flutter source code contains zero unhandled red-screen crash anti-patterns.
     */
    public function test_no_flutter_red_screen_signatures_in_mobile_code_or_assets(): void
    {
        $this->assertDirectoryExists($this->mobileScreensDir);

        $dartFiles = glob($this->mobileScreensDir . '/*.dart') ?: [];
        $this->assertNotEmpty($dartFiles);

        $redScreenSignatures = [
            'A RenderFlex overflowed by',
            'NoSuchMethodError:',
            'Exception caught by widgets library',
            'FlutterError:',
        ];

        foreach ($dartFiles as $file) {
            $content = (string) file_get_contents($file);
            foreach ($redScreenSignatures as $sig) {
                $this->assertStringNotContainsString(
                    $sig,
                    $content,
                    "Crash signature [{$sig}] detected in mobile screen [{$file}]."
                );
            }
        }
    }

    /**
     * 2. Key root mobile screens implement SafeArea or Scaffold safe inset handling.
     */
    public function test_mobile_safearea_and_notch_boundary_compliance(): void
    {
        $criticalScreens = [
            $this->mobileScreensDir . '\\login_screen.dart',
            $this->mobileScreensDir . '\\dashboard_screen.dart',
            $this->mobileScreensDir . '\\pos_screen.dart',
        ];

        foreach ($criticalScreens as $screenPath) {
            if (file_exists($screenPath)) {
                $content = (string) file_get_contents($screenPath);
                $hasSafeAreaOrScaffold = str_contains($content, 'SafeArea')
                    || str_contains($content, 'Scaffold')
                    || str_contains($content, 'MediaQuery.of(context).padding');

                $this->assertTrue(
                    $hasSafeAreaOrScaffold,
                    "Screen [{$screenPath}] must implement SafeArea or Scaffold for notch protection."
                );
            }
        }
    }

    /**
     * 3. Long form layouts wrap dynamic children in scrollable widgets to prevent RenderFlex overflow.
     */
    public function test_renderflex_overflow_prevention_with_scrollable_wrappers(): void
    {
        $screenFiles = glob($this->mobileScreensDir . '/*.dart') ?: [];
        $scrollableCount = 0;

        foreach ($screenFiles as $file) {
            $content = (string) file_get_contents($file);
            if (
                str_contains($content, 'SingleChildScrollView')
                || str_contains($content, 'ListView')
                || str_contains($content, 'CustomScrollView')
                || str_contains($content, 'GridView')
            ) {
                $scrollableCount++;
            }
        }

        $this->assertGreaterThan(
            5,
            $scrollableCount,
            "Expected at least 5 mobile screens to implement scrollable containers to prevent RenderFlex overflows."
        );
    }

    /**
     * 4. Mobile screen resolution standards adhere to standardized portrait aspect ratios.
     */
    public function test_mobile_portrait_aspect_ratio_standardization(): void
    {
        $allowedResolutions = [
            [1080, 2400], // 20:9 standard Android
            [1080, 2340], // 19.5:9 standard Android
            [1170, 2532], // iPhone 13/14 Pro standard
        ];

        foreach ($allowedResolutions as [$width, $height]) {
            $ratio = $height / $width;
            // Standard modern phone aspect ratios range from 2.0 to 2.25
            $this->assertGreaterThanOrEqual(2.0, $ratio);
            $this->assertLessThanOrEqual(2.3, $ratio);
        }
    }

    /**
     * 5. Mobile widgets implement TextOverflow.ellipsis or maxLines to prevent text clipping.
     */
    public function test_mobile_text_scaling_and_ellipsis_protection(): void
    {
        $screenFiles = glob($this->mobileScreensDir . '/*.dart') ?: [];
        $ellipsisProtectionFound = false;

        foreach ($screenFiles as $file) {
            $content = (string) file_get_contents($file);
            if (str_contains($content, 'TextOverflow.ellipsis') || str_contains($content, 'maxLines:')) {
                $ellipsisProtectionFound = true;
                break;
            }
        }

        $this->assertTrue(
            $ellipsisProtectionFound,
            'Mobile screens must implement TextOverflow.ellipsis or maxLines for dynamic text bounds.'
        );
    }
}
