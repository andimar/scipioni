<?php

namespace Tests\Feature\Api;

use App\Models\AdminUser;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\EventTag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class EventApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_events_endpoint_returns_only_published_events(): void
    {
        $admin = AdminUser::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $category = EventCategory::create([
            'name' => 'Degustazioni',
            'slug' => 'degustazioni',
            'is_active' => true,
        ]);

        $tag = EventTag::create([
            'name' => 'Bollicine',
            'slug' => 'bollicine',
        ]);

        $published = Event::create([
            'category_id' => $category->id,
            'created_by_admin_id' => $admin->id,
            'updated_by_admin_id' => $admin->id,
            'title' => 'Evento Pubblicato',
            'slug' => 'evento-pubblicato',
            'full_description' => 'Descrizione completa',
            'venue_name' => 'Magazzino Scipioni',
            'starts_at' => now()->addDay(),
            'capacity' => 20,
            'price' => 30,
            'booking_status' => 'open',
            'status' => 'published',
        ]);

        $published->tags()->attach($tag->id);

        Event::create([
            'category_id' => $category->id,
            'created_by_admin_id' => $admin->id,
            'updated_by_admin_id' => $admin->id,
            'title' => 'Evento Bozza',
            'slug' => 'evento-bozza',
            'full_description' => 'Descrizione bozza',
            'venue_name' => 'Magazzino Scipioni',
            'starts_at' => now()->addDays(2),
            'capacity' => 20,
            'price' => 30,
            'booking_status' => 'open',
            'status' => 'draft',
        ]);

        $response = $this->getJson('/api/v1/events');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', 'evento-pubblicato')
            ->assertJsonPath('data.0.tags.0.slug', 'bollicine');
    }
}
