<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Strava\Actions\Auth\ResetPasswordAction;
use Strava\Models\User;
use Tests\TestCase;

class ResetPasswordActionTest extends TestCase
{
    use RefreshDatabase;

    public function testResetsPasswordWithValidTokenAndUpdatesRememberToken(): void
    {
        $user = User::factory()->create([
            "email" => "user@gmail.com",
            "password" => Hash::make("old-password"),
        ]);

        $token = Password::broker()->createToken($user);

        $action = new ResetPasswordAction();

        $ok = $action->execute([
            "email" => "user@gmail.com",
            "token" => $token,
            "password" => "new-password-123",
            "password_confirmation" => "new-password-123",
        ]);

        $this->assertTrue($ok);

        $user->refresh();

        $this->assertTrue(Hash::check("new-password-123", $user->password));
        $this->assertFalse(Hash::check("old-password", $user->password));

        $this->assertNotEmpty($user->remember_token);
        $this->assertSame(60, strlen((string)$user->remember_token));
    }

    public function testReturnsFalseWhenTokenIsInvalidAndDoesNotChangePassword(): void
    {
        $user = User::factory()->create([
            "email" => "user@gmail.com",
            "password" => Hash::make("old-password"),
        ]);

        $action = new ResetPasswordAction();

        $ok = $action->execute([
            "email" => "user@gmail.com",
            "token" => "invalid-token",
            "password" => "new-password-123",
            "password_confirmation" => "new-password-123",
        ]);

        $this->assertFalse($ok);

        $user->refresh();

        $this->assertTrue(Hash::check("old-password", $user->password));
        $this->assertFalse(Hash::check("new-password-123", $user->password));
    }
}
