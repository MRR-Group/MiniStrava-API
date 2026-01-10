<?php

declare(strict_types=1);

namespace Strava\Filament\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait AppliesActivityFilters
{
    protected function applyUserFilter(Builder $q, ?string $table = null): Builder
    {
        $userId = $this->filters['user_id'] ?? null;
        if (!$userId) {
            return $q;
        }

        $col = $table ? "{$table}.user_id" : 'user_id';
        return $q->where($col, (int) $userId);
    }

    protected function userIdFilter(): ?int
    {
        $userId = $this->filters['user_id'] ?? null;
        return $userId ? (int) $userId : null;
    }
}
