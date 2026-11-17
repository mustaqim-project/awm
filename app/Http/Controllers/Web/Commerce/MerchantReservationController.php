<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Commerce;

use App\Domain\Commerce\Storefront\ReservationBookingService;
use App\Http\Controllers\Controller;
use App\Models\CommerceReservation;
use App\Models\Location;
use App\Models\PosTable;
use App\Models\Product;
use App\Support\Context;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

final class MerchantReservationController extends Controller
{
    public function __construct(
        private readonly ReservationBookingService $reservationService = new ReservationBookingService(),
    ) {}

    /**
     * Display merchant reservations dashboard & calendar list.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $business = Context::business();
        abort_unless($business, 404);
        abort_unless(Context::hasPermission('storefront.reservations.manage'), 403);

        if (! $business->hasIndustry()) {
            return redirect()->route('settings.index', ['tab' => 'modules'])
                ->with('error', 'Fitur reservasi dinonaktifkan karena bisnis belum memiliki klasifikasi industri.');
        }

        if (! $business->supportsReservation() || ! $business->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_RESERVATION)) {
            return redirect()->route('settings.index', ['tab' => 'modules'])
                ->with('error', 'Fitur reservasi atau booking tidak tersedia untuk sektor industri bisnis Anda.');
        }

        $statusTab = $request->query('tab', 'all');
        $selectedDate = $request->query('date', Carbon::today()->toDateString());
        $filterLocationId = $request->query('location_id');

        $query = CommerceReservation::where('business_id', $business->id)
            ->with(['posTable', 'product', 'location', 'staff', 'creator'])
            ->orderBy('reservation_date')
            ->orderBy('time_slot');

        if ($filterLocationId) {
            $query->where('location_id', $filterLocationId);
        }

        if ($statusTab === 'pending') {
            $query->where('status', CommerceReservation::STATUS_PENDING_CONFIRMATION);
        } elseif ($statusTab === 'confirmed') {
            $query->whereIn('status', [CommerceReservation::STATUS_CONFIRMED, CommerceReservation::STATUS_SEATED]);
        } elseif ($statusTab === 'today') {
            $query->whereDate('reservation_date', Carbon::today()->toDateString());
        } elseif ($statusTab === 'completed') {
            $query->where('status', CommerceReservation::STATUS_COMPLETED);
        } elseif ($statusTab === 'cancelled') {
            $query->whereIn('status', [CommerceReservation::STATUS_CANCELLED, CommerceReservation::STATUS_NO_SHOW]);
        } elseif ($statusTab === 'dp_paid') {
            $query->whereIn('payment_status', [CommerceReservation::PAYMENT_DP_PAID, CommerceReservation::PAYMENT_PAID_IN_FULL]);
        } elseif ($statusTab === 'unpaid') {
            $query->where('payment_status', CommerceReservation::PAYMENT_UNPAID);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search): void {
                $q->where('reservation_code', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%")
                    ->orWhere('vehicle_plate', 'like', "%{$search}%");
            });
        }

        $perPage = \App\Support\PaginationHelper::perPage($request, 25);
        $reservations = $query->paginate($perPage)->withQueryString();

        $pendingCount = CommerceReservation::where('business_id', $business->id)
            ->where('status', CommerceReservation::STATUS_PENDING_CONFIRMATION)
            ->count();

        $todayCount = CommerceReservation::where('business_id', $business->id)
            ->whereDate('reservation_date', Carbon::today()->toDateString())
            ->whereIn('status', [
                CommerceReservation::STATUS_PENDING_CONFIRMATION,
                CommerceReservation::STATUS_CONFIRMED,
                CommerceReservation::STATUS_SEATED,
            ])
            ->count();

        $dpCollectedSum = (float) CommerceReservation::where('business_id', $business->id)
            ->whereIn('payment_status', [CommerceReservation::PAYMENT_DP_PAID, CommerceReservation::PAYMENT_PAID_IN_FULL])
            ->sum('dp_amount');

        $dpPaidCount = CommerceReservation::where('business_id', $business->id)
            ->whereIn('payment_status', [CommerceReservation::PAYMENT_DP_PAID, CommerceReservation::PAYMENT_PAID_IN_FULL])
            ->count();

        $tables = PosTable::where('business_id', $business->id)
            ->where('is_active', true)
            ->orderBy('table_number')
            ->get();

        $locations = Location::where('business_id', $business->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $staffList = $business->users()
            ->select('users.id', 'users.name', 'users.email')
            ->get();

        $services = Product::where('business_id', $business->id)
            ->where('is_active', true)
            ->where('type', 'service')
            ->orderBy('name')
            ->get();

        $isFnB = $business->hasDineInFeature() || ($business->isFoodIndustry() && in_array($business->template_code, ['fnb_resto', 'fnb_cafe'], true));
        $isWorkshop = $business->isWorkshop();
        $isBarbershop = $business->isBarbershop();
        $isEvent = $business->isEventService();
        $isTailor = $business->isTailor();

        $activeServicesCount = $services->count();

        return view('app.storefront.reservations.index', compact(
            'business',
            'reservations',
            'statusTab',
            'pendingCount',
            'todayCount',
            'dpCollectedSum',
            'dpPaidCount',
            'tables',
            'locations',
            'staffList',
            'services',
            'selectedDate',
            'filterLocationId',
            'isFnB',
            'isWorkshop',
            'isBarbershop',
            'isEvent',
            'isTailor',
            'activeServicesCount'
        ));
    }

    /**
     * Store a manual reservation directly from merchant backoffice / cashier.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $business = Context::business();
        abort_unless($business, 404);
        abort_unless(Context::hasPermission('storefront.reservations.manage'), 403);

        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'min:2', 'max:150'],
            'customer_phone' => ['required', 'string', 'min:8', 'max:30'],
            'customer_email' => ['nullable', 'email', 'max:150'],
            'reservation_date' => ['required', 'date'],
            'time_slot' => ['required', 'string', 'max:50'],
            'guest_count' => ['nullable', 'integer', 'min:1', 'max:500'],
            'location_id' => ['nullable', 'uuid', 'exists:locations,id'],
            'pos_table_id' => ['nullable', 'uuid', 'exists:pos_tables,id'],
            'product_id' => ['nullable', 'uuid', 'exists:products,id'],
            'staff_id' => ['nullable', 'uuid', 'exists:users,id'],
            'vehicle_plate' => ['nullable', 'string', 'max:30'],
            'vehicle_model' => ['nullable', 'string', 'max:100'],
            'service_duration_minutes' => ['nullable', 'integer', 'min:5', 'max:720'],
            'event_venue' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'estimated_total' => ['nullable', 'numeric', 'min:0'],
            'pay_dp_now' => ['nullable'],
            'dp_amount' => ['nullable', 'numeric', 'min:0'],
            'dp_payment_method' => ['nullable', 'string', 'max:50'],
            'dp_payment_reference' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string', 'in:pending_confirmation,confirmed'],
        ]);

        $payDpNow = $request->boolean('pay_dp_now') || (! empty($validated['dp_amount']) && (float) $validated['dp_amount'] > 0);
        $dpAmount = $payDpNow ? (float) ($validated['dp_amount'] ?? 0) : 0.0;

        $extraData = [
            'location_id' => $validated['location_id'] ?? null,
            'staff_id' => $validated['staff_id'] ?? null,
            'created_by' => $request->user()?->id,
            'vehicle_plate' => $validated['vehicle_plate'] ?? null,
            'vehicle_model' => $validated['vehicle_model'] ?? null,
            'service_duration_minutes' => isset($validated['service_duration_minutes']) ? (int) $validated['service_duration_minutes'] : null,
            'event_venue' => $validated['event_venue'] ?? null,
            'estimated_total' => (float) ($validated['estimated_total'] ?? 0),
            'dp_amount' => $dpAmount,
            'dp_payment_method' => $validated['dp_payment_method'] ?? 'cash',
            'dp_payment_reference' => $validated['dp_payment_reference'] ?? null,
            'status' => $validated['status'] ?? CommerceReservation::STATUS_CONFIRMED,
            'source' => CommerceReservation::SOURCE_MANUAL,
        ];

        try {
            $reservation = $this->reservationService->createReservation(
                business: $business,
                customerData: [
                    'name' => $validated['customer_name'],
                    'phone' => $validated['customer_phone'],
                    'email' => $validated['customer_email'] ?? null,
                ],
                date: $validated['reservation_date'],
                timeSlot: $validated['time_slot'],
                guestCount: (int) ($validated['guest_count'] ?? 1),
                tableId: $validated['pos_table_id'] ?? null,
                productId: $validated['product_id'] ?? null,
                notes: $validated['notes'] ?? null,
                extraData: $extraData
            );

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Reservasi #{$reservation->reservation_code} berhasil ditambahkan.",
                    'reservation' => $reservation,
                ]);
            }

            return redirect()->route('storefront.reservations.index')->with('success', "Reservasi manual #{$reservation->reservation_code} untuk {$reservation->customer_name} berhasil dibuat.");
        } catch (Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return redirect()->route('storefront.reservations.index')->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Record or update Down Payment (DP) for an existing reservation.
     */
    public function recordDp(Request $request, CommerceReservation $reservation): RedirectResponse|JsonResponse
    {
        $business = Context::business();
        abort_unless($business && $reservation->business_id === $business->id, 403);
        abort_unless(Context::hasPermission('storefront.reservations.manage'), 403);

        $validated = $request->validate([
            'dp_amount' => ['required', 'numeric', 'min:1000'],
            'dp_payment_method' => ['required', 'string', 'max:50'],
            'dp_payment_reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $updated = $this->reservationService->recordDpPayment(
                reservation: $reservation,
                dpAmount: (float) $validated['dp_amount'],
                paymentMethod: $validated['dp_payment_method'],
                reference: $validated['dp_payment_reference'] ?? null,
                notes: $validated['notes'] ?? null
            );

            $formattedDp = number_format((float) $validated['dp_amount'], 0, ',', '.');

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Pembayaran DP Rp {$formattedDp} untuk reservasi #{$reservation->reservation_code} berhasil dicatat.",
                    'reservation' => $updated,
                ]);
            }

            return redirect()->route('storefront.reservations.index')->with('success', "Pembayaran DP Rp {$formattedDp} untuk reservasi #{$reservation->reservation_code} berhasil dicatat.");
        } catch (Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return redirect()->route('storefront.reservations.index')->with('error', $e->getMessage());
        }
    }

    /**
     * Update reservation status (confirm, seated, complete, cancel).
     */
    public function updateStatus(Request $request, CommerceReservation $reservation): RedirectResponse
    {
        $business = Context::business();
        abort_unless($business && $reservation->business_id === $business->id, 403);
        abort_unless(Context::hasPermission('storefront.reservations.manage'), 403);

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:confirmed,seated,completed,cancelled,no_show'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $this->reservationService->updateReservationStatus(
                reservation: $reservation,
                status: $validated['status'],
                reason: $validated['reason'] ?? null
            );

            return back()->with('success', "Status reservasi #{$reservation->reservation_code} berhasil diperbarui.");
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Assign a table to a reservation.
     */
    public function assignTable(Request $request, CommerceReservation $reservation): RedirectResponse
    {
        $business = Context::business();
        abort_unless($business && $reservation->business_id === $business->id, 403);
        abort_unless(Context::hasPermission('storefront.reservations.manage'), 403);

        $validated = $request->validate([
            'pos_table_id' => ['required', 'uuid', 'exists:pos_tables,id'],
        ]);

        try {
            $table = PosTable::where('business_id', $business->id)->findOrFail($validated['pos_table_id']);
            $this->reservationService->assignTable($reservation, $table);

            return back()->with('success', "Meja #{$table->table_number} ({$table->name}) berhasil dialokasikan untuk reservasi {$reservation->reservation_code}.");
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
