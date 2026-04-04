<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->isAdmin()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized Access Protocol.'], 403);
            }
            return redirect('/')->with('error', 'You do not have administrative clearance for this sector.');
        }

        if (!$request->user()->isActive()) {
            \Illuminate\Support\Facades\Auth::logout();
            return redirect('/login')->with('error', 'Your account access has been restricted or is pending review.');
        }

        return $next($request);
    }
}
