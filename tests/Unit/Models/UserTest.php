<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Strava\Helpers\IdenticonHelper;
use Strava\Models\Activity;
use Strava\Models\User;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function testUserHasManyActivities(): void
    {
        $user = User::factory()->create();

        $a1 = Activity::query()->create([
            "user_id" => $user->id,
            "title" => "Walk",
            "notes" => "",
            "duration_s" => 1,
            "distance_m" => 1,
            "activity_type" => "walk",
        ]);

        $a2 = Activity::query()->create([
            "user_id" => $user->id,
            "title" => "Other",
            "notes" => "",
            "duration_s" => 2,
            "distance_m" => 2,
            "activity_type" => "other",
        ]);

        $ids = $user->activities()->pluck("id")->all();

        $this->assertContains($a1->id, $ids);
        $this->assertContains($a2->id, $ids);
    }

    public function testAvatarAccessorReturnsIdenticonUrl(): void
    {
        $user = User::factory()->create();

        $this->assertSame(IdenticonHelper::url($user->id), $user->avatar);
    }

    public function testBirthDateIsCastToDate(): void
    {
        $user = User::factory()->create([
            "birth_date" => "2000-01-02",
        ]);

        $user->refresh();

        $this->assertNotNull($user->birth_date);
        $this->assertSame("2000-01-02", $user->birth_date->toDateString());
    }

    public function testPasswordIsHashedCast(): void
    {
        $user = User::factory()->create([
            "password" => "plainpassword123",
        ]);

        $user->refresh();

        $this->assertNotSame("plainpassword123", $user->password);
        $this->assertTrue(Hash::check("plainpassword123", $user->password));
    }
}
