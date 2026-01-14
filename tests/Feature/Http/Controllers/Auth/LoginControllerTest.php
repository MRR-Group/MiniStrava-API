<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Strava\Models\User;
use Tests\TestCase;

class LoginControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testLoginReturnsTokenAndUserIdWhenCredentialsAreValid(): void
    {
        $user = User::factory()->create([
            "email" => "user@gmail.com",
            "password" => Hash::make("password123"),
        ]);

        $payload = [
            "email" => "user@gmail.com",
            "password" => "password123",
        ];

        $response = $this->postJson(route("login"), $payload);

        $response->assertOk();
        $response->assertJsonStructure([
            "token",
            "user_id",
        ]);

        $response->assertJson([
            "user_id" => $user->id,
        ]);

        $token = $response->json("token");
        $this->assertIsString($token);
        $this->assertNotSame("", $token);

        $this->assertDatabaseHas("personal_access_tokens", [
            "tokenable_type" => User::class,
            "tokenable_id" => $user->id,
            "name" => "api-token",
        ]);
    }

    public function testLoginReturns403WhenCredentialsAreInvalid(): void
    {
        User::factory()->create([
            "email" => "user@gmail.com",
            "password" => Hash::make("password123"),
        ]);

        $payload = [
            "email" => "user@gmail.com",
            "password" => "wrong",
        ];

        $response = $this->postJson(route("login"), $payload);

        $response->assertForbidden();
        $response->assertExactJson([]);

        $this->assertDatabaseCount("personal_access_tokens", 0);
    }

    public function testLoginRequiresEmail(): void
    {
        $payload = [
            "password" => "password123",
        ];

        $response = $this->postJson(route("login"), $payload);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(["email"]);
    }

    public function testLoginRequiresPassword(): void
    {
        $payload = [
            "email" => "user@gmail.com",
        ];

        $response = $this->postJson(route("login"), $payload);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(["password"]);
    }

    public function testLoginRejectsInvalidEmailFormat(): void
    {
        $payload = [
            "email" => "not-an-email",
            "password" => "password123",
        ];

        $response = $this->postJson(route("login"), $payload);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(["email"]);
    }
}
