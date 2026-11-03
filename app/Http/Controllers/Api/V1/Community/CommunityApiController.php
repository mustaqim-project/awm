<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Community;

use App\Domain\Billing\EntitlementService;
use App\Domain\Community\ContentModerationService;
use App\Domain\Community\EventCheckinService;
use App\Domain\Community\EventTicketService;
use App\Domain\Community\TrendingTopicService;
use App\Domain\Payment\TripayService;
use App\Domain\Storage\OwnerStorageQuotaService;
use App\Domain\Storage\StorageTrackingService;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Business;
use App\Models\CommunityComment;
use App\Models\CommunityEvent;
use App\Models\CommunityEventRegistration;
use App\Models\CommunityLike;
use App\Models\CommunityNotification;
use App\Models\CommunityPost;
use App\Models\CommunityRepost;
use App\Models\StorageFile;
use App\Models\User;
use App\Support\Context;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class CommunityApiController extends Controller implements HasMiddleware
{
    public const MAX_IMAGE_BYTES = 5 * 1024 * 1024; // 5 MB

    public static function middleware(): array
    {
        return [];
    }

    public function __construct(
        private readonly OwnerStorageQuotaService $storageQuota = new OwnerStorageQuotaService,
        private readonly EventCheckinService $checkinService = new EventCheckinService,
        private readonly EventTicketService $ticketService = new EventTicketService,
        private readonly TrendingTopicService $trendingService = new TrendingTopicService,
        private readonly ContentModerationService $moderationService = new ContentModerationService,
        private readonly EntitlementService $entitlementService = new EntitlementService,
        private readonly TripayService $tripayService = new TripayService,
    ) {}

    private function resolveBusiness(Request $request): Business
    {
        $business = Context::business();
        if ($business instanceof Business) {
            return $business;
        }

        $user = $request->user();
        if ($user instanceof User) {
            $fallback = $user->activeBusiness ?? $user->businesses()->first();
            if ($fallback instanceof Business) {
                Context::setBusiness($fallback);
                return $fallback;
            }
        }

        abort(422, 'Tidak ada bisnis aktif yang dipilih.');
    }

    /**
     * Get Community Feed (posts, trending topics, unread count, subscription status).
     */
    public function feed(Request $request): JsonResponse
    {
        $business = $this->resolveBusiness($request);
        $user = $request->user();

        $activeTab = (string) $request->query('tab', 'feed'); // 'feed' or 'my_posts'
        $activeTopic = $request->query('topic');
        $activeSearch = $request->query('q');
        $perPage = min(30, max(5, (int) $request->query('per_page', 15)));

        $postsQuery = CommunityPost::with([
            'owner:id,name,avatar',
            'business:id,name,business_type',
            'comments.user:id,name,avatar',
            'reposts.user:id,name',
        ])->withCount(['likes', 'comments', 'reposts']);

        if ($activeTab === 'my_posts' && $user !== null) {
            $myRepostedIds = CommunityRepost::where('user_id', $user->getAuthIdentifier())->pluck('post_id');
            $postsQuery->where(function ($q) use ($user, $myRepostedIds): void {
                $q->where('owner_id', $user->getAuthIdentifier())
                  ->orWhereIn('id', $myRepostedIds);
            });
        }

        if ($activeTopic) {
            $postsQuery->where('content', 'like', "%{$activeTopic}%");
        }

        if ($activeSearch) {
            $postsQuery->where('content', 'like', "%{$activeSearch}%");
        }

        $paginatedPosts = $postsQuery->latest()->paginate($perPage);

        $likedPostIds = collect();
        $repostedPostIds = collect();
        if ($user !== null) {
            $postIds = $paginatedPosts->pluck('id');
            $likedPostIds = CommunityLike::where('user_id', $user->getAuthIdentifier())
                ->whereIn('post_id', $postIds)
                ->pluck('post_id')
                ->flip();

            $repostedPostIds = CommunityRepost::where('user_id', $user->getAuthIdentifier())
                ->whereIn('post_id', $postIds)
                ->pluck('post_id')
                ->flip();
        }

        $formattedPosts = $paginatedPosts->getCollection()->map(function (CommunityPost $post) use ($user, $likedPostIds, $repostedPostIds): array {
            $isMine = $user !== null && (string) $post->owner_id === (string) $user->getAuthIdentifier();
            $likedByMe = $likedPostIds->has($post->id);
            $repostedByMe = $repostedPostIds->has($post->id);

            return [
                'id' => (string) $post->id,
                'content' => $post->content,
                'image_url' => $post->image_url,
                'owner_id' => (string) $post->owner_id,
                'business_id' => (string) $post->business_id,
                'created_at' => $post->created_at?->toIso8601String(),
                'created_at_human' => $post->created_at?->diffForHumans() ?? '',
                'likes_count' => (int) $post->likes_count,
                'comments_count' => (int) $post->comments_count,
                'reposts_count' => (int) $post->reposts_count,
                'liked_by_me' => $likedByMe,
                'reposted_by_me' => $repostedByMe,
                'is_mine' => $isMine,
                'owner' => [
                    'id' => (string) ($post->owner?->id ?? ''),
                    'name' => $post->owner?->name ?? 'Pengguna Cooca',
                    'avatar_url' => $post->owner?->avatar_url,
                ],
                'business' => [
                    'id' => (string) ($post->business?->id ?? ''),
                    'name' => $post->business?->name ?? 'Usaha Mitra',
                    'business_type' => $post->business?->business_type ?? 'UMKM',
                ],
                'comments' => $post->comments->take(10)->map(fn (CommunityComment $c): array => [
                    'id' => (string) $c->id,
                    'content' => $c->content,
                    'created_at_human' => $c->created_at?->diffForHumans() ?? '',
                    'user' => [
                        'id' => (string) ($c->user?->id ?? ''),
                        'name' => $c->user?->name ?? 'Member',
                        'avatar_url' => $c->user?->avatar_url,
                    ],
                ]),
            ];
        });

        // Top 5 trending topics
        $trendingTopics = $this->trendingService->getTopTrendingTopics(5);

        // Unread notifications count
        $unreadNotificationsCount = 0;
        if ($user !== null) {
            $unreadNotificationsCount = CommunityNotification::where('user_id', $user->getAuthIdentifier())
                ->where('is_read', false)
                ->count();
        }

        $isSubscribed = $this->entitlementService->canParticipateInCommunity($business, $user);

        return response()->json([
            'success' => true,
            'posts' => $formattedPosts,
            'pagination' => [
                'current_page' => $paginatedPosts->currentPage(),
                'last_page' => $paginatedPosts->lastPage(),
                'per_page' => $paginatedPosts->perPage(),
                'total' => $paginatedPosts->total(),
                'has_more' => $paginatedPosts->hasMorePages(),
            ],
            'trending_topics' => $trendingTopics,
            'unread_notifications' => $unreadNotificationsCount,
            'is_subscribed' => $isSubscribed,
        ]);
    }

    /**
     * Store a new community post (supports image file or image_base64).
     */
    public function storePost(Request $request): JsonResponse
    {
        $business = $this->resolveBusiness($request);
        $user = $request->user();
        abort_unless($user !== null, 403);

        if (! $this->entitlementService->canParticipateInCommunity($business, $user)) {
            return response()->json([
                'success' => false,
                'code' => 'SUBSCRIPTION_REQUIRED',
                'message' => 'Akses membuat postingan komunitas khusus untuk member yang telah berlangganan (subscribe) paket Cooca.',
            ], 403);
        }

        $validated = $request->validate([
            'content' => ['required', 'string', 'max:5000'],
            'image' => ['nullable', 'file', 'image', 'mimes:jpeg,png,webp,gif', 'max:' . (self::MAX_IMAGE_BYTES / 1024)],
            'image_base64' => ['nullable', 'string'],
        ]);

        try {
            $cleanContent = $this->moderationService->validateOrFail($validated['content']);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->validator->errors()->first('content') ?: 'Konten mengandung kata yang dilarang atau melanggar pedoman komunitas.',
            ], 422);
        }

        $imagePath = null;
        $trackingService = app(StorageTrackingService::class);
        $owner = app(OwnerStorageQuotaService::class)->ownerForBusiness($business) ?? $user;

        // Process standard file upload
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $trackingService->assertCanUpload($owner, (int) $file->getSize(), 'image');

            $extension = $file->getClientOriginalExtension() ?: 'jpg';
            $dir = "businesses/{$business->id}/community";
            $imagePath = $file->storeAs(
                $dir,
                Str::uuid() . '.' . $extension,
                'public'
            );

            $trackingService->recordUpload(
                file: $file,
                filePath: $imagePath,
                category: StorageFile::CATEGORY_COMMUNITY_IMAGE,
                module: 'community',
                owner: $owner,
                business: $business,
                uploader: $user
            );
        }
        // Process base64 image upload
        elseif (! empty($validated['image_base64'])) {
            $raw = $validated['image_base64'];
            if (preg_match('/^data:image\/(\w+);base64,/', $raw, $type)) {
                $raw = substr($raw, strpos($raw, ',') + 1);
                $ext = strtolower($type[1]);
                if (! in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
                    $ext = 'jpg';
                }
            } else {
                $ext = 'jpg';
            }

            $decoded = base64_decode($raw);
            if ($decoded !== false && strlen($decoded) > 0) {
                if (strlen($decoded) > self::MAX_IMAGE_BYTES) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Ukuran gambar melebihi batas maksimum 5 MB.',
                    ], 422);
                }

                $trackingService->assertCanUpload($owner, strlen($decoded), 'image');
                $filename = Str::uuid() . '.' . $ext;
                $dir = "businesses/{$business->id}/community";
                $imagePath = "{$dir}/{$filename}";
                Storage::disk('public')->put($imagePath, $decoded);

                $trackingService->recordUpload(
                    file: $imagePath,
                    filePath: $imagePath,
                    category: StorageFile::CATEGORY_COMMUNITY_IMAGE,
                    module: 'community',
                    owner: $owner,
                    business: $business,
                    uploader: $user
                );
            }
        }

        $post = CommunityPost::create([
            'owner_id' => $user->getAuthIdentifier(),
            'business_id' => $business->id,
            'content' => $cleanContent,
            'image_path' => $imagePath,
        ]);

        $post->load(['owner:id,name,avatar', 'business:id,name,business_type']);

        return response()->json([
            'success' => true,
            'message' => 'Postingan komunitas berhasil dibagikan.',
            'post' => [
                'id' => (string) $post->id,
                'content' => $post->content,
                'image_url' => $post->image_url,
                'owner_id' => (string) $post->owner_id,
                'business_id' => (string) $post->business_id,
                'created_at' => $post->created_at?->toIso8601String(),
                'created_at_human' => 'Baru saja',
                'likes_count' => 0,
                'comments_count' => 0,
                'reposts_count' => 0,
                'liked_by_me' => false,
                'reposted_by_me' => false,
                'is_mine' => true,
                'owner' => [
                    'id' => (string) ($post->owner?->id ?? ''),
                    'name' => $post->owner?->name ?? 'Pengguna Cooca',
                    'avatar_url' => $post->owner?->avatar_url,
                ],
                'business' => [
                    'id' => (string) ($post->business?->id ?? ''),
                    'name' => $post->business?->name ?? 'Usaha Mitra',
                    'business_type' => $post->business?->business_type ?? 'UMKM',
                ],
                'comments' => [],
            ],
        ], 201);
    }

    /**
     * Delete a community post (only post owner).
     */
    public function destroyPost(Request $request, CommunityPost $post): JsonResponse
    {
        $user = $request->user();
        abort_unless($user !== null, 403);

        if ((string) $post->owner_id !== (string) $user->getAuthIdentifier()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda hanya dapat menghapus postingan milik sendiri.',
            ], 403);
        }

        AuditLog::create([
            'business_id' => $post->business_id,
            'user_id' => $user->getAuthIdentifier(),
            'action' => 'community.post.deleted',
            'auditable_type' => CommunityPost::class,
            'auditable_id' => $post->id,
            'risk_level' => AuditLog::RISK_MEDIUM,
            'notes' => 'Menghapus postingan komunitas: ' . Str::limit($post->content, 60),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        if ($post->image_path) {
            app(StorageTrackingService::class)->deleteFile($post->image_path, 'public');
        }

        $post->delete();

        return response()->json([
            'success' => true,
            'message' => 'Postingan berhasil dihapus.',
        ]);
    }

    /**
     * Toggle like on post.
     */
    public function toggleLike(Request $request, CommunityPost $post): JsonResponse
    {
        $user = $request->user();
        abort_unless($user !== null, 403);

        $like = CommunityLike::where('post_id', $post->id)
            ->where('user_id', $user->getAuthIdentifier())
            ->first();

        if ($like) {
            $like->delete();
            $liked = false;
        } else {
            CommunityLike::create([
                'post_id' => $post->id,
                'user_id' => $user->getAuthIdentifier(),
            ]);
            $liked = true;

            if ((string) $post->owner_id !== (string) $user->getAuthIdentifier()) {
                CommunityNotification::create([
                    'user_id' => $post->owner_id,
                    'actor_id' => $user->getAuthIdentifier(),
                    'post_id' => $post->id,
                    'type' => CommunityNotification::TYPE_LIKE,
                    'message' => 'menyukai postingan Anda',
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'liked' => $liked,
            'likes_count' => $post->likes()->count(),
        ]);
    }

    /**
     * Toggle repost.
     */
    public function toggleRepost(Request $request, CommunityPost $post): JsonResponse
    {
        $user = $request->user();
        abort_unless($user !== null, 403);
        $business = $this->resolveBusiness($request);

        if (! $this->entitlementService->canParticipateInCommunity($business, $user)) {
            return response()->json([
                'success' => false,
                'code' => 'SUBSCRIPTION_REQUIRED',
                'message' => 'Akses repost postingan komunitas khusus untuk member yang telah berlangganan (subscribe).',
            ], 403);
        }

        $repost = CommunityRepost::where('post_id', $post->id)
            ->where('user_id', $user->getAuthIdentifier())
            ->first();

        if ($repost) {
            $repost->delete();
            $reposted = false;
        } else {
            CommunityRepost::create([
                'post_id' => $post->id,
                'user_id' => $user->getAuthIdentifier(),
                'business_id' => $business->id,
            ]);
            $reposted = true;

            if ((string) $post->owner_id !== (string) $user->getAuthIdentifier()) {
                CommunityNotification::create([
                    'user_id' => $post->owner_id,
                    'actor_id' => $user->getAuthIdentifier(),
                    'post_id' => $post->id,
                    'type' => CommunityNotification::TYPE_REPOST,
                    'message' => 'me-repost postingan Anda',
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'reposted' => $reposted,
            'reposts_count' => $post->reposts()->count(),
        ]);
    }

    /**
     * Add a comment to a post.
     */
    public function comment(Request $request, CommunityPost $post): JsonResponse
    {
        $user = $request->user();
        abort_unless($user !== null, 403);
        $business = $this->resolveBusiness($request);

        if (! $this->entitlementService->canParticipateInCommunity($business, $user)) {
            return response()->json([
                'success' => false,
                'code' => 'SUBSCRIPTION_REQUIRED',
                'message' => 'Akses komentar komunitas khusus untuk member yang telah berlangganan (subscribe).',
            ], 403);
        }

        $validated = $request->validate([
            'content' => ['required', 'string', 'max:1000'],
        ]);

        try {
            $cleanContent = $this->moderationService->validateOrFail($validated['content']);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->validator->errors()->first('content') ?: 'Komentar mengandung kata yang dilarang.',
            ], 422);
        }

        $comment = CommunityComment::create([
            'post_id' => $post->id,
            'user_id' => $user->getAuthIdentifier(),
            'content' => $cleanContent,
        ]);

        $comment->load(['user:id,name,avatar']);

        if ((string) $post->owner_id !== (string) $user->getAuthIdentifier()) {
            CommunityNotification::create([
                'user_id' => $post->owner_id,
                'actor_id' => $user->getAuthIdentifier(),
                'post_id' => $post->id,
                'type' => CommunityNotification::TYPE_COMMENT,
                'message' => 'membalas: "' . Str::limit($validated['content'], 40) . '"',
            ]);
        }

        return response()->json([
            'success' => true,
            'comment' => [
                'id' => (string) $comment->id,
                'content' => $comment->content,
                'created_at_human' => 'Baru saja',
                'user' => [
                    'id' => (string) ($comment->user?->id ?? ''),
                    'name' => $comment->user?->name ?? 'Member',
                    'avatar_url' => $comment->user?->avatar_url,
                ],
            ],
            'comments_count' => $post->comments()->count(),
        ]);
    }

    /**
     * Get user's notifications.
     */
    public function notifications(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user !== null, 403);

        $notifications = CommunityNotification::with([
            'actor:id,name,avatar',
            'post:id,content',
        ])
            ->where('user_id', $user->getAuthIdentifier())
            ->latest()
            ->take(40)
            ->get();

        $unreadCount = CommunityNotification::where('user_id', $user->getAuthIdentifier())
            ->where('is_read', false)
            ->count();

        $formatted = $notifications->map(fn (CommunityNotification $n): array => [
            'id' => (string) $n->id,
            'type' => $n->type,
            'message' => $n->message,
            'is_read' => (bool) $n->is_read,
            'post_id' => (string) $n->post_id,
            'post_preview' => Str::limit($n->post?->content ?? '', 50),
            'created_at' => $n->created_at?->toIso8601String(),
            'created_at_human' => $n->created_at?->diffForHumans() ?? '',
            'actor' => [
                'id' => (string) ($n->actor?->id ?? ''),
                'name' => $n->actor?->name ?? 'Member',
                'avatar_url' => $n->actor?->avatar_url,
            ],
        ]);

        return response()->json([
            'success' => true,
            'notifications' => $formatted,
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markNotificationsRead(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user !== null, 403);

        CommunityNotification::where('user_id', $user->getAuthIdentifier())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    /**
     * List all upcoming events & user's registered events.
     */
    public function events(Request $request): JsonResponse
    {
        $user = $request->user();

        $upcomingEvents = CommunityEvent::published()
            ->upcoming()
            ->withCount('activeRegistrations')
            ->orderBy('start_time', 'asc')
            ->get();

        $userRegistrations = collect();
        if ($user !== null) {
            $userRegistrations = CommunityEventRegistration::where('user_id', $user->getAuthIdentifier())
                ->whereIn('event_id', $upcomingEvents->pluck('id'))
                ->get()
                ->keyBy('event_id');
        }

        $formattedEvents = $upcomingEvents->map(function (CommunityEvent $event) use ($userRegistrations): array {
            $reg = $userRegistrations->get($event->id);

            return [
                'id' => (string) $event->id,
                'title' => $event->title,
                'slug' => $event->slug,
                'description' => $event->description,
                'banner_url' => $event->banner_url,
                'event_type' => $event->event_type,
                'is_online' => $event->isOnline(),
                'is_offline' => $event->isOffline(),
                'price_type' => $event->price_type,
                'is_paid' => $event->isPaid(),
                'price' => (float) $event->price,
                'formatted_price' => $event->formatted_price,
                'location_name' => $event->location_name,
                'location_address' => $event->location_address,
                'online_meeting_url' => $event->online_meeting_url,
                'latitude' => $event->latitude,
                'longitude' => $event->longitude,
                'checkin_radius_meters' => $event->checkin_radius_meters,
                'start_time' => $event->start_time?->toIso8601String(),
                'start_time_formatted' => $event->start_time?->translatedFormat('l, d M Y • H:i') . ' WIB',
                'end_time' => $event->end_time?->toIso8601String(),
                'end_time_formatted' => $event->end_time?->translatedFormat('H:i') . ' WIB',
                'registration_deadline' => $event->registration_deadline?->toIso8601String(),
                'quota' => $event->quota,
                'registered_count' => (int) $event->active_registrations_count,
                'is_registration_open' => $event->isRegistrationOpen(),
                'my_registration' => $reg ? [
                    'id' => (string) $reg->id,
                    'ticket_code' => $reg->ticket_code,
                    'status' => $reg->status,
                    'payment_status' => $reg->payment_status,
                    'is_paid' => $reg->isPaid(),
                    'is_checked_in' => $reg->isCheckedIn(),
                    'checked_in_at' => $reg->checked_in_at?->translatedFormat('d M Y, H:i') . ' WIB',
                    'checkin_method' => $reg->checkin_method,
                    'gateway_qr_url' => $reg->gateway_qr_url,
                    'gateway_qr_string' => $reg->gateway_qr_string,
                    'gateway_expired_at' => $reg->gateway_expired_at?->toIso8601String(),
                ] : null,
            ];
        });

        // Also fetch my registered events (including attended)
        $myRegistrations = collect();
        if ($user !== null) {
            $myRegistrations = CommunityEventRegistration::with(['event'])
                ->where('user_id', $user->getAuthIdentifier())
                ->latest()
                ->get()
                ->map(fn (CommunityEventRegistration $r): array => [
                    'id' => (string) $r->id,
                    'ticket_code' => $r->ticket_code,
                    'status' => $r->status,
                    'payment_status' => $r->payment_status,
                    'is_paid' => $r->isPaid(),
                    'is_checked_in' => $r->isCheckedIn(),
                    'checked_in_at' => $r->checked_in_at?->translatedFormat('d M Y, H:i') . ' WIB',
                    'event' => $r->event ? [
                        'id' => (string) $r->event->id,
                        'title' => $r->event->title,
                        'banner_url' => $r->event->banner_url,
                        'event_type' => $r->event->event_type,
                        'is_online' => $r->event->isOnline(),
                        'location_name' => $r->event->location_name,
                        'start_time_formatted' => $r->event->start_time?->translatedFormat('l, d M Y • H:i') . ' WIB',
                    ] : null,
                ]);
        }

        return response()->json([
            'success' => true,
            'upcoming_events' => $formattedEvents,
            'my_registrations' => $myRegistrations,
        ]);
    }

    /**
     * Register for event (Free or Paid via QRIS).
     */
    public function registerEvent(Request $request, CommunityEvent $event): JsonResponse
    {
        $business = $this->resolveBusiness($request);
        $user = $request->user();
        abort_unless($user !== null, 403);

        if (! $event->isRegistrationOpen()) {
            return response()->json([
                'success' => false,
                'message' => 'Pendaftaran untuk event ini sudah ditutup atau kuota peserta telah penuh.',
            ], 422);
        }

        $registration = CommunityEventRegistration::where('event_id', $event->id)
            ->where('user_id', $user->getAuthIdentifier())
            ->first();

        $isPaidEvent = $event->isPaid();

        if ($registration) {
            if ($registration->status !== CommunityEventRegistration::STATUS_CANCELLED && $registration->isPaid()) {
                return response()->json([
                    'success' => true,
                    'message' => "Anda sudah terdaftar dan tiket sudah aktif untuk event '{$event->title}'.",
                    'registration' => $registration,
                ]);
            }

            if ($isPaidEvent && $registration->isPendingPayment() && ! $registration->isExpired()) {
                return response()->json([
                    'success' => true,
                    'is_paid_event' => true,
                    'message' => "Lanjutkan pembayaran QRIS untuk tiket event '{$event->title}'.",
                    'registration' => $registration,
                ]);
            }

            $registration->update([
                'status' => CommunityEventRegistration::STATUS_REGISTERED,
                'amount_paid' => $isPaidEvent ? (float) $event->price : 0,
                'payment_status' => $isPaidEvent ? CommunityEventRegistration::PAYMENT_STATUS_PENDING : CommunityEventRegistration::PAYMENT_STATUS_PAID,
                'payment_method' => 'qris',
                'payment_gateway' => 'tripay',
                'registered_at' => Carbon::now(),
            ]);
        } else {
            $ticketCode = $this->ticketService->generateTicketCode();
            $registration = CommunityEventRegistration::create([
                'event_id' => $event->id,
                'user_id' => $user->getAuthIdentifier(),
                'business_id' => $business->id,
                'ticket_code' => $ticketCode,
                'amount_paid' => $isPaidEvent ? (float) $event->price : 0,
                'payment_status' => $isPaidEvent ? CommunityEventRegistration::PAYMENT_STATUS_PENDING : CommunityEventRegistration::PAYMENT_STATUS_PAID,
                'payment_method' => 'qris',
                'payment_gateway' => 'tripay',
                'registered_at' => Carbon::now(),
                'status' => CommunityEventRegistration::STATUS_REGISTERED,
            ]);
        }

        if ($isPaidEvent) {
            $tripayRes = $this->tripayService->createEventRegistrationTransaction($registration, 'QRIS');
            if ($tripayRes['success'] ?? false) {
                $registration->update([
                    'gateway_reference' => $tripayRes['reference'] ?? null,
                    'gateway_pay_code' => $tripayRes['pay_code'] ?? null,
                    'gateway_qr_url' => $tripayRes['qr_url'] ?? null,
                    'gateway_qr_string' => $tripayRes['qr_string'] ?? null,
                    'gateway_fee' => (float) ($tripayRes['fee'] ?? 0),
                    'gateway_expired_at' => isset($tripayRes['expired_time'])
                        ? Carbon::createFromTimestamp($tripayRes['expired_time'])
                        : Carbon::now()->addMinutes(15),
                ]);
            }

            return response()->json([
                'success' => true,
                'is_paid_event' => true,
                'message' => "Silakan lakukan pembayaran QRIS untuk tiket event '{$event->title}'.",
                'registration' => $registration,
            ]);
        }

        $registration->update(['paid_at' => Carbon::now()]);

        return response()->json([
            'success' => true,
            'is_paid_event' => false,
            'message' => "Pendaftaran berhasil! Anda terdaftar pada event '{$event->title}' [Gratis]. Tiket digital siap digunakan.",
            'registration' => $registration,
        ]);
    }

    /**
     * Cancel an event registration.
     */
    public function cancelEventRegistration(Request $request, CommunityEvent $event): JsonResponse
    {
        $user = $request->user();
        abort_unless($user !== null, 403);

        $registration = CommunityEventRegistration::where('event_id', $event->id)
            ->where('user_id', $user->getAuthIdentifier())
            ->first();

        if (! $registration) {
            return response()->json(['success' => false, 'message' => 'Anda belum terdaftar pada event ini.'], 404);
        }

        if ($registration->isCheckedIn()) {
            return response()->json([
                'success' => false,
                'message' => 'Tiket yang sudah digunakan untuk check-in tidak dapat dibatalkan.',
            ], 422);
        }

        $registration->update([
            'status' => CommunityEventRegistration::STATUS_CANCELLED,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Pendaftaran event '{$event->title}' berhasil dibatalkan.",
        ]);
    }

    /**
     * Get digital ticket details.
     */
    public function getEventTicket(Request $request, CommunityEvent $event): JsonResponse
    {
        $user = $request->user();
        abort_unless($user !== null, 403);

        $registration = CommunityEventRegistration::with(['event', 'business', 'user'])
            ->where('event_id', $event->id)
            ->where('user_id', $user->getAuthIdentifier())
            ->where('status', '!=', CommunityEventRegistration::STATUS_CANCELLED)
            ->first();

        if (! $registration) {
            return response()->json([
                'success' => false,
                'message' => 'Tiket tidak ditemukan. Anda belum terdaftar pada event ini.',
            ], 404);
        }

        if ($event->isPaid() && ! $registration->isPaid()) {
            return response()->json([
                'success' => false,
                'is_pending_payment' => true,
                'registration_id' => $registration->id,
                'gateway_qr_url' => $registration->gateway_qr_url,
                'message' => "Tiket belum aktif karena pembayaran QRIS sebesar {$event->formatted_price} belum diselesaikan.",
            ], 402);
        }

        $qrCodeSvg = $this->ticketService->generateQrCodeSvg($registration->ticket_code, 240);
        $barcodeSvg = $this->ticketService->generateBarcodeSvg($registration->ticket_code, 320, 75);

        return response()->json([
            'success' => true,
            'ticket' => [
                'ticket_code' => $registration->ticket_code,
                'status' => $registration->status,
                'is_checked_in' => $registration->isCheckedIn(),
                'checked_in_at' => $registration->checked_in_at?->translatedFormat('d M Y, H:i') . ' WIB',
                'checkin_method' => $registration->checkin_method,
                'event_id' => (string) $event->id,
                'event_title' => $event->title,
                'event_type' => $event->event_type,
                'is_online' => $event->isOnline(),
                'is_offline' => $event->isOffline(),
                'price_type' => $event->price_type,
                'is_paid' => $event->isPaid(),
                'price' => (float) $event->price,
                'formatted_price' => $event->formatted_price,
                'online_meeting_url' => $event->online_meeting_url,
                'location_name' => $event->location_name,
                'location_address' => $event->location_address,
                'latitude' => $event->latitude,
                'longitude' => $event->longitude,
                'checkin_radius_meters' => $event->checkin_radius_meters,
                'start_time' => $event->start_time?->translatedFormat('l, d F Y • H:i') . ' WIB',
                'end_time' => $event->end_time?->translatedFormat('H:i') . ' WIB',
                'participant_name' => $user->name,
                'business_name' => $registration->business?->name ?? '',
                'qr_code_svg' => $qrCodeSvg,
                'barcode_svg' => $barcodeSvg,
            ],
        ]);
    }

    /**
     * Check-in via GPS Location.
     */
    public function checkinLocation(Request $request, CommunityEvent $event): JsonResponse
    {
        $user = $request->user();
        abort_unless($user !== null, 403);

        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $result = $this->checkinService->checkinViaLocation(
            $event,
            $user,
            (float) $validated['latitude'],
            (float) $validated['longitude']
        );

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * Check-in via Barcode / QR Code.
     */
    public function checkinBarcode(Request $request, CommunityEvent $event): JsonResponse
    {
        $user = $request->user();
        abort_unless($user !== null, 403);

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:128'],
        ]);

        $result = $this->checkinService->checkinViaBarcode(
            $event,
            $validated['code'],
            $user
        );

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * Simulate event QRIS payment in development / test environment.
     */
    public function simulateEventPayment(Request $request, CommunityEventRegistration $registration): JsonResponse
    {
        $user = $request->user();
        abort_unless($user !== null, 403);

        $registration->markAsPaid();

        AuditLog::create([
            'business_id' => $registration->business_id,
            'user_id' => $user->getAuthIdentifier(),
            'action' => 'community.event.payment_simulated',
            'auditable_type' => CommunityEventRegistration::class,
            'auditable_id' => $registration->id,
            'risk_level' => AuditLog::RISK_LOW,
            'notes' => "Simulasi pembayaran QRIS event: {$registration->event?->title}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Simulasi pembayaran QRIS berhasil! Tiket event '{$registration->event?->title}' sekarang lunas dan aktif.",
        ]);
    }
}
