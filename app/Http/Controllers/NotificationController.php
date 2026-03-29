<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Notification;

class NotificationController extends Controller
{
    public function customerIndex()
    {
        $notifications = Notification::query()
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        $unreadCount = Notification::query()
            ->where('user_id', Auth::id())
            ->where('is_read', false)
            ->count();

        return view('pages.notifications', compact('notifications', 'unreadCount'));
    }


    public function adminIndex()
    {
        $user = Auth::user();
        $notificationsQuery = Notification::query()->latest();

        if ($user->role !== 'admin') {
            $notificationsQuery->where('user_id', $user->id);
        }

        $notifications = $notificationsQuery->paginate(5);

        $unreadCountQuery = Notification::query()->where('is_read', false);
        if ($user->role !== 'admin') {
            $unreadCountQuery->where('user_id', $user->id);
        }

        $unreadCount = $unreadCountQuery->count();

        return view('admin.notifications.index', compact('notifications', 'unreadCount'));
    }

    public function read(Request $request, $id)
    {
        $user = Auth::user();

        $notificationQuery = Notification::where('id', $id);

        if ($user->role !== 'admin') {
            $notificationQuery->where('user_id', $user->id);
        }

        $notification = $notificationQuery->first();

        if (!$notification) {
            $fallbackRoute = in_array($user->role, ['admin', 'staff', 'order_staff', 'warehouse'])
                ? 'admin.notifications'
                : 'customer.notifications';

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Thông báo không tồn tại hoặc bạn không có quyền truy cập.',
                ], 404);
            }

            return redirect()->route($fallbackRoute)
                ->with('error', 'Thông báo không tồn tại hoặc bạn không có quyền truy cập.');
        }

        if (!$notification->is_read) {
            $notification->update(['is_read' => true]);
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'url' => $notification->url,
            ]);
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
        $user = Auth::user();
        $notificationsQuery = Notification::query()->latest();

        if ($user->role !== 'admin') {
            $notificationsQuery->where('user_id', $user->id);
        }

        $notifications = $notificationsQuery->limit(3)->get();

        $unreadCountQuery = Notification::query()->where('is_read', false);
        if ($user->role !== 'admin') {
            $unreadCountQuery->where('user_id', $user->id);
        }

        $unreadCount = $unreadCountQuery->count();

        return response()->json([
            'notifications' => $notifications,
            'unreadCount' => $unreadCount
        ]);
    }

    public function markAllAsRead(Request $request)
    {
        $user = Auth::user();
        $query = Notification::query()->where('is_read', false);

        if ($user->role !== 'admin') {
            $query->where('user_id', $user->id);
        }

        $query->update(['is_read' => true]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Đã đánh dấu tất cả thông báo là đã đọc.');
    }

    public function markChatNotificationsAsRead()
    {
        $user = Auth::user();
        if ($user->role !== 'customer') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        Notification::query()
            ->where('user_id', Auth::id())
            ->where('type', 'chat_staff_reply')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }
}
