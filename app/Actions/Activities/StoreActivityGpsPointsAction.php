<?php

declare(strict_types=1);

namespace Strava\Actions\Activities;

use Carbon\Carbon;
use Strava\Models\GpsPoint;

class StoreActivityGpsPointsAction
{
    public function execute(int $activityId, array $points): void
    {
        if ($points === []) {
            return;
        }

        $now = Carbon::now();

        $rows = [];

        foreach ($points as $i => $p) {
            $rows[] = [
                "activity_id" => $activityId,
                "lat" => (float)$p["lat"],
                "lng" => (float)$p["lng"],
                "alt_m" => array_key_exists("alt_m", $p) ? ($p["alt_m"] !== null ? (float)$p["alt_m"] : null) : null,
                "accuracy_m" => array_key_exists("accuracy_m", $p) ? ($p["accuracy_m"] !== null ? (float)$p["accuracy_m"] : null) : null,
                "timestamp" => (int)$p["timestamp"],
                "created_at" => $now,
                "updated_at" => $now,
            ];
        }

        foreach (array_chunk($rows, 2000) as $chunk) {
            GpsPoint::query()->insert($chunk);
        }
    }
}
