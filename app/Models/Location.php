<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\Auditable;
use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasSlug;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use Auditable, BelongsToBusiness, HasFactory, HasSlug, HasUuid;

    protected $table = 'locations';

    protected $fillable = [
        'business_id',
        'parent_id',
        'name',
        'slug',
        'type',
        'code',
        'phone',
        'address',
        'province',
        'city',
        'district',
        'village',
        'postal_code',
        'latitude',
        'longitude',
        'geofence_radius_meters',
        'biteship_area_id',
        'is_primary',
        'is_active',
        'is_online_fulfillment',
        'allow_storefront_pickup',
        'timezone',
        'timezone_mode',
        'operating_hours_mode',
        'operating_hours',
        'pos_supervisor_pin',
    ];

    protected $hidden = [
        'pos_supervisor_pin',
    ];

    protected $appends = [
        'has_custom_supervisor_pin',
    ];

    /**
     * Check if this location has its own custom supervisor PIN configured.
     */
    public function getHasCustomSupervisorPinAttribute(): bool
    {
        return ! empty($this->pos_supervisor_pin);
    }

    /**
     * Check if supervisor PIN is active (either branch-specific or business default).
     */
    public function hasSupervisorPin(): bool
    {
        if (! empty($this->pos_supervisor_pin)) {
            return true;
        }

        $business = $this->relationLoaded('business') ? $this->business : Business::find($this->business_id);
        return $business?->hasSupervisorPin() ?? false;
    }

    /**
     * Check specifically if this location has an explicit custom supervisor PIN.
     */
    public function hasCustomSupervisorPin(): bool
    {
        return ! empty($this->pos_supervisor_pin);
    }

    /**
     * Mutator to ensure supervisor PIN is always stored as a Bcrypt hash.
     */
    public function setPosSupervisorPinAttribute($value): void
    {
        if (empty($value)) {
            $this->attributes['pos_supervisor_pin'] = null;
            return;
        }

        $val = (string) $value;
        $info = password_get_info($val);
        $isHashed = ($info['algoName'] ?? '') === 'bcrypt'
            || str_starts_with($val, '$2y$')
            || str_starts_with($val, '$2a$')
            || str_starts_with($val, '$2b$');

        $this->attributes['pos_supervisor_pin'] = $isHashed ? $val : \Illuminate\Support\Facades\Hash::make($val);
    }

    /**
     * Validate supervisor PIN with branch-first hierarchy, resilient Bcrypt verification, and fallback to master business PIN.
     */
    public function verifySupervisorPin(string $pin): bool
    {
        if ($pin === '') {
            return false;
        }

        // 1. If branch has its own custom PIN, verify branch PIN first
        if (! empty($this->pos_supervisor_pin)) {
            $stored = (string) $this->pos_supervisor_pin;
            $info = password_get_info($stored);
            $isBcrypt = ($info['algoName'] ?? '') === 'bcrypt'
                || str_starts_with($stored, '$2y$')
                || str_starts_with($stored, '$2a$')
                || str_starts_with($stored, '$2b$');

            if ($isBcrypt) {
                try {
                    if (\Illuminate\Support\Facades\Hash::check($pin, $stored)) {
                        return true;
                    }
                } catch (\Throwable) {
                    // Fall through to master business check
                }
            } elseif (hash_equals($stored, $pin)) {
                try {
                    $this->updateQuietly([
                        'pos_supervisor_pin' => \Illuminate\Support\Facades\Hash::make($pin),
                    ]);
                } catch (\Throwable) {}
                return true;
            }
        }

        // 2. Fallback to business master PIN (or inherit if no custom PIN was set)
        $business = $this->relationLoaded('business') ? $this->business : Business::find($this->business_id);
        return $business?->verifySupervisorPin($pin) ?? false;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'is_active' => 'boolean',
            'is_online_fulfillment' => 'boolean',
            'allow_storefront_pickup' => 'boolean',
            'latitude' => 'float',
            'longitude' => 'float',
            'geofence_radius_meters' => 'integer',
            'operating_hours' => 'array',
        ];
    }

    public function getTimezone(): string
    {
        return \App\Support\TimezoneHelper::resolve($this->business, $this);
    }

    public function getOperatingHours(): array
    {
        $mode = $this->operating_hours_mode ?? 'inherit';
        if ($mode === 'custom' && ! empty($this->operating_hours)) {
            return \App\Support\TimezoneHelper::normalizeOperatingHours($this->operating_hours);
        }

        // Inherit from business
        if ($this->relationLoaded('business') && $this->business) {
            return $this->business->getOperatingHours();
        }

        if (! empty($this->business_id)) {
            $b = Business::find($this->business_id);
            if ($b) {
                return $b->getOperatingHours();
            }
        }

        return \App\Support\TimezoneHelper::defaultOperatingHours();
    }

    public function localNow(): \Carbon\Carbon
    {
        return \App\Support\TimezoneHelper::now(null, $this);
    }

    public function localToday(): string
    {
        return \App\Support\TimezoneHelper::todayString(null, $this);
    }

    public function isOperatingAt(?\Carbon\Carbon $localTime = null): bool
    {
        $time = $localTime ?? $this->localNow();
        return \App\Support\TimezoneHelper::isOperatingAt($this->getOperatingHours(), $time);
    }

    public function getFormattedFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address,
            $this->village,
            $this->district,
            $this->city,
            $this->province,
            $this->postal_code,
        ]);

        return implode(', ', $parts);
    }

    public function parent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function childWarehouses(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->where('type', 'warehouse');
    }

    public function isRoot(): bool
    {
        return empty($this->parent_id);
    }

    public function isSubWarehouse(): bool
    {
        return ! empty($this->parent_id);
    }

    public function isCentralWarehouse(): bool
    {
        return $this->type === 'warehouse' && empty($this->parent_id);
    }

    public function scopeRootLocations(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->whereNull('parent_id');
    }

    public function scopeSubLocations(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->whereNotNull('parent_id');
    }

    public function scopeOutlets(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->whereIn('type', ['outlet', 'store', 'central_kitchen']);
    }

    /**
     * Resolve location ID and any child sub-warehouse IDs for stock aggregation.
     *
     * @return array<int, string>
     */
    public static function resolveLocationIds(string $locationId): array
    {
        $childIds = self::where('parent_id', $locationId)->pluck('id')->all();

        return array_merge([$locationId], $childIds);
    }

    public function stocks(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(InventoryStock::class);
    }

    public function posRegisters(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PosRegister::class);
    }

    public function posOrders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PosOrder::class);
    }

    public function attendances(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function workShifts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(WorkShift::class);
    }

    public function employeeSchedules(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(EmployeeSchedule::class);
    }

    public function storeEdcTerminals(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(StoreEdcTerminal::class);
    }

    /**
     * Calculate distance in meters to a given coordinate using Haversine formula.
     */
    public function distanceTo(float $targetLat, float $targetLon): ?int
    {
        if ($this->latitude === null || $this->longitude === null) {
            return null;
        }

        $earthRadius = 6371000; // meters
        $latDelta = deg2rad($targetLat - (float) $this->latitude);
        $lonDelta = deg2rad($targetLon - (float) $this->longitude);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
            cos(deg2rad((float) $this->latitude)) * cos(deg2rad($targetLat)) *
            sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return (int) round($earthRadius * $c);
    }

    /**
     * Get the resolved effective timezone for this location.
     */
    public function getEffectiveTimezone(?Business $business = null): string
    {
        return \App\Support\TimezoneHelper::resolve($business ?? $this->business, $this);
    }

    /**
     * Get the resolved effective operating hours for this location.
     *
     * @return array<string, mixed>
     */
    public function getEffectiveOperatingHours(?Business $business = null): array
    {
        return \App\Support\TimezoneHelper::operatingHours($business ?? $this->business, $this);
    }

    /**
     * Check if this location is currently open.
     */
    public function isCurrentlyOpen(?\Carbon\Carbon $at = null): bool
    {
        return \App\Support\TimezoneHelper::isOpen($this->business, $this, $at);
    }
}
