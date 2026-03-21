<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StaffPosition
{
    public function handle($request, Closure $next, ...$positions)
    {
        $user = auth()->user();

        if (!$user || $user->role !== 'staff') {
            abort(403);
        }

        if (!$user->staff) {
            abort(403);
        }

        if (!in_array($user->staff->position, $positions)) {
            abort(403);
        }

        return $next($request);
    }
}
