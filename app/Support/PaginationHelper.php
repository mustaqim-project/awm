<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Http\Request;

final class PaginationHelper
{
    public const DEFAULT_PER_PAGE = 25;
    public const MIN_PER_PAGE = 1;
    public const MAX_PER_PAGE = 10000;

    /**
     * Standard allowed per-page options selectable by the user.
     * Includes: 10, 25 (default), 50, 100, 500, 1000, 5000, 10000.
     *
     * @var array<int, int>
     */
    public const OPTIONS = [10, 25, 50, 100, 500, 1000, 5000, 10000];

    /**
     * Resolve and validate requested per_page parameter from request.
     * Defaults to 25 rows per page.
     */
    public static function perPage(?Request $request = null, int $default = self::DEFAULT_PER_PAGE): int
    {
        $request = $request ?? request();
        $raw = $request->input('per_page', $default);

        if (! is_numeric($raw)) {
            return $default;
        }

        $perPage = (int) $raw;

        if ($perPage < self::MIN_PER_PAGE) {
            return $default;
        }

        // Clamp to a safe upper bound (10,000) for query execution safety and high performance
        return min($perPage, self::MAX_PER_PAGE);
    }

    /**
     * Get all selectable per-page options for the user interface.
     *
     * @return array<int, int>
     */
    public static function options(int $current = self::DEFAULT_PER_PAGE): array
    {
        $options = self::OPTIONS;

        if ($current > 0 && ! in_array($current, $options, true)) {
            $options[] = $current;
            sort($options);
        }

        return $options;
    }
}

