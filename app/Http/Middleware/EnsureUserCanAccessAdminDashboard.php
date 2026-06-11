<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserCanAccessAdminDashboard
{
    /**
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !in_array($user->role, [
            User::ROLE_SUPER_ADMIN,
            User::ROLE_ADMIN,
            User::ROLE_INSTRUCTOR,
        ], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Admin dashboard access denied.',
                'data' => null,
                'meta' => null,
                'errors' => null,
            ], 403);
        }

        if (!$user->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Account is not active.',
                'data' => null,
                'meta' => null,
                'errors' => null,
            ], 403);
        }

        return $next($request);
    }
}
