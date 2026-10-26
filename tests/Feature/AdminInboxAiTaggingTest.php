<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\SocialMedia\AdminInboxAiService;
use App\Models\Admin;
use App\Models\Business;
use App\Models\SocialMediaComment;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class AdminInboxAiTaggingTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;
    private AdminInboxAiService $aiService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::create([
            'name'     => 'CS Admin',
            'email'    => 'admin-inbox@cooca.id',
            'password' => bcrypt('password123'),
            'role'     => 'super_admin',
        ]);

        SystemSetting::set('admin_inbox_ai_enabled', 'true', AdminInboxAiService::SETTING_GROUP);
        SystemSetting::set('admin_inbox_ai_auto_tagging', 'true', AdminInboxAiService::SETTING_GROUP);

        $this->withoutMiddleware([
            \Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class,
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
        ]);

        $this->aiService = app(AdminInboxAiService::class);
    }

    public function test_ai_tags_lead_as_prospect_when_inquiring_pricing_and_packages(): void
    {
        $comment = SocialMediaComment::create([
            'is_platform'          => true,
            'platform'             => 'live_chat',
            'platform_comment_id'  => (string) Str::uuid(),
            'platform_post_id'     => 'live_chat',
            'from_id'              => 'visitor_prospect_1',
            'from_name'            => 'Calon Pelanggan Budi',
            'from_email'           => 'budi_baru@gmail.com',
            'session_token'        => 'session_prospect_1',
            'message'              => 'Halo kak, mau tanya harga paket Premium untuk 2 outlet cafe berapa ya per bulannya dan dapat fitur apa saja?',
            'is_from_page'         => false,
            'status'               => 'unread',
            'tags'                 => [],
        ]);

        $result = $this->aiService->analyzeAndInferTags($comment, ['persist' => true]);

        $this->assertTrue($result['success']);
        $this->assertContains('prospek', $result['tags']);
        $this->assertFalse($result['is_registered_client']);

        // Check persistence in database
        $fresh = $comment->fresh();
        $this->assertContains('prospek', (array) $fresh->tags);
    }

    public function test_ai_tags_as_client_when_customer_email_matches_registered_user(): void
    {
        $business = Business::create([
            'name'             => 'Kopi Kenangan Senja',
            'slug'             => 'kopi-kenangan-senja',
            'email'            => 'owner@kopisenja.com',
            'phone'            => '081234567890',
            'is_active'        => true,
            'currency'         => 'IDR',
            'industry_category'=> 'fnb_coffee_shop',
        ]);

        $user = User::create([
            'name'               => 'Andi Owner Kopi',
            'email'              => 'andi@kopisenja.com',
            'phone'              => '081234567890',
            'password'           => bcrypt('secret123'),
            'active_business_id' => $business->id,
        ]);

        $comment = SocialMediaComment::create([
            'is_platform'          => true,
            'platform'             => 'whatsapp',
            'platform_comment_id'  => (string) Str::uuid(),
            'platform_post_id'     => 'whatsapp',
            'from_id'              => '6281234567890',
            'from_name'            => 'Andi Owner Kopi',
            'from_email'           => 'andi@kopisenja.com',
            'from_phone'           => '081234567890',
            'message'              => 'Halo min, mau tanya cara tambah meja kasir POS di outlet kedua saya ya.',
            'is_from_page'         => false,
            'status'               => 'unread',
            'tags'                 => [],
        ]);

        $result = $this->aiService->analyzeAndInferTags($comment, ['persist' => true]);

        $this->assertTrue($result['success']);
        $this->assertTrue($result['is_registered_client']);
        $this->assertContains('client', $result['tags']);
        $this->assertEquals('Kopi Kenangan Senja', $result['client_business_name']);

        $fresh = $comment->fresh();
        $this->assertContains('client', (array) $fresh->tags);
    }

    public function test_ai_tags_as_urgent_complaint_when_error_reported(): void
    {
        $comment = SocialMediaComment::create([
            'is_platform'          => true,
            'platform'             => 'live_chat',
            'platform_comment_id'  => (string) Str::uuid(),
            'platform_post_id'     => 'live_chat',
            'from_id'              => 'visitor_complaint_1',
            'from_name'            => 'Rudi Kasir',
            'session_token'        => 'session_complaint_1',
            'message'              => 'Min urgent tolong dibantu, kasir POS kami error tidak bisa cetak struk dan transaksi gagal, antrian panjang!',
            'is_from_page'         => false,
            'status'               => 'unread',
            'tags'                 => [],
        ]);

        $result = $this->aiService->analyzeAndInferTags($comment, ['persist' => true]);

        $this->assertTrue($result['success']);
        $this->assertContains('komplain', $result['tags']);
        $this->assertContains('prioritas', $result['tags']);

        $fresh = $comment->fresh();
        $this->assertContains('komplain', (array) $fresh->tags);
        $this->assertContains('prioritas', (array) $fresh->tags);
        $this->assertEquals('priority', $fresh->status);
    }

    public function test_ai_tag_endpoint_via_http_post(): void
    {
        $comment = SocialMediaComment::create([
            'is_platform'          => true,
            'platform'             => 'messenger',
            'platform_comment_id'  => (string) Str::uuid(),
            'platform_post_id'     => 'messenger',
            'from_id'              => 'messenger_user_1',
            'from_name'            => 'Dewi Sartika',
            'message'              => 'Halo COOCA, bagaimana cara daftar langganan paket Standar?',
            'is_from_page'         => false,
            'status'               => 'unread',
            'tags'                 => [],
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->postJson(route('admin.inbox.ai-tag', $comment->id));

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'tags',
                'primary_category',
                'reason',
                'lead_score',
                'is_registered_client',
            ]);

        $this->assertContains('prospek', $response->json('tags'));

        $fresh = $comment->fresh();
        $this->assertContains('prospek', (array) $fresh->tags);
    }

    public function test_ai_auto_tagging_settings_can_be_saved(): void
    {
        $this->aiService->saveSettings([
            'auto_tagging' => false,
        ]);

        $settings = $this->aiService->getSettings();
        $this->assertFalse($settings['auto_tagging']);

        $this->aiService->saveSettings([
            'auto_tagging' => true,
        ]);

        $settings = $this->aiService->getSettings();
        $this->assertTrue($settings['auto_tagging']);
    }
}
