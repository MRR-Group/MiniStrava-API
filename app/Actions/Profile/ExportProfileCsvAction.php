<?php

declare(strict_types=1);

namespace Strava\Actions\Profile;

use Strava\Models\User;

class ExportProfileCsvAction
{
    public function execute(User $user): string
    {
        $out = fopen("php://temp", "r+");

        fwrite($out, "\xEF\xBB\xBF");

        fputcsv($out, [
            "SECTION",
            "id",
            "first_name",
            "last_name",
            "email",
            "birth_date",
            "height",
            "weight",
            "created_at",
        ]);
        fputcsv($out, [
            "USER",
            $user->id,
            $user->first_name,
            $user->last_name,
            $user->email,
            $user->birth_date,
            $user->height,
            $user->weight,
            optional($user->created_at)?->toDateTimeString(),
        ]);
        fputcsv($out, []);

        fputcsv($out, [
            "SECTION",
            "activity_id",
            "title",
            "activity_type",
            "distance_m",
            "duration_s",
            "started_at",
            "created_at",
        ]);

        $user->activities()
            ->orderBy("id")
            ->chunk(500, function ($activities) use ($out): void {
                foreach ($activities as $a) {
                    fputcsv($out, [
                        "ACTIVITY",
                        $a->id,
                        $a->title,
                        (string)$a->activity_type->value,
                        $a->distance_m,
                        $a->duration_s,
                        optional($a->started_at)?->toDateTimeString(),
                        optional($a->created_at)?->toDateTimeString(),
                    ]);
                }
            });

        rewind($out);
        $csv = stream_get_contents($out);
        fclose($out);

        return $csv;
    }
}
