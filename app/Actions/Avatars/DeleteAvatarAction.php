<?php

declare(strict_types=1);

namespace Strava\Actions\Avatars;

use Illuminate\Support\Facades\Storage;

class DeleteAvatarAction
{
    public function execute(int $userId): bool
    {
        $filename = $userId . ".png";

        if (Storage::disk("avatars")->exists($filename)) {
            Storage::disk("avatars")->delete($filename);

            return true;
        }

        return false;
    }
}
