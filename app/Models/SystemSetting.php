<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class SystemSetting extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'system_settings';

    protected $fillable = [
        'key',
        'value',
        'group',
        'is_secret',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_secret' => 'boolean',
        ];
    }

    /**
     * Get a setting by key. Decrypts transparently if stored encrypted or flagged as secret.
     */
    public static function get(string $key, ?string $default = null): ?string
    {
        try {
            return Cache::remember("system_setting_{$key}", 3600, function () use ($key, $default) {
                $setting = static::where('key', $key)->first();
                if ($setting === null) {
                    return Cache::get("system_setting_fallback_{$key}", $default);
                }

                $raw = $setting->value;
                if ($raw !== null && $raw !== '' && $setting->is_secret) {
                    try {
                        return Crypt::decryptString($raw);
                    } catch (\Throwable) {
                        // Fallback for legacy unencrypted values in database
                        return $raw;
                    }
                }

                return $raw ?? Cache::get("system_setting_fallback_{$key}", $default);
            });
        } catch (\Throwable) {
            return Cache::get("system_setting_fallback_{$key}", $default);
        }
    }

    /**
     * Set a setting by key. Automatically encrypts value at rest when isSecret is true.
     */
    public static function set(string $key, ?string $value, string $group = 'general', bool $isSecret = false): ?self
    {
        Cache::forget("system_setting_{$key}");
        Cache::forever("system_setting_fallback_{$key}", $value);

        $storedValue = $value;
        if ($isSecret && $value !== null && $value !== '') {
            try {
                $storedValue = Crypt::encryptString($value);
            } catch (\Throwable) {
                $storedValue = $value;
            }
        }

        try {
            return static::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $storedValue,
                    'group' => $group,
                    'is_secret' => $isSecret,
                ]
            );
        } catch (\Throwable) {
            return null;
        }
    }
}
