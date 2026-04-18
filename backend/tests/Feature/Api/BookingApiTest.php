<?php

namespace Tests\Feature\Api;

use App\Models\AdminUser;
use App\Models\Booking;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BookingApiTest extends TestCase
{
    use RefreshDatabase;

    protected function createUser(string $email = 'mario@example.com'): User
    {
        $user = User::create([
            'first_name' => 'Mario',
            'last_name' => 'Rossi',
            'email' => $email,
            'phone' => '+39 333 0000000',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        UserProfile::create([
            'user_id' => $user->id,
            'privacy_consent' => true,
            'privacy_consented_at' => now(),
            'marketing_consent' => true,
            'marketing_consented_at' => now(),
        ]);

        return $user;
    }

    protected function createEvent(): Event
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

        return Event::create([
            'category_id' => $category->id,
            'created_by_admin_id' => $admin->id,
            'updated_by_admin_id' => $admin->id,
            'title' => 'Serata Rossi',
            'slug' => 'serata-rossi',
            'full_description' => 'Descrizione completa',
            'venue_name' => 'Magazzino Scipioni',
            'starts_at' => now()->addDay(),
            'capacity' => 10,
            'price' => 35,
            'booking_status' => 'open',
            'status' => 'published',
            'requires_approval' => false,
        ]);
    }

    public function test_authenticated_user_can_create_booking(): void
    {
        $user = $this->createUser();
        $event = $this->createEvent();
        $token = $user->createToken('phpunit')->plainTextToken;
        $headers = ['Authorization' => 'Bearer '.$token];

        $response = $this->postJson('/api/v1/bookings', [
            'event_id' => $event->id,
            'seats_reserved' => 2,
            'customer_notes' => 'Tavolo vicino alla vetrina',
        ], $headers);

        $response
            ->assertCreated()
            ->assertJsonPath('data.status', 'confirmed')
            ->assertJsonPath('data.seats_reserved', 2);

        $this->assertDatabaseHas('bookings', [
            'user_id' => $user->id,
            'event_id' => $event->id,
            'status' => 'confirmed',
        ]);
    }

    public function test_booking_goes_to_waitlist_when_capacity_is_exceeded(): void
    {
        $user = $this->createUser();
        $event = $this->createEvent();

        Booking::create([
            'user_id' => $user->id,
            'event_id' => $event->id,
            'status' => 'confirmed',
            'seats_reserved' => 10,
            'confirmed_at' => now(),
        ]);

        $otherUser = $this->createUser('giulia@example.com');
        $token = $otherUser->createToken('phpunit')->plainTextToken;
        $headers = ['Authorization' => 'Bearer '.$token];

        $this->postJson('/api/v1/bookings', [
            'event_id' => $event->id,
            'seats_reserved' => 1,
        ], $headers)
            ->assertCreated()
            ->assertJsonPath('data.status', 'waitlist');
    }
}
