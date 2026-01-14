<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Strava\Models\User;
use Tests\TestCase;

class RegisterControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testRegisterCreatesUserAndReturns201(): void
    {
        $payload = [
            "name" => "Kacper",
            "email" => "user@gmail.com",
            "password" => "password123",
            "password_confirmation" => "password123",
        ];

        $response = $this->postJson(route("register"), $payload);

        $response->assertCreated();
        $response->assertExactJson([]);

        $this->assertDatabaseHas("users", [
            "name" => "Kacper",
            "email" => "user@gmail.com",
        ]);

        $user = User::query()->where("email", "user@gmail.com")->firstOrFail();

        $this->assertTrue(Hash::check("password123", $user->password));
        $this->assertNotSame("password123", $user->password);
    }

    public function testRegisterRequiresName(): void
    {
        $payload = [
            "email" => "user@gmail.com",
            "password" => "password123",
            "password_confirmation" => "password123",
        ];

        $response = $this->postJson(route("register"), $payload);

        $response->assertUnprocessable();

        $this->assertDatabaseMissing("users", [
            "email" => "user@gmail.com",
        ]);
    }

    public function testRegisterRequiresEmail(): void
    {
        $payload = [
            "name" => "Kacper",
            "password" => "password123",
            "password_confirmation" => "password123",
        ];

        $response = $this->postJson(route("register"), $payload);

        $response->assertUnprocessable();
    }

    public function testRegisterRequiresPasswordConfirmationToMatch(): void
    {
        $payload = [
            "name" => "Kacper",
            "email" => "user@gmail.com",
            "password" => "password123",
            "password_confirmation" => "different",
        ];

        $response = $this->postJson(route("register"), $payload);

        $response->assertUnprocessable();

        $this->assertDatabaseMissing("users", [
            "email" => "user@gmail.com",
        ]);
    }

    public function testRegisterRequiresUniqueEmail(): void
    {
        User::factory()->create([
            "email" => "dupe@gmail.com",
        ]);

        $payload = [
            "name" => "Kacper",
            "email" => "dupe@gmail.com",
            "password" => "password123",
            "password_confirmation" => "password123",
        ];

        $response = $this->postJson(route("register"), $payload);

        $response->assertUnprocessable();
    }
}
