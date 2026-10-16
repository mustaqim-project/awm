<?php

namespace App\Traits;

use Illuminate\Contracts\Database\Query\Builder as QueryBuilderContract;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as BaseQueryBuilder;
use Illuminate\Http\Request;

trait SortableQueryTrait
{
    /**
     * Apply safe sorting to an Eloquent or Query Builder instance.
     *
     * @param EloquentBuilder|BaseQueryBuilder|Relation|QueryBuilderContract $query
     * @param Request|array $request
     * @param array $allowedSorts Whitelist of allowed column keys, or associative array [key => 'table.column']
     * @param string $defaultCol Default column if invalid or unspecified
     * @param string $defaultDir Default direction ('desc' or 'asc')
     * @return EloquentBuilder|BaseQueryBuilder|Relation|QueryBuilderContract
     */
    public function applySorting(
        $query,
        Request|array $request,
        array $allowedSorts,
        string $defaultCol = 'created_at',
        string $defaultDir = 'desc'
    ) {
        $sort = is_array($request) ? ($request['sort'] ?? null) : $request->query('sort');
        $dir = is_array($request) ? ($request['dir'] ?? null) : $request->query('dir');

        // Normalize direction strictly to 'asc' or 'desc'
        $dir = strtolower((string) $dir);
        if (!in_array($dir, ['asc', 'desc'], true)) {
            $dir = in_array(strtolower($defaultDir), ['asc', 'desc'], true) ? strtolower($defaultDir) : 'desc';
        }

        // Resolve target column with whitelist validation
        $targetColumn = null;
        if ($sort !== null && $sort !== '') {
            $sort = (string) $sort;
            if (array_is_list($allowedSorts)) {
                if (in_array($sort, $allowedSorts, true)) {
                    $targetColumn = $sort;
                }
            } else {
                if (isset($allowedSorts[$sort])) {
                    $targetColumn = $allowedSorts[$sort];
                } elseif (in_array($sort, $allowedSorts, true)) {
                    $targetColumn = $sort;
                }
            }
        }

        // Fallback to default column and direction if not matched in whitelist
        if (!$targetColumn) {
            $targetColumn = $defaultCol;
            $dir = in_array(strtolower($defaultDir), ['asc', 'desc'], true) ? strtolower($defaultDir) : 'desc';
        }

        return $query->orderBy($targetColumn, $dir);
    }
}
