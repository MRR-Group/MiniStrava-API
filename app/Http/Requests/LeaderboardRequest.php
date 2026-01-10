<?php

declare(strict_types=1);

namespace Strava\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class LeaderboardRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "from" => ["nullable", "date", "before_or_equal:today"],
            "to" => ["nullable", "date", "after_or_equal:from", "before_or_equal:today"],
            "limit" => ["nullable", "integer", "min:1", "max:200"],
        ];
    }
}
