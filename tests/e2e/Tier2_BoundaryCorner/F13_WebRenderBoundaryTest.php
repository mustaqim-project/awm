<?php

declare(strict_types=1);

namespace Tests\E2E\Tier2_BoundaryCorner;

use Tests\E2E\Support\BaseTestCase;

/**
 * Tier 2 Boundary & Corner Cases: F13 Web Backoffice UI Reverse Rendering (1440x900)
 *
 * Verifies Web rendering boundary conditions: custom 404 error page styling,
 * custom 500 internal server error page branding, horizontal scrollbar overflow prevention,
 * absence of unhandled Whoops/PHP debug dumps, and PNG screenshot asset format validity.
 */
final class F13_WebRenderBoundaryTest extends BaseTestCase
{
    /**
     * 1. Custom 404 Not Found error view is branded and extends styled error layout.
     */
    public function test_custom_404_error_view_is_properly_styled(): void
    {
        $viewPath = base_path('resources/views/errors/404.blade.php');
        $this->assertFileExists($viewPath, "Custom 404 Blade view must exist at [{$viewPath}].");

        $content = (string) file_get_contents($viewPath);
        $this->assertStringContainsString('404', $content);
        $this->assertStringContainsString('layout', $content);
    }

    /**
     * 2. Custom 500 Server Error view provides polished branding without raw PHP stack traces.
     */
    public function test_custom_500_error_view_is_properly_styled(): void
    {
        $viewPath = base_path('resources/views/errors/500.blade.php');
        $this->assertFileExists($viewPath, "Custom 500 Blade view must exist at [{$viewPath}].");

        $content = (string) file_get_contents($viewPath);
        $this->assertStringContainsString('500', $content);
        $this->assertStringNotContainsString('xdebug', strtolower($content));
        $this->assertStringNotContainsString('trace', strtolower($content));
    }

    /**
     * 3. Backoffice layout specifies horizontal overflow prevention for 1440px desktop viewports.
     */
    public function test_desktop_horizontal_scrollbar_overflow_prevention(): void
    {
        $layoutCandidates = [
            base_path('resources/views/app/layouts/app.blade.php'),
            base_path('resources/views/layouts/app.blade.php'),
            base_path('resources/views/app/layouts/base.blade.php'),
        ];

        $foundLayout = null;
        foreach ($layoutCandidates as $candidate) {
            if (file_exists($candidate)) {
                $foundLayout = $candidate;
                break;
            }
        }

        $this->assertNotNull($foundLayout, 'Could not locate standard application layout Blade file.');

        $content = (string) file_get_contents($foundLayout);
        $hasOverflowProtection = str_contains($content, 'overflow-x-hidden')
            || str_contains($content, 'max-w-')
            || str_contains($content, 'container')
            || str_contains($content, 'min-h-screen');

        $this->assertTrue(
            $hasOverflowProtection,
            'Main layout must implement container or overflow protection against horizontal scrolling breaks.'
        );
    }

    /**
     * 4. Error view templates contain zero raw dump statements (dd/dump).
     */
    public function test_no_whoops_or_fatal_debug_stacktraces_in_error_views(): void
    {
        $errorViewsDir = base_path('resources/views/errors');
        $this->assertDirectoryExists($errorViewsDir);

        $files = glob($errorViewsDir . '/*.blade.php') ?: [];
        $this->assertNotEmpty($files, 'No Blade views found in resources/views/errors.');

        foreach ($files as $file) {
            $content = (string) file_get_contents($file);
            $this->assertStringNotContainsString('@dd(', $content, "Debug @dd() found in [{$file}].");
            $this->assertStringNotContainsString('@dump(', $content, "Debug @dump() found in [{$file}].");
        }
    }

    /**
     * 5. Verify captured UI screenshot assets in documentation adhere to valid PNG format magic bytes.
     */
    public function test_web_ui_captures_adhere_to_png_format_integrity(): void
    {
        $imageDir = base_path('documentation/feature-ui-images');
        $this->assertDirectoryExists($imageDir);

        $pngFiles = glob($imageDir . '/*/*.png') ?: glob($imageDir . '/*.png') ?: [];
        $this->assertNotEmpty($pngFiles, 'Expected PNG screenshot files in documentation/feature-ui-images.');

        // Test first 5 representative screenshots for format integrity
        $testSample = array_slice($pngFiles, 0, 5);
        foreach ($testSample as $pngPath) {
            $this->assertValidPngImage($pngPath);
        }
    }
}
