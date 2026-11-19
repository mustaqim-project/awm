<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeriodicInventoryClosing extends Model
{
    use BelongsToBusiness, HasFactory, HasUuid;

    protected $fillable = [
        'business_id',
        'period_year',
        'period_month',
        'item_type',
        'item_id',
        'location_id',
        'opening_quantity',
        'opening_value',
        'received_quantity',
        'received_cost',
        'closing_average_cost',
        'closing_quantity',
        'closing_value',
        'is_locked',
        'closed_by',
        'closed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'period_year' => 'integer',
            'period_month' => 'integer',
            'opening_quantity' => 'float',
            'opening_value' => 'float',
            'received_quantity' => 'float',
            'received_cost' => 'float',
            'closing_average_cost' => 'float',
            'closing_quantity' => 'float',
            'closing_value' => 'float',
            'is_locked' => 'boolean',
            'closed_at' => 'datetime',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function closer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }
}
