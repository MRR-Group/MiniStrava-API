<?php

declare(strict_types=1);

namespace Strava\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            "email" => $this->email,
            "name" => $this->name,
            "first_name" => $this->first_name,
            "last_name" => $this->last_name,
            "birth_date" => $this->birth_date?->format("Y-m-d"),
            "height" => $this->height,
            "weight" => $this->weight,
            "avatar" => $this->avatar,
            "created_at" => $this->created_at,
        ];
    }
}
