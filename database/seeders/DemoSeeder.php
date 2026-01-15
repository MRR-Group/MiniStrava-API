<?php

declare(strict_types=1);

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Strava\Enums\Gender;
use Strava\Models\Activity;
use Strava\Models\GpsPoint;
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
            GpsPoint::query()->delete();
            Activity::query()->delete();
            User::query()->delete();

            $now = now();

            $users = collect([
                [
                    "name" => "Adam",
                    "first_name" => "Adam",
                    "last_name" => "Kowalski",
                    "email" => "adam@test.com",
                    "gender" => Gender::Male,
                ],
                [
                    "name" => "Bartek",
                    "first_name" => "Bartosz",
                    "last_name" => "Nowak",
                    "email" => "bartek@test.com",
                    "gender" => Gender::Male,
                ],
                [
                    "name" => "Celina",
                    "first_name" => "Celina",
                    "last_name" => "Wiśniewska",
                    "email" => "celina@test.com",
                    "gender" => Gender::Female,
                ],
                [
                    "name" => "Daria",
                    "first_name" => "Daria",
                    "last_name" => "Kamińska",
                    "email" => "daria@test.com",
                    "gender" => Gender::Female,
                ],
                [
                    "name" => "Ewa",
                    "first_name" => "Ewa",
                    "last_name" => "Lewandowska",
                    "email" => "ewa@test.com",
                    "gender" => Gender::Female,
                ],
                [
                    "name" => "Filip",
                    "first_name" => "Filip",
                    "last_name" => "Zieliński",
                    "email" => "filip@test.com",
                    "gender" => Gender::Male,
                ],
            ])->map(fn(array $u) => User::query()->create([
                "name" => $u["name"],
                "first_name" => $u["first_name"],
                "last_name" => $u["last_name"],
                "email" => $u["email"],
                "password" => Hash::make("password"),
                "birth_date" => Carbon::now()->subYears(rand(18, 45))->subDays(rand(0, 365)),
                "height" => rand(155, 195),
                "weight" => rand(55, 95),
                "gender" => $u["gender"],
                "email_verified_at" => now(),
            ]));

            $activityTypes = [
                "run" => [
                    "distance" => [3000, 12000],
                    "duration" => [900, 3600],
                    "speedMps" => [2.2, 4.0],
                ],
                "walk" => [
                    "distance" => [1000, 6000],
                    "duration" => [900, 5400],
                    "speedMps" => [0.9, 1.7],
                ],
                "ride" => [
                    "distance" => [8000, 60000],
                    "duration" => [1200, 7200],
                    "speedMps" => [4.0, 10.0],
                ],
            ];

            foreach (range(0, 28) as $daysAgo) {
                $day = $now->copy()->subDays($daysAgo)->startOfDay();
                $activeUsers = $users->shuffle()->take(rand(1, $users->count()));

                foreach ($activeUsers as $user) {
                    foreach (range(1, rand(1, 2)) as $n) {
                        $type = collect(array_keys($activityTypes))->random();
                        $cfg = $activityTypes[$type];

                        $startedAt = $day->copy()->addMinutes(rand(0, 1000));

                        $activity = Activity::query()->create([
                            "user_id" => $user->id,
                            "title" => ucfirst($type),
                            "notes" => "Demo {$type}",
                            "distance_m" => rand($cfg["distance"][0], $cfg["distance"][1]),
                            "duration_s" => rand($cfg["duration"][0], $cfg["duration"][1]),
                            "activity_type" => $type,
                            "started_at" => $startedAt,
                            "created_at" => $startedAt,
                        ]);

                        if (rand(0, 100) < 60) {
                            $this->seedGpsPointsForActivity($activity, $cfg, $startedAt);
                        }
                    }
                }
            }

            $peakDay = $now->copy()->subDays(3)->setTime(10, 0);

            foreach ($users as $user) {
                $cfg = $activityTypes["ride"];

                $activity = Activity::query()->create([
                    "user_id" => $user->id,
                    "title" => "Group ride",
                    "notes" => "Everyone active",
                    "distance_m" => rand(15000, 40000),
                    "duration_s" => rand(1800, 5400),
                    "activity_type" => "ride",
                    "started_at" => $peakDay,
                    "created_at" => $peakDay,
                ]);

                $this->seedGpsPointsForActivity($activity, $cfg, $peakDay);
            }
        });
    }

    private function seedGpsPointsForActivity(Activity $activity, array $cfg, Carbon $startedAt): void
    {
        $durationS = (int)$activity->duration_s;
        $pointsCount = min(900, max(30, (int)floor($durationS / 5)));

        $lat = 52.2297 + (rand(-200, 200) / 10000);
        $lng = 21.0122 + (rand(-200, 200) / 10000);

        $rows = [];
        $tsBase = $startedAt->utc()->timestamp;
        $now = now();

        for ($i = 0; $i < $pointsCount; $i++) {
            $lat += rand(-5, 5) / 100000;
            $lng += rand(-5, 5) / 100000;

            $rows[] = [
                "activity_id" => $activity->id,
                "lat" => $lat,
                "lng" => $lng,
                "alt_m" => rand(80, 160),
                "accuracy_m" => rand(3, 15),
                "timestamp" => $tsBase + ($i * 5),
                "created_at" => $now,
                "updated_at" => $now,
            ];
        }

        foreach (array_chunk($rows, 2000) as $chunk) {
            GpsPoint::query()->insert($chunk);
        }
    }
}
