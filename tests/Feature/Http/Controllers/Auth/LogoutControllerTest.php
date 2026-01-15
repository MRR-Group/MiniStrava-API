<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Strava\Models\User;
use Tests\TestCase;

class LogoutControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testLogoutDeletesCurrentAccessToken(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken("api-token")->plainTextToken;

        $this->assertDatabaseCount("personal_access_tokens", 1);

        $response = $this
            ->withHeader("Authorization", "Bearer {$token}")
            ->postJson(route("logout"));

        $response->assertOk();
        $response->assertExactJson([]);

        $this->assertDatabaseCount("personal_access_tokens", 0);
    }

    public function testLogoutReturns200EvenWhenUserHasNoToken(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->postJson(route("logout"));

        $response->assertOk();
        $response->assertExactJson([]);
    }

    public function testLogoutRequiresAuthentication(): void
    {
        $response = $this->postJson(route("logout"));

        $response->assertUnauthorized();
    }
}
