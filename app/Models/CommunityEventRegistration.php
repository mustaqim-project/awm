<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasUuid;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunityEventRegistration extends Model
{
    use HasFactory, HasUuid;

    public const STATUS_REGISTERED = 'registered';
    public const STATUS_ATTENDED = 'attended';
    public const STATUS_CANCELLED = 'cancelled';

    public const PAYMENT_STATUS_PAID = 'paid';
    public const PAYMENT_STATUS_PENDING = 'pending';
    public const PAYMENT_STATUS_FAILED = 'failed';

    public const GATEWAY_TRIPAY = 'tripay';
    public const GATEWAY_MANUAL = 'manual';

    public const METHOD_LOCATION = 'location';
    public const METHOD_BARCODE = 'barcode';
    public const METHOD_MANUAL = 'manual';

    protected $fillable = [
        'event_id',
        'user_id',
        'business_id',
        'ticket_code',
        'amount_paid',
        'payment_status',
        'payment_method',
        'payment_gateway',
        'gateway_reference',
        'gateway_pay_code',
        'gateway_qr_url',
        'gateway_qr_string',
        'gateway_fee',
        'gateway_expired_at',
        'paid_at',
        'registered_at',
        'status',
        'checked_in_at',
        'checkin_method',
        'checkin_latitude',
        'checkin_longitude',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount_paid' => 'float',
            'gateway_fee' => 'float',
            'gateway_expired_at' => 'datetime',
            'paid_at' => 'datetime',
            'registered_at' => 'datetime',
            'checked_in_at' => 'datetime',
            'checkin_latitude' => 'float',
            'checkin_longitude' => 'float',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(CommunityEvent::class, 'event_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class, 'business_id');
    }

    public function isPaid(): bool
    {
        return $this->payment_status === self::PAYMENT_STATUS_PAID;
    }

    public function isPendingPayment(): bool
    {
        return $this->payment_status === self::PAYMENT_STATUS_PENDING;
    }

    public function isExpired(): bool
    {
        return $this->gateway_expired_at !== null && $this->gateway_expired_at->isPast() && ! $this->isPaid();
    }

    public function getFormattedAmountPaid(): string
    {
        return 'Rp ' . number_format($this->amount_paid, 0, ',', '.');
    }

    public function isCheckedIn(): bool
    {
        return $this->status === self::STATUS_ATTENDED && $this->checked_in_at !== null;
    }

    public function markAsPaid(): void
    {
        $this->update([
            'payment_status' => self::PAYMENT_STATUS_PAID,
            'paid_at' => Carbon::now(),
        ]);
    }

    public function markAsAttended(string $method, ?float $latitude = null, ?float $longitude = null): void
    {
        $this->update([
            'status' => self::STATUS_ATTENDED,
            'checked_in_at' => Carbon::now(),
            'checkin_method' => $method,
            'checkin_latitude' => $latitude,
            'checkin_longitude' => $longitude,
        ]);
    }
}
