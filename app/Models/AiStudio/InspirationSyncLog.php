<?php

declare(strict_types=1);

namespace App\Models\AiStudio;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class InspirationSyncLog
 *
 * @property string $id
 * @property string|null $source_id
 * @property string $provider_code
 * @property string $operation
 * @property string $status
 * @property int $request_count
 * @property int $result_count
 * @property string|null $error_message
 * @property \Illuminate\Support\Carbon|null $started_at
 * @property \Illuminate\Support\Carbon|null $finished_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class InspirationSyncLog extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'inspiration_sync_logs';

    protected $fillable = [
        'source_id',
        'provider_code',
        'operation',
        'status',
        'request_count',
        'result_count',
        'error_message',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'request_count' => 'integer',
        'result_count' => 'integer',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<InspirationSource, $this>
     */
    public function source(): BelongsTo
    {
        return $this->belongsTo(InspirationSource::class, 'source_id');
    }
}
