<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Storefront\StorefrontThemeService;
use App\Models\Business;
use App\Models\BusinessLandingPage;
use App\Models\CommerceStoreSetting;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\DefaultUnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class StorefrontThemeCustomizationTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Business $business;
    private BusinessLandingPage $landingPage;
    private StorefrontThemeService $themeService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DefaultUnitSeeder::class);

        $this->themeService = app(StorefrontThemeService::class);

        $this->owner = User::create([
            'name' => 'Owner Bengkel & Toko',
            'email' => 'owner.theme@cooca.id',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);

        $this->business = Business::create([
            'name' => 'Bengkel Apex Motor',
            'slug' => 'bengkel-apex-motor',
            'currency' => 'IDR',
            'is_active' => true,
        ]);

        $ownerRole = Role::firstOrCreate(
            ['business_id' => $this->business->id, 'slug' => 'owner'],
            ['name' => 'Owner', 'display_name' => 'Pemilik Bisnis']
        );

        $this->owner->businesses()->attach($this->business->id, ['role' => 'owner', 'role_id' => $ownerRole->id]);
        $this->owner->forceFill(['active_business_id' => $this->business->id])->save();

        CommerceStoreSetting::create([
            'business_id' => $this->business->id,
            'is_storefront_enabled' => true,
            'is_discoverable' => true,
            'allow_pickup' => true,
            'allow_delivery' => true,
        ]);

        $this->landingPage = BusinessLandingPage::create([
            'business_id' => $this->business->id,
            'theme_preset' => 'modern_umkm',
            'theme_color' => '#059669',
            'font_family' => 'Plus Jakarta Sans',
            'headline' => 'Servis Motor Handal',
            'subheadline' => 'Bengkel terpercaya dengan mekanik berpengalaman',
            'is_published' => true,
            'active_pages' => [
                'home' => true,
                'catalog' => true,
                'about' => true,
                'contact' => true,
            ],
        ]);
    }

    public function test_landing_page_cms_editor_renders_with_42_themes_and_fonts(): void
    {
        $response = $this->actingAs($this->owner, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('landing-page.edit'));

        $response->assertOk();
        $response->assertSee('Koleksi 42 Tema Industri Otentik &amp; Storefront', false);
        $response->assertSee('Tipografi &amp; Google Fonts Storefront', false);
        $response->assertSee('Active Theme Cockpit Card', false);
        $response->assertSee('MODAL: THEME INSPECTOR', false);
        $response->assertSee('Bengkel Apex Motor');
    }

    public function test_owner_can_update_theme_preset_color_and_font(): void
    {
        $payload = [
            'theme_preset' => 'service_workshop',
            'theme_color' => '#EA580C',
            'font_family' => 'Chakra Petch',
            'headline' => 'Performa Mesin Prima, Servis Ahli & Sparepart Bergaransi',
            'subheadline' => 'Bengkel perawatan berkala, tune-up komputer, dan servis rem.',
            'announcement_badge' => 'Bengkel Terverifikasi & Mekanik Handal',
            'cta_primary_text' => 'Booking Servis Sekarang',
        ];

        $response = $this->actingAs($this->owner, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->put(route('landing-page.update'), $payload);

        $response->assertRedirect();

        $this->assertDatabaseHas('business_landing_pages', [
            'business_id' => $this->business->id,
            'theme_preset' => 'service_workshop',
            'theme_color' => '#EA580C',
            'font_family' => 'Chakra Petch',
            'headline' => 'Performa Mesin Prima, Servis Ahli & Sparepart Bergaransi',
        ]);

        $this->landingPage->refresh();
        $this->assertSame('service_workshop', $this->landingPage->theme_preset);
        $this->assertSame('#EA580C', $this->landingPage->theme_color);
        $this->assertSame('Chakra Petch', $this->landingPage->font_family);
    }

    public function test_storefront_renders_selected_theme_identity(): void
    {
        $this->landingPage->update([
            'theme_preset' => 'service_workshop',
            'theme_color' => '#EA580C',
            'font_family' => 'Chakra Petch',
            'headline' => 'Performa Mesin Prima & Servis Terpercaya',
        ]);

        $response = $this->get('/' . $this->business->slug);

        $response->assertOk();
        $response->assertSee('Bengkel Apex Motor');
        $response->assertSee('Performa Mesin Prima &amp; Servis Terpercaya', false);
        // Verify font Chakra Petch is rendered in head style or font config
        $response->assertSee('Chakra Petch');
        // Verify theme color #EA580C is rendered
        $response->assertSee('#EA580C');
    }

    public function test_authorized_owner_can_live_preview_different_theme_without_saving(): void
    {
        // Database has service_workshop
        $this->landingPage->update([
            'theme_preset' => 'service_workshop',
            'theme_color' => '#EA580C',
        ]);

        // Owner previews fnb_fastfood via query string
        $response = $this->actingAs($this->owner, 'web')
            ->withSession(['active_business_id' => $this->business->id])
            ->get('/' . $this->business->slug . '?preview=1&preview_theme=fnb_fastfood');

        $response->assertOk();
        // Should resolve fnb_fastfood styling (Outfit font, neon red styling)
        $response->assertSee('Outfit');
        // Database should still be service_workshop (zero side-effect test drive)
        $this->landingPage->refresh();
        $this->assertSame('service_workshop', $this->landingPage->theme_preset);
    }

    public function test_all_42_themes_resolve_and_render_without_errors(): void
    {
        $themes = $this->themeService->getAllThemes();
        $this->assertCount(42, $themes, 'Storefront must have exactly 42 registered themes');

        foreach ($themes as $key => $theme) {
            $this->assertArrayHasKey('name', $theme, "Theme $key must have name");
            $this->assertArrayHasKey('primary_color', $theme, "Theme $key must have primary_color");
            $this->assertArrayHasKey('font_heading', $theme, "Theme $key must have font_heading");
            $this->assertArrayHasKey('bundle_dir', $theme, "Theme $key must have bundle_dir");
            $this->assertArrayHasKey('recommendation', $theme, "Theme $key must have recommendation");

            // Verify recommendation has copy
            $rec = $theme['recommendation'];
            $this->assertNotEmpty($rec['headline'], "Theme $key must have recommendation headline");
            $this->assertNotEmpty($rec['subheadline'], "Theme $key must have recommendation subheadline");
            $this->assertNotEmpty($rec['cta_primary_text'], "Theme $key must have recommendation cta");
            $this->assertCount(4, $rec['trust_pillars'], "Theme $key must have 4 trust pillars");

            // Verify live rendering via preview URL
            $response = $this->actingAs($this->owner, 'web')
                ->withSession(['active_business_id' => $this->business->id])
                ->get('/' . $this->business->slug . '?preview=1&preview_theme=' . $key);

            $this->assertTrue(
                $response->isOk(),
                "Theme '$key' failed to render on storefront! Status: " . $response->status()
            );
        }
    }
}
