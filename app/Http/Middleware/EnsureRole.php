<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = Auth::user();

        if (! $user) {
            return to_route('login');
        }

        $allowedRoles = collect($roles)
            ->map(static fn (string $role) => UserRole::tryFrom($role))
            ->filter();

        if ($allowedRoles->isEmpty()) {
            abort(403, 'Role tidak valid.');
        }

        if (! $allowedRoles->contains($user->role)) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}
