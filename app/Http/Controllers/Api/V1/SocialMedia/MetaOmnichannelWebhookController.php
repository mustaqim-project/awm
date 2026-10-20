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
        if (empty($payload)) {
            $raw = (string) $request->getContent();
            $payload = ! empty($raw) ? (json_decode($raw, true) ?: []) : [];
        }
        $object = (string) ($payload['object'] ?? '');

        Log::channel('daily')->info('[MetaOmnichannelWebhook] Raw event received', [
            'object'        => $object,
            'entries_count' => count($payload['entry'] ?? []),
            'path'          => $request->path(),
            'payload'       => $payload,
        ]);

        $entries = $payload['entry'] ?? [];

        foreach ($entries as $entry) {
            $entryId = (string) ($entry['id'] ?? '');

            // Auto-discover Page ID or Instagram ID if missing
            if ($object === 'page' && ! empty($entryId) && $entryId !== '0') {
                if (empty(SystemSetting::get('meta_page_id'))) {
                    SystemSetting::set('meta_page_id', $entryId, 'meta_omnichannel');
                    SystemSetting::set('social_media_page_id', $entryId, 'social_media');
                }
            } elseif ($object === 'instagram' && ! empty($entryId) && $entryId !== '0') {
                if (empty(SystemSetting::get('meta_instagram_id'))) {
                    SystemSetting::set('meta_instagram_id', $entryId, 'meta_omnichannel');
                    SystemSetting::set('instagram_account_id', $entryId, 'social_media');
                }
            }

            // 1. WhatsApp Business Account Events (object = 'whatsapp_business_account' or entry.changes field = 'messages')
            if ($object === 'whatsapp_business_account' || isset($entry['changes'])) {
                foreach ($entry['changes'] ?? [] as $change) {
                    $field = (string) ($change['field'] ?? '');
                    $val = $change['value'] ?? [];

                    // 1A. WhatsApp Messages & Delivery Statuses
                    if ($field === 'messages' && (isset($val['messages']) || isset($val['statuses']))) {
                        $this->processWhatsAppMessages($val, $entryId);
                        continue;
                    }

                    // 1B. Facebook & Instagram Comments, Mentions, Live Comments
                    if (in_array($field, ['feed', 'comments', 'live_comments', 'mentions', 'mention'], true)) {
                        $this->processFeedComments($val, $entryId, $object, $request);
                        continue;
                    }
                }
            }

            // 2. Direct Messages: Handle BOTH entry.messaging AND entry.standby (Handover secondary receiver)
            $messagingEvents = array_merge(
                is_array($entry['messaging'] ?? null) ? $entry['messaging'] : [],
                is_array($entry['standby'] ?? null) ? $entry['standby'] : []
            );

            if (! empty($messagingEvents)) {
                $this->processDirectMessaging($messagingEvents, $entryId, $object, $request);
            }
        }

        return response()->json(['status' => 'EVENT_RECEIVED'], 200);
    }

    /**
     * Process Messenger and Instagram Direct incoming messages.
     *
     * @param array<int, array<string, mixed>> $messagingEvents
     */
    protected function processDirectMessaging(array $messagingEvents, string $pageOrIgId, string $object = '', ?Request $request = null): void
    {
        $platformPageId = (string) (SystemSetting::get('meta_page_id') ?: SystemSetting::get('social_media_page_id', ''));
        $platformIgId = (string) (SystemSetting::get('meta_instagram_id') ?: SystemSetting::get('instagram_account_id', ''));
        $token = (string) (
            SystemSetting::get('meta_system_user_token')
            ?: SystemSetting::get('social_media_page_token')
            ?: SystemSetting::get('instagram_access_token', '')
        );

        $isFromSocialMediaEndpoint = $request && (
            str_contains($request->path(), 'social-media')
            || str_contains($request->path(), 'instagram')
        );

        foreach ($messagingEvents as $msgEvent) {
            $senderId = (string) data_get($msgEvent, 'sender.id');
            $recipientId = (string) data_get($msgEvent, 'recipient.id');
            $messageData = data_get($msgEvent, 'message', []);
            $mid = (string) data_get($messageData, 'mid');
            $text = (string) data_get($messageData, 'text');
            $timestamp = data_get($msgEvent, 'timestamp');
            $isEcho = (bool) data_get($messageData, 'is_echo', false);

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

            // Handle media attachments (photo, video, audio, file, share)
            $imageUrl = null;
            if (isset($messageData['attachments'])) {
                $att = data_get($messageData, 'attachments.0', []);
                $attType = (string) ($att['type'] ?? 'media');
                $imageUrl = (string) data_get($att, 'payload.url', '');
                if (empty($text)) {
                    $text = $attType === 'image' ? '[Foto]' : "[Lampiran Media: {$attType}]";
                }
            }

            if (empty($text) && empty($imageUrl)) {
                continue;
            }

            // Determine channel: Instagram DM or Facebook Messenger
            $isInstagram = (
                $object === 'instagram'
                || $isFromSocialMediaEndpoint
                || (! empty($platformIgId) && ($recipientId === $platformIgId || $senderId === $platformIgId || $pageOrIgId === $platformIgId))
                || ($object === 'page' && ! empty($recipientId) && ! empty($pageOrIgId) && $recipientId !== $pageOrIgId)
                || str_contains($mid, 'ig_')
                || str_starts_with($mid, 'aWdf')
                || str_contains($mid, 'a_')
            );
            $channel = $isInstagram ? 'instagram' : 'messenger';

            // Auto-discover & save Instagram account ID if discovered in Page webhook payload
            if ($isInstagram && empty($platformIgId) && ! empty($recipientId) && $recipientId !== $pageOrIgId && $recipientId !== '0') {
                SystemSetting::set('meta_instagram_id', $recipientId, 'meta_omnichannel');
                SystemSetting::set('instagram_account_id', $recipientId, 'social_media');
                $platformIgId = $recipientId;
            }

            // Determine if message is outbound from page/admin (echo) or inbound from customer
            $isFromPage = $isEcho;
            if ($isEcho) {
                // In echo event, sender is the page, recipient is the customer
                $customerId = $recipientId;
            } else {
                // In regular inbound message, sender is the customer
                $customerId = $senderId;
                if (! empty($platformPageId) && $senderId === $platformPageId) {
                    $isFromPage = true;
                    $customerId = $recipientId;
                } elseif (! empty($platformIgId) && $senderId === $platformIgId) {
                    $isFromPage = true;
                    $customerId = $recipientId;
                }
            }

            if (empty($customerId) || $customerId === '0' && empty($text)) {
                continue;
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

            // Resolve customer sender name via Meta Graph API or cache
            $senderName = $isFromPage ? 'Cooca Indonesia (Admin)' : $this->resolveUserName($customerId, $channel, $token);

            SocialMediaComment::updateOrCreate(
                [
                    'is_platform'         => true,
                    'platform'            => $channel,
                    'platform_comment_id' => Str::limit($mid ?: (string) Str::uuid(), 500, ''),
                ],
                [
                    'business_id'             => null,
                    'social_media_account_id' => null,
                    'social_media_post_id'    => null,
                    'platform_post_id'        => Str::limit((string) ($pageOrIgId ?: $recipientId), 500, ''),
                    'parent_comment_id'       => null,
                    'from_id'                 => Str::limit((string) $customerId, 500, ''),
                    'from_name'               => $senderName,
                    'message'                 => $text ?: '[Foto]',
                    'media_url'               => ! empty($imageUrl) ? $imageUrl : null,
                    'media_type'              => ! empty($imageUrl) ? 'image' : null,
                    'is_from_page'            => $isFromPage,
                    'status'                  => $isFromPage ? 'replied' : 'unread',
                    'created_time'            => $createdTime,
                ]
            );

            Log::channel('daily')->info("[MetaOmnichannelWebhook] Direct message saved: {$channel} (customer: {$customerId}, fromPage: " . ($isFromPage ? '1' : '0') . "): {$text}");
        }
    }

    /**
     * Resolve sender name using Meta Graph API with caching.
     */
    protected function resolveUserName(string $userId, string $channel, string $token): string
    {
        $cacheKey = "meta_user_name_{$channel}_{$userId}";
        $cached = cache()->get($cacheKey);
        if (! empty($cached)) {
            return (string) $cached;
        }

        if (! empty($token) && ! in_array($userId, ['0', '123456789'], true)) {
            try {
                $fields = $channel === 'instagram' ? 'name,username' : 'name,first_name,last_name';
                $res = \Illuminate\Support\Facades\Http::timeout(3)->get("https://graph.facebook.com/v21.0/{$userId}", [
                    'fields'       => $fields,
                    'access_token' => $token,
                ]);

                if ($res->successful()) {
                    $data = $res->json();
                    $resolved = (string) (
                        data_get($data, 'name')
                        ?: data_get($data, 'username')
                        ?: ((data_get($data, 'first_name') ? data_get($data, 'first_name') . ' ' . data_get($data, 'last_name') : ''))
                    );
                    if (! empty(trim($resolved))) {
                        cache()->put($cacheKey, trim($resolved), now()->addDays(7));
                        return trim($resolved);
                    }
                }
            } catch (\Throwable) {
                // Silently fallback on failure
            }
        }

        return $channel === 'instagram' ? "Pengguna Instagram ({$userId})" : "Pengguna Messenger ({$userId})";
    }

    /**
     * Process Facebook and Instagram comments from feed/comments webhook.
     *
     * @param array<string, mixed> $val
     */
    protected function processFeedComments(array $val, string $pageOrIgId, string $object = '', ?Request $request = null): void
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

        $isFromSocialMediaEndpoint = $request && (
            str_contains($request->path(), 'social-media')
            || str_contains($request->path(), 'instagram')
        );

        // Determine if FB or IG comment
        $platformPageId = (string) (SystemSetting::get('meta_page_id') ?: SystemSetting::get('social_media_page_id', ''));
        $platformIgId = (string) (SystemSetting::get('meta_instagram_id') ?: SystemSetting::get('instagram_account_id', ''));
        $platform = 'facebook_comments';
        if ($object === 'instagram' || $isFromSocialMediaEndpoint || str_contains($commentId, 'ig_') || isset($val['media']) || isset($val['media']['id'])) {
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

        $isFromCooca = ($senderId === $platformPageId)
            || (! empty($platformIgId) && $senderId === $platformIgId)
            || str_contains(strtolower($senderName), 'cooca')
            || str_contains(strtolower($senderName), 'admin');

        SocialMediaComment::updateOrCreate(
            [
                'is_platform'         => true,
                'platform'            => $platform,
                'platform_comment_id' => Str::limit($commentId ?: (string) Str::uuid(), 500, ''),
            ],
            [
                'business_id'             => null,
                'social_media_account_id' => null,
                'social_media_post_id'    => null,
                'platform_post_id'        => Str::limit((string) ($postId ?: $pageOrIgId), 500, ''),
                'parent_comment_id'       => ($parentId && $parentId !== $postId) ? Str::limit((string) $parentId, 500, '') : null,
                'from_id'                 => Str::limit((string) $senderId, 500, ''),
                'from_name'               => $senderName,
                'message'                 => $message,
                'is_from_page'            => $isFromCooca,
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
        $statuses = $val['statuses'] ?? [];

        $phoneNumberId = (string) (data_get($val, 'metadata.phone_number_id') ?? $wabaId);

        // Auto-save WhatsApp Phone Number ID into SystemSetting if not yet saved
        if (! empty($phoneNumberId) && $phoneNumberId !== '0') {
            if (empty(SystemSetting::get('whatsapp_phone_number_id'))) {
                SystemSetting::set('whatsapp_phone_number_id', $phoneNumberId, 'meta_omnichannel');
            }
        }

        // Handle Delivery Statuses (sent, delivered, read)
        foreach ($statuses as $st) {
            $wamid = (string) ($st['id'] ?? '');
            $newStatus = (string) ($st['status'] ?? '');
            if ($wamid && in_array($newStatus, ['sent', 'delivered', 'read'], true)) {
                SocialMediaComment::where('is_platform', true)
                    ->where('platform_comment_id', $wamid)
                    ->update(['status' => $newStatus]);
            }
        }

        $contactMap = [];
        foreach ($contacts as $c) {
            $waId = (string) ($c['wa_id'] ?? '');
            $name = (string) (data_get($c, 'profile.name') ?: $waId);
            if ($waId) {
                $contactMap[$waId] = $name;
            }
        }

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
            $mediaUrl = null;
            $mediaType = null;

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
                $mediaUrl = (string) (data_get($msg, "{$type}.link") ?: data_get($msg, "{$type}.url") ?: '');
                $mediaType = $type;
                $textBody = $caption ?: ($type === 'image' ? '[Foto]' : "[Pesan Media: {$type}]");
            } elseif ($type === 'location') {
                $textBody = '[Lokasi: ' . data_get($msg, 'location.name', 'Peta Lokasi') . ']';
            } elseif ($type === 'contacts') {
                $textBody = '[Kontak Dibagikan]';
            } else {
                $textBody = (string) (data_get($msg, 'text.body') ?: "[Pesan: {$type}]");
            }

            if (empty($fromNumber) || (empty($textBody) && empty($mediaUrl))) {
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
                    'platform_comment_id' => Str::limit($wamid ?: (string) Str::uuid(), 500, ''),
                ],
                [
                    'business_id'             => null,
                    'social_media_account_id' => null,
                    'social_media_post_id'    => null,
                    'platform_post_id'        => Str::limit((string) ($phoneNumberId ?: $wabaId), 500, ''),
                    'parent_comment_id'       => null,
                    'from_id'                 => Str::limit((string) $fromNumber, 500, ''),
                    'from_name'               => $senderName,
                    'message'                 => $textBody ?: '[Foto]',
                    'media_url'               => ! empty($mediaUrl) ? $mediaUrl : null,
                    'media_type'              => $mediaType,
                    'is_from_page'            => false,
                    'status'                  => 'unread',
                    'created_time'            => $createdTime,
                ]
            );

            Log::channel('daily')->info("[MetaOmnichannelWebhook] WhatsApp message saved from {$fromNumber}: {$textBody}");
        }
    }
}
