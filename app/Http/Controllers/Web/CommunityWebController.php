<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Community\EventCheckinService;
use App\Domain\Community\EventTicketService;
use App\Domain\Community\TrendingTopicService;
use App\Domain\Storage\OwnerStorageQuotaService;
use App\Domain\Storage\TenantStorage;
use App\Http\Controllers\Controller;
use App\Models\CommunityComment;
use App\Models\CommunityEvent;
use App\Models\CommunityEventRegistration;
use App\Models\CommunityLike;
use App\Models\CommunityNotification;
use App\Models\CommunityPost;
use App\Models\CommunityRepost;
use App\Support\Context;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class CommunityWebController extends Controller
{
    public const MAX_IMAGE_BYTES = 5 * 1024 * 1024; // 5 MB

    public function __construct(
        private readonly OwnerStorageQuotaService $storageQuota = new OwnerStorageQuotaService,
        private readonly EventCheckinService $checkinService = new EventCheckinService,
        private readonly EventTicketService $ticketService = new EventTicketService,
        private readonly TrendingTopicService $trendingService = new TrendingTopicService,
    ) {}

    /**
     * Display the owner community feed & upcoming events banner.
     */
    public function index(Request $request): View
    {
        $business = Context::requireBusiness();
        $user = Context::user();

        // Parameter navigasi & filter
        $activeTab = $request->query('tab', 'feed'); // 'feed', 'my_posts', 'notifications'
        if (! in_array($activeTab, ['feed', 'my_posts', 'notifications'], true)) {
            $activeTab = 'feed';
        }
        $activeTopic = $request->query('topic');
        $activeSearch = $request->query('q');

        // 1. Fetch upcoming active events (yang sudah lewat jangan tampilkan!)
        $upcomingEvents = CommunityEvent::published()
            ->upcoming()
            ->withCount('activeRegistrations')
            ->with(['activeRegistrations' => function ($q) use ($user): void {
                if ($user !== null) {
                    $q->where('user_id', $user->getAuthIdentifier());
                }
            }])
            ->take(6)
            ->get();

        // Attach registration state for current user
        if ($user !== null) {
            $userRegistrations = CommunityEventRegistration::where('user_id', $user->getAuthIdentifier())
                ->whereIn('event_id', $upcomingEvents->pluck('id'))
                ->get()
                ->keyBy('event_id');

            $upcomingEvents->each(function (CommunityEvent $event) use ($userRegistrations): void {
                $event->my_registration = $userRegistrations->get($event->id);
            });
        }

        // 2. Fetch community discussion feed with dynamic tabs & topic filters
        $postsQuery = CommunityPost::with(['owner', 'business', 'comments.user', 'reposts.user'])
            ->withCount('likes', 'comments', 'reposts');

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

        $posts = $postsQuery->latest()->paginate(12)->withQueryString();

        // Bubble "liked by me" and "reposted by me" state without N+1 lookups.
        if ($user !== null) {
            $likedPostIds = CommunityLike::where('user_id', $user->getAuthIdentifier())
                ->whereIn('post_id', $posts->pluck('id'))
                ->pluck('post_id')
                ->flip();

            $repostedPostIds = CommunityRepost::where('user_id', $user->getAuthIdentifier())
                ->whereIn('post_id', $posts->pluck('id'))
                ->pluck('post_id')
                ->flip();

            $posts->getCollection()->transform(function (CommunityPost $post) use ($likedPostIds, $repostedPostIds): CommunityPost {
                $post->liked_by_me = $likedPostIds->has($post->id);
                $post->reposted_by_me = $repostedPostIds->has($post->id);
                return $post;
            });
        }

        // 3. Analisa Top 5 Trending Topics dalam 24 Jam
        $trendingTopics = $this->trendingService->getTopTrendingTopics(5);

        // 4. Notifikasi Interaksi
        $notifications = collect();
        $unreadNotificationsCount = 0;
        if ($user !== null) {
            $notifications = CommunityNotification::with(['actor', 'post'])
                ->where('user_id', $user->getAuthIdentifier())
                ->latest()
                ->take(30)
                ->get();

            $unreadNotificationsCount = CommunityNotification::where('user_id', $user->getAuthIdentifier())
                ->where('is_read', false)
                ->count();
        }

        // Storage summary for the upload warning.
        $storageSummary = $this->storageQuota->getSummary($user);

        return view('app.community.index', compact(
            'business',
            'posts',
            'storageSummary',
            'upcomingEvents',
            'trendingTopics',
            'activeTab',
            'activeTopic',
            'activeSearch',
            'notifications',
            'unreadNotificationsCount'
        ));
    }

    /**
     * List all upcoming events & user's registered events.
     */
    public function events(Request $request): View
    {
        $business = Context::requireBusiness();
        $user = Context::user();

        // Filter: Yang sudah lewat jangan tampilkan untuk tab mendatang
        $upcomingEvents = CommunityEvent::published()
            ->upcoming()
            ->withCount('activeRegistrations')
            ->paginate(12)
            ->withQueryString();

        $myRegistrations = [];
        if ($user !== null) {
            $myRegistrations = CommunityEventRegistration::with(['event'])
                ->where('user_id', $user->getAuthIdentifier())
                ->latest()
                ->get();
        }

        return view('app.community.events', compact('business', 'upcomingEvents', 'myRegistrations'));
    }

    /**
     * Register for a community event (1-click registration).
     */
    public function registerEvent(Request $request, CommunityEvent $event): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();
        $user = $request->user();
        abort_unless($user !== null, 403);

        if (! $event->isRegistrationOpen()) {
            $msg = 'Pendaftaran untuk event ini sudah ditutup atau kuota peserta telah penuh.';
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        $registration = CommunityEventRegistration::where('event_id', $event->id)
            ->where('user_id', $user->getAuthIdentifier())
            ->first();

        if ($registration) {
            if ($registration->status === CommunityEventRegistration::STATUS_CANCELLED) {
                $registration->update([
                    'status' => CommunityEventRegistration::STATUS_REGISTERED,
                    'amount_paid' => $event->isPaid() ? (float) $event->price : 0,
                    'payment_status' => 'paid',
                    'registered_at' => Carbon::now(),
                ]);
            }
        } else {
            $ticketCode = $this->ticketService->generateTicketCode();
            $registration = CommunityEventRegistration::create([
                'event_id' => $event->id,
                'user_id' => $user->getAuthIdentifier(),
                'business_id' => $business->id,
                'ticket_code' => $ticketCode,
                'amount_paid' => $event->isPaid() ? (float) $event->price : 0,
                'payment_status' => 'paid',
                'registered_at' => Carbon::now(),
                'status' => CommunityEventRegistration::STATUS_REGISTERED,
            ]);
        }

        $priceNote = $event->isPaid() ? " [Tiket {$event->formatted_price}]" : " [Gratis]";
        $successMsg = "Pendaftaran berhasil! Anda terdaftar pada event '{$event->title}'{$priceNote}. Tiket digital dan barcode check-in siap digunakan.";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $successMsg,
                'registration' => $registration,
            ]);
        }

        return back()->with('success', $successMsg);
    }

    /**
     * Cancel an event registration.
     */
    public function cancelEventRegistration(Request $request, CommunityEvent $event): RedirectResponse|JsonResponse
    {
        $user = $request->user();
        abort_unless($user !== null, 403);

        $registration = CommunityEventRegistration::where('event_id', $event->id)
            ->where('user_id', $user->getAuthIdentifier())
            ->first();

        if (! $registration) {
            $msg = 'Anda belum terdaftar pada event ini.';
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 404);
            }
            return back()->with('error', $msg);
        }

        if ($registration->isCheckedIn()) {
            $msg = 'Anda sudah melakukan check-in pada event ini, pendaftaran tidak dapat dibatalkan.';
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        $registration->update(['status' => CommunityEventRegistration::STATUS_CANCELLED]);

        $msg = "Pendaftaran untuk event '{$event->title}' telah dibatalkan.";
        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $msg]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Get digital ticket details with SVG Barcode and SVG QR Code.
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
                'start_time' => $event->start_time->translatedFormat('l, d F Y • H:i') . ' WIB',
                'end_time' => $event->end_time->translatedFormat('H:i') . ' WIB',
                'participant_name' => $user->name,
                'business_name' => $registration->business->name ?? '',
                'qr_code_svg' => $qrCodeSvg,
                'barcode_svg' => $barcodeSvg,
            ],
        ]);
    }

    /**
     * Check-in peserta ke event via Geolocation GPS lokasi.
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
     * Check-in peserta ke event via Barcode / QR Code.
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
     * Store a new community post (text + optional image).
     */
    public function store(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();
        $user = $request->user();
        abort_unless($user !== null, 403);

        $validated = $request->validate([
            'content' => ['required', 'string', 'max:5000'],
            'image' => ['nullable', 'file', 'image', 'mimes:jpeg,png,webp,gif', 'max:' . (self::MAX_IMAGE_BYTES / 1024)],
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $trackingService = app(\App\Domain\Storage\StorageTrackingService::class);
            $owner = app(\App\Domain\Storage\OwnerStorageQuotaService::class)->ownerForBusiness($business) ?? $user;
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
                category: \App\Models\StorageFile::CATEGORY_COMMUNITY_IMAGE,
                module: 'community',
                owner: $owner,
                business: $business,
                uploader: $user
            );
        }

        CommunityPost::create([
            'owner_id' => $user->getAuthIdentifier(),
            'business_id' => $business->id,
            'content' => $validated['content'],
            'image_path' => $imagePath,
        ]);

        return back()->with('success', 'Postingan komunitas berhasil dibagikan.');
    }

    /**
     * Toggle like on a community post (JSON).
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

            // Trigger notification to post author if not liking own post
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
     * Toggle repost on a community post (JSON).
     */
    public function toggleRepost(Request $request, CommunityPost $post): JsonResponse
    {
        $user = $request->user();
        abort_unless($user !== null, 403);
        $business = Context::requireBusiness();

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

            // Trigger notification to post author if not self-repost
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
     * Store a comment on a community post (JSON).
     */
    public function comment(Request $request, CommunityPost $post): JsonResponse
    {
        $user = $request->user();
        abort_unless($user !== null, 403);

        $validated = $request->validate([
            'content' => ['required', 'string', 'max:1000'],
        ]);

        $comment = CommunityComment::create([
            'post_id' => $post->id,
            'user_id' => $user->getAuthIdentifier(),
            'content' => $validated['content'],
        ]);

        $comment->load(['user']);

        // Trigger notification to post author if not commenting on own post
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
                'id' => $comment->id,
                'content' => $comment->content,
                'created_at' => $comment->created_at->diffForHumans(),
                'user' => [
                    'name' => $comment->user->name ?? 'Owner',
                ],
            ],
            'comments_count' => $post->comments()->count(),
        ]);
    }

    /**
     * Mark all unread community notifications as read (JSON).
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
     * Delete a community post (only the author/owner).
     */
    public function destroy(Request $request, CommunityPost $post): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user !== null, 403);

        if ((string) $post->owner_id !== (string) $user->getAuthIdentifier()) {
            abort(403, 'Anda hanya dapat menghapus postingan milik sendiri.');
        }

        if ($post->image_path) {
            app(\App\Domain\Storage\StorageTrackingService::class)->deleteFile($post->image_path, 'public');
        }

        $post->delete();

        return back()->with('success', 'Postingan berhasil dihapus.');
    }
}