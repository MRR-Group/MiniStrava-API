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

class WeekVsLastWeekInsight extends StatsOverviewWidget
{
    use InteractsWithPageFilters;
    use ResolvesActivityDateRange;
    use AppliesActivityFilters;
    use AppliesActivityDistanceFilter;
    use AppliesActivityTypeFilter;

    protected static bool $isDiscovered = false;
    protected static ?int $sort = 30;
    protected ?string $heading = "Current vs previous period";

    protected function getStats(): array
    {
        [$from, $to] = $this->resolveRange();

        $spanSeconds = $from->diffInSeconds($to);

        $prevTo = (clone $from)->subSecond();
        $prevFrom = (clone $prevTo)->subSeconds($spanSeconds);

        $thisQ = Activity::query()->whereBetween("started_at", [$from, $to]);
        $lastQ = Activity::query()->whereBetween("started_at", [$prevFrom, $prevTo]);

        $thisQ = $this->applyUserFilter($thisQ);
        $thisQ = $this->applyActivityDistanceFilter($thisQ);
        $thisQ = $this->applyActivityTypeFilter($thisQ);
        $lastQ = $this->applyUserFilter($lastQ);
        $lastQ = $this->applyActivityDistanceFilter($lastQ);
        $lastQ = $this->applyActivityTypeFilter($lastQ);

        $thisCount = (clone $thisQ)->count();
        $lastCount = (clone $lastQ)->count();

        $thisDistKm = ((int)(clone $thisQ)->sum("distance_m")) / 1000;
        $lastDistKm = ((int)(clone $lastQ)->sum("distance_m")) / 1000;

        $thisDurS = (int)(clone $thisQ)->sum("duration_s");
        $lastDurS = (int)(clone $lastQ)->sum("duration_s");

        return [
            Stat::make("Activities", $thisCount)
                ->description($this->trendLabel($lastCount, $thisCount))
                ->descriptionIcon($this->trendIcon($lastCount, $thisCount)),

            Stat::make("Distance", number_format($thisDistKm, 2) . " km")
                ->description($this->trendLabel($lastDistKm, $thisDistKm))
                ->descriptionIcon($this->trendIcon($lastDistKm, $thisDistKm)),

            Stat::make("Time", $this->formatDuration($thisDurS))
                ->description($this->trendLabel($lastDurS, $thisDurS))
                ->descriptionIcon($this->trendIcon($lastDurS, $thisDurS)),
        ];
    }

    private function trendLabel(float|int $prev, float|int $curr): string
    {
        if ($prev === 0 && $curr === 0) {
            return "0% vs previous";
        }

        if ($prev === 0 && $curr > 0) {
            return "New vs previous";
        }

        if ($prev > 0 && $curr === 0) {
            return "-100% vs previous";
        }

        $pct = (($curr - $prev) / $prev) * 100;
        $sign = $pct >= 0 ? "+" : "";

        return $sign . number_format($pct, 0) . "% vs previous";
    }

    private function trendIcon(float|int $prev, float|int $curr): ?string
    {
        if ($curr > $prev) {
            return "heroicon-m-arrow-trending-up";
        }

        if ($curr < $prev) {
            return "heroicon-m-arrow-trending-down";
        }

        return "heroicon-m-minus";
    }

    private function formatDuration(int $seconds): string
    {
        $h = intdiv($seconds, 3600);
        $m = intdiv($seconds % 3600, 60);

        return sprintf("%d:%02d", $h, $m);
    }
}
