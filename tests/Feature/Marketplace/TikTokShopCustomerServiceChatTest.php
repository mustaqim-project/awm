<?php

declare(strict_types=1);

namespace Tests\Feature\Marketplace;

use App\Domain\Marketplace\MarketplaceChatService;
use App\Models\Business;
use App\Models\MarketplaceAccount;
use App\Models\MarketplaceConversation;
use App\Models\MarketplaceMessage;
use App\Models\SystemSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class TikTokShopCustomerServiceChatTest extends TestCase
{
    use RefreshDatabase;

    protected Business $business;
    protected MarketplaceAccount $account;
    protected string $appSecret = 'test_tiktok_shop_secret_12345';
    protected string $shopId = '7495392019283';

    protected function setUp(): void
    {
        parent::setUp();

        $this->business = Business::create([
            'name' => 'Demo Roastery ID',
            'slug' => 'demo-roastery-id-' . uniqid(),
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
        ]);

        $this->account = MarketplaceAccount::create([
            'business_id' => $this->business->id,
            'channel' => MarketplaceAccount::CHANNEL_TIKTOK,
            'shop_id' => $this->shopId,
            'shop_name' => 'Demo TikTok Store',
            'status' => MarketplaceAccount::STATUS_CONNECTED,
            'access_token' => 'test_access_token_xyz',
            'settings' => [
                'shop_cipher' => 'cipher_' . $this->shopId,
            ],
        ]);

        SystemSetting::set('tiktok_shop_app_secret', $this->appSecret);
        config(['services.tiktok_shop.app_secret' => $this->appSecret]);
    }

    public function test_it_receives_event_14_webhook_and_returns_code_zero(): void
    {
        $payload = [
            'type' => 14,
            'shop_id' => $this->shopId,
            'timestamp' => time(),
            'data' => [
                'content' => json_encode(['content' => 'Halo apakah kopi gayo masih ready?']),
                'conversation_id' => 'conv_test_9921',
                'create_time' => time(),
                'is_visible' => true,
                'message_id' => 'msg_ext_88123',
                'index' => '1',
                'type' => 'TEXT',
                'sender' => [
                    'im_user_id' => 'usr_buyer_111',
                    'role' => 'BUYER',
                ],
            ],
        ];

        $rawBody = json_encode($payload);
        $signature = hash_hmac('sha256', $rawBody, $this->appSecret);

        $response = $this->call(
            'POST',
            '/webhooks/marketplace/tiktok_shop',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => $signature,
            ],
            $rawBody
        );

        $response->assertStatus(200);
        $response->assertJson([
            'code' => 0,
            'success' => true,
        ]);
    }

    public function test_it_processes_event_14_and_saves_conversation_and_message(): void
    {
        $chatService = app(MarketplaceChatService::class);

        $eventData = [
            'event' => '14',
            'shop_id' => $this->shopId,
            'payload' => [
                'type' => 14,
                'shop_id' => $this->shopId,
                'data' => [
                    'content' => json_encode(['content' => 'Halo barang ready kak?']),
                    'conversation_id' => 'conv_test_1001',
                    'create_time' => time(),
                    'message_id' => 'msg_unique_1001',
                    'type' => 'TEXT',
                    'sender' => [
                        'im_user_id' => 'buyer_123',
                        'role' => 'BUYER',
                    ],
                ],
            ],
        ];

        $chatService->processChatWebhook('tiktok_shop', $eventData);

        $conversation = MarketplaceConversation::where('external_conversation_id', 'conv_test_1001')->first();
        $this->assertNotNull($conversation);
        $this->assertEquals('Halo barang ready kak?', $conversation->last_message_content);
        $this->assertEquals(1, $conversation->unread_count);

        $message = MarketplaceMessage::where('external_message_id', 'msg_unique_1001')->first();
        $this->assertNotNull($message);
        $this->assertEquals('Halo barang ready kak?', $message->content_text);
        $this->assertEquals('BUYER', $message->sender_role);
    }

    public function test_it_is_idempotent_and_ignores_duplicate_event_14(): void
    {
        $chatService = app(MarketplaceChatService::class);

        $eventData = [
            'event' => '14',
            'shop_id' => $this->shopId,
            'payload' => [
                'type' => 14,
                'shop_id' => $this->shopId,
                'data' => [
                    'content' => json_encode(['content' => 'Pesan percobaan']),
                    'conversation_id' => 'conv_test_idempotent',
                    'create_time' => time(),
                    'message_id' => 'msg_dup_test',
                    'type' => 'TEXT',
                    'sender' => [
                        'im_user_id' => 'buyer_456',
                        'role' => 'BUYER',
                    ],
                ],
            ],
        ];

        // First delivery
        $chatService->processChatWebhook('tiktok_shop', $eventData);
        $this->assertEquals(1, MarketplaceMessage::where('external_message_id', 'msg_dup_test')->count());

        // Simulated duplicate retry from TikTok Shop
        $chatService->processChatWebhook('tiktok_shop', $eventData);
        $this->assertEquals(1, MarketplaceMessage::where('external_message_id', 'msg_dup_test')->count());
    }

    public function test_it_sends_agent_reply_and_marks_as_read(): void
    {
        $chatService = app(MarketplaceChatService::class);

        $conversation = MarketplaceConversation::create([
            'business_id' => $this->business->id,
            'marketplace_account_id' => $this->account->id,
            'channel' => 'tiktok_shop',
            'shop_id' => $this->shopId,
            'external_conversation_id' => 'conv_reply_test',
            'unread_count' => 2,
        ]);

        $reply = $chatService->sendMessage($conversation, 'TEXT', 'Siap kak, barang ready dan siap kirim!');
        $this->assertNotNull($reply);
        $this->assertEquals('SELLER', $reply->sender_role);
        $this->assertEquals('Siap kak, barang ready dan siap kirim!', $reply->content_text);

        // Mark as read
        $chatService->markAsRead($conversation);
        $conversation->refresh();
        $this->assertEquals(0, $conversation->unread_count);
    }
}
