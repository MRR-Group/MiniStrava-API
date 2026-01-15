<?php

declare(strict_types=1);

namespace Strava\Filament\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait AppliesActivityTypeFilter
{
    protected function applyActivityTypeFilter(Builder $q): Builder
    {
        $filters = $this->filters ?? [];
        $type = $filters["activity_type"] ?? null;

        if (!empty($type)) {
            $q->where("activity_type", $type);
        }

        return $q;
    }
}
