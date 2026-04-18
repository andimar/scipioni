<?php

namespace Database\Seeders;

use App\Models\AdminUser;
use App\Models\Event;
use App\Models\EventAudience;
use App\Models\EventCategory;
use App\Models\EventTag;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = AdminUser::updateOrCreate(
            ['email' => 'admin@magazzinoscipioni.it'],
            [
                'name' => 'Scipioni Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        $category = EventCategory::updateOrCreate(
            ['slug' => 'degustazioni'],
            [
                'name' => 'Degustazioni',
                'description' => 'Degustazioni guidate e serate a tema.',
                'is_active' => true,
            ]
        );

        $tags = collect([
            ['name' => 'Bollicine', 'slug' => 'bollicine'],
            ['name' => 'Rossi', 'slug' => 'rossi'],
            ['name' => 'Jazz', 'slug' => 'jazz'],
        ])->map(fn (array $tag) => EventTag::updateOrCreate(['slug' => $tag['slug']], $tag));

        $user = User::updateOrCreate(
            ['email' => 'cliente@example.com'],
            [
                'first_name' => 'Mario',
                'last_name' => 'Rossi',
                'phone' => '+39 333 1234567',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );

        UserProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'gender' => 'uomo',
                'age_range' => '35-44',
                'origin_city' => 'Roma',
                'rome_area' => 'Prati',
                'food_preferences' => ['vino rosso', 'formaggi'],
                'event_preferences' => ['degustazioni guidate', 'eventi esclusivi'],
                'privacy_consent' => true,
                'privacy_consented_at' => now(),
                'marketing_consent' => true,
                'marketing_consented_at' => now(),
            ]
        );

        $event = Event::updateOrCreate(
            ['slug' => 'serata-bollicine-di-primavera'],
            [
                'category_id' => $category->id,
                'created_by_admin_id' => $admin->id,
                'updated_by_admin_id' => $admin->id,
                'title' => 'Serata Bollicine di Primavera',
                'subtitle' => 'Percorso di degustazione dedicato alle bollicine europee',
                'short_description' => 'Una degustazione riservata con selezione di etichette e abbinamenti.',
                'full_description' => 'Evento dedicato agli iscritti del club con selezione guidata di bollicine, racconti di cantina e piccoli abbinamenti dalla cucina.',
                'venue_name' => 'Magazzino Scipioni',
                'venue_address' => 'Via degli Scipioni, Roma',
                'starts_at' => now()->addWeeks(2),
                'ends_at' => now()->addWeeks(2)->addHours(2),
                'capacity' => 24,
                'price' => 45,
                'booking_status' => 'open',
                'status' => 'published',
                'requires_approval' => false,
                'is_featured' => true,
                'published_at' => now(),
            ]
        );

        $event->tags()->sync($tags->pluck('id')->take(2)->all());

        EventAudience::updateOrCreate(
            ['event_id' => $event->id],
            [
                'filters' => [
                    'age_ranges' => ['30-34', '35-44', '45-54'],
                    'rome_areas' => ['Prati', 'Roma Nord'],
                    'event_preferences' => ['degustazioni guidate'],
                ],
                'is_enabled' => true,
            ]
        );
    }
}
