<?php

declare(strict_types=1);

namespace Strava\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "current_password" => ["required", "string"],
            "password" => ["required", "string", "min:8", "confirmed"],
        ];
    }
}
