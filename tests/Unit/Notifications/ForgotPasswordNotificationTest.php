<?php

declare(strict_types=1);

namespace Tests\Unit\Notifications;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Messages\MailMessage;
use stdClass;
use Strava\Models\User;
use Strava\Notifications\ForgotPasswordNotification;
use Tests\TestCase;

class ForgotPasswordNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function testNotificationIsSentViaMailChannel(): void
    {
        $notification = new ForgotPasswordNotification("123456");

        $channels = $notification->via(new stdClass());

        $this->assertSame(["mail"], $channels);
    }

    public function testToMailBuildsCorrectMailMessage(): void
    {
        $user = User::factory()->create([
            "email" => "user@gmail.com",
            "name" => "User",
        ]);

        $notification = new ForgotPasswordNotification("654321");

        $mail = $notification->toMail($user);

        $this->assertInstanceOf(MailMessage::class, $mail);

        $this->assertSame(
            "(MRRGroup) MiniStrava - Password Reset Code",
            $mail->subject,
        );

        $this->assertSame("emails.forgotPassword", $mail->view);

        $this->assertSame("654321", $mail->viewData["code"]);
        $this->assertSame($user, $mail->viewData["user"]);
    }

    public function testToArrayReturnsEmptyArray(): void
    {
        $notification = new ForgotPasswordNotification("000000");

        $this->assertSame([], $notification->toArray(new stdClass()));
    }
}
