<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommerceReservation extends Model
{
    use BelongsToBusiness, HasFactory, HasUuid, SoftDeletes;

    public const STATUS_PENDING_CONFIRMATION = 'pending_confirmation';
    public const STATUS_CONFIRMED            = 'confirmed';
    public const STATUS_SEATED               = 'seated';
    public const STATUS_COMPLETED            = 'completed';
    public const STATUS_CANCELLED            = 'cancelled';
    public const STATUS_NO_SHOW              = 'no_show';

    public const PAYMENT_UNPAID              = 'unpaid';
    public const PAYMENT_DP_PAID             = 'dp_paid';
    public const PAYMENT_PAID_IN_FULL        = 'paid_in_full';

    public const SOURCE_ONLINE               = 'online';
    public const SOURCE_MANUAL               = 'manual';
    public const SOURCE_POS                  = 'pos';

    protected $table = 'commerce_reservations';

    protected $fillable = [
        'business_id',
        'location_id',
        'commerce_order_id',
        'pos_table_id',
        'product_id',
        'staff_id',
        'created_by',
        'reservation_code',
        'customer_name',
        'customer_phone',
        'customer_email',
        'reservation_date',
        'time_slot',
        'guest_count',
        'vehicle_plate',
        'vehicle_model',
        'service_duration_minutes',
        'event_venue',
        'estimated_total',
        'dp_amount',
        'dp_payment_method',
        'dp_paid_at',
        'dp_payment_reference',
        'payment_status',
        'status',
        'source',
        'notes',
        'cancellation_reason',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'reservation_date' => 'date',
            'guest_count' => 'integer',
            'service_duration_minutes' => 'integer',
            'estimated_total' => 'decimal:2',
            'dp_amount' => 'decimal:2',
            'dp_paid_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(CommerceOrder::class, 'commerce_order_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function posTable(): BelongsTo
    {
        return $this->belongsTo(PosTable::class, 'pos_table_id');
    }

    public function table(): BelongsTo
    {
        return $this->posTable();
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isConfirmed(): bool
    {
        return in_array($this->status, [self::STATUS_CONFIRMED, self::STATUS_SEATED, self::STATUS_COMPLETED], true);
    }

    public function isDpPaid(): bool
    {
        return in_array($this->payment_status, [self::PAYMENT_DP_PAID, self::PAYMENT_PAID_IN_FULL], true)
            || ((float) $this->dp_amount > 0 && ! empty($this->dp_paid_at));
    }

    public function isFullyPaid(): bool
    {
        return $this->payment_status === self::PAYMENT_PAID_IN_FULL;
    }

    public function remainingBalance(): float
    {
        $est = (float) $this->estimated_total;
        $dp = (float) $this->dp_amount;

        return max(0.0, $est - $dp);
    }

    public function markDpAsPaid(float $amount, string $paymentMethod, ?string $reference = null): void
    {
        $est = (float) $this->estimated_total;
        $newPaymentStatus = ($est > 0 && $amount >= $est) ? self::PAYMENT_PAID_IN_FULL : self::PAYMENT_DP_PAID;

        $this->update([
            'dp_amount' => $amount,
            'dp_payment_method' => $paymentMethod,
            'dp_payment_reference' => $reference,
            'dp_paid_at' => now(),
            'payment_status' => $newPaymentStatus,
            // Automatically confirm pending reservation once DP is settled
            'status' => $this->status === self::STATUS_PENDING_CONFIRMATION ? self::STATUS_CONFIRMED : $this->status,
        ]);
    }

    public function confirm(): void
    {
        $this->update(['status' => self::STATUS_CONFIRMED]);
    }

    public function markAsSeated(): void
    {
        $this->update(['status' => self::STATUS_SEATED]);

        if ($this->pos_table_id && $this->posTable) {
            $this->posTable->update(['status' => PosTable::STATUS_OCCUPIED]);
        }
    }

    public function markAsCompleted(): void
    {
        $this->update(['status' => self::STATUS_COMPLETED]);

        if ($this->pos_table_id && $this->posTable) {
            $this->posTable->update(['status' => PosTable::STATUS_AVAILABLE]);
        }
    }

    public function cancel(?string $reason = null): void
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'cancellation_reason' => $reason,
        ]);

        if ($this->pos_table_id && $this->posTable && $this->posTable->status === PosTable::STATUS_OCCUPIED) {
            $this->posTable->update(['status' => PosTable::STATUS_AVAILABLE]);
        }
    }
}
