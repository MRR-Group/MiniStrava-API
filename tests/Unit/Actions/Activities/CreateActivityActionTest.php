<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Activities;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Strava\Actions\Activities\CreateActivityAction;
use Strava\Models\Activity;
use Strava\Models\User;
use Tests\TestCase;

class CreateActivityActionTest extends TestCase
{
    use RefreshDatabase;

    public function testCreatesActivityForUserAndReturnsModel(): void
    {
        $user = User::factory()->create();

        $action = new CreateActivityAction();

        $data = [
            "title" => "Morning Run",
            "notes" => "Nice weather",
            "duration_s" => 600,
            "distance_m" => 2000,
            "activity_type" => "run",
            "started_at" => now(),
        ];

        $activity = $action->execute($user->id, $data);

        $this->assertInstanceOf(Activity::class, $activity);

        $this->assertDatabaseHas("activities", [
            "id" => $activity->id,
            "user_id" => $user->id,
            "title" => "Morning Run",
            "notes" => "Nice weather",
            "duration_s" => 600,
            "distance_m" => 2000,
            "activity_type" => "run",
            "started_at" => now(),
        ]);
    }

    public function testNotesDefaultsToEmptyStringWhenNotProvided(): void
    {
        $user = User::factory()->create();

        $action = new CreateActivityAction();

        $activity = $action->execute($user->id, [
            "title" => "Run",
            "duration_s" => 100,
            "distance_m" => 500,
            "activity_type" => "run",
            "started_at" => now(),
        ]);

        $activity->refresh();

        $this->assertSame("", $activity->notes);
    }
}
