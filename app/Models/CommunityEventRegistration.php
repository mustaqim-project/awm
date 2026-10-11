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

    public function isCheckedIn(): bool
    {
        return $this->status === self::STATUS_ATTENDED && $this->checked_in_at !== null;
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
