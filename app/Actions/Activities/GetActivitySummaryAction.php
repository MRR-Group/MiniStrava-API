<?php

declare(strict_types=1);

namespace Strava\Actions\Activities;

use Gemini\Client;
use Strava\Models\Activity;
use Strava\Models\User;

class GetActivitySummaryAction
{
    /**
     * @throws \JsonException
     */
    public function execute(User $user, Activity $activity): string
    {
        if (!empty($activity->summary)) {
            return $activity->summary;
        }

        $user->loadCount([
            'activities as activities_last_7_days_count' => fn ($q) => $q->where('started_at', '>=', now()->subDays(7)),
            'activities as activities_last_30_days_count' => fn ($q) => $q->where('started_at', '>=', now()->subDays(30)),
        ]);

        $avgPaceSecPerKm = $activity->distance_m > 0
            ? (int) round($activity->duration_s / ($activity->distance_m / 1000))
            : null;

        $trainingData = [
            'user' => [
                'birth_date' => $user->birth_date ?? null,
                'height' => $user->height ?? null,
                'weight' => $user->weight ?? null,
                'created_at' => optional($user->created_at)?->toDateTimeString(),
                'activities_last_7_days_count' => $user->activities_last_7_days_count ?? null,
                'activities_last_30_days_count' => $user->activities_last_30_days_count ?? null,
            ],
            'activity' => [
                'title' => $activity->title,
                'notes' => $activity->notes,
                'type' => $activity->activity_type?->value ?? $activity->activity_type,
                'started_at' => optional($activity->started_at)?->toDateTimeString(),
                'duration_s' => $activity->duration_s,
                'distance_m' => $activity->distance_m,
                'avg_pace_sec_per_km' => $avgPaceSecPerKm,
            ],
        ];

        $prompt = <<<PROMPT
            Jesteś doświadczonym trenerem sportowym i analitykiem danych treningowych.

            Na podstawie poniższych danych użytkownika i jednej aktywności:
            - oceń jakość treningu,
            - wskaż mocne strony,
            - zauważ ewentualne problemy lub ryzyka (np. przeciążenie),
            - daj JEDNĄ konkretną, praktyczną radę na kolejny trening.

            Zasady odpowiedzi:
            - dokładnie 3 krótkie zdania podsumowania,
            - + 1 zdanie jako rada (oddzielone od podsumowania),
            - język prosty i motywujący,
            - bez emotek, bez markdown,
            - nie wspominaj o AI ani modelach,
            - nie powtarzaj liczb 1:1.

            DANE (JSON):
            {$this->toJsonForPrompt($trainingData)}
            PROMPT;

        $client = app(Client::class);
        $model = $client->generativeModel('models/gemini-2.5-flash');
        $result = $model->generateContent($prompt);

        $summary = trim((string) $result->text());

        $activity->summary = $summary;
        $activity->save();

        return $summary;
    }

    /**
     * @throws \JsonException
     */
    private function toJsonForPrompt(array $data): string
    {
        return (string) json_encode(
            $data,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
        );
    }
}
