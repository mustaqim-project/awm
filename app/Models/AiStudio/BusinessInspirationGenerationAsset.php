<?php

declare(strict_types=1);

namespace App\Models\AiStudio;

use App\Models\StorageFile;
use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class BusinessInspirationGenerationAsset
 *
 * @property string $id
 * @property string $business_id
 * @property string $generation_id
 * @property string $output_image_url
 * @property string|null $storage_path
 * @property string|null $thumbnail_url
 * @property string $aspect_ratio
 * @property int|null $width
 * @property int|null $height
 * @property bool $is_watermarked
 * @property bool $is_selected_favorite
 * @property bool $is_upscaled
 * @property string|null $storage_file_id
 * @property array|null $metadata
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class BusinessInspirationGenerationAsset extends Model
{
    use BelongsToBusiness, HasFactory, HasUuid;

    protected $table = 'business_inspiration_generation_assets';

    protected $fillable = [
        'business_id',
        'generation_id',
        'output_image_url',
        'storage_path',
        'thumbnail_url',
        'aspect_ratio',
        'width',
        'height',
        'is_watermarked',
        'is_selected_favorite',
        'is_upscaled',
        'storage_file_id',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'width' => 'integer',
            'height' => 'integer',
            'is_watermarked' => 'boolean',
            'is_selected_favorite' => 'boolean',
            'is_upscaled' => 'boolean',
            'metadata' => 'array',
        ];
    }

    public function generation(): BelongsTo
    {
        return $this->belongsTo(BusinessInspirationGeneration::class, 'generation_id');
    }

    public function storageFile(): BelongsTo
    {
        return $this->belongsTo(StorageFile::class, 'storage_file_id');
    }
}
