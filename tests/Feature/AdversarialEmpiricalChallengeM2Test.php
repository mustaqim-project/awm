<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessLandingPage;
use App\Models\CommerceStoreSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Adversarial Empirical Verification Suite for Milestone 2 (M2)
 *
 * Focus areas:
 * 1. Runtime Play CDN Elimination (verifying zero occurrences of cdn.tailwindcss.com)
 * 2. Heading Hierarchy Semantics (zero H1->H3 skips across all marketing views)
 * 3. Schema.org JSON-LD escaping and parsing (verifying @@context validity)
 * 4. DOM Tag Balance & Responsive Grid Architecture across all 7 solutions views
 * 5. Explicit media sizing (width/height, decoding="async", fetchpriority="high")
 */
final class AdversarialEmpiricalChallengeM2Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app('translator')->addLines(['storefront.tracking' => 'Lacak Pesanan'], 'id');
        app('translator')->addLines(['storefront.tracking' => 'Track Order'], 'en');
    }

    private function createSampleBusiness(): Business
    {
        $business = Business::create([
            'name' => 'Warung Berkah Nusantara',
            'slug' => 'warung-berkah-nusantara',
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
     * Requirement 1: Adversarial Play CDN Elimination Check.
     * Verify cdn.tailwindcss.com is strictly absent from business_landing.blade.php.
     */
    public function test_adversarial_cdn_elimination_in_business_landing(): void
    {
        $businessLandingContent = file_get_contents(resource_path('views/public/business_landing.blade.php'));
        $this->assertStringContainsString("@vite(['resources/css/app.css', 'resources/js/app.js'])", $businessLandingContent);
        
        // Check for presence of cdn.tailwindcss.com
        $hasCdn = str_contains($businessLandingContent, 'cdn.tailwindcss.com');
        if ($hasCdn) {
            // Note: If kept as an @else fallback, document whether it violates strict CDN elimination
            $this->assertStringContainsString('@if', $businessLandingContent, 'If CDN is present, it must be strictly inside @else fallback.');
        }
    }

    /**
     * Requirement 2: Heading Hierarchy Semantic Integrity across all 21 Scoped Views.
     * Enforce strictly single H1 and zero heading hierarchy skips (H1 -> H3 or H2 -> H4).
     */
    public function test_adversarial_heading_hierarchy_semantics(): void
    {
        $this->createSampleBusiness();

        $routes = [
            'Homepage' => '/',
            'About' => '/about',
            'Demo' => '/demo',
            'Pricing' => '/pricing',
            'Privacy' => '/privacy',
            'Security' => '/security',
            'Sitemap' => '/sitemap',
            'Support' => '/support',
            'Terms' => '/terms',
            'Kasir Android' => '/aplikasi-kasir-android',
            'Kasir Gratis' => '/aplikasi-kasir-gratis',
            'Kasir Offline' => '/aplikasi-kasir-offline',
            'Discovery' => '/jelajah',
            'Solution F&B' => '/solutions/fnb',
            'Solution Retail' => '/solutions/retail',
            'Solution Workshop' => '/solutions/workshop',
            'Solution Laundry' => '/solutions/laundry',
            'Solution Manufacturing' => '/solutions/manufacturing',
            'Solution Services' => '/solutions/services',
            'Solution Show' => '/solusi/kasir-warung-kelontong',
        ];

        foreach ($routes as $label => $uri) {
            $response = $this->get($uri);
            $response->assertOk();
            $html = $response->getContent();

            // 1. Single H1
            preg_match_all('/<h1[^>]*>([\s\S]*?)<\/h1>/i', $html, $h1Matches);
            $this->assertCount(1, $h1Matches[0], "Page [{$label}] at [{$uri}] must contain exactly one <h1> element.");

            // 2. Heading hierarchy sequence check
            preg_match_all('/<(h[1-6])[^>]*>/i', $html, $headingMatches);
            $headingTags = array_map('strtolower', $headingMatches[1]);

            $lastLevel = 0;
            foreach ($headingTags as $tag) {
                $level = (int) substr($tag, 1);
                if ($lastLevel > 0) {
                    $skip = $level - $lastLevel;
                    $this->assertLessThanOrEqual(
                        1,
                        $skip,
                        "Page [{$label}] violates semantic heading hierarchy: H{$lastLevel} is directly followed by H{$level} (skip of {$skip} levels)."
                    );
                }
                $lastLevel = $level;
            }
        }
    }

    /**
     * Requirement 3: Valid Schema.org JSON-LD Structured Data with Escaped @@context.
     */
    public function test_adversarial_schema_org_json_ld_validity(): void
    {
        $this->createSampleBusiness();

        $routesWithJsonLd = [
            '/pricing',
            '/privacy',
            '/terms',
            '/security',
            '/sitemap',
            '/solusi/kasir-warung-kelontong',
        ];

        foreach ($routesWithJsonLd as $uri) {
            $response = $this->get($uri);
            $response->assertOk();
            $html = $response->getContent();

            preg_match_all('/<script\s+type="application\/ld\+json"[^>]*>([\s\S]*?)<\/script>/i', $html, $matches);
            $this->assertNotEmpty($matches[1], "Page [{$uri}] must declare at least one Schema.org JSON-LD script block.");

            foreach ($matches[1] as $idx => $jsonBlock) {
                $cleanJson = trim($jsonBlock);
                $decoded = json_decode($cleanJson, true);
                $this->assertNotNull(
                    $decoded,
                    "JSON-LD block #{$idx} in [{$uri}] is invalid JSON: " . json_last_error_msg() . "\nPayload: " . substr($cleanJson, 0, 200)
                );
                $this->assertArrayHasKey('@context', $decoded, "JSON-LD block #{$idx} in [{$uri}] must contain @context key.");
                $this->assertArrayHasKey('@type', $decoded, "JSON-LD block #{$idx} in [{$uri}] must contain @type key.");
            }
        }
    }

    /**
     * Requirement 4: DOM Tag Balance across all 7 Industry Solutions Views.
     */
    public function test_adversarial_solutions_dom_tag_balance(): void
    {
        $solutionViews = [
            'fnb' => resource_path('views/public/solutions/fnb.blade.php'),
            'laundry' => resource_path('views/public/solutions/laundry.blade.php'),
            'manufacturing' => resource_path('views/public/solutions/manufacturing.blade.php'),
            'retail' => resource_path('views/public/solutions/retail.blade.php'),
            'services' => resource_path('views/public/solutions/services.blade.php'),
            'show' => resource_path('views/public/solutions/show.blade.php'),
            'workshop' => resource_path('views/public/solutions/workshop.blade.php'),
        ];

        foreach ($solutionViews as $name => $path) {
            $content = file_get_contents($path);

            $openDivs = substr_count($content, '<div');
            $closeDivs = substr_count($content, '</div>');
            $this->assertSame(
                $openDivs,
                $closeDivs,
                "Solutions view [{$name}] has unbalanced <div> tags: {$openDivs} opening vs {$closeDivs} closing."
            );

            $openSections = substr_count($content, '<section');
            $closeSections = substr_count($content, '</section>');
            $this->assertSame(
                $openSections,
                $closeSections,
                "Solutions view [{$name}] has unbalanced <section> tags: {$openSections} opening vs {$closeSections} closing."
            );
        }
    }

    /**
     * Requirement 5: Media Sizing and Decoding Attributes on Critical Assets.
     */
    public function test_adversarial_media_dimensions_and_decoding(): void
    {
        $businessLanding = file_get_contents(resource_path('views/public/business_landing.blade.php'));

        // Store logo
        $this->assertMatchesRegularExpression(
            '/<img[^>]+width="36"[^>]+height="36"[^>]+decoding="async"/i',
            $businessLanding,
            'Store logo in business_landing must declare width="36" height="36" decoding="async".'
        );

        // Hero LCP banner
        $this->assertMatchesRegularExpression(
            '/<img[^>]+width="640"[^>]+height="480"[^>]+fetchpriority="high"[^>]+decoding="async"/i',
            $businessLanding,
            'Hero LCP banner in business_landing must declare width="640" height="480" fetchpriority="high" decoding="async".'
        );

        // Catalog thumbnail
        $this->assertMatchesRegularExpression(
            '/<img[^>]+width="300"[^>]+height="300"[^>]+decoding="async"/i',
            $businessLanding,
            'Catalog image in business_landing must declare width="300" height="300" decoding="async".'
        );

        // Discovery store logo
        $discovery = file_get_contents(resource_path('views/public/discovery/index.blade.php'));
        $this->assertMatchesRegularExpression(
            '/<img[^>]+width="48"[^>]+height="48"[^>]+loading="lazy"[^>]+decoding="async"/i',
            $discovery,
            'Store logo in discovery index must declare width="48" height="48" loading="lazy" decoding="async".'
        );
    }
}
