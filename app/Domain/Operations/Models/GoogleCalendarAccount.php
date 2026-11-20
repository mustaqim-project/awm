<?php

declare(strict_types=1);

namespace App\Domain\Operations\Models;

use App\Models\Business;
use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

/**
 * Class GoogleCalendarAccount
 *
 * Akun Google Calendar yang terhubung untuk sinkronisasi kegiatan dan pengingat.
 *
 * @property string $id
 * @property string $business_id
 * @property string|null $user_id
 * @property string $google_id
 * @property string $email
 * @property string|null $name
 * @property string|null $avatar_url
 * @property string $access_token
 * @property string|null $refresh_token
 * @property \Carbon\Carbon|null $token_expires_at
 * @property string $calendar_id
 * @property string|null $calendar_name
 * @property bool $is_active
 * @property bool $auto_sync
 * @property string|null $sync_token
 * @property \Carbon\Carbon|null $last_synced_at
 * @property array|null $settings
 */
class GoogleCalendarAccount extends Model
{
    use HasFactory, HasUuid, BelongsToBusiness;

    protected $table = 'google_calendar_accounts';

    protected $attributes = [
        'calendar_id' => 'primary',
        'is_active' => true,
        'auto_sync' => true,
    ];

    protected $fillable = [
        'business_id',
        'user_id',
        'google_id',
        'email',
        'name',
        'avatar_url',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'calendar_id',
        'calendar_name',
        'is_active',
        'auto_sync',
        'sync_token',
        'last_synced_at',
        'settings',
    ];

    protected $hidden = [
        'access_token',
        'refresh_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'token_expires_at' => 'datetime',
            'last_synced_at' => 'datetime',
            'is_active' => 'boolean',
            'auto_sync' => 'boolean',
            'settings' => 'array',
        ];
    }

    public function getAccessTokenAttribute($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Throwable) {
            return (string) $value;
        }
    }

    public function setAccessTokenAttribute($value): void
    {
        if (! empty($value)) {
            $this->attributes['access_token'] = Crypt::encryptString((string) $value);
        } else {
            $this->attributes['access_token'] = Crypt::encryptString('');
        }
    }

    public function getRefreshTokenAttribute($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Throwable) {
            return (string) $value;
        }
    }

    public function setRefreshTokenAttribute($value): void
    {
        if (! empty($value)) {
            $this->attributes['refresh_token'] = Crypt::encryptString((string) $value);
        } else {
            $this->attributes['refresh_token'] = null;
        }
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(CalendarEvent::class, 'google_calendar_account_id');
    }

    public function isTokenExpired(): bool
    {
        if (! $this->token_expires_at) {
            return false;
        }

        return $this->token_expires_at->lte(now()->addMinutes(5));
    }
}
