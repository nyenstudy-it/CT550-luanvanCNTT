<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\CategoryProduct;
use App\Models\Notification;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrap();

        // Categories cho frontend
        View::composer('pages.*', function ($view) {
            $view->with('categories', CategoryProduct::all());
        });

        // Notifications cho mọi view
        View::composer('*', function ($view) {
            $notifications = collect(); // default rỗng
            $unreadCount = 0;

            if (Auth::check()) {
                $user = Auth::user();

                if ($user->role === 'customer') {
                    $notifications = Notification::where('user_id', $user->id)
                        ->orderBy('created_at', 'desc')
                        ->get(); // lấy tất cả notifications của customer
                    $unreadCount = $notifications->where('is_read', false)->count();
                } elseif (in_array($user->role, ['admin', 'staff', 'order_staff', 'warehouse'])) {
                    $notifications = Notification::where('user_id', $user->id)
                        ->orderBy('created_at', 'desc')
                        ->take(5)
                        ->get(); // admin/staff: chỉ lấy thông báo của chính user
                    $unreadCount = $notifications->where('is_read', false)->count();
                }
            }

            $view->with(compact('notifications', 'unreadCount'));
        });
    }
}
