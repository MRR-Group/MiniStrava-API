<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Activities;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Strava\Actions\Activities\StoreActivityGpsPointsAction;
use Strava\Models\Activity;
use Strava\Models\GpsPoint;
use Strava\Models\User;
use Tests\TestCase;

class StoreActivityGpsPointsActionTest extends TestCase
{
    use RefreshDatabase;

    public function testDoesNothingWhenPointsAreEmpty(): void
    {
        $action = new StoreActivityGpsPointsAction();

        $action->execute(123, []);

        $this->assertSame(0, GpsPoint::query()->count());
    }

    public function testInsertsPointsWithCastsAndNullableFields(): void
    {
        $user = User::factory()->create();

        $activity = Activity::query()->create([
            "user_id" => $user->id,
            "title" => "Walk",
            "notes" => "",
            "duration_s" => 10,
            "distance_m" => 10,
            "activity_type" => "walk",
            "started_at" => now(),
        ]);

        $action = new StoreActivityGpsPointsAction();

        $action->execute($activity->id, [
            [
                "id" => 1,
                "lat" => "52.1",
                "lng" => "21.2",
                "alt_m" => "100.5",
                "accuracy_m" => null,
                "timestamp" => "1700000000",
            ],
            [
                "id" => 2,
                "lat" => 53,
                "lng" => 22,
                "timestamp" => 1700000001,
            ],
        ]);

        $this->assertSame(
            2,
            GpsPoint::query()->where("activity_id", $activity->id)->count(),
        );

        $p1 = GpsPoint::query()
            ->where("activity_id", $activity->id)
            ->orderBy("timestamp")
            ->first();

        $this->assertNotNull($p1);
        $this->assertSame($activity->id, (int)$p1->activity_id);
        $this->assertSame(52.1, (float)$p1->lat);
        $this->assertSame(21.2, (float)$p1->lng);
        $this->assertSame(100.5, (float)$p1->alt_m);
        $this->assertNull($p1->accuracy_m);
        $this->assertSame(1700000000, (int)$p1->timestamp);

        $p2 = GpsPoint::query()
            ->where("activity_id", $activity->id)
            ->orderBy("timestamp", "desc")
            ->first();

        $this->assertNotNull($p2);
        $this->assertSame(53.0, (float)$p2->lat);
        $this->assertSame(22.0, (float)$p2->lng);
        $this->assertNull($p2->alt_m);
        $this->assertNull($p2->accuracy_m);
        $this->assertSame(1700000001, (int)$p2->timestamp);
    }

    public function testInsertsMoreThan2000PointsUsingChunks(): void
    {
        $user = User::factory()->create();

        $activity = Activity::query()->create([
            "user_id" => $user->id,
            "title" => "Run",
            "notes" => "",
            "duration_s" => 100,
            "distance_m" => 1000,
            "activity_type" => "run",
            "started_at" => now(),
        ]);

        $action = new StoreActivityGpsPointsAction();

        $points = [];

        for ($i = 0; $i < 2001; $i++) {
            $points[] = [
                "id" => $i + 1,
                "lat" => 52.0 + ($i / 100000),
                "lng" => 21.0 + ($i / 100000),
                "timestamp" => 1700000000 + $i,
            ];
        }

        $action->execute($activity->id, $points);

        $this->assertSame(
            2001,
            GpsPoint::query()->where("activity_id", $activity->id)->count(),
        );
    }
}
