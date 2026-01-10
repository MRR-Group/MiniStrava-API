<?php

declare(strict_types=1);

namespace Strava\Filament\Widgets\Activity\Charts;

use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Collection;
use Strava\Filament\Concerns\AppliesActivityFilters;
use Strava\Filament\Concerns\ResolvesActivityDateRange;
use Strava\Models\Activity;

class ActivitiesPerDayChart extends ChartWidget
{
    use InteractsWithPageFilters;
    use ResolvesActivityDateRange;
    use AppliesActivityFilters;

    protected static bool $isDiscovered = false;
    protected static ?int $sort = 12;
    protected ?string $heading = "Activities per day";

    protected function getData(): array
    {
        [$from, $to] = $this->resolveRange();

        $days = (int)max($from->copy()->startOfDay()->diffInDays($to->copy()->startOfDay()) + 1, 1);

        $q = Activity::query()
            ->whereBetween("created_at", [$from, $to]);

        $q = $this->applyUserFilter($q);

        $rows = $q
            ->selectRaw("DATE(created_at) as d, COUNT(*) as c")
            ->groupBy("d")
            ->orderBy("d")
            ->get();

        [$labels, $values] = $this->fillDailySeries(
            $rows,
            $from->copy()->startOfDay(),
            $days,
            valueKey: "c",
            mapper: fn($c) => (int)$c,
        );

        return [
            "labels" => $labels,
            "datasets" => [
                [
                    "label" => "count",
                    "data" => $values,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return "bar";
    }

    private function fillDailySeries(
        Collection $rows,
        Carbon $from,
        int $days,
        string $valueKey,
        callable $mapper,
    ): array {
        $map = $rows->keyBy("d");

        $labels = [];
        $values = [];

        for ($i = 0; $i < $days; $i++) {
            $date = (clone $from)->addDays($i);
            $key = $date->toDateString();

            $labels[] = $date->format("d.m");
            $raw = $map->get($key)?->{$valueKey} ?? 0;

            $values[] = $mapper($raw);
        }

        return [$labels, $values];
    }
}
