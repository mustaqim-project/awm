<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Ai\Platform\AiPlatformEngineService;
use App\Domain\SocialMedia\AdminInboxAiService;
use App\Models\Admin;
use App\Models\SocialMediaAccount;
use App\Models\SocialMediaComment;
use App\Models\SystemSetting;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class AdminInboxAiChatbotTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->admin = Admin::create([
            'name' => 'Super Administrator',
            'email' => 'superadmin@cooca.id',
            'password' => 'supersecret123',
            'role' => 'superadmin',
            'is_active' => true,
        ]);
    }

    public function test_inbox_page_renders_with_ai_chatbot_manager_and_guidelines(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.inbox.index'));

        $response->assertOk();
        $response->assertSee('Kelola AI Chatbot & Pedoman', false);
        $response->assertSee('Kelola AI Chatbot & Pedoman Kerja', false);
        $response->assertSee('Informasi Sistem (Knowledge Base)', false);
        $response->assertSee('Pedoman Kerja (Do\'s & Don\'ts)', false);
        $response->assertSee('Uji Coba Chatbot (AI Sandbox)', false);
        $response->assertSee('Mode Asisten (Human-in-the-Loop)', false);
        $response->assertSee('Mode Auto-Pilot (Balasan Otomatis)', false);
    }

    public function test_get_ai_settings_endpoint_returns_json_configuration(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->getJson(route('admin.inbox.ai-settings'));

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'settings' => [
                'enabled',
                'mode',
                'channels',
                'model',
                'temperature',
                'system_info',
                'dos',
                'donts',
                'greeting_template',
                'platform_engine',
            ],
        ]);

        $this->assertTrue($response->json('settings.enabled'));
        $this->assertSame('assistant', $response->json('settings.mode'));
        $this->assertNotEmpty($response->json('settings.dos'));
        $this->assertNotEmpty($response->json('settings.donts'));
        $this->assertStringContainsString('PROFIL COOCA INDONESIA', $response->json('settings.system_info'));
    }

    public function test_save_ai_settings_endpoint_persists_guidelines_and_rules(): void
    {
        $payload = [
            'enabled' => true,
            'mode' => 'auto_pilot',
            'channels' => ['whatsapp', 'live_chat'],
            'model' => 'ag/gemini-3.8-flash-high',
            'temperature' => 0.2,
            'system_info' => 'COOCA adalah platform SaaS ERP UMKM Indonesia dengan POS Kasir dan Inventori.',
            'dos' => [
                'Sapa pelanggan dengan ramah',
                'Gunakan bahasa Indonesia santun',
            ],
            'donts' => [
                'Dilarang janji diskon sepihak',
                'Dilarang sebut kompetitor',
            ],
            'greeting_template' => 'Halo Kak {customer_name}! Ada yang bisa kami bantu?',
        ];

        $response = $this->actingAs($this->admin, 'admin')
            ->postJson(route('admin.inbox.ai-settings.save'), $payload);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'Pengaturan AI Chatbot & Pedoman Kerja berhasil disimpan.',
        ]);

        // Verify database persistence via SystemSetting
        $this->assertSame('auto_pilot', SystemSetting::get('admin_inbox_ai_mode'));
        $this->assertSame('true', SystemSetting::get('admin_inbox_ai_enabled'));
        $this->assertSame('0.2', SystemSetting::get('admin_inbox_ai_temperature'));

        $savedDos = json_decode((string) SystemSetting::get('admin_inbox_ai_dos'), true);
        $this->assertContains('Sapa pelanggan dengan ramah', $savedDos);

        $savedDonts = json_decode((string) SystemSetting::get('admin_inbox_ai_donts'), true);
        $this->assertContains('Dilarang janji diskon sepihak', $savedDonts);
    }

    public function test_test_ai_prompt_simulation_endpoint(): void
    {
        // Mock tunnel endpoint response
        Http::fake([
            '*/chat/completions' => Http::response([
                'id' => 'chatcmpl-test-simulation',
                'choices' => [
                    [
                        'message' => [
                            'role' => 'assistant',
                            'content' => 'Halo Kak! Paket Pro COOCA hadir dengan harga Rp 249.000 per bulan yang mencakup POS Kasir, sinkronisasi marketplace, dan WhatsApp terintegrasi. Anda juga dapat mencoba Free Trial 14 hari di https://cooca.id. Ada yang ingin ditanyakan lagi? 😊',
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->postJson(route('admin.inbox.ai-test'), [
                'message' => 'Halo Kak, mau tanya harga paket Pro COOCA berapa ya per bulan?',
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);
        $this->assertStringContainsString('Paket Pro COOCA', $response->json('reply'));
        $this->assertGreaterThanOrEqual(0, $response->json('latency_ms'));
    }

    public function test_generate_ai_reply_for_comment_thread(): void
    {
        // Setup initial demo comment
        $comment = SocialMediaComment::create([
            'is_platform' => true,
            'platform' => 'whatsapp',
            'platform_post_id' => 'wa_thread_001',
            'platform_comment_id' => 'wa_msg_test_001',
            'from_id' => '6281234567890',
            'from_name' => 'Budi Santoso',
            'message' => 'Halo kak, apakah COOCA bisa dipakai untuk toko kelontong 2 cabang?',
            'created_time' => now(),
            'status' => 'unread',
        ]);

        // Mock tunnel endpoint response
        Http::fake([
            '*/chat/completions' => Http::response([
                'id' => 'chatcmpl-inbox-reply',
                'choices' => [
                    [
                        'message' => [
                            'role' => 'assistant',
                            'content' => 'Halo Kak Budi Santoso! Tentu saja bisa, COOCA sangat cocok untuk toko kelontong dengan multi-cabang melalui fitur Multi-Gudang dan transfer stok antar outlet kami. Kakak bisa mencoba uji coba gratis 14 hari di https://cooca.id untuk melihat langsung kemudahannya. 😊',
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->postJson(route('admin.inbox.generate-ai-reply', ['id' => $comment->id]));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);

        $reply = $response->json('reply');
        $this->assertStringContainsString('Budi Santoso', $reply);
        $this->assertStringContainsString('COOCA', $reply);
    }

    public function test_generate_reply_suggestion_directly_without_channel_option(): void
    {
        $comment = SocialMediaComment::create([
            'is_platform' => true,
            'platform' => 'live_chat',
            'platform_post_id' => 'lc_thread_001',
            'platform_comment_id' => 'lc_msg_001',
            'from_id' => 'visitor_99',
            'from_name' => null,
            'message' => 'Halo min, COOCA bisa multi outlet?',
            'created_time' => now(),
            'status' => 'unread',
        ]);

        Http::fake([
            '*/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'role' => 'assistant',
                            'content' => 'Halo Kak! Tentu bisa, COOCA mendukung multi-outlet.',
                        ],
                    ],
                ],
            ], 200),
        ]);

        $service = app(AdminInboxAiService::class);
        $result = $service->generateReplySuggestion($comment);

        $this->assertTrue($result['success']);
        $this->assertNotEmpty($result['reply']);
    }
}
