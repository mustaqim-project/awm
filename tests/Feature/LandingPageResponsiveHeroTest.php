<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

final class LandingPageResponsiveHeroTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_renders_successfully(): void
    {
        $response = $this->get(route('landing'));

        $response->assertStatus(200);
    }

    public function test_hero_section_contains_responsive_viewport_and_safe_padding(): void
    {
        $response = $this->get(route('landing'));

        $response->assertStatus(200);
        // Modern 100svh viewport targeting
        $response->assertSee('min-h-[calc(100svh-4rem)]', false);
        $response->assertSee('lg:min-h-[calc(100svh-84px)]', false);
        // Bottom clearance for mobile fixed bottom navigation dock
        $response->assertSee('pb-[calc(5rem+env(safe-area-inset-bottom,0px))]', false);
    }

    public function test_hero_content_has_responsive_typography_and_clean_trust_reassurance(): void
    {
        // 1. Default state: unverified social proof is hidden, clean factual trust badge is displayed
        Config::set('marketing.social_proof.show', false);

        $response = $this->get(route('landing'));
        $response->assertStatus(200);

        // Eyebrow overline kicker
        $response->assertSee('Aplikasi Kasir, Stok &amp; Pembukuan UMKM', false);
        // Headline
        $response->assertSee('Aplikasi Kasir UMKM: Kasir, Stok &amp; Pembukuan dalam Satu Sistem Cepat', false);
        // Responsive headline scale
        $response->assertSee('text-2xl xs:text-3xl sm:text-5xl', false);
        // Left-aligned layout on mobile & desktop
        $response->assertSee('text-left flex flex-col items-start', false);
        // Compact action button
        $response->assertSee('Mulai Coba Gratis');

        // Verified factual trust badge (Single clean reassurance, zero duplicate)
        $response->assertSee('Gratis untuk mulai');
        $response->assertSee('Tanpa kartu kredit');
        $response->assertSee('Data milik Anda 100%');
        $response->assertSee('Dibuat untuk UMKM Indonesia');
        // Redundant second reassurance row must NOT be duplicated when social proof is off
        $response->assertDontSee('Gratis Selamanya (s.d.');

        // 2. When social proof is explicitly enabled
        Config::set('marketing.social_proof.show', true);
        Config::set('marketing.social_proof.user_count', '10.000+');
        Config::set('marketing.social_proof.rating_value', '4.9');

        $responseWithProof = $this->get(route('landing'));
        $responseWithProof->assertStatus(200);
        $responseWithProof->assertSee('10.000+');
        $responseWithProof->assertSee('4.9 / 5.0');
        $responseWithProof->assertSee('Tanpa Kartu Kredit');
        $responseWithProof->assertSee('Siap 2 Menit');
    }

    public function test_hero_interactive_3d_neon_hub_is_rendered_without_being_hidden_on_mobile(): void
    {
        $response = $this->get(route('landing'));

        $response->assertStatus(200);

        // Central Ecosystem Core
        $response->assertSee('COOCA', false);
        $response->assertSee('hero-holo-beam', false);

        // Ecosystem Hub Nodes
        $response->assertSee('Operasional');
        $response->assertSee('Social Media');
        $response->assertSee('WhatsApp');
        $response->assertSee('POS');
        $response->assertSee('Content Automation');
        $response->assertSee('Customer');
    }
}
