<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Billing\EntitlementService;
use App\Models\Post;
use App\Models\SystemSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PublicMarketingConsistencyAndRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_pricing_page_renders_source_of_truth_pricing_and_tier_limits(): void
    {
        $allLimits = app(EntitlementService::class)->getAllTierLimits();

        $response = $this->get('/pricing');
        $response->assertOk();

        // 1. Verify pricing amounts rendered from single source of truth
        $response->assertSee('Rp 0');
        $response->assertSee('Rp 39.000');
        $response->assertSee('Rp 89.000');
        $response->assertSee('Rp 199.000');

        // 2. Verify limits rendered matching EntitlementService
        $freeProductLimit = (int) ($allLimits['free']['product'] ?? 10);
        $freeMaterialLimit = (int) ($allLimits['free']['material'] ?? 20);
        $freePosLimit = (int) ($allLimits['free']['pos'] ?? 30);

        $response->assertSee("Maksimal {$freeProductLimit} Produk &amp; {$freeMaterialLimit} Bahan Baku", false);
        $response->assertSee("{$freePosLimit} Transaksi Kasir POS per bulan", false);

        // 3. Verify that changing SystemSetting changes the rendered view (anti-hardcoding test)
        SystemSetting::set('tier_limit_free_product', '45');
        $updatedResponse = $this->get('/pricing');
        $updatedResponse->assertOk();
        $updatedResponse->assertSee("Maksimal 45 Produk &amp; {$freeMaterialLimit} Bahan Baku", false);
    }

    public function test_kasir_gratis_page_renders_dynamic_free_tier_limits(): void
    {
        $allLimits = app(EntitlementService::class)->getAllTierLimits();
        $freeProduct = (int) ($allLimits['free']['product'] ?? 10);
        $freePos = (int) ($allLimits['free']['pos'] ?? 30);

        $response = $this->get('/aplikasi-kasir-gratis');
        $response->assertOk();

        $response->assertSee("{$freeProduct} Produk Gratis Selamanya");
        $response->assertSee("{$freePos} Transaksi Kasir");
    }

    public function test_security_compliance_page_renders_verified_pillars(): void
    {
        $response = $this->get('/security');
        $response->assertOk();

        // Verify the 5 verified security pillars
        $response->assertSee('Isolasi Data Multi-Tenant');
        $response->assertSee('Enkripsi Standar Perbankan (AES-256 &amp; Bcrypt)', false);
        $response->assertSee('Rekam Jejak Audit Log Lengkap');
        $response->assertSee('Proteksi Web Terhadap Serangan Siber (OWASP)');
        $response->assertSee('Kepatuhan UU Perlindungan Data Pribadi (UU PDP)');

        // Verify cloud snapshot transparency
        $response->assertSee('Pencadangan Data Cloud (Infrastructure Snapshot)');

        // Verify /keamanan redirect 301
        $redirectResponse = $this->get('/keamanan');
        $redirectResponse->assertStatus(301);
        $redirectResponse->assertRedirect('/security');
    }

    public function test_blog_resources_redirects_301_to_canonical_blog(): void
    {
        $response = $this->get('/resources/blog');
        $response->assertStatus(301);
        $response->assertRedirect('/blog');

        $canonicalResponse = $this->get('/blog');
        $canonicalResponse->assertOk();
    }

    public function test_all_public_mega_menu_canonical_routes_return_ok(): void
    {
        $routes = [
            route('landing'),
            route('public.pricing'),
            route('public.security'),
            route('public.privacy'),
            route('public.terms'),
            route('public.about'),
            route('contact'),
            route('public.bos.overview'),
            route('public.erp.erp'),
            route('public.erp.pos'),
            route('public.erp.accounting'),
            route('public.erp.inventory'),
            route('public.erp.finance'),
            route('public.erp.crm'),
            route('public.erp.hrm'),
            route('public.content.creation'),
        ];

        foreach ($routes as $route) {
            $response = $this->get($route);
            $this->assertNotEquals(404, $response->getStatusCode(), "Route {$route} returned 404");
            $this->assertTrue(
                in_array($response->getStatusCode(), [200, 301, 302], true),
                "Route {$route} returned unexpected status {$response->getStatusCode()}"
            );
        }
    }

    public function test_blog_post_renders_author_and_expert_reviewer(): void
    {
        $post = Post::create([
            'title' => 'Strategi Manajemen Arus Kas Warung Makan',
            'slug' => 'strategi-manajemen-arus-kas-warung-makan',
            'cluster' => 'tutorial',
            'category' => 'Finansial UMKM',
            'excerpt' => 'Panduan mengelola arus kas agar operasional warung tetap aman.',
            'content' => '<h2>Arus Kas Masuk</h2><p>Mencatat penjualan tunai dan non-tunai.</p>',
            'author_name' => 'Tim Finansial Cooca',
            'read_time' => 3,
            'is_published' => true,
            'published_at' => now(),
        ]);

        $response = $this->get('/blog/' . $post->slug);
        $response->assertOk();

        // Verify author and reviewer metadata in schema & UI
        $response->assertSee('Tim Finansial Cooca');
        $response->assertSee('Ditinjau:');
        $response->assertSee('Praktisi Akuntansi &amp; Keuangan UMKM', false);
        $response->assertSee('"reviewedBy"', false);
    }

    public function test_support_sla_rendered_dynamically_on_contact(): void
    {
        SystemSetting::set('support_working_hours', 'Senin - Jumat, 08:30 - 17:30 WIB');
        SystemSetting::set('support_response_time', '< 15 Menit pada Jam Kerja');

        $response = $this->get('/kontak');
        $response->assertOk();

        $response->assertSee('Senin - Jumat, 08:30 - 17:30 WIB');
        $response->assertSee('&lt; 15 Menit pada Jam Kerja', false);
    }
}
