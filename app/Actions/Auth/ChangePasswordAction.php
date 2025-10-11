<?php

namespace Strava\Actions\Auth;

use Illuminate\Support\Facades\Hash;
use Strava\Models\User;

class ChangePasswordAction
{
    public function execute(User $user, string $currentPassword, string $newPassword): bool
    {
        if (!Hash::check($currentPassword, $user->password))
        {
            return false;
        }

        $hashedPassword = Hash::make($newPassword);

        $user->password = $hashedPassword;
        $user->save();

        return true;
    }
}
