<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Strava\Actions\Auth\ChangePasswordAction;
use Strava\Models\User;
use Tests\TestCase;

class ChangePasswordActionTest extends TestCase
{
    use RefreshDatabase;

    public function testReturnsFalseWhenCurrentPasswordIsInvalid(): void
    {
        $user = User::factory()->create([
            "password" => Hash::make("old-password"),
        ]);

        $action = new ChangePasswordAction();

        $result = $action->execute($user, "wrong-password", "new-password");

        $this->assertFalse($result);

        $user->refresh();
        $this->assertTrue(Hash::check("old-password", $user->password));
    }

    public function testChangesPasswordWhenCurrentPasswordIsValid(): void
    {
        $user = User::factory()->create([
            "password" => Hash::make("old-password"),
        ]);

        $action = new ChangePasswordAction();

        $result = $action->execute($user, "old-password", "new-password");

        $this->assertTrue($result);

        $user->refresh();

        $this->assertTrue(Hash::check("new-password", $user->password));
        $this->assertFalse(Hash::check("old-password", $user->password));
    }
}
