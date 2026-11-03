<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Ai\Platform\AiPlatformEngineService;
use App\Domain\Ai\Studio\Adapters\OpenAiImageAdapter;
use App\Models\Admin;
use App\Models\AdminAiVisualAsset;
use App\Models\AiStudio\InspirationCategory;
use App\Models\AiStudio\InspirationItem;
use App\Models\AiStudio\InspirationSource;
use App\Models\SocialMediaPost;
use App\Models\SocialPostTarget;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminAiStudioVisualTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->admin = Admin::factory()->create([
            'role'      => 'super_admin',
            'is_active' => true,
        ]);
    }

    public function test_guest_is_redirected_to_admin_login(): void
    {
        $response = $this->get(route('admin.ai-studio.visual.index'));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_view_ai_studio_visual_creator_page(): void
    {
        $asset = AdminAiVisualAsset::create([
            'admin_id'        => $this->admin->id,
            'title'           => 'Banner Promo SaaS Q4',
            'prompt'          => '3D futuristic banner for SaaS ERP',
            'model_name'      => 'gpt-image-2.5-flare',
            'aspect_ratio'    => '1:1',
            'quality'         => 'high',
            'file_path'       => 'admin/ai-studio/test-banner.png',
            'image_url'       => 'https://cdn.cooca.id/admin/ai-studio/test-banner.png',
            'width'           => 1024,
            'height'          => 1024,
            'file_size'       => 150000,
            'is_favorite'     => true,
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.ai-studio.visual.index'));

        $response->assertOk();
        $response->assertViewIs('admin.ai-studio.visual');
        $response->assertSee('AI Studio Visual Creator');
        $response->assertSee('Banner Promo SaaS Q4');
        $response->assertSee('Studio Visual &amp; Medsos', false);
    }

    public function test_admin_can_generate_visual_asset_with_ai(): void
    {
        $mockAdapter = $this->createMock(OpenAiImageAdapter::class);
        $mockAdapter->method('generate')->willReturn([
            'status'     => 'completed',
            'provider'   => 'openai',
            'model_name' => 'gpt-image-2.5-flare',
            'images'     => [
                [
                    'url'          => 'data:image/png;base64,' . base64_encode('fake-png-binary-content'),
                    'width'        => 1024,
                    'height'       => 1024,
                    'content_type' => 'image/png',
                ],
            ],
            'usage'      => [
                'prompt_tokens'     => 150,
                'completion_tokens' => 250,
                'total_tokens'      => 400,
            ],
            'latency_ms' => 1250,
            'error_message' => null,
        ]);

        $this->app->instance(OpenAiImageAdapter::class, $mockAdapter);

        $payload = [
            'title'           => 'Desain Peluncuran Fitur Baru',
            'prompt'          => 'Minimalist 3D isometric layout of mobile POS app with soft purple gradient lighting',
            'negative_prompt' => 'watermark, blur, low quality',
            'aspect_ratio'    => '1:1',
            'model_name'      => 'gpt-image-2.5-flare',
            'quality'         => 'high',
        ];

        $response = $this->actingAs($this->admin, 'admin')
            ->postJson(route('admin.ai-studio.visual.generate'), $payload);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('admin_ai_visual_assets', [
            'admin_id'     => $this->admin->id,
            'title'        => 'Desain Peluncuran Fitur Baru',
            'model_name'   => 'gpt-image-2.5-flare',
            'aspect_ratio' => '1:1',
        ]);
    }

    public function test_admin_can_send_visual_asset_to_social_media(): void
    {
        $asset = AdminAiVisualAsset::create([
            'admin_id'        => $this->admin->id,
            'title'           => 'Post Feed Diskon Langganan',
            'prompt'          => 'Modern discount banner',
            'model_name'      => 'gpt-image-2.5-flare',
            'aspect_ratio'    => '1:1',
            'quality'         => 'high',
            'file_path'       => 'admin/ai-studio/sample-social.png',
            'image_url'       => 'https://cdn.cooca.id/admin/ai-studio/sample-social.png',
            'width'           => 1024,
            'height'          => 1024,
            'file_size'       => 120000,
        ]);

        $payload = [
            'platforms'    => ['instagram', 'facebook'],
            'content'      => 'Dapatkan diskon 50% langganan tahunan COOCA Cloud ERP hari ini! #COOCA #BisnisNaikKelas',
            'timing_mode'  => 'now',
            'scheduled_at' => null,
        ];

        $response = $this->actingAs($this->admin, 'admin')
            ->postJson(route('admin.ai-studio.visual.send-to-social-media', $asset->id), $payload);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);

        // Verifikasi SocialMediaPost platform terbuat
        $this->assertDatabaseHas('social_media_posts', [
            'admin_id'    => $this->admin->id,
            'is_platform' => true,
            'content'     => 'Dapatkan diskon 50% langganan tahunan COOCA Cloud ERP hari ini! #COOCA #BisnisNaikKelas',
        ]);

        // Verifikasi target kanal dibuat
        $this->assertDatabaseHas('social_post_targets', [
            'channel' => 'instagram',
        ]);
        $this->assertDatabaseHas('social_post_targets', [
            'channel' => 'facebook',
        ]);

        // Verifikasi riwayat posting di metadata aset
        $asset->refresh();
        $this->assertNotEmpty($asset->metadata['published_posts']);
    }

    public function test_admin_can_toggle_favorite_status(): void
    {
        $asset = AdminAiVisualAsset::create([
            'admin_id'        => $this->admin->id,
            'title'           => 'Desain Feed Favorit',
            'prompt'          => 'Test prompt',
            'model_name'      => 'gpt-image-2.5-flare',
            'aspect_ratio'    => '9:16',
            'quality'         => 'standard',
            'file_path'       => 'admin/ai-studio/fav.png',
            'image_url'       => 'https://cdn.cooca.id/admin/ai-studio/fav.png',
            'is_favorite'     => false,
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->postJson(route('admin.ai-studio.visual.favorite', $asset->id));

        $response->assertOk();
        $response->assertJson([
            'success'     => true,
            'is_favorite' => true,
        ]);

        $this->assertTrue($asset->fresh()->is_favorite);
    }

    public function test_admin_can_delete_visual_asset(): void
    {
        $filePath = 'admin/ai-studio/to-delete.png';
        Storage::disk('public')->put($filePath, 'dummy-content');

        $asset = AdminAiVisualAsset::create([
            'admin_id'        => $this->admin->id,
            'title'           => 'Aset Akan Dihapus',
            'prompt'          => 'Test prompt',
            'model_name'      => 'gpt-image-2.5-flare',
            'aspect_ratio'    => '1:1',
            'quality'         => 'standard',
            'file_path'       => $filePath,
            'image_url'       => 'https://cdn.cooca.id/admin/ai-studio/to-delete.png',
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->deleteJson(route('admin.ai-studio.visual.destroy', $asset->id));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseMissing('admin_ai_visual_assets', [
            'id' => $asset->id,
        ]);
        Storage::disk('public')->assertMissing($filePath);
    }

    public function test_admin_can_fetch_inspirations_for_modal(): void
    {
        $source = InspirationSource::create([
            'code' => 'pinterest',
            'name' => 'Pinterest Commercial Library',
            'driver_class' => 'App\Domain\Ai\Studio\Inspiration\Adapters\PinterestAdapter',
            'is_active' => true,
        ]);

        $category = InspirationCategory::create([
            'name' => 'SaaS Marketing',
            'slug' => 'saas-marketing',
            'sort_order' => 1,
        ]);

        $item = InspirationItem::create([
            'source_id' => $source->id,
            'category_id' => $category->id,
            'external_id' => 'pin-test-999',
            'title' => 'SaaS Promotional Dashboard 3D',
            'image_url' => 'https://cdn.cooca.id/test.png',
            'preview_url' => 'https://cdn.cooca.id/test-thumb.png',
            'status' => 'active',
            'commercial_license' => 'allowed',
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->getJson(route('admin.ai-studio.visual.inspirations', ['q' => 'Dashboard']));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonFragment([
            'title' => 'SaaS Promotional Dashboard 3D',
        ]);
    }

    public function test_admin_can_generate_visual_with_directives_and_reference(): void
    {
        $source = InspirationSource::create([
            'code' => 'cooca_curated',
            'name' => 'Cooca Curated',
            'driver_class' => 'App\Domain\Ai\Studio\Inspiration\Adapters\GrokImageAdapter',
            'is_active' => true,
        ]);

        $category = InspirationCategory::create([
            'name' => 'Branding',
            'slug' => 'branding',
            'sort_order' => 2,
        ]);

        $inspiration = InspirationItem::create([
            'source_id' => $source->id,
            'category_id' => $category->id,
            'external_id' => 'curated-001',
            'title' => 'Isometric ERP Software',
            'image_url' => 'https://cdn.cooca.id/curated-001.png',
            'status' => 'active',
            'commercial_license' => 'allowed',
        ]);

        $mockAdapter = $this->createMock(OpenAiImageAdapter::class);
        $mockAdapter->method('generate')->willReturn([
            'status'     => 'completed',
            'provider'   => 'openai',
            'model_name' => 'gpt-image-2.5-flare',
            'images'     => [
                [
                    'url'          => 'data:image/png;base64,' . base64_encode('fake-generated-image'),
                    'width'        => 1024,
                    'height'       => 1024,
                    'content_type' => 'image/png',
                ],
            ],
            'usage'      => [
                'total_tokens' => 500,
            ],
            'latency_ms' => 1100,
            'error_message' => null,
        ]);

        $this->app->instance(OpenAiImageAdapter::class, $mockAdapter);

        $payload = [
            'title'                     => 'Banner Diskon Q4 Multi-Konten',
            'prompt'                    => 'Promotional banner with glassmorphism',
            'negative_prompt'           => 'watermark, blur',
            'aspect_ratio'              => '1:1',
            'model_name'                => 'gpt-image-2.5-flare',
            'quality'                   => 'high',
            'content_type'              => 'saas_promo',
            'visual_style'              => '3d_apple_glass',
            'lighting_style'            => 'softbox_studio',
            'color_palette'             => 'cooca_signature',
            'leave_space_for_text'      => '1',
            'include_ui_mockup'         => '1',
            'banner_headline'           => 'Diskon 50% Langganan Tahunan',
            'reference_inspiration_id'  => $inspiration->id,
        ];

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.ai-studio.visual.generate'), $payload, [
                'Accept' => 'application/json',
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);

        $asset = AdminAiVisualAsset::where('title', 'Banner Diskon Q4 Multi-Konten')->first();
        $this->assertNotNull($asset);
        $this->assertEquals('saas_promo', $asset->metadata['content_type']);
        $this->assertEquals('3d_apple_glass', $asset->metadata['visual_style']);
        $this->assertEquals($inspiration->id, $asset->metadata['reference_id']);
        $this->assertStringContainsString('negative space', $asset->prompt);
    }
}

