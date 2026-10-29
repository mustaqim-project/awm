<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Middleware\AllowVerifiedCrawlersMiddleware;
use App\Models\Business;
use App\Services\Seo\SitemapService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class WebsiteAuditPromptsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Prompt 1: Social proof & credibility in Hero.
     */
    public function test_prompt_one_hero_social_proof_is_factual_and_configurable(): void
    {
        config(['marketing.social_proof.show' => false]);

        $response = $this->get('/');
        $response->assertStatus(200);

        // Factual trust badges present
        $response->assertSee('Gratis untuk mulai');
        $response->assertSee('Tanpa kartu kredit');
        $response->assertSee('Data milik Anda 100%');
        $response->assertSee('Dibuat untuk UMKM Indonesia');

        // Unverified claims suppressed
        $response->assertDontSee('Dari 10.000+ Pemilik Usaha');
        $response->assertDontSee('"aggregateRating"');
    }

    /**
     * Prompt 2: Case studies labeled as illustration with statutory disclaimer.
     */
    public function test_prompt_two_case_studies_have_illustration_badge_and_disclaimer(): void
    {
        $response = $this->get('/resources/case-studies');
        $response->assertStatus(200);

        $response->assertSee('Contoh Skenario: Bagaimana UMKM Menutup Kebocoran Kasir', false);
        $response->assertSee('Naik', false);
        $response->assertSee('Kelas', false);
        $response->assertSee('Contoh Skenario');
        $response->assertSee('Angka merupakan ilustrasi berdasarkan skenario umum dan bukan jaminan hasil.');
        $response->assertDontSee('Audit Operasional Terverifikasi');
    }

    /**
     * Prompt 3: Marketplace scope enforces is_test, is_demo, and is_public_listed.
     */
    public function test_prompt_three_marketplace_scope_excludes_test_stores(): void
    {
        $sql = Business::query()->publicListed()->toSql();

        $this->assertStringContainsString('is_test', $sql);
        $this->assertStringContainsString('is_demo', $sql);
        $this->assertStringContainsString('is_public_listed', $sql);
    }

    /**
     * Prompt 4: Verified crawler middleware passes search engine & social bots.
     */
    public function test_prompt_four_allow_verified_crawlers_middleware(): void
    {
        $middleware = new AllowVerifiedCrawlersMiddleware();

        // Localhost Googlebot is allowed
        $this->assertTrue($middleware->isVerifiedCrawler('Googlebot/2.1 (+http://www.google.com/bot.html)', '127.0.0.1'));
        // WhatsApp link preview is allowed
        $this->assertTrue($middleware->isVerifiedCrawler('WhatsApp/2.21.12.21 A', '203.0.113.1'));
        // Facebook external hit is allowed
        $this->assertTrue($middleware->isVerifiedCrawler('facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)', '203.0.113.2'));
        // Regular browser is not flagged as verified crawler
        $this->assertFalse($middleware->isVerifiedCrawler('Mozilla/5.0 (Windows NT 10.0; Win64; x64)', '203.0.113.3'));
    }

    /**
     * Prompt 5: Sitemap split into index and 4 sub-sitemaps with HTTPS URLs.
     */
    public function test_prompt_five_sitemap_index_and_sub_sitemaps(): void
    {
        $sitemapService = app(SitemapService::class);

        $indexXml = $sitemapService->generateSitemapIndexXml();
        $this->assertStringContainsString('<sitemapindex', $indexXml);
        $this->assertStringContainsString('https://cooca.id/sitemap_pages.xml', $indexXml);
        $this->assertStringContainsString('https://cooca.id/sitemap_blog.xml', $indexXml);
        $this->assertStringContainsString('https://cooca.id/sitemap_marketplace.xml', $indexXml);
        $this->assertStringContainsString('https://cooca.id/sitemap_tools.xml', $indexXml);

        $pagesXml = $sitemapService->generatePagesXml();
        $this->assertStringContainsString('https://cooca.id/pricing', $pagesXml);
        $this->assertStringContainsString('https://cooca.id/solutions/fnb', $pagesXml);
        $this->assertStringContainsString('https://cooca.id/resources/faq', $pagesXml);
    }

    /**
     * Prompt 6: HTML lang="id", single JSON-LD instance, Organization contactPoint, SoftwareApplication offers.
     */
    public function test_prompt_six_seo_onpage_and_schema_markup(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        $content = $response->getContent();

        // 1. Language attribute is id
        $this->assertStringContainsString('<html lang="id"', $content);

        // 2. og:locale is id_ID
        $this->assertStringContainsString('property="og:locale" content="id_ID"', $content);

        // 3. Exactly ONE SoftwareApplication schema (no duplication from double @stack)
        $softwareAppOccurrences = substr_count($content, '"SoftwareApplication"');
        $this->assertSame(1, $softwareAppOccurrences);

        // 4. Organization schema has WhatsApp contactPoint
        $this->assertStringContainsString('+62 852-8786-4176', $content);
        $this->assertStringContainsString('"contactType": "customer support"', $content);

        // 5. SoftwareApplication has 4 pricing tiers
        $this->assertStringContainsString('"name": "Gratis"', $content);
        $this->assertStringContainsString('"name": "Standard"', $content);
        $this->assertStringContainsString('"name": "Premium"', $content);
        $this->assertStringContainsString('"name": "Prestige"', $content);

        // 6. WebSite schema has SearchAction
        $this->assertStringContainsString('"SearchAction"', $content);
    }

    /**
     * Prompt 7 & 8: Pinned asset versions, header CTA button, and chat greeting popup.
     */
    public function test_prompt_seven_and_eight_assets_and_ux_cta(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        $content = $response->getContent();

        // Pinned CDN versions
        $this->assertStringContainsString('lucide@0.469.0', $content);
        $this->assertStringContainsString('alpinejs@3.14.8', $content);

        // Header CTA button whitespace-nowrap and responsive label
        $this->assertStringContainsString('whitespace-nowrap shrink-0', $content);
        $this->assertStringContainsString('Coba COOCA Gratis', $content);
        $this->assertStringContainsString('Coba Gratis', $content);

        // Chat greeting hidden on mobile
        $this->assertStringContainsString('hidden sm:block', $content);
    }

    /**
     * Prompt 9: Clean Indonesian copywriting.
     */
    public function test_prompt_nine_indonesian_copywriting_consistency(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        $content = $response->getContent();

        $this->assertStringContainsString('Semua Kebutuhan Bisnis Anda, Terhubung Jadi Satu', $content);
        $this->assertStringContainsString('Cocok untuk Berbagai Jenis Usaha', $content);
        $this->assertStringContainsString('Mulai Rapikan Bisnis Anda Hari Ini', $content);
        $this->assertStringContainsString('Daftarkan usaha', $content);
        $this->assertStringContainsString('Pilih modul', $content);
        $this->assertStringContainsString('Hubungkan channel', $content);
        $this->assertStringContainsString('Jalankan bisnis', $content);
        $this->assertStringContainsString('Satu Sistem, Berbagai Kemudahan Usaha', $content);

        // Untranslated English headlines should no longer exist
        $this->assertStringNotContainsString('Everything Your Business Needs. Connected.', $content);
        $this->assertStringNotContainsString('Start Building Your Business Operating System.', $content);
    }

    /**
     * Prompt 10: Multi-channel tracking, UU PDP consent banner, and testimonials.
     */
    public function test_prompt_ten_tracking_and_cookie_consent(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        $content = $response->getContent();

        // UU PDP Cookie consent banner present
        $this->assertStringContainsString('Privasi &amp; Cookie (Kepatuhan UU PDP)', $content);
        $this->assertStringContainsString('Setuju Semua', $content);
        $this->assertStringContainsString('Hanya Esensial', $content);

        // Multi-channel tracking engine present
        $this->assertStringContainsString('window.coocaTrack = function', $content);
        $this->assertStringContainsString('window.initMarketingPixels = function', $content);
    }
}
