<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Leaderboard;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Strava\Actions\Leaderboard\GetWeeklyRankingAction;
use Strava\Enums\RankingTypes;
use Strava\Models\Activity;
use Strava\Models\User;
use Tests\TestCase;

class GetWeeklyRankingActionTest extends TestCase
{
    use RefreshDatabase;

    public function testDistanceRankingSumsDistanceOrdersDescAndLimits(): void
    {
        $u1 = User::factory()->create(["email" => "u1@gmail.com"]);
        $u2 = User::factory()->create(["email" => "u2@gmail.com"]);
        $u3 = User::factory()->create(["email" => "u3@gmail.com"]);

        Activity::query()->insert([
            [
                "user_id" => $u1->id,
                "title" => "A1",
                "notes" => "",
                "duration_s" => 100,
                "distance_m" => 1000,
                "activity_type" => "run",
                "created_at" => Carbon::parse("2026-01-02 10:00:00"),
                "updated_at" => Carbon::parse("2026-01-02 10:00:00"),
                "started_at" => now(),
            ],
            [
                "user_id" => $u1->id,
                "title" => "A2",
                "notes" => "",
                "duration_s" => 200,
                "distance_m" => 400,
                "activity_type" => "walk",
                "created_at" => Carbon::parse("2026-01-03 10:00:00"),
                "updated_at" => Carbon::parse("2026-01-03 10:00:00"),
                "started_at" => now(),
            ],
            [
                "user_id" => $u2->id,
                "title" => "B1",
                "notes" => "",
                "duration_s" => 50,
                "distance_m" => 2000,
                "activity_type" => "ride",
                "created_at" => Carbon::parse("2026-01-02 10:00:00"),
                "updated_at" => Carbon::parse("2026-01-02 10:00:00"),
                "started_at" => now(),
            ],
            [
                "user_id" => $u3->id,
                "title" => "Outside",
                "notes" => "",
                "duration_s" => 999,
                "distance_m" => 9999,
                "activity_type" => "other",
                "created_at" => Carbon::parse("2025-12-25 10:00:00"),
                "updated_at" => Carbon::parse("2025-12-25 10:00:00"),
                "started_at" => now(),
            ],
        ]);

        $action = new GetWeeklyRankingAction();

        $rows = $action->execute(
            field: RankingTypes::Distance,
            limit: 1,
            from: "2026-01-01",
            to: "2026-01-07",
        );

        $this->assertCount(1, $rows);
        $this->assertSame($u2->id, $rows[0]->user_id);
        $this->assertSame(2000, (int)$rows[0]->score);
    }

    public function testDurationRankingSumsDuration(): void
    {
        $u1 = User::factory()->create(["email" => "u1@gmail.com"]);
        $u2 = User::factory()->create(["email" => "u2@gmail.com"]);

        Activity::query()->insert([
            [
                "user_id" => $u1->id,
                "title" => "A1",
                "notes" => "",
                "duration_s" => 500,
                "distance_m" => 1,
                "activity_type" => "run",
                "created_at" => Carbon::parse("2026-01-02 10:00:00"),
                "updated_at" => Carbon::parse("2026-01-02 10:00:00"),
                "started_at" => now(),
            ],
            [
                "user_id" => $u2->id,
                "title" => "B1",
                "notes" => "",
                "duration_s" => 600,
                "distance_m" => 1,
                "activity_type" => "walk",
                "created_at" => Carbon::parse("2026-01-02 10:00:00"),
                "updated_at" => Carbon::parse("2026-01-02 10:00:00"),
                "started_at" => now(),
            ],
        ]);

        $action = new GetWeeklyRankingAction();

        $rows = $action->execute(
            field: RankingTypes::Duration,
            limit: 10,
            from: "2026-01-01",
            to: "2026-01-07",
        );

        $this->assertCount(2, $rows);
        $this->assertSame($u2->id, $rows[0]->user_id);
        $this->assertSame(600, (int)$rows[0]->score);
        $this->assertSame($u1->id, $rows[1]->user_id);
        $this->assertSame(500, (int)$rows[1]->score);
    }

    public function testToDateIsInclusive(): void
    {
        $u1 = User::factory()->create(["email" => "u1@gmail.com"]);

        Activity::query()->insert([
            [
                "user_id" => $u1->id,
                "title" => "Edge",
                "notes" => "",
                "duration_s" => 1,
                "distance_m" => 111,
                "activity_type" => "run",
                "created_at" => Carbon::parse("2026-01-07 23:59:59"),
                "updated_at" => Carbon::parse("2026-01-07 23:59:59"),
                "started_at" => now(),
            ],
        ]);

        $action = new GetWeeklyRankingAction();

        $rows = $action->execute(
            field: RankingTypes::Distance,
            limit: 10,
            from: "2026-01-01",
            to: "2026-01-07",
        );

        $this->assertCount(1, $rows);
        $this->assertSame(111, (int)$rows[0]->score);
    }
}
