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
                $query = Notification::query()
                    ->where('user_id', $user->id)
                    ->orderBy('created_at', 'desc');

                $notifications = $user->role === 'customer'
                    ? (clone $query)->get()
                    : (clone $query)->take(3)->get();

                $unreadCount = Notification::query()
                    ->where('user_id', $user->id)
                    ->where('is_read', false)
                    ->count();
            }

            $view->with(compact('notifications', 'unreadCount'));
        });
    }
}
