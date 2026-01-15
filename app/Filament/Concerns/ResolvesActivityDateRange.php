<?php

declare(strict_types=1);

namespace Strava\Filament\Concerns;

use Carbon\Carbon;

trait ResolvesActivityDateRange
{
    /**
     * Reads filters from InteractsWithPageFilters ($this->filters) and returns [from, to].
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    protected function resolveRange(): array
    {
        $range = (string)($this->filters["range"] ?? "30");

        if ($range === "this_week") {
            return [Carbon::now()->startOfWeek(), Carbon::now()->endOfDay()];
        }

        if ($range === "last_week") {
            $from = Carbon::now()->startOfWeek()->subWeek();
            $to = Carbon::now()->startOfWeek()->subSecond();

            return [$from, $to];
        }

        if ($range === "custom") {
            $from = !empty($this->filters["from"])
                ? Carbon::parse((string)$this->filters["from"])->startOfDay()
                : Carbon::now()->subDays(29)->startOfDay();

            $to = !empty($this->filters["to"])
                ? Carbon::parse((string)$this->filters["to"])->endOfDay()
                : Carbon::now()->endOfDay();

            if ($from->greaterThan($to)) {
                [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
            }

            return [$from, $to];
        }

        $days = max((int)$range, 1);
        $from = Carbon::now()->subDays($days - 1)->startOfDay();
        $to = Carbon::now()->endOfDay();

        return [$from, $to];
    }
}
