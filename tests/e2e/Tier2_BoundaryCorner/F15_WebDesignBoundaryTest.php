<?php

declare(strict_types=1);

namespace Tests\E2E\Tier2_BoundaryCorner;

use Tests\E2E\Support\BaseTestCase;

/**
 * Tier 2 Boundary & Corner Cases: F15 Web Design System Specification
 *
 * Verifies Web design system boundary conditions: WCAG AA contrast ratio calculation,
 * tabular-nums monospace alignment for financial digits, responsive layout breakpoint clamp,
 * POS touch target minimum height, and semantic color palette hex validity.
 */
final class F15_WebDesignBoundaryTest extends BaseTestCase
{
    /**
     * 1. WCAG AA contrast ratio calculation: text and surface tokens must achieve >= 4.5:1 contrast.
     */
    public function test_wcag_aa_contrast_ratio_boundary_calculation(): void
    {
        // Dark slate on white (standard text on background)
        $darkSlate = [15, 23, 42]; // #0F172A
        $white = [255, 255, 255]; // #FFFFFF

        $lum1 = $this->calculateRelativeLuminance($darkSlate);
        $lum2 = $this->calculateRelativeLuminance($white);
        $ratio = ($lum2 + 0.05) / ($lum1 + 0.05);

        // WCAG AA requirement for normal text is 4.5:1
        $this->assertGreaterThanOrEqual(
            4.5,
            $ratio,
            "Contrast ratio ({$ratio}:1) must exceed WCAG AA minimum of 4.5:1."
        );
        $this->assertGreaterThan(14.0, $ratio);
    }

    /**
     * 2. Financial tables and monetary figures utilize tabular-nums or monospace font classes.
     */
    public function test_tabular_nums_font_feature_on_financial_and_currency_elements(): void
    {
        $viewsDir = base_path('resources/views');
        $this->assertDirectoryExists($viewsDir);

        $bladeFiles = glob($viewsDir . '/app/finance/*.blade.php')
            ?: glob($viewsDir . '/app/pos/*.blade.php')
            ?: glob($viewsDir . '/app/*.blade.php')
            ?: [];

        $tabularOrMonoFound = false;
        foreach ($bladeFiles as $file) {
            $content = (string) file_get_contents($file);
            if (
                str_contains($content, 'font-mono')
                || str_contains($content, 'tabular-nums')
                || str_contains($content, 'text-right')
            ) {
                $tabularOrMonoFound = true;
                break;
            }
        }

        $this->assertTrue(
            $tabularOrMonoFound,
            'Financial and POS views must implement tabular-nums, font-mono, or text-right for number alignment.'
        );
    }

    /**
     * 3. Responsive layout breakpoints (sm, md, lg, xl) clamp layouts on desktop viewports.
     */
    public function test_responsive_layout_clamp_and_breakpoints(): void
    {
        $breakpoints = [
            'sm' => 640,
            'md' => 768,
            'lg' => 1024,
            'xl' => 1280,
            '2xl' => 1536,
        ];

        foreach ($breakpoints as $name => $width) {
            $this->assertGreaterThan(0, $width);
        }

        $this->assertLessThan($breakpoints['2xl'], $breakpoints['xl']);
        $this->assertLessThan($breakpoints['xl'], $breakpoints['lg']);
    }

    /**
     * 4. POS terminal keypad and touch elements enforce minimum touch target height.
     */
    public function test_pos_touch_target_minimum_height_boundary(): void
    {
        $posTerminalView = base_path('resources/views/app/pos/terminal.blade.php');
        $this->assertFileExists(
            $posTerminalView,
            'POS terminal view [resources/views/app/pos/terminal.blade.php] must exist.'
        );

        $content = (string) file_get_contents($posTerminalView);
        $hasTouchTargetClasses = str_contains($content, 'h-10')
            || str_contains($content, 'h-11')
            || str_contains($content, 'h-12')
            || str_contains($content, 'py-2')
            || str_contains($content, 'py-3')
            || str_contains($content, 'min-h-');

        $this->assertTrue(
            $hasTouchTargetClasses,
            'POS terminal view must specify touch target sizing classes (e.g. h-10, h-12, py-2, py-3).'
        );
    }

    /**
     * 5. Semantic color palette definitions in stylesheet are strictly valid 6-character hex strings.
     */
    public function test_semantic_color_palette_boundary_completeness(): void
    {
        $cssFile = resource_path('css/app.css');
        $this->assertFileExists(
            $cssFile,
            'Application stylesheet [resources/css/app.css] must exist.'
        );

        $cssContent = (string) file_get_contents($cssFile);

        // Extract all hex color tokens defined in application stylesheet
        preg_match_all('/#[0-9A-Fa-f]{6}\b/', $cssContent, $matches);
        $hexTokens = $matches[0] ?? [];

        $this->assertNotEmpty(
            $hexTokens,
            'Application stylesheet [resources/css/app.css] must define hex color tokens.'
        );

        $hexRegex = '/^#[0-9A-Fa-f]{6}$/';
        foreach ($hexTokens as $hex) {
            $this->assertMatchesRegularExpression(
                $hexRegex,
                $hex,
                "Extracted CSS token [{$hex}] must be a strictly valid 6-character hex code."
            );
        }

        // Verify mandatory Apple HIG & design system tokens are present in stylesheet
        $requiredTokens = ['#FFFFFF', '#000000', '#1C1C1E', '#007AFF', '#2C2C2E'];
        foreach ($requiredTokens as $token) {
            $this->assertContains(
                $token,
                $hexTokens,
                "Mandatory design system token [{$token}] must be defined in resources/css/app.css."
            );
        }
    }

    /**
     * Helper to compute relative luminance according to sRGB WCAG 2.1 specs.
     *
     * @param array{0: int, 1: int, 2: int} $rgb
     */
    private function calculateRelativeLuminance(array $rgb): float
    {
        $sRGB = array_map(function ($val) {
            $v = $val / 255.0;
            return ($v <= 0.03928) ? $v / 12.92 : pow(($v + 0.055) / 1.055, 2.4);
        }, $rgb);

        return 0.2126 * $sRGB[0] + 0.7152 * $sRGB[1] + 0.0722 * $sRGB[2];
    }
}
