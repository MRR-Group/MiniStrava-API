<?php

declare(strict_types=1);

namespace Strava\Filament\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait AppliesActivityDistanceFilter
{
    protected function applyActivityDistanceFilter(Builder $q): Builder
    {
        $filters = $this->filters ?? [];

        $minKm = isset($filters["distance_min_km"]) ? (float)$filters["distance_min_km"] : null;
        $maxKm = isset($filters["distance_max_km"]) ? (float)$filters["distance_max_km"] : null;

        if ($minKm !== null && $maxKm !== null && $minKm > $maxKm) {
            [$minKm, $maxKm] = [$maxKm, $minKm];
        }

        if ($minKm !== null && $minKm > 0) {
            $q->where("distance_m", ">=", (int)round($minKm * 1000));
        }

        if ($maxKm !== null && $maxKm > 0) {
            $q->where("distance_m", "<=", (int)round($maxKm * 1000));
        }

        return $q;
    }
}
