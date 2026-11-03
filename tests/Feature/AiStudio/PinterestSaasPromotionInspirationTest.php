<?php

declare(strict_types=1);

namespace Tests\Feature\AiStudio;

use App\Domain\Ai\Inspiration\Adapters\PinterestAdapter;
use App\Models\AiStudio\InspirationCategory;
use App\Models\AiStudio\InspirationItem;
use App\Models\AiStudio\InspirationSource;
use App\Models\AiStudio\InspirationTrendScore;
use App\Models\AiStudio\InspirationVisualAnalysis;
use App\Models\Business;
use App\Models\User;
use Database\Seeders\PinterestSaasPromotionInspirationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PinterestSaasPromotionInspirationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_pinterest_saas_promotion_inspirations_into_database(): void
    {
        $seeder = new PinterestSaasPromotionInspirationSeeder();
        $seeder->run();

        // 1. Verifikasi Sumber Terdaftar
        $this->assertDatabaseHas('inspiration_sources', [
            'code' => 'pinterest',
            'is_active' => true,
        ]);

        // 2. Verifikasi Kategori SaaS Terdaftar
        $this->assertDatabaseHas('inspiration_categories', [
            'slug' => 'saas-software-promotion',
        ]);

        // 3. Verifikasi Item Inspirasi SaaS Terdaftar (Minimal 12 item)
        $saasCount = InspirationItem::where('external_id', 'like', 'pin-saas-%')->count();
        $this->assertEquals(12, $saasCount);

        // Verifikasi salah satu item kunci
        $item = InspirationItem::where('external_id', 'pin-saas-001')->first();
        $this->assertNotNull($item);
        $this->assertEquals('B2B SaaS Analytics & Revenue Growth Dashboard Banner', $item->title);
        $this->assertEquals('16:9', $item->visualAnalysis?->suggested_aspect_ratios[0] ?? null);
        $this->assertStringContainsString('B2B SaaS', $item->visualAnalysis?->extracted_style_prompt ?? '');

        // Verifikasi Analisis Visual
        $this->assertDatabaseHas('inspiration_visual_analyses', [
            'inspiration_item_id' => $item->id,
            'composition' => 'isometric_glassmorphic_ui',
        ]);

        // Verifikasi Trend Score
        $this->assertDatabaseHas('inspiration_trend_scores', [
            'inspiration_item_id' => $item->id,
            'trend_score' => 98.50,
        ]);
    }

    public function test_it_searches_saas_promotion_pins_via_pinterest_adapter(): void
    {
        $adapter = new PinterestAdapter();

        // Cari dengan query "saas"
        $results = $adapter->search('saas');
        $this->assertNotEmpty($results);
        $this->assertGreaterThanOrEqual(12, count($results));

        // Verifikasi detail pin pertama
        $first = $results[0];
        $this->assertStringStartsWith('pin-saas-', $first['external_id']);
        $this->assertNotEmpty($first['thumbnail_url']);
        $this->assertNotEmpty($first['visual_analysis']['extracted_style_prompt']);
        $this->assertContains('saas', $first['tags']);

        // Cari dengan query "pos"
        $posResults = $adapter->search('pos');
        $this->assertNotEmpty($posResults);
        $hasSaasPos = collect($posResults)->contains(fn ($p) => $p['external_id'] === 'pin-saas-003');
        $this->assertTrue($hasSaasPos);
    }

    public function test_it_is_accessible_in_owner_ai_studio_inspiration_catalog(): void
    {
        // Jalankan seeder
        (new PinterestSaasPromotionInspirationSeeder())->run();

        // Siapkan Tenant User & Business
        $user = User::create([
            'name'     => 'Owner Merchant',
            'email'    => 'merchant-saas@cooca.id',
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
        ]);

        $business = Business::create([
            'name' => 'SaaS Company Demo',
        ]);

        $business->users()->attach($user->id, [
            'id'   => (string) \Illuminate\Support\Str::uuid(),
            'role' => 'owner',
        ]);

        $user->update(['active_business_id' => $business->id]);
        \App\Support\Context::setBusiness($business);

        $response = $this->actingAs($user)
            ->withSession(['current_business_id' => $business->id])
            ->get(route('ai-studio.inspiration.index', ['q' => 'saas']));

        $response->assertOk();
        $response->assertSee('B2B SaaS Analytics');
    }
}
