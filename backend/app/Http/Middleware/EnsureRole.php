<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Usage in routes:
 *   ->middleware('role:admin,super_admin')
 *
 * Blocks the request with 403 if the authenticated user's role
 * is not one of the allowed roles. Used to protect admin-only
 * and super-admin-only API endpoints on the BACKEND (the frontend
 * route guard is only for UX, this is the real security check).
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if (!$user->is_active) {
            return response()->json(['message' => 'Account deactivated.'], 403);
        }

        if (!in_array($user->role, $roles, true)) {
            return response()->json(['message' => 'Forbidden. You do not have permission to access this resource.'], 403);
        }

        return $next($request);
    }
}
