<?php

declare(strict_types=1);

namespace App\Models\AiStudio;

use App\Models\Traits\HasUuid;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class InspirationSourceCredential
 *
 * @property string $id
 * @property string $source_id
 * @property string $credential_type
 * @property array|string $credentials_encrypted
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property string|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class InspirationSourceCredential extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'inspiration_source_credentials';

    protected $fillable = [
        'source_id',
        'credential_type',
        'credentials_encrypted',
        'status',
        'expires_at',
        'created_by',
    ];

    protected $hidden = [
        'credentials_encrypted',
    ];

    protected function casts(): array
    {
        return [
            'credentials_encrypted' => 'encrypted:array',
            'expires_at' => 'datetime',
        ];
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(InspirationSource::class, 'source_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
