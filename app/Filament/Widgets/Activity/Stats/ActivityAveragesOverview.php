<?php

declare(strict_types=1);

namespace Strava\Filament\Widgets\Activity\Stats;

use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Strava\Filament\Concerns\AppliesActivityDistanceFilter;
use Strava\Filament\Concerns\AppliesActivityFilters;
use Strava\Filament\Concerns\AppliesActivityTypeFilter;
use Strava\Filament\Concerns\ResolvesActivityDateRange;
use Strava\Models\Activity;

class ActivityAveragesOverview extends StatsOverviewWidget
{
    use InteractsWithPageFilters;
    use ResolvesActivityDateRange;
    use AppliesActivityFilters;
    use AppliesActivityDistanceFilter;
    use AppliesActivityTypeFilter;

    protected static bool $isDiscovered = false;
    protected ?string $heading = "Activity averages";

    protected function getStats(): array
    {
        [$from, $to] = $this->resolveRange();

        $q = Activity::query()
            ->whereBetween("started_at", [$from, $to]);

        $q = $this->applyUserFilter($q);
        $q = $this->applyActivityDistanceFilter($q);
        $q = $this->applyActivityTypeFilter($q);

        $count = (clone $q)->count();

        $distanceKm = round(((int)(clone $q)->sum("distance_m")) / 1000, 2);
        $durationS = (int)(clone $q)->sum("duration_s");

        $avgDistanceKm = $count > 0 ? round($distanceKm / $count, 2) : 0;
        $avgDurationS = $count > 0 ? (int)round($durationS / $count) : 0;

        return [
            Stat::make("Avg distance", $count > 0 ? ($avgDistanceKm . " km") : "-"),
            Stat::make("Avg time", $count > 0 ? $this->formatDuration($avgDurationS) : "-"),
        ];
    }

    private function formatDuration(int $seconds): string
    {
        $h = intdiv($seconds, 3600);
        $m = intdiv($seconds % 3600, 60);

        return sprintf("%d:%02d", $h, $m);
    }
}
