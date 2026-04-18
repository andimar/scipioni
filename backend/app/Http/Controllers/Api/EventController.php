<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Models\Event;
use Illuminate\Http\JsonResponse;

class EventController extends Controller
{
    public function index(): JsonResponse
    {
        $events = Event::query()
            ->with(['category', 'tags'])
            ->withCount([
                'bookings as confirmed_seats_count' => fn ($query) => $query
                    ->where('status', 'confirmed'),
            ])
            ->where('status', 'published')
            ->orderBy('starts_at')
            ->get();

        return response()->json([
            'data' => EventResource::collection($events),
        ]);
    }

    public function show(Event $event): JsonResponse
    {
        $event->load(['category', 'tags'])
            ->loadCount([
                'bookings as confirmed_seats_count' => fn ($query) => $query
                    ->where('status', 'confirmed'),
            ]);

        return response()->json([
            'data' => new EventResource($event),
        ]);
    }
}
