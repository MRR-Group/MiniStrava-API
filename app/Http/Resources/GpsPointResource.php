<?php

declare(strict_types=1);

namespace Strava\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GpsPointResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "lat" => $this->lat,
            "lng" => $this->lng,
            "alt_m" => $this->alt_m,
            "accuracy_m" => $this->accuracy_m,
            "timestamp" => $this->timestamp,
        ];
    }
}
