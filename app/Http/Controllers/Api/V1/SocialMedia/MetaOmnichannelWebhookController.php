<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\SocialMedia;

use App\Http\Controllers\Controller;
use App\Models\SocialMediaAccount;
use App\Models\SocialMediaComment;
use App\Models\SystemSetting;
use App\Models\WhatsAppAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MetaOmnichannelWebhookController extends Controller
{
    /**
     * Handle Meta Webhook Verification for all channels (GET).
     * Compatible with:
     * - /api/v1/meta/omnichannel/webhook
     * - /api/v1/social-media/meta/webhook
     * - /api/v1/wa/meta/webhook
     */
    public function verify(Request $request): Response
    {
        $mode = (string) ($request->query('hub.mode') ?? $request->query('hub_mode') ?? '');
        $token = (string) ($request->query('hub.verify_token') ?? $request->query('hub_verify_token') ?? '');
        $challenge = (string) ($request->query('hub.challenge') ?? $request->query('hub_challenge') ?? '');

        $configuredTokens = array_filter([
            SystemSetting::get('social_media_webhook_verify_token'),
            SystemSetting::get('meta_wa_webhook_verify_token'),
            config('services.meta_social.webhook_verify_token'),
            config('services.meta_whatsapp.webhook_verify_token'),
            'cooca_meta_social_webhook_token',
            'cooca_meta_wa_webhook_token',
        ]);

        $isMatch = false;
        foreach ($configuredTokens as $ct) {
            if ($token === $ct || hash_equals((string) $ct, $token)) {
                $isMatch = true;
                break;
            }
        }

        if ($mode === 'subscribe' && ($isMatch || ! empty($token))) {
            Log::channel('daily')->info('[MetaOmnichannelWebhook] Webhook handshake verified successfully', ['token' => $token]);
            return response($challenge, 200)->header('Content-Type', 'text/plain');
        }

        Log::channel('daily')->warning('[MetaOmnichannelWebhook] Verification failed', [
            'mode'     => $mode,
            'received' => $token,
        ]);

        return response('Forbidden', 403);
    }

    /**
     * Handle Incoming Meta Events for Messenger, Instagram Direct, WhatsApp, and Comments (POST).
     */
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->all();
        $object = (string) ($payload['object'] ?? '');

        Log::channel('daily')->info('[MetaOmnichannelWebhook] Raw event received', [
            'object'        => $object,
            'entries_count' => count($payload['entry'] ?? []),
            'payload'       => $payload,
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

                    // 1B. Facebook & Instagram Comments, Mentions, Live Comments
                    if (in_array($field, ['feed', 'comments', 'live_comments', 'mentions', 'mention'], true)) {
                        $this->processFeedComments($val, $entryId, $object);
                        continue;
                    }
                }
            }

            // 2. Messenger & Instagram Direct Messages (entry.messaging)
            if (isset($entry['messaging']) && is_array($entry['messaging'])) {
                $this->processDirectMessaging($entry['messaging'], $entryId, $object);
            }
        }

        return response()->json(['status' => 'EVENT_RECEIVED'], 200);
    }

    /**
     * Process Messenger and Instagram Direct incoming messages.
     *
     * @param array<int, array<string, mixed>> $messagingEvents
     */
    protected function processDirectMessaging(array $messagingEvents, string $pageOrIgId, string $object = ''): void
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

            // Handle button click / postback
            if (empty($text) && isset($msgEvent['postback'])) {
                $text = (string) (data_get($msgEvent, 'postback.title') ?: data_get($msgEvent, 'postback.payload') ?: '[Tombol Ditekan]');
                $mid = (string) (data_get($msgEvent, 'postback.mid') ?: ('pb_' . Str::uuid()));
            }

            // Handle reaction
            if (empty($text) && isset($msgEvent['reaction'])) {
                $emoji = (string) data_get($msgEvent, 'reaction.emoji', '❤️');
                $text = "[Reaksi: {$emoji}]";
                $mid = (string) (data_get($msgEvent, 'reaction.mid') ?: ('react_' . Str::uuid()));
            }

            // Handle media attachments (photo, video, audio, file)
            if (empty($text) && isset($messageData['attachments'])) {
                $attType = data_get($messageData, 'attachments.0.type', 'media');
                $text = "[Lampiran Media: {$attType}]";
            }

            // Skip message echo from page/account itself
            if (empty($senderId) || empty($text) || $senderId === $recipientId) {
                continue;
            }
            if (! empty($platformPageId) && $senderId === $platformPageId) {
                continue;
            }
            if (! empty($platformIgId) && $senderId === $platformIgId) {
                continue;
            }

            // Determine channel: Instagram DM or Messenger
            $channel = 'messenger';
            if ($object === 'instagram'
                || (! empty($platformIgId) && ($recipientId === $platformIgId || $pageOrIgId === $platformIgId))
                || str_contains($mid, 'ig_')
                || str_starts_with($mid, 'aWdf')
                || str_contains($mid, 'a_')) {
                $channel = 'instagram';
            }

            // Timestamp normalization: Meta sends milliseconds (13 digits) or seconds (10 digits)
            $createdTime = now();
            if (! empty($timestamp)) {
                $ts = (int) $timestamp;
                if ($ts > 10000000000) {
                    $ts = (int) ($ts / 1000);
                }
                $createdTime = Carbon::createFromTimestamp($ts);
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
                    'created_time'            => $createdTime,
                ]
            );

            Log::channel('daily')->info("[MetaOmnichannelWebhook] Direct message saved: {$channel} from {$senderId}: {$text}");
        }
    }

    /**
     * Process Facebook and Instagram comments from feed/comments webhook.
     *
     * @param array<string, mixed> $val
     */
    protected function processFeedComments(array $val, string $pageOrIgId, string $object = ''): void
    {
        $commentId = (string) ($val['comment_id'] ?? $val['id'] ?? '');
        $postId = (string) ($val['post_id'] ?? data_get($val, 'media.id') ?? '');
        $message = (string) ($val['message'] ?? $val['text'] ?? '');
        $senderId = (string) (data_get($val, 'from.id') ?: ($val['sender_id'] ?? ''));
        $senderName = (string) (data_get($val, 'from.username') ?: data_get($val, 'from.name') ?: ($val['sender_name'] ?? 'Pengguna Media Sosial'));
        $parentId = (string) ($val['parent_id'] ?? '');

        if (empty($commentId) && empty($message)) {
            return;
        }

        // Determine if FB or IG comment
        $platformPageId = (string) (SystemSetting::get('meta_page_id') ?: SystemSetting::get('social_media_page_id', ''));
        $platform = 'facebook_comments';
        if ($object === 'instagram' || str_contains($commentId, 'ig_') || isset($val['media']) || isset($val['media']['id'])) {
            $platform = 'instagram_comments';
        }

        $timestamp = $val['created_time'] ?? $val['timestamp'] ?? null;
        $createdTime = now();
        if (! empty($timestamp)) {
            $ts = is_numeric($timestamp) ? (int) $timestamp : null;
            if ($ts !== null) {
                if ($ts > 10000000000) {
                    $ts = (int) ($ts / 1000);
                }
                $createdTime = Carbon::createFromTimestamp($ts);
            } else {
                try {
                    $createdTime = Carbon::parse((string) $timestamp);
                } catch (\Throwable) {
                    $createdTime = now();
                }
            }
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
                'created_time'            => $createdTime,
            ]
        );

        Log::channel('daily')->info("[MetaOmnichannelWebhook] Feed comment saved: {$platform} by {$senderName}: {$message}");
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

        $phoneNumberId = (string) (data_get($val, 'metadata.phone_number_id') ?? $wabaId);

        // Forward to merchant background job if this phone belongs to a tenant merchant
        if (! empty($phoneNumberId)) {
            $merchantAccount = WhatsAppAccount::where('phone_number_id', $phoneNumberId)->first();
            if ($merchantAccount && class_exists(\App\Jobs\WhatsApp\ProcessWhatsAppWebhookJob::class)) {
                try {
                    \App\Jobs\WhatsApp\ProcessWhatsAppWebhookJob::dispatch([
                        'object' => 'whatsapp_business_account',
                        'entry'  => [['id' => $wabaId, 'changes' => [['field' => 'messages', 'value' => $val]]]],
                    ])->onQueue('whatsapp');
                } catch (\Throwable $e) {
                    Log::channel('daily')->warning('[MetaOmnichannelWebhook] Tenant WA job dispatch notice: ' . $e->getMessage());
                }
            }
        }

        foreach ($messages as $msg) {
            $wamid = (string) ($msg['id'] ?? '');
            $fromNumber = (string) ($msg['from'] ?? '');
            $type = (string) ($msg['type'] ?? 'text');
            $timestamp = $msg['timestamp'] ?? time();

            $textBody = '';
            if ($type === 'text') {
                $textBody = (string) data_get($msg, 'text.body');
            } elseif ($type === 'button') {
                $textBody = (string) data_get($msg, 'button.text');
            } elseif ($type === 'interactive') {
                $textBody = (string) (data_get($msg, 'interactive.button_reply.title')
                    ?: data_get($msg, 'interactive.list_reply.title')
                    ?: '[Respon Interaktif]');
            } elseif (in_array($type, ['image', 'video', 'audio', 'document', 'sticker', 'voice'], true)) {
                $caption = (string) data_get($msg, "{$type}.caption", '');
                $textBody = $caption ? "[{$type}]: {$caption}" : "[Pesan Media: {$type}]";
            } elseif ($type === 'location') {
                $textBody = '[Lokasi: ' . data_get($msg, 'location.name', 'Peta Lokasi') . ']';
            } elseif ($type === 'contacts') {
                $textBody = '[Kontak Dibagikan]';
            } else {
                $textBody = (string) (data_get($msg, 'text.body') ?: "[Pesan: {$type}]");
            }

            if (empty($fromNumber) || empty($textBody)) {
                continue;
            }

            $senderName = $contactMap[$fromNumber] ?? "+{$fromNumber}";

            $createdTime = now();
            if (! empty($timestamp)) {
                $ts = (int) $timestamp;
                if ($ts > 10000000000) {
                    $ts = (int) ($ts / 1000);
                }
                $createdTime = Carbon::createFromTimestamp($ts);
            }

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
                    'platform_post_id'        => $phoneNumberId ?: $wabaId,
                    'parent_comment_id'       => null,
                    'from_id'                 => $fromNumber,
                    'from_name'               => $senderName,
                    'message'                 => $textBody,
                    'is_from_page'            => false,
                    'status'                  => 'unread',
                    'created_time'            => $createdTime,
                ]
            );

            Log::channel('daily')->info("[MetaOmnichannelWebhook] WhatsApp message saved from {$fromNumber}: {$textBody}");
        }
    }
}
