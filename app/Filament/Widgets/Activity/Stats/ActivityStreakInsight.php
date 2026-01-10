<?php

namespace Strava\Filament\Widgets\Activity\Stats;

use Carbon\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Strava\Filament\Concerns\AppliesActivityFilters;
use Strava\Filament\Concerns\ResolvesActivityDateRange;
use Strava\Models\Activity;

class ActivityStreakInsight extends StatsOverviewWidget
{
    use InteractsWithPageFilters;
    use ResolvesActivityDateRange;
    use AppliesActivityFilters;

    protected static bool $isDiscovered = false;

    protected ?string $heading = 'Streak';
    protected static ?int $sort = 32;

    protected function getStats(): array
    {
        [$from, $to] = $this->resolveRange();

        $fromDay = (clone $from)->startOfDay();
        $toDay = (clone $to)->endOfDay();

        $q = Activity::query()
            ->whereBetween('created_at', [$fromDay, $toDay]);

        $q = $this->applyUserFilter($q);

        $dates = $q
            ->selectRaw('DATE(created_at) as d')
            ->groupBy('d')
            ->orderBy('d')
            ->pluck('d')
            ->map(fn ($d) => Carbon::parse($d)->toDateString())
            ->all();

        [$current, $best] = $this->computeStreaks($dates, (clone $toDay)->startOfDay());

        return [
            Stat::make('Current streak', $current > 0 ? $current . ' days' : '-'),
            Stat::make('Best streak', $best > 0 ? $best . ' days' : '-'),
        ];
    }

    private function computeStreaks(array $dateStrings, Carbon $anchorDay): array
    {
        if (empty($dateStrings)) return [0, 0];

        $set = array_flip($dateStrings);

        $best = 1;
        $run = 1;

        for ($i = 1; $i < count($dateStrings); $i++) {
            $prev = Carbon::parse($dateStrings[$i - 1]);
            $curr = Carbon::parse($dateStrings[$i]);

            if ($prev->copy()->addDay()->toDateString() === $curr->toDateString()) {
                $run++;
                $best = max($best, $run);
            } else {
                $run = 1;
            }
        }

        $current = 0;

        for ($i = 0; $i < 365; $i++) {
            $d = (clone $anchorDay)->subDays($i)->toDateString();
            if (!isset($set[$d])) break;
            $current++;
        }

        return [$current, $best];
    }
}
