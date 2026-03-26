<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Notification;

class NotificationController extends Controller
{
    public function customerIndex()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        $unreadCount = Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->count();

        return view('pages.notifications', compact('notifications', 'unreadCount'));
    }


    public function adminIndex()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->latest()
            ->paginate(15);

        $unreadCount = Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->count();

        return view('admin.notifications.index', compact('notifications', 'unreadCount'));
    }

    public function read($id)
    {
        $user = Auth::user();

        $notification = Notification::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$notification) {
            $fallbackRoute = in_array($user->role, ['admin', 'staff', 'order_staff', 'warehouse'])
                ? 'admin.notifications'
                : 'customer.notifications';

            return redirect()->route($fallbackRoute)
                ->with('error', 'Thông báo không tồn tại hoặc bạn không có quyền truy cập.');
        }

        if (!$notification->is_read) {
            $notification->update(['is_read' => true]);
        }

        return redirect($notification->url);
    }

    public function markAsRead($id)
    {
        $notif = \App\Models\Notification::where('user_id', Auth::id())->findOrFail($id);
        $notif->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    public function getDropdown()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->latest()
            ->limit(5)
            ->get();

        $unreadCount = Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->count();

        return response()->json([
            'notifications' => $notifications,
            'unreadCount' => $unreadCount
        ]);
    }
}
