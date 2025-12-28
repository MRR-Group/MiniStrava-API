<?php

declare(strict_types=1);

namespace Strava\Actions\Avatars;

use Illuminate\Http\UploadedFile;

class ChangeAvatarAction
{
    public function execute(UploadedFile $uploadedFile, int $userId): bool
    {
        $stored = $uploadedFile->storeAs("", $userId . ".png", "avatars");

        return $stored !== false;
    }
}
