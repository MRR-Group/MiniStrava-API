<?php

namespace Strava\Filament\Widgets\Activity\Charts;

use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Strava\Filament\Concerns\AppliesActivityFilters;
use Strava\Filament\Concerns\ResolvesActivityDateRange;
use Strava\Models\Activity;

class ActivityTypeShareChart extends ChartWidget
{
    use InteractsWithPageFilters;
    use ResolvesActivityDateRange;
    use AppliesActivityFilters;

    protected static bool $isDiscovered = false;

    protected ?string $heading = 'Activity types';
    protected static ?int $sort = 13;

    protected function getData(): array
    {
        [$from, $to] = $this->resolveRange();

        $q = Activity::query()
            ->whereBetween('created_at', [$from, $to]);

        $q = $this->applyUserFilter($q);

        $rows = $q
            ->selectRaw('"activityType" as t, COUNT(*) as c')
            ->groupBy('t')
            ->orderByDesc('c')
            ->get();

        return [
            'labels' => $rows->pluck('t')->map(fn ($t) => $t ?: 'unknown')->all(),
            'datasets' => [
                [
                    'label' => 'count',
                    'data' => $rows->pluck('c')->map(fn ($c) => (int) $c)->all(),
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
