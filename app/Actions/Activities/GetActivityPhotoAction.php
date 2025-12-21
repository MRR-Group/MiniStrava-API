<?php

declare(strict_types=1);

namespace Strava\Actions\Activities;

use Illuminate\Support\Facades\Storage;

class GetActivityPhotoAction
{
    public function execute(int $userId): ?string
    {
        $filename = "activity_" . $userId . ".png";

        if (Storage::disk("activityPhotos")->exists($filename)) {
            return Storage::disk("activityPhotos")->get($filename);
        }

        return null;
    }
}
