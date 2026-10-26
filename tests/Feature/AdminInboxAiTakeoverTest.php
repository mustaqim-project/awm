<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Ai\Platform\AiPlatformEngineService;
use App\Domain\Ai\Providers\AiProviderInterface;
use App\Domain\Ai\Providers\AiProviderManager;
use App\Domain\SocialMedia\AdminInboxAiService;
use App\Models\Admin;
use App\Models\InboxThreadTakeover;
use App\Models\SocialMediaComment;
use App\Models\SystemSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Tests\TestCase;

final class AdminInboxAiTakeoverTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;
    private AdminInboxAiService $aiService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::create([
            'name'     => 'Customer Service Agent',
            'email'    => 'cs@cooca.id',
            'password' => bcrypt('password123'),
            'role'     => 'super_admin',
        ]);

        // Enable AI and set to auto_pilot
        SystemSetting::set('admin_inbox_ai_enabled', 'true', AdminInboxAiService::SETTING_GROUP);
        SystemSetting::set('admin_inbox_ai_mode', 'auto_pilot', AdminInboxAiService::SETTING_GROUP);
        SystemSetting::set('admin_inbox_ai_channels', json_encode([
            'live_chat', 'whatsapp', 'instagram', 'messenger', 'facebook_comments', 'instagram_comments'
        ]), AdminInboxAiService::SETTING_GROUP);

        $this->withoutMiddleware([
            \Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class,
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
        ]);

        $this->aiService = app(AdminInboxAiService::class);
    }

    public function test_ai_reply_sanitization_removes_markdown_bold_completely(): void
    {
        $input = "Halo Kak! Kami merekomendasikan paket **Premium Plan** seharga **Rp 89.000 / bulan** untuk bisnis Anda.";
        $output = $this->aiService->sanitizeReplyText($input);

        $this->assertStringNotContainsString('**', $output);
        $this->assertStringContainsString('Premium Plan', $output);
        $this->assertStringContainsString('Rp 89.000 / bulan', $output);
    }

    public function test_ai_reply_sanitization_transforms_raw_urls_to_descriptive_links(): void
    {
        $input = "Untuk mengatur hak akses staf, silakan buka menu /roles. Lalu periksa gudang di /inventory dan pengaturan di /settings?tab=general.";
        $output = $this->aiService->sanitizeReplyText($input);

        $this->assertStringNotContainsString('/roles ', $output . ' ');
        $this->assertStringNotContainsString('/inventory ', $output . ' ');
        $this->assertStringContainsString('[Kelola Peran & Hak Akses Staf](https://cooca.id/roles)', $output);
        $this->assertStringContainsString('[Manajemen Gudang & Multi-Cabang](https://cooca.id/inventory)', $output);
        $this->assertStringContainsString('[Pengaturan Profil Bisnis](https://cooca.id/settings?tab=general)', $output);
    }

    public function test_ai_reply_preserves_valid_descriptive_markdown_links(): void
    {
        $input = "Silakan pelajari langkahnya di artikel [Setup Multi-Outlet COOCA UMKM dalam 24 Jam: Step-by-step Migrasi](https://cooca.id/blog/setup-multi-outlet-cooca-umkm-dalam-24-jam-step-by-step-migrasi) resmi kami.";
        $output = $this->aiService->sanitizeReplyText($input);

        $this->assertStringContainsString('[Setup Multi-Outlet COOCA UMKM dalam 24 Jam: Step-by-step Migrasi](https://cooca.id/blog/setup-multi-outlet-cooca-umkm-dalam-24-jam-step-by-step-migrasi)', $output);
        $this->assertStringNotContainsString('[[', $output);
    }

    public function test_ai_auto_reply_fires_when_takeover_is_inactive(): void
    {
        $comment = SocialMediaComment::create([
            'is_platform'         => true,
            'platform'            => 'live_chat',
            'platform_comment_id' => 'lc_' . Str::uuid(),
            'platform_post_id'    => 'live_chat_website',
            'from_id'             => 'session_visitor_123',
            'from_name'           => 'Budi Santoso',
            'from_email'          => 'budi@example.com',
            'session_token'       => 'session_visitor_123',
            'message'             => 'Halo, berapa harga paket COOCA?',
            'is_from_page'        => false,
            'status'              => 'unread',
            'created_time'        => now(),
        ]);

        $reply = $this->aiService->processAutoReplyIfEligible($comment);

        $this->assertNotNull($reply);
        $this->assertTrue($reply->is_from_page);
        $this->assertEquals('live_chat', $reply->platform);
        $this->assertEquals('replied', $comment->fresh()->status);
        $this->assertStringNotContainsString('**', $reply->message);

        // Verify thread takeover table recorded the AI reply timestamp
        $threadKey = InboxThreadTakeover::resolveThreadKey('live_chat', 'session_visitor_123', 'session_visitor_123');
        $takeover = InboxThreadTakeover::getTakeoverStatus($threadKey);
        $this->assertNotNull($takeover);
        $this->assertFalse($takeover->is_human_takeover);
        $this->assertNotNull($takeover->last_ai_reply_at);
    }

    public function test_ai_auto_reply_does_not_fire_when_human_takeover_is_active(): void
    {
        $sessionToken = 'session_visitor_takeover_456';
        $threadKey = InboxThreadTakeover::resolveThreadKey('live_chat', $sessionToken, $sessionToken);

        // Mark thread as taken over by human agent
        InboxThreadTakeover::markTakeover($threadKey, 'live_chat', $sessionToken, (string) $this->admin->id, $this->admin->name);

        $this->assertTrue(InboxThreadTakeover::isTakenOver($threadKey));

        $comment = SocialMediaComment::create([
            'is_platform'         => true,
            'platform'            => 'live_chat',
            'platform_comment_id' => 'lc_' . Str::uuid(),
            'platform_post_id'    => 'live_chat_website',
            'from_id'             => $sessionToken,
            'from_name'           => 'Citra Lestari',
            'session_token'       => $sessionToken,
            'message'             => 'Tolong jelaskan fitur kasir POS offline.',
            'is_from_page'        => false,
            'status'              => 'unread',
            'created_time'        => now(),
        ]);

        $reply = $this->aiService->processAutoReplyIfEligible($comment);

        // AI must NOT reply
        $this->assertNull($reply);
        $this->assertEquals('unread', $comment->fresh()->status);
        $this->assertDatabaseMissing('social_media_comments', [
            'parent_comment_id' => $comment->platform_comment_id,
            'is_from_page'      => true,
        ]);
    }

    public function test_ai_resumes_auto_reply_after_human_agent_releases_takeover(): void
    {
        $sessionToken = 'session_visitor_release_789';
        $threadKey = InboxThreadTakeover::resolveThreadKey('live_chat', $sessionToken, $sessionToken);

        // 1. Takeover active
        InboxThreadTakeover::markTakeover($threadKey, 'live_chat', $sessionToken, (string) $this->admin->id, $this->admin->name);
        $this->assertTrue(InboxThreadTakeover::isTakenOver($threadKey));

        // 2. Explicitly release takeover
        $released = InboxThreadTakeover::releaseTakeover($threadKey);
        $this->assertTrue($released);
        $this->assertFalse(InboxThreadTakeover::isTakenOver($threadKey));

        // 3. New message arrives
        $comment = SocialMediaComment::create([
            'is_platform'         => true,
            'platform'            => 'live_chat',
            'platform_comment_id' => 'lc_' . Str::uuid(),
            'platform_post_id'    => 'live_chat_website',
            'from_id'             => $sessionToken,
            'from_name'           => 'Dewi Sartika',
            'session_token'       => $sessionToken,
            'message'             => 'Halo, apakah ada promo paket tahunan?',
            'is_from_page'        => false,
            'status'              => 'unread',
            'created_time'        => now(),
        ]);

        $reply = $this->aiService->processAutoReplyIfEligible($comment);

        // AI must resume replying
        $this->assertNotNull($reply);
        $this->assertTrue($reply->is_from_page);
        $this->assertStringNotContainsString('**', $reply->message);
    }

    public function test_manual_reply_by_human_agent_sets_takeover_status_automatically(): void
    {
        $comment = SocialMediaComment::create([
            'is_platform'         => true,
            'platform'            => 'live_chat',
            'platform_comment_id' => 'lc_' . Str::uuid(),
            'platform_post_id'    => 'live_chat_website',
            'from_id'             => 'session_agent_takeover_101',
            'from_name'           => 'Ahmad Dani',
            'session_token'       => 'session_agent_takeover_101',
            'message'             => 'Saya butuh bantuan kendala printer kasir.',
            'is_from_page'        => false,
            'status'              => 'unread',
            'created_time'        => now(),
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->postJson(route('admin.inbox.reply', $comment->id), [
                'message' => 'Halo Kak Ahmad, saya CS Support Cooca akan membantu Anda.',
            ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        // Verify thread is now automatically marked as human takeover
        $threadKey = InboxThreadTakeover::resolveThreadKey('live_chat', 'session_agent_takeover_101', 'session_agent_takeover_101');
        $this->assertTrue(InboxThreadTakeover::isTakenOver($threadKey));

        $takeover = InboxThreadTakeover::getTakeoverStatus($threadKey);
        $this->assertEquals($this->admin->id, $takeover->takeover_by_id);
        $this->assertEquals($this->admin->name, $takeover->takeover_by_name);

        // Verify comment record also updated
        $freshComment = $comment->fresh();
        $this->assertTrue($freshComment->is_human_takeover);
        $this->assertEquals($this->admin->id, $freshComment->takeover_by_id);
    }

    public function test_toggle_takeover_endpoint_switches_between_takeover_and_release(): void
    {
        $comment = SocialMediaComment::create([
            'is_platform'         => true,
            'platform'            => 'live_chat',
            'platform_comment_id' => 'lc_' . Str::uuid(),
            'platform_post_id'    => 'live_chat_website',
            'from_id'             => 'session_toggle_202',
            'from_name'           => 'Eka Pratama',
            'session_token'       => 'session_toggle_202',
            'message'             => 'Bisa dibantu integrasi Shopee?',
            'is_from_page'        => false,
            'status'              => 'unread',
            'created_time'        => now(),
        ]);

        // 1. Takeover
        $resTakeover = $this->actingAs($this->admin, 'admin')
            ->postJson(route('admin.inbox.toggle-takeover', $comment->id), [
                'action' => 'takeover',
            ]);

        $resTakeover->assertOk();
        $resTakeover->assertJson([
            'success'           => true,
            'is_human_takeover' => true,
            'takeover_by_name'  => $this->admin->name,
        ]);

        $threadKey = InboxThreadTakeover::resolveThreadKey('live_chat', 'session_toggle_202', 'session_toggle_202');
        $this->assertTrue(InboxThreadTakeover::isTakenOver($threadKey));

        // 2. Release takeover
        $resRelease = $this->actingAs($this->admin, 'admin')
            ->postJson(route('admin.inbox.toggle-takeover', $comment->id), [
                'action' => 'release',
            ]);

        $resRelease->assertOk();
        $resRelease->assertJson([
            'success'           => true,
            'is_human_takeover' => false,
        ]);

        $this->assertFalse(InboxThreadTakeover::isTakenOver($threadKey));
    }

    public function test_poll_endpoint_includes_takeover_metadata_in_threads(): void
    {
        $sessionToken = 'session_poll_meta_303';
        $threadKey = InboxThreadTakeover::resolveThreadKey('live_chat', $sessionToken, $sessionToken);
        InboxThreadTakeover::markTakeover($threadKey, 'live_chat', $sessionToken, (string) $this->admin->id, $this->admin->name);

        SocialMediaComment::create([
            'is_platform'         => true,
            'platform'            => 'live_chat',
            'platform_comment_id' => 'lc_' . Str::uuid(),
            'platform_post_id'    => 'live_chat_website',
            'from_id'             => $sessionToken,
            'from_name'           => 'Fajar Hidayat',
            'session_token'       => $sessionToken,
            'message'             => 'Pesan polling test.',
            'is_from_page'        => false,
            'status'              => 'unread',
            'created_time'        => now(),
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->getJson(route('admin.inbox.poll'));

        $response->assertOk();
        $threads = $response->json('threads');
        $this->assertNotEmpty($threads);

        $matchingThread = collect($threads)->firstWhere('customer_id', $sessionToken);
        $this->assertNotNull($matchingThread);
        $this->assertTrue($matchingThread['is_human_takeover']);
        $this->assertEquals($this->admin->name, $matchingThread['takeover_by_name']);
        $this->assertEquals($threadKey, $matchingThread['thread_key']);
    }

    public function test_anti_double_reply_prevents_duplicate_replies(): void
    {
        $comment = SocialMediaComment::create([
            'is_platform'         => true,
            'platform'            => 'live_chat',
            'platform_comment_id' => 'lc_' . Str::uuid(),
            'platform_post_id'    => 'live_chat_website',
            'from_id'             => 'session_double_404',
            'from_name'           => 'Gita Gutawa',
            'session_token'       => 'session_double_404',
            'message'             => 'Berapa biaya per outlet tambahan?',
            'is_from_page'        => false,
            'status'              => 'unread',
            'created_time'        => now(),
        ]);

        // First call generates reply
        $firstReply = $this->aiService->processAutoReplyIfEligible($comment);
        $this->assertNotNull($firstReply);

        // Second call immediately with same comment must return null because comment status is now replied
        $secondReply = $this->aiService->processAutoReplyIfEligible($comment);
        $this->assertNull($secondReply);

        // Verify only 1 reply exists in database for this comment
        $replyCount = SocialMediaComment::where('parent_comment_id', $comment->platform_comment_id)
            ->where('is_from_page', true)
            ->count();
        $this->assertEquals(1, $replyCount);
    }
}
