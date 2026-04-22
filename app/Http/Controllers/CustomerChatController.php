<?php

namespace App\Http\Controllers;

use App\Models\CustomerMessage;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class CustomerChatController extends Controller
{
    /**
     * Gửi tin nhắn từ khách hàng và lưu vào hệ thống.
     *
     * Nếu khách chưa từng nhận phản hồi từ cửa hàng, hệ thống sẽ tự động gửi một tin nhắn xác nhận
     * để khách yên tâm trong lúc chờ nhân viên hỗ trợ.
     */
    public function sendMessage(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập để gửi tin nhắn.'
            ], 401);
        }

        $validated = $request->validate([
            'message' => 'required|string|max:1000',
            'product_id' => 'nullable|exists:products,id',
        ], [
            'message.required' => 'Vui lòng nhập tin nhắn.',
            'message.max' => 'Tin nhắn không được vượt quá 1000 ký tự.',
            'product_id.exists' => 'Sản phẩm không tồn tại.',
        ]);

        DB::beginTransaction();
        try {
            $message = CustomerMessage::create([
                'customer_id' => Auth::id(),
                'product_id' => $request->product_id,
                'message' => $validated['message'],
                'sender_type' => 'customer',
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lưu tin nhắn: ' . $e->getMessage()
            ], 500);
        }

        \Illuminate\Support\Facades\Cache::forget('chat.all_conversations');

        try {
            $recipientIds = Notification::recipientIdsForGroups(['admin', 'order_staff']);

            if (empty($recipientIds)) {
                $recipientIds = [0];
            }

            Notification::createForRecipients($recipientIds, [
                'type' => 'chat_customer_message',
                'title' => 'Tin nhắn mới từ khách hàng',
                'content' => Str::limit($validated['message'], 120),
                'related_id' => (int) Auth::id(),
            ]);

            $hasStaffReply = CustomerMessage::query()
                ->where('customer_id', Auth::id())
                ->whereIn('sender_type', ['admin', 'staff'])
                ->exists();

            if (!$hasStaffReply) {
                $defaultReply = 'Xin chào bạn, cửa hàng đã nhận được tin nhắn của bạn. Nhân viên tư vấn sẽ phản hồi chi tiết trong ít phút nữa.';
                $assignedStaffId = $recipientIds[0] > 0 ? $recipientIds[0] : null;
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
        } catch (\Exception $e) {
        }

        return response()->json([
            'success' => true,
            'message' => $message->load('customer'),
        ]);
    }

    /**
     * Lấy danh sách tin nhắn của khách hàng.
     *
     * Hỗ trợ tải tăng dần theo `from_id` để chỉ lấy các tin mới phát sinh.
     */
    public function getMessages(Request $request)
    {
        if (!$request->expectsJson() && !$request->ajax()) {
            return view('pages.customer-chat');
        }

        $limit = 100;
        $fromId = (int) $request->query('from_id', 0);
        $customerId = Auth::id();

        $query = CustomerMessage::where('customer_id', $customerId);

        if ($fromId > 0) {
            $messages = $query->where('id', '>', $fromId)
                ->orderBy('id', 'asc')
                ->get();

            $unreadIds = $messages->where('is_read', false)
                ->whereIn('sender_type', ['staff', 'admin'])
                ->pluck('id');

            if ($unreadIds->count() > 0) {
                CustomerMessage::whereIn('id', $unreadIds)->update(['is_read' => true]);
                \Illuminate\Support\Facades\Cache::forget("chat.unread_count.{$customerId}");
            }
        } else {
            $messages = $query->latest('id')
                ->limit($limit)
                ->get()
                ->reverse()
                ->values();

            $unreadIds = $messages->where('is_read', false)
                ->whereIn('sender_type', ['staff', 'admin'])
                ->pluck('id');

            if ($unreadIds->count() > 0) {
                CustomerMessage::whereIn('id', $unreadIds)->update(['is_read' => true]);
                \Illuminate\Support\Facades\Cache::forget("chat.unread_count.{$customerId}");
            }
        }

        if ($messages->whereIn('sender_type', ['staff', 'admin'])->count() > 0) {
            $messages->load('staff:id,name');
        }

        return response()->json($messages->values());
    }

    /**
     * Admin/nhân viên phản hồi hội thoại của một khách hàng.
     */
    public function replyMessage(Request $request, $customerId)
    {
        $customerId = (int) $customerId;

        $validated = $request->validate([
            'reply' => 'required|string|max:1000',
        ]);

        if (!\App\Models\Customer::where('user_id', $customerId)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Khách hàng không tồn tại'
            ], 404);
        }

        $lastMessage = CustomerMessage::where('customer_id', $customerId)
            ->latest('id')
            ->first();

        $reply = CustomerMessage::create([
            'customer_id' => $customerId,
            'product_id' => $lastMessage?->product_id,
            'staff_id' => Auth::id(),
            'message' => $validated['reply'],
            'sender_type' => Auth::user()->role === 'admin' ? 'admin' : 'staff',
        ]);

        \Illuminate\Support\Facades\Cache::forget("chat.unread_count.{$customerId}");
        \Illuminate\Support\Facades\Cache::forget('chat.all_conversations');

        try {
            Notification::createForRecipients([$customerId], [
                'type' => 'chat_staff_reply',
                'title' => 'Cửa hàng đã phản hồi',
                'content' => Str::limit($validated['reply'], 120),
                'related_id' => (int) $customerId,
            ]);
        } catch (\Exception $e) {
        }

        return response()->json([
            'success' => true,
            'message' => $reply->load('staff'),
        ]);
    }

    /**
     * Danh sách hội thoại cho giao diện chat admin/nhân viên.
     *
     * Chỉ lấy số lượng hội thoại gần nhất để tránh truy vấn nặng.
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

        $cacheKey = 'chat.all_conversations';
        $latestByCustomer = \Illuminate\Support\Facades\Cache::remember($cacheKey, 5, function () {
            $latestIds = CustomerMessage::query()
                ->selectRaw('MAX(id) as id')
                ->groupBy('customer_id')
                ->orderByRaw('MAX(id) DESC')
                ->limit(30)
                ->pluck('id');

            if ($latestIds->isEmpty()) {
                return [];
            }

            $messages = CustomerMessage::query()
                ->whereIn('id', $latestIds)
                ->select(['id', 'customer_id', 'message', 'sender_type', 'is_read', 'created_at'])
                ->with(['customer:id,name,avatar'])
                ->get()
                ->groupBy('customer_id')
                ->map(function ($msgs) {
                    $latest = $msgs->first();
                    $unreadCount = $msgs->where('sender_type', 'customer')->where('is_read', false)->count();

                    return [
                        'customer_id' => (int) $latest->customer_id,
                        'customer_name' => optional($latest->customer)->name ?? 'Khách hàng',
                        'customer_avatar' => optional($latest->customer)->avatar,
                        'last_message' => $latest->message,
                        'last_sender_type' => $latest->sender_type,
                        'last_at' => $latest->created_at,
                        'unread_count' => (int) $unreadCount,
                    ];
                })
                ->sortByDesc('last_at')
                ->values();

            return $messages;
        });

        return response()->json([
            'data' => $latestByCustomer,
        ]);
    }

    /**
     * Chi tiết hội thoại với một khách hàng (admin/nhân viên).
     *
     * Mặc định chỉ tải lịch sử gần nhất để đảm bảo tốc độ hiển thị.
     */
    public function getConversation(Request $request, $customerId)
    {
        $limit = 40;
        $fromId = (int) $request->query('from_id', 0);

        $customerId = (int) $customerId;

        if ($fromId > 0) {
            $messages = CustomerMessage::where('customer_id', $customerId)
                ->where('id', '>', $fromId)
                ->orderBy('id', 'asc')
                ->select(['id', 'customer_id', 'message', 'sender_type', 'is_read', 'created_at', 'staff_id'])
                ->get();
        } else {
            $messages = CustomerMessage::where('customer_id', $customerId)
                ->latest('id')
                ->limit($limit)
                ->select(['id', 'customer_id', 'message', 'sender_type', 'is_read', 'created_at', 'staff_id'])
                ->get()
                ->reverse()
                ->values();
        }

        if ($messages->whereIn('sender_type', ['staff', 'admin'])->count() > 0) {
            $messages->load('staff:id,name');
        }

        $unreadIds = $messages->where('is_read', false)
            ->where('sender_type', 'customer')
            ->pluck('id');

        if ($unreadIds->count() > 0) {
            CustomerMessage::whereIn('id', $unreadIds)->update(['is_read' => true]);
            \Illuminate\Support\Facades\Cache::forget("chat.unread_count.{$customerId}");
        }

        return response()->json($messages->values());
    }

    /**
     * Số tin nhắn cửa hàng chưa đọc ở phía khách hàng.
     */
    public function unreadCount()
    {
        $customerId = Auth::id();
        if (!$customerId) {
            return response()->json(['unread_count' => 0]);
        }

        $cacheKey = "chat.unread_count.{$customerId}";

        $cached = \Illuminate\Support\Facades\Cache::get($cacheKey);
        if ($cached !== null) {
            return response()->json(['unread_count' => $cached]);
        }

        $count = CustomerMessage::query()
            ->where('customer_id', $customerId)
            ->whereIn('sender_type', ['staff', 'admin'])
            ->where('is_read', false)
            ->count();

        \Illuminate\Support\Facades\Cache::put($cacheKey, $count, 5);

        return response()->json([
            'unread_count' => $count,
        ]);
    }
}
