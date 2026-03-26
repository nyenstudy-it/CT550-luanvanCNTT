<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Cho phép admin đi qua tất cả.
 * Staff chỉ được vào nếu position của họ nằm trong danh sách $positions.
 * Ví dụ: middleware('can.position:warehouse')
 *         middleware('can.position:cashier,order_staff')
 */
class AdminOrPosition
{
    public function handle(Request $request, Closure $next, string ...$positions): mixed
    {
        $user = auth()->user();

        if (! $user) {
            abort(401);
        }

        if ($user->role === 'admin') {
            return $next($request);
        }

        if (
            $user->role === 'staff' &&
            $user->staff &&
            in_array($user->staff->position, $positions)
        ) {
            return $next($request);
        }

        abort(403, 'Bạn không có quyền truy cập khu vực này.');
    }
}
