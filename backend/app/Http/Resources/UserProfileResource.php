<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'gender' => $this->gender,
            'age_range' => $this->age_range,
            'origin_city' => $this->origin_city,
            'rome_area' => $this->rome_area,
            'food_preferences' => $this->food_preferences ?? [],
            'event_preferences' => $this->event_preferences ?? [],
            'source_channel' => $this->source_channel,
            'privacy_consent' => (bool) $this->privacy_consent,
            'privacy_consented_at' => $this->privacy_consented_at,
            'marketing_consent' => (bool) $this->marketing_consent,
            'marketing_consented_at' => $this->marketing_consented_at,
        ];
    }
}
