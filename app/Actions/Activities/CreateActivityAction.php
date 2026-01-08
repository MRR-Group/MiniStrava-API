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
        $activity->duration_s = $data["duration_s"];
        $activity->distance_m = $data["distance_m"];
        $activity->activityType = $data["activityType"];

        $activity->save();

        return $activity;
    }
}
