<?php

declare(strict_types=1);

namespace Strava\Actions\Profile;

use Strava\Models\Activity;

class GetStatisticsByProfileIdAction
{
    public function execute(int $userId): array
    {
        $statistics = Activity::query()->where("user_id", $userId)
            ->selectRaw("COUNT(*) as activities_count")
            ->selectRaw("COALESCE(SUM(distance_m), 0) as total_distance_m")
            ->selectRaw("COALESCE(SUM(duration_s), 0) as total_duration_s")
            ->selectRaw("COALESCE(AVG(distance_m), 0) as avg_distance_m")
            ->selectRaw("COALESCE(AVG(duration_s), 0) as avg_duration_s")
            ->selectRaw("COALESCE(MAX(distance_m), 0) as longest_distance_m")
            ->selectRaw("COALESCE(MAX(duration_s), 0) as longest_duration_s")
            ->selectRaw("MIN(created_at) as first_activity_at")
            ->selectRaw("MAX(created_at) as last_activity_at")
            ->firstOrFail();

        $count = $statistics->activities_count;
        $totalDistanceM = $statistics->total_distance_m;
        $totalDurationS = $statistics->total_duration_s;

        $avgSpeedMps = $totalDurationS > 0 ? $totalDistanceM / $totalDurationS : 0.0;
        $avgSpeedKph = $avgSpeedMps * 3.6;
        $avgPaceSecPerKm = $totalDistanceM > 0 ? ($totalDurationS / ($totalDistanceM / 1000)) : 0.0;

        return [
            "activitiesCount" => $count,
            "totalDistanceM" => $totalDistanceM,
            "totalDurationS" => $totalDurationS,

            "avgDistance_M" => (float)$statistics->avg_distance_m,
            "avgDuration_S" => (float)$statistics->avg_duration_s,

            "avgSpeedMps" => $avgSpeedMps,
            "avgSpeedKph" => $avgSpeedKph,
            "avgPaceSecPerKm" => $avgPaceSecPerKm,

            "longestDistanceM" => (int)$statistics->longest_distance_m,
            "longestDurationS" => (int)$statistics->longest_duration_s,

            "firstActivity" => $statistics->first_activity_at,
            "lastActivity" => $statistics->last_activity_at,
        ];
    }
}
