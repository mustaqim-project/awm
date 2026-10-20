<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\SocialMedia\AdminSocialMediaService;
use App\Domain\SocialMedia\Clients\MetaSocialMediaClient;
use App\Http\Controllers\Controller;
use App\Models\SocialMediaAccount;
use App\Models\SocialMediaComment;
use App\Models\SocialMediaPost;
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

        // Ensure initial baseline demo threads exist across all 5 channels
        $this->ensureInitialPlatformData();

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
     * AJAX/GET: Real-time background polling endpoint for Inbox messages and unread counts.
     */
    public function poll(Request $request): JsonResponse
    {
        $status = (string) $request->query('status', 'all');
        $channel = (string) $request->query('channel', 'all');
        $search = (string) $request->query('search', '');

        $this->ensureInitialPlatformData();

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
        $threads = $this->buildPlatformThreads($allComments);

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

        return response()->json([
            'success' => true,
            'threads' => $threads,
            'counts'  => $counts,
        ]);
    }

    /**
     * AJAX/GET: Retrieve direct chat history with a specific customer for the DM modal.
     */
    public function customerHistory(Request $request): JsonResponse
    {
        $customerId = (string) $request->query('customer_id', '');
        $customerName = (string) $request->query('customer_name', '');
        $channel = (string) $request->query('channel', 'instagram');

        $targetChannel = match ($channel) {
            'facebook', 'facebook_comments', 'messenger' => 'messenger',
            'whatsapp' => 'whatsapp',
            default => 'instagram',
        };

        $platformPageId = (string) (SystemSetting::get('meta_page_id') ?: SystemSetting::get('social_media_page_id', ''));
        $platformIgId = (string) (SystemSetting::get('meta_instagram_id') ?: SystemSetting::get('instagram_account_id', ''));

        $query = SocialMediaComment::query()
            ->where('is_platform', true)
            ->whereIn('platform', [$targetChannel, $targetChannel . '_dm']);

        $query->where(function ($q) use ($customerId, $customerName) {
            $hasCondition = false;
            if (! empty($customerId)) {
                $q->where('from_id', $customerId);
                $hasCondition = true;
            }
            if (! empty($customerName)) {
                if ($hasCondition) {
                    $q->orWhere('from_name', $customerName);
                } else {
                    $q->where('from_name', $customerName);
                    $hasCondition = true;
                }
            }
            // Also match if message has customer reference
            if (! empty($customerId)) {
                $q->orWhere('parent_comment_id', 'like', "%{$customerId}%");
            }
        });

        $messages = $query->orderBy('created_time', 'asc')
            ->orderBy('created_at', 'asc')
            ->take(100)
            ->get();

        $formatted = $messages->map(function ($m) use ($customerName, $platformPageId, $platformIgId) {
            $isFromPage = (bool) $m->is_from_page
                || str_contains(strtolower((string) $m->from_name), 'cooca')
                || in_array((string) $m->from_id, array_filter([$platformPageId, $platformIgId]), true);

            return [
                'id'           => $m->id,
                'comment_id'   => $m->platform_comment_id,
                'from_id'      => $m->from_id,
                'from_name'    => $isFromPage ? 'Cooca Indonesia' : ($m->from_name ?: $customerName),
                'message'      => $m->message,
                'media_url'    => $m->media_url,
                'media_type'   => $m->media_type,
                'is_from_page' => $isFromPage,
                'is_cooca'     => $isFromPage,
                'time'         => $m->created_time ? $m->created_time->format('H.i') : $m->created_at->format('H.i'),
                'time_relative'=> $m->created_time ? $m->created_time->diffForHumans(null, true) : 'Baru saja',
                'date'         => $m->created_time ? $m->created_time->format('d M Y') : $m->created_at->format('d M Y'),
            ];
        });

        return response()->json([
            'success'       => true,
            'messages'      => $formatted,
            'count'         => $formatted->count(),
            'channel'       => $targetChannel,
            'customer_name' => $customerName,
            'customer_id'   => $customerId,
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
     * AJAX/Post: Upload media attachment for DM, Messenger, or WhatsApp.
     */
    public function uploadMedia(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'image' => ['required', 'file', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
        ]);

        $file = $request->file('image');
        $filename = 'inbox_' . Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('inbox_media', $filename, 'public');
        $publicUrl = asset('storage/' . $path);

        return response()->json([
            'success'  => true,
            'url'      => $publicUrl,
            'filename' => $filename,
        ]);
    }

    /**
     * AJAX/Post: Send official platform reply to a customer/comment thread.
     * Supports text, image attachments (WhatsApp Cloud API & Meta Graph API DM), or both.
     */
    public function reply(Request $request, string $id): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'message'   => ['nullable', 'string', 'max:2000'],
            'image_url' => ['nullable', 'string', 'max:1000'],
        ]);

        $messageText = trim((string) ($validated['message'] ?? ''));
        $imageUrl = ! empty($validated['image_url']) ? trim((string) $validated['image_url']) : null;

        if (empty($messageText) && empty($imageUrl)) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Pesan atau lampiran gambar harus diisi.'], 422);
            }
            return back()->with('error', 'Pesan atau lampiran gambar harus diisi.');
        }

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

                    // A. Send image attachment if attached
                    if ($imageUrl) {
                        $resImg = Http::timeout(12)->post("https://graph.facebook.com/v21.0/{$targetPath}", [
                            'recipient'    => ['id' => $targetRecipient],
                            'message'      => [
                                'attachment' => [
                                    'type'    => 'image',
                                    'payload' => [
                                        'url'         => $imageUrl,
                                        'is_reusable' => true,
                                    ],
                                ],
                            ],
                            'access_token' => $token,
                        ]);

                        if ($resImg->successful()) {
                            $replyId = (string) ($resImg->json('message_id') ?? $resImg->json('id') ?? $replyId);
                        } else {
                            Log::warning('[AdminInbox] Reply DM Image failed: ' . $resImg->body());
                        }
                    }

                    // B. Send text message if text body is provided
                    if (! empty($messageText)) {
                        $resText = Http::timeout(12)->post("https://graph.facebook.com/v21.0/{$targetPath}", [
                            'recipient'    => ['id' => $targetRecipient],
                            'message'      => ['text' => $messageText],
                            'access_token' => $token,
                        ]);

                        if ($resText->successful() && ! $imageUrl) {
                            $replyId = (string) ($resText->json('message_id') ?? $resText->json('id') ?? $replyId);
                        }
                    }
                }
                // 2. Facebook Comments
                elseif (in_array($channel, ['facebook', 'facebook_comments'], true)) {
                    $fbCommentText = $messageText ?: ($imageUrl ? "Lampiran: {$imageUrl}" : '');
                    $res = Http::timeout(12)->post("https://graph.facebook.com/v21.0/{$comment->platform_comment_id}/comments", [
                        'message'      => $fbCommentText,
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
                    $igCommentText = $messageText ?: ($imageUrl ? "Lampiran: {$imageUrl}" : '');
                    $res = Http::timeout(12)->post("https://graph.facebook.com/v21.0/{$comment->platform_comment_id}/replies", [
                        'message'      => $igCommentText,
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
                        if ($imageUrl) {
                            // WhatsApp Image message with optional caption
                            $payload = [
                                'messaging_product' => 'whatsapp',
                                'to'                => $waTarget,
                                'type'              => 'image',
                                'image'             => [
                                    'link'    => $imageUrl,
                                    'caption' => ! empty($messageText) ? $messageText : null,
                                ],
                                'access_token'      => $token,
                            ];
                        } else {
                            // WhatsApp Text message
                            $payload = [
                                'messaging_product' => 'whatsapp',
                                'to'                => $waTarget,
                                'type'              => 'text',
                                'text'              => ['body' => $messageText],
                                'access_token'      => $token,
                            ];
                        }

                        $res = Http::timeout(12)->post("https://graph.facebook.com/v21.0/{$targetPhoneId}/messages", $payload);

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
                'from_id'                 => $comment->from_id ?: $comment->platform_comment_id,
                'from_name'               => 'Cooca Indonesia (Admin)',
                'message'                 => $messageText ?: ($imageUrl ? '[Foto]' : ''),
                'media_url'               => $imageUrl,
                'media_type'              => $imageUrl ? 'image' : null,
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
                        'media_url'  => $reply->media_url,
                        'media_type' => $reply->media_type,
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
     * AJAX/Post: Send official Private Reply (Direct Message) to a customer from a comment.
     * Uses Meta Graph API Private Reply and provisions the target DM thread.
     */
    public function sendPrivateDm(Request $request, string $id): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'message'   => ['nullable', 'string', 'max:2000'],
            'image_url' => ['nullable', 'string', 'max:1000'],
        ]);

        $messageText = trim((string) ($validated['message'] ?? ''));
        $imageUrl = ! empty($validated['image_url']) ? trim((string) $validated['image_url']) : null;

        if (empty($messageText) && empty($imageUrl)) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Pesan atau lampiran gambar harus diisi.'], 422);
            }
            return back()->with('error', 'Pesan atau lampiran gambar harus diisi.');
        }

        $admin = auth('admin')->user();
        if (! $admin) {
            abort(403);
        }

        $comment = SocialMediaComment::where('is_platform', true)->find($id);

        if (! $comment) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Komentar tidak ditemukan.'], 404);
            }
            return back()->with('error', 'Komentar tidak ditemukan.');
        }

        $rawPlatform = strtolower((string) $comment->platform);
        $targetDmChannel = in_array($rawPlatform, ['facebook', 'facebook_comments'], true) ? 'messenger' : 'instagram';

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

        $replyId = (string) Str::uuid();

        // 1. Send via Meta Graph API Private Reply
        try {
            if (! empty($token)) {
                $targetPath = ! empty($pageId) ? "{$pageId}/messages" : "me/messages";

                if ($imageUrl) {
                    Http::timeout(12)->post("https://graph.facebook.com/v21.0/{$targetPath}", [
                        'recipient'    => ['comment_id' => $comment->platform_comment_id],
                        'message'      => [
                            'attachment' => [
                                'type'    => 'image',
                                'payload' => [
                                    'url'         => $imageUrl,
                                    'is_reusable' => true,
                                ],
                            ],
                        ],
                        'access_token' => $token,
                    ]);
                }

                if (! empty($messageText)) {
                    $res = Http::timeout(12)->post("https://graph.facebook.com/v21.0/{$targetPath}", [
                        'recipient'    => ['comment_id' => $comment->platform_comment_id],
                        'message'      => ['text' => $messageText],
                        'access_token' => $token,
                    ]);

                    if ($res->successful()) {
                        $replyId = (string) ($res->json('message_id') ?? $res->json('id') ?? $replyId);
                    } else {
                        Log::warning('[AdminInbox] Private Reply to comment failed: ' . $res->body());
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('[AdminInbox] Private Reply network exception: ' . $e->getMessage());
        }

        $customerId = $comment->from_id ?: 'user_' . Str::slug($comment->from_name);

        // 2. Ensure an initial thread exists in database for this customer in DM
        $customerDmThread = SocialMediaComment::where('is_platform', true)
            ->where('platform', $targetDmChannel)
            ->where('from_id', $customerId)
            ->whereNull('parent_comment_id')
            ->first();

        if (! $customerDmThread) {
            $customerDmThread = SocialMediaComment::create([
                'business_id'             => null,
                'is_platform'             => true,
                'social_media_account_id' => $comment->social_media_account_id,
                'platform'                => $targetDmChannel,
                'platform_comment_id'     => 'dm_ref_' . ($comment->platform_comment_id ?: Str::uuid()),
                'platform_post_id'        => $comment->platform_post_id,
                'parent_comment_id'       => null,
                'from_id'                 => $customerId,
                'from_name'               => $comment->from_name ?: 'Pengguna Media Sosial',
                'message'                 => "[Komentar: " . Str::limit($comment->message, 50) . "]",
                'is_from_page'            => false,
                'status'                  => 'read',
                'created_time'            => $comment->created_time ?? now()->subMinute(),
            ]);
        }

        // 3. Save the outbound private direct message
        $dmReply = SocialMediaComment::create([
            'business_id'             => null,
            'is_platform'             => true,
            'social_media_account_id' => $comment->social_media_account_id,
            'platform'                => $targetDmChannel,
            'platform_comment_id'     => $replyId,
            'platform_post_id'        => $comment->platform_post_id,
            'parent_comment_id'       => $customerDmThread->platform_comment_id ?: $customerDmThread->id,
            'from_id'                 => $customerId,
            'from_name'               => 'Cooca Indonesia (Admin)',
            'message'                 => $messageText ?: ($imageUrl ? '[Foto]' : ''),
            'media_url'               => $imageUrl,
            'media_type'              => $imageUrl ? 'image' : null,
            'is_from_page'            => true,
            'status'                  => 'replied',
            'created_time'            => now(),
        ]);

        $comment->update(['status' => 'replied']);

        if ($request->wantsJson()) {
            return response()->json([
                'success'          => true,
                'message'          => 'Pesan DM berhasil dikirim via Meta Private Reply!',
                'redirect_channel' => $targetDmChannel,
                'customer_id'      => $customerId,
                'customer_name'    => $comment->from_name,
                'thread_id'        => $customerDmThread->id,
                'reply'            => [
                    'id'         => $dmReply->id,
                    'message'    => $dmReply->message,
                    'media_url'  => $dmReply->media_url,
                    'media_type' => $dmReply->media_type,
                    'created_at' => now()->format('H:i'),
                    'sender'     => 'Cooca Indonesia',
                ],
            ]);
        }

        return redirect()->route('admin.inbox.index', ['channel' => $targetDmChannel])
            ->with('success', 'Pesan DM berhasil dikirim via Meta Private Reply!');
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
     * Direct Chat channels (Messenger, Instagram, WhatsApp) are grouped by Customer Conversation.
     * Comment channels (FB comments, IG comments) are grouped by Post Comment + Replies.
     *
     * @param  \Illuminate\Support\Collection<int, SocialMediaComment>  $comments
     * @return array<int, array<string, mixed>>
     */
    protected function buildPlatformThreads($comments): array
    {
        $threads = [];

        // Preload posts by platform_post_id or id
        $postIds = $comments->pluck('platform_post_id')->filter()->unique();
        $postsMap = SocialMediaPost::whereIn('platform_post_id', $postIds)
            ->orWhereIn('id', $comments->pluck('social_media_post_id')->filter()->unique())
            ->get()
            ->keyBy('platform_post_id');

        // Separate comment channels from direct chat channels
        $commentItems = $comments->filter(function ($c) {
            $p = strtolower((string) $c->platform);
            return in_array($p, ['facebook_comments', 'instagram_comments', 'facebook'], true);
        });

        $directChatItems = $comments->filter(function ($c) {
            $p = strtolower((string) $c->platform);
            return in_array($p, ['messenger', 'instagram', 'instagram_dm', 'whatsapp'], true);
        });

        // Preload Meta IDs for accurate Cooca Official attribution
        $platformPageId = (string) (SystemSetting::get('meta_page_id') ?: SystemSetting::get('social_media_page_id', ''));
        $platformIgId = (string) (SystemSetting::get('meta_instagram_id') ?: SystemSetting::get('instagram_account_id', ''));

        // =========================================================================
        // A. BUILD COMMENT THREADS (Grouped by Post Comment + Child Replies)
        // =========================================================================
        $topLevelComments = $commentItems->whereNull('parent_comment_id');
        $repliesComments = $commentItems->whereNotNull('parent_comment_id');

        foreach ($topLevelComments as $c) {
            $rawPlatform = strtolower((string) $c->platform);
            $channel = ($rawPlatform === 'facebook' || $rawPlatform === 'facebook_comments') ? 'facebook_comments' : 'instagram_comments';

            // Related post resolution
            $post = $c->post ?? $postsMap->get($c->platform_post_id);
            $postMediaUrl = null;
            if ($post) {
                $mediaUrls = (array) ($post->media_urls ?? []);
                $localPaths = (array) ($post->local_media_paths ?? []);
                $rawUrl = $mediaUrls[0] ?? $localPaths[0] ?? null;
                if ($rawUrl) {
                    $postMediaUrl = str_starts_with($rawUrl, 'http') ? $rawUrl : asset($rawUrl);
                }
            }
            if (! $postMediaUrl) {
                $postMediaUrl = asset('assets/image/cooca_hero_banner.png');
            }

            $defaultCaption = "Bisnis bertumbuh, tapi semakin sulit dikendalikan? COOCA adalah software manajemen bisnis yang membantu perusahaan mengelola berbagai proses dalam satu ekosistem.\n\nMulai dari manajemen customer, manajemen persediaan, manajemen keuangan, hingga manajemen SDM dan project.\n\nDengan proses yang lebih terintegrasi, informasi lebih mudah dipantau dan keputusan bisnis dapat dibuat berdasarkan data.";
            $postCaption = $post?->content ?: $defaultCaption;
            $postLikes = (int) ($post?->metrics['likes'] ?? 5);
            $postCommentsCount = (int) ($post?->metrics['comments'] ?? 1);
            $postPublishedAt = $post?->published_at ? $post->published_at->format('d M Y H.i') : ($c->created_time ? $c->created_time->format('d M Y H.i') : '1 Sep 2026 14.30');

            // Find child replies (Sub-komentar / balasan)
            $threadReplies = $repliesComments->filter(function ($r) use ($c) {
                return $r->parent_comment_id === $c->platform_comment_id
                    || $r->parent_comment_id === $c->id;
            })->map(function ($r) use ($platformPageId, $platformIgId) {
                $isCooca = (bool) $r->is_from_page
                    || str_contains(strtolower((string) $r->from_name), 'cooca')
                    || in_array((string) $r->from_id, array_filter([$platformPageId, $platformIgId]), true);

                return [
                    'id'           => $r->id,
                    'comment_id'   => $r->platform_comment_id,
                    'from_id'      => $r->from_id,
                    'from_name'    => $isCooca ? 'Cooca Indonesia' : ($r->from_name ?: 'Pengguna'),
                    'message'      => $r->message,
                    'media_url'    => $r->media_url,
                    'media_type'   => $r->media_type,
                    'is_from_page' => $isCooca,
                    'is_cooca'     => $isCooca,
                    'role'         => $isCooca ? 'cooca' : 'visitor',
                    'time'         => $r->created_time ? $r->created_time->diffForHumans(null, true) : 'Baru saja',
                    'time_exact'   => $r->created_time ? $r->created_time->format('H.i') : $r->created_at->format('H.i'),
                    'date'         => $r->created_time ? $r->created_time->format('d M Y') : $r->created_at->format('d M Y'),
                    'parent_id'    => $r->parent_comment_id,
                ];
            })->values()->all();

            $isParentCooca = (bool) $c->is_from_page
                || str_contains(strtolower((string) $c->from_name), 'cooca')
                || in_array((string) $c->from_id, array_filter([$platformPageId, $platformIgId]), true);

            $parentCommentData = [
                'id'           => $c->id,
                'comment_id'   => $c->platform_comment_id,
                'from_id'      => $c->from_id,
                'from_name'    => $isParentCooca ? 'Cooca Indonesia' : ($c->from_name ?: 'Pengguna'),
                'message'      => $c->message,
                'media_url'    => $c->media_url,
                'media_type'   => $c->media_type,
                'is_from_page' => $isParentCooca,
                'is_cooca'     => $isParentCooca,
                'role'         => $isParentCooca ? 'cooca' : 'visitor',
                'time'         => $c->created_time ? $c->created_time->diffForHumans(null, true) : '2 jam',
                'time_exact'   => $c->created_time ? $c->created_time->format('H.i') : $c->created_at->format('H.i'),
                'date'         => $c->created_time ? $c->created_time->format('d M Y') : $c->created_at->format('d M Y'),
                'parent_id'    => null,
            ];

            $sortTimestamp = $c->created_time ? $c->created_time->timestamp : $c->created_at->timestamp;

            $threads[] = [
                'id'             => $c->id,
                'comment_id'     => $c->platform_comment_id,
                'customer_id'    => $c->from_id ?: $c->id,
                'name'           => $c->from_name ?: 'Pengguna Media Sosial',
                'channel'        => $channel,
                'channel_raw'    => $c->platform,
                'last_message'   => $c->message,
                'media_url'      => $c->media_url,
                'media_type'     => $c->media_type,
                'status'         => $c->status ?: 'unread',
                'is_unread'      => $c->status === 'unread',
                'post_snippet'   => Str::limit($postCaption, 60),
                'time'           => $c->created_time ? $c->created_time->format('H.i') : $c->created_at->format('H.i'),
                'time_relative'  => $c->created_time ? $c->created_time->diffForHumans(null, true) : '2 jam',
                'date'           => $c->created_time ? $c->created_time->format('d M Y') : $c->created_at->format('d M Y'),
                'sort_time'      => $sortTimestamp,
                'post'           => [
                    'id'             => $post?->id ?? $c->platform_post_id,
                    'title'          => Str::limit($postCaption, 70),
                    'caption'        => $postCaption,
                    'media_url'      => $postMediaUrl,
                    'likes'          => $postLikes,
                    'comments_count' => $postCommentsCount,
                    'published_at'   => $postPublishedAt,
                    'author'         => 'cooca.indonesia',
                ],
                'parent_comment' => $parentCommentData,
                'replies'        => $threadReplies,
                'messages'       => array_merge([$parentCommentData], $threadReplies),
            ];
        }

        // =========================================================================
        // B. BUILD DIRECT CHAT THREADS (Grouped by Customer / Conversation)
        // =========================================================================
        $chatGroups = $directChatItems->groupBy(function ($c) {
            $raw = strtolower((string) $c->platform);
            $ch = ($raw === 'instagram_dm' || $raw === 'instagram') ? 'instagram' : $raw;
            $cid = (string) ($c->from_id ?: $c->platform_comment_id ?: 'customer');
            return "{$ch}::{$cid}";
        });

        foreach ($chatGroups as $key => $groupMessages) {
            [$channel, $customerId] = explode('::', $key, 2);

            // Sort conversation messages chronologically (oldest first for natural chat flow)
            $sortedMsgs = $groupMessages->sortBy(function ($m) {
                return $m->created_time ? $m->created_time->timestamp : $m->created_at->timestamp;
            })->values();

            $latestMsg = $sortedMsgs->last();
            $customerMsg = $sortedMsgs->firstWhere('is_from_page', false) ?? $latestMsg;

            $customerName = (string) ($customerMsg->from_name ?: $customerMsg->from_id ?: 'Pelanggan');
            if (in_array($customerName, ['Cooca Indonesia (Admin)', 'Cooca Official', 'Cooca Indonesia'], true)) {
                $firstNonAdmin = $sortedMsgs->first(fn($m) => ! $m->is_from_page && ! empty($m->from_name));
                $customerName = $firstNonAdmin ? $firstNonAdmin->from_name : ($customerId ?: 'Pelanggan');
            }

            // Unread if any incoming message is unread
            $hasUnread = $sortedMsgs->contains(function ($m) {
                return ! $m->is_from_page && $m->status === 'unread';
            });

            $threadStatus = $latestMsg->status ?: ($hasUnread ? 'unread' : 'read');

            $formattedMessages = $sortedMsgs->map(function ($m) use ($customerName) {
                $isFromPage = (bool) $m->is_from_page;
                return [
                    'id'           => $m->id,
                    'comment_id'   => $m->platform_comment_id,
                    'from_id'      => $m->from_id,
                    'from_name'    => $isFromPage ? 'Cooca Indonesia' : ($m->from_name ?: $customerName),
                    'message'      => $m->message,
                    'media_url'    => $m->media_url,
                    'media_type'   => $m->media_type,
                    'is_from_page' => $isFromPage,
                    'is_cooca'     => $isFromPage,
                    'time'         => $m->created_time ? $m->created_time->format('H.i') : $m->created_at->format('H.i'),
                    'date'         => $m->created_time ? $m->created_time->format('d M Y') : $m->created_at->format('d M Y'),
                    'parent_id'    => $m->parent_comment_id,
                ];
            })->all();

            $sortTimestamp = $latestMsg->created_time ? $latestMsg->created_time->timestamp : $latestMsg->created_at->timestamp;

            $threads[] = [
                'id'           => $latestMsg->id,
                'comment_id'   => $latestMsg->platform_comment_id,
                'customer_id'  => $customerId,
                'name'         => $customerName,
                'channel'      => $channel,
                'channel_raw'  => $latestMsg->platform,
                'last_message' => $latestMsg->message,
                'media_url'    => $latestMsg->media_url,
                'media_type'   => $latestMsg->media_type,
                'status'       => $threadStatus,
                'is_unread'    => $hasUnread,
                'post_snippet' => '',
                'time'         => $latestMsg->created_time ? $latestMsg->created_time->format('H.i') : $latestMsg->created_at->format('H.i'),
                'time_relative'=> $latestMsg->created_time ? $latestMsg->created_time->diffForHumans(null, true) : 'Baru saja',
                'date'         => $latestMsg->created_time ? $latestMsg->created_time->format('d M Y') : $latestMsg->created_at->format('d M Y'),
                'sort_time'    => $sortTimestamp,
                'post'         => null,
                'messages'     => $formattedMessages,
            ];
        }

        // Sort all threads by latest activity descending (newest conversation on top)
        usort($threads, function ($a, $b) {
            return ($b['sort_time'] ?? 0) <=> ($a['sort_time'] ?? 0);
        });

        return $threads;
    }

    /**
     * Ensure baseline demo platform post and comments exist across all 5 channels.
     * Guaranteed baseline conversation available for WhatsApp, Messenger, Instagram, and Comments.
     */
    protected function ensureInitialPlatformData(): void
    {
        $post = SocialMediaPost::firstOrCreate(
            [
                'is_platform'      => true,
                'platform'         => 'instagram',
                'platform_post_id' => 'ig_post_cooca_001',
            ],
            [
                'content'      => "Bisnis bertumbuh, tapi semakin sulit dikendalikan? COOCA adalah software manajemen bisnis yang membantu perusahaan mengelola berbagai proses dalam satu ekosistem.\n\nMulai dari manajemen customer, manajemen persediaan, manajemen keuangan, hingga manajemen SDM dan project.\n\nDengan proses yang lebih terintegrasi, informasi lebih mudah dipantau dan keputusan bisnis dapat dibuat berdasarkan data.",
                'media_type'   => 'image',
                'media_urls'   => ['assets/image/cooca_hero_banner.png'],
                'status'       => 'published',
                'metrics'      => ['likes' => 5, 'comments' => 1],
                'published_at' => Carbon::parse('2026-09-01 14:30:00'),
            ]
        );

        // 1. Instagram Comments Baseline
        if (SocialMediaComment::where('is_platform', true)->where('platform', 'instagram_comments')->count() === 0) {
            SocialMediaComment::updateOrCreate(
                [
                    'is_platform'         => true,
                    'platform'            => 'instagram_comments',
                    'platform_comment_id' => 'ig_comm_agung_001',
                ],
                [
                    'social_media_post_id' => $post->id,
                    'platform_post_id'     => $post->platform_post_id,
                    'parent_comment_id'    => null,
                    'from_id'              => 'agung.mustaqim',
                    'from_name'            => 'agung.mustaqim',
                    'message'              => '🔥🔥🔥',
                    'is_from_page'         => false,
                    'status'               => 'unread',
                    'created_time'         => now()->subHours(2),
                ]
            );

            SocialMediaComment::updateOrCreate(
                [
                    'is_platform'         => true,
                    'platform'            => 'instagram_comments',
                    'platform_comment_id' => 'ig_comm_reply_cooca_001',
                ],
                [
                    'social_media_post_id' => $post->id,
                    'platform_post_id'     => $post->platform_post_id,
                    'parent_comment_id'    => 'ig_comm_agung_001',
                    'from_id'              => 'cooca.indonesia',
                    'from_name'            => 'Cooca Indonesia',
                    'message'              => 'Halo Kak! Terima kasih telah menghubungi kami. Ada yang bisa kami bantu? 😊',
                    'is_from_page'         => true,
                    'status'               => 'replied',
                    'created_time'         => now()->subMinutes(17),
                ]
            );
        }

        // 2. Facebook Comments Baseline
        if (SocialMediaComment::where('is_platform', true)->whereIn('platform', ['facebook', 'facebook_comments'])->count() === 0) {
            SocialMediaComment::updateOrCreate(
                [
                    'is_platform'         => true,
                    'platform'            => 'facebook_comments',
                    'platform_comment_id' => 'fb_comm_agung_002',
                ],
                [
                    'social_media_post_id' => $post->id,
                    'platform_post_id'     => $post->platform_post_id,
                    'from_id'              => 'agung.mustaqim',
                    'from_name'            => 'Agung Mustaqim',
                    'message'              => 'Keren sekali solusinya, mau tanya info harga paketnya min?',
                    'is_from_page'         => false,
                    'status'               => 'unread',
                    'created_time'         => now()->subHours(3),
                ]
            );
        }

        // 3. Instagram Direct Message Baseline
        if (SocialMediaComment::where('is_platform', true)->whereIn('platform', ['instagram', 'instagram_dm'])->count() === 0) {
            SocialMediaComment::updateOrCreate(
                [
                    'is_platform'         => true,
                    'platform'            => 'instagram',
                    'platform_comment_id' => 'ig_dm_dian_001',
                ],
                [
                    'platform_post_id'     => 'meta_ig_account_demo',
                    'from_id'              => 'dian_sari',
                    'from_name'            => 'dian_sari',
                    'message'              => 'Halo min, apakah akun Instagram ini official Cooca?',
                    'is_from_page'         => false,
                    'status'               => 'unread',
                    'created_time'         => now()->subHours(1),
                ]
            );
        }

        // 4. Facebook Messenger Baseline
        if (SocialMediaComment::where('is_platform', true)->where('platform', 'messenger')->count() === 0) {
            SocialMediaComment::updateOrCreate(
                [
                    'is_platform'         => true,
                    'platform'            => 'messenger',
                    'platform_comment_id' => 'fb_msg_budi_001',
                ],
                [
                    'platform_post_id'     => 'meta_page_demo',
                    'from_id'              => 'budi_santoso_fb',
                    'from_name'            => 'Budi Santoso',
                    'message'              => 'Halo kak, mau tanya fitur laporan keuangan dan integrasi POS di Cooca.',
                    'is_from_page'         => false,
                    'status'               => 'unread',
                    'created_time'         => now()->subMinutes(45),
                ]
            );
        }

        // 5. WhatsApp Cloud API Baseline (Incoming + Outgoing with Image Attachment)
        if (SocialMediaComment::where('is_platform', true)->where('platform', 'whatsapp')->count() === 0) {
            SocialMediaComment::updateOrCreate(
                [
                    'is_platform'         => true,
                    'platform'            => 'whatsapp',
                    'platform_comment_id' => 'wa_msg_citra_001',
                ],
                [
                    'platform_post_id'     => 'meta_wa_phone_demo',
                    'from_id'              => '6281234567890',
                    'from_name'            => 'Citra Lestari (+6281234567890)',
                    'message'              => 'Halo kak, saya mau tanya harga paket Cooca Enterprise untuk 3 cabang retail.',
                    'is_from_page'         => false,
                    'status'               => 'unread',
                    'created_time'         => now()->subMinutes(20),
                ]
            );

            SocialMediaComment::updateOrCreate(
                [
                    'is_platform'         => true,
                    'platform'            => 'whatsapp',
                    'platform_comment_id' => 'wa_msg_cooca_reply_001',
                ],
                [
                    'platform_post_id'     => 'meta_wa_phone_demo',
                    'parent_comment_id'    => 'wa_msg_citra_001',
                    'from_id'              => '6281234567890',
                    'from_name'            => 'Cooca Indonesia (Admin)',
                    'message'              => 'Halo Kak Citra! Berikut kami lampirkan brosur katalog paket Cooca Enterprise untuk kebutuhan retail multi-cabang. Ada yang ingin ditanyakan lagi kak? 😊',
                    'media_url'            => asset('assets/image/cooca_hero_banner.png'),
                    'media_type'           => 'image',
                    'is_from_page'         => true,
                    'status'               => 'replied',
                    'created_time'         => now()->subMinutes(10),
                ]
            );
        }
    }
}
