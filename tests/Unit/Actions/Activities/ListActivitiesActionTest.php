<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Activities;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Strava\Actions\Activities\ListActivitiesAction;
use Strava\Models\Activity;
use Strava\Models\User;
use Tests\TestCase;

class ListActivitiesActionTest extends TestCase
{
    use RefreshDatabase;

    public function testReturnsPaginatedLatestActivitiesForUser(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        Activity::query()->insert([
            [
                "user_id" => $user->id,
                "title" => "Old",
                "notes" => "",
                "duration_s" => 10,
                "distance_m" => 100,
                "activity_type" => "run",
                "created_at" => Carbon::parse("2026-01-01 10:00:00"),
                "updated_at" => Carbon::parse("2026-01-01 10:00:00"),
                "started_at" => now(),
            ],
            [
                "user_id" => $user->id,
                "title" => "New",
                "notes" => "",
                "duration_s" => 20,
                "distance_m" => 200,
                "activity_type" => "walk",
                "created_at" => Carbon::parse("2026-01-02 10:00:00"),
                "updated_at" => Carbon::parse("2026-01-02 10:00:00"),
                "started_at" => now(),
            ],
            [
                "user_id" => $other->id,
                "title" => "Other",
                "notes" => "",
                "duration_s" => 999,
                "distance_m" => 999,
                "activity_type" => "ride",
                "created_at" => Carbon::parse("2026-01-03 10:00:00"),
                "updated_at" => Carbon::parse("2026-01-03 10:00:00"),
                "started_at" => now(),
            ],
        ]);

        $action = new ListActivitiesAction();

        $result = $action->execute($user->id);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);

        $items = $result->items();

        $this->assertCount(2, $items);
        $this->assertSame("New", $items[0]->title);
        $this->assertSame("Old", $items[1]->title);
    }

    public function testPaginatesBy10Items(): void
    {
        $user = User::factory()->create();

        $rows = [];

        for ($i = 1; $i <= 15; $i++) {
            $rows[] = [
                "user_id" => $user->id,
                "title" => "A{$i}",
                "notes" => "",
                "duration_s" => 1,
                "distance_m" => 1,
                "activity_type" => "run",
                "created_at" => now()->addSeconds($i),
                "updated_at" => now()->addSeconds($i),
                "started_at" => now(),
            ];
        }

        Activity::query()->insert($rows);

        $action = new ListActivitiesAction();

        $page1 = $action->execute($user->id);

        $this->assertCount(10, $page1->items());
        $this->assertSame(15, $page1->total());
    }
}
