<?php

declare(strict_types=1);

namespace Strava\Actions\Leaderboard;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;
use Strava\Enums\RankingTypes;
use Strava\Models\Activity;

class GetWeeklyRankingAction
{
    public function execute(RankingTypes $field, int $limit = 10, ?string $from = null, ?string $to = null): Collection
    {
        $fromDate = $from
            ? Carbon::parse($from)->startOfDay()
            : now()->startOfWeek(CarbonInterface::MONDAY)->startOfDay();

        $toDate = $to
            ? Carbon::parse($to)->addDay()->startOfDay()
            : $fromDate->copy()->addWeek();

        return Activity::query()
            ->whereBetween("created_at", [$fromDate, $toDate])
            ->select("user_id")
            ->selectRaw("COALESCE(SUM($field->value), 0) as score")
            ->groupBy("user_id")
            ->orderByDesc("score")
            ->limit($limit)
            ->get();
    }
}
