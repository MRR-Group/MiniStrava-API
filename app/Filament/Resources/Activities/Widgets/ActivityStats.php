<?php

declare(strict_types=1);

namespace Strava\Filament\Resources\Activities\Widgets;

use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Strava\Models\Activity;

class ActivityStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $from = Carbon::now()->startOfWeek();

        $q = Activity::query()
            ->where("created_at", ">=", $from);

        $count = (clone $q)->count();
        $distanceKm = round(((int)(clone $q)->sum("distance_m")) / 1000, 2);
        $durationS = (int)(clone $q)->sum("duration_s");

        $maxDistanceM = (int)(clone $q)->max("distance_m");
        $maxDurationS = (int)(clone $q)->max("duration_s");

        $avgDistanceKm = $count > 0 ? round(($distanceKm / $count), 2) : 0;
        $avgDurationS = $count > 0 ? (int)round($durationS / $count) : 0;

        $bestDay = (clone $q)
            ->selectRaw("DATE(created_at) as d, SUM(distance_m) as m")
            ->groupBy("d")
            ->orderByDesc("m")
            ->first();

        $bestDayLabel = $bestDay
            ? Carbon::parse($bestDay->d)->translatedFormat("l")
            : "-";

        $bestDayKm = $bestDay
            ? round(((int)$bestDay->m) / 1000, 2)
            : 0;

        $bestPace = (clone $q)
            ->where("activityType", "run")
            ->where("distance_m", ">", 0)
            ->selectRaw("(duration_s / (distance_m / 1000.0)) as pace_s_per_km")
            ->orderBy("pace_s_per_km")
            ->value("pace_s_per_km");

        $bestPaceLabel = $bestPace
            ? $this->formatPace((int)round($bestPace))
            : "-";

        return [
            Stat::make("Activities (week)", $count),
            Stat::make("Distance (week)", $distanceKm . " km"),
            Stat::make("Time (week)", $this->formatDuration($durationS)),

            Stat::make("Avg distance", $avgDistanceKm . " km"),
            Stat::make("Avg time", $this->formatDuration($avgDurationS)),

            Stat::make("Longest distance", round($maxDistanceM / 1000, 2) . " km"),
            Stat::make("Longest time", $this->formatDuration($maxDurationS)),

            Stat::make(
                "Most active day",
                $bestDayLabel,
            )->description($bestDayKm . " km"),

            Stat::make("Best pace (run)", $bestPaceLabel),
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
