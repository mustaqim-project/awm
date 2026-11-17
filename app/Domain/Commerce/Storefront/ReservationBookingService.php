<?php

declare(strict_types=1);

namespace App\Domain\Commerce\Storefront;

use App\Models\Business;
use App\Models\CommerceOrder;
use App\Models\CommerceReservation;
use App\Models\CommerceStoreSetting;
use App\Models\Location;
use App\Models\PosTable;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use DomainException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

final class ReservationBookingService
{
    public function __construct(
        private readonly CommerceOrderService $orderService = new CommerceOrderService(),
    ) {}

    /**
     * Create a new reservation with table/service collision detection and multi-industry support.
     *
     * @param  array{
     *     name: string,
     *     phone: string,
     *     email?: string|null,
     * }  $customerData
     * @param  array{
     *     location_id?: string|null,
     *     staff_id?: string|null,
     *     created_by?: string|null,
     *     vehicle_plate?: string|null,
     *     vehicle_model?: string|null,
     *     service_duration_minutes?: int|null,
     *     event_venue?: string|null,
     *     estimated_total?: float|int|string|null,
     *     dp_amount?: float|int|string|null,
     *     dp_payment_method?: string|null,
     *     dp_payment_reference?: string|null,
     *     payment_status?: string|null,
     *     status?: string|null,
     *     source?: string|null,
     * }  $extraData
     */
    public function createReservation(
        Business $business,
        array $customerData,
        string $date,
        string $timeSlot,
        int $guestCount = 1,
        ?string $tableId = null,
        ?string $productId = null,
        ?string $notes = null,
        array $extraData = []
    ): CommerceReservation {
        $source = $extraData['source'] ?? CommerceReservation::SOURCE_ONLINE;

        // Storefront setting check applies to online submissions
        if ($source === CommerceReservation::SOURCE_ONLINE) {
            $setting = CommerceStoreSetting::where('business_id', $business->id)->first();
            if ($setting && ! $setting->allow_reservation) {
                throw new DomainException('Toko ini sedang tidak menerima layanan reservasi.');
            }
        }

        $custName = trim($customerData['name'] ?? '');
        $custPhone = $this->orderService->normalizePhone((string) ($customerData['phone'] ?? ''));

        if ($custName === '') {
            throw new InvalidArgumentException('Nama pemesan wajib diisi.');
        }

        if ($custPhone === '' || strlen($custPhone) < 9) {
            throw new InvalidArgumentException('Nomor WhatsApp pemesan tidak valid.');
        }

        $reservationDate = Carbon::parse($date)->toDateString();

        return DB::transaction(function () use (
            $business,
            $customerData,
            $custName,
            $custPhone,
            $reservationDate,
            $timeSlot,
            $guestCount,
            $tableId,
            $productId,
            $notes,
            $extraData,
            $source
        ): CommerceReservation {
            // 1. Collision detection for table (if assigned)
            if ($tableId) {
                $table = PosTable::where('business_id', $business->id)->find($tableId);
                if (! $table) {
                    throw new InvalidArgumentException('Meja yang dipilih tidak ditemukan.');
                }

                $collision = CommerceReservation::where('business_id', $business->id)
                    ->where('pos_table_id', $tableId)
                    ->whereDate('reservation_date', $reservationDate)
                    ->where('time_slot', $timeSlot)
                    ->whereIn('status', [
                        CommerceReservation::STATUS_PENDING_CONFIRMATION,
                        CommerceReservation::STATUS_CONFIRMED,
                        CommerceReservation::STATUS_SEATED,
                    ])
                    ->lockForUpdate()
                    ->exists();

                if ($collision) {
                    throw new DomainException("Meja #{$table->table_number} ({$table->name}) sudah direservasi pada tanggal dan slot waktu tersebut.");
                }
            }

            // 2. Validate product/service if provided
            if ($productId) {
                $product = Product::where('business_id', $business->id)->find($productId);
                if (! $product) {
                    throw new InvalidArgumentException('Layanan yang dipilih tidak ditemukan.');
                }
            }

            // 3. Validate staff if provided
            $staffId = $extraData['staff_id'] ?? null;
            if ($staffId) {
                $staff = User::find($staffId);
                if (! $staff) {
                    $staffId = null;
                }
            }

            // 4. Validate location if provided
            $locationId = $extraData['location_id'] ?? null;
            if ($locationId) {
                $loc = Location::where('business_id', $business->id)->find($locationId);
                if (! $loc) {
                    $locationId = null;
                }
            }

            // 5. Down payment calculations
            $estimatedTotal = max(0.0, (float) ($extraData['estimated_total'] ?? 0));
            $dpAmount = max(0.0, (float) ($extraData['dp_amount'] ?? 0));
            $dpPaymentMethod = $extraData['dp_payment_method'] ?? null;
            $dpPaymentReference = $extraData['dp_payment_reference'] ?? null;
            $dpPaidAt = null;

            if ($dpAmount > 0) {
                $dpPaidAt = now();
                $dpPaymentMethod = $dpPaymentMethod ?: 'cash';
                $paymentStatus = ($estimatedTotal > 0 && $dpAmount >= $estimatedTotal)
                    ? CommerceReservation::PAYMENT_PAID_IN_FULL
                    : CommerceReservation::PAYMENT_DP_PAID;
            } else {
                $paymentStatus = CommerceReservation::PAYMENT_UNPAID;
            }

            // Status: for manual submissions, if not explicitly provided, confirmed if DP is paid or if created by staff
            $defaultStatus = ($dpAmount > 0 || $source === CommerceReservation::SOURCE_MANUAL || $source === CommerceReservation::SOURCE_POS)
                ? CommerceReservation::STATUS_CONFIRMED
                : CommerceReservation::STATUS_PENDING_CONFIRMATION;

            $status = $extraData['status'] ?? $defaultStatus;

            // 6. Generate unique reservation code
            $datePrefix = Carbon::parse($reservationDate)->format('Ymd');
            $randomCode = strtoupper(Str::random(4));
            $reservationCode = "RSV-{$datePrefix}-{$randomCode}";

            $reservation = new CommerceReservation();
            $reservation->business_id = $business->id;
            $reservation->location_id = $locationId;
            $reservation->pos_table_id = $tableId;
            $reservation->product_id = $productId;
            $reservation->staff_id = $staffId;
            $reservation->created_by = $extraData['created_by'] ?? null;
            $reservation->reservation_code = $reservationCode;
            $reservation->customer_name = $custName;
            $reservation->customer_phone = $custPhone;
            $reservation->customer_email = $customerData['email'] ?? null;
            $reservation->reservation_date = $reservationDate;
            $reservation->time_slot = $timeSlot;
            $reservation->guest_count = max(1, $guestCount);
            $reservation->vehicle_plate = $extraData['vehicle_plate'] ?? null;
            $reservation->vehicle_model = $extraData['vehicle_model'] ?? null;
            $reservation->service_duration_minutes = isset($extraData['service_duration_minutes']) ? (int) $extraData['service_duration_minutes'] : null;
            $reservation->event_venue = $extraData['event_venue'] ?? null;
            $reservation->estimated_total = $estimatedTotal;
            $reservation->dp_amount = $dpAmount;
            $reservation->dp_payment_method = $dpPaymentMethod;
            $reservation->dp_paid_at = $dpPaidAt;
            $reservation->dp_payment_reference = $dpPaymentReference;
            $reservation->payment_status = $paymentStatus;
            $reservation->status = $status;
            $reservation->source = $source;
            $reservation->notes = $notes;
            $reservation->save();

            return $reservation->load(['posTable', 'product', 'location', 'staff', 'creator']);
        });
    }

    /**
     * Record Down Payment (DP) for an existing reservation.
     */
    public function recordDpPayment(
        CommerceReservation $reservation,
        float $dpAmount,
        string $paymentMethod,
        ?string $reference = null,
        ?string $notes = null
    ): CommerceReservation {
        if ($dpAmount <= 0) {
            throw new InvalidArgumentException('Nominal DP harus lebih besar dari 0.');
        }

        return DB::transaction(function () use ($reservation, $dpAmount, $paymentMethod, $reference, $notes): CommerceReservation {
            $reservation->markDpAsPaid($dpAmount, $paymentMethod, $reference);

            if ($notes) {
                $existingNotes = $reservation->notes ? $reservation->notes . "\n" : '';
                $reservation->update(['notes' => $existingNotes . '[Catatan DP]: ' . $notes]);
            }

            return $reservation->fresh(['posTable', 'product', 'location', 'staff', 'creator']);
        });
    }

    /**
     * Check if a specific slot or table is available.
     */
    public function checkSlotAvailability(
        Business $business,
        string $date,
        string $timeSlot,
        ?string $tableId = null
    ): bool {
        $reservationDate = Carbon::parse($date)->toDateString();

        $query = CommerceReservation::where('business_id', $business->id)
            ->whereDate('reservation_date', $reservationDate)
            ->where('time_slot', $timeSlot)
            ->whereIn('status', [
                CommerceReservation::STATUS_PENDING_CONFIRMATION,
                CommerceReservation::STATUS_CONFIRMED,
                CommerceReservation::STATUS_SEATED,
            ]);

        if ($tableId) {
            $query->where('pos_table_id', $tableId);
            return ! $query->exists();
        }

        // If no table is specified, check if all tables are booked
        $totalTables = PosTable::where('business_id', $business->id)->where('is_active', true)->count();
        if ($totalTables === 0) {
            return true; // Flexible reservation
        }

        $bookedTablesCount = (clone $query)->whereNotNull('pos_table_id')->count();
        return $bookedTablesCount < $totalTables;
    }

    /**
     * Get list of currently available tables for a specific date, time slot, and guest count.
     *
     * @return Collection<int, PosTable>
     */
    public function getAvailableTables(
        Business $business,
        string $date,
        string $timeSlot,
        int $guestCount = 1
    ): Collection {
        $reservationDate = Carbon::parse($date)->toDateString();

        $bookedTableIds = CommerceReservation::where('business_id', $business->id)
            ->whereDate('reservation_date', $reservationDate)
            ->where('time_slot', $timeSlot)
            ->whereIn('status', [
                CommerceReservation::STATUS_PENDING_CONFIRMATION,
                CommerceReservation::STATUS_CONFIRMED,
                CommerceReservation::STATUS_SEATED,
            ])
            ->whereNotNull('pos_table_id')
            ->pluck('pos_table_id')
            ->toArray();

        return PosTable::where('business_id', $business->id)
            ->where('is_active', true)
            ->where('capacity', '>=', $guestCount)
            ->whereNotIn('id', $bookedTableIds)
            ->orderBy('capacity')
            ->get();
    }

    /**
     * Update status of reservation with state handling.
     */
    public function updateReservationStatus(
        CommerceReservation $reservation,
        string $status,
        ?string $reason = null
    ): CommerceReservation {
        return DB::transaction(function () use ($reservation, $status, $reason): CommerceReservation {
            switch ($status) {
                case CommerceReservation::STATUS_CONFIRMED:
                    $reservation->confirm();
                    break;
                case CommerceReservation::STATUS_SEATED:
                    $reservation->markAsSeated();
                    break;
                case CommerceReservation::STATUS_COMPLETED:
                    $reservation->markAsCompleted();
                    break;
                case CommerceReservation::STATUS_CANCELLED:
                    $reservation->cancel($reason);
                    break;
                case CommerceReservation::STATUS_NO_SHOW:
                    $reservation->update(['status' => CommerceReservation::STATUS_NO_SHOW]);
                    break;
                default:
                    throw new InvalidArgumentException("Status reservasi '{$status}' tidak valid.");
            }

            return $reservation->fresh(['posTable', 'product', 'location', 'staff', 'creator']);
        });
    }

    /**
     * Assign a table to a reservation.
     */
    public function assignTable(CommerceReservation $reservation, PosTable $table): CommerceReservation
    {
        if ($table->business_id !== $reservation->business_id) {
            throw new InvalidArgumentException('Meja tidak berada dalam bisnis yang sama.');
        }

        // Collision check
        $collision = CommerceReservation::where('business_id', $reservation->business_id)
            ->where('id', '!=', $reservation->id)
            ->where('pos_table_id', $table->id)
            ->whereDate('reservation_date', $reservation->reservation_date)
            ->where('time_slot', $reservation->time_slot)
            ->whereIn('status', [
                CommerceReservation::STATUS_PENDING_CONFIRMATION,
                CommerceReservation::STATUS_CONFIRMED,
                CommerceReservation::STATUS_SEATED,
            ])
            ->exists();

        if ($collision) {
            throw new DomainException("Meja #{$table->table_number} sudah direservasi pada tanggal dan jam tersebut.");
        }

        $reservation->update(['pos_table_id' => $table->id]);

        return $reservation->fresh(['posTable', 'product', 'location', 'staff', 'creator']);
    }
}
