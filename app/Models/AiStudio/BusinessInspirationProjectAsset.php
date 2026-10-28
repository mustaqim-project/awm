<?php

declare(strict_types=1);

namespace App\Models\AiStudio;

use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class BusinessInspirationProjectAsset
 *
 * @property string $id
 * @property string $business_id
 * @property string $project_id
 * @property string $asset_type
 * @property string $file_path
 * @property string|null $original_filename
 * @property string|null $mime_type
 * @property int $file_size_bytes
 * @property int|null $width
 * @property int|null $height
 * @property bool $is_primary
 * @property array|null $metadata
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class BusinessInspirationProjectAsset extends Model
{
    use BelongsToBusiness, HasFactory, HasUuid;

    protected $table = 'business_inspiration_project_assets';

    protected $fillable = [
        'business_id',
        'project_id',
        'asset_type',
        'file_path',
        'original_filename',
        'mime_type',
        'file_size_bytes',
        'width',
        'height',
        'is_primary',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'file_size_bytes' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
            'metadata' => 'array',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(BusinessInspirationProject::class, 'project_id');
    }
}
