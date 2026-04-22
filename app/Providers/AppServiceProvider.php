<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\CategoryProduct;
use App\Models\Notification;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Login;
use App\Listeners\RecordAdminNetworkOnLogin;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Login event listener is registered in EventServiceProvider; avoid duplicate registration here.

        Paginator::useBootstrap();

        // Categories cho frontend
        View::composer('pages.*', function ($view) {
            $view->with('categories', CategoryProduct::all());
        });

        // Notifications cho mọi view (không ghi đè cho admin.notifications.index)
        View::composer('*', function ($view) {
            // Skip if this is the admin notifications detail page that already has paginated notifications
            if ($view->getName() === 'admin.notifications.index') {
                return;
            }

            $notifications = collect(); // default rỗng
            $unreadCount = 0;

            if (Auth::check()) {
                $user = Auth::user();

                // All users only see notifications meant for them (by user_id)
                $query = Notification::query()
                    ->where('user_id', $user->id)
                    ->orderBy('created_at', 'desc');

                // Get more notifications for dropdown (5 instead of 3)
                $dropdownLimit = $user->role === 'customer' ? 10 : 5;
                $notifications = (clone $query)->take($dropdownLimit)->get();

                // Count unread notifications
                $unreadCountQuery = Notification::query()
                    ->where('user_id', $user->id)
                    ->where('is_read', false);
                $unreadCount = $unreadCountQuery->count();
            }

            $view->with(compact('notifications', 'unreadCount'));
        });
    }
}
