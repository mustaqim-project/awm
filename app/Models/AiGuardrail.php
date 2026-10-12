<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class AiGuardrail extends Model
{
    use BelongsToBusiness, HasFactory, HasUuid;

    public const DOMAIN_INBOX = 'inbox';
    public const DOMAIN_POS = 'pos';
    public const DOMAIN_PURCHASING = 'purchasing';
    public const DOMAIN_FINANCE = 'finance';
    public const DOMAIN_GENERAL = 'general';

    public const TYPE_DO = 'do';
    public const TYPE_DONT = 'dont';

    protected $table = 'ai_guardrails';

    protected $fillable = [
        'business_id',
        'domain',
        'type',
        'instruction',
        'is_active',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
