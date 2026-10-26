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

final class SubscriptionPricingBatch extends Model
{
    use HasFactory, HasUuid;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_ACTIVE,
        self::STATUS_COMPLETED,
        self::STATUS_EXPIRED,
        self::STATUS_CANCELLED,
    ];

    public const MODE_QUOTA = 'quota_based';
    public const MODE_TIME = 'time_based';
    public const MODE_HYBRID = 'hybrid';

    public const MODES = [
        self::MODE_QUOTA,
        self::MODE_TIME,
        self::MODE_HYBRID,
    ];

    public const BASIS_SUBSCRIPTION_ANNIVERSARY = 'subscription_anniversary';
    public const BASIS_CALENDAR_ANNIVERSARY = 'calendar_anniversary';
    public const BASIS_FIXED_DATE = 'fixed_date';

    public const ROUND_NONE = 'none';
    public const ROUND_NEAREST_1000 = 'nearest_1000';
    public const ROUND_NEAREST_100 = 'nearest_100';
    public const ROUND_CEIL_1000 = 'ceiling_1000';

    public const POLICY_RESUBSCRIBE_GRACE = 'grace_period';
    public const POLICY_RESUBSCRIBE_FORFEIT = 'immediate_forfeit';
    public const POLICY_RESUBSCRIBE_RETAIN = 'always_retain';

    public const POLICY_UPGRADE_INHERIT = 'inherit_active_batch';
    public const POLICY_UPGRADE_FORFEIT = 'forfeit_to_normal';
    public const POLICY_UPGRADE_RETAIN_DISCOUNT = 'retain_grandfathered_discount';

    protected $fillable = [
        'program_id',
        'batch_number',
        'name',
        'status',
        'trigger_mode',
        'quota_limit',
        'quota_used',
        'starts_at',
        'ends_at',
        'next_batch_id',
        'fallback_behavior',
        'standard_monthly_price',
        'standard_annual_price',
        'premium_monthly_price',
        'premium_annual_price',
        'prestige_monthly_price',
        'prestige_annual_price',
        'annual_increase_percentage',
        'annual_increase_basis',
        'annual_increase_effective_month',
        'annual_increase_effective_day',
        'rounding_mode',
        'resubscribe_policy',
        'resubscribe_grace_days',
        'upgrade_downgrade_policy',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'batch_number' => 'integer',
            'quota_limit' => 'integer',
            'quota_used' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'standard_monthly_price' => 'float',
            'standard_annual_price' => 'float',
            'premium_monthly_price' => 'float',
            'premium_annual_price' => 'float',
            'prestige_monthly_price' => 'float',
            'prestige_annual_price' => 'float',
            'annual_increase_percentage' => 'float',
            'annual_increase_effective_month' => 'integer',
            'annual_increase_effective_day' => 'integer',
            'resubscribe_grace_days' => 'integer',
        ];
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPricingProgram::class, 'program_id');
    }

    public function nextBatch(): BelongsTo
    {
        return $this->belongsTo(self::class, 'next_batch_id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(BusinessSubscription::class, 'pricing_batch_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SubscriptionPayment::class, 'pricing_batch_id');
    }

    public function priceHistories(): HasMany
    {
        return $this->hasMany(SubscriptionPriceHistory::class, 'pricing_batch_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isQuotaExceeded(): bool
    {
        if ($this->quota_limit === null) {
            return false;
        }

        return $this->quota_used >= $this->quota_limit;
    }

    public function isTimeExpired(): bool
    {
        if ($this->ends_at === null) {
            return false;
        }

        return Carbon::now()->greaterThanOrEqualTo($this->ends_at);
    }

    public function hasStarted(): bool
    {
        if ($this->starts_at === null) {
            return true;
        }

        return Carbon::now()->greaterThanOrEqualTo($this->starts_at);
    }

    public function shouldTransition(): bool
    {
        if (! $this->isActive()) {
            return false;
        }

        return match ($this->trigger_mode) {
            self::MODE_QUOTA => $this->isQuotaExceeded(),
            self::MODE_TIME => $this->isTimeExpired(),
            self::MODE_HYBRID => $this->isQuotaExceeded() || $this->isTimeExpired(),
            default => false,
        };
    }

    public function getRemainingQuota(): ?int
    {
        if ($this->quota_limit === null) {
            return null;
        }

        return max(0, $this->quota_limit - $this->quota_used);
    }

    public function getPriceForTier(string $tier, string $cycle = 'monthly'): ?float
    {
        $tier = strtolower($tier);
        $isAnnual = $cycle === 'annual';

        $price = match ($tier) {
            BusinessSubscription::TIER_PRESTIGE => $isAnnual ? $this->prestige_annual_price : $this->prestige_monthly_price,
            BusinessSubscription::TIER_PREMIUM => $isAnnual ? $this->premium_annual_price : $this->premium_monthly_price,
            BusinessSubscription::TIER_STANDARD => $isAnnual ? $this->standard_annual_price : $this->standard_monthly_price,
            default => null,
        };

        return $price !== null ? (float) $price : null;
    }

    public function hasSpecialPriceFor(string $tier, string $cycle = 'monthly'): bool
    {
        return $this->getPriceForTier($tier, $cycle) !== null;
    }

    public function calculateRoundedPrice(float $rawPrice): float
    {
        return match ($this->rounding_mode) {
            self::ROUND_NEAREST_1000 => (float) round($rawPrice, -3),
            self::ROUND_NEAREST_100 => (float) round($rawPrice, -2),
            self::ROUND_CEIL_1000 => (float) (ceil($rawPrice / 1000) * 1000),
            default => round($rawPrice, 2),
        };
    }
}
