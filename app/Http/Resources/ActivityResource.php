<?php

declare(strict_types=1);

namespace Strava\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            "title" => $this->title,
            "notes" => $this->notes,
            "activity_type" => $this->activity_Type,
            "duration_s" => $this->duration_s,
            "distance_m" => $this->distance_m,
            "photo" => $this->photo,
            "created_at" => $this->created_at,
        ];
    }
}
