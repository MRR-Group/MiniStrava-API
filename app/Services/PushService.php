<?php

declare(strict_types=1);

namespace Strava\Services;

use Illuminate\Support\Collection;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\RegistrationTokens;
use RuntimeException;
use Strava\Models\PushToken;

class PushService
{
    private Messaging $messaging;

    public function __construct()
    {
        $path = config("firebase.credentials");

        if (!$path) {
            throw new RuntimeException("firebase.credentials is not set (FIREBASE_CREDENTIALS)");
        }

        $fullPath = $this->resolveCredentialsPath($path);

        if (!is_file($fullPath)) {
            throw new RuntimeException("Firebase credentials file not found: {$fullPath}");
        }

        $factory = new Factory()->withServiceAccount($fullPath);
        $this->messaging = $factory->createMessaging();
    }

    public function sendToUser(
        int $userId,
        string $title,
        string $body,
        array $data = [],
    ): void {
        $tokens = PushToken::query()
            ->where("user_id", $userId)
            ->pluck("token");

        if ($tokens->isEmpty()) {
            return;
        }

        $this->sendToTokens($tokens, $title, $body, $data);
    }

    public function sendToTokens(
        Collection $tokens,
        string $title,
        string $body,
        array $data = [],
    ): void {
        $message = CloudMessage::new()
            ->withNotification(Notification::create($title, $body))
            ->withData($this->stringifyData($data));

        $report = $this->messaging->sendMulticast($message, RegistrationTokens::fromValue($tokens->all()));

        $invalid = [];

        foreach ($report->failures()->getItems() as $failure) {
            $token = $failure->target()->value();

            $invalid[] = $token;
        }

        if ($invalid !== []) {
            PushToken::query()->whereIn("token", $invalid)->delete();
        }
    }

    private function resolveCredentialsPath(string $path): string
    {
        $fullPath = str_starts_with($path, "/")
            ? $path
            : base_path($path);

        if (
            !is_file($fullPath)
            && config("firebase.storage_prefix_fallback", true)
            && str_starts_with($path, "/storage/")
        ) {
            $fullPath = base_path(ltrim($path, "/"));
        }

        return $fullPath;
    }

    private function stringifyData(array $data): array
    {
        $out = [];

        foreach ($data as $k => $v) {
            if ($v === null) {
                continue;
            }
            $out[(string)$k] = is_string($v) ? $v : json_encode($v, JSON_UNESCAPED_UNICODE);
        }

        return $out;
    }
}
