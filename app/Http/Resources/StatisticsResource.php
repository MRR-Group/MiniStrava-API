<?php

declare(strict_types=1);

namespace Strava\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StatisticsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "activitiesCount" => $this["activitiesCount"],
            "totalDistanceM" => $this["totalDistanceM"],
            "totalDurationS" => $this["totalDurationS"],

            "avgDistance_M" => $this["avgDistance_M"],
            "avgDuration_S" => $this["avgDuration_S"],

            "avgSpeedMps" => $this["avgSpeedMps"],
            "avgSpeedKph" => $this["avgSpeedKph"],
            "avgPaceSecPerKm" => $this["avgPaceSecPerKm"],

            "longestDistanceM" => $this["longestDistanceM"],
            "longestDurationS" => $this["longestDurationS"],

            "firstActivity" => $this["firstActivity"],
            "lastActivity" => $this["lastActivity"],
        ];
    }
}
