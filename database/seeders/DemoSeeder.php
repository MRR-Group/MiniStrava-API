<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Strava\Models\Activity;
use Strava\Models\User;
use Throwable;

class DemoSeeder extends Seeder
{
    /**
     * @throws Throwable
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            Activity::query()->delete();
            User::query()->delete();

            $now = now();

            $users = collect([
                ["name" => "Adam", "email" => "adam@test.com"],
                ["name" => "Bartek", "email" => "bartek@test.com"],
                ["name" => "Celina", "email" => "celina@test.com"],
                ["name" => "Daria", "email" => "daria@test.com"],
            ])->map(fn($u) => User::query()->create([
                "name" => $u["name"],
                "email" => $u["email"],
                "password" => Hash::make("password"),
            ]));

            foreach ($users as $i => $user) {
                Activity::query()->create([
                    "user_id" => $user->id,
                    "title" => "Run",
                    "notes" => "Morning run",
                    "distance_m" => 3000 + ($i * 2000),
                    "duration_s" => 900 + ($i * 600),
                    "activityType" => "run",
                    "created_at" => $now->copy()->startOfWeek()->addDays($i),
                ]);

                Activity::query()->create([
                    "user_id" => $user->id,
                    "title" => "Evening run",
                    "notes" => "Easy pace",
                    "distance_m" => 2000 + ($i * 1500),
                    "duration_s" => 800 + ($i * 500),
                    "activityType" => "run",
                    "created_at" => $now->copy()->startOfWeek()->addDays($i)->addHours(5),
                ]);
            }

            foreach ($users as $i => $user) {
                Activity::query()->create([
                    "user_id" => $user->id,
                    "title" => "Last week run",
                    "notes" => "Old activity",
                    "distance_m" => 4000 + ($i * 1000),
                    "duration_s" => 1200 + ($i * 400),
                    "activityType" => "run",
                    "created_at" => $now->copy()->subWeek()->startOfWeek()->addDays($i),
                ]);
            }
        });
    }
}
