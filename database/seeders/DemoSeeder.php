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
                ["name" => "Ewa", "email" => "ewa@test.com"],
                ["name" => "Filip", "email" => "filip@test.com"],
            ])->map(fn($u) => User::query()->create([
                "name" => $u["name"],
                "email" => $u["email"],
                "password" => Hash::make("password"),
            ]));

            $activityTypes = [
                "run" => [
                    "distance" => [3000, 12000],
                    "duration" => [900, 3600],
                ],
                "walk" => [
                    "distance" => [1000, 6000],
                    "duration" => [900, 5400],
                ],
                "ride" => [
                    "distance" => [8000, 60000],
                    "duration" => [1200, 7200],
                ],
            ];

            foreach (range(0, 28) as $daysAgo) {
                $day = $now->copy()->subDays($daysAgo)->startOfDay();

                $activeUsers = $users->shuffle()->take(rand(1, $users->count()));

                foreach ($activeUsers as $user) {
                    foreach (range(1, rand(1, 2)) as $n) {
                        $type = collect(array_keys($activityTypes))->random();
                        $cfg = $activityTypes[$type];

                        Activity::query()->create([
                            "user_id" => $user->id,
                            "title" => ucfirst($type),
                            "notes" => "Demo {$type}",
                            "distance_m" => rand($cfg["distance"][0], $cfg["distance"][1]),
                            "duration_s" => rand($cfg["duration"][0], $cfg["duration"][1]),
                            "activity_type" => $type,
                            "started_at" => $day->copy()->addMinutes(rand(0, 1000)),
                            "created_at" => $day->copy()->addMinutes(rand(0, 1000)),
                        ]);
                    }
                }
            }

            $peakDay = $now->copy()->subDays(3)->setTime(10, 0);

            foreach ($users as $user) {
                Activity::query()->create([
                    "user_id" => $user->id,
                    "title" => "Group ride",
                    "notes" => "Everyone active",
                    "distance_m" => rand(15000, 40000),
                    "duration_s" => rand(1800, 5400),
                    "activity_type" => "ride",
                    "started_at" => $peakDay,
                    "created_at" => $peakDay,
                ]);
            }
        });
    }
}
