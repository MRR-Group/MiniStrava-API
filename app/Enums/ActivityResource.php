<?php

declare(strict_types=1);

namespace Strava\Enums;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Strava\Http\Resources\GpsPointResource;

class ActivityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "title" => $this->title,
            "notes" => $this->notes,
            "activity_type" => $this->activity_type,
            "duration_s" => $this->duration_s,
            "distance_m" => $this->distance_m,
            "photo" => $this->photo,
            "started_at" => $this->started_at,
            "gps_points" => GpsPointResource::collection(
                $this->whenLoaded("gpsPoints"),
            ),
        ];
    }
}
