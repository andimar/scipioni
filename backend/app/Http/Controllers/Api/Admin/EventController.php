<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EventController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $events = Event::query()
            ->with('category')
            ->withCount('bookings')
            ->when(
                $request->filled('search'),
                fn ($query) => $query->where(function ($nestedQuery) use ($request): void {
                    $search = $request->string('search')->toString();

                    $nestedQuery
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('subtitle', 'like', "%{$search}%");
                })
            )
            ->when(
                $request->filled('status'),
                fn ($query) => $query->where('status', $request->string('status')->toString())
            )
            ->orderByDesc('starts_at')
            ->paginate($request->integer('per_page', 15))
            ->withQueryString();

        return response()->json([
            'data' => $events->through(fn (Event $event): array => [
                'id' => $event->id,
                'title' => $event->title,
                'slug' => $event->slug,
                'subtitle' => $event->subtitle,
                'cover_image_path' => $event->cover_image_path,
                'cover_image_url' => $this->resolveImageUrl($event->cover_image_path),
                'starts_at' => $event->starts_at,
                'ends_at' => $event->ends_at,
                'price' => $event->price,
                'status' => $event->status,
                'booking_status' => $event->booking_status,
                'bookings_count' => (int) $event->bookings_count,
                'category' => $event->category ? [
                    'id' => $event->category->id,
                    'name' => $event->category->name,
                    'slug' => $event->category->slug,
                ] : null,
            ])->items(),
            'meta' => [
                'current_page' => $events->currentPage(),
                'last_page' => $events->lastPage(),
                'per_page' => $events->perPage(),
                'total' => $events->total(),
                'from' => $events->firstItem(),
                'to' => $events->lastItem(),
            ],
        ]);
    }

    public function show(Event $event): JsonResponse
    {
        $event->load('category')
            ->loadCount('bookings');

        return response()->json([
            'data' => $this->transformEvent($event),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $event = new Event();
        $data = $this->validatedData($request, $event);

        $event->fill($data);
        $event->created_by_admin_id = $request->user('admin')->id;
        $event->updated_by_admin_id = $request->user('admin')->id;
        $event->save();
        $event->load('category')
            ->loadCount('bookings');

        return response()->json([
            'message' => 'Evento creato correttamente.',
            'data' => $this->transformEvent($event),
        ], 201);
    }

    public function update(Request $request, Event $event): JsonResponse
    {
        $data = $this->validatedData($request, $event);

        $event->fill($data);
        $event->updated_by_admin_id = $request->user('admin')->id;
        $event->save();
        $event->load('category')
            ->loadCount('bookings');

        return response()->json([
            'message' => 'Evento aggiornato correttamente.',
            'data' => $this->transformEvent($event),
        ]);
    }

    public function destroy(Event $event): JsonResponse
    {
        if ($event->bookings()->exists()) {
            throw ValidationException::withMessages([
                'event' => ['Non puoi eliminare un evento con prenotazioni associate. Archivialo invece di cancellarlo.'],
            ]);
        }

        $event->delete();

        return response()->json([
            'message' => 'Evento eliminato correttamente.',
        ]);
    }

    private function transformEvent(Event $event): array
    {
        return [
            'id' => $event->id,
            'title' => $event->title,
            'slug' => $event->slug,
            'subtitle' => $event->subtitle,
            'short_description' => $event->short_description,
            'full_description' => $event->full_description,
            'cover_image_path' => $event->cover_image_path,
            'cover_image_url' => $this->resolveImageUrl($event->cover_image_path),
            'starts_at' => $event->starts_at,
            'ends_at' => $event->ends_at,
            'venue_name' => $event->venue_name,
            'venue_address' => $event->venue_address,
            'capacity' => (int) $event->capacity,
            'price' => $event->price,
            'status' => $event->status,
            'booking_status' => $event->booking_status,
            'requires_approval' => (bool) $event->requires_approval,
            'is_featured' => (bool) $event->is_featured,
            'bookings_count' => (int) ($event->bookings_count ?? 0),
            'category_id' => $event->category_id,
            'category' => $event->category ? [
                'id' => $event->category->id,
                'name' => $event->category->name,
                'slug' => $event->category->slug,
            ] : null,
        ];
    }

    private function validatedData(Request $request, Event $event): array
    {
        $data = $request->validate([
            'category_id' => ['nullable', 'integer', 'exists:event_categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'short_description' => ['nullable', 'string'],
            'full_description' => ['required', 'string'],
            'cover_image_path' => ['nullable', 'string', 'max:2048'],
            'venue_name' => ['required', 'string', 'max:255'],
            'venue_address' => ['nullable', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'capacity' => ['required', 'integer', 'min:0'],
            'price' => ['required', 'numeric', 'min:0'],
            'booking_status' => ['required', 'in:open,closed,waitlist'],
            'status' => ['required', 'in:draft,published,archived'],
            'requires_approval' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
        ]);

        $title = trim((string) $request->input('title'));

        if ($title === '') {
            throw ValidationException::withMessages([
                'title' => ['Il titolo e\' obbligatorio.'],
            ]);
        }

        $data['slug'] = $this->uniqueSlug($title, $event);
        $data['requires_approval'] = $request->boolean('requires_approval');
        $data['is_featured'] = $request->boolean('is_featured');
        $data['starts_at'] = Carbon::parse($data['starts_at']);
        $data['ends_at'] = filled($data['ends_at'] ?? null) ? Carbon::parse($data['ends_at']) : null;
        $data['published_at'] = $data['status'] === 'published'
            ? ($event->published_at ?? now())
            : null;

        return $data;
    }

    private function resolveImageUrl(?string $path): ?string
    {
        if (! filled($path)) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        $normalized = Str::of($path)
            ->trim()
            ->trim('/')
            ->toString();

        return url($normalized);
    }

    private function uniqueSlug(string $title, Event $event): string
    {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug;
        $suffix = 2;

        while (Event::query()
            ->where('slug', $slug)
            ->when($event->exists, fn ($query) => $query->whereKeyNot($event->getKey()))
            ->exists()) {
            $slug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
