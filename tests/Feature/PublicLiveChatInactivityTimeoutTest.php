<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\Web\PublicLiveChatController;
use App\Models\InboxThreadTakeover;
use App\Models\SocialMediaComment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Tests\TestCase;

final class PublicLiveChatInactivityTimeoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([
            \Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class,
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_visitor_can_start_live_chat_session_and_receive_token(): void
    {
        $response = $this->postJson(route('public.live-chat.start'), [
            'name'    => 'Agung Wijaya',
            'phone'   => '081234567890',
            'email'   => 'agung@example.com',
            'message' => 'Halo min, mau tanya cara setup sistemnya?',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'visitor' => [
                'name'  => 'Agung Wijaya',
                'email' => 'agung@example.com',
            ],
        ]);

        $sessionToken = $response->json('session_token');
        $this->assertNotEmpty($sessionToken);
        $this->assertDatabaseHas('social_media_comments', [
            'platform'      => 'live_chat',
            'session_token' => $sessionToken,
            'is_from_page'  => false,
            'status'        => 'replied',
        ]);
    }

    public function test_visitor_can_send_followup_message_in_active_session(): void
    {
        $sessionToken = 'live_test_' . Str::random(20);

        // Visitor initial message
        SocialMediaComment::create([
            'is_platform'         => true,
            'platform'            => 'live_chat',
            'platform_comment_id' => 'lc_' . Str::uuid(),
            'platform_post_id'    => 'live_chat_website',
            'from_id'             => $sessionToken,
            'from_name'           => 'Agung Wijaya',
            'from_email'          => 'agung@example.com',
            'from_phone'          => '081234567890',
            'session_token'       => $sessionToken,
            'message'             => 'Pesan pertama pengunjung',
            'is_from_page'        => false,
            'status'              => 'unread',
            'created_time'        => now(),
        ]);

        $response = $this->postJson(route('public.live-chat.send'), [
            'session_token' => $sessionToken,
            'message'       => 'kak mau tanya cara setup sistemnya',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success'       => true,
            'session_ended' => false,
            'message'       => [
                'message'      => 'kak mau tanya cara setup sistemnya',
                'from_name'    => 'Agung Wijaya',
                'is_from_page' => false,
            ],
        ]);
    }

    public function test_session_does_not_expire_if_last_message_was_from_visitor(): void
    {
        $sessionToken = 'live_test_visitor_' . Str::random(20);

        SocialMediaComment::create([
            'is_platform'         => true,
            'platform'            => 'live_chat',
            'platform_comment_id' => 'lc_' . Str::uuid(),
            'platform_post_id'    => 'live_chat_website',
            'from_id'             => $sessionToken,
            'from_name'           => 'Agung Wijaya',
            'session_token'       => $sessionToken,
            'message'             => 'Halo admin?',
            'is_from_page'        => false,
            'status'              => 'unread',
            'created_time'        => now()->subMinutes(10),
        ]);

        $controller = app(PublicLiveChatController::class);
        $this->assertFalse($controller->checkAndHandleInactivityTimeout($sessionToken));
    }

    public function test_session_expires_after_5_minutes_if_visitor_does_not_reply_to_admin(): void
    {
        $now = Carbon::parse('2026-10-26 15:00:00');
        Carbon::setTestNow($now);

        $sessionToken = 'live_test_timeout_' . Str::random(20);

        // 1. Visitor initial message
        $visitorMsg = SocialMediaComment::create([
            'is_platform'         => true,
            'platform'            => 'live_chat',
            'platform_comment_id' => 'lc_' . Str::uuid(),
            'platform_post_id'    => 'live_chat_website',
            'from_id'             => $sessionToken,
            'from_name'           => 'Agung Wijaya',
            'session_token'       => $sessionToken,
            'message'             => 'Cara setup sistem?',
            'is_from_page'        => false,
            'status'              => 'replied',
            'created_time'        => $now,
        ]);

        // 2. Admin replies at 15:01:00
        $adminMsgTime = $now->copy()->addMinute();
        SocialMediaComment::create([
            'is_platform'         => true,
            'platform'            => 'live_chat',
            'platform_comment_id' => 'lc_' . Str::uuid(),
            'platform_post_id'    => 'live_chat_website',
            'parent_comment_id'   => $visitorMsg->platform_comment_id,
            'from_id'             => 'cooca_support',
            'from_name'           => 'Cooca Support',
            'session_token'       => $sessionToken,
            'message'             => 'Buka menu Pengaturan -> Peran & Hak Akses',
            'is_from_page'        => true,
            'status'              => 'replied',
            'created_time'        => $adminMsgTime,
        ]);

        // Mark takeover
        $threadKey = InboxThreadTakeover::resolveThreadKey('live_chat', $sessionToken, $sessionToken);
        InboxThreadTakeover::markTakeover($threadKey, 'live_chat', $sessionToken, '1', 'Admin CS');
        $this->assertTrue(InboxThreadTakeover::isTakenOver($threadKey));

        // 3. At 3 minutes after admin reply (15:04:00): session must still be active
        Carbon::setTestNow($adminMsgTime->copy()->addMinutes(3));
        $controller = app(PublicLiveChatController::class);
        $this->assertFalse($controller->checkAndHandleInactivityTimeout($sessionToken));

        // 4. At 5 minutes and 1 second after admin reply (15:06:01): session must expire!
        Carbon::setTestNow($adminMsgTime->copy()->addMinutes(5)->addSecond());
        $this->assertTrue($controller->checkAndHandleInactivityTimeout($sessionToken));

        // Thread status must transition to resolved
        $this->assertEquals('resolved', $visitorMsg->fresh()->status);

        // Takeover must be released
        $this->assertFalse(InboxThreadTakeover::isTakenOver($threadKey));
    }

    public function test_poll_and_history_endpoints_return_session_ended_when_inactivity_exceeds_5_minutes(): void
    {
        $now = Carbon::parse('2026-10-26 15:00:00');
        Carbon::setTestNow($now);

        $sessionToken = 'live_test_poll_' . Str::random(20);

        SocialMediaComment::create([
            'is_platform'         => true,
            'platform'            => 'live_chat',
            'platform_comment_id' => 'lc_' . Str::uuid(),
            'platform_post_id'    => 'live_chat_website',
            'from_id'             => $sessionToken,
            'from_name'           => 'Agung Wijaya',
            'session_token'       => $sessionToken,
            'message'             => 'Pesan pembuka',
            'is_from_page'        => false,
            'status'              => 'replied',
            'created_time'        => $now,
        ]);

        $adminTime = $now->copy()->addMinute();
        SocialMediaComment::create([
            'is_platform'         => true,
            'platform'            => 'live_chat',
            'platform_comment_id' => 'lc_' . Str::uuid(),
            'platform_post_id'    => 'live_chat_website',
            'from_id'             => 'cooca_support',
            'from_name'           => 'Cooca Support',
            'session_token'       => $sessionToken,
            'message'             => 'Halo Kak Agung, ada yang bisa kami bantu?',
            'is_from_page'        => true,
            'status'              => 'replied',
            'created_time'        => $adminTime,
        ]);

        // Advance 6 minutes after admin message
        Carbon::setTestNow($adminTime->copy()->addMinutes(6));

        // Poll endpoint returns session_ended = true
        $pollRes = $this->getJson(route('public.live-chat.poll', ['session_token' => $sessionToken]));
        $pollRes->assertOk();
        $pollRes->assertJson([
            'success'       => true,
            'session_ended' => true,
        ]);

        // History endpoint returns session_ended = true
        $historyRes = $this->getJson(route('public.live-chat.history', ['session_token' => $sessionToken]));
        $historyRes->assertOk();
        $historyRes->assertJson([
            'success'       => true,
            'session_ended' => true,
        ]);
    }

    public function test_visitor_send_message_rejected_with_410_when_session_expired(): void
    {
        $now = Carbon::parse('2026-10-26 15:00:00');
        Carbon::setTestNow($now);

        $sessionToken = 'live_test_rejected_' . Str::random(20);

        SocialMediaComment::create([
            'is_platform'         => true,
            'platform'            => 'live_chat',
            'platform_comment_id' => 'lc_' . Str::uuid(),
            'platform_post_id'    => 'live_chat_website',
            'from_id'             => $sessionToken,
            'from_name'           => 'Agung Wijaya',
            'session_token'       => $sessionToken,
            'message'             => 'Tanya fitur POS',
            'is_from_page'        => false,
            'status'              => 'replied',
            'created_time'        => $now,
        ]);

        $adminTime = $now->copy()->addMinute();
        SocialMediaComment::create([
            'is_platform'         => true,
            'platform'            => 'live_chat',
            'platform_comment_id' => 'lc_' . Str::uuid(),
            'platform_post_id'    => 'live_chat_website',
            'from_id'             => 'cooca_support',
            'from_name'           => 'Cooca Support',
            'session_token'       => $sessionToken,
            'message'             => 'Fitur POS kami mendukung multi-outlet dan printer thermal.',
            'is_from_page'        => true,
            'status'              => 'replied',
            'created_time'        => $adminTime,
        ]);

        // Advance 6 minutes after admin reply (exceeding 5 minutes timeout)
        Carbon::setTestNow($adminTime->copy()->addMinutes(6));

        $response = $this->postJson(route('public.live-chat.send'), [
            'session_token' => $sessionToken,
            'message'       => 'kak mau tanya cara setup sistemnya',
        ]);

        $response->assertStatus(410);
        $response->assertJson([
            'success'       => false,
            'session_ended' => true,
        ]);
        $this->assertStringContainsString('5 menit', $response->json('message'));
    }

    public function test_live_chat_history_preserves_action_links_and_trial_url(): void
    {
        $sessionToken = 'live_test_links_' . Str::random(20);

        SocialMediaComment::create([
            'is_platform'         => true,
            'platform'            => 'live_chat',
            'platform_comment_id' => 'lc_' . Str::uuid(),
            'platform_post_id'    => 'live_chat_website',
            'from_id'             => 'cooca_support',
            'from_name'           => 'Cooca Support',
            'session_token'       => $sessionToken,
            'message'             => 'Kak Agung bisa mencoba semua fitur via [Pendaftaran Free Trial 14 Hari](https://cooca.id/register). 🚀',
            'is_from_page'        => true,
            'status'              => 'replied',
            'created_time'        => now(),
        ]);

        $response = $this->getJson(route('public.live-chat.history', ['session_token' => $sessionToken]));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);
        $messages = $response->json('messages');
        $this->assertCount(1, $messages);
        $this->assertStringContainsString('[Pendaftaran Free Trial 14 Hari](https://cooca.id/register)', $messages[0]['message']);
    }
}
