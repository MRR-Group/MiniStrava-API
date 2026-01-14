<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Leaderboard;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Strava\Models\Activity;
use Strava\Models\User;
use Tests\TestCase;

class LeaderboardControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testLeaderboardReturns404ForUnknownType(): void
    {
        $response = $this->getJson($this->leaderboardUrl("unknown"));
        $response->assertNotFound();
    }

    public function testLeaderboardDistanceOrdersBySumDescAndFormatsPlaces(): void
    {
        $u1 = User::factory()->create(["email" => "u1@gmail.com"]);
        $u2 = User::factory()->create(["email" => "u2@gmail.com"]);
        $u3 = User::factory()->create(["email" => "u3@gmail.com"]);

        Activity::query()->insert([
            [
                "user_id" => $u1->id,
                "title" => "Run",
                "notes" => "Good run",
                "duration_s" => 100,
                "distance_m" => 1000,
                "activity_type" => "run",
                "created_at" => Carbon::parse("2026-01-02 10:00:00"),
                "updated_at" => Carbon::parse("2026-01-02 10:00:00"),
            ],
            [
                "user_id" => $u1->id,
                "title" => "Walk",
                "notes" => null,
                "duration_s" => 200,
                "distance_m" => 400,
                "activity_type" => "walk",
                "created_at" => Carbon::parse("2026-01-03 10:00:00"),
                "updated_at" => Carbon::parse("2026-01-03 10:00:00"),
            ],
            [
                "user_id" => $u2->id,
                "title" => "Other",
                "notes" => null,
                "duration_s" => 50,
                "distance_m" => 2000,
                "activity_type" => "other",
                "created_at" => Carbon::parse("2026-01-02 10:00:00"),
                "updated_at" => Carbon::parse("2026-01-02 10:00:00"),
            ],
            [
                "user_id" => $u3->id,
                "title" => "Other",
                "notes" => null,
                "duration_s" => 999,
                "distance_m" => 10,
                "activity_type" => "other",
                "created_at" => Carbon::parse("2025-12-25 10:00:00"),
                "updated_at" => Carbon::parse("2025-12-25 10:00:00"),
            ],
        ]);

        $response = $this->getJson($this->leaderboardUrl("distance", [
            "from" => "2026-01-01",
            "to" => "2026-01-07",
        ]));

        $response->assertOk();
        $response->assertJsonStructure([
            "data" => [
                ["place", "score", "user"],
            ],
        ]);

        $data = $response->json("data");

        $this->assertIsArray($data);
        $this->assertCount(2, $data);

        $response->assertJsonStructure([
            "data" => [
                ["place", "score", "user"],
            ],
        ]);

        $this->assertSame(1, $data[0]["place"]);
        $this->assertSame(2000, $data[0]["score"]);
        $this->assertSame($u2->id, $data[0]["user"]["id"]);

        $this->assertSame(2, $data[1]["place"]);
        $this->assertSame(1400, $data[1]["score"]);
        $this->assertSame($u1->id, $data[1]["user"]["id"]);
    }

    public function testLeaderboardDurationUsesDurationSSum(): void
    {
        $u1 = User::factory()->create(["email" => "u1@gmail.com"]);
        $u2 = User::factory()->create(["email" => "u2@gmail.com"]);

        Activity::query()->insert([
            [
                "user_id" => $u1->id,
                "title" => "Walk",
                "notes" => null,
                "duration_s" => 500,
                "distance_m" => 100,
                "activity_type" => "walk",
                "created_at" => Carbon::parse("2026-01-02 10:00:00"),
                "updated_at" => Carbon::parse("2026-01-02 10:00:00"),
            ],
            [
                "user_id" => $u2->id,
                "title" => "Other",
                "notes" => null,
                "duration_s" => 600,
                "distance_m" => 9999,
                "activity_type" => "other",
                "created_at" => Carbon::parse("2026-01-02 10:00:00"),
                "updated_at" => Carbon::parse("2026-01-02 10:00:00"),
            ],
        ]);

        $response = $this->getJson($this->leaderboardUrl("duration", [
            "from" => "2026-01-01",
            "to" => "2026-01-07",
        ]));

        $response->assertOk();

        $data = $response->json("data");
        $this->assertCount(2, $data);

        $this->assertSame($u2->id, $data[0]["user"]["id"]);
        $this->assertSame(600, $data[0]["score"]);

        $this->assertSame($u1->id, $data[1]["user"]["id"]);
        $this->assertSame(500, $data[1]["score"]);
    }

    public function testLeaderboardLimitIsApplied(): void
    {
        $u1 = User::factory()->create(["email" => "u1@gmail.com"]);
        $u2 = User::factory()->create(["email" => "u2@gmail.com"]);
        $u3 = User::factory()->create(["email" => "u3@gmail.com"]);

        Activity::query()->insert([
            [
                "user_id" => $u1->id,
                "title" => "Other",
                "notes" => null,
                "duration_s" => 1,
                "distance_m" => 100,
                "activity_type" => "other",
                "created_at" => Carbon::parse("2026-01-02 10:00:00"),
                "updated_at" => Carbon::parse("2026-01-02 10:00:00"),
            ],
            [
                "user_id" => $u2->id,
                "title" => "Other",
                "notes" => "Something",
                "duration_s" => 100,
                "distance_m" => 200,
                "activity_type" => "other",
                "created_at" => Carbon::parse("2026-01-02 10:00:00"),
                "updated_at" => Carbon::parse("2026-01-02 10:00:00"),
            ],
            [
                "user_id" => $u3->id,
                "title" => "Other",
                "notes" => null,
                "duration_s" => 10,
                "distance_m" => 300,
                "activity_type" => "other",
                "created_at" => Carbon::parse("2026-01-02 10:00:00"),
                "updated_at" => Carbon::parse("2026-01-02 10:00:00"),
            ],
        ]);

        $response = $this->getJson($this->leaderboardUrl("distance", [
            "from" => "2026-01-01",
            "to" => "2026-01-07",
            "limit" => 2,
        ]));

        $response->assertOk();
        $this->assertCount(2, $response->json("data"));
    }

    public function testLeaderboardIncludesToDateInclusive(): void
    {
        $u1 = User::factory()->create(["email" => "u1@gmail.com"]);

        Activity::query()->insert([
            [
                "user_id" => $u1->id,
                "title" => "Run",
                "notes" => null,
                "duration_s" => 15,
                "distance_m" => 111,
                "activity_type" => "run",
                "created_at" => Carbon::parse("2026-01-07 23:59:59"),
                "updated_at" => Carbon::parse("2026-01-07 23:59:59"),
            ],
        ]);

        $response = $this->getJson($this->leaderboardUrl("distance", [
            "from" => "2026-01-01",
            "to" => "2026-01-07",
        ]));

        $response->assertOk();

        $data = $response->json("data");
        $this->assertCount(1, $data);
        $this->assertSame(111, $data[0]["score"]);
        $this->assertSame($u1->id, $data[0]["user"]["id"]);
    }

    private function leaderboardUrl(string $type, array $query = []): string
    {
        $url = route("leaderboard.show", ["type" => $type]);

        if ($query === []) {
            return $url;
        }

        return $url . "?" . http_build_query($query);
    }
}
