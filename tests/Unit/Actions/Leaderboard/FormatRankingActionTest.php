<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Leaderboard;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Strava\Actions\Leaderboard\FormatRankingAction;
use Strava\Http\Resources\UserResource;
use Strava\Models\User;
use Tests\TestCase;

class FormatRankingActionTest extends TestCase
{
    use RefreshDatabase;

    public function testFormatsRowsWithPlacesScoresAndUserResource(): void
    {
        $u1 = User::factory()->create(["email" => "u1@gmail.com"]);
        $u2 = User::factory()->create(["email" => "u2@gmail.com"]);

        $rows = collect([
            (object)[
                "user_id" => $u2->id,
                "score" => "2000",
            ],
            (object)[
                "user_id" => $u1->id,
                "score" => 1400,
            ],
        ]);

        $action = new FormatRankingAction();

        $result = $action->execute($rows);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(2, $result);

        $first = $result[0];
        $this->assertSame(1, $first["place"]);
        $this->assertSame(2000, $first["score"]);
        $this->assertInstanceOf(UserResource::class, $first["user"]);
        $this->assertSame($u2->id, $first["user"]->resource->id);

        $second = $result[1];
        $this->assertSame(2, $second["place"]);
        $this->assertSame(1400, $second["score"]);
        $this->assertInstanceOf(UserResource::class, $second["user"]);
        $this->assertSame($u1->id, $second["user"]->resource->id);
    }
}
