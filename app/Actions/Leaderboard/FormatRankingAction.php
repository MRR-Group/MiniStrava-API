<?php

declare(strict_types=1);

namespace Strava\Actions\Leaderboard;

use Illuminate\Support\Collection;
use Strava\Http\Resources\UserResource;
use Strava\Models\User;

class FormatRankingAction
{
    public function execute(Collection $rows): Collection
    {
        $users = User::query()
            ->whereIn("id", $rows->pluck("user_id"))
            ->get()
            ->keyBy("id");

        return $rows->values()->map(function ($r, int $i) use ($users) {
            $score = (int)$r->score;

            return [
                "place" => $i + 1,
                "score" => $score,
                "user" => UserResource::make($users->firstWhere("id", $r->user_id)),
            ];
        });
    }
}
