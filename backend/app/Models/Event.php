<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'created_by_admin_id',
        'updated_by_admin_id',
        'title',
        'slug',
        'subtitle',
        'short_description',
        'full_description',
        'cover_image_path',
        'gallery_images',
        'venue_name',
        'venue_address',
        'starts_at',
        'ends_at',
        'capacity',
        'price',
        'booking_status',
        'status',
        'requires_approval',
        'is_featured',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'gallery_images' => 'array',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'price' => 'decimal:2',
            'requires_approval' => 'boolean',
            'is_featured' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(EventCategory::class, 'category_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(EventTag::class, 'event_tag_event', 'event_id', 'event_tag_id');
    }

    public function audiences(): HasMany
    {
        return $this->hasMany(EventAudience::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
