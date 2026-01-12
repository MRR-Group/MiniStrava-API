<?php

declare(strict_types=1);

namespace Strava\Filament\Widgets\User\Charts;

use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Strava\Filament\Concerns\ResolvesActivityDateRange;
use Strava\Models\Activity;

class ActiveUsersPerDayChart extends ChartWidget
{
    use InteractsWithPageFilters;
    use ResolvesActivityDateRange;

    protected static bool $isDiscovered = false;
    protected static ?int $sort = 21;
    protected ?string $heading = "Active users per day";

    protected function getData(): array
    {
        [$from, $to] = $this->resolveRange();

        $rows = Activity::query()
            ->selectRaw("DATE(created_at) as d, COUNT(DISTINCT user_id) as c")
            ->whereBetween("created_at", [$from, $to])
            ->groupBy("d")
            ->orderBy("d")
            ->get();

        $labels = $rows->pluck("d")->map(fn($d) => (string)$d)->all();
        $data = $rows->pluck("c")->map(fn($c) => (int)$c)->all();

        return [
            "labels" => $labels,
            "datasets" => [
                [
                    "label" => "Active users",
                    "data" => $data,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return "line";
    }
}
