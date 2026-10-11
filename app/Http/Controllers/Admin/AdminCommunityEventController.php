<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Community\EventCheckinService;
use App\Domain\Community\EventTicketService;
use App\Domain\Storage\AdminStorage;
use App\Http\Controllers\Controller;
use App\Models\CommunityEvent;
use App\Models\CommunityEventRegistration;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class AdminCommunityEventController extends Controller
{
    public function __construct(
        private readonly EventCheckinService $checkinService = new EventCheckinService,
        private readonly EventTicketService $ticketService = new EventTicketService,
    ) {}

    /**
     * Display listing of all community events for platform admin.
     */
    public function index(Request $request): View
    {
        $status = $request->query('status', 'all');
        $search = $request->query('q');

        $query = CommunityEvent::withCount(['registrations', 'activeRegistrations'])
            ->with(['creatorAdmin']);

        if ($status === 'upcoming') {
            $query->where('end_time', '>=', Carbon::now());
        } elseif ($status === 'past') {
            $query->where('end_time', '<', Carbon::now());
        } elseif (in_array($status, [CommunityEvent::STATUS_PUBLISHED, CommunityEvent::STATUS_DRAFT, CommunityEvent::STATUS_CANCELLED, CommunityEvent::STATUS_COMPLETED], true)) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search): void {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('location_name', 'like', "%{$search}%")
                  ->orWhere('checkin_code', 'like', "%{$search}%");
            });
        }

        $events = $query->orderBy('start_time', 'desc')->paginate(15)->withQueryString();

        $stats = [
            'total' => CommunityEvent::count(),
            'upcoming' => CommunityEvent::where('end_time', '>=', Carbon::now())->where('status', CommunityEvent::STATUS_PUBLISHED)->count(),
            'total_participants' => CommunityEventRegistration::where('status', '!=', CommunityEventRegistration::STATUS_CANCELLED)->count(),
            'total_attended' => CommunityEventRegistration::where('status', CommunityEventRegistration::STATUS_ATTENDED)->count(),
        ];

        return view('admin.events.index', compact('events', 'stats', 'status', 'search'));
    }

    /**
     * Show form for creating a new community event.
     */
    public function create(): View
    {
        return view('admin.events.create');
    }

    /**
     * Store a newly created community event.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'event_type' => ['required', 'in:offline,online'],
            'price_type' => ['nullable', 'in:free,paid'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'online_meeting_url' => ['nullable', 'url', 'max:1000'],
            'location_name' => ['required', 'string', 'max:255'],
            'location_address' => ['nullable', 'string', 'max:1000'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'checkin_radius_meters' => ['nullable', 'integer', 'min:10', 'max:10000'],
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date', 'after:start_time'],
            'registration_deadline' => ['required', 'date'],
            'quota' => ['nullable', 'integer', 'min:1'],
            'status' => ['required', 'in:draft,published,cancelled,completed'],
            'banner_image' => ['nullable', 'file', 'image', 'mimes:jpeg,png,webp,jpg', 'max:5120'],
        ]);

        $bannerPath = null;
        if ($request->hasFile('banner_image')) {
            $bannerPath = $request->file('banner_image')->store('admin/events', 'public');
        }

        $priceType = $validated['price_type'] ?? CommunityEvent::PRICE_TYPE_FREE;
        $price = ($priceType === CommunityEvent::PRICE_TYPE_PAID) ? (float) ($validated['price'] ?? 0) : 0.00;
        $onlineMeetingUrl = ($validated['event_type'] === CommunityEvent::TYPE_ONLINE) ? ($validated['online_meeting_url'] ?? null) : null;

        $slug = Str::slug($validated['title']) . '-' . strtolower(Str::random(5));
        $checkinCode = $this->ticketService->generateEventCheckinCode();

        $event = CommunityEvent::create([
            'title' => $validated['title'],
            'slug' => $slug,
            'description' => $validated['description'],
            'banner_image' => $bannerPath,
            'event_type' => $validated['event_type'],
            'price_type' => $priceType,
            'price' => $price,
            'online_meeting_url' => $onlineMeetingUrl,
            'location_name' => $validated['location_name'],
            'location_address' => $validated['location_address'] ?? null,
            'latitude' => isset($validated['latitude']) ? (float) $validated['latitude'] : null,
            'longitude' => isset($validated['longitude']) ? (float) $validated['longitude'] : null,
            'checkin_radius_meters' => (int) ($validated['checkin_radius_meters'] ?? 200),
            'start_time' => Carbon::parse($validated['start_time']),
            'end_time' => Carbon::parse($validated['end_time']),
            'registration_deadline' => Carbon::parse($validated['registration_deadline']),
            'quota' => isset($validated['quota']) ? (int) $validated['quota'] : null,
            'checkin_code' => $checkinCode,
            'status' => $validated['status'],
            'created_by_admin_id' => $request->user('admin')?->id,
        ]);

        return redirect()->route('admin.events.show', $event)->with('success', "Event '{$event->title}' berhasil dibuat.");
    }

    /**
     * Display event details, participants list, and live check-in scanner.
     */
    public function show(Request $request, CommunityEvent $event): View
    {
        $event->loadCount(['registrations', 'activeRegistrations']);

        $participantQuery = $event->registrations()
            ->with(['user', 'business'])
            ->latest('registered_at');

        if ($request->filled('participant_status')) {
            $participantQuery->where('status', $request->query('participant_status'));
        }

        if ($request->filled('q')) {
            $q = $request->query('q');
            $participantQuery->where(function ($sub) use ($q): void {
                $sub->where('ticket_code', 'like', "%{$q}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%"))
                    ->orWhereHas('business', fn ($b) => $b->where('name', 'like', "%{$q}%"));
            });
        }

        $participants = $participantQuery->paginate(20)->withQueryString();

        $registeredCount = $event->activeRegistrations()->count();
        $attendedCount = $event->registrations()->where('status', CommunityEventRegistration::STATUS_ATTENDED)->count();
        $attendanceRate = $registeredCount > 0 ? round(($attendedCount / $registeredCount) * 100, 1) : 0;

        $eventQrSvg = $this->ticketService->generateQrCodeSvg($event->checkin_code, 200);

        return view('admin.events.show', compact('event', 'participants', 'registeredCount', 'attendedCount', 'attendanceRate', 'eventQrSvg'));
    }

    /**
     * Show form for editing an existing community event.
     */
    public function edit(CommunityEvent $event): View
    {
        return view('admin.events.edit', compact('event'));
    }

    /**
     * Update an existing community event.
     */
    public function update(Request $request, CommunityEvent $event): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'event_type' => ['required', 'in:offline,online'],
            'price_type' => ['nullable', 'in:free,paid'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'online_meeting_url' => ['nullable', 'url', 'max:1000'],
            'location_name' => ['required', 'string', 'max:255'],
            'location_address' => ['nullable', 'string', 'max:1000'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'checkin_radius_meters' => ['nullable', 'integer', 'min:10', 'max:10000'],
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date', 'after:start_time'],
            'registration_deadline' => ['required', 'date'],
            'quota' => ['nullable', 'integer', 'min:1'],
            'status' => ['required', 'in:draft,published,cancelled,completed'],
            'banner_image' => ['nullable', 'file', 'image', 'mimes:jpeg,png,webp,jpg', 'max:5120'],
        ]);

        if ($request->hasFile('banner_image')) {
            $bannerPath = $request->file('banner_image')->store('admin/events', 'public');
            $event->banner_image = $bannerPath;
        }

        $priceType = $validated['price_type'] ?? CommunityEvent::PRICE_TYPE_FREE;
        $price = ($priceType === CommunityEvent::PRICE_TYPE_PAID) ? (float) ($validated['price'] ?? 0) : 0.00;
        $onlineMeetingUrl = ($validated['event_type'] === CommunityEvent::TYPE_ONLINE) ? ($validated['online_meeting_url'] ?? null) : null;

        $event->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'event_type' => $validated['event_type'],
            'price_type' => $priceType,
            'price' => $price,
            'online_meeting_url' => $onlineMeetingUrl,
            'location_name' => $validated['location_name'],
            'location_address' => $validated['location_address'] ?? null,
            'latitude' => isset($validated['latitude']) ? (float) $validated['latitude'] : null,
            'longitude' => isset($validated['longitude']) ? (float) $validated['longitude'] : null,
            'checkin_radius_meters' => (int) ($validated['checkin_radius_meters'] ?? 200),
            'start_time' => Carbon::parse($validated['start_time']),
            'end_time' => Carbon::parse($validated['end_time']),
            'registration_deadline' => Carbon::parse($validated['registration_deadline']),
            'quota' => isset($validated['quota']) ? (int) $validated['quota'] : null,
            'status' => $validated['status'],
        ]);

        return redirect()->route('admin.events.show', $event)->with('success', "Event '{$event->title}' berhasil diperbarui.");
    }

    /**
     * Delete an existing community event.
     */
    public function destroy(CommunityEvent $event): RedirectResponse
    {
        $title = $event->title;
        $event->delete();

        return redirect()->route('admin.events.index')->with('success', "Event '{$title}' berhasil dihapus.");
    }

    /**
     * Check-in participant by admin (via Barcode scan or manual button).
     */
    public function checkin(Request $request, CommunityEvent $event): JsonResponse|RedirectResponse
    {
        // 1. Scan via Barcode ticket code
        if ($request->filled('ticket_code')) {
            $code = $request->string('ticket_code')->toString();
            $result = $this->checkinService->checkinViaBarcode($event, $code);

            if ($request->wantsJson()) {
                return response()->json($result, $result['success'] ? 200 : 422);
            }

            return back()->with($result['success'] ? 'success' : 'error', $result['message']);
        }

        // 2. Manual toggle by registration ID
        if ($request->filled('registration_id')) {
            $registration = CommunityEventRegistration::where('event_id', $event->id)
                ->where('id', $request->string('registration_id')->toString())
                ->firstOrFail();

            if ($registration->isCheckedIn()) {
                $registration->update([
                    'status' => CommunityEventRegistration::STATUS_REGISTERED,
                    'checked_in_at' => null,
                    'checkin_method' => null,
                ]);
                $msg = 'Status kehadiran peserta diubah kembali menjadi belum check-in.';
            } else {
                $this->checkinService->checkinManual($registration);
                $msg = 'Peserta berhasil di-check-in secara manual.';
            }

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => $msg]);
            }

            return back()->with('success', $msg);
        }

        abort(422, 'Parameter ticket_code atau registration_id dibutuhkan.');
    }

    /**
     * Export participant roster to CSV.
     */
    public function export(CommunityEvent $event): Response
    {
        $participants = $event->registrations()
            ->with(['user', 'business'])
            ->orderBy('registered_at', 'asc')
            ->get();

        $csvHeader = [
            'No',
            'Kode Tiket',
            'Nama Peserta',
            'Email',
            'WhatsApp / HP',
            'Workspace Bisnis',
            'Waktu Pendaftaran',
            'Status',
            'Waktu Check-In',
            'Metode Check-In',
        ];

        $rows = [];
        $rows[] = implode(',', array_map(fn ($h) => '"' . str_replace('"', '""', $h) . '"', $csvHeader));

        foreach ($participants as $idx => $reg) {
            $rows[] = implode(',', array_map(fn ($val) => '"' . str_replace('"', '""', (string) $val) . '"', [
                $idx + 1,
                $reg->ticket_code,
                $reg->user->name ?? '-',
                $reg->user->email ?? '-',
                $reg->user->phone ?? '-',
                $reg->business->name ?? '-',
                $reg->registered_at->format('Y-m-d H:i:s'),
                $reg->status,
                $reg->checked_in_at ? $reg->checked_in_at->format('Y-m-d H:i:s') : '-',
                $reg->checkin_method ?? '-',
            ]));
        }

        $csvContent = "\xEF\xBB\xBF" . implode("\r\n", $rows);
        $filename = 'pendaftar-event-' . Str::slug($event->title) . '-' . date('Ymd_His') . '.csv';

        return response($csvContent, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
