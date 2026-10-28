<?php

declare(strict_types=1);

namespace App\Models\AiStudio;

use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class BusinessInspirationPromptVersion
 *
 * @property string $id
 * @property string $business_id
 * @property string $project_id
 * @property int $version_number
 * @property string $positive_prompt
 * @property string|null $negative_prompt
 * @property string|null $style_preset
 * @property string $aspect_ratio
 * @property array|null $prompt_parameters
 * @property bool $is_approved
 * @property string $guardrail_status
 * @property string|null $guardrail_notes
 * @property string|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class BusinessInspirationPromptVersion extends Model
{
    use BelongsToBusiness, HasFactory, HasUuid;

    protected $table = 'business_inspiration_prompt_versions';

    protected $fillable = [
        'business_id',
        'project_id',
        'version_number',
        'positive_prompt',
        'negative_prompt',
        'style_preset',
        'aspect_ratio',
        'prompt_parameters',
        'is_approved',
        'guardrail_status',
        'guardrail_notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'version_number' => 'integer',
            'prompt_parameters' => 'array',
            'is_approved' => 'boolean',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(BusinessInspirationProject::class, 'project_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function generations(): HasMany
    {
        return $this->hasMany(BusinessInspirationGeneration::class, 'prompt_version_id');
    }
}
