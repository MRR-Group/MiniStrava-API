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

class ActivityStatsOverview extends StatsOverviewWidget
{
    use InteractsWithPageFilters;
    use ResolvesActivityDateRange;
    use AppliesActivityFilters;
    use AppliesActivityDistanceFilter;
    use AppliesActivityTypeFilter;

    protected static bool $isDiscovered = false;

    protected function getStats(): array
    {
        [$from, $to] = $this->resolveRange();

        $q = Activity::query()
            ->whereBetween("created_at", [$from, $to]);

        $q = $this->applyUserFilter($q);
        $q = $this->applyActivityDistanceFilter($q);
        $q = $this->applyActivityTypeFilter($q);

        $maxDistanceM = (int)(clone $q)->max("distance_m");
        $maxDurationS = (int)(clone $q)->max("duration_s");

        $bestPace = (clone $q)
            ->where("activityType", "run")
            ->where("distance_m", ">", 0)
            ->selectRaw("(duration_s / (distance_m / 1000.0)) as pace_s_per_km")
            ->orderBy("pace_s_per_km")
            ->value("pace_s_per_km");

        return [
            Stat::make("Longest distance", $maxDistanceM > 0 ? round($maxDistanceM / 1000, 2) . " km" : "-"),
            Stat::make("Longest time", $maxDurationS > 0 ? $this->formatDuration($maxDurationS) : "-"),
            Stat::make("Best pace (run)", $bestPace ? $this->formatPace((int)$bestPace) : "-"),
        ];
    }

    private function formatDuration(int $seconds): string
    {
        $h = intdiv($seconds, 3600);
        $m = intdiv($seconds % 3600, 60);

        return sprintf("%d:%02d", $h, $m);
    }

    private function formatPace(int $secondsPerKm): string
    {
        $m = intdiv($secondsPerKm, 60);
        $s = $secondsPerKm % 60;

        return sprintf("%d:%02d min/km", $m, $s);
    }
}
