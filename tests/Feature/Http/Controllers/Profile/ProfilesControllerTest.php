<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Profile;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Strava\Models\User;
use Tests\TestCase;

class ProfilesControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testIndexReturnsPaginatedUsers(): void
    {
        User::factory()->count(12)->create();

        $res = $this->getJson(route("profiles.index"));

        $res->assertOk();
        $res->assertJsonStructure([
            "data" => [
                ["id", "email", "name", "first_name", "last_name", "birth_date", "height", "weight", "avatar", "created_at"],
            ],
            "links",
            "meta",
        ]);

        $this->assertCount(10, $res->json("data"));
        $this->assertSame(12, $res->json("meta.total"));
    }

    public function testShowReturns404AndEmptyJsonWhenUserMissing(): void
    {
        $res = $this->getJson(route("profiles.show", ["id" => 9999]));

        $res->assertNotFound();
        $res->assertExactJson([]);
    }

    public function testShowReturnsUserResourceWhenUserExists(): void
    {
        $user = User::factory()->create([
            "email" => "user@gmail.com",
            "name" => "Kacper",
        ]);

        $res = $this->getJson(route("profiles.show", ["id" => $user->id]));

        $res->assertOk();
        $res->assertJsonStructure([
            "id",
            "email",
            "name",
            "first_name",
            "last_name",
            "birth_date",
            "height",
            "weight",
            "avatar",
            "created_at",
        ]);

        $this->assertSame($user->id, $res->json("id"));
        $this->assertSame("user@gmail.com", $res->json("email"));
        $this->assertSame("Kacper", $res->json("name"));
    }

    public function testStatisticsReturnsStatisticsResourceShape(): void
    {
        $user = User::factory()->create();

        $res = $this->getJson(route("profiles.statistics.show", ["id" => $user->id]));

        $res->assertOk();
        $res->assertJsonStructure([
            "data" => [
                "activitiesCount",
                "totalDistanceM",
                "totalDurationS",
                "avgDistanceM",
                "avgDurationS",
                "avgSpeedMps",
                "avgSpeedKph",
                "avgPaceSecPerKm",
                "longestDistanceM",
                "longestDurationS",
                "firstActivity",
                "lastActivity",
            ],
        ]);
    }
}
