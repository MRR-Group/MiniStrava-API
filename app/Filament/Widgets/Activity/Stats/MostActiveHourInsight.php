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

class MostActiveHourInsight extends StatsOverviewWidget
{
    use InteractsWithPageFilters;
    use ResolvesActivityDateRange;
    use AppliesActivityFilters;
    use AppliesActivityDistanceFilter;
    use AppliesActivityTypeFilter;

    protected static bool $isDiscovered = false;
    protected static ?int $sort = 31;
    protected ?string $heading = "When people train";

    protected function getStats(): array
    {
        [$from, $to] = $this->resolveRange();

        $q = Activity::query()
            ->whereBetween("started_at", [$from, $to]);

        $q = $this->applyUserFilter($q);
        $q = $this->applyActivityDistanceFilter($q);
        $q = $this->applyActivityTypeFilter($q);

        $row = $q
            ->selectRaw("EXTRACT(HOUR FROM started_at) as h, COUNT(*) as c")
            ->groupBy("h")
            ->orderByDesc("c")
            ->first();

        $hour = $row ? (int)$row->h : null;
        $count = $row ? (int)$row->c : 0;

        return [
            Stat::make("Most active hour", $hour !== null ? sprintf("%02d:00", $hour) : "-")
                ->description($count > 0 ? ($count . " activities") : "No data"),
        ];
    }
}
