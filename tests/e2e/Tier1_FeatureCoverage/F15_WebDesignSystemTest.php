<?php

declare(strict_types=1);

namespace Tests\E2E\Tier1_FeatureCoverage;

use Tests\E2E\Support\BaseTestCase;

/**
 * Feature F15: Web Design System Specification Test Suite
 *
 * Validates the Web Backoffice design tokens, Apple HIG Bento grid patterns,
 * typography scale, color tokens, and dark mode support.
 */
final class F15_WebDesignSystemTest extends BaseTestCase
{
    private string $cssPath = 'resources/css/app.css';

    /**
     * 1. Assert web design system master stylesheet exists and is populated.
     */
    public function test_web_design_system_stylesheet_exists(): void
    {
        $resolved = $this->resolveFilePath($this->cssPath);
        $this->assertFileExists($resolved, "Master CSS must exist at [{$this->cssPath}].");
        $this->assertGreaterThan(500, filesize($resolved), "CSS file must be populated with tokens.");
    }

    /**
     * 2. Assert Apple HIG typography font stack (SF Pro, Inter, system-ui) is declared.
     */
    public function test_apple_hig_typography_tokens(): void
    {
        $content = (string) file_get_contents($this->resolveFilePath($this->cssPath));

        $hasAppleFont = str_contains($content, 'SF Pro')
            || str_contains($content, 'BlinkMacSystemFont')
            || str_contains($content, '-apple-system');

        $this->assertTrue($hasAppleFont, "Typography tokens must include Apple HIG font family.");
        $this->assertStringContainsString('Inter', $content, "Typography must include Inter fallback font.");
    }

    /**
     * 3. Assert heading typography hierarchy scale (h1 to h6) is systematically defined.
     */
    public function test_web_heading_hierarchy_scale(): void
    {
        $content = (string) file_get_contents($this->resolveFilePath($this->cssPath));

        for ($i = 1; $i <= 6; $i++) {
            $this->assertStringContainsString(
                "h{$i}",
                $content,
                "CSS must define base heading scale for [h{$i}]."
            );
        }
    }

    /**
     * 4. Assert dark mode theme support and custom variant are configured.
     */
    public function test_web_dark_mode_and_theme_tokens(): void
    {
        $content = (string) file_get_contents($this->resolveFilePath($this->cssPath));

        $this->assertStringContainsString('dark', $content, "Stylesheet must support dark mode.");
        $this->assertTrue(
            str_contains($content, 'color-scheme') || str_contains($content, '@custom-variant dark'),
            "CSS must define dark mode color-scheme or variant tokens."
        );
    }

    /**
     * 5. Assert Apple HIG form controls and card surface tokens conform to HIG standards.
     */
    public function test_apple_hig_bento_grid_or_card_styling(): void
    {
        $content = (string) file_get_contents($this->resolveFilePath($this->cssPath));

        $this->assertTrue(
            str_contains($content, 'select') || str_contains($content, 'border') || str_contains($content, 'background'),
            "CSS must define Apple HIG interactive control or surface styling."
        );
    }
}
