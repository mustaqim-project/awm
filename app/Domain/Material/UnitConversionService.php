<?php

declare(strict_types=1);

namespace App\Domain\Material;

use App\Models\MaterialUnitConversion;
use App\Models\ProductUnitConversion;
use App\Models\Unit;
use App\Models\UnitConversion;
use App\Support\Context;
use InvalidArgumentException;
use SplQueue;

final class UnitConversionService
{
    /**
     * Convert quantity from one unit to another using Graph Pathfinding (BFS).
     * Seamlessly traverses hybrid multi-hop conversion graphs combining global units,
     * material-specific conversions, and product-specific conversions (e.g. Dus -> Pcs -> Liter -> ML).
     *
     * @throws InvalidArgumentException
     */
    public function convert(
        float $qty,
        Unit|string $from,
        Unit|string $to,
        ?string $materialId = null,
        ?string $supplierId = null,
        ?string $productId = null
    ): float {
        $fromUnit = $this->resolveUnit($from);
        $toUnit = $this->resolveUnit($to);

        // Identical unit
        if ($fromUnit->id === $toUnit->id) {
            return $qty;
        }

        $factor = $this->getConversionFactor($fromUnit, $toUnit, $materialId, $supplierId, $productId);

        return $qty * $factor;
    }

    /**
     * Get the cumulative conversion factor between two units.
     *
     * @throws InvalidArgumentException
     */
    public function getConversionFactor(
        Unit|string $from,
        Unit|string $to,
        ?string $materialId = null,
        ?string $supplierId = null,
        ?string $productId = null
    ): float {
        $fromUnit = $this->resolveUnit($from);
        $toUnit = $this->resolveUnit($to);

        if ($fromUnit->id === $toUnit->id) {
            return 1.0;
        }

        $graph = $this->buildConversionGraph($materialId, $supplierId, $productId);

        // BFS Search to find shortest conversion path
        /** @var SplQueue<array{unit_id: string, cumulative_factor: float}> $queue */
        $queue = new SplQueue;
        $queue->enqueue([
            'unit_id' => $fromUnit->id,
            'cumulative_factor' => 1.0,
        ]);

        $visited = [$fromUnit->id => true];

        while (! $queue->isEmpty()) {
            $current = $queue->dequeue();
            $currentId = $current['unit_id'];
            $currentFactor = $current['cumulative_factor'];

            if ($currentId === $toUnit->id) {
                return $currentFactor;
            }

            if (isset($graph[$currentId])) {
                foreach ($graph[$currentId] as $edge) {
                    $nextId = $edge['to'];
                    $edgeFactor = $edge['factor'];

                    if (! isset($visited[$nextId])) {
                        $visited[$nextId] = true;
                        $queue->enqueue([
                            'unit_id' => $nextId,
                            'cumulative_factor' => $currentFactor * $edgeFactor,
                        ]);
                    }
                }
            }
        }

        throw new InvalidArgumentException(
            "Cannot convert from [{$fromUnit->code}] to [{$toUnit->code}]. Incompatible categories or missing conversion factor."
        );
    }

    /**
     * Check if a valid conversion path exists between two units.
     */
    public function canConvert(
        Unit|string $from,
        Unit|string $to,
        ?string $materialId = null,
        ?string $supplierId = null,
        ?string $productId = null
    ): bool {
        try {
            $fromUnit = $this->resolveUnit($from);
            $toUnit = $this->resolveUnit($to);

            if ($fromUnit->id === $toUnit->id) {
                return true;
            }

            $this->getConversionFactor($fromUnit, $toUnit, $materialId, $supplierId, $productId);
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Convert safely without throwing exception, returning fallback if not convertible.
     */
    public function convertSafely(
        float $qty,
        Unit|string $from,
        Unit|string $to,
        ?string $materialId = null,
        ?string $supplierId = null,
        ?string $productId = null,
        float $fallbackFactor = 1.0
    ): float {
        try {
            return $this->convert($qty, $from, $to, $materialId, $supplierId, $productId);
        } catch (\Throwable) {
            return $qty * $fallbackFactor;
        }
    }

    /**
     * Build integrated adjacency conversion graph merging global, material, and product conversions.
     *
     * @return array<string, array<int, array{to: string, factor: float}>>
     */
    private function buildConversionGraph(
        ?string $materialId = null,
        ?string $supplierId = null,
        ?string $productId = null
    ): array {
        $graph = [];

        // 1. Load global standard & tenant conversions
        $globalConversions = UnitConversion::available()->get();
        foreach ($globalConversions as $conv) {
            $fromId = (string) $conv->from_unit_id;
            $toId = (string) $conv->to_unit_id;
            $factor = (float) $conv->factor;

            if ($factor <= 0) {
                continue;
            }

            $this->addEdge($graph, $fromId, $toId, $factor);
        }

        // 2. Load material-specific conversions if requested
        if ($materialId !== null) {
            $businessId = Context::hasBusiness() ? Context::business()?->id : null;

            $matQuery = MaterialUnitConversion::query()
                ->where('material_id', $materialId);

            if ($businessId !== null) {
                $matQuery->where(function ($q) use ($businessId): void {
                    $q->where('business_id', $businessId)->orWhereNull('business_id');
                });
            }

            if ($supplierId !== null) {
                $matQuery->where(function ($subQuery) use ($supplierId): void {
                    $subQuery->where('supplier_id', $supplierId)
                        ->orWhereNull('supplier_id');
                });
            }

            $matConversions = $matQuery
                ->orderByRaw('supplier_id IS NULL')
                ->orderByDesc('is_default')
                ->orderByDesc('effective_from')
                ->get();

            foreach ($matConversions as $mConv) {
                $fromId = (string) $mConv->from_unit_id;
                $toId = (string) $mConv->to_unit_id;
                $factor = (float) $mConv->factor;

                if ($factor <= 0) {
                    continue;
                }

                $this->addEdge($graph, $fromId, $toId, $factor, true);
            }
        }

        // 3. Load product-specific conversions if requested
        if ($productId !== null) {
            $prodQuery = ProductUnitConversion::available()
                ->where('product_id', $productId)
                ->orderByDesc('is_default');

            $prodConversions = $prodQuery->get();
            foreach ($prodConversions as $pConv) {
                $fromId = (string) $pConv->from_unit_id;
                $toId = (string) $pConv->to_unit_id;
                $factor = (float) $pConv->factor;

                if ($factor <= 0) {
                    continue;
                }

                $this->addEdge($graph, $fromId, $toId, $factor, true);
            }
        }

        return $graph;
    }

    /**
     * Add directed forward and reverse edges to the graph.
     * Item-specific conversions take priority over generic global ones.
     */
    private function addEdge(array &$graph, string $fromId, string $toId, float $factor, bool $prepend = false): void
    {
        $forward = ['to' => $toId, 'factor' => $factor];
        $reverse = ['to' => $fromId, 'factor' => 1.0 / $factor];

        if (! isset($graph[$fromId])) {
            $graph[$fromId] = [];
        }
        if (! isset($graph[$toId])) {
            $graph[$toId] = [];
        }

        if ($prepend) {
            array_unshift($graph[$fromId], $forward);
            array_unshift($graph[$toId], $reverse);
        } else {
            $graph[$fromId][] = $forward;
            $graph[$toId][] = $reverse;
        }
    }

    /**
     * Resolve unit model by instance, id or code.
     */
    public function resolveUnit(Unit|string $unit): Unit
    {
        if ($unit instanceof Unit) {
            return $unit;
        }

        /** @var Unit|null $resolved */
        $resolved = Unit::available()
            ->where(function ($query) use ($unit): void {
                $query->where('id', $unit)
                    ->orWhere('code', $unit);
            })
            ->first();

        if ($resolved === null) {
            // Also search case-insensitively for unit codes like 'pcs', 'liter', 'kg', 'ml'
            $resolved = Unit::available()
                ->whereRaw('LOWER(code) = ?', [strtolower($unit)])
                ->first();
        }

        if ($resolved === null) {
            throw new InvalidArgumentException("Unit [{$unit}] was not found.");
        }

        return $resolved;
    }
}
