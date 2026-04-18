<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'gender',
        'age_range',
        'origin_city',
        'rome_area',
        'food_preferences',
        'event_preferences',
        'source_channel',
        'privacy_consent',
        'privacy_consented_at',
        'marketing_consent',
        'marketing_consented_at',
    ];

    protected function casts(): array
    {
        return [
            'food_preferences' => 'array',
            'event_preferences' => 'array',
            'privacy_consent' => 'boolean',
            'privacy_consented_at' => 'datetime',
            'marketing_consent' => 'boolean',
            'marketing_consented_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
