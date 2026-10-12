<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class CommunityWordFilter extends Model
{
    use HasFactory;

    public const CATEGORY_JUDOL = 'judol';
    public const CATEGORY_SCAM = 'scam';
    public const CATEGORY_KATA_KASAR = 'kata_kasar';
    public const CATEGORY_PORNOGRAFI = 'pornografi';
    public const CATEGORY_CUSTOM = 'custom';

    public const ACTION_BLOCK = 'block';
    public const ACTION_CENSOR = 'censor';

    protected $fillable = [
        'word',
        'category',
        'action',
        'is_active',
        'description',
        'created_by_admin_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saved(function (): void {
            Cache::forget('community_active_word_filters');
        });

        static::deleted(function (): void {
            Cache::forget('community_active_word_filters');
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by_admin_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeCategory(Builder $query, ?string $category): Builder
    {
        if (! $category || $category === 'all') {
            return $query;
        }

        return $query->where('category', $category);
    }

    public function setWordAttribute(string $value): void
    {
        $this->attributes['word'] = strtolower(trim($value));
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            self::CATEGORY_JUDOL => 'Judi Online (Judol)',
            self::CATEGORY_SCAM => 'Penipuan / Scam',
            self::CATEGORY_KATA_KASAR => 'Kata Kasar & Toxic',
            self::CATEGORY_PORNOGRAFI => 'Pornografi & Asusila',
            default => 'Kustom / Khusus',
        };
    }

    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            self::ACTION_CENSOR => 'Sensor (***)',
            default => 'Tolak / Blokir',
        };
    }
}
