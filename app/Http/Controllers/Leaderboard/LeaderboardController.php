<?php

declare(strict_types=1);

namespace Strava\Http\Controllers\Leaderboard;

use Illuminate\Http\JsonResponse;
use Strava\Actions\Leaderboard\FormatRankingAction;
use Strava\Actions\Leaderboard\GetWeeklyRankingAction;
use Strava\Enums\RankingTypes;
use Strava\Http\Controllers\Controller;
use Strava\Http\Requests\LeaderboardRequest;

class LeaderboardController extends Controller
{
    public function show(
        string $type,
        LeaderboardRequest $request,
        GetWeeklyRankingAction $getWeeklyRankingAction,
        FormatRankingAction $formatRankingAction,
    ): JsonResponse {
        $rankingType = match ($type) {
            "distance" => RankingTypes::Distance,
            "duration" => RankingTypes::Duration,
            default => null,
        };

        if ($rankingType === null) {
            abort(404);
        }

        $validated = $request->validated();

        $from = $validated["from"] ?? null;
        $to = $validated["to"] ?? null;
        $limit = (int)($validated["limit"] ?? 10);

        $rows = $getWeeklyRankingAction->execute(
            field: $rankingType,
            limit: max(1, $limit),
            from: $from,
            to: $to,
        );

        return response()->json([
            "data" => $formatRankingAction->execute($rows),
        ]);
    }
}
