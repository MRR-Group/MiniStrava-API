<?php

declare(strict_types=1);

namespace Strava\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "token" => ["required", "string"],
            "email" => ["required", "string", "email"],
            "password" => ["required", "string", "min:8", "max:255", "confirmed"],
        ];
    }
}
