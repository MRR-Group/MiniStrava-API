<?php

declare(strict_types=1);

namespace Strava\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Random\RandomException;

class ForgotPasswordNotification extends Notification
{
    use Queueable;

    protected string $code = "";

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public string $email,
    ) {
        try {
            $this->code = (string)random_int(100000, 999999);
        } catch (RandomException $e) {
            report($e);

            abort(500, "Unable to generate secure reset code.");
        }

        DB::table("password_reset_tokens")->updateOrInsert(
            ["email" => $this->email],
            [
                "email" => $this->email,
                "token" => Hash::make($this->code),
                "created_at" => now(),
            ],
        );
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ["mail"];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return new MailMessage()
            ->subject("(MRRGroup) MiniStrava - Password Reset Code")
            ->view("emails.forgotPassword", [
                "code" => $this->code,
                "user" => $notifiable,
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [];
    }
}
