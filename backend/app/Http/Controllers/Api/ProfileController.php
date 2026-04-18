<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user()->load('profile');

        return response()->json([
            'data' => new UserResource($user),
        ]);
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user()->load('profile');
        $payload = $request->validated();

        if (array_key_exists('phone', $payload)) {
            $user->update(['phone' => $payload['phone']]);
        }

        $profileData = collect($payload)->except('phone')->all();

        if (array_key_exists('marketing_consent', $profileData)) {
            $profileData['marketing_consented_at'] = $profileData['marketing_consent'] ? now() : null;
        }

        $user->profile->update($profileData);

        return response()->json([
            'message' => 'Profile updated.',
            'data' => new UserResource($user->fresh()->load('profile')),
        ]);
    }
}
