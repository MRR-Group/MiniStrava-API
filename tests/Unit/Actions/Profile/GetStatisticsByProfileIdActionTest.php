<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Profile;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Strava\Actions\Profile\GetStatisticsByProfileIdAction;
use Strava\Models\Activity;
use Strava\Models\User;
use Tests\TestCase;

class GetStatisticsByProfileIdActionTest extends TestCase
{
    use RefreshDatabase;

    public function testReturnsZerosAndNullDatesWhenUserHasNoActivities(): void
    {
        $user = User::factory()->create();

        $action = new GetStatisticsByProfileIdAction();

        $stats = $action->execute($user->id);

        $this->assertSame(0, (int)$stats["activitiesCount"]);
        $this->assertSame(0, (int)$stats["totalDistanceM"]);
        $this->assertSame(0, (int)$stats["totalDurationS"]);

        $this->assertSame(0.0, (float)$stats["avgDistanceM"]);
        $this->assertSame(0.0, (float)$stats["avgDurationS"]);

        $this->assertSame(0.0, (float)$stats["avgSpeedMps"]);
        $this->assertSame(0.0, (float)$stats["avgSpeedKph"]);
        $this->assertSame(0.0, (float)$stats["avgPaceSecPerKm"]);

        $this->assertSame(0, (int)$stats["longestDistanceM"]);
        $this->assertSame(0, (int)$stats["longestDurationS"]);

        $this->assertNull($stats["firstActivity"]);
        $this->assertNull($stats["lastActivity"]);
    }

    public function testCalculatesTotalsAveragesSpeedPaceLongestAndFirstLast(): void
    {
        $user = User::factory()->create();

        Activity::query()->insert([
            [
                "user_id" => $user->id,
                "title" => "A1",
                "notes" => "",
                "duration_s" => 600,
                "distance_m" => 2000,
                "activity_type" => "run",
                "created_at" => Carbon::parse("2026-01-01 10:00:00"),
                "updated_at" => Carbon::parse("2026-01-01 10:00:00"),
            ],
            [
                "user_id" => $user->id,
                "title" => "A2",
                "notes" => "",
                "duration_s" => 300,
                "distance_m" => 1000,
                "activity_type" => "run",
                "created_at" => Carbon::parse("2026-01-03 12:00:00"),
                "updated_at" => Carbon::parse("2026-01-03 12:00:00"),
            ],
        ]);

        $action = new GetStatisticsByProfileIdAction();

        $stats = $action->execute($user->id);

        $this->assertSame(2, (int)$stats["activitiesCount"]);
        $this->assertSame(3000, (int)$stats["totalDistanceM"]);
        $this->assertSame(900, (int)$stats["totalDurationS"]);

        $this->assertSame(1500.0, (float)$stats["avgDistanceM"]);
        $this->assertSame(450.0, (float)$stats["avgDurationS"]);

        $avgSpeedMps = 3000 / 900;
        $avgSpeedKph = $avgSpeedMps * 3.6;
        $avgPaceSecPerKm = 900 / (3000 / 1000);

        $this->assertEqualsWithDelta($avgSpeedMps, (float)$stats["avgSpeedMps"], 0.000001);
        $this->assertEqualsWithDelta($avgSpeedKph, (float)$stats["avgSpeedKph"], 0.000001);
        $this->assertEqualsWithDelta($avgPaceSecPerKm, (float)$stats["avgPaceSecPerKm"], 0.000001);

        $this->assertSame(2000, (int)$stats["longestDistanceM"]);
        $this->assertSame(600, (int)$stats["longestDurationS"]);

        $this->assertSame("2026-01-01 10:00:00", (string)$stats["firstActivity"]);
        $this->assertSame("2026-01-03 12:00:00", (string)$stats["lastActivity"]);
    }
}
