<?php

declare(strict_types=1);

namespace Strava\Actions\Activities;

use Illuminate\Http\UploadedFile;

class StoreActivityPhotoAction
{
    public function execute(UploadedFile $photo, $activityId): false|string
    {
        return $photo->storeAs("", "activity_" . $activityId . ".png", "activityPhotos");
    }
}
