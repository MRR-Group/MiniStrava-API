<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Profile;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Strava\Actions\Profile\UpdateProfileAction;
use Strava\Models\User;
use Tests\TestCase;

class UpdateProfileActionTest extends TestCase
{
    use RefreshDatabase;

    public function testUpdatesUserProfileAndReturnsFreshModel(): void
    {
        $user = User::factory()->create([
            "first_name" => "Old",
            "last_name" => "Name",
            "height" => 170,
            "weight" => "70.00",
        ]);

        $data = [
            "first_name" => "New",
            "last_name" => "Surname",
            "height" => 185,
            "weight" => "82.50",
        ];

        $action = new UpdateProfileAction();

        $updated = $action->execute($user, $data);

        $this->assertSame($user->id, $updated->id);
        $this->assertSame("New", $updated->first_name);
        $this->assertSame("Surname", $updated->last_name);
        $this->assertSame(185, $updated->height);
        $this->assertSame("82.50", $updated->weight);

        $this->assertDatabaseHas("users", [
            "id" => $user->id,
            "first_name" => "New",
            "last_name" => "Surname",
            "height" => 185,
            "weight" => "82.50",
        ]);
    }

    public function testDoesNotChangeFieldsNotPresentInPayload(): void
    {
        $user = User::factory()->create([
            "first_name" => "Kacper",
            "last_name" => "Test",
            "height" => 180,
        ]);

        $action = new UpdateProfileAction();

        $updated = $action->execute($user, [
            "height" => 190,
        ]);

        $this->assertSame("Kacper", $updated->first_name);
        $this->assertSame("Test", $updated->last_name);
        $this->assertSame(190, $updated->height);
    }
}
