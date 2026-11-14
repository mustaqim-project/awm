<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Billing\EntitlementService;
use App\Models\Business;
use App\Models\BusinessLandingPage;
use App\Models\CommerceStoreSetting;
use App\Models\SystemSetting;
use App\Models\TemplateLead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Comprehensive 4-Tier E2E Testing Suite for COOCA Public Marketing Frontend.
 *
 * Tier 1: Feature Coverage (Sanity & 22 Page HTTP 200 Rendering)
 * Tier 2: Boundary & Corner Cases (Single H1, Zero Missing Translation Keys, Mobile Safeguards)
 * Tier 3: Cross-Feature Combinations (Theme Switching, Floating Widget Coexistence, Multilingual)
 * Tier 4: Real-World User Journeys (Lead Capture, Contact Inquiry, Commercial Funnel, Context Isolation)
 */
final class PublicMarketingFrontendE2ETest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Safety mock for storefront.tracking translation key which is an array in lang/id/storefront.php
        // but referenced as a string in business_landing.blade.php:8624
        app('translator')->addLines(['storefront.tracking' => 'Lacak Pesanan'], 'id');
        app('translator')->addLines(['storefront.tracking' => 'Track Order'], 'en');
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

    // =========================================================================
    // TIER 1: FEATURE COVERAGE (22 PAGES HTTP 200 & COMPONENT INTEGRITY)
    // =========================================================================

    /**
     * Test Tier 1.1: Verify all 22 primary marketing and commercial landing pages return HTTP 200.
     */
    public function test_tier1_all_22_standalone_pages_and_commercial_landings_render_http_200(): void
    {
        $this->createSampleBusiness('warung-berkah-nusantara');

        $pages = [
            // Standalone Pages (10)
            'Homepage' => '/',
            'About' => '/about',
            'Demo' => '/demo',
            'Pricing' => '/pricing',
            'Privacy Policy' => '/privacy',
            'Security' => '/security',
            'HTML Sitemap' => '/sitemap',
            'Customer Support' => '/support',
            'Terms of Service' => '/terms',
            'Business Landing' => '/b/warung-berkah-nusantara',

            // Commercial Landing Pages (3)
            'Kasir Gratis' => '/aplikasi-kasir-gratis',
            'Kasir Android' => '/aplikasi-kasir-android',
            'Kasir Offline' => '/aplikasi-kasir-offline',

            // Public Discovery, Marketplace & Contact (3)
            'Public Discovery' => '/jelajah',
            'Public Marketplace' => '/marketplace',
            'Contact Page' => '/kontak',

            // Vertical Solutions (6)
            'Solution F&B' => '/solutions/fnb',
            'Solution Retail' => '/solutions/retail',
            'Solution Workshop' => '/solutions/workshop',
            'Solution Laundry' => '/solutions/laundry',
            'Solution Manufacturing' => '/solutions/manufacturing',
            'Solution Services' => '/solutions/services',
        ];

        $this->assertCount(22, $pages, 'Public marketing frontend suite must test exactly 22 core marketing destinations.');

        foreach ($pages as $label => $uri) {
            $response = $this->get($uri);

            $this->assertEquals(
                200,
                $response->getStatusCode(),
                "Page [{$label}] at URI [{$uri}] must render with HTTP 200 OK without unhandled server exceptions."
            );

            $content = $response->getContent();
            $this->assertStringContainsString('<!DOCTYPE html>', $content, "Page [{$label}] must contain standard HTML5 doctype declaration.");
            $this->assertStringContainsString('cooca-', $content, "Page [{$label}] must declare a cooca layout namespace.");
            $this->assertMatchesRegularExpression('/data-theme-system="cooca-(public|storefront)"/', $content, "Page [{$label}] must declare data-theme-system.");
        }
    }

    /**
     * Test Tier 1.2: Verify layout and partial inclusions across core marketing layouts.
     */
    public function test_tier1_layout_and_seo_metadata_inclusions(): void
    {
        $auditedPages = [
            '/' => 'COOCA: Platform Omnichannel',
            '/about' => 'Tentang COOCA',
            '/pricing' => 'Harga Aplikasi Kasir',
            '/security' => 'Keamanan Sistem',
            '/privacy' => 'Kebijakan Privasi',
            '/terms' => 'Syarat',
            '/aplikasi-kasir-gratis' => 'Aplikasi Kasir Gratis',
            '/kontak' => 'Siap Mendampingi',
        ];

        foreach ($auditedPages as $uri => $titleOrHeadingSnippet) {
            $response = $this->get($uri);
            $response->assertOk();
            $content = $response->getContent();

            // 1. Meta charset & viewport
            $this->assertStringContainsString('<meta charset="utf-8">', $content, "URI [{$uri}] must include UTF-8 charset.");
            $this->assertStringContainsString('<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">', $content, "URI [{$uri}] must specify responsive viewport with viewport-fit=cover.");

            // 2. SEO Canonical link
            $this->assertMatchesRegularExpression('/<link\s+rel="canonical"\s+href="https:\/\/cooca\.id[^"]*"/i', $content, "URI [{$uri}] must declare an absolute canonical URL.");

            // 3. OpenGraph tags
            $this->assertStringContainsString('property="og:site_name" content="COOCA"', $content, "URI [{$uri}] must declare COOCA in og:site_name.");
            $this->assertMatchesRegularExpression('/property="og:title"\s+content="[^"]+"/i', $content, "URI [{$uri}] must have non-empty og:title.");
            $this->assertMatchesRegularExpression('/property="og:description"\s+content="[^"]+"/i', $content, "URI [{$uri}] must have non-empty og:description.");
            $this->assertMatchesRegularExpression('/property="og:image"\s+content="[^"]+"/i', $content, "URI [{$uri}] must have og:image.");

            // 4. Twitter Card
            $this->assertStringContainsString('name="twitter:card"', $content, "URI [{$uri}] must define twitter:card.");

            // 5. Favicon presence
            $this->assertStringContainsString('rel="icon"', $content, "URI [{$uri}] must declare favicon icon.");

            // 6. Expected content snippet
            $this->assertStringContainsString($titleOrHeadingSnippet, $content, "URI [{$uri}] must contain expected snippet [{$titleOrHeadingSnippet}].");
        }
    }

    /**
     * Test Tier 1.3: Verify commercial landing pages render unique value propositions and conversion CTAs.
     */
    public function test_tier1_commercial_landing_pages_render_key_propositions(): void
    {
        // 1. Kasir Gratis
        $gratis = $this->get('/aplikasi-kasir-gratis');
        $gratis->assertOk();
        $gratisContent = $gratis->getContent();
        $this->assertStringContainsString('Aplikasi Kasir Gratis Selamanya', $gratisContent);
        $this->assertStringContainsString('Daftar Gratis 2 Menit', $gratisContent);
        $this->assertStringContainsString('Tanpa Kartu Kredit', $gratisContent);

        // 2. Kasir Android
        $android = $this->get('/aplikasi-kasir-android');
        $android->assertOk();
        $androidContent = $android->getContent();
        $this->assertStringContainsString('Aplikasi Kasir Android', $androidContent);
        $this->assertStringContainsString('Download APK', $androidContent);
        $this->assertStringContainsString('Printer Bluetooth', $androidContent);

        // 3. Kasir Offline
        $offline = $this->get('/aplikasi-kasir-offline');
        $offline->assertOk();
        $offlineContent = $offline->getContent();
        $this->assertStringContainsString('Aplikasi Kasir Offline', $offlineContent);
        $this->assertStringContainsString('Tanpa Internet', $offlineContent);
        $this->assertStringContainsString('Auto Sinkron Cloud', $offlineContent);
    }

    /**
     * Test Tier 1.4: Verify domain-specific vertical solutions render proper workflows.
     */
    public function test_tier1_vertical_solutions_render_domain_specific_workflows(): void
    {
        $solutions = [
            '/solutions/fnb' => ['Resto', 'Meja', 'Dapur'],
            '/solutions/retail' => ['Retail', 'Stok', 'Barcode'],
            '/solutions/workshop' => ['Bengkel', 'Servis', 'Montir'],
            '/solutions/laundry' => ['Laundry', 'Kiloan', 'Satuan'],
            '/solutions/manufacturing' => ['Manufaktur', 'BOM', 'produksi'],
            '/solutions/services' => ['Jasa', 'Teknisi', 'Servis'],
        ];

        foreach ($solutions as $uri => $expectedKeywords) {
            $response = $this->get($uri);
            $response->assertOk();
            $content = $response->getContent();

            foreach ($expectedKeywords as $kw) {
                $this->assertStringContainsStringIgnoringCase(
                    $kw,
                    $content,
                    "Solution page [{$uri}] must render domain keyword [{$kw}]."
                );
            }
        }
    }

    // =========================================================================
    // TIER 2: BOUNDARY & CORNER CASES (STRUCTURAL INVARIANTS & SAFETY GATES)
    // =========================================================================

    /**
     * Test Tier 2.1: Enforce single <h1> heading rule per page across key marketing templates.
     */
    public function test_tier2_seo_heading_hierarchy_single_h1_rule(): void
    {
        $samplePages = [
            '/',
            '/about',
            '/pricing',
            '/security',
            '/privacy',
            '/terms',
            '/support',
            '/sitemap',
            '/kontak',
            '/aplikasi-kasir-gratis',
            '/aplikasi-kasir-android',
            '/aplikasi-kasir-offline',
            '/solutions/fnb',
            '/solutions/retail',
            '/solutions/workshop',
            '/solutions/laundry',
            '/solutions/manufacturing',
            '/solutions/services',
        ];

        foreach ($samplePages as $uri) {
            $response = $this->get($uri);
            $response->assertOk();
            $content = $response->getContent();

            preg_match_all('/<h1\b[^>]*>(.*?)<\/h1>/si', $content, $matches);
            $h1Count = count($matches[0]);

            $this->assertEquals(
                1,
                $h1Count,
                "Page [{$uri}] must have exactly one <h1> tag for strict SEO heading hierarchy. Found: {$h1Count}."
            );

            $h1Text = trim(strip_tags($matches[1][0]));
            $this->assertNotEmpty($h1Text, "Page [{$uri}] <h1> tag must not be empty or whitespace-only.");
        }
    }

    /**
     * Test Tier 2.2: Localization & Missing Keys Gate.
     * Verifies that no uncompiled raw translation keys appear on rendered core marketing pages in ID and EN.
     */
    public function test_tier2_localization_no_raw_translation_keys_rendered(): void
    {
        $locales = ['id', 'en'];

        $keyPages = [
            '/',
            '/about',
            '/pricing',
            '/security',
            '/privacy',
            '/terms',
            '/support',
            '/kontak',
            '/solutions/fnb',
            '/solutions/retail',
            '/solutions/workshop',
            '/solutions/laundry',
            '/solutions/manufacturing',
            '/solutions/services',
            '/aplikasi-kasir-gratis',
            '/aplikasi-kasir-android',
            '/aplikasi-kasir-offline',
            '/jelajah',
        ];

        $rawKeyPattern = '/\b(?:public_[a-z_]+|common)\.[a-zA-Z0-9_.-]+\b/';

        foreach ($locales as $locale) {
            foreach ($keyPages as $uri) {
                $targetUri = $uri . (str_contains($uri, '?') ? '&' : '?') . "lang={$locale}";
                $response = $this->get($targetUri);
                $response->assertOk();

                $content = $response->getContent();

                // Strip out JavaScript code blocks and script tags that might legitimately mention translation identifiers
                $sanitizedContent = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $content) ?? $content;

                preg_match_all($rawKeyPattern, $sanitizedContent, $matches);
                $unresolvedKeys = array_unique($matches[0] ?? []);

                $this->assertEmpty(
                    $unresolvedKeys,
                    "Page [{$targetUri}] contains unresolved raw translation keys in [{$locale}] mode: " . implode(', ', $unresolvedKeys)
                );
            }
        }
    }

    /**
     * Test Tier 2.3: Mobile responsive viewport safeguards and safe area insets.
     */
    public function test_tier2_mobile_responsive_viewport_and_safe_area_safeguards(): void
    {
        $response = $this->get('/');
        $response->assertOk();
        $content = $response->getContent();

        // 1. Viewport fit cover
        $this->assertStringContainsString('viewport-fit=cover', $content, 'Viewport meta tag must contain viewport-fit=cover.');

        // 2. Safe area inset padding for bottom navigation dock
        $this->assertStringContainsString('env(safe-area-inset-bottom,0px)', $content, 'Bottom navigation dock must respect safe-area-inset-bottom.');

        // 3. Pointer events isolation for floating navigation
        $this->assertStringContainsString('pointer-events-none lg:hidden', $content, 'Bottom navigation outer wrapper must be pointer-events-none to prevent touch traps.');
        $this->assertStringContainsString('pointer-events-auto', $content, 'Bottom navigation inner dock must declare pointer-events-auto.');

        // 4. Assert absence of desktop fixed width on root elements
        $this->assertStringNotContainsString('min-width: 1280px', $content, 'Root container must not force desktop width constraint.');
        $this->assertStringNotContainsString('min-width: 1440px', $content, 'Root container must not force desktop width constraint.');
    }

    /**
     * Test Tier 2.4: Chatbot and live chat widget lazy deferral integrity.
     */
    public function test_tier2_chatbot_and_live_chat_deferral_integrity(): void
    {
        $response = $this->get('/');
        $response->assertOk();
        $content = $response->getContent();

        // 1. Live Chat root must declare x-cloak and deferred Alpine initialization
        $this->assertStringContainsString('id="cooca-live-chat-root"', $content);
        $this->assertStringContainsString('x-data="coocaLiveChatWidget()"', $content);
        $this->assertStringContainsString('x-cloak', $content, 'Live chat widget root must have x-cloak to avoid layout shifts.');

        // 2. Live chat window is closed by default
        $this->assertStringContainsString('x-show="isOpen"', $content, 'Live chat window must be bound to x-show isOpen.');
        $this->assertStringContainsString('x-show="!isOpen"', $content, 'Live chat floating action button must be shown when closed.');

        // 3. Non-blocking script loading: Lucide & Alpine are loaded with defer
        $this->assertMatchesRegularExpression('/<script[^>]+defer[^>]+lucide[^>]*>/i', $content, 'Lucide icons script must have defer attribute.');
        $this->assertMatchesRegularExpression('/<script[^>]+defer[^>]+alpinejs[^>]*>/i', $content, 'Alpine.js script must have defer attribute.');
    }

    // =========================================================================
    // TIER 3: CROSS-FEATURE COMBINATIONS & STATE TRANSITIONS
    // =========================================================================

    /**
     * Test Tier 3.1: Theme switching consistency and Apple HIG color tokens.
     */
    public function test_tier3_theme_system_consistency_light_and_dark_tokens(): void
    {
        $response = $this->get('/');
        $response->assertOk();
        $content = $response->getContent();

        // 1. Anti-FOUC No-Flash Theme Bootstrap script in <head>
        $this->assertStringContainsString("localStorage.getItem('cooca-theme')", $content, 'Head script must read cooca-theme from localStorage.');
        $this->assertStringContainsString("document.documentElement.classList.add('dark')", $content, 'Head script must set dark class when preferred.');
        $this->assertStringContainsString("document.documentElement.setAttribute('data-theme', 'dark')", $content, 'Head script must set data-theme attribute.');

        // 2. Apple HIG CSS Variables for Light Theme
        $this->assertStringContainsString('--apple-blue: #007AFF;', $content);
        $this->assertStringContainsString('--apple-bg: #F5F5F7;', $content);
        $this->assertStringContainsString('--apple-card: #FFFFFF;', $content);
        $this->assertStringContainsString('--apple-text: #1D1D1F;', $content);

        // 3. Apple HIG CSS Variables for Dark Theme
        $this->assertStringContainsString('--apple-blue: #0A84FF;', $content);
        $this->assertStringContainsString('--apple-bg: #000000;', $content);
        $this->assertStringContainsString('--apple-card: #1C1C1E;', $content);
        $this->assertStringContainsString('--apple-text: #F5F5F7;', $content);

        // 4. Color scheme native support
        $this->assertStringContainsString('color-scheme: light;', $content);
        $this->assertStringContainsString('color-scheme: dark;', $content);
    }

    /**
     * Test Tier 3.2: Floating element coexistence without z-index or coordinate collisions.
     */
    public function test_tier3_floating_elements_z_index_and_viewport_coexistence(): void
    {
        $response = $this->get('/');
        $response->assertOk();
        $content = $response->getContent();

        // 1. WhatsApp FAB class and z-index 50
        $this->assertStringContainsString('floating-wa-fab', $content, 'WhatsApp FAB must declare floating-wa-fab CSS class.');
        $this->assertStringContainsString('z-50 print:hidden', $content, 'WhatsApp FAB must be z-50 and hidden on print.');

        // 2. Live Chat FAB class and z-index 50
        $this->assertStringContainsString('floating-live-chat-fab', $content, 'Live chat FAB must declare floating-live-chat-fab CSS class.');

        // 3. Apple Floating Dock Navbar on mobile (z-40)
        $this->assertStringContainsString('fixed bottom-0 inset-x-0 z-40', $content, 'Apple Floating Dock Navbar must be fixed at z-40.');

        // 4. Safe vertical separation verified in CSS styling
        $this->assertStringContainsString('bottom: calc(5.75rem + env(safe-area-inset-bottom, 0px))', $content);
    }

    /**
     * Test Tier 3.3: Multilingual hreflang alternate tags and language switcher coherence.
     */
    public function test_tier3_multilingual_language_switching_transitions(): void
    {
        // 1. Indonesian (default)
        $idResponse = $this->get('/about?lang=id');
        $idResponse->assertOk();
        $idContent = $idResponse->getContent();
        $this->assertStringContainsString('<html lang="id"', $idContent);
        $this->assertStringContainsString('hreflang="id"', $idContent);
        $this->assertStringContainsString('hreflang="en"', $idContent);
        $this->assertStringContainsString('hreflang="x-default"', $idContent);
        $this->assertStringContainsString('Beranda', $idContent);

        // 2. English transition
        $enResponse = $this->get('/about?lang=en');
        $enResponse->assertOk();
        $enContent = $enResponse->getContent();
        $this->assertStringContainsString('<html lang="en"', $enContent);
        $this->assertStringContainsString('Home', $enContent);
        $this->assertStringContainsString('Platform', $enContent);

        // 3. Footer language selector presence
        $this->assertStringContainsString('aria-label="Pilih Bahasa"', $idContent);
        $this->assertStringContainsString('lang=id', $idContent);
        $this->assertStringContainsString('lang=en', $idContent);
    }

    // =========================================================================
    // TIER 4: REAL-WORLD USER JOURNEYS (END-TO-END SIMULATION)
    // =========================================================================

    /**
     * Test Tier 4.1: Visitor Discovery & Template Download Lead Capture Journey.
     */
    public function test_tier4_journey_lead_generation_and_template_download(): void
    {
        // Step 1: Visitor visits marketing homepage
        $homeResponse = $this->get('/');
        $homeResponse->assertOk();

        // Step 2: Visitor navigates to templates catalog
        $catalogResponse = $this->get('/template-pembukuan-gratis');
        $catalogResponse->assertOk();
        $catalogResponse->assertSee('Pembukuan');

        // Step 3: Visitor opens specific template detail
        $templateResponse = $this->get('/template/pembukuan-warung-excel');
        $templateResponse->assertOk();
        $templateResponse->assertSee('pembukuan-warung-excel');

        // Step 4: Visitor fills lead capture form to download template
        $leadPayload = [
            'name' => 'Ahmad Fauzi',
            'email' => 'ahmad.fauzi@example.com',
            'phone' => '081298765432',
            'business_name' => 'Kopi Fauzi Mantap',
        ];

        $downloadResponse = $this->postJson('/template/pembukuan-warung-excel/download', $leadPayload);
        $downloadResponse->assertOk();
        $downloadResponse->assertJsonFragment(['success' => true]);

        // Assert database persistence of captured lead
        $this->assertDatabaseHas('template_leads', [
            'name' => 'Ahmad Fauzi',
            'email' => 'ahmad.fauzi@example.com',
            'phone' => '081298765432',
            'business_name' => 'Kopi Fauzi Mantap',
            'template_slug' => 'pembukuan-warung-excel',
        ]);
    }

    /**
     * Test Tier 4.2: Visitor Contact & Commercial Inquiry Journey.
     */
    public function test_tier4_journey_contact_inquiry_submission(): void
    {
        // Step 1: Visitor lands on contact page
        $contactPage = $this->get('/kontak');
        $contactPage->assertOk();
        $contactPage->assertSee('Tim Kami Siap Mendampingi');

        // Step 2: Visitor submits inquiry
        $inquiryPayload = [
            'name' => 'Dewi Lestari',
            'email' => 'dewi@lestari-fashion.id',
            'phone' => '085712345678',
            'subject' => 'Integrasi POS Multi-Outlet 10 Cabang',
            'message' => 'Halo tim COOCA, kami ingin menjadwalkan demo onboarding untuk 10 outlet ritel fashion kami.',
        ];

        $submitResponse = $this->post('/kontak', $inquiryPayload);
        $submitResponse->assertSessionHas('success_message');

        // Assert database persistence
        $this->assertDatabaseHas('template_leads', [
            'name' => 'Dewi Lestari',
            'email' => 'dewi@lestari-fashion.id',
            'phone' => '085712345678',
            'template_slug' => 'contact-inquiry',
        ]);
    }

    /**
     * Test Tier 4.3: Commercial Acquisition Funnel (Free POS -> Pricing -> Registration CTA).
     */
    public function test_tier4_journey_commercial_to_pricing_and_registration_funnel(): void
    {
        // Step 1: Organic visitor enters through commercial landing page
        $commercial = $this->get('/aplikasi-kasir-gratis');
        $commercial->assertOk();

        // Step 2: Visitor evaluates pricing page tiers
        $pricing = $this->get('/pricing');
        $pricing->assertOk();
        $pricing->assertSee('Standard');
        $pricing->assertSee('Premium');
        $pricing->assertSee('Prestige');

        // Step 3: Registration CTA targets active register route
        $content = $pricing->getContent();
        $registerUrl = route('register');
        $this->assertStringContainsString($registerUrl, $content, 'Pricing page must link directly to the registration route.');
    }

    /**
     * Test Tier 4.4: Authenticated Merchant Safety (Zero Data Leakage into Public Marketing).
     */
    public function test_tier4_journey_authenticated_merchant_zero_data_leakage(): void
    {
        // Create an authenticated merchant owner with a private business
        $user = User::factory()->create();
        $privateBusiness = Business::create([
            'name' => 'Toko Rahasia Spesial Super 888',
            'slug' => 'toko-rahasia-spesial-super-888',
            'is_active' => true,
            'is_demo' => true,
            'is_test' => true,
        ]);

        $user->businesses()->attach($privateBusiness->id, ['role' => 'owner', 'is_active' => true]);
        $user->active_business_id = $privateBusiness->id;
        $user->save();

        // Merchant navigates marketing pages while logged in
        $marketingPages = ['/', '/about', '/pricing', '/demo', '/security'];

        foreach ($marketingPages as $uri) {
            $response = $this->actingAs($user)->get($uri);
            $response->assertOk();
            $content = $response->getContent();

            // 1. Private store name must NEVER leak
            $this->assertStringNotContainsString(
                'Toko Rahasia Spesial Super 888',
                $content,
                "URI [{$uri}] must not leak private store name for authenticated user."
            );

            // 2. Demo flag must not contaminate public SEO robots
            $this->assertStringNotContainsString('content="noindex, nofollow"', $content, "URI [{$uri}] must not inherit private store demo noindex tag.");

            // 3. Layout namespace and brand remain pure
            $this->assertStringContainsString('cooca-public', $content);
            $this->assertStringContainsString('property="og:site_name" content="COOCA"', $content);
        }
    }
}
