<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (! $request->user()) {
            return redirect()->route('login');
        }

        $expectedRole = UserRole::from($role);

        if ($request->user()->role !== $expectedRole) {
            abort(403, 'Anda tidak memiliki izin mengakses area ini.');
        }

        return $next($request);
    }
}
