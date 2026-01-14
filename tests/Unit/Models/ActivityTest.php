<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Strava\Models\Activity;
use Strava\Models\User;
use Tests\TestCase;

class ActivityTest extends TestCase
{
    use RefreshDatabase;

    public function testActivityBelongsToUser(): void
    {
        $user = User::factory()->create();

        $activity = Activity::query()->create([
            "user_id" => $user->id,
            "title" => "Walk",
            "notes" => "",
            "duration_s" => 1200,
            "distance_m" => 1500,
            "activity_type" => "walk",
        ]);

        $this->assertTrue($activity->user->is($user));
    }

    public function testPhotoAccessorReturnsApiUrl(): void
    {
        $user = User::factory()->create();

        $activity = Activity::query()->create([
            "user_id" => $user->id,
            "title" => "Run",
            "notes" => "",
            "duration_s" => 120,
            "distance_m" => 1500,
            "activity_type" => "run",
        ]);

        $this->assertSame(url("/api/activities/{$activity->id}/photo"), $activity->photo);
    }

    public function testDurationAndDistanceAreCastToInt(): void
    {
        $user = User::factory()->create();

        $activity = Activity::query()->create([
            "user_id" => $user->id,
            "title" => "Run",
            "notes" => "",
            "duration_s" => "120",
            "distance_m" => "1500",
            "activity_type" => "run",
        ]);

        $activity->refresh();

        $this->assertIsInt($activity->duration_s);
        $this->assertIsInt($activity->distance_m);
        $this->assertSame(120, $activity->duration_s);
        $this->assertSame(1500, $activity->distance_m);
    }
}
