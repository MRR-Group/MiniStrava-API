<?php

declare(strict_types=1);

namespace Strava\Actions\Avatars;

use Illuminate\Support\Facades\Storage;

class GetAvatarAction
{
    public function execute(int $userId): ?string
    {
        $filename = $userId . ".png";

        if (Storage::disk("avatars")->exists($filename)) {
            return Storage::disk("avatars")->get($filename);
        }

        return null;
    }
}
