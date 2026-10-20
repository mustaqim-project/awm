<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\SocialMedia;

use App\Http\Controllers\Controller;
use App\Models\SocialMediaAccount;
use App\Models\SocialMediaComment;
use App\Models\SystemSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MetaOmnichannelWebhookController extends Controller
{
    /**
     * Handle Meta Webhook Verification (GET /api/v1/meta/omnichannel/webhook).
     */
    public function verify(Request $request): Response
    {
        $mode = (string) ($request->query('hub.mode') ?? $request->query('hub_mode') ?? '');
        $token = (string) ($request->query('hub.verify_token') ?? $request->query('hub_verify_token') ?? '');
        $challenge = (string) ($request->query('hub.challenge') ?? $request->query('hub_challenge') ?? '');

        $configuredToken = (string) (
            SystemSetting::get('social_media_webhook_verify_token')
            ?: config('services.meta_social.webhook_verify_token')
            ?: config('services.meta_whatsapp.webhook_verify_token')
            ?: 'cooca_meta_social_webhook_token'
        );

        if ($mode === 'subscribe' && ($token === $configuredToken || hash_equals($configuredToken, $token))) {
            Log::info('[MetaOmnichannelWebhook] Webhook verified successfully');
            return response($challenge, 200)->header('Content-Type', 'text/plain');
        }

        Log::warning('[MetaOmnichannelWebhook] Verification failed', [
            'mode'     => $mode,
            'received' => $token,
            'expected' => $configuredToken,
        ]);

        return response('Forbidden', 403);
    }

    /**
     * Handle Incoming Meta Events for Messenger, Instagram, WhatsApp, and Comments (POST).
     */
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->all();
        $object = (string) ($payload['object'] ?? '');

        Log::info('[MetaOmnichannelWebhook] Event received', [
            'object' => $object,
            'entries_count' => count($payload['entry'] ?? []),
        ]);

        $entries = $payload['entry'] ?? [];

        foreach ($entries as $entry) {
            $entryId = (string) ($entry['id'] ?? '');

            // 1. WhatsApp Business Account Events (object = 'whatsapp_business_account' or entry.changes field = 'messages')
            if ($object === 'whatsapp_business_account' || isset($entry['changes'])) {
                foreach ($entry['changes'] ?? [] as $change) {
                    $field = (string) ($change['field'] ?? '');
                    $val = $change['value'] ?? [];

                    // 1A. WhatsApp Messages
                    if ($field === 'messages' && isset($val['messages'])) {
                        $this->processWhatsAppMessages($val, $entryId);
                        continue;
                    }

                    // 1B. Facebook & Instagram Comments (field = 'feed' or 'comments')
                    if (in_array($field, ['feed', 'comments'], true)) {
                        $this->processFeedComments($val, $entryId);
                        continue;
                    }
                }
            }

            // 2. Messenger & Instagram Direct Messages (entry.messaging)
            if (isset($entry['messaging']) && is_array($entry['messaging'])) {
                $this->processDirectMessaging($entry['messaging'], $entryId);
            }
        }

        return response()->json(['status' => 'EVENT_RECEIVED'], 200);
    }

    /**
     * Process Messenger and Instagram Direct incoming messages.
     *
     * @param array<int, array<string, mixed>> $messagingEvents
     */
    protected function processDirectMessaging(array $messagingEvents, string $pageOrIgId): void
    {
        $platformPageId = (string) (SystemSetting::get('meta_page_id') ?: SystemSetting::get('social_media_page_id', ''));
        $platformIgId = (string) (SystemSetting::get('meta_instagram_id') ?: SystemSetting::get('instagram_account_id', ''));

        foreach ($messagingEvents as $msgEvent) {
            $senderId = (string) data_get($msgEvent, 'sender.id');
            $recipientId = (string) data_get($msgEvent, 'recipient.id');
            $messageData = data_get($msgEvent, 'message', []);
            $mid = (string) data_get($messageData, 'mid');
            $text = (string) data_get($messageData, 'text');
            $timestamp = data_get($msgEvent, 'timestamp');

            // Skip message echo from page itself
            if (empty($senderId) || empty($text) || $senderId === $recipientId) {
                continue;
            }
            if ($senderId === $platformPageId || $senderId === $platformIgId) {
                continue;
            }

            // Determine channel: Instagram DM or Messenger
            $channel = 'messenger';
            if ($recipientId === $platformIgId || str_contains($mid, 'ig_') || str_contains($mid, 'a_')) {
                $channel = 'instagram';
            }

            SocialMediaComment::updateOrCreate(
                [
                    'is_platform'         => true,
                    'platform'            => $channel,
                    'platform_comment_id' => $mid ?: (string) Str::uuid(),
                ],
                [
                    'business_id'             => null,
                    'social_media_account_id' => null,
                    'social_media_post_id'    => null,
                    'platform_post_id'        => $recipientId ?: $pageOrIgId,
                    'parent_comment_id'       => null,
                    'from_id'                 => $senderId,
                    'from_name'               => $channel === 'instagram' ? 'Instagram Direct User' : 'Messenger User',
                    'message'                 => $text,
                    'is_from_page'            => false,
                    'status'                  => 'unread',
                    'created_time'            => $timestamp ? date('Y-m-d H:i:s', (int) ($timestamp / 1000)) : now(),
                ]
            );

            Log::info("[MetaOmnichannelWebhook] Direct message saved: {$channel} from {$senderId}");
        }
    }

    /**
     * Process Facebook and Instagram comments from feed/comments webhook.
     *
     * @param array<string, mixed> $val
     */
    protected function processFeedComments(array $val, string $pageOrIgId): void
    {
        $item = (string) ($val['item'] ?? '');
        $commentId = (string) ($val['comment_id'] ?? '');
        $postId = (string) ($val['post_id'] ?? '');
        $message = (string) ($val['message'] ?? '');
        $senderId = (string) (data_get($val, 'from.id') ?: ($val['sender_id'] ?? ''));
        $senderName = (string) (data_get($val, 'from.name') ?: ($val['sender_name'] ?? 'Pengguna Media Sosial'));
        $parentId = (string) ($val['parent_id'] ?? '');

        if (empty($commentId) && empty($message)) {
            return;
        }

        // Determine if FB or IG comment
        $platformPageId = (string) (SystemSetting::get('meta_page_id') ?: SystemSetting::get('social_media_page_id', ''));
        $platform = 'facebook_comments';
        if (str_contains($commentId, 'ig_') || (isset($val['media']) && isset($val['media']['id']))) {
            $platform = 'instagram_comments';
        }

        SocialMediaComment::updateOrCreate(
            [
                'is_platform'         => true,
                'platform'            => $platform,
                'platform_comment_id' => $commentId ?: (string) Str::uuid(),
            ],
            [
                'business_id'             => null,
                'social_media_account_id' => null,
                'social_media_post_id'    => null,
                'platform_post_id'        => $postId ?: $pageOrIgId,
                'parent_comment_id'       => ($parentId && $parentId !== $postId) ? $parentId : null,
                'from_id'                 => $senderId,
                'from_name'               => $senderName,
                'message'                 => $message,
                'is_from_page'            => ($senderId === $platformPageId),
                'status'                  => 'unread',
                'created_time'            => isset($val['created_time']) ? date('Y-m-d H:i:s', (int) $val['created_time']) : now(),
            ]
        );

        Log::info("[MetaOmnichannelWebhook] Feed comment saved: {$platform} by {$senderName}");
    }

    /**
     * Process WhatsApp Business Cloud API incoming messages.
     *
     * @param array<string, mixed> $val
     */
    protected function processWhatsAppMessages(array $val, string $wabaId): void
    {
        $messages = $val['messages'] ?? [];
        $contacts = $val['contacts'] ?? [];
        $contactMap = [];
        foreach ($contacts as $c) {
            $waId = (string) ($c['wa_id'] ?? '');
            $name = (string) (data_get($c, 'profile.name') ?: $waId);
            if ($waId) {
                $contactMap[$waId] = $name;
            }
        }

        foreach ($messages as $msg) {
            $wamid = (string) ($msg['id'] ?? '');
            $fromNumber = (string) ($msg['from'] ?? '');
            $type = (string) ($msg['type'] ?? 'text');
            $timestamp = (int) ($msg['timestamp'] ?? time());

            $textBody = '';
            if ($type === 'text') {
                $textBody = (string) data_get($msg, 'text.body');
            } elseif ($type === 'button') {
                $textBody = (string) data_get($msg, 'button.text');
            } elseif ($type === 'interactive') {
                $textBody = (string) (data_get($msg, 'interactive.button_reply.title') ?: data_get($msg, 'interactive.list_reply.title') ?: '[Respon Interaktif]');
            } else {
                $textBody = "[Pesan Media: {$type}]";
            }

            if (empty($fromNumber) || empty($textBody)) {
                continue;
            }

            $senderName = $contactMap[$fromNumber] ?? "+{$fromNumber}";

            SocialMediaComment::updateOrCreate(
                [
                    'is_platform'         => true,
                    'platform'            => 'whatsapp',
                    'platform_comment_id' => $wamid ?: (string) Str::uuid(),
                ],
                [
                    'business_id'             => null,
                    'social_media_account_id' => null,
                    'social_media_post_id'    => null,
                    'platform_post_id'        => $wabaId,
                    'parent_comment_id'       => null,
                    'from_id'                 => $fromNumber,
                    'from_name'               => $senderName,
                    'message'                 => $textBody,
                    'is_from_page'            => false,
                    'status'                  => 'unread',
                    'created_time'            => date('Y-m-d H:i:s', $timestamp),
                ]
            );

            Log::info("[MetaOmnichannelWebhook] WhatsApp message saved from {$fromNumber}: {$textBody}");
        }
    }
}
