<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessLandingPage;
use App\Models\CommerceStoreSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Adversarial Empirical Verification Suite for Milestone 2:
 * 1. Heading Outline Semantics (Single H1, Sequential H1->H2->H3, No Skips) across all 21 scoped views
 * 2. Bilingual Localization Testing (Indonesian 'id' & English 'en', Zero Raw Keys) across all 21 scoped views
 * 3. Theme Contrast & Dark Mode Hardening (WCAG AA Contrast >= 4.5:1 text, >= 3:1 icons)
 */
final class AdversarialM2ThemeLocalizationHierarchyTest extends TestCase
{
    use RefreshDatabase;

    private const SCOPED_DESTINATIONS = [
        'Landing' => '/',
        'About' => '/about',
        'Demo' => '/demo',
        'Pricing' => '/pricing',
        'Privacy' => '/privacy',
        'Security' => '/security',
        'Sitemap' => '/sitemap',
        'Support' => '/support',
        'Terms' => '/terms',
        'Business Landing' => '/b/warung-berkah-nusantara',
        'Kasir Android' => '/aplikasi-kasir-android',
        'Kasir Gratis' => '/aplikasi-kasir-gratis',
        'Kasir Offline' => '/aplikasi-kasir-offline',
        'Discovery Index' => '/jelajah',
        'Solution FnB' => '/solutions/fnb',
        'Solution Laundry' => '/solutions/laundry',
        'Solution Manufacturing' => '/solutions/manufacturing',
        'Solution Retail' => '/solutions/retail',
        'Solution Services' => '/solutions/services',
        'Solution Show' => '/solusi/kasir-warung',
        'Solution Workshop' => '/solutions/workshop',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->createSampleBusiness('warung-berkah-nusantara');
    }

    private function createSampleBusiness(string $slug = 'warung-berkah-nusantara'): Business
    {
        $business = Business::create([
            'name' => 'Warung Berkah Nusantara',
            'slug' => $slug,
            'is_active' => true,
            'is_demo' => false,
            'is_test' => false,
            'phone' => '081234567890',
            'email' => 'berkah@example.com',
            'address' => 'Jl. Merdeka No. 45, Jakarta Pusat',
        ]);

        CommerceStoreSetting::create([
            'business_id' => $business->id,
            'is_storefront_enabled' => true,
            'is_discoverable' => true,
            'allow_pickup' => true,
            'allow_delivery' => true,
            'allow_scheduled_order' => false,
            'allow_request_order' => false,
            'min_order_amount' => 5000,
        ]);

        BusinessLandingPage::create([
            'business_id' => $business->id,
            'headline' => 'Warung Berkah Nusantara',
            'subheadline' => 'Menyajikan kuliner khas nusantara higienis dan nikmat.',
            'is_published' => true,
            'show_pos_products' => true,
            'theme_color' => '#007AFF',
            'cta_primary_text' => 'Pesan Sekarang',
            'whatsapp_number' => '081234567890',
        ]);

        return $business;
    }

    /**
     * EMPIRICAL TEST 1: Strict Heading Outline Semantics across all 21 scoped views.
     * - Exactly one <h1> tag.
     * - Sequential progression: no jumps like H1 -> H3, H1 -> H4, H2 -> H4, H3 -> H5.
     */
    public function test_empirical_heading_hierarchy_single_h1_and_no_skips_across_all_21_scoped_views(): void
    {
        $this->assertCount(21, self::SCOPED_DESTINATIONS, 'Scope must contain exactly 21 target destinations.');

        $headingViolations = [];

        foreach (self::SCOPED_DESTINATIONS as $name => $uri) {
            $response = $this->get($uri);
            $this->assertSame(200, $response->getStatusCode(), "Destination [{$name}] at URI [{$uri}] must return HTTP 200.");

            $html = $response->getContent();

            // Extract all heading tags in document order
            preg_match_all('/<h([1-6])\b[^>]*>(.*?)<\/h\1>/si', $html, $matches, PREG_SET_ORDER);

            $h1List = [];
            $outline = [];
            $skips = [];

            $prevLevel = null;

            foreach ($matches as $match) {
                $level = (int) $match[1];
                $text = trim(strip_tags($match[2]));
                $outline[] = ['level' => $level, 'text' => $text];

                if ($level === 1) {
                    $h1List[] = $text;
                }

                if ($prevLevel !== null) {
                    // Check for forward skips: level should not jump by more than 1
                    if ($level > $prevLevel + 1) {
                        $skips[] = "Skip from H{$prevLevel} to H{$level} (Text: \"{$text}\")";
                    }
                }
                $prevLevel = $level;
            }

            // Invariant 1: Exactly 1 H1
            if (count($h1List) !== 1) {
                $headingViolations[] = "[{$name} - {$uri}] Expected exactly 1 H1, found " . count($h1List) . ": " . json_encode($h1List);
            }

            // Invariant 2: No forward skips
            if (!empty($skips)) {
                $headingViolations[] = "[{$name} - {$uri}] Heading skips detected: " . implode('; ', $skips);
            }
        }

        $this->assertEmpty(
            $headingViolations,
            "Heading outline semantic violations detected:\n" . implode("\n", $headingViolations)
        );
    }

    /**
     * EMPIRICAL TEST 2: Bilingual Localization (ID and EN) across all 21 scoped views.
     * - Verifies both ?lang=id and ?lang=en render HTTP 200.
     * - Verifies ZERO raw uncompiled translation keys (e.g., public_*, storefront.*, common.*).
     */
    public function test_empirical_bilingual_localization_zero_uncompiled_keys_in_id_and_en(): void
    {
        $locales = ['id', 'en'];
        $uncompiledKeyPattern = '/\b(?:public_[a-z0-9_]+|storefront|common|billing|landing_page)\.[a-zA-Z0-9_.-]+\b/';

        $localizationErrors = [];

        foreach ($locales as $locale) {
            foreach (self::SCOPED_DESTINATIONS as $name => $uri) {
                $urlWithLocale = $uri . (str_contains($uri, '?') ? '&' : '?') . "lang={$locale}";
                $response = $this->get($urlWithLocale);

                if ($response->getStatusCode() !== 200) {
                    $localizationErrors[] = "[{$name} - {$urlWithLocale}] HTTP Status {$response->getStatusCode()} in locale {$locale}";
                    continue;
                }

                $content = $response->getContent();

                // Strip script and style tags to avoid false positives on legitimate JS identifiers
                $sanitized = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $content) ?? $content;
                $sanitized = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $sanitized) ?? $sanitized;

                preg_match_all($uncompiledKeyPattern, $sanitized, $rawMatches);
                $uniqueKeys = array_unique($rawMatches[0] ?? []);

                if (!empty($uniqueKeys)) {
                    $localizationErrors[] = "[{$name} - {$urlWithLocale}] Uncompiled translation keys found in [{$locale}]: " . implode(', ', $uniqueKeys);
                }
            }
        }

        $this->assertEmpty(
            $localizationErrors,
            "Localization raw translation key leaks detected:\n" . implode("\n", $localizationErrors)
        );
    }

    /**
     * EMPIRICAL TEST 3: Theme Contrast & Dark Mode Hardening.
     * Computes relative luminance and contrast ratios according to WCAG 2.1 specs.
     */
    public function test_empirical_theme_contrast_calculations_and_wcag_aa(): void
    {
        // Helper to compute WCAG 2.1 contrast ratio
        $calcContrast = function (string $hexFg, string $hexBg): float {
            $getLuminance = function (string $hex): float {
                $hex = ltrim($hex, '#');
                if (strlen($hex) === 3) {
                    $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
                }
                $r = hexdec(substr($hex, 0, 2)) / 255;
                $g = hexdec(substr($hex, 2, 2)) / 255;
                $b = hexdec(substr($hex, 4, 2)) / 255;

                $rLinear = ($r <= 0.04045) ? $r / 12.92 : pow(($r + 0.055) / 1.055, 2.4);
                $gLinear = ($g <= 0.04045) ? $g / 12.92 : pow(($g + 0.055) / 1.055, 2.4);
                $bLinear = ($b <= 0.04045) ? $b / 12.92 : pow(($b + 0.055) / 1.055, 2.4);

                return 0.2126 * $rLinear + 0.7152 * $gLinear + 0.0722 * $bLinear;
            };

            $l1 = $getLuminance($hexFg);
            $l2 = $getLuminance($hexBg);

            $lighter = max($l1, $l2);
            $darker = min($l1, $l2);

            return round(($lighter + 0.05) / ($darker + 0.05), 2);
        };

        // Standard Tailwind Palette Colors
        $colors = [
            'white' => '#ffffff',
            'dark_bg_main' => '#0B0F19',
            'dark_bg_card' => '#1C1C1E',
            'dark_bg_slate' => '#0f172a',
            'slate_400' => '#94a3b8',
            'slate_500' => '#64748b',
            'slate_600' => '#475569',
            'emerald_400' => '#34d399',
            'emerald_600' => '#059669',
            'amber_400' => '#fbbf24',
            'amber_600' => '#d97706',
            'cyan_400' => '#22d3ee',
            'cyan_600' => '#0891b2',
            'blue_apple' => '#007AFF',
            'cyan_apple' => '#00C4D8',
        ];

        // 1. Verify Pricing Modal checkmarks (dark:text-emerald-400)
        // Background in dark mode modal is #1C1C1E or #0B0F19
        $contrastEmerald400onDark = $calcContrast($colors['emerald_400'], $colors['dark_bg_card']);
        $this->assertGreaterThanOrEqual(
            3.0,
            $contrastEmerald400onDark,
            "emerald-400 on dark card ({$contrastEmerald400onDark}:1) must meet WCAG non-text minimum (>= 3:1)"
        );
        $this->assertGreaterThanOrEqual(
            4.5,
            $contrastEmerald400onDark,
            "emerald-400 on dark card ({$contrastEmerald400onDark}:1) also meets WCAG AA normal text minimum (>= 4.5:1)"
        );

        // In light mode modal: emerald-600 on white
        $contrastEmerald600onWhite = $calcContrast($colors['emerald_600'], $colors['white']);
        $this->assertGreaterThanOrEqual(
            3.0,
            $contrastEmerald600onWhite,
            "emerald-600 on white ({$contrastEmerald600onWhite}:1) meets WCAG non-text minimum (>= 3:1)"
        );

        // 2. Verify Discovery filter labels (text-slate-600 dark:text-slate-400)
        // Light mode: slate-600 on white
        $contrastSlate600onWhite = $calcContrast($colors['slate_600'], $colors['white']);
        $this->assertGreaterThanOrEqual(
            4.5,
            $contrastSlate600onWhite,
            "slate-600 on white ({$contrastSlate600onWhite}:1) must satisfy WCAG AA normal text (>= 4.5:1)"
        );

        // Dark mode: slate-400 on dark card (#1C1C1E) and main (#0B0F19)
        $contrastSlate400onDarkCard = $calcContrast($colors['slate_400'], $colors['dark_bg_card']);
        $contrastSlate400onDarkMain = $calcContrast($colors['slate_400'], $colors['dark_bg_main']);
        $this->assertGreaterThanOrEqual(
            4.5,
            $contrastSlate400onDarkCard,
            "slate-400 on dark card ({$contrastSlate400onDarkCard}:1) must satisfy WCAG AA normal text (>= 4.5:1)"
        );
        $this->assertGreaterThanOrEqual(
            4.5,
            $contrastSlate400onDarkMain,
            "slate-400 on dark main ({$contrastSlate400onDarkMain}:1) must satisfy WCAG AA normal text (>= 4.5:1)"
        );

        // Contrast bug analysis: slate-400 on white
        $contrastSlate400onWhite = $calcContrast($colors['slate_400'], $colors['white']);
        $this->assertLessThan(
            4.5,
            $contrastSlate400onWhite,
            "EMPIRICAL PROOF: slate-400 on white is {$contrastSlate400onWhite}:1 which FAILS WCAG AA (< 4.5:1). Remediations replacing text-slate-400 with text-slate-600 dark:text-slate-400 are required."
        );

        // 3. Verify Industry Solutions Feature Icon Boxes
        // Amber: amber-600 on white (3.17:1), amber-400 on dark (10.08:1)
        $contrastAmberLight = $calcContrast($colors['amber_600'], $colors['white']);
        $contrastAmberDark = $calcContrast($colors['amber_400'], $colors['dark_bg_card']);
        $this->assertGreaterThanOrEqual(3.0, $contrastAmberLight, "amber-600 on white must be >= 3:1 for icons");
        $this->assertGreaterThanOrEqual(3.0, $contrastAmberDark, "amber-400 on dark must be >= 3:1 for icons");

        // Cyan: cyan-600 on white, cyan-400 on dark
        $contrastCyanLight = $calcContrast($colors['cyan_600'], $colors['white']);
        $contrastCyanDark = $calcContrast($colors['cyan_400'], $colors['dark_bg_card']);
        $this->assertGreaterThanOrEqual(3.0, $contrastCyanLight, "cyan-600 on white must be >= 3:1 for icons");
        $this->assertGreaterThanOrEqual(3.0, $contrastCyanDark, "cyan-400 on dark must be >= 3:1 for icons");

        // Blue/Cyan Apple: #007AFF on white (3.98:1), #00C4D8 on dark (9.72:1)
        $contrastBlueLight = $calcContrast($colors['blue_apple'], $colors['white']);
        $contrastCyanDarkApple = $calcContrast($colors['cyan_apple'], $colors['dark_bg_card']);
        $this->assertGreaterThanOrEqual(3.0, $contrastBlueLight, "#007AFF on white must be >= 3:1 for icons");
        $this->assertGreaterThanOrEqual(3.0, $contrastCyanDarkApple, "#00C4D8 on dark must be >= 3:1 for icons");
    }

    /**
     * EMPIRICAL TEST 4: Specific View-Level Contrast Scan on Scoped Blade Files.
     * Checks pricing comparison modal, discovery filter labels, and solutions views.
     */
    public function test_empirical_scoped_views_specific_contrast_markers(): void
    {
        // 1. Kasir Gratis comparison table text contrast
        $kasirGratisView = file_get_contents(resource_path('views/public/landing_commercial/kasir_gratis.blade.php'));
        $this->assertStringContainsString(
            'text-slate-600 dark:text-slate-400',
            $kasirGratisView,
            'Kasir gratis comparison table must use text-slate-600 dark:text-slate-400'
        );

        // 2. Discovery index filter label
        $discoveryView = file_get_contents(resource_path('views/public/discovery/index.blade.php'));
        $this->assertStringContainsString(
            'text-slate-600 dark:text-slate-400 font-semibold uppercase tracking-wider',
            $discoveryView,
            'Discovery filter label must use text-slate-600 dark:text-slate-400'
        );

        // 3. Pricing modal checkmark dark mode support
        $pricingView = file_get_contents(resource_path('views/public/pricing.blade.php'));
        $this->assertStringContainsString(
            'dark:text-emerald-400',
            $pricingView,
            'Pricing modal comparison must include dark:text-emerald-400'
        );

        // 4. Industry solutions feature icon boxes dual theme support
        $solutionViews = [
            'fnb' => resource_path('views/public/solutions/fnb.blade.php'),
            'laundry' => resource_path('views/public/solutions/laundry.blade.php'),
            'manufacturing' => resource_path('views/public/solutions/manufacturing.blade.php'),
            'retail' => resource_path('views/public/solutions/retail.blade.php'),
            'services' => resource_path('views/public/solutions/services.blade.php'),
            'workshop' => resource_path('views/public/solutions/workshop.blade.php'),
        ];

        foreach ($solutionViews as $name => $path) {
            $content = file_get_contents($path);
            $this->assertStringContainsString(
                'text-emerald-600 dark:text-emerald-400',
                $content,
                "Solution [{$name}] must specify text-emerald-600 dark:text-emerald-400 on feature icon boxes"
            );
            $this->assertStringContainsString(
                'text-amber-600 dark:text-amber-400',
                $content,
                "Solution [{$name}] must specify text-amber-600 dark:text-amber-400 on feature icon boxes"
            );
        }
    }
}
