<?php

declare(strict_types=1);

namespace Strava\Actions\Auth;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Random\RandomException;

class GenerateResetCodeAction
{
    public function execute(string $email): string
    {
        $code = "";

        try {
            $code = (string)random_int(100000, 999999);
        } catch (RandomException $e) {
            report($e);

            abort(500, "Unable to generate secure reset code.");
        }

        DB::table("password_reset_tokens")->updateOrInsert(
            ["email" => $email],
            [
                "email" => $email,
                "token" => Hash::make($code),
                "created_at" => now(),
            ],
        );

        return $code;
    }
}
