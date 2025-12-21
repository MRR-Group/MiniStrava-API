<?php

declare(strict_types=1);

namespace Strava\Enums;

enum ActivityType: string
{
    case Run = "run";
    case Ride = "ride";
    case Walk = "walk";
    case Other = "other";
}
