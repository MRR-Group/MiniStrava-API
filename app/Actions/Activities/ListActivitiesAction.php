<?php

declare(strict_types=1);

namespace Strava\Actions\Activities;

use Illuminate\Pagination\LengthAwarePaginator;
use Strava\Models\Activity;

class ListActivitiesAction
{
    public function execute(int $userId): LengthAwarePaginator
    {
        return Activity::query()
            ->where("user_id", $userId)
            ->latest()
            ->paginate(10);
    }
}
