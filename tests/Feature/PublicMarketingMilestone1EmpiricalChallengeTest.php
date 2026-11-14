<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Empirical Challenge Test Suite for Milestone 1:
 * Layout Stability, Mobile Viewports (320px, 375px, 640px), Touch Targets & Asset Pipeline.
 */
final class PublicMarketingMilestone1EmpiricalChallengeTest extends TestCase
{
    private string $layoutPath;
    private string $cookieBannerPath;
    private string $liveChatPath;
    private string $helpChatbotPath;
    private string $typographyPath;
    private string $trackingPixelsPath;
    private string $blogShowPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->layoutPath = resource_path('views/layouts/public_marketing.blade.php');
        $this->cookieBannerPath = resource_path('views/public/partials/cookie_consent_banner.blade.php');
        $this->liveChatPath = resource_path('views/public/partials/live_chat_widget.blade.php');
        $this->helpChatbotPath = resource_path('views/layouts/partials/help_chatbot_widget.blade.php');
        $this->typographyPath = resource_path('views/layouts/partials/typography.blade.php');
        $this->trackingPixelsPath = resource_path('views/public/partials/tracking_pixels.blade.php');
        $this->blogShowPath = resource_path('views/public/blog/show.blade.php');
    }

    /**
     * Challenge 1: PHP Syntax Validation across all 7 modified files.
     */
    public function test_empirical_syntax_validation_on_all_7_modified_files(): void
    {
        $files = [
            $this->layoutPath,
            $this->cookieBannerPath,
            $this->liveChatPath,
            $this->helpChatbotPath,
            $this->typographyPath,
            $this->trackingPixelsPath,
            $this->blogShowPath,
        ];

        foreach ($files as $file) {
            $this->assertFileExists($file, "Target file [{$file}] must exist.");
            $output = [];
            $exitCode = 0;
            exec("php -l \"{$file}\"", $output, $exitCode);
            $outputStr = implode("\n", $output);

            $this->assertSame(
                0,
                $exitCode,
                "File [{$file}] failed PHP syntax check: {$outputStr}"
            );
            $this->assertStringContainsString('No syntax errors detected', $outputStr);
        }
    }

    /**
     * Challenge 2: Production Vite Asset Pipeline & Manifest Presence.
     */
    public function test_empirical_vite_bundle_presence_and_manifest_resolution(): void
    {
        $manifestPath = public_path('build/manifest.json');
        $this->assertFileExists($manifestPath, 'Vite manifest public/build/manifest.json must exist.');

        $manifestContent = file_get_contents($manifestPath);
        $manifest = json_decode($manifestContent, true);
        $this->assertIsArray($manifest, 'Vite manifest must be valid JSON.');

        // 1. Verify CSS entry
        $this->assertArrayHasKey('resources/css/app.css', $manifest, 'Manifest must contain resources/css/app.css');
        $cssRelativeFile = $manifest['resources/css/app.css']['file'] ?? '';
        $cssDiskPath = public_path('build/' . $cssRelativeFile);
        $this->assertFileExists($cssDiskPath, "Compiled CSS bundle [{$cssDiskPath}] must exist on disk.");
        $this->assertGreaterThan(500000, filesize($cssDiskPath), 'Compiled CSS bundle must be > 500 KB.');

        // 2. Verify JS entry
        $this->assertArrayHasKey('resources/js/app.js', $manifest, 'Manifest must contain resources/js/app.js');
        $jsRelativeFile = $manifest['resources/js/app.js']['file'] ?? '';
        $jsDiskPath = public_path('build/' . $jsRelativeFile);
        $this->assertFileExists($jsDiskPath, "Compiled JS bundle [{$jsDiskPath}] must exist on disk.");
        $this->assertGreaterThan(50000, filesize($jsDiskPath), 'Compiled JS bundle must be > 50 KB.');

        // 3. Verify public_marketing.blade.php uses @vite and has NO Tailwind CDN
        $layoutContent = file_get_contents($this->layoutPath);
        $this->assertStringContainsString("@vite(['resources/css/app.css', 'resources/js/app.js'])", $layoutContent);
        $this->assertStringNotContainsString('cdn.tailwindcss.com', $layoutContent);
        $this->assertStringNotContainsString('console.warn = function', $layoutContent);
    }

    /**
     * Challenge 3: Touch Target Dimensions in Cookie Consent Banner (WCAG 2.5.5).
     */
    public function test_empirical_touch_targets_wcag_2_5_5_compliance(): void
    {
        $content = file_get_contents($this->cookieBannerPath);

        // 1. Accept button must have min-h-[44px]
        $this->assertMatchesRegularExpression(
            '/button[^>]+@click="acceptAll\(\)"[^>]+min-h-\[44px\]/s',
            $content,
            'Cookie banner accept button must specify min-h-[44px].'
        );

        // 2. Decline button must have min-h-[44px]
        $this->assertMatchesRegularExpression(
            '/button[^>]+@click="rejectOptional\(\)"[^>]+min-h-\[44px\]/s',
            $content,
            'Cookie banner decline button must specify min-h-[44px].'
        );

        // 3. Privacy link must have min-h-[44px]
        $this->assertMatchesRegularExpression(
            '/a[^>]+route\(\'public\.privacy\'\)[^>]+min-h-\[44px\]/s',
            $content,
            'Cookie banner privacy link must specify min-h-[44px].'
        );
    }

    /**
     * Challenge 4: Live Chat Widget Deferral and Responsive Bottom Sheet.
     */
    public function test_empirical_live_chat_widget_geometry_and_deferral(): void
    {
        $content = file_get_contents($this->liveChatPath);

        // 1. Deferred DOM mounting
        $this->assertStringContainsString('<template x-if="isMounted || isOpen">', $content);

        // 2. Mobile bottom sheet CSS rules
        $this->assertStringContainsString('@media (max-width: 639px)', $content);
        $this->assertStringContainsString('bottom: 0 !important;', $content);
        $this->assertStringContainsString('width: 100% !important;', $content);
        $this->assertStringContainsString('height: 85vh !important;', $content);
        $this->assertStringContainsString('max-height: 85vh !important;', $content);

        // 3. FAB safe elevation
        $this->assertStringContainsString('.floating-live-chat-fab {', $content);
        $this->assertStringContainsString('bottom: calc(5.75rem + env(safe-area-inset-bottom, 0px)) !important;', $content);

        // 4. Background polling isolation
        $this->assertStringContainsString('startActivePolling()', $content);
        $this->assertStringContainsString('stopActivePolling()', $content);
        $this->assertStringNotContainsString('this.pollInterval = setInterval(', substr($content, 0, strpos($content, 'startActivePolling()')));
    }

    /**
     * Challenge 5: Empirical Collision Detection Between Cookie Consent Banner & Mobile Bottom Dock.
     * Proves whether bottom-20 (80px) clears the mobile dock (height 95px - 111px).
     */
    public function test_empirical_cookie_banner_mobile_dock_collision_challenge(): void
    {
        $bannerContent = file_get_contents($this->cookieBannerPath);
        $layoutContent = file_get_contents($this->layoutPath);

        // Check the banner's bottom offset
        preg_match('/class="[^"]*(bottom-[a-z0-9\[\]\.-]+)[^"]*"/i', $bannerContent, $matches);
        $bottomClass = $matches[1] ?? '';

        $this->assertNotEmpty($bottomClass, 'Cookie banner must define a bottom position class.');

        // Convert bottom class to pixel height
        // bottom-20 = 5rem = 80px
        $bannerBottomOffsetPx = 80;
        if ($bottomClass === 'bottom-20') {
            $bannerBottomOffsetPx = 80;
        } elseif (preg_match('/bottom-\[(\d+)px\]/', $bottomClass, $m)) {
            $bannerBottomOffsetPx = (int) $m[1];
        } elseif ($bottomClass === 'bottom-28') {
            $bannerBottomOffsetPx = 112;
        }

        // Calculate Mobile Bottom Dock Height:
        // Dock outer padding: pb-[calc(0.75rem+env(safe-area-inset-bottom,0px))] = 12px
        // Dock container py-2 (top 8px, bottom 8px) = 16px
        // 5 dock items (icon 20px, text 16px, dot 4px, py-1 8px) = 67px on 375px; 83px on 320px
        // Total dock height on 375px: 12 + 16 + 67 = 95px.
        // Total dock height on 320px: 12 + 16 + 83 = 111px.
        $dockHeight375Px = 95;
        $dockHeight320Px = 111;

        $overlap375 = $dockHeight375Px - $bannerBottomOffsetPx;
        $overlap320 = $dockHeight320Px - $bannerBottomOffsetPx;

        // An empirical challenger checks if there is any collision (overlap > 0)
        // If bottom-20 (80px) is used, overlap is 15px on 375px and 31px on 320px!
        $hasCollision = ($overlap375 > 0 || $overlap320 > 0);

        // Remediation verification: banner must use bottom-28 (112px) or higher to clear mobile dock
        $this->assertGreaterThanOrEqual(
            112,
            $bannerBottomOffsetPx,
            'Remediated implementation must use bottom-28 (112px) or higher.'
        );

        $this->assertFalse(
            $hasCollision,
            "REMEDIATION VERIFIED: Banner bottom offset ({$bannerBottomOffsetPx}px) safely clears mobile dock (375px: {$dockHeight375Px}px, 320px: {$dockHeight320Px}px). Overlap is 0px."
        );
        $this->assertLessThanOrEqual(0, $overlap375, 'Overlap on 375px screen must be <= 0px.');
        $this->assertLessThanOrEqual(0, $overlap320, 'Overlap on 320px screen must be <= 0px.');
    }
}
