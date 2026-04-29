<?php

namespace Tests\Feature\Api;

use App\Models\AdminUser;
use App\Models\Booking;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminBackofficeApiTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(string $role = 'admin', string $email = 'admin@example.com'): AdminUser
    {
        $email = str_replace('@', '+'.uniqid('', true).'@', $email);

        return AdminUser::create([
            'name' => $role === 'admin' ? 'Admin' : 'Gestore',
            'email' => $email,
            'password' => Hash::make('password'),
            'role' => $role,
            'is_active' => true,
        ]);
    }

    private function customer(string $email = 'cliente@example.com'): User
    {
        $email = str_replace('@', '+'.uniqid('', true).'@', $email);

        return User::create([
            'first_name' => 'Mario',
            'last_name' => 'Rossi',
            'email' => $email,
            'phone' => '+39 333 0000000',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
    }

    private function category(): EventCategory
    {
        $slug = 'degustazioni-'.uniqid();

        return EventCategory::create([
            'name' => 'Degustazioni',
            'slug' => $slug,
            'is_active' => true,
        ]);
    }

    private function eventPayload(array $overrides = []): array
    {
        $categoryId = $overrides['category_id'] ?? $this->category()->id;

        return array_merge([
            'category_id' => $categoryId,
            'title' => 'Serata Test',
            'subtitle' => 'Sottotitolo',
            'short_description' => 'Descrizione breve',
            'full_description' => 'Descrizione completa evento',
            'cover_image_path' => 'https://example.com/cover.jpg',
            'venue_name' => 'Magazzino Scipioni',
            'venue_address' => 'Via test 1',
            'starts_at' => now()->addWeek()->format('Y-m-d\TH:i'),
            'ends_at' => now()->addWeek()->addHours(2)->format('Y-m-d\TH:i'),
            'capacity' => 20,
            'price' => 30,
            'booking_status' => 'open',
            'status' => 'draft',
            'requires_approval' => false,
            'is_featured' => false,
        ], $overrides);
    }

    private function event(AdminUser $admin): Event
    {
        $payload = $this->eventPayload([
            'category_id' => $this->category()->id,
            'title' => 'Evento Esistente',
            'slug' => 'evento-esistente',
        ]);

        unset($payload['ends_at']);

        return Event::create(array_merge($payload, [
            'created_by_admin_id' => $admin->id,
            'updated_by_admin_id' => $admin->id,
        ]));
    }

    public function test_normal_user_cannot_login_to_admin_backoffice(): void
    {
        $this->customer('cliente@example.com');

        $this->postJson('/api/admin/auth/login', [
            'email' => 'cliente@example.com',
            'password' => 'password',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_admin_can_create_show_update_and_delete_event_without_bookings(): void
    {
        $admin = $this->admin();

        $createResponse = $this
            ->actingAs($admin, 'admin')
            ->postJson('/api/admin/events', $this->eventPayload([
                'title' => 'Evento CRUD',
            ]))
            ->assertCreated()
            ->assertJsonPath('data.title', 'Evento CRUD')
            ->assertJsonPath('data.cover_image_url', 'https://example.com/cover.jpg');

        $eventId = $createResponse->json('data.id');

        $this
            ->actingAs($admin, 'admin')
            ->getJson("/api/admin/events/{$eventId}")
            ->assertOk()
            ->assertJsonPath('data.id', $eventId);

        $this
            ->actingAs($admin, 'admin')
            ->putJson("/api/admin/events/{$eventId}", $this->eventPayload([
                'category_id' => $createResponse->json('data.category_id'),
                'title' => 'Evento CRUD Aggiornato',
                'status' => 'published',
                'booking_status' => 'closed',
            ]))
            ->assertOk()
            ->assertJsonPath('data.title', 'Evento CRUD Aggiornato')
            ->assertJsonPath('data.status', 'published');

        $this
            ->actingAs($admin, 'admin')
            ->deleteJson("/api/admin/events/{$eventId}")
            ->assertOk()
            ->assertJsonPath('message', 'Evento eliminato correttamente.');

        $this->assertDatabaseMissing('events', ['id' => $eventId]);
    }

    public function test_event_with_bookings_cannot_be_deleted(): void
    {
        $admin = $this->admin();
        $event = $this->event($admin);
        $customer = $this->customer();

        Booking::create([
            'event_id' => $event->id,
            'user_id' => $customer->id,
            'status' => 'confirmed',
            'seats_reserved' => 1,
        ]);

        $this
            ->actingAs($admin, 'admin')
            ->deleteJson("/api/admin/events/{$event->id}")
            ->assertUnprocessable()
            ->assertJsonValidationErrors('event');
    }

    public function test_admin_can_update_booking_status(): void
    {
        $admin = $this->admin();
        $event = $this->event($admin);
        $customer = $this->customer();
        $booking = Booking::create([
            'event_id' => $event->id,
            'user_id' => $customer->id,
            'status' => 'requested',
            'seats_reserved' => 2,
        ]);

        $this
            ->actingAs($admin, 'admin')
            ->patchJson("/api/admin/bookings/{$booking->id}", [
                'status' => 'confirmed',
                'internal_notes' => 'Confermato da test',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'confirmed')
            ->assertJsonPath('data.internal_notes', 'Confermato da test');
    }

    public function test_admin_can_manage_customer_and_staff_users(): void
    {
        $admin = $this->admin();
        $customer = $this->customer();
        $staff = $this->admin('staff', 'staff@example.com');

        $this
            ->actingAs($admin, 'admin')
            ->putJson("/api/admin/users/{$customer->id}", [
                'first_name' => 'Giulia',
                'last_name' => 'Bianchi',
                'email' => 'giulia@example.com',
                'phone' => '+39 333 1111111',
                'is_active' => false,
            ])
            ->assertOk()
            ->assertJsonPath('data.email', 'giulia@example.com')
            ->assertJsonPath('data.is_active', false);

        $this
            ->actingAs($admin, 'admin')
            ->postJson('/api/admin/admin-users', [
                'name' => 'Nuovo Gestore',
                'email' => 'gestore@example.com',
                'password' => 'password123',
                'role' => 'staff',
                'is_active' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('data.role', 'staff');

        $this
            ->actingAs($admin, 'admin')
            ->putJson("/api/admin/admin-users/{$staff->id}", [
                'name' => 'Gestore Aggiornato',
                'email' => 'staff-updated@example.com',
                'password' => '',
                'role' => 'staff',
                'is_active' => true,
            ])
            ->assertOk()
            ->assertJsonPath('data.email', 'staff-updated@example.com');
    }

    public function test_staff_can_read_but_cannot_modify_users_or_admin_users(): void
    {
        $staff = $this->admin('staff', 'staff@example.com');
        $admin = $this->admin('admin', 'admin2@example.com');
        $customer = $this->customer();

        $this
            ->actingAs($staff, 'admin')
            ->getJson('/api/admin/users')
            ->assertOk();

        $this
            ->actingAs($staff, 'admin')
            ->getJson('/api/admin/admin-users')
            ->assertOk()
            ->assertJsonFragment(['email' => $admin->email]);

        $this
            ->actingAs($staff, 'admin')
            ->putJson("/api/admin/users/{$customer->id}", [
                'first_name' => 'Mario',
                'last_name' => 'Rossi',
                'email' => 'blocked@example.com',
                'phone' => '',
                'is_active' => true,
            ])
            ->assertForbidden();

        $this
            ->actingAs($staff, 'admin')
            ->putJson("/api/admin/admin-users/{$admin->id}", [
                'name' => 'Blocked',
                'email' => 'blocked-admin@example.com',
                'password' => '',
                'role' => 'staff',
                'is_active' => true,
            ])
            ->assertForbidden();
    }
}
