<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $payload = $request->validated();

        $user = User::create([
            'first_name' => $payload['first_name'],
            'last_name' => $payload['last_name'],
            'email' => $payload['email'],
            'phone' => $payload['phone'] ?? null,
            'password' => Hash::make($payload['password']),
        ]);

        UserProfile::create([
            'user_id' => $user->id,
            'gender' => $payload['gender'] ?? null,
            'age_range' => $payload['age_range'] ?? null,
            'origin_city' => $payload['origin_city'] ?? null,
            'rome_area' => $payload['rome_area'] ?? null,
            'food_preferences' => $payload['food_preferences'] ?? [],
            'event_preferences' => $payload['event_preferences'] ?? [],
            'privacy_consent' => $payload['privacy_consent'],
            'privacy_consented_at' => now(),
            'marketing_consent' => $payload['marketing_consent'] ?? false,
            'marketing_consented_at' => ($payload['marketing_consent'] ?? false) ? now() : null,
        ]);

        $token = $user->createToken($payload['device_name'] ?? 'mobile-app')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully.',
            'token' => $token,
            'token_type' => 'Bearer',
            'data' => new UserResource($user->load('profile')),
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $payload = $request->validated();
        $user = User::with('profile')->where('email', $payload['email'])->first();

        if (! $user || ! Hash::check($payload['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials.',
            ], 401);
        }

        if (! $user->is_active) {
            return response()->json([
                'message' => 'User account is inactive.',
            ], 403);
        }

        $user->forceFill(['last_login_at' => now()])->save();
        $token = $user->createToken($payload['device_name'] ?? 'mobile-app')->plainTextToken;

        return response()->json([
            'message' => 'Login successful.',
            'token' => $token,
            'token_type' => 'Bearer',
            'data' => new UserResource($user),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'data' => new UserResource($request->user()->load('profile')),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Logout successful.',
        ]);
    }
}
