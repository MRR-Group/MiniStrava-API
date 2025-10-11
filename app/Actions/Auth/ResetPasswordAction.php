<?php

declare(strict_types=1);

namespace Strava\Actions\Auth;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Strava\Models\User;

class ResetPasswordAction
{
    public function execute(array $credentials): bool
    {
        $status = Password::reset($credentials, function (User $user, string $password): void {
            $user->forceFill([
                "password" => Hash::make($password),
            ])->setRememberToken(Str::random(60));

            $user->save();

            event(new PasswordReset($user));
        });

        return $status === Password::PASSWORD_RESET;
    }
}
