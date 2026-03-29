<?php

namespace App\Http\Controllers;

use App\Models\CustomerMessage;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CustomerChatController extends Controller
{
    /**
     * Gửi tin nhắn từ khách hàng.
     */
    public function sendMessage(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
            'product_id' => 'nullable|exists:products,id',
        ]);

        $message = CustomerMessage::create([
            'customer_id' => Auth::id(),
            'product_id' => $request->product_id,
            'message' => $validated['message'],
            'sender_type' => 'customer',
        ]);

        // Gửi thông báo chat cho admin + nhân viên xử lý đơn.
        $recipientIds = Notification::recipientIdsForGroups(['admin', 'order_staff']);
        Notification::createForRecipients($recipientIds, [
            'type' => 'chat_customer_message',
            'title' => 'Tin nhắn mới từ khách hàng',
            'content' => Str::limit($validated['message'], 120),
            'related_id' => (int) Auth::id(),
        ]);

        // Tự động gửi một tin nhắn mẫu khi khách bắt đầu liên hệ.
        $hasStoreReply = CustomerMessage::query()
            ->where('customer_id', Auth::id())
            ->whereIn('sender_type', ['admin', 'staff'])
            ->exists();

        if (!$hasStoreReply) {
            $defaultReply = 'Xin chào bạn, cửa hàng đã nhận được tin nhắn. Nhân viên tư vấn sẽ phản hồi chi tiết trong ít phút nữa.';
            $assignedStaffId = $recipientIds[0] ?? null;
            $senderType = 'admin';

            if ($assignedStaffId) {
                $assignedUser = User::query()->find($assignedStaffId);
                if ($assignedUser && $assignedUser->role === 'staff') {
                    $senderType = 'staff';
                }
            }

            CustomerMessage::create([
                'customer_id' => Auth::id(),
                'product_id' => $request->product_id,
                'staff_id' => $assignedStaffId,
                'message' => $defaultReply,
                'sender_type' => $senderType,
                'is_read' => false,
            ]);

            Notification::createForRecipients([(int) Auth::id()], [
                'type' => 'chat_staff_reply',
                'title' => 'Cửa hàng đã phản hồi',
                'content' => Str::limit($defaultReply, 120),
                'related_id' => (int) Auth::id(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => $message->load('customer'),
        ]);
    }

    /**
     * Lấy danh sách tin nhắn cho khách hàng.
     */
    public function getMessages(Request $request)
    {
        if (!$request->expectsJson() && !$request->ajax()) {
            return view('pages.customer-chat');
        }

        $messages = CustomerMessage::where('customer_id', Auth::id())
            ->with(['customer', 'staff', 'product'])
            ->orderBy('created_at', 'asc')
            ->get();

        CustomerMessage::where('customer_id', Auth::id())
            ->where('sender_type', '!=', 'customer')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json($messages);
    }

    /**
     * Admin/Staff trả lời tin nhắn theo customer.
     */
    public function replyMessage(Request $request, $customerId)
    {
        $validated = $request->validate([
            'reply' => 'required|string|max:1000',
        ]);

        $lastMessage = CustomerMessage::where('customer_id', $customerId)
            ->latest('id')
            ->firstOrFail();

        $reply = CustomerMessage::create([
            'customer_id' => (int) $customerId,
            'product_id' => $lastMessage->product_id,
            'staff_id' => Auth::id(),
            'message' => $validated['reply'],
            'sender_type' => Auth::user()->role === 'admin' ? 'admin' : 'staff',
        ]);

        // Gửi thông báo cho khách hàng khi cửa hàng trả lời.
        Notification::createForRecipients([(int) $customerId], [
            'type' => 'chat_staff_reply',
            'title' => 'Cửa hàng đã phản hồi',
            'content' => Str::limit($validated['reply'], 120),
            'related_id' => (int) $customerId,
        ]);

        return response()->json([
            'success' => true,
            'message' => $reply->load('staff'),
        ]);
    }

    /**
     * Danh sách hội thoại cho giao diện chat admin/staff.
     */
    public function getAllConversations(Request $request)
    {
        if (!$request->expectsJson() && !$request->ajax()) {
            $customerId = (int) $request->query('customer', 0);

            return redirect()->route('admin.dashboard', [
                'open_admin_chat' => 1,
                'customer' => $customerId > 0 ? $customerId : null,
            ]);
        }

        $latestByCustomer = CustomerMessage::query()
            ->with(['customer', 'product'])
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('customer_id')
            ->map(function ($messages, $customerId) {
                $latest = $messages->first();

                return [
                    'customer_id' => (int) $customerId,
                    'customer_name' => optional($latest->customer)->name ?? 'Khách hàng',
                    'customer_avatar' => optional($latest->customer)->avatar,
                    'product_name' => optional($latest->product)->name,
                    'last_message' => $latest->message,
                    'last_sender_type' => $latest->sender_type,
                    'last_at' => $latest->created_at,
                    'unread_count' => (int) $messages
                        ->where('sender_type', 'customer')
                        ->where('is_read', false)
                        ->count(),
                ];
            })
            ->sortByDesc('last_at')
            ->values();

        return response()->json([
            'data' => $latestByCustomer,
        ]);
    }

    /**
     * Chi tiết hội thoại với một khách hàng.
     */
    public function getConversation($customerId)
    {
        $messages = CustomerMessage::where('customer_id', $customerId)
            ->with(['customer', 'staff', 'product'])
            ->orderBy('created_at', 'asc')
            ->get();

        CustomerMessage::where('customer_id', $customerId)
            ->where('sender_type', 'customer')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json($messages);
    }

    /**
     * Số tin nhắn cửa hàng chưa đọc ở phía khách hàng.
     */
    public function unreadCount()
    {
        $count = CustomerMessage::query()
            ->where('customer_id', Auth::id())
            ->where('sender_type', '!=', 'customer')
            ->where('is_read', false)
            ->count();

        return response()->json([
            'unread_count' => $count,
        ]);
    }
}
