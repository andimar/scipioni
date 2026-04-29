<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminUserController extends Controller
{
    private const ROLES = ['admin', 'staff'];

    public function index(Request $request): JsonResponse
    {
        $admins = AdminUser::query()
            ->when(
                $request->filled('search'),
                fn ($query) => $query->where(function ($nestedQuery) use ($request): void {
                    $search = $request->string('search')->toString();

                    $nestedQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('role', 'like', "%{$search}%");
                })
            )
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $admins->map(fn (AdminUser $admin): array => $this->transformAdmin($admin))->values(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->ensureAdmin($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:admin_users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', Rule::in(self::ROLES)],
            'is_active' => ['required', 'boolean'],
        ]);

        $data['password'] = Hash::make($data['password']);

        $admin = AdminUser::create($data);

        return response()->json([
            'message' => 'Account staff creato correttamente.',
            'data' => $this->transformAdmin($admin),
        ], 201);
    }

    public function update(Request $request, AdminUser $adminUser): JsonResponse
    {
        $this->ensureAdmin($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:admin_users,email,'.$adminUser->id],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['required', Rule::in(self::ROLES)],
            'is_active' => ['required', 'boolean'],
        ]);

        if ($adminUser->id === $request->user('admin')->id && (! $data['is_active'] || $data['role'] !== 'admin')) {
            throw ValidationException::withMessages([
                'permission' => ['Non puoi rimuovere il tuo accesso amministratore dalla sessione corrente.'],
            ]);
        }

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        $adminUser->update($data);

        return response()->json([
            'message' => 'Account staff aggiornato correttamente.',
            'data' => $this->transformAdmin($adminUser),
        ]);
    }

    private function transformAdmin(AdminUser $admin): array
    {
        return [
            'id' => $admin->id,
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => $admin->role,
            'is_active' => (bool) $admin->is_active,
            'created_at' => $admin->created_at,
        ];
    }

    private function ensureAdmin(Request $request): void
    {
        if ($request->user('admin')?->role !== 'admin') {
            abort(403, 'Solo un amministratore puo modificare gli account staff.');
        }
    }
}
