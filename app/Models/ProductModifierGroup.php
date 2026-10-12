<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Relations\Pivot;

final class ProductModifierGroup extends Pivot
{
    use HasUuid;

    protected $table = 'product_modifier_groups';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'product_id',
        'modifier_group_id',
        'sort_order',
    ];
}
