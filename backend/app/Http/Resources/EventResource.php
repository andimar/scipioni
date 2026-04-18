<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $confirmedSeats = (int) ($this->confirmed_seats_count ?? 0);
        $availableSeats = max(0, (int) $this->capacity - $confirmedSeats);

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'subtitle' => $this->subtitle,
            'short_description' => $this->short_description,
            'full_description' => $this->full_description,
            'cover_image_path' => $this->cover_image_path,
            'gallery_images' => $this->gallery_images ?? [],
            'venue_name' => $this->venue_name,
            'venue_address' => $this->venue_address,
            'starts_at' => $this->starts_at,
            'ends_at' => $this->ends_at,
            'capacity' => (int) $this->capacity,
            'price' => $this->price,
            'booking_status' => $this->booking_status,
            'status' => $this->status,
            'requires_approval' => (bool) $this->requires_approval,
            'is_featured' => (bool) $this->is_featured,
            'published_at' => $this->published_at,
            'confirmed_seats' => $confirmedSeats,
            'available_seats' => $availableSeats,
            'category' => new EventCategoryResource($this->whenLoaded('category')),
            'tags' => EventTagResource::collection($this->whenLoaded('tags')),
        ];
    }
}
