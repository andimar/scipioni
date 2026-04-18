<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventAudience extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'filters',
        'is_enabled',
    ];

    protected function casts(): array
    {
        return [
            'filters' => 'array',
            'is_enabled' => 'boolean',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
