<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Manages users for the AdminUsersPage.
 * All routes here are protected with the "role:admin,super_admin" middleware
 * (changeRole and deactivate additionally restricted to super_admin in routes).
 */
class UserController extends Controller
{
    /**
     * GET /api/users
     */
    public function index(Request $request)
    {
        $query = User::query();

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role = $request->query('role')) {
            $query->where('role', $role);
        }

        return response()->json($query->orderBy('name')->get());
    }

    /**
     * GET /api/users/{user}
     */
    public function show(User $user)
    {
        $user->load('households');

        return response()->json($user);
    }

    /**
     * PUT/PATCH /api/users/{user}
     * Allows updating basic profile fields.
     */
    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', 'unique:users,email,' . $user->id],
        ]);

        $user->update($data);

        return response()->json($user);
    }

    /**
     * PATCH /api/users/{user}/role
     * super_admin only (enforced via route middleware).
     */
    public function changeRole(Request $request, User $user)
    {
        $data = $request->validate([
            'role' => ['required', 'in:user,admin,super_admin'],
        ]);

        $user->update(['role' => $data['role']]);

        return response()->json($user);
    }

    /**
     * PATCH /api/users/{user}/deactivate
     * Toggles is_active. super_admin only (enforced via route middleware).
     */
    public function deactivate(Request $request, User $user)
    {
        $user->update(['is_active' => !$user->is_active]);

        return response()->json($user);
    }
}
