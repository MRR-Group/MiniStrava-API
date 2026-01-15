<?php

declare(strict_types=1);

namespace Strava\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Strava\Enums\ActivityType;

class StoreActivityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "title" => ["required", "string", "max:255"],
            "notes" => ["nullable", "string", "max:2048"],
            "duration_s" => ["required", "integer", "min:1"],
            "distance_m" => ["required", "integer", "min:1"],
            'started_at' => ['required', 'date', 'before_or_equal:now'],
            "activity_type" => ["required", new Enum(ActivityType::class)],
            "photo" => ["image", "mimes:png", "max:4096"],

            "gps_points" => ["nullable", "array", "max:20000"],
            "gps_points.*.seq" => ["required", "integer", "min:1"],
            "gps_points.*.lat" => ["required", "numeric", "between:-90,90"],
            "gps_points.*.lng" => ["required", "numeric", "between:-180,180"],
            "gps_points.*.alt_m" => ["nullable", "numeric"],
            "gps_points.*.accuracy_m" => ["nullable", "numeric", "min:0"],
            "gps_points.*.timestamp" => ["required", "integer", "min:0"],
        ];
    }
}
