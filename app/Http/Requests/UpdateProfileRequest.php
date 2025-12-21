<?php

declare(strict_types=1);

namespace Strava\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Strava\Enums\Gender;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            "first_name" => ["nullable", "string", "max:255"],
            "last_name" => ["nullable", "string", "max:255"],
            "birth_date" => ["nullable", "date", "before:today"],
            "height" => ["nullable", "integer", "min:50", "max:300"],
            "weight" => ["nullable", "numeric", "min:10", "max:300"],
            "gender" => ["nullable", new Enum(Gender::class)],
        ];
    }
}
