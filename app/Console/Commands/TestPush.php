<?php

declare(strict_types=1);

namespace Strava\Console\Commands;

use Illuminate\Console\Command;
use Strava\Jobs\SendPushJob;
use Strava\Models\User;

class TestPush extends Command
{
    protected $signature = "push:test {userId}";
    protected $description = "Send test push notification to given user";

    public function handle(): int
    {
        $userId = (int)$this->argument("userId");

        $user = User::query()->find($userId);

        if (!$user) {
            $this->error("User not found");

            return self::FAILURE;
        }

        SendPushJob::dispatch(
            userId: $user->id,
            title: "Test push 🚀",
            body: "Jeśli to widzisz, FCM działa poprawnie",
            data: [
                "screen" => "profile",
            ],
        )->onQueue("push");

        $this->info("Push job dispatched ✅");

        return self::SUCCESS;
    }
}
