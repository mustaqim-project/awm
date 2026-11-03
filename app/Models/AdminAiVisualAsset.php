<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminAiVisualAsset extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'admin_ai_visual_assets';

    protected $fillable = [
        'admin_id',
        'title',
        'prompt',
        'negative_prompt',
        'model_name',
        'aspect_ratio',
        'quality',
        'file_path',
        'image_url',
        'width',
        'height',
        'file_size',
        'metadata',
        'is_favorite',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'metadata'    => 'array',
        'is_favorite' => 'boolean',
        'width'       => 'integer',
        'height'      => 'integer',
        'file_size'   => 'integer',
    ];

    /**
     * Relasi ke Admin pembuat aset.
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }
}
