<?php

declare(strict_types=1);

namespace Strava\Actions\Activities;

use Illuminate\Support\Facades\Storage;

class GetActivityPhotoAction
{
    public function execute(int $activityId): ?string
    {
        $filename = "activity_" . $activityId . ".png";

        if (Storage::disk("activityPhotos")->exists($filename)) {
            return Storage::disk("activityPhotos")->get($filename);
        }

        return null;
    }
}
