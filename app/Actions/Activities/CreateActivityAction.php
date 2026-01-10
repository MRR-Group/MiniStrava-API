<?php

declare(strict_types=1);

namespace Strava\Actions\Activities;

use Strava\Models\Activity;

class CreateActivityAction
{
    public function execute(int $userId, array $data): Activity
    {
        $activity = new Activity();

        $activity->user_id = $userId;
        $activity->title = $data["title"];
        $activity->notes = $data["notes"] ?? "";
        $activity->duration_s = (int)$data["duration_s"];
        $activity->distance_m = (int)$data["distance_m"];
        $activity->activity_type = $data["activity_type"];

        $activity->save();

        return $activity;
    }
}
