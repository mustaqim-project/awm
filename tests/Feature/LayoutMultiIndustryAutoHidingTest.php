<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Template\ModuleRegistry;
use App\Models\Business;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Tests\TestCase;

class LayoutMultiIndustryAutoHidingTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();
        Cache::flush();

        $this->seed(\Database\Seeders\RbacSeeder::class);

        $this->owner = User::create([
            'name' => 'Industry Multi Owner',
            'email' => 'multi_industry@cooca.id',
            'password' => 'password123',
            'email_verified_at' => now(),
        ]);
    }

    protected function tearDown(): void
    {
        Context::flush();
        Cache::flush();
        parent::tearDown();
    }

    public function test_pos_is_hidden_when_pos_modules_are_disabled_for_b2b_or_service(): void
    {
        $biz = Business::create([
            'name' => 'Agency Kreatif Cooca',
            'business_type' => 'services',
            'status' => 'active',
            'is_active' => true,
            'disabled_modules' => [
                ModuleRegistry::MODULE_POS_RETAIL,
                ModuleRegistry::MODULE_POS_DINEIN,
                ModuleRegistry::MODULE_INVENTORY_WAREHOUSE,
            ],
        ]);

        $biz->users()->attach($this->owner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->owner->update(['active_business_id' => $biz->id]);
        Context::setBusiness($biz);

        $response = $this->actingAs($this->owner)
            ->withSession(['locale' => 'id'])
            ->get(route('dashboard'));

        $response->assertOk();
        // POS terminal button should NOT be in the sidebar navigation
        $response->assertDontSee('id="tour-group-pos"', false);
        $response->assertDontSee('id="tour-nav-pos-terminal"', false);
        // Warehouse stocks should NOT be visible
        $response->assertDontSee('id="tour-nav-inventory-stocks"', false);
    }

    public function test_workshop_industry_renders_work_order_as_topbar_primary_cta_when_pos_disabled(): void
    {
        $biz = Business::create([
            'name' => 'Bengkel Motor Makmur',
            'business_type' => 'workshop',
            'template_code' => 'service_workshop',
            'status' => 'active',
            'is_active' => true,
            'disabled_modules' => [
                ModuleRegistry::MODULE_POS_RETAIL,
                ModuleRegistry::MODULE_POS_DINEIN,
            ],
        ]);

        $biz->users()->attach($this->owner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->owner->update(['active_business_id' => $biz->id]);
        Context::setBusiness($biz);

        $response = $this->actingAs($this->owner)
            ->withSession(['locale' => 'id'])
            ->get(route('dashboard'));

        $response->assertOk();
        // Topbar Primary CTA should point to services.index with Order Kerja & SPK text
        $response->assertSee(route('services.index'), false);
        $response->assertSee(__('navigation.services_spk'));
    }

    public function test_b2b_distribution_industry_renders_sales_orders_as_topbar_primary_cta(): void
    {
        $biz = Business::create([
            'name' => 'Distributor Bahan Kimia',
            'business_type' => 'b2b',
            'template_code' => 'distribution_fmcg',
            'status' => 'active',
            'is_active' => true,
            'disabled_modules' => [
                ModuleRegistry::MODULE_POS_RETAIL,
                ModuleRegistry::MODULE_POS_DINEIN,
                ModuleRegistry::MODULE_SERVICE_WORKSHOP,
            ],
        ]);

        $biz->users()->attach($this->owner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->owner->update(['active_business_id' => $biz->id]);
        Context::setBusiness($biz);

        $response = $this->actingAs($this->owner)
            ->withSession(['locale' => 'id'])
            ->get(route('dashboard'));

        $response->assertOk();
        // Topbar Primary CTA should point to sales.orders.index
        $response->assertSee(route('sales.orders.index'), false);
        $response->assertSee(__('navigation.sales_orders'));
    }

    public function test_currency_formatter_and_window_i18n_adaptation_in_english(): void
    {
        $biz = Business::create([
            'name' => 'Global Retail Cafe',
            'business_type' => 'fnb',
            'template_code' => 'fnb_cafe',
            'status' => 'active',
            'is_active' => true,
            'disabled_modules' => [],
        ]);

        $biz->users()->attach($this->owner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->owner->update(['active_business_id' => $biz->id]);
        Context::setBusiness($biz);

        $response = $this->actingAs($this->owner)
            ->withSession(['locale' => 'en'])
            ->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee("window.COOCA_LOCALE = 'en'", false);
        $response->assertSee('window.COOCA_I18N =', false);
        $response->assertSee('inventory:', false);
        $response->assertSee('purchasing:', false);
        $response->assertSee('customers:', false);
        $response->assertSee('services:', false);
    }

    public function test_fnb_industry_shows_universal_services_and_never_shows_workshop_or_laundry_attributes(): void
    {
        $biz = Business::create([
            'name' => 'Kopi Senja Utama',
            'business_type' => 'fnb',
            'template_code' => 'fnb_cafe',
            'status' => 'active',
            'is_active' => true,
            'disabled_modules' => [],
        ]);

        $biz->users()->attach($this->owner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->owner->update(['active_business_id' => $biz->id]);
        Context::setBusiness($biz);

        $response = $this->actingAs($this->owner)
            ->withSession(['locale' => 'id'])
            ->get(route('dashboard'));

        $response->assertOk();
        // Services catalog should remain universally accessible
        $response->assertSee(route('services.index'), false);
        $response->assertSee(__('navigation.services'));

        $content = $response->getContent();
        // Strip out script tags to assert purely on visual UI DOM
        $htmlWithoutScripts = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $content);
        $this->assertStringNotContainsString('SPK Servis', $htmlWithoutScripts);
        $this->assertStringNotContainsString('Layanan & Servis Bengkel', $htmlWithoutScripts);
        $this->assertStringNotContainsString('SPK Bengkel', $htmlWithoutScripts);

        // POS terminal in F&B must NOT show license plate or laundry rack inputs
        $posResponse = $this->actingAs($this->owner)
            ->withHeader('X-Business-Id', $biz->id)
            ->withSession(['locale' => 'id', 'active_business_id' => $biz->id])
            ->get(route('pos.terminal'));

        $posResponse->assertOk();
        $posHtml = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $posResponse->getContent());
        $this->assertStringNotContainsString('Nomor Polisi (Plat)', $posHtml);
        $this->assertStringNotContainsString('Nomor Rak / Loker Cucian', $posHtml);
    }

    public function test_workshop_industry_shows_booking_servis_and_universal_services(): void
    {
        $biz = Business::create([
            'name' => 'Bengkel Mobil Sentosa',
            'business_type' => 'workshop',
            'template_code' => 'service_workshop',
            'status' => 'active',
            'is_active' => true,
            'disabled_modules' => [],
        ]);

        $biz->users()->attach($this->owner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->owner->update(['active_business_id' => $biz->id]);
        Context::setBusiness($biz);

        $response = $this->actingAs($this->owner)
            ->withSession(['locale' => 'id'])
            ->get(route('dashboard'));

        $response->assertOk();
        $content = $response->getContent();

        // Workshop reservations nav element must show 'Booking Servis'
        $this->assertStringContainsString('id="tour-nav-storefront-reservations"', $content);
        if (preg_match('/id="tour-nav-storefront-reservations"[^>]*>.*?<\/a>/s', $content, $matches)) {
            $this->assertStringNotContainsString('Reservasi Meja', $matches[0]);
            $this->assertStringContainsString('Booking Servis', $matches[0]);
        }

        // Services catalog should point to services.index
        $response->assertSee(route('services.index'), false);
    }

    public function test_workshop_industry_shows_vehicle_attributes_in_pos_terminal(): void
    {
        $biz = Business::create([
            'name' => 'Bengkel Mobil Sentosa',
            'business_type' => 'workshop',
            'template_code' => 'service_workshop',
            'status' => 'active',
            'is_active' => true,
            'disabled_modules' => [],
        ]);

        $biz->users()->attach($this->owner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->owner->update(['active_business_id' => $biz->id]);
        $this->owner->refresh();
        Context::setBusiness($biz);

        // POS terminal in Workshop MUST show vehicle license plate and NOT laundry rack
        $posResponse = $this->actingAs($this->owner)
            ->withHeader('X-Business-Id', $biz->id)
            ->withSession(['locale' => 'id', 'active_business_id' => $biz->id])
            ->get(route('pos.terminal'));

        $posResponse->assertOk();
        $posHtml = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $posResponse->getContent());
        $this->assertStringContainsString('Nomor Polisi (Plat)', $posHtml);
        $this->assertStringNotContainsString('Nomor Rak / Loker Cucian', $posHtml);
    }

    public function test_laundry_industry_shows_rack_and_laundry_attributes_in_pos_terminal(): void
    {
        $biz = Business::create([
            'name' => 'Laundry Bersih Kilat',
            'business_type' => 'laundry',
            'template_code' => 'service_laundry',
            'status' => 'active',
            'is_active' => true,
            'disabled_modules' => [],
        ]);

        $biz->users()->attach($this->owner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->owner->update(['active_business_id' => $biz->id]);
        Context::setBusiness($biz);

        // POS terminal in Laundry MUST show rack location and NOT vehicle license plate
        $posResponse = $this->actingAs($this->owner)
            ->withHeader('X-Business-Id', $biz->id)
            ->withSession(['locale' => 'id', 'active_business_id' => $biz->id])
            ->get(route('pos.terminal'));

        $posResponse->assertOk();
        $posHtml = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $posResponse->getContent());
        $this->assertStringContainsString('Nomor Rak / Loker Cucian', $posHtml);
        $this->assertStringNotContainsString('Nomor Polisi (Plat)', $posHtml);
    }
}
