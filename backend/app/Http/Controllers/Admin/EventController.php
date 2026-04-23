<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(): View
    {
        $events = Event::query()
            ->with('category')
            ->withCount('bookings')
            ->orderByDesc('starts_at')
            ->paginate(15);

        return view('admin.events.index', [
            'events' => $events,
        ]);
    }

    public function create(): View
    {
        return view('admin.events.create', [
            'categories' => EventCategory::query()->where('is_active', true)->orderBy('name')->get(),
            'event' => new Event([
                'venue_name' => 'Magazzino Scipioni',
                'booking_status' => 'open',
                'status' => 'draft',
                'capacity' => 0,
                'price' => 0,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $event = new Event();
        $data = $this->validatedData($request, $event);

        $event->fill($data);
        $event->created_by_admin_id = auth('admin')->id();
        $event->updated_by_admin_id = auth('admin')->id();
        $event->save();

        return redirect()
            ->route('admin.events.edit', $event)
            ->with('status', 'Evento creato correttamente.');
    }

    public function edit(Event $event): View
    {
        return view('admin.events.edit', [
            'categories' => EventCategory::query()->where('is_active', true)->orderBy('name')->get(),
            'event' => $event,
        ]);
    }

    public function update(Request $request, Event $event): RedirectResponse
    {
        $data = $this->validatedData($request, $event);

        $event->fill($data);
        $event->updated_by_admin_id = auth('admin')->id();
        $event->save();

        return redirect()
            ->route('admin.events.edit', $event)
            ->with('status', 'Evento aggiornato correttamente.');
    }

    private function validatedData(Request $request, Event $event): array
    {
        $data = $request->validate([
            'category_id' => ['nullable', 'integer', 'exists:event_categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'short_description' => ['nullable', 'string'],
            'full_description' => ['required', 'string'],
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

        $data['slug'] = $this->uniqueSlug($request->string('title')->toString(), $event);
        $data['requires_approval'] = $request->boolean('requires_approval');
        $data['is_featured'] = $request->boolean('is_featured');
        $data['starts_at'] = Carbon::parse($data['starts_at']);
        $data['ends_at'] = filled($data['ends_at'] ?? null) ? Carbon::parse($data['ends_at']) : null;
        $data['published_at'] = $data['status'] === 'published'
            ? ($event->published_at ?? now())
            : null;

        return $data;
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
