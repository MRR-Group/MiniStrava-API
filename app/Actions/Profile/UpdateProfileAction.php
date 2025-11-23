<?php

declare(strict_types=1);

namespace Strava\Actions\Profile;

use Strava\Models\User;

class UpdateProfileAction
{
    public function execute(User $user, array $data): ?User
    {
        $user->fill($data);
        $user->save();

        return $user->fresh();
    }
}
