<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Ai\Platform\AiPlatformEngineService;
use App\Domain\Ai\Studio\Adapters\OpenAiImageAdapter;
use App\Models\Admin;
use App\Models\AdminAiVisualAsset;
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
}

