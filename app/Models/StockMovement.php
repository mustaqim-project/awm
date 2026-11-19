<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use BelongsToBusiness, HasFactory, HasUuid;

    public const TYPE_POS_SALE = 'pos_sale';

    public const TYPE_POS_REFUND = 'pos_refund';

    public const TYPE_OPNAME = 'opname';

    public const TYPE_ADJUSTMENT = 'adjustment';

    public const TYPE_TRANSFER_IN = 'transfer_in';

    public const TYPE_TRANSFER_OUT = 'transfer_out';

    public const TYPE_GOODS_RECEIPT = 'goods_receipt';

    public const TYPE_GOODS_ISSUE = 'goods_issue';

    public const TYPE_INVOICE_SALE = 'invoice_sale';

    public const TYPE_INVOICE_RETURN = 'invoice_return';

    public const TYPE_PURCHASE_RETURN = 'purchase_return';

    public const TYPE_INITIAL = 'initial';

    public const TYPE_ONLINE_SALE = 'online_sale';

    public const TYPE_SALES = 'online_sale';

    public const TYPE_PRODUCTION_ISSUE = 'production_issue';

    public const TYPE_PRODUCTION_RECEIPT = 'production_receipt';

    public const TYPE_PRODUCTION_SCRAP = 'production_scrap';

    public const TYPE_PRODUCTION_RETURN = 'production_return';

    protected $fillable = [
        'business_id',
        'location_id',
        'material_id',
        'product_id',
        'movement_type',
        'reference_id',
        'reference_number',
        'quantity_change',
        'balance_before',
        'balance_after',
        'unit_cost',
        'cost_before',
        'cost_after',
        'total_cost',
        'purchase_unit_id',
        'purchase_quantity',
        'batch_number',
        'expiry_date',
        'notes',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expiry_date' => 'date',
            'quantity_change' => 'float',
            'balance_before' => 'float',
            'balance_after' => 'float',
            'unit_cost' => 'float',
            'cost_before' => 'float',
            'cost_after' => 'float',
            'total_cost' => 'float',
            'purchase_quantity' => 'float',
        ];
    }

    /**
     * @return BelongsTo<Location, $this>
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * @return BelongsTo<Material, $this>
     */
    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return BelongsTo<Unit, $this>
     */
    public function purchaseUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'purchase_unit_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
