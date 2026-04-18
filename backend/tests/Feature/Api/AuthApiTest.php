<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_and_receive_token(): void
    {
        $user = User::create([
            'first_name' => 'Mario',
            'last_name' => 'Rossi',
            'email' => 'mario@example.com',
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

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'mario@example.com',
            'password' => 'password',
            'device_name' => 'phpunit-device',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('message', 'Login successful.')
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonPath('data.email', 'mario@example.com');

        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_user_can_register_and_receive_token(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'first_name' => 'Giulia',
            'last_name' => 'Bianchi',
            'email' => 'giulia@example.com',
            'phone' => '+39 333 1111111',
            'password' => 'password123',
            'device_name' => 'iphone-test',
            'gender' => 'donna',
            'age_range' => '25-34',
            'origin_city' => 'Roma',
            'rome_area' => 'Prati',
            'food_preferences' => ['vino bianco'],
            'event_preferences' => ['degustazioni guidate'],
            'privacy_consent' => true,
            'marketing_consent' => true,
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('message', 'User registered successfully.')
            ->assertJsonPath('data.email', 'giulia@example.com')
            ->assertJsonPath('data.profile.rome_area', 'Prati');

        $this->assertDatabaseHas('users', [
            'email' => 'giulia@example.com',
        ]);
    }
}
