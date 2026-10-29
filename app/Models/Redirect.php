<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class Redirect extends Model
{
    protected $fillable = [
        'source_url',
        'target_url',
        'status_code',
        'hits_count',
    ];

    protected $casts = [
        'status_code' => 'integer',
        'hits_count' => 'integer',
    ];

    /**
     * Look up and record a redirect hit if exists.
     */
    public static function findAndRecord(string $path): ?self
    {
        $normalized = '/' . trim($path, '/');
        $redirect = static::where('source_url', $normalized)->first();

        if ($redirect) {
            $redirect->increment('hits_count');
        }

        return $redirect;
    }
}

