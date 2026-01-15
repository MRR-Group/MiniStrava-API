<?php

declare(strict_types=1);

namespace Strava\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PushTokenStoreRequest extends FormRequest
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
            "token" => ["required", "string", "min:20", "max:4096"],
            "platform" => ["required", "string", "in:android,ios,web"],
            "device_id" => ["nullable", "string", "max:255"],
            "device_name" => ["nullable", "string", "max:255"],
        ];
    }
}
