<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Billing\EntitlementService;
use App\Domain\Community\ContentModerationService;
use App\Domain\Community\EventCheckinService;
use App\Domain\Community\EventTicketService;
use App\Domain\Community\TrendingTopicService;
use App\Domain\Payment\TripayService;
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
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

final class CommunityWebController extends Controller
{
    public const MAX_IMAGE_BYTES = 5 * 1024 * 1024; // 5 MB

    public function __construct(
        private readonly OwnerStorageQuotaService $storageQuota = new OwnerStorageQuotaService,
        private readonly EventCheckinService $checkinService = new EventCheckinService,
        private readonly EventTicketService $ticketService = new EventTicketService,
        private readonly TrendingTopicService $trendingService = new TrendingTopicService,
        private readonly ContentModerationService $moderationService = new ContentModerationService,
        private readonly EntitlementService $entitlementService = new EntitlementService,
        private readonly TripayService $tripayService = new TripayService,
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

        // Subscription access check (Wajib subscribe untuk posting, komentar, dan repost)
        $isSubscribed = $this->entitlementService->canParticipateInCommunity($business, $user);

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
            'unreadNotificationsCount',
            'isSubscribed'
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
     * Register for a community event.
     * Untuk event berbayar, pembayaran via QRIS seperti langganan SaaS.
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

        $isPaidEvent = $event->isPaid();

        if ($registration) {
            // Jika sudah terdaftar dan lunas
            if ($registration->status !== CommunityEventRegistration::STATUS_CANCELLED && $registration->isPaid()) {
                $msg = "Anda sudah terdaftar dan tiket sudah aktif untuk event '{$event->title}'.";
                if ($request->wantsJson()) {
                    return response()->json(['success' => true, 'message' => $msg, 'registration' => $registration]);
                }
                return back()->with('info', $msg);
            }

            // Jika statusnya pending payment untuk event berbayar
            if ($isPaidEvent && $registration->isPendingPayment() && ! $registration->isExpired()) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'is_paid_event' => true,
                        'redirect_url' => route('community.events.payment.show', $registration),
                        'message' => "Lanjutkan pembayaran QRIS untuk tiket event '{$event->title}'.",
                    ]);
                }
                return redirect()->route('community.events.payment.show', $registration);
            }

            // Re-activate registration
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

        // EVENT BERBAYAR: Inisialisasi Transaksi QRIS seperti Pembayaran Subscribe
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

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'is_paid_event' => true,
                    'redirect_url' => route('community.events.payment.show', $registration),
                    'message' => "Silakan scan kode QRIS untuk menyelesaikan pembayaran tiket event '{$event->title}'.",
                    'registration' => $registration,
                ]);
            }

            return redirect()->route('community.events.payment.show', $registration)
                ->with('info', "Silakan scan kode QRIS untuk menyelesaikan pembayaran tiket event '{$event->title}'.");
        }

        // EVENT GRATIS: Langsung aktif
        $registration->update(['paid_at' => Carbon::now()]);
        $successMsg = "Pendaftaran berhasil! Anda terdaftar pada event '{$event->title}' [Gratis]. Tiket digital dan barcode check-in siap digunakan.";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'is_paid_event' => false,
                'message' => $successMsg,
                'registration' => $registration,
            ]);
        }

        return back(fallback: route('community.events.index'))->with('success', $successMsg);
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
     * Show event QRIS payment page (Identical to Subscription QRIS Payment).
     */
    public function showEventPayment(Request $request, CommunityEventRegistration $registration): View|RedirectResponse
    {
        $user = $request->user();
        abort_unless($user !== null, 403);
        abort_unless((string) $registration->user_id === (string) $user->getAuthIdentifier(), 403);

        $registration->load(['event', 'business', 'user']);
        $event = $registration->event;

        // Jika sudah lunas, langsung arahkan ke events
        if ($registration->isPaid()) {
            return redirect()->route('community.events.index')
                ->with('success', "Pembayaran tiket event '{$event->title}' telah lunas. Tiket digital Anda siap digunakan!");
        }

        // Jika belum ada QR string (misal gagal saat inisialisasi awal), generate kembali
        if (empty($registration->gateway_qr_string) && ! $registration->isPaid()) {
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
        }

        // SVG QR Code untuk tampilan cadangan & visual instan
        $qrString = $registration->gateway_qr_string ?: $registration->ticket_code;
        $qrisSvg = $this->ticketService->generateQrCodeSvg($qrString, 280);

        return view('app.community.event_payment', compact('registration', 'event', 'qrisSvg'));
    }

    /**
     * Check real-time payment status of event registration (JSON polling).
     */
    public function checkEventPaymentStatus(Request $request, CommunityEventRegistration $registration): JsonResponse
    {
        $user = $request->user();
        abort_unless($user !== null, 403);
        abort_unless((string) $registration->user_id === (string) $user->getAuthIdentifier(), 403);

        // Jika menggunakan gateway TriPay dan ada gateway_reference dan belum paid, sinkronkan ke gateway
        if (! $registration->isPaid() && $registration->gateway_reference) {
            $gatewayDetail = $this->tripayService->getTransactionDetail($registration->gateway_reference);
            if ($gatewayDetail && in_array(strtoupper((string) ($gatewayDetail['status'] ?? '')), ['PAID', 'SETTLED', 'SUCCESS'], true)) {
                $registration->markAsPaid();
            }
        }

        return response()->json([
            'success' => true,
            'is_paid' => $registration->isPaid(),
            'payment_status' => $registration->payment_status,
            'ticket_code' => $registration->ticket_code,
            'paid_at' => $registration->paid_at?->translatedFormat('d M Y, H:i') . ' WIB',
            'redirect_url' => route('community.events.index'),
        ]);
    }

    /**
     * Simulate successful QRIS payment (Dev / Testing / Instant Sandbox).
     */
    public function simulateEventPayment(Request $request, CommunityEventRegistration $registration): RedirectResponse|JsonResponse
    {
        $user = $request->user();
        abort_unless($user !== null, 403);
        abort_unless((string) $registration->user_id === (string) $user->getAuthIdentifier(), 403);

        $registration->markAsPaid();

        $msg = "Simulasi pembayaran QRIS berhasil! Tiket event '{$registration->event->title}' sekarang lunas dan aktif.";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'redirect_url' => route('community.events.index'),
            ]);
        }

        return redirect()->route('community.events.index')->with('success', $msg);
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

        if ($event->isPaid() && ! $registration->isPaid()) {
            return response()->json([
                'success' => false,
                'is_pending_payment' => true,
                'payment_url' => route('community.events.payment.show', $registration),
                'message' => "Tiket belum aktif karena pembayaran QRIS sebesar {$event->formatted_price} belum diselesaikan. Silakan selesaikan pembayaran terlebih dahulu.",
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

        if (! $this->entitlementService->canParticipateInCommunity($business, $user)) {
            return back()->withInput()->with('error', 'Akses membuat postingan komunitas khusus untuk member yang telah berlangganan (subscribe) paket Cooca. Silakan aktifkan paket langganan bisnis Anda.');
        }

        $validated = $request->validate([
            'content' => ['required', 'string', 'max:5000'],
            'image' => ['nullable', 'file', 'image', 'mimes:jpeg,png,webp,gif', 'max:' . (self::MAX_IMAGE_BYTES / 1024)],
        ]);

        try {
            $cleanContent = $this->moderationService->validateOrFail($validated['content']);
        } catch (ValidationException $e) {
            $firstError = $e->validator->errors()->first('content') ?: 'Konten mengandung kata yang dilarang.';
            return back()->withInput()->withErrors($e->validator->errors())->with('error', $firstError);
        }

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
            'content' => $cleanContent,
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

        if (! $this->entitlementService->canParticipateInCommunity($business, $user)) {
            return response()->json([
                'success' => false,
                'code' => 'SUBSCRIPTION_REQUIRED',
                'message' => 'Akses repost postingan komunitas khusus untuk member yang telah berlangganan (subscribe). Silakan tingkatkan paket Anda untuk membagikan postingan.',
                'upgrade_url' => route('billing.limits'),
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
        $business = Context::business() ?? $user->activeBusiness ?? $user->businesses()->first();

        if (! $business || ! $this->entitlementService->canParticipateInCommunity($business, $user)) {
            return response()->json([
                'success' => false,
                'code' => 'SUBSCRIPTION_REQUIRED',
                'message' => 'Akses komentar komunitas khusus untuk member yang telah berlangganan (subscribe). Silakan tingkatkan paket Anda untuk ikut berdiskusi.',
                'upgrade_url' => route('billing.limits'),
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