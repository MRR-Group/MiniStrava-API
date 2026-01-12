<?php

declare(strict_types=1);

namespace Strava\Filament\Widgets\User\Stats;

use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Strava\Filament\Concerns\ResolvesActivityDateRange;
use Strava\Models\Activity;
use Strava\Models\User;

class UsersOverview extends StatsOverviewWidget
{
    use InteractsWithPageFilters;
    use ResolvesActivityDateRange;

    protected static bool $isDiscovered = false;
    protected static ?int $sort = 20;
    protected ?string $heading = 'Users Overview';
    protected static string $routePath = "users-overview";

    protected function getStats(): array
    {
        [$from, $to] = $this->resolveRange();

        $newUsers = User::query()
            ->whereBetween('created_at', [$from, $to])
            ->count();

        $activeUsers = Activity::query()
            ->whereBetween('created_at', [$from, $to])
            ->distinct('user_id')
            ->count('user_id');

        $totalActivities = Activity::query()
            ->whereBetween('created_at', [$from, $to])
            ->count();

        $avgActivitiesPerActiveUser = $activeUsers > 0
            ? round($totalActivities / $activeUsers, 2)
            : 0;

        $sumDistanceM = (int) Activity::query()
            ->whereBetween('created_at', [$from, $to])
            ->sum('distance_m');

        $avgDistanceKmPerActiveUser = $activeUsers > 0
            ? round(($sumDistanceM / 1000) / $activeUsers, 2)
            : 0;

        $days = max($from->copy()->startOfDay()->diffInDays($to->copy()->startOfDay()) + 1, 1);
        $prevFrom = $from->copy()->subDays($days);
        $prevTo = $to->copy()->subDays($days);

        $prevActiveUsers = Activity::query()
            ->whereBetween('created_at', [$prevFrom, $prevTo])
            ->distinct('user_id')
            ->count('user_id');

        $activeUsersDelta = $activeUsers - $prevActiveUsers;
        $activeUsersDeltaPct = $prevActiveUsers > 0
            ? round(($activeUsersDelta / $prevActiveUsers) * 100, 1)
            : null;

        $topByDistance = Activity::query()
            ->selectRaw('user_id, SUM(distance_m) as total_m')
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('user_id')
            ->orderByDesc('total_m')
            ->first();

        $topLabel = '—';
        if ($topByDistance) {
            $topUser = User::query()->find($topByDistance->user_id);
            $topKm = round(((int) $topByDistance->total_m) / 1000, 2);
            $topLabel = ($topUser?->name ?? ('User #' . $topByDistance->user_id)) . " ({$topKm} km)";
        }

        return [
            Stat::make('New users', $newUsers),

            Stat::make('Active users', $activeUsers)
                ->description($activeUsersDeltaPct === null
                    ? ($activeUsersDelta >= 0 ? "+{$activeUsersDelta}" : (string)$activeUsersDelta)
                    : (($activeUsersDelta >= 0 ? '+' : '') . $activeUsersDelta . " ({$activeUsersDeltaPct}%)")),

            Stat::make('Avg activities / active user', $avgActivitiesPerActiveUser),

            Stat::make('Avg distance / active user', $avgDistanceKmPerActiveUser . ' km'),

            Stat::make('Top by distance', $topLabel),
        ];
    }
}
