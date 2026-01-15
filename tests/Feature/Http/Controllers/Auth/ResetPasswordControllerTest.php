<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Strava\Models\User;
use Tests\TestCase;

class ResetPasswordControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testResetPasswordReturns200AndUpdatesPasswordWhenTokenIsValid(): void
    {
        $user = User::factory()->create([
            "email" => "user@gmail.com",
            "password" => Hash::make("oldpassword123"),
        ]);

        $token = Password::createToken($user);

        $response = $this->postJson(route("reset-password"), [
            "token" => $token,
            "email" => "user@gmail.com",
            "password" => "newpassword123",
            "password_confirmation" => "newpassword123",
        ]);

        $response->assertOk();
        $response->assertExactJson([]);

        $user->refresh();
        $this->assertTrue(Hash::check("newpassword123", $user->password));
    }

    public function testResetPasswordReturns400WhenTokenIsInvalid(): void
    {
        $user = User::factory()->create([
            "email" => "user@gmail.com",
            "password" => Hash::make("oldpassword123"),
        ]);

        $response = $this->postJson(route("reset-password"), [
            "token" => "invalid-token",
            "email" => "user@gmail.com",
            "password" => "newpassword123",
            "password_confirmation" => "newpassword123",
        ]);

        $response->assertBadRequest();
        $response->assertExactJson([]);

        $user->refresh();
        $this->assertTrue(Hash::check("oldpassword123", $user->password));
    }

    public function testResetPasswordRequiresFields(): void
    {
        $response = $this->postJson(route("reset-password"), []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(["token", "email", "password"]);
    }
}
