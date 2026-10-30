<?php

declare(strict_types=1);

namespace Tests\Feature\Public;

use App\Domain\Billing\EntitlementService;
use App\Models\BusinessSubscription;
use App\Models\SystemSetting;
use Database\Seeders\DefaultCostCategorySeeder;
use Database\Seeders\DefaultUnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPricingAndEntitlementConsistencyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DefaultUnitSeeder::class);
        $this->seed(DefaultCostCategorySeeder::class);
    }

    /**
     * Test that pricing page dynamically reflects custom prices and limits configured in SystemSetting.
     */
    public function test_pricing_page_renders_dynamically_from_system_settings_and_entitlements(): void
    {
        // 1. Configure custom tier prices in SystemSetting
        SystemSetting::set('subscription_price_standard_monthly', '45000', 'billing');
        SystemSetting::set('subscription_price_standard_annual', '450000', 'billing');
        SystemSetting::set('subscription_price_premium_monthly', '95000', 'billing');
        SystemSetting::set('subscription_price_premium_annual', '950000', 'billing');
        SystemSetting::set('subscription_price_prestige_monthly', '225000', 'billing');
        SystemSetting::set('subscription_price_prestige_annual', '2250000', 'billing');

        // 2. Configure custom free tier POS limit in SystemSetting
        SystemSetting::set('tier_limit_free_pos', '50', 'billing');
        SystemSetting::set('tier_limit_free_product', '15', 'billing');

        $response = $this->get('/pricing');

        $response->assertStatus(200);

        // Verify prices dynamically reflect updated SystemSettings
        $response->assertSee('45.000', false);
        $response->assertSee('95.000', false);
        $response->assertSee('225.000', false);

        // Verify limits dynamically reflect updated SystemSettings
        $response->assertSee('50 Trx/bln', false);
        $response->assertSee('15 produk', false);

        // Verify overclaim is removed
        $response->assertDontSee('Hemat Hingga Rp 2.400.000', false);
        $response->assertSee('dengan Paket Tahunan', false);
    }

    /**
     * Test that support SLA dynamically binds to SystemSetting across public views.
     */
    public function test_support_sla_reads_from_system_settings(): void
    {
        SystemSetting::set('support_working_hours', 'Senin – Jumat 09.00 – 18.00 WIB', 'general');
        SystemSetting::set('support_response_time', '< 10 Menit', 'general');

        $response = $this->get('/support');

        $response->assertStatus(200);
        $response->assertSee('Senin – Jumat 09.00 – 18.00 WIB', false);
        $response->assertSee('10 Menit di Jam Kerja', false);
        $response->assertDontSee('08:00 - 22:00 WIB', false);
    }

    /**
     * Smoke test: Key public marketing, ecosystem, and solution pages must return 200 OK.
     */
    public function test_key_public_pages_return_200_ok(): void
    {
        $publicRoutes = [
            '/',
            '/pricing',
            '/kontak',
            '/support',
            '/security',
            '/privacy',
            '/terms',
            '/omnichannel-erp/pos',
            '/omnichannel-erp/inventory',
            '/omnichannel-erp/accounting',
            '/omnichannel/whatsapp',
            '/solutions/fnb',
            '/solutions/retail',
            '/solutions/workshop',
            '/solutions/laundry',
            '/solutions/manufacturing',
            '/solutions/services',
            '/solusi/kasir-warung',
            '/solusi/kasir-cafe-kecil',
            '/solusi/kasir-kios',
            '/solusi/kasir-salon',
            '/solusi/kasir-barbershop',
            '/solusi/kasir-irt',
            '/solusi/kasir-apotek',
            '/solusi/aplikasi-catering',
            '/aplikasi-kasir-gratis',
        ];

        foreach ($publicRoutes as $route) {
            $response = $this->get($route);
            $response->assertStatus(200);
        }
    }

    /**
     * Test that mega menu canonical routes exist and return successful HTTP responses.
     */
    public function test_mega_menu_links_exist_and_do_not_produce_404(): void
    {
        $canonicalRoutes = [
            route('landing'),
            route('public.pricing'),
            route('public.erp.pos'),
            route('public.erp.accounting'),
            route('public.erp.inventory'),
            route('public.omnichannel.whatsapp'),
            route('public.solutions.fnb'),
            route('public.solutions.retail'),
            route('public.solutions.workshop'),
            route('public.solutions.laundry'),
            route('public.solutions.manufacturing'),
            route('public.solutions.services'),
            route('public.security'),
            route('public.privacy'),
            route('public.terms'),
        ];

        foreach ($canonicalRoutes as $url) {
            $response = $this->get($url);
            $this->assertNotEquals(404, $response->getStatusCode(), "URL [{$url}] returned 404 Not Found");
            $this->assertTrue(in_array($response->getStatusCode(), [200, 301, 302], true), "URL [{$url}] returned unexpected status: " . $response->getStatusCode());
        }
    }
}
