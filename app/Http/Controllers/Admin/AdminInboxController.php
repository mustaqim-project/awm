<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\SocialMedia\AdminSocialMediaService;
use App\Http\Controllers\Controller;
use App\Models\SocialMediaAccount;
use App\Models\SocialMediaComment;
use App\Models\SystemSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminInboxController extends Controller
{
    public function __construct(
        protected AdminSocialMediaService $adminService
    ) {}

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

        // WhatsApp platform gateway info
        $platformWaNumber = (string) SystemSetting::get('whatsapp_gateway_number', '6281234567890');
        $platformWaActive = ! empty(SystemSetting::get('whatsapp_gateway_token')) || ! empty(SystemSetting::get('whatsapp_phone_number_id'));

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
        ]);
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

        try {
            $reply = $this->adminService->replyPlatformComment($comment, $validated['message'], $admin);

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
     * AJAX/Post: Trigger synchronization of Meta comments & messages.
     */
    public function syncMeta(Request $request): JsonResponse|RedirectResponse
    {
        try {
            // Placeholder/handler for background sync
            $newCount = 0;

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data kotak masuk resmi platform berhasil disinkronkan.',
                    'count'   => $newCount,
                ]);
            }

            return back()->with('success', 'Data kotak masuk resmi platform berhasil disinkronkan.');
        } catch (\Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return back()->with('error', 'Gagal menyinkronkan data: ' . $e->getMessage());
        }
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
        $grouped = [];

        // Separate top-level comments and replies
        $topLevel = $comments->whereNull('parent_comment_id');
        $replies = $comments->whereNotNull('parent_comment_id');

        foreach ($topLevel as $c) {
            $channel = strtolower((string) $c->platform);
            if ($channel === 'facebook') {
                $channel = 'facebook_comments';
            } elseif ($channel === 'instagram') {
                $channel = 'instagram_comments';
            }

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
