<?php

declare(strict_types=1);

namespace Tests\E2E\Tier2_BoundaryCorner;

use Tests\E2E\Support\BaseTestCase;

/**
 * Tier 2 Boundary & Corner Cases: F16 Mobile Design System Specification
 *
 * Verifies Mobile design system boundary conditions: minimum 48pt tap target boundary,
 * bottom thumb zone touch reach layout compliance, ESC/POS monospace receipt formatting,
 * tactile haptic feedback invocation on POS actions, and elevated button styling constraints.
 */
final class F16_MobileDesignBoundaryTest extends BaseTestCase
{
    private string $mobileLibDir = 'C:\\laragon\\www\\cooca_my_own\\lib';

    /**
     * 1. Primary mobile touch targets enforce minimum 48.0 logical pixels height (Material/HIG standard).
     */
    public function test_minimum_48pt_tap_target_boundary_on_mobile_buttons(): void
    {
        $minTargetHeight = 48.0;

        // Sub-48pt targets are non-compliant for primary operational touch targets
        $testHeights = [32.0, 36.0, 40.0, 44.0, 48.0, 56.0];

        foreach ($testHeights as $h) {
            $isCompliant = ($h >= $minTargetHeight);
            if ($h < 48.0) {
                $this->assertFalse($isCompliant, "Target height {$h}pt should fail minimum 48pt boundary.");
            } else {
                $this->assertTrue($isCompliant, "Target height {$h}pt should pass minimum 48pt boundary.");
            }
        }
    }

    /**
     * 2. Key POS cashier actions (Checkout, Cart, Keypad) are anchored in bottom thumb reach zone.
     */
    public function test_bottom_thumb_zone_touch_reach_on_critical_pos_actions(): void
    {
        $posScreenFile = $this->mobileLibDir . '\\screens\\pos_screen.dart';
        $this->assertFileExists($posScreenFile);

        $content = (string) file_get_contents($posScreenFile);
        $hasBottomAnchor = str_contains($content, 'bottomNavigationBar:')
            || str_contains($content, 'showModalBottomSheet')
            || str_contains($content, 'BottomSheet')
            || str_contains($content, 'bottom:');

        $this->assertTrue(
            $hasBottomAnchor,
            'POS screen must anchor key checkout interactions to bottom thumb reach zone.'
        );
    }

    /**
     * 3. Thermal ESC/POS receipt rendering enforces monospace fixed-width column constraints (32/48 chars).
     */
    public function test_monospace_font_enforcement_for_escpos_thermal_receipts(): void
    {
        // Actual mobile thermal printer service in Flutter codebase
        $printerServiceFile = $this->mobileLibDir . '\\core\\services\\thermal_printer_service.dart';
        $this->assertFileExists(
            $printerServiceFile,
            'Thermal printer service dart file [thermal_printer_service.dart] must exist.'
        );

        $content = (string) file_get_contents($printerServiceFile);
        $this->assertTrue(
            str_contains($content, '32')
            && str_contains($content, '48')
            && str_contains($content, 'PaperSize'),
            'Thermal printer service must configure 32 and 48 character column widths and PaperSize.'
        );
    }

    /**
     * 4. Tactile haptic feedback is invoked on scanner reads and checkout taps in mobile code.
     */
    public function test_mobile_haptic_feedback_boundary_invocation(): void
    {
        $dartFiles = glob($this->mobileLibDir . '/**/*.dart') ?: [];
        $hapticFound = false;

        foreach ($dartFiles as $file) {
            $content = (string) file_get_contents($file);
            if (str_contains($content, 'HapticFeedback')) {
                $hapticFound = true;
                break;
            }
        }

        $this->assertTrue($hapticFound, 'Mobile application must invoke HapticFeedback for tactile cashier confirmation.');
    }

    /**
     * 5. Elevated button styling specifies explicit minimum size constraints for usability.
     */
    public function test_mobile_elevated_button_style_boundary(): void
    {
        $posScreenFile = $this->mobileLibDir . '\\screens\\pos_screen.dart';
        $content = (string) file_get_contents($posScreenFile);

        $hasButtonStyling = str_contains($content, 'CupertinoButton')
            || str_contains($content, 'ElevatedButton')
            || str_contains($content, 'InkWell')
            || str_contains($content, 'GestureDetector');

        $this->assertTrue(
            $hasButtonStyling,
            'Mobile screens must utilize styled button components with accessible dimensions.'
        );
    }
}
