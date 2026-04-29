<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $users = User::query()
            ->withCount('bookings')
            ->when(
                $request->filled('search'),
                fn ($query) => $query->where(function ($nestedQuery) use ($request): void {
                    $search = $request->string('search')->toString();

                    $nestedQuery
                        ->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                })
            )
            ->when(
                $request->filled('status'),
                fn ($query) => $query->where('is_active', $request->string('status')->toString() === 'active')
            )
            ->latest()
            ->paginate($request->integer('per_page', 15))
            ->withQueryString();

        return response()->json([
            'data' => $users->through(fn (User $user): array => $this->transformUser($user))->items(),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
                'from' => $users->firstItem(),
                'to' => $users->lastItem(),
            ],
        ]);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $this->ensureAdmin($request);

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'phone' => ['nullable', 'string', 'max:30'],
            'is_active' => ['required', 'boolean'],
        ]);

        $user->update($data);
        $user->loadCount('bookings');

        return response()->json([
            'message' => 'Utente aggiornato correttamente.',
            'data' => $this->transformUser($user),
        ]);
    }

    private function transformUser(User $user): array
    {
        return [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'name' => trim($user->first_name.' '.$user->last_name),
            'email' => $user->email,
            'phone' => $user->phone,
            'is_active' => (bool) $user->is_active,
            'bookings_count' => (int) ($user->bookings_count ?? 0),
            'last_login_at' => $user->last_login_at,
            'created_at' => $user->created_at,
        ];
    }

    private function ensureAdmin(Request $request): void
    {
        if ($request->user('admin')?->role !== 'admin') {
            abort(403, 'Solo un amministratore puo modificare gli utenti.');
        }
    }
}
