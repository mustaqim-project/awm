<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class MarketingSocialProofTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_landing_page_renders_factual_trust_badge_and_no_unverified_claims(): void
    {
        Config::set('marketing.social_proof.show', false);

        $response = $this->get('/');
        $response->assertOk();

        // Must see factual trust badge
        $response->assertSee('Gratis untuk mulai');
        $response->assertSee('Tanpa kartu kredit');
        $response->assertSee('Data milik Anda 100%');
        $response->assertSee('Dibuat untuk UMKM Indonesia');

        // Must NOT see unverified social proof claims
        $response->assertDontSee('Dari 10.000+ Pemilik');
        $response->assertDontSee('+10k</span>', false);
        $response->assertDontSee('4.9 / 5.0');
        $response->assertDontSee('aggregateRating');
    }

    public function test_landing_page_renders_social_proof_and_rating_link_when_explicitly_configured(): void
    {
        Config::set('marketing.social_proof.show', true);
        Config::set('marketing.social_proof.user_count', '12.500+');
        Config::set('marketing.social_proof.rating_value', '4.95');
        Config::set('marketing.social_proof.rating_source_url', 'https://play.google.com/store/apps/details?id=id.cooca.app');

        $response = $this->get('/');
        $response->assertOk();

        $response->assertSee('Dari 12.500+ Pemilik Usaha', false);
        $response->assertSee('4.95 / 5.0');
        $response->assertSee('https://play.google.com/store/apps/details?id=id.cooca.app');
        $response->assertSee('AggregateRating');
    }

    public function test_landing_page_does_not_emit_aggregaterating_if_rating_source_url_is_missing(): void
    {
        Config::set('marketing.social_proof.show', true);
        Config::set('marketing.social_proof.rating_source_url', null);

        $response = $this->get('/');
        $response->assertOk();

        // aggregateRating schema should NOT be emitted without verified source
        $response->assertDontSee('aggregateRating');
        $response->assertDontSee('AggregateRating');
    }
}
