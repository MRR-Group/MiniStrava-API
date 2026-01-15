<?php

declare(strict_types=1);

namespace Strava\Enums;

enum ActivityType: string
{
    case Run = "run";
    case Ride = "ride";
    case Walk = "walk";
    case Other = "other";

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn(self $case) => [$case->value => $case->label()])
            ->all();
    }

    public function label(): string
    {
        return match ($this) {
            self::Run => "Run",
            self::Ride => "Ride",
            self::Walk => "Walk",
            self::Other => "Other",
        };
    }
}
