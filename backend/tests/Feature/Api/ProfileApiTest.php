<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileApiTest extends TestCase
{
    use RefreshDatabase;

    protected function createAuthenticatedUser(): User
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
            'gender' => 'uomo',
            'age_range' => '35-44',
            'origin_city' => 'Roma',
            'rome_area' => 'Prati',
            'privacy_consent' => true,
            'privacy_consented_at' => now(),
            'marketing_consent' => true,
            'marketing_consented_at' => now(),
        ]);

        return $user;
    }

    public function test_profile_requires_authentication(): void
    {
        $this->getJson('/api/v1/profile')
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Unauthenticated.');
    }

    public function test_authenticated_user_can_read_and_update_profile(): void
    {
        $user = $this->createAuthenticatedUser();

        $token = $user->createToken('phpunit')->plainTextToken;
        $headers = ['Authorization' => 'Bearer '.$token];

        $this->getJson('/api/v1/profile', $headers)
            ->assertOk()
            ->assertJsonPath('data.email', 'mario@example.com')
            ->assertJsonPath('data.profile.rome_area', 'Prati');

        $this->putJson('/api/v1/profile', [
            'phone' => '+39 333 9999999',
            'rome_area' => 'Roma Nord',
            'marketing_consent' => false,
        ], $headers)
            ->assertOk()
            ->assertJsonPath('data.phone', '+39 333 9999999')
            ->assertJsonPath('data.profile.rome_area', 'Roma Nord')
            ->assertJsonPath('data.profile.marketing_consent', false);
    }
}
