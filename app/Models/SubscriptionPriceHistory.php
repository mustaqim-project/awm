<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class SubscriptionPriceHistory extends Model
{
    use BelongsToBusiness, HasFactory, HasUuid;

    public const EVENT_INITIAL_LOCK = 'initial_lock';
    public const EVENT_ANNUAL_ESCALATION = 'annual_escalation';
    public const EVENT_MANUAL_ADMIN_OVERRIDE = 'manual_admin_override';
    public const EVENT_UPGRADE_DOWNGRADE = 'upgrade_downgrade';
    public const EVENT_CYCLE_SWITCH = 'cycle_switch';
    public const EVENT_FORFEITED = 'forfeited';
    public const EVENT_RESUBSCRIBE_RESTORED = 'resubscribe_restored';

    protected $fillable = [
        'business_id',
        'business_subscription_id',
        'pricing_batch_id',
        'event_type',
        'previous_price',
        'new_price',
        'previous_plan_code',
        'new_plan_code',
        'increase_percentage',
        'effective_date',
        'performed_by_type',
        'performed_by_id',
        'notes',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'previous_price' => 'float',
            'new_price' => 'float',
            'increase_percentage' => 'float',
            'effective_date' => 'date',
            'metadata' => 'array',
        ];
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(BusinessSubscription::class, 'business_subscription_id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPricingBatch::class, 'pricing_batch_id');
    }
}
