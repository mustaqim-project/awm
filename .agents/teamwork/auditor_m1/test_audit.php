<?php

declare(strict_types=1);

/**
 * Independent Forensic Audit Test Harness for Milestone 1
 * Auditor: auditor_m1
 */

$workspace = 'C:\\laragon\\www\\cooca_core';
$errors = [];
$passes = [];

function assertCheck(bool $condition, string $checkName, string $failureDetails = ''): void {
    global $passes, $errors;
    if ($condition) {
        $passes[] = "[PASS] $checkName";
        echo "[PASS] $checkName\n";
    } else {
        $errors[] = "[FAIL] $checkName - $failureDetails";
        echo "[FAIL] $checkName - $failureDetails\n";
    }
}

echo "=== STARTING FORENSIC INTEGRITY AUDIT ===\n\n";

// 1. Check resources/views/layouts/public_marketing.blade.php
$file1 = file_get_contents("$workspace/resources/views/layouts/public_marketing.blade.php");
assertCheck(!str_contains($file1, 'cdn.tailwindcss.com'), "Removal of Tailwind Play CDN", "Found cdn.tailwindcss.com in public_marketing.blade.php");
assertCheck(str_contains($file1, "@vite(['resources/css/app.css', 'resources/js/app.js'])"), "Vite Production Pipeline Asset Injection", "@vite call missing or incorrect");
assertCheck(str_contains($file1, 'script defer src="{{ asset(\'js/app-alert.js\') }}"'), "AppAlert Script Deferred", "app-alert.js missing defer attribute");
assertCheck(str_contains($file1, '<link rel="alternate" hreflang="id" href="{{ url()->current() }}?lang=id" />') && str_contains($file1, '<link rel="alternate" hreflang="en" href="{{ url()->current() }}?lang=en" />'), "Sanitized Canonical Hreflang Tags", "Hreflang tags do not use clean current url");
assertCheck(str_contains($file1, 'toggleTheme()') && !str_contains($file1, 'hidden sm:inline-flex w-8 h-8 rounded-full items-center justify-center text-slate-300'), "Mobile Theme Toggle Accessibility in Header", "Theme switcher still hidden on mobile in header");

// 2. Check resources/views/layouts/partials/help_chatbot_widget.blade.php
$file2 = file_get_contents("$workspace/resources/views/layouts/partials/help_chatbot_widget.blade.php");
assertCheck(str_contains($file2, '<template x-if="isMounted || isOpen">'), "Chatbot Window Lazy DOM Mounting", "<template x-if=\"isMounted || isOpen\"> missing in help_chatbot_widget");
assertCheck(str_contains($file2, 'ensureHydrated()'), "Chatbot Deferred Hydration Logic", "ensureHydrated() method missing in help_chatbot_widget");
assertCheck(str_contains($file2, 'requestIdleCallback'), "Chatbot Non-blocking Idle Callback", "requestIdleCallback missing for non-blocking deep link check");

// 3. Check resources/views/layouts/partials/typography.blade.php
$file3 = file_get_contents("$workspace/resources/views/layouts/partials/typography.blade.php");
assertCheck(str_contains($file3, 'text-wrap: balance;'), "Typography text-wrap: balance on Headings", "text-wrap: balance missing in typography.blade.php");
assertCheck(str_contains($file3, '-apple-system, BlinkMacSystemFont'), "Apple System Typography Font Stack", "Font stack does not prioritize -apple-system");

// 4. Check resources/views/public/partials/live_chat_widget.blade.php
$file4 = file_get_contents("$workspace/resources/views/public/partials/live_chat_widget.blade.php");
assertCheck(str_contains($file4, '<template x-if="isMounted || isOpen">'), "Live Chat Modal Lazy DOM Mounting", "<template x-if=\"isMounted || isOpen\"> missing in live_chat_widget");
assertCheck(str_contains($file4, '@media (max-width: 639px)') && str_contains($file4, 'bottom: 0 !important;') && str_contains($file4, 'max-height: 85vh !important;'), "Mobile Bottom Sheet CSS in Live Chat", "Mobile bottom sheet CSS rules missing or incorrect");
assertCheck(str_contains($file4, 'startActivePolling()') && str_contains($file4, 'stopActivePolling()'), "Live Chat Controlled Active Polling", "Controlled polling methods missing");

// 5. Check resources/views/public/partials/cookie_consent_banner.blade.php
$file5 = file_get_contents("$workspace/resources/views/public/partials/cookie_consent_banner.blade.php");
assertCheck(str_contains($file5, 'bottom-20 left-4 right-4 sm:left-auto sm:right-6 sm:bottom-6'), "Cookie Banner Elevated Mobile Positioning", "Cookie banner offset missing bottom-20 on mobile");
$countTouchTargets = substr_count($file5, 'min-h-[44px]');
assertCheck($countTouchTargets >= 3, "WCAG 2.5.5 / Apple HIG Touch Targets (>=44px)", "Expected at least 3 min-h-[44px] touch targets, found $countTouchTargets");

// 6. Check resources/views/public/partials/tracking_pixels.blade.php
$file6 = file_get_contents("$workspace/resources/views/public/partials/tracking_pixels.blade.php");
assertCheck(str_contains($file6, 'requestIdleCallback') && str_contains($file6, 'initMarketingPixels'), "Deferred Marketing Pixel Initialization", "Marketing pixels not deferred to idle time");
assertCheck(str_contains($file6, '{ passive: true }'), "Passive Event Listener on Global Click Tracker", "{ passive: true } option missing on click listener");

// 7. Check resources/views/public/blog/show.blade.php
$file7 = file_get_contents("$workspace/resources/views/public/blog/show.blade.php");
assertCheck(!str_contains($file7, 'late-styles'), "Elimination of Redundant late-styles Push Block", "Found late-styles in blog/show.blade.php");
assertCheck(str_contains($file7, '.dark .article-prose') && str_contains($file7, '[data-theme="dark"] .article-prose'), "Consolidated High-Performance Dark Mode Selectors", "Dark mode article prose selectors missing in blog/show");

// 8. Manifest & Asset Verification
$manifestPath = "$workspace/public/build/manifest.json";
assertCheck(file_exists($manifestPath), "Vite Production Manifest Exists", "public/build/manifest.json not found");
if (file_exists($manifestPath)) {
    $manifest = json_decode(file_get_contents($manifestPath), true);
    assertCheck(isset($manifest['resources/css/app.css']['file']), "Vite CSS Compiled Entry Present", "resources/css/app.css entry missing in manifest");
    assertCheck(isset($manifest['resources/js/app.js']['file']), "Vite JS Compiled Entry Present", "resources/js/app.js entry missing in manifest");
}

echo "\n=== AUDIT SUMMARY ===\n";
echo "Total Passes: " . count($passes) . "\n";
echo "Total Failures: " . count($errors) . "\n";

if (empty($errors)) {
    echo "\nFINAL VERDICT: CLEAN\n";
    exit(0);
} else {
    echo "\nFINAL VERDICT: INTEGRITY VIOLATION\n";
    exit(1);
}
