<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Strava\Models\User;
use Strava\Notifications\ForgotPasswordNotification;
use Tests\TestCase;

class ForgotPasswordControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testForgotPasswordSendsNotificationAndSavesHashedCodeForExistingUser(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            "email" => "user@gmail.com",
        ]);

        $response = $this->postJson(route("forgot-password"), [
            "email" => "user@gmail.com",
        ]);

        $response->assertOk();
        $response->assertExactJson([]);

        $row = DB::table("password_reset_tokens")->where("email", "user@gmail.com")->first();
        $this->assertNotNull($row);

        Notification::assertSentTo(
            $user,
            ForgotPasswordNotification::class,
            function (ForgotPasswordNotification $notification) use ($row): bool {
                $this->assertMatchesRegularExpression('/^\d{6}$/', $notification->code);

                return Hash::check($notification->code, (string)$row->token);
            },
        );
    }

    public function testForgotPasswordCreatesTokenButDoesNotSendNotificationWhenUserDoesNotExist(): void
    {
        Notification::fake();

        $response = $this->postJson(route("forgot-password"), [
            "email" => "nouser@gmail.com",
        ]);

        $response->assertOk();
        $response->assertExactJson([]);

        $this->assertDatabaseHas("password_reset_tokens", [
            "email" => "nouser@gmail.com",
        ]);

        Notification::assertNothingSent();
    }

    public function testForgotPasswordRequiresEmail(): void
    {
        $response = $this->postJson(route("forgot-password"), []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(["email"]);
    }

    public function testForgotPasswordRejectsInvalidEmailFormat(): void
    {
        $response = $this->postJson(route("forgot-password"), [
            "email" => "not-an-email",
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(["email"]);
    }
}
