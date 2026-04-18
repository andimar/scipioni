<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreBookingRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Booking::query()
            ->with(['event.category', 'event.tags'])
            ->where('user_id', $request->user()->id);

        return response()->json([
            'data' => BookingResource::collection($query->latest()->get()),
        ]);
    }

    public function store(StoreBookingRequest $request): JsonResponse
    {
        $payload = $request->validated();
        $user = $request->user();
        $event = Event::findOrFail($payload['event_id']);
        $requestedSeats = $payload['seats_reserved'] ?? 1;

        if ($event->status !== 'published') {
            return response()->json([
                'message' => 'Event is not available for booking.',
            ], 422);
        }

        if ($event->booking_status === 'closed') {
            return response()->json([
                'message' => 'Bookings are closed for this event.',
            ], 422);
        }

        $confirmedSeats = (int) Booking::query()
            ->where('event_id', $event->id)
            ->where('status', 'confirmed')
            ->sum('seats_reserved');

        $remainingSeats = max(0, $event->capacity - $confirmedSeats);
        $status = $event->requires_approval ? 'requested' : 'confirmed';
        $confirmedAt = $event->requires_approval ? null : now();

        if ($event->booking_status === 'waitlist' || $requestedSeats > $remainingSeats) {
            $status = 'waitlist';
            $confirmedAt = null;
        }

        $booking = Booking::updateOrCreate(
            [
                'user_id' => $user->id,
                'event_id' => $event->id,
            ],
            [
                'status' => $status,
                'seats_reserved' => $requestedSeats,
                'customer_notes' => $payload['customer_notes'] ?? null,
                'confirmed_at' => $confirmedAt,
                'cancelled_at' => null,
            ]
        );

        return response()->json([
            'message' => 'Booking stored.',
            'data' => new BookingResource($booking->load(['event.category', 'event.tags'])),
        ], 201);
    }
}
