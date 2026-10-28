<?php

declare(strict_types=1);

namespace App\Models\AiStudio;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class InspirationSourcePolicy
 *
 * @property string $id
 * @property string $source_id
 * @property bool $allow_commercial_use
 * @property bool $require_attribution
 * @property int $retention_days
 * @property string|null $disclaimer_template
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class InspirationSourcePolicy extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'inspiration_source_policies';

    protected $fillable = [
        'source_id',
        'allow_commercial_use',
        'require_attribution',
        'retention_days',
        'disclaimer_template',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'allow_commercial_use' => 'boolean',
            'require_attribution' => 'boolean',
            'retention_days' => 'integer',
        ];
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(InspirationSource::class, 'source_id');
    }
}
