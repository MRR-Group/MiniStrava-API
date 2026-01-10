<?php

declare(strict_types=1);

namespace Strava\Enums;

enum RankingTypes: string
{
    case Distance = "distance_m";
    case Duration = "duration_s";
}
