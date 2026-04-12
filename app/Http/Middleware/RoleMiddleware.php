<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect('/');
        }
        if (empty($roles)) return $next($request);
        if (in_array($user->role, $roles)) {
            return $next($request);
        }
        abort(403, 'Unauthorized');
    }
}
