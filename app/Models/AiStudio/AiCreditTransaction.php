<?php

declare(strict_types=1);

namespace App\Models\AiStudio;

use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class AiCreditTransaction
 *
 * @property string $id
 * @property string $business_id
 * @property string|null $user_id
 * @property string|null $generation_id
 * @property string $type
 * @property int $amount
 * @property int $balance_after
 * @property string $idempotency_key
 * @property string $description
 * @property array|null $metadata
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class AiCreditTransaction extends Model
{
    use BelongsToBusiness, HasFactory, HasUuid;

    protected $table = 'ai_credit_transactions';

    protected $fillable = [
        'business_id',
        'user_id',
        'generation_id',
        'type',
        'amount',
        'balance_after',
        'idempotency_key',
        'description',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'balance_after' => 'integer',
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function generation(): BelongsTo
    {
        return $this->belongsTo(BusinessInspirationGeneration::class, 'generation_id');
    }
}
