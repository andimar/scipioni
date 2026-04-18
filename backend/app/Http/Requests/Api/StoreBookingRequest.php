<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'event_id' => ['required', 'integer', 'exists:events,id'],
            'seats_reserved' => ['nullable', 'integer', 'min:1', 'max:12'],
            'customer_notes' => ['nullable', 'string'],
        ];
    }
}
