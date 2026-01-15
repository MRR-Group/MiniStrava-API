<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Strava\Models\Activity;
use Strava\Models\GpsPoint;
use Strava\Models\User;
use Tests\TestCase;

class GpsPointTest extends TestCase
{
    use RefreshDatabase;

    public function testCastsFieldsToExpectedTypes(): void
    {
        $user = User::factory()->create();
        $activity = Activity::query()->create([
            "user_id" => $user->id,
            "title" => "Run",
            "notes" => "",
            "duration_s" => 10,
            "distance_m" => 10,
            "activity_type" => "run",
            "started_at" => now(),
        ]);

        $gps = GpsPoint::query()->create([
            "activity_id" => $activity->id,
            "lat" => "52.10",
            "lng" => "21.20",
            "alt_m" => "100.50",
            "accuracy_m" => "3.40",
            "timestamp" => "1700000000",
        ]);

        $gps->refresh();

        $this->assertIsFloat($gps->lat);
        $this->assertIsFloat($gps->lng);
        $this->assertIsFloat($gps->alt_m);
        $this->assertIsFloat($gps->accuracy_m);
        $this->assertIsInt($gps->timestamp);

        $this->assertSame(52.10, $gps->lat);
        $this->assertSame(21.20, $gps->lng);
        $this->assertSame(100.50, $gps->alt_m);
        $this->assertSame(3.40, $gps->accuracy_m);
        $this->assertSame(1700000000, $gps->timestamp);
    }

    public function testBelongsToActivityRelation(): void
    {
        $user = User::factory()->create();
        $activity = Activity::query()->create([
            "user_id" => $user->id,
            "title" => "Run",
            "notes" => "",
            "duration_s" => 10,
            "distance_m" => 10,
            "activity_type" => "run",
            "started_at" => now(),
        ]);

        GpsPoint::query()->insert([
            "activity_id" => $activity->id,
            "lat" => 1.0,
            "lng" => 2.0,
            "alt_m" => 3.0,
            "accuracy_m" => 4.0,
            "timestamp" => 1700000000,
            "created_at" => now(),
            "updated_at" => now(),
        ]);

        $gps = GpsPoint::query()->where("activity_id", $activity->id)->firstOrFail();

        $this->assertTrue($gps->activity->is($activity));
        $this->assertSame($activity->id, $gps->activity->id);
    }
}
