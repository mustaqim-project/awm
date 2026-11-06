<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Shared\ReservedSlugService;
use App\Models\Business;
use App\Models\BusinessLandingPage;
use App\Models\CommerceStoreSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicWebsiteThemeIsolationTest extends TestCase
{
    use RefreshDatabase;
    /**
     * Test A: Public Website Integrity
     * Asserts that all public marketing pages maintain cooca-public namespace,
     * consistent COOCA branding, and clean SEO tags without storefront contamination.
     */
    public function test_public_marketing_pages_have_cooca_public_namespace_and_seo_integrity(): void
    {
        $publicRoutes = [
            '/',
            '/about',
            '/pricing',
            '/demo',
            '/kontak',
            '/blog',
            '/business-operating-system/overview',
            '/omnichannel/whatsapp',
            '/solutions/fnb',
            '/resources/faq',
        ];

        foreach ($publicRoutes as $route) {
            $response = $this->get($route);
            $response->assertStatus(200);

            $content = $response->getContent();

            // Verify namespace presence
            $this->assertStringContainsString('cooca-public', $content, "Route {$route} must contain cooca-public namespace class.");
            $this->assertStringContainsString('data-theme-system="cooca-public"', $content, "Route {$route} must declare cooca-public theme system.");

            // Verify site name in Open Graph
            $this->assertStringContainsString('property="og:site_name" content="COOCA"', $content, "Route {$route} must have COOCA as og:site_name.");

            // Verify robots tag is not forced to noindex
            $this->assertStringNotContainsString('content="noindex, nofollow"', $content, "Route {$route} must not be noindex.");
        }
    }

    /**
     * Test A2: Context Leak Isolation Test
     * Asserts that when a logged-in merchant with an active demo/test business visits
     * public marketing pages, their store data does NOT leak into public pages.
     */
    public function test_tenant_context_does_not_leak_into_public_marketing_pages(): void
    {
        $user = User::factory()->create();
        $business = Business::create([
            'name' => 'Toko Demo Leak Test',
            'slug' => 'toko-demo-leak-test',
            'is_active' => true,
            'is_demo' => true,
            'is_test' => true,
        ]);

        $landingPage = BusinessLandingPage::create([
            'business_id' => $business->id,
            'headline' => 'Headline Toko Demo Spesial',
            'theme_color' => '#FF00FF',
            'font_family' => 'Papyrus',
            'is_published' => true,
        ]);

        $user->businesses()->attach($business->id, ['role' => 'owner', 'is_active' => true]);
        $user->active_business_id = $business->id;
        $user->save();

        // Access marketing homepage and about page as the authenticated tenant owner
        $responseHome = $this->actingAs($user)->get('/');
        $responseHome->assertStatus(200);
        $contentHome = $responseHome->getContent();

        $this->assertStringContainsString('cooca-public', $contentHome);
        $this->assertStringNotContainsString('Toko Demo Leak Test', $contentHome, 'Public homepage must not show tenant store name.');
        $this->assertStringNotContainsString('Headline Toko Demo Spesial', $contentHome, 'Public homepage must not show tenant landing headline.');
        $this->assertStringNotContainsString('content="noindex', $contentHome, 'Public homepage must not inherit tenant demo noindex.');
        $this->assertStringContainsString('property="og:site_name" content="COOCA"', $contentHome);

        $responseAbout = $this->actingAs($user)->get('/about');
        $responseAbout->assertStatus(200);
        $contentAbout = $responseAbout->getContent();

        $this->assertStringContainsString('cooca-public', $contentAbout);
        $this->assertStringNotContainsString('Toko Demo Leak Test', $contentAbout);
        $this->assertStringNotContainsString('content="noindex', $contentAbout);
        $this->assertStringContainsString('property="og:site_name" content="COOCA"', $contentAbout);
    }

    /**
     * Test B: Storefront Functionality & Scoping
     * Asserts that storefront has cooca-storefront namespace, inherits its theme tokens,
     * and functions independently.
     */
    public function test_storefront_pages_have_cooca_storefront_namespace_and_configured_theme(): void
    {
        $business = Business::create([
            'name' => 'Kopi Artisan Nusantara',
            'slug' => 'kopi-artisan-nusantara',
            'is_active' => true,
        ]);

        CommerceStoreSetting::create([
            'business_id' => $business->id,
            'is_storefront_enabled' => true,
        ]);

        BusinessLandingPage::create([
            'business_id' => $business->id,
            'headline' => 'Seduhan Kopi Asli Indonesia',
            'subheadline' => 'Kopi pilihan dari petani lokal Jawa dan Sumatra.',
            'is_published' => true,
            'theme_preset' => 'coffee_beverage',
            'theme_color' => '#6F4E37',
            'active_pages' => [
                'home' => true,
                'catalog' => true,
                'about' => true,
                'contact' => true,
            ],
        ]);

        $response = $this->get('/kopi-artisan-nusantara');
        $response->assertStatus(200);
        $content = $response->getContent();

        // Must declare storefront namespace
        $this->assertStringContainsString('cooca-storefront', $content);
        $this->assertStringContainsString('data-theme-system="cooca-storefront"', $content);
        $this->assertStringContainsString('Seduhan Kopi Asli Indonesia', $content);

        // Storefront catalog
        $responseCatalog = $this->get('/kopi-artisan-nusantara/katalog');
        $responseCatalog->assertStatus(200);
        $contentCatalog = $responseCatalog->getContent();
        $this->assertStringContainsString('cooca-storefront', $contentCatalog);
    }

    /**
     * Test C: Reverse Isolation Test
     * Asserts that changes in tenant landing page settings (theme color, font, headline)
     * NEVER affect public marketing pages.
     */
    public function test_reverse_isolation_landing_page_changes_do_not_affect_public_website(): void
    {
        $business = Business::create([
            'name' => 'Bengkel Mobil Maju Jaya',
            'slug' => 'bengkel-mobil-maju-jaya',
            'is_active' => true,
        ]);

        $landingPage = BusinessLandingPage::create([
            'business_id' => $business->id,
            'headline' => 'Spesialis Tune Up dan Ganti Oli',
            'theme_preset' => 'service_workshop',
            'theme_color' => '#E11D48',
            'font_family' => 'Oswald',
            'is_published' => true,
        ]);

        // Public marketing page before and after
        $publicPricing = $this->get('/pricing')->getContent();
        $this->assertStringNotContainsString('Bengkel Mobil Maju Jaya', $publicPricing);
        $this->assertStringNotContainsString('Spesialis Tune Up dan Ganti Oli', $publicPricing);
        $this->assertStringNotContainsString('#E11D48', $publicPricing);
        $this->assertStringContainsString('cooca-public', $publicPricing);

        // Mutate landing page
        $landingPage->update([
            'headline' => 'Headline Diubah Total',
            'theme_color' => '#10B981',
            'font_family' => 'Cinzel',
        ]);

        $publicPricingAfter = $this->get('/pricing')->getContent();
        $this->assertStringNotContainsString('Headline Diubah Total', $publicPricingAfter);
        $this->assertStringNotContainsString('#10B981', $publicPricingAfter);
        $this->assertStringContainsString('cooca-public', $publicPricingAfter);
    }

    /**
     * Test D: Route Collision & Reserved Slug Protection
     * Asserts that system routes are never intercepted by Storefront controller.
     */
    public function test_reserved_slug_protection_blocks_storefront_route_collision(): void
    {
        $this->assertTrue(ReservedSlugService::isReserved('about'));
        $this->assertTrue(ReservedSlugService::isReserved('pricing'));
        $this->assertTrue(ReservedSlugService::isReserved('demo'));
        $this->assertTrue(ReservedSlugService::isReserved('solutions'));
        $this->assertTrue(ReservedSlugService::isReserved('marketplace'));
        $this->assertTrue(ReservedSlugService::isReserved('blog'));
        $this->assertTrue(ReservedSlugService::isReserved('kontak'));
        $this->assertTrue(ReservedSlugService::isReserved('cooca'));
        $this->assertTrue(ReservedSlugService::isReserved('cooca-indonesia'));

        // Brand redirects
        $responseCooca = $this->get('/cooca');
        $responseCooca->assertRedirect('/');

        $responseCoocaId = $this->get('/cooca-indonesia');
        $responseCoocaId->assertRedirect('/');

        // Public marketing routes must load marketing views, NOT storefront
        $responseAbout = $this->get('/about');
        $responseAbout->assertStatus(200);
        $this->assertStringContainsString('cooca-public', $responseAbout->getContent());
        $this->assertStringNotContainsString('cooca-storefront', $responseAbout->getContent());

        $responsePricing = $this->get('/pricing');
        $responsePricing->assertStatus(200);
        $this->assertStringContainsString('cooca-public', $responsePricing->getContent());
        $this->assertStringNotContainsString('cooca-storefront', $responsePricing->getContent());
    }

    /**
     * Test E: Preview Isolation & Iframe Sandbox
     * Asserts that preview iframe in landing page builder has strict sandbox attributes.
     */
    public function test_landing_page_builder_preview_iframe_has_sandbox_attribute(): void
    {
        $editorPath = resource_path('views/app/landing_page/edit.blade.php');
        $editorContent = file_get_contents($editorPath);

        $this->assertStringContainsString('sandbox="allow-scripts allow-same-origin allow-forms allow-popups"', $editorContent);
    }

    /**
     * Test F: Comprehensive Dark Mode & Light Mode Theme Compatibility Audit
     * Asserts that both Public Marketing and Storefront ecosystems fully support
     * seamless dark and light modes with anti-FOUC scripts, CSS variables, and toggles.
     */
    public function test_dark_and_light_mode_theme_compatibility(): void
    {
        // 1. Audit Public Marketing Layout for Dark & Light Mode
        $publicMarketingHtml = $this->get('/')->getContent();

        // Check Anti-FOUC synchronous head script
        $this->assertStringContainsString("localStorage.getItem('cooca-theme')", $publicMarketingHtml);
        $this->assertStringContainsString("document.documentElement.classList.add('dark')", $publicMarketingHtml);
        $this->assertStringContainsString("document.documentElement.classList.remove('dark')", $publicMarketingHtml);

        // Check Tailwind darkMode: 'class'
        $this->assertStringContainsString("darkMode: 'class'", $publicMarketingHtml);

        // Check CSS variables for both light and dark modes
        $this->assertStringContainsString('--apple-bg: #F5F5F7', $publicMarketingHtml);
        $this->assertStringContainsString('--apple-text: #1D1D1F', $publicMarketingHtml);
        $this->assertStringContainsString('--apple-card: #FFFFFF', $publicMarketingHtml);
        $this->assertStringContainsString('--apple-bg: #000000', $publicMarketingHtml);
        $this->assertStringContainsString('--apple-text: #F5F5F7', $publicMarketingHtml);
        $this->assertStringContainsString('--apple-card: #1C1C1E', $publicMarketingHtml);

        // Check accessible dark mode select options
        $this->assertStringContainsString('html.dark select option', $publicMarketingHtml);

        // Check Theme Switcher button presence
        $this->assertStringContainsString('toggleTheme()', $publicMarketingHtml);

        // 2. Audit Storefront Layout for Dark & Light Mode
        $businessLight = Business::create([
            'name' => 'Toko Mode Terang',
            'slug' => 'toko-mode-terang',
            'is_active' => true,
        ]);
        BusinessLandingPage::create([
            'business_id' => $businessLight->id,
            'headline' => 'Toko Siang Terang',
            'dark_mode' => false,
            'is_published' => true,
        ]);

        $responseLight = $this->get('/toko-mode-terang');
        $responseLight->assertStatus(200);
        $contentLight = $responseLight->getContent();

        // Light mode HTML should NOT have initial 'dark' class
        $this->assertMatchesRegularExpression('/<html[^>]*class="[^"]*(?<!\bdark\b)scroll-smooth[^"]*"[^>]*>/', $contentLight);
        $this->assertStringContainsString("localStorage.getItem('storefront_dark_mode')", $contentLight);
        $this->assertStringContainsString('window.toggleDarkMode', $contentLight);

        // 3. Test Storefront Default Dark Mode
        $businessDark = Business::create([
            'name' => 'Toko Mode Gelap',
            'slug' => 'toko-mode-gelap',
            'is_active' => true,
        ]);
        BusinessLandingPage::create([
            'business_id' => $businessDark->id,
            'headline' => 'Toko Malam Gelap',
            'dark_mode' => true,
            'is_published' => true,
        ]);

        $responseDark = $this->get('/toko-mode-gelap');
        $responseDark->assertStatus(200);
        $contentDark = $responseDark->getContent();

        // Dark mode HTML must have 'dark' class
        $this->assertMatchesRegularExpression('/<html[^>]*class="[^"]*\bdark\b[^"]*"[^>]*>/', $contentDark);
        $this->assertStringContainsString('dark:text-neutral-100', $contentDark);
    }
}
