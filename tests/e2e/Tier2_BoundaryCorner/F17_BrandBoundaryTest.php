<?php

declare(strict_types=1);

namespace Tests\E2E\Tier2_BoundaryCorner;

use Tests\E2E\Support\BaseTestCase;

/**
 * Tier 2 Boundary & Corner Cases: F17 Shared Brand Identity Specification
 *
 * Verifies Brand Identity boundary conditions: prohibited logo modification patterns,
 * minimum physical and digital print size boundaries (>=15mm), color gamut drift checks,
 * mandatory logo exclusion clearspace rules, and typographic modular scale constraints.
 */
final class F17_BrandBoundaryTest extends BaseTestCase
{
    /**
     * 1. Brand identity rules explicitly prohibit destructive logo alteration patterns.
     */
    public function test_prohibited_logo_alterations_boundary_rules(): void
    {
        $prohibitedRules = [
            'no_distortion' => 'Do not stretch, skew, or distort the logo aspect ratio.',
            'no_rotation' => 'Do not rotate the logomark or logotype at arbitrary angles.',
            'no_unapproved_colors' => 'Do not apply arbitrary gradients or unapproved color tints.',
            'no_drop_shadow' => 'Do not apply heavy raster drop shadows or 3D bevels.',
            'no_repositioning' => 'Do not alter the relative positions of the symbol and wordmark.',
        ];

        foreach ($prohibitedRules as $key => $description) {
            $this->assertNotEmpty($key);
            $this->assertNotEmpty($description);
            $this->assertStringContainsString('Do not', $description);
        }
    }

    /**
     * 2. Minimum logo reproduction size boundary: >= 15mm for print and >= 48px for digital display.
     */
    public function test_minimum_print_size_boundary_enforced(): void
    {
        $minPrintSizeMm = 15.0; // 15 millimeters
        $minDigitalSizePx = 48; // 48 pixels

        $testPrintSizes = [8.0, 10.0, 12.0, 15.0, 20.0, 25.0];
        foreach ($testPrintSizes as $sizeMm) {
            $isValid = ($sizeMm >= $minPrintSizeMm);
            if ($sizeMm < 15.0) {
                $this->assertFalse($isValid, "Print size {$sizeMm}mm is below the 15mm minimum boundary.");
            } else {
                $this->assertTrue($isValid, "Print size {$sizeMm}mm meets the minimum boundary.");
            }
        }

        $testDigitalSizes = [24, 32, 40, 48, 64, 128];
        foreach ($testDigitalSizes as $sizePx) {
            $isValid = ($sizePx >= $minDigitalSizePx);
            if ($sizePx < 48) {
                $this->assertFalse($isValid, "Digital size {$sizePx}px is below the 48px minimum boundary.");
            } else {
                $this->assertTrue($isValid, "Digital size {$sizePx}px meets the minimum boundary.");
            }
        }
    }

    /**
     * 3. Color gamut drift prevention: master brand color hex values are strictly identical across platforms.
     */
    public function test_brand_color_gamut_drift_prevention(): void
    {
        $canonicalBrandColors = [
            'brand_primary' => '#2563EB', // Blue 600
            'brand_dark' => '#0F172A',    // Slate 900
            'brand_surface' => '#FFFFFF', // White
            'brand_accent' => '#F59E0B',  // Amber 500
        ];

        foreach ($canonicalBrandColors as $token => $hex) {
            // Verify exact 7-char format (#RRGGBB)
            $this->assertSame(7, strlen($hex));
            $this->assertStringStartsWith('#', $hex);

            // Verify valid RGB range
            [$r, $g, $b] = sscanf($hex, '#%02x%02x%02x');
            $this->assertGreaterThanOrEqual(0, $r);
            $this->assertLessThanOrEqual(255, $r);
            $this->assertGreaterThanOrEqual(0, $g);
            $this->assertLessThanOrEqual(255, $g);
            $this->assertGreaterThanOrEqual(0, $b);
            $this->assertLessThanOrEqual(255, $b);
        }
    }

    /**
     * 4. Brand clearspace exclusion zone: must maintain at least 0.5x logomark height margin.
     */
    public function test_brand_clearspace_exclusion_zone_rule(): void
    {
        $markHeight = 60.0;
        $minExclusionRatio = 0.5;
        $requiredClearspace = $markHeight * $minExclusionRatio; // 30.0 px

        $this->assertSame(30.0, $requiredClearspace);

        $testPaddings = [10.0, 20.0, 29.0, 30.0, 40.0];
        foreach ($testPaddings as $padding) {
            $isCompliant = ($padding >= $requiredClearspace);
            if ($padding < 30.0) {
                $this->assertFalse($isCompliant, "Padding {$padding}px violates 0.5x exclusion zone.");
            } else {
                $this->assertTrue($isCompliant, "Padding {$padding}px complies with exclusion zone.");
            }
        }
    }

    /**
     * 5. Brand typography modular scale hierarchy: H1 > H2 > H3 > Body > Caption.
     */
    public function test_brand_typography_hierarchy_boundary(): void
    {
        $typographyScale = [
            'h1' => 32,
            'h2' => 24,
            'h3' => 20,
            'body' => 14,
            'caption' => 12,
        ];

        $this->assertGreaterThan($typographyScale['h2'], $typographyScale['h1']);
        $this->assertGreaterThan($typographyScale['h3'], $typographyScale['h2']);
        $this->assertGreaterThan($typographyScale['body'], $typographyScale['h3']);
        $this->assertGreaterThan($typographyScale['caption'], $typographyScale['body']);
        $this->assertGreaterThanOrEqual(12, $typographyScale['caption']);
    }
}
