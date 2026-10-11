<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasUuid;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class CommunityEvent extends Model
{
    use HasFactory, HasUuid;

    public const TYPE_OFFLINE = 'offline';
    public const TYPE_ONLINE = 'online';

    public const PRICE_TYPE_FREE = 'free';
    public const PRICE_TYPE_PAID = 'paid';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'title',
        'slug',
        'description',
        'banner_image',
        'event_type',
        'price_type',
        'price',
        'location_name',
        'location_address',
        'online_meeting_url',
        'latitude',
        'longitude',
        'checkin_radius_meters',
        'start_time',
        'end_time',
        'registration_deadline',
        'quota',
        'checkin_code',
        'status',
        'created_by_admin_id',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'float',
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'registration_deadline' => 'datetime',
            'latitude' => 'float',
            'longitude' => 'float',
            'checkin_radius_meters' => 'integer',
            'quota' => 'integer',
        ];
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(CommunityEventRegistration::class, 'event_id');
    }

    public function activeRegistrations(): HasMany
    {
        return $this->hasMany(CommunityEventRegistration::class, 'event_id')
            ->where('status', '!=', CommunityEventRegistration::STATUS_CANCELLED);
    }

    public function creatorAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by_admin_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    /**
     * Saring event yang belum lewat:
     * Yang sudah lewat jangan tampilkan!
     */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('end_time', '>=', Carbon::now())
            ->orderBy('start_time', 'asc');
    }

    public function scopePast(Builder $query): Builder
    {
        return $query->where('end_time', '<', Carbon::now())
            ->orderBy('end_time', 'desc');
    }

    public function isPast(): bool
    {
        return $this->end_time->isPast();
    }

    public function isPaid(): bool
    {
        return $this->price_type === self::PRICE_TYPE_PAID || (float) $this->price > 0;
    }

    public function isFree(): bool
    {
        return ! $this->isPaid();
    }

    public function isOnline(): bool
    {
        return $this->event_type === self::TYPE_ONLINE;
    }

    public function isOffline(): bool
    {
        return $this->event_type === self::TYPE_OFFLINE;
    }

    public function getFormattedPriceAttribute(): string
    {
        if ($this->isFree()) {
            return 'Gratis';
        }

        return 'Rp ' . number_format((float) $this->price, 0, ',', '.');
    }

    public function isRegistrationOpen(): bool
    {
        if ($this->status !== self::STATUS_PUBLISHED) {
            return false;
        }

        if ($this->isPast()) {
            return false;
        }

        if ($this->registration_deadline->isPast()) {
            return false;
        }

        if ($this->quota !== null && $this->activeRegistrations()->count() >= $this->quota) {
            return false;
        }

        return true;
    }

    public function getBannerUrlAttribute(): ?string
    {
        if (! $this->banner_image) {
            return null;
        }

        if (str_starts_with($this->banner_image, 'http://') || str_starts_with($this->banner_image, 'https://')) {
            return $this->banner_image;
        }

        return Storage::disk('public')->url($this->banner_image);
    }

    public function getRegisteredCountAttribute(): int
    {
        return $this->activeRegistrations()->count();
    }

    public function isUserRegistered(?User $user): bool
    {
        if ($user === null) {
            return false;
        }

        return $this->registrations()
            ->where('user_id', $user->getAuthIdentifier())
            ->where('status', '!=', CommunityEventRegistration::STATUS_CANCELLED)
            ->exists();
    }

    public function getUserRegistration(?User $user): ?CommunityEventRegistration
    {
        if ($user === null) {
            return null;
        }

        return $this->registrations()
            ->where('user_id', $user->getAuthIdentifier())
            ->first();
    }
}
