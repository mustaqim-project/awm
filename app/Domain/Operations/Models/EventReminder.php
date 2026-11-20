<?php

declare(strict_types=1);

namespace App\Domain\Operations\Models;

use App\Models\Traits\HasUuid;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class EventReminder
 *
 * Pengingat kegiatan otomatis via WhatsApp, Email, atau In-App notification.
 *
 * @property string $id
 * @property string $calendar_event_id
 * @property string $channel
 * @property int $minutes_before
 * @property \Carbon\Carbon $remind_at
 * @property string|null $recipient_phone
 * @property string|null $recipient_email
 * @property string|null $recipient_user_id
 * @property string $status
 * @property \Carbon\Carbon|null $sent_at
 * @property string|null $error_message
 */
class EventReminder extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'event_reminders';

    protected $fillable = [
        'calendar_event_id',
        'channel',
        'minutes_before',
        'remind_at',
        'recipient_phone',
        'recipient_email',
        'recipient_user_id',
        'status',
        'sent_at',
        'error_message',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'minutes_before' => 'integer',
            'remind_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(CalendarEvent::class, 'calendar_event_id');
    }

    public function recipientUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }

    public function scopeDue(Builder $query): Builder
    {
        return $query->where('status', 'pending')
            ->where('remind_at', '<=', now());
    }
}
