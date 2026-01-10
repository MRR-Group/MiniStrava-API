<?php

namespace Strava\Filament\Widgets\Activity\Charts;

use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Collection;
use Strava\Filament\Concerns\AppliesActivityFilters;
use Strava\Filament\Concerns\ResolvesActivityDateRange;
use Strava\Models\Activity;

class DurationPerDayChart extends ChartWidget
{
    use InteractsWithPageFilters;
    use ResolvesActivityDateRange;
    use AppliesActivityFilters;

    protected static bool $isDiscovered = false;

    protected ?string $heading = 'Time per day';
    protected static ?int $sort = 11;

    protected function getData(): array
    {
        [$from, $to] = $this->resolveRange();

        $fromDay = $from->copy()->startOfDay();
        $toDay = $to->copy()->startOfDay();

        $days = max($fromDay->diffInDays($toDay) + 1, 1);

        $q = Activity::query()
            ->whereBetween('created_at', [$from, $to]);

        $q = $this->applyUserFilter($q);

        $rows = $q
            ->selectRaw('DATE(created_at) as d, SUM(duration_s) as s')
            ->groupBy('d')
            ->orderBy('d')
            ->get();

        [$labels, $values] = $this->fillDailySeries(
            $rows,
            $fromDay,
            $days,
            valueKey: 's',
            mapper: fn ($s) => round(((int) $s) / 3600, 2),
        );

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'hours',
                    'data' => $values,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    private function fillDailySeries(
        Collection $rows,
        Carbon $from,
        int $days,
        string $valueKey,
        callable $mapper
    ): array {
        $map = $rows->keyBy('d');

        $labels = [];
        $values = [];

        for ($i = 0; $i < $days; $i++) {
            $date = (clone $from)->addDays($i);
            $key = $date->toDateString();

            $labels[] = $date->format('d.m');
            $raw = $map->get($key)?->{$valueKey} ?? 0;

            $values[] = $mapper($raw);
        }

        return [$labels, $values];
    }
}
