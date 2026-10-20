<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\SocialMedia\AdminSocialMediaService;
use App\Domain\SocialMedia\Clients\MetaSocialMediaClient;
use App\Http\Controllers\Controller;
use App\Models\SocialMediaAccount;
use App\Models\SocialMediaComment;
use App\Models\SystemSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminInboxController extends Controller
{
    public function __construct(
        protected AdminSocialMediaService $adminService,
        protected ?MetaSocialMediaClient $metaClient = null
    ) {
        $this->metaClient = $metaClient ?? app(MetaSocialMediaClient::class);
    }

    /**
     * Display Standalone Admin Omnichannel Inbox (Messenger, Instagram, WhatsApp, Comments).
     */
    public function index(Request $request): View
    {
        $status = (string) $request->query('status', 'all');
        $channel = (string) $request->query('channel', 'all');
        $search = (string) $request->query('search', '');

        // Official platform social accounts (Facebook Page, Instagram, WhatsApp, etc.)
        $platformAccounts = SocialMediaAccount::where('is_platform', true)
            ->where('status', 'active')
            ->get();

        // Platform official comments & customer messages
        $commentsQuery = SocialMediaComment::query()
            ->where('is_platform', true)
            ->with(['post', 'account'])
            ->latest('created_time');

        if (! empty($search)) {
            $commentsQuery->where(function ($q) use ($search) {
                $q->where('from_name', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        $allComments = $commentsQuery->take(300)->get();

        // Build structured threads
        $threads = $this->buildPlatformThreads($allComments);

        // Calculate counts per channel & status
        $counts = [
            'all'                => count($threads),
            'messenger'          => count(array_filter($threads, fn($t) => $t['channel'] === 'messenger')),
            'instagram'          => count(array_filter($threads, fn($t) => in_array($t['channel'], ['instagram', 'instagram_dm']))),
            'whatsapp'           => count(array_filter($threads, fn($t) => $t['channel'] === 'whatsapp')),
            'facebook_comments'  => count(array_filter($threads, fn($t) => in_array($t['channel'], ['facebook', 'facebook_comments']))),
            'instagram_comments' => count(array_filter($threads, fn($t) => $t['channel'] === 'instagram_comments')),
            'unread'             => count(array_filter($threads, fn($t) => $t['is_unread'])),
            'priority'           => count(array_filter($threads, fn($t) => $t['status'] === 'priority')),
            'ad_replies'         => count(array_filter($threads, fn($t) => $t['status'] === 'ad_replies')),
            'followup'           => count(array_filter($threads, fn($t) => $t['status'] === 'followup')),
        ];

        // Filter threads according to selected channel and status
        $filteredThreads = array_values(array_filter($threads, function ($t) use ($channel, $status) {
            $matchChannel = match ($channel) {
                'all' => true,
                'messenger' => $t['channel'] === 'messenger',
                'instagram' => in_array($t['channel'], ['instagram', 'instagram_dm']),
                'whatsapp' => $t['channel'] === 'whatsapp',
                'facebook_comments' => in_array($t['channel'], ['facebook', 'facebook_comments']),
                'instagram_comments' => $t['channel'] === 'instagram_comments',
                default => true,
            };

            $matchStatus = match ($status) {
                'all' => true,
                'unread' => $t['is_unread'] || $t['status'] === 'unread',
                'priority' => $t['status'] === 'priority',
                'ad_replies' => $t['status'] === 'ad_replies',
                'followup' => $t['status'] === 'followup',
                default => true,
            };

            return $matchChannel && $matchStatus;
        }));

        // Meta Omnichannel Credentials & Config
        $metaSettings = [
            'meta_page_id'                      => (string) (SystemSetting::get('meta_page_id') ?: SystemSetting::get('social_media_page_id', '')),
            'meta_system_user_token'            => (string) (SystemSetting::get('meta_system_user_token') ?: SystemSetting::get('social_media_page_token') ?: SystemSetting::get('instagram_access_token', '')),
            'meta_instagram_id'                 => (string) (SystemSetting::get('meta_instagram_id') ?: SystemSetting::get('instagram_account_id', '')),
            'whatsapp_phone_number_id'          => (string) (SystemSetting::get('whatsapp_phone_number_id', '')),
            'social_media_webhook_verify_token' => (string) (SystemSetting::get('social_media_webhook_verify_token', 'cooca_meta_social_webhook_token')),
            'webhook_url'                       => url('/api/v1/meta/omnichannel/webhook'),
        ];

        // WhatsApp platform gateway info
        $platformWaNumber = (string) SystemSetting::get('whatsapp_gateway_number', '6281234567890');
        $platformWaActive = ! empty($metaSettings['whatsapp_phone_number_id']) || ! empty(SystemSetting::get('whatsapp_gateway_token'));

        return view('admin.inbox.index', [
            'threads'          => $filteredThreads,
            'allThreads'       => $threads,
            'counts'           => $counts,
            'status'           => $status,
            'channel'          => $channel,
            'search'           => $search,
            'platformAccounts' => $platformAccounts,
            'platformWaNumber' => $platformWaNumber,
            'platformWaActive' => $platformWaActive,
            'metaSettings'     => $metaSettings,
        ]);
    }

    /**
     * Save Meta Omnichannel Settings & Webhook Credentials.
     */
    public function saveSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'meta_page_id'                      => ['nullable', 'string', 'max:100'],
            'meta_system_user_token'            => ['nullable', 'string'],
            'meta_instagram_id'                 => ['nullable', 'string', 'max:100'],
            'whatsapp_phone_number_id'          => ['nullable', 'string', 'max:100'],
            'social_media_webhook_verify_token' => ['nullable', 'string', 'max:100'],
        ]);

        foreach ($validated as $key => $val) {
            $isSecret = ($key === 'meta_system_user_token');
            SystemSetting::set($key, (string) $val, 'meta_omnichannel', $isSecret);
        }

        // Also sync to legacy social_media_page_id / token for seamless backward compatibility
        if (! empty($validated['meta_page_id'])) {
            SystemSetting::set('social_media_page_id', (string) $validated['meta_page_id'], 'social_media');
        }
        if (! empty($validated['meta_system_user_token'])) {
            SystemSetting::set('social_media_page_token', (string) $validated['meta_system_user_token'], 'social_media', true);
            SystemSetting::set('social_media_app_token', (string) $validated['meta_system_user_token'], 'social_media', true);
            SystemSetting::set('instagram_access_token', (string) $validated['meta_system_user_token'], 'social_media', true);
        }
        if (! empty($validated['meta_instagram_id'])) {
            SystemSetting::set('instagram_account_id', (string) $validated['meta_instagram_id'], 'social_media');
        }

        return back()->with('success', 'Pengaturan Kredensial Meta & Webhook berhasil disimpan!');
    }

    /**
     * AJAX/Post: Send official platform reply to a customer/comment thread.
     */
    public function reply(Request $request, string $id): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $admin = auth('admin')->user();
        if (! $admin) {
            abort(403);
        }

        $comment = SocialMediaComment::where('is_platform', true)->find($id);

        if (! $comment) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Percakapan tidak ditemukan.'], 404);
            }
            return back()->with('error', 'Percakapan tidak ditemukan.');
        }

        $token = (string) (
            SystemSetting::get('meta_system_user_token')
            ?: SystemSetting::get('social_media_page_token')
            ?: SystemSetting::get('instagram_access_token')
            ?: SystemSetting::get('social_media_app_token', '')
        );

        $pageId = (string) (
            SystemSetting::get('meta_page_id')
            ?: SystemSetting::get('social_media_page_id', '')
        );

        $phoneNumberId = (string) (
            SystemSetting::get('whatsapp_phone_number_id')
            ?: SystemSetting::get('whatsapp_gateway_account_id', '')
        );

        $replyId = (string) Str::uuid();
        $channel = strtolower((string) $comment->platform);

        try {
            if (! empty($token)) {
                // 1. Messenger / Instagram DM (Direct Messages)
                if (in_array($channel, ['messenger', 'instagram', 'instagram_dm'], true)) {
                    $targetRecipient = $comment->from_id ?: $comment->platform_comment_id;
                    $targetPath = ! empty($pageId) ? "{$pageId}/messages" : "me/messages";

                    $res = Http::timeout(12)->post("https://graph.facebook.com/v21.0/{$targetPath}", [
                        'recipient'    => ['id' => $targetRecipient],
                        'message'      => ['text' => $validated['message']],
                        'access_token' => $token,
                    ]);

                    if ($res->successful()) {
                        $replyId = (string) ($res->json('message_id') ?? $res->json('id') ?? $replyId);
                    } else {
                        Log::warning('[AdminInbox] Reply DM failed: ' . $res->body());
                    }
                }
                // 2. Facebook Comments
                elseif (in_array($channel, ['facebook', 'facebook_comments'], true)) {
                    $res = Http::timeout(12)->post("https://graph.facebook.com/v21.0/{$comment->platform_comment_id}/comments", [
                        'message'      => $validated['message'],
                        'access_token' => $token,
                    ]);

                    if ($res->successful()) {
                        $replyId = (string) ($res->json('id') ?? $replyId);
                    } else {
                        Log::warning('[AdminInbox] Reply FB comment failed: ' . $res->body());
                    }
                }
                // 3. Instagram Comments
                elseif (in_array($channel, ['instagram_comments'], true)) {
                    $res = Http::timeout(12)->post("https://graph.facebook.com/v21.0/{$comment->platform_comment_id}/replies", [
                        'message'      => $validated['message'],
                        'access_token' => $token,
                    ]);

                    if ($res->successful()) {
                        $replyId = (string) ($res->json('id') ?? $replyId);
                    } else {
                        Log::warning('[AdminInbox] Reply IG comment failed: ' . $res->body());
                    }
                }
                // 4. WhatsApp Cloud API
                elseif ($channel === 'whatsapp') {
                    $waTarget = $comment->from_id;
                    $targetPhoneId = $phoneNumberId ?: $comment->platform_post_id;

                    if ($targetPhoneId) {
                        $res = Http::timeout(12)->post("https://graph.facebook.com/v21.0/{$targetPhoneId}/messages", [
                            'messaging_product' => 'whatsapp',
                            'to'                => $waTarget,
                            'type'              => 'text',
                            'text'              => ['body' => $validated['message']],
                            'access_token'      => $token,
                        ]);

                        if ($res->successful()) {
                            $replyId = (string) (data_get($res->json(), 'messages.0.id') ?? $replyId);
                        } else {
                            Log::warning('[AdminInbox] Reply WA failed: ' . $res->body());
                        }
                    }
                }
            }

            // Save reply record into database
            $reply = SocialMediaComment::create([
                'business_id'             => null,
                'is_platform'             => true,
                'social_media_post_id'    => $comment->social_media_post_id,
                'platform'                => $comment->platform,
                'platform_comment_id'     => $replyId,
                'platform_post_id'        => $comment->platform_post_id,
                'parent_comment_id'       => $comment->platform_comment_id ?: $comment->id,
                'from_name'               => 'Cooca Indonesia (Admin)',
                'message'                 => $validated['message'],
                'is_from_page'            => true,
                'status'                  => 'replied',
                'created_time'            => now(),
            ]);

            $comment->update(['status' => 'replied']);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Balasan berhasil dikirimkan sebagai Cooca Official!',
                    'reply'   => [
                        'id'         => $reply->id,
                        'message'    => $reply->message,
                        'created_at' => now()->format('H:i'),
                        'sender'     => 'Cooca Indonesia',
                    ],
                ]);
            }

            return back()->with('success', 'Balasan berhasil dikirimkan sebagai Cooca Official!');
        } catch (\Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return back()->with('error', 'Gagal mengirim balasan: ' . $e->getMessage());
        }
    }

    /**
     * AJAX/Post: Toggle conversation status (unread, priority, ad_replies, followup, resolved).
     */
    public function toggleStatus(Request $request, string $id): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:unread,read,priority,ad_replies,followup,resolved'],
        ]);

        $comment = SocialMediaComment::where('is_platform', true)->find($id);
        if ($comment) {
            $comment->update(['status' => $validated['status']]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'status'  => $validated['status'],
                'message' => 'Status percakapan berhasil diperbarui.',
            ]);
        }

        return back()->with('success', 'Status percakapan diperbarui.');
    }

    /**
     * AJAX/Post: Trigger synchronization of Meta comments & messages from Graph API.
     */
    public function syncMeta(Request $request): JsonResponse|RedirectResponse
    {
        $pageId = (string) (SystemSetting::get('meta_page_id') ?: SystemSetting::get('social_media_page_id', ''));
        $token = (string) (
            SystemSetting::get('meta_system_user_token')
            ?: SystemSetting::get('social_media_page_token')
            ?: SystemSetting::get('instagram_access_token')
            ?: SystemSetting::get('social_media_app_token', '')
        );
        $igUserId = (string) (
            SystemSetting::get('meta_instagram_id')
            ?: SystemSetting::get('instagram_account_id')
            ?: SystemSetting::get('instagram_graph_user_id', '')
        );

        if (empty($token)) {
            $msg = 'Kredensial Meta (System User Token) belum diisi. Silakan klik tombol "Kelola" lalu pilih "Pengaturan Kredensial Meta & Webhook" untuk mengisi token.';
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg, 'count' => 0], 422);
            }
            return back()->with('error', $msg);
        }

        $newCount = 0;
        $errors = [];

        // 1. Sinkronkan Percakapan Messenger Halaman Facebook
        if (! empty($pageId)) {
            try {
                $conversations = $this->metaClient->getPageConversations($pageId, $token, 30);
                foreach ($conversations as $conv) {
                    $messages = data_get($conv, 'messages.data', []);
                    foreach ($messages as $msg) {
                        $fromId = (string) data_get($msg, 'from.id');
                        $fromName = (string) (data_get($msg, 'from.name') ?: 'Pengguna Messenger');
                        $msgText = (string) data_get($msg, 'message');
                        $msgId = (string) data_get($msg, 'id');
                        $createdTime = data_get($msg, 'created_time');

                        // Lewati jika pesan dari Page sendiri
                        if ($fromId === $pageId || empty($msgText)) {
                            continue;
                        }

                        $saved = SocialMediaComment::updateOrCreate(
                            [
                                'is_platform'         => true,
                                'platform'            => 'messenger',
                                'platform_comment_id' => $msgId,
                            ],
                            [
                                'business_id'             => null,
                                'social_media_account_id' => null,
                                'social_media_post_id'    => null,
                                'platform_post_id'        => $pageId,
                                'parent_comment_id'       => null,
                                'from_id'                 => $fromId,
                                'from_name'               => $fromName,
                                'message'                 => $msgText,
                                'is_from_page'            => false,
                                'status'                  => 'unread',
                                'created_time'            => $createdTime ? Carbon::parse($createdTime) : now(),
                            ]
                        );
                        if ($saved->wasRecentlyCreated) {
                            $newCount++;
                        }
                    }
                }
            } catch (\Throwable $e) {
                $errors[] = 'Messenger: ' . $e->getMessage();
            }

            // 2. Sinkronkan Komentar Facebook Page Feed
            try {
                $feedPosts = $this->metaClient->getPageFeedComments($pageId, $token, 25);
                foreach ($feedPosts as $post) {
                    $postId = (string) ($post['id'] ?? '');
                    $comments = data_get($post, 'comments.data', []);
                    foreach ($comments as $comment) {
                        $commId = (string) ($comment['id'] ?? '');
                        $commMsg = (string) ($comment['message'] ?? '');
                        $commFromId = (string) data_get($comment, 'from.id');
                        $commFromName = (string) (data_get($comment, 'from.name') ?: 'Pengguna Facebook');
                        $commTime = data_get($comment, 'created_time');

                        if ($commFromId === $pageId || empty($commMsg)) {
                            continue;
                        }

                        $saved = SocialMediaComment::updateOrCreate(
                            [
                                'is_platform'         => true,
                                'platform'            => 'facebook_comments',
                                'platform_comment_id' => $commId,
                            ],
                            [
                                'business_id'             => null,
                                'social_media_account_id' => null,
                                'social_media_post_id'    => null,
                                'platform_post_id'        => $postId ?: $pageId,
                                'parent_comment_id'       => null,
                                'from_id'                 => $commFromId,
                                'from_name'               => $commFromName,
                                'message'                 => $commMsg,
                                'is_from_page'            => false,
                                'status'                  => 'unread',
                                'created_time'            => $commTime ? Carbon::parse($commTime) : now(),
                            ]
                        );
                        if ($saved->wasRecentlyCreated) {
                            $newCount++;
                        }
                    }
                }
            } catch (\Throwable $e) {
                $errors[] = 'Facebook Comments: ' . $e->getMessage();
            }
        }

        // 3. Sinkronkan Instagram Direct Messages & Komentar Post Instagram
        if (! empty($igUserId) || ! empty($pageId)) {
            try {
                $igConversations = $this->metaClient->getInstagramConversations($igUserId ?: $pageId, $token, 25, $pageId);
                foreach ($igConversations as $conv) {
                    $messages = data_get($conv, 'messages.data', []);
                    foreach ($messages as $msg) {
                        $fromId = (string) data_get($msg, 'from.id');
                        $fromName = (string) (data_get($msg, 'from.username') ?: data_get($msg, 'from.name') ?: 'Pengguna Instagram');
                        $msgText = (string) data_get($msg, 'message');
                        $msgId = (string) data_get($msg, 'id');
                        $createdTime = data_get($msg, 'created_time');

                        if ($fromId === $igUserId || empty($msgText)) {
                            continue;
                        }

                        $saved = SocialMediaComment::updateOrCreate(
                            [
                                'is_platform'         => true,
                                'platform'            => 'instagram',
                                'platform_comment_id' => $msgId,
                            ],
                            [
                                'business_id'             => null,
                                'social_media_account_id' => null,
                                'social_media_post_id'    => null,
                                'platform_post_id'        => $igUserId ?: $pageId,
                                'parent_comment_id'       => null,
                                'from_id'                 => $fromId,
                                'from_name'               => $fromName,
                                'message'                 => $msgText,
                                'is_from_page'            => false,
                                'status'                  => 'unread',
                                'created_time'            => $createdTime ? Carbon::parse($createdTime) : now(),
                            ]
                        );
                        if ($saved->wasRecentlyCreated) {
                            $newCount++;
                        }
                    }
                }
            } catch (\Throwable $e) {
                $errors[] = 'Instagram DM: ' . $e->getMessage();
            }

            if (! empty($igUserId)) {
                try {
                    $mediaItems = $this->metaClient->getInstagramMediaComments($igUserId, $token, 20);
                    foreach ($mediaItems as $media) {
                        $mediaId = (string) ($media['id'] ?? '');
                        $comments = data_get($media, 'comments.data', []);
                        foreach ($comments as $comment) {
                            $commId = (string) ($comment['id'] ?? '');
                            $commMsg = (string) ($comment['text'] ?? $comment['message'] ?? '');
                            $commFromId = (string) data_get($comment, 'from.id');
                            $commFromName = (string) ($comment['username'] ?? data_get($comment, 'from.username') ?: 'Pengguna Instagram');
                            $commTime = data_get($comment, 'timestamp');

                            if ($commFromId === $igUserId || empty($commMsg)) {
                                continue;
                            }

                            $saved = SocialMediaComment::updateOrCreate(
                                [
                                    'is_platform'         => true,
                                    'platform'            => 'instagram_comments',
                                    'platform_comment_id' => $commId,
                                ],
                                [
                                    'business_id'             => null,
                                    'social_media_account_id' => null,
                                    'social_media_post_id'    => null,
                                    'platform_post_id'        => $mediaId,
                                    'parent_comment_id'       => null,
                                    'from_id'                 => $commFromId,
                                    'from_name'               => $commFromName,
                                    'message'                 => $commMsg,
                                    'is_from_page'            => false,
                                    'status'                  => 'unread',
                                    'created_time'            => $commTime ? Carbon::parse($commTime) : now(),
                                ]
                            );
                            if ($saved->wasRecentlyCreated) {
                                $newCount++;
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    $errors[] = 'Instagram Comments: ' . $e->getMessage();
                }
            }
        }

        $message = $newCount > 0
            ? "Berhasil menyinkronkan {$newCount} data pesan/komentar baru dari Meta Graph API."
            : "Sinkronisasi selesai. Tidak ada data pesan atau komentar baru dari Meta saat ini.";

        if (! empty($errors)) {
            Log::info('[AdminInbox] Sync Meta notices: ' . implode(' | ', $errors));
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'count'   => $newCount,
                'notices' => $errors,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Build unified thread items from comments/messages.
     *
     * @param  \Illuminate\Support\Collection<int, SocialMediaComment>  $comments
     * @return array<int, array<string, mixed>>
     */
    protected function buildPlatformThreads($comments): array
    {
        $threads = [];

        // Separate top-level comments and replies
        $topLevel = $comments->whereNull('parent_comment_id');
        $replies = $comments->whereNotNull('parent_comment_id');

        foreach ($topLevel as $c) {
            $rawPlatform = strtolower((string) $c->platform);
            $channel = match ($rawPlatform) {
                'facebook'           => 'facebook_comments',
                'facebook_comments'  => 'facebook_comments',
                'instagram'          => 'instagram',
                'instagram_dm'       => 'instagram',
                'instagram_comments' => 'instagram_comments',
                'whatsapp'           => 'whatsapp',
                'messenger'          => 'messenger',
                default              => $rawPlatform,
            };

            // Find child replies
            $threadReplies = $replies->filter(function ($r) use ($c) {
                return $r->parent_comment_id === $c->platform_comment_id
                    || $r->parent_comment_id === $c->id;
            })->map(function ($r) {
                return [
                    'id'           => $r->id,
                    'from_name'    => $r->from_name ?: ($r->is_from_page ? 'Cooca Official' : 'Pengguna'),
                    'message'      => $r->message,
                    'is_from_page' => (bool) $r->is_from_page,
                    'time'         => $r->created_time ? $r->created_time->format('H:i') : $r->created_at->format('H:i'),
                    'date'         => $r->created_time ? $r->created_time->format('d M Y') : $r->created_at->format('d M Y'),
                ];
            })->values()->all();

            $threads[] = [
                'id'           => $c->id,
                'customer_id'  => $c->from_id ?: $c->id,
                'name'         => $c->from_name ?: 'Pengguna Media Sosial',
                'channel'      => $channel,
                'channel_raw'  => $c->platform,
                'last_message' => $c->message,
                'status'       => $c->status ?: 'unread',
                'is_unread'    => $c->status === 'unread',
                'post_snippet' => $c->post?->content ? Str::limit($c->post->content, 45) : null,
                'time'         => $c->created_time ? $c->created_time->format('H:i') : $c->created_at->format('H:i'),
                'date'         => $c->created_time ? $c->created_time->format('d M Y') : $c->created_at->format('d M Y'),
                'messages'     => array_merge([
                    [
                        'id'           => $c->id,
                        'from_name'    => $c->from_name ?: 'Pengguna',
                        'message'      => $c->message,
                        'is_from_page' => false,
                        'time'         => $c->created_time ? $c->created_time->format('H:i') : $c->created_at->format('H:i'),
                        'date'         => $c->created_time ? $c->created_time->format('d M Y') : $c->created_at->format('d M Y'),
                    ]
                ], $threadReplies),
            ];
        }

        return $threads;
    }
}
