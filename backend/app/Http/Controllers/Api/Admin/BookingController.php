<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $bookings = Booking::query()
            ->with(['event.category', 'user.profile'])
            ->when(
                $request->filled('status'),
                fn ($query) => $query->where('status', $request->string('status')->toString())
            )
            ->when(
                $request->filled('event_id'),
                fn ($query) => $query->where('event_id', $request->integer('event_id'))
            )
            ->latest()
            ->paginate($request->integer('per_page', 15))
            ->withQueryString();

        return response()->json([
            'data' => $bookings->through(fn (Booking $booking): array => [
                'id' => $booking->id,
                'status' => $booking->status,
                'seats_reserved' => (int) $booking->seats_reserved,
                'customer_notes' => $booking->customer_notes,
                'internal_notes' => $booking->internal_notes,
                'confirmed_at' => $booking->confirmed_at,
                'cancelled_at' => $booking->cancelled_at,
                'created_at' => $booking->created_at,
                'event' => $booking->event ? [
                    'id' => $booking->event->id,
                    'title' => $booking->event->title,
                    'slug' => $booking->event->slug,
                    'cover_image_url' => $this->resolveImageUrl($booking->event->cover_image_path),
                    'starts_at' => $booking->event->starts_at,
                    'category' => $booking->event->category ? [
                        'id' => $booking->event->category->id,
                        'name' => $booking->event->category->name,
                    ] : null,
                ] : null,
                'user' => $booking->user ? [
                    'id' => $booking->user->id,
                    'first_name' => $booking->user->first_name,
                    'last_name' => $booking->user->last_name,
                    'email' => $booking->user->email,
                    'phone' => $booking->user->phone,
                ] : null,
            ])->items(),
            'meta' => [
                'current_page' => $bookings->currentPage(),
                'last_page' => $bookings->lastPage(),
                'per_page' => $bookings->perPage(),
                'total' => $bookings->total(),
                'from' => $bookings->firstItem(),
                'to' => $bookings->lastItem(),
            ],
        ]);
    }

    public function update(Request $request, Booking $booking): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:requested,confirmed,waitlist,cancelled'],
            'internal_notes' => ['nullable', 'string'],
        ]);

        $booking->status = $data['status'];
        $booking->internal_notes = $data['internal_notes'] ?? $booking->internal_notes;
        $booking->confirmed_at = $data['status'] === 'confirmed'
            ? ($booking->confirmed_at ?? now())
            : ($data['status'] === 'cancelled' ? null : $booking->confirmed_at);
        $booking->cancelled_at = $data['status'] === 'cancelled'
            ? now()
            : null;
        $booking->save();
        $booking->load(['event.category', 'user.profile']);

        return response()->json([
            'message' => 'Prenotazione aggiornata correttamente.',
            'data' => [
                'id' => $booking->id,
                'status' => $booking->status,
                'seats_reserved' => (int) $booking->seats_reserved,
                'customer_notes' => $booking->customer_notes,
                'internal_notes' => $booking->internal_notes,
                'confirmed_at' => $booking->confirmed_at,
                'cancelled_at' => $booking->cancelled_at,
                'created_at' => $booking->created_at,
                'event' => $booking->event ? [
                    'id' => $booking->event->id,
                    'title' => $booking->event->title,
                    'slug' => $booking->event->slug,
                    'cover_image_url' => $this->resolveImageUrl($booking->event->cover_image_path),
                    'starts_at' => $booking->event->starts_at,
                    'category' => $booking->event->category ? [
                        'id' => $booking->event->category->id,
                        'name' => $booking->event->category->name,
                    ] : null,
                ] : null,
                'user' => $booking->user ? [
                    'id' => $booking->user->id,
                    'first_name' => $booking->user->first_name,
                    'last_name' => $booking->user->last_name,
                    'email' => $booking->user->email,
                    'phone' => $booking->user->phone,
                ] : null,
            ],
        ]);
    }

    private function resolveImageUrl(?string $path): ?string
    {
        if (! filled($path)) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        return url(trim($path, '/'));
    }
}
