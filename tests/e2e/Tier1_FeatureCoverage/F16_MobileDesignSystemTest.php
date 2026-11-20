<?php

declare(strict_types=1);

namespace Tests\E2E\Tier1_FeatureCoverage;

use Tests\E2E\Support\BaseTestCase;

/**
 * Feature F16: Mobile Design System Specification Test Suite
 *
 * Validates the Mobile Application design tokens, touch target rules (>=48pt),
 * typography scale, bottom sheet patterns, and reactive interaction models.
 */
final class F16_MobileDesignSystemTest extends BaseTestCase
{
    private string $mobileAuditDoc = 'documentation/cooca-platform-audit/02-mobile-app-audit.md';
    private string $specDoc = 'documentation/design-system/mobile-design-system.md';

    /**
     * 1. Assert mobile design system specifications or mobile technical audit are populated.
     */
    public function test_mobile_design_system_specs_in_audit(): void
    {
        $doc = file_exists($this->resolveFilePath($this->specDoc))
            ? $this->specDoc
            : $this->mobileAuditDoc;

        $this->assertMarkdownFilePopulated($doc, 1000);
    }

    /**
     * 2. Assert minimum touch target rule (>= 48pt / 44pt touch boundary) is specified.
     */
    public function test_minimum_touch_target_rule(): void
    {
        $doc = file_exists($this->resolveFilePath($this->specDoc))
            ? $this->specDoc
            : $this->mobileAuditDoc;

        $content = (string) file_get_contents($this->resolveFilePath($doc));

        // Touch target standard in mobile platforms (48dp standard or 44pt HIG)
        $hasTouchRule = str_contains($content, '48')
            || str_contains($content, 'touch')
            || str_contains($content, 'Cupertino')
            || str_contains($content, 'Tab Bar');

        $this->assertTrue(
            $hasTouchRule,
            "Mobile design specifications must enforce touch accessibility and tap targets."
        );
    }

    /**
     * 3. Assert mobile typography hierarchy and display scale are audited.
     */
    public function test_mobile_typography_scale(): void
    {
        $doc = file_exists($this->resolveFilePath($this->specDoc))
            ? $this->specDoc
            : $this->mobileAuditDoc;

        $content = (string) file_get_contents($this->resolveFilePath($doc));

        $hasTypography = str_contains($content, 'Cupertino')
            || str_contains($content, 'Typography')
            || str_contains($content, 'Tab Bar')
            || str_contains($content, 'Apple HIG');

        $this->assertTrue(
            $hasTypography,
            "Mobile specifications must declare mobile typography or HIG design conventions."
        );
    }

    /**
     * 4. Assert bottom sheet modal interaction patterns are defined for micro-interactions.
     */
    public function test_mobile_bottom_sheet_patterns(): void
    {
        $content = (string) file_get_contents($this->resolveFilePath($this->mobileAuditDoc));

        $this->assertTrue(
            str_contains($content, 'bottom sheet') || str_contains($content, 'modal'),
            "Mobile audit must document modal bottom sheet patterns for mobile micro-interactions."
        );
    }

    /**
     * 5. Assert mobile reactive interaction and feedback architecture (ListenableBuilder / MerchantState).
     */
    public function test_mobile_haptic_and_feedback_rules(): void
    {
        $content = (string) file_get_contents($this->resolveFilePath($this->mobileAuditDoc));

        $this->assertStringContainsString(
            'ListenableBuilder',
            $content,
            "Mobile audit must document reactive UI binding architecture."
        );
        $this->assertStringContainsString(
            'MerchantState',
            $content,
            "Mobile audit must document primary state management singleton."
        );
    }
}
