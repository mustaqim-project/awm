<?php

declare(strict_types=1);

namespace App\Domain\Marketplace;

use App\Models\MarketplaceAccount;
use App\Models\MarketplaceConversation;
use App\Models\MarketplaceMessage;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MarketplaceChatService
{
    public function __construct(protected MarketplaceManagerService $manager) {}

    /**
     * Process incoming chat webhook events (Event 13: New conversation, Event 14: New message).
     */
    public function processChatWebhook(string $channel, array $eventData): void
    {
        $eventType = (int) ($eventData['event'] ?? ($eventData['payload']['type'] ?? 0));
        $shopId    = (string) ($eventData['shop_id'] ?? ($eventData['payload']['shop_id'] ?? ''));
        $payload   = $eventData['payload'] ?? [];
        $data      = $payload['data'] ?? [];

        if (empty($shopId) || empty($data)) {
            return;
        }

        // Find associated connected account
        $account = MarketplaceAccount::where('channel', $channel)
            ->where(function ($query) use ($shopId) {
                $query->where('shop_id', $shopId)
                    ->orWhere('settings->shop_cipher', $shopId);
            })
            ->first();

        // Fallback for review/sandbox store if shopId matches review pattern
        if (! $account) {
            $account = MarketplaceAccount::where('channel', $channel)->first();
        }

        if (! $account) {
            Log::warning("Marketplace account not found for chat webhook", ['channel' => $channel, 'shop_id' => $shopId]);
            return;
        }

        $conversationId = (string) ($data['conversation_id'] ?? '');
        if (empty($conversationId)) {
            return;
        }

        // 1. Upsert Conversation Thread
        $conversation = MarketplaceConversation::firstOrCreate([
            'channel'                  => $channel,
            'external_conversation_id' => $conversationId,
        ], [
            'business_id'             => $account->business_id,
            'marketplace_account_id'  => $account->id,
            'shop_id'                 => $account->shop_id,
            'shop_cipher'             => $account->settings['shop_cipher'] ?? $account->shop_id,
            'participant_id'          => $data['sender']['im_user_id'] ?? null,
            'participant_name'        => 'TikTok Buyer (' . substr($conversationId, -6) . ')',
            'status'                  => 'open',
            'raw_metadata'            => $data,
        ]);

        // 2. Process Event 14: New Message
        if ($eventType === 14) {
            $messageId = (string) ($data['message_id'] ?? '');
            if (empty($messageId)) {
                return;
            }

            // Primary Idempotency Check: prevent duplicate messages from webhook retries
            if (MarketplaceMessage::where('external_message_id', $messageId)->exists()) {
                Log::info("Duplicate chat message ignored (idempotent)", ['message_id' => $messageId]);
                return;
            }

            // Extract content from stringified JSON
            $rawContent = (string) ($data['content'] ?? '');
            $decoded    = json_decode($rawContent, true);
            $contentText = null;
            $mediaUrl    = null;

            if (is_array($decoded)) {
                $contentText = $decoded['content'] ?? ($decoded['text'] ?? null);
                $mediaUrl    = $decoded['image_url'] ?? null;
            } else {
                $contentText = $rawContent;
            }

            $messageType = strtoupper((string) ($data['type'] ?? 'TEXT'));
            $senderRole  = strtoupper((string) ($data['sender']['role'] ?? 'BUYER'));
            $isBuyer     = ($senderRole === 'BUYER');

            $createdAt = isset($data['create_time'])
                ? Carbon::createFromTimestamp((int) $data['create_time'])
                : now();

            MarketplaceMessage::create([
                'marketplace_conversation_id' => $conversation->id,
                'external_message_id'        => $messageId,
                'sender_role'                => $senderRole,
                'sender_user_id'             => (string) ($data['sender']['im_user_id'] ?? ''),
                'message_type'               => $messageType,
                'raw_content'                => $rawContent,
                'content_text'               => $contentText ?? ($mediaUrl ? '[Gambar]' : ''),
                'media_url'                  => $mediaUrl,
                'is_read_by_agent'           => ! $isBuyer,
                'is_synced_to_marketplace'   => true,
                'marketplace_created_at'     => $createdAt,
            ]);

            // Update thread state
            $conversation->update([
                'last_message_content' => $contentText ?? ($mediaUrl ? '[Gambar]' : ''),
                'last_message_type'    => $messageType,
                'last_message_at'      => $createdAt,
                'unread_count'         => $isBuyer ? DB::raw('unread_count + 1') : $conversation->unread_count,
            ]);

            Log::info("Processed TikTok Shop chat message", [
                'conversation_id' => $conversationId,
                'message_id'      => $messageId,
                'role'            => $senderRole,
            ]);
        } elseif ($eventType === 13) {
            Log::info("Processed TikTok Shop new conversation", [
                'conversation_id' => $conversationId,
            ]);
        }
    }

    /**
     * Send a message from Cooca to TikTok Shop and save locally.
     */
    public function sendMessage(MarketplaceConversation $conversation, string $type, string $textOrUrl): MarketplaceMessage
    {
        $account = $conversation->account;
        if (! $account) {
            throw new \RuntimeException('Marketplace account tidak ditemukan untuk percakapan ini.');
        }

        $adapter = $this->manager->driver($conversation->channel);

        // Call TikTok Shop OpenAPI
        $resp = $adapter->sendChatMessage(
            $account,
            $conversation->external_conversation_id,
            $type,
            $textOrUrl
        );

        if (($resp['code'] ?? -1) !== 0) {
            throw new \RuntimeException($resp['message'] ?? 'Gagal mengirim pesan ke TikTok Shop.');
        }

        $messageId = $resp['data']['message_id'] ?? ('msg_agent_' . uniqid());

        $message = MarketplaceMessage::create([
            'marketplace_conversation_id' => $conversation->id,
            'external_message_id'        => $messageId,
            'sender_role'                => 'SELLER',
            'sender_user_id'             => 'agent_cooca',
            'message_type'               => strtoupper($type),
            'raw_content'                => json_encode(['content' => $textOrUrl]),
            'content_text'               => $textOrUrl,
            'media_url'                  => ($type === 'IMAGE') ? $textOrUrl : null,
            'is_read_by_agent'           => true,
            'is_synced_to_marketplace'   => true,
            'marketplace_created_at'     => now(),
        ]);

        $conversation->update([
            'last_message_content' => $textOrUrl,
            'last_message_type'    => strtoupper($type),
            'last_message_at'      => now(),
        ]);

        return $message;
    }

    /**
     * Mark conversation as read both on TikTok Shop and locally.
     */
    public function markAsRead(MarketplaceConversation $conversation): void
    {
        $conversation->update(['unread_count' => 0]);
        $conversation->messages()->where('is_read_by_agent', false)->update(['is_read_by_agent' => true]);

        $account = $conversation->account;
        if ($account) {
            try {
                $adapter = $this->manager->driver($conversation->channel);
                $adapter->markConversationRead($account, $conversation->external_conversation_id);
            } catch (\Throwable $e) {
                Log::warning("Failed to mark conversation read on marketplace: {$e->getMessage()}");
            }
        }
    }
}
