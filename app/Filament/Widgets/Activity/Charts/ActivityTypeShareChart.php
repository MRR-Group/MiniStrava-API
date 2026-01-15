<?php

declare(strict_types=1);

namespace Strava\Filament\Widgets\Activity\Charts;

use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Strava\Filament\Concerns\AppliesActivityDistanceFilter;
use Strava\Filament\Concerns\AppliesActivityFilters;
use Strava\Filament\Concerns\AppliesActivityTypeFilter;
use Strava\Filament\Concerns\ResolvesActivityDateRange;
use Strava\Models\Activity;

class ActivityTypeShareChart extends ChartWidget
{
    use InteractsWithPageFilters;
    use ResolvesActivityDateRange;
    use AppliesActivityFilters;
    use AppliesActivityDistanceFilter;
    use AppliesActivityTypeFilter;

    protected static bool $isDiscovered = false;
    protected static ?int $sort = 13;
    protected ?string $heading = "Activity types";

    protected function getData(): array
    {
        [$from, $to] = $this->resolveRange();

        $q = Activity::query()
            ->whereBetween("started_at", [$from, $to]);

        $q = $this->applyUserFilter($q);
        $q = $this->applyActivityDistanceFilter($q);
        $q = $this->applyActivityTypeFilter($q);

        $rows = $q
            ->selectRaw('"activity_type" as t, COUNT(*) as c')
            ->groupBy("t")
            ->orderByDesc("c")
            ->get();

        return [
            "labels" => $rows->pluck("t")->map(fn($t) => $t ?: "unknown")->all(),
            "datasets" => [
                [
                    "label" => "count",
                    "data" => $rows->pluck("c")->map(fn($c) => (int)$c)->all(),
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return "doughnut";
    }
}
