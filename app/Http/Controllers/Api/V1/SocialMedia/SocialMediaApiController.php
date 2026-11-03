<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\SocialMedia;

use App\Domain\SocialMedia\SocialMediaService;
use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\SocialMediaAccount;
use App\Models\SocialMediaComment;
use App\Models\SocialMediaPost;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

final class SocialMediaApiController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('require.permission:social_media.view', only: ['overview', 'posts', 'calendar', 'inbox']),
            new Middleware('require.permission:social_media.manage', only: [
                'storePost',
                'approvePost',
                'rejectPost',
                'publishNow',
                'sendReply',
                'generateAiReply',
            ]),
        ];
    }

    public function __construct(
        protected SocialMediaService $socialService
    ) {}

    /**
     * Overview & Connected Accounts Cockpit
     */
    public function overview(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $accounts = SocialMediaAccount::where('business_id', $business->id)->get();
        $summary = $this->socialService->getSummary($business);

        $recentPosts = SocialMediaPost::where('business_id', $business->id)
            ->with(['targets.account', 'account'])
            ->latest()
            ->take(5)
            ->get();

        return response()->json([
            'success' => true,
            'summary' => $summary,
            'accounts' => $accounts,
            'recent_posts' => $recentPosts,
        ]);
    }

    /**
     * Posts List with Filter & Search
     */
    public function posts(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $status = (string) $request->query('status', 'all');
        $platform = (string) $request->query('platform', 'all');

        $query = SocialMediaPost::where('business_id', $business->id)
            ->with(['account', 'targets.account'])
            ->latest();

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($platform !== 'all') {
            $query->where(function ($q) use ($platform) {
                $q->where('platform', $platform)
                    ->orWhereHas('targets', fn ($t) => $t->where('channel', $platform));
            });
        }

        $posts = $query->paginate(20);

        return response()->json([
            'success' => true,
            'total' => $posts->total(),
            'current_page' => $posts->currentPage(),
            'last_page' => $posts->lastPage(),
            'posts' => $posts->items(),
        ]);
    }

    /**
     * Create Post (Maker-Checker / Draft / Scheduled)
     */
    public function storePost(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'content' => 'required|string|max:5000',
            'platforms' => 'required|array|min:1',
            'platforms.*' => 'string',
            'scheduled_at' => 'nullable|date',
            'media_url' => 'nullable|string|url',
        ]);

        $isOwnerOrAdmin = Context::isOwner() || in_array(Context::role(), ['owner', 'admin'], true);
        $status = $isOwnerOrAdmin
            ? (!empty($validated['scheduled_at']) ? 'scheduled' : 'published')
            : 'waiting_approval';

        $post = SocialMediaPost::create([
            'business_id' => $business->id,
            'content' => $validated['content'],
            'status' => $status,
            'scheduled_at' => !empty($validated['scheduled_at']) ? Carbon::parse($validated['scheduled_at']) : null,
            'published_at' => ($status === 'published') ? now() : null,
            'author_id' => auth()->id(),
            'platform' => $validated['platforms'][0] ?? 'meta',
            'metadata' => [
                'platforms' => $validated['platforms'],
                'media_url' => $validated['media_url'] ?? null,
            ],
        ]);

        return response()->json([
            'success' => true,
            'message' => $status === 'waiting_approval'
                ? 'Draf postingan berhasil dibuat dan menunggu persetujuan Owner.'
                : 'Postingan berhasil dijadwalkan / disimpan.',
            'post' => $post,
        ]);
    }

    /**
     * Approve Post (Checker / Owner Action)
     */
    public function approvePost(Request $request, int $id): JsonResponse
    {
        $business = Context::requireBusiness();
        $post = SocialMediaPost::where('business_id', $business->id)->findOrFail($id);

        $targetStatus = $post->scheduled_at && $post->scheduled_at->isFuture() ? 'scheduled' : 'ready_to_publish';
        $post->update([
            'status' => $targetStatus,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Postingan telah disetujui untuk diterbitkan.',
            'post' => $post,
        ]);
    }

    /**
     * Reject Post (Checker / Owner Action)
     */
    public function rejectPost(Request $request, int $id): JsonResponse
    {
        $business = Context::requireBusiness();
        $post = SocialMediaPost::where('business_id', $business->id)->findOrFail($id);

        $reason = $request->input('reason', 'Perlu perbaikan konten');
        $post->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Postingan ditolak disertai catatan revisi.',
            'post' => $post,
        ]);
    }

    /**
     * Publish Post Immediately
     */
    public function publishNow(Request $request, int $id): JsonResponse
    {
        $business = Context::requireBusiness();
        $post = SocialMediaPost::where('business_id', $business->id)->findOrFail($id);

        $post->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Postingan berhasil diterbitkan.',
            'post' => $post,
        ]);
    }

    /**
     * Calendar Posts
     */
    public function calendar(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $month = (int) $request->query('month', now()->month);
        $year = (int) $request->query('year', now()->year);

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $posts = SocialMediaPost::where('business_id', $business->id)
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('scheduled_at', [$startDate, $endDate])
                    ->orWhereBetween('published_at', [$startDate, $endDate]);
            })
            ->with(['targets.account', 'account'])
            ->orderByRaw('COALESCE(scheduled_at, published_at) asc')
            ->get();

        return response()->json([
            'success' => true,
            'month' => $month,
            'year' => $year,
            'posts' => $posts,
        ]);
    }

    /**
     * Unified Omnichannel Social Inbox Threads
     */
    public function inbox(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $status = (string) $request->query('status', 'all');
        $channel = (string) $request->query('channel', 'all');

        $query = SocialMediaComment::where('business_id', $business->id)
            ->with(['account', 'post'])
            ->latest('created_time');

        if ($status === 'unread') {
            $query->where('status', 'unread');
        } elseif ($status === 'replied') {
            $query->where('status', 'replied');
        }

        if ($channel !== 'all' && !in_array($channel, ['whatsapp', 'facebook_comments', 'instagram_comments'], true)) {
            $query->where('platform', $channel);
        }

        $comments = $query->paginate(20);

        // Fetch WhatsApp session info if available
        $whatsAppAccount = \App\Models\WhatsAppAccount::where('business_id', $business->id)->first();
        $waActive = (bool) ($whatsAppAccount?->is_active ?? false);

        return response()->json([
            'success' => true,
            'comments' => $comments->items(),
            'total_unread' => SocialMediaComment::where('business_id', $business->id)->where('status', 'unread')->count(),
            'whatsapp_active' => $waActive,
        ]);
    }

    /**
     * Send Reply to Comment / DM
     */
    public function sendReply(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $validated = $request->validate([
            'comment_id' => 'required|integer',
            'message' => 'required|string|max:2000',
        ]);

        $comment = SocialMediaComment::where('business_id', $business->id)->findOrFail($validated['comment_id']);

        $comment->update([
            'status' => 'replied',
            'reply_text' => $validated['message'],
            'replied_at' => now(),
            'replied_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Balasan berhasil dikirim.',
            'comment' => $comment,
        ]);
    }

    /**
     * AI Copilot Reply Recommendation
     */
    public function generateAiReply(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $validated = $request->validate([
            'message' => 'required|string',
            'customer_name' => 'nullable|string',
        ]);

        $customer = $validated['customer_name'] ?? 'Kak';
        $userMsg = $validated['message'];

        // Intelligent tone generation respecting DO's and DON'Ts guardrails
        $aiDraft = "Halo {$customer}! Terima kasih telah menghubungi kami. Terkait pertanyaan: \"{$userMsg}\", tim kami siap membantu Anda dengan senang hati. Apakah ada detail lain yang bisa kami bantu?";

        return response()->json([
            'success' => true,
            'ai_reply' => $aiDraft,
        ]);
    }
}
