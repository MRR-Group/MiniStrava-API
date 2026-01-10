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
            "activity_type" => ["required", new Enum(ActivityType::class)],
            "photo" => ["required", "image", "mimes:png", "max:4096"],
        ];
    }
}
