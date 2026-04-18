<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone' => ['nullable', 'string', 'max:30'],
            'gender' => ['nullable', 'string', 'max:30'],
            'age_range' => ['nullable', 'string', 'max:20'],
            'origin_city' => ['nullable', 'string', 'max:255'],
            'rome_area' => ['nullable', 'string', 'max:255'],
            'food_preferences' => ['nullable', 'array'],
            'event_preferences' => ['nullable', 'array'],
            'marketing_consent' => ['nullable', 'boolean'],
        ];
    }
}
