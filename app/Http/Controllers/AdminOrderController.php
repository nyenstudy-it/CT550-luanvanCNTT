<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderCancellation;
use App\Models\ImportItem;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class AdminOrderController extends Controller
{
    private const ADMIN_CANCEL_REASON_PRESETS = [
        'out_of_stock' => 'Hết hàng trong kho',
        'cannot_contact' => 'Không liên hệ được khách hàng',
        'delivery_area_unavailable' => 'Khu vực giao hàng tạm ngưng phục vụ',
        'suspected_fraud' => 'Đơn hàng có dấu hiệu rủi ro/gian lận',
        'system_error' => 'Lỗi hệ thống xử lý đơn hàng',
        'other' => 'Lý do khác',
    ];

    // Danh sách đơn hàng
    public function index(Request $request)
    {
        $query = Order::with('customer', 'payment', 'returns.images');

        // lọc trạng thái
        if ($request->status) {
            $query->where('status', $request->status);
        }

        // tìm theo mã đơn
        if ($request->order_id) {
            $query->where('id', $request->order_id);
        }

        if ($request->phone) {
            $query->where('receiver_phone', 'like', '%' . $request->phone . '%');
        }

        $orders = $query->latest()->paginate(10);

        $stats = [
            'total' => Order::count(),
            'pending' => Order::where('status', 'pending')->count(),
            'shipping' => Order::where('status', 'shipping')->count(),
            'refund_requested' => Order::where('status', 'refund_requested')->count(),
            'today' => Order::whereDate('created_at', now()->toDateString())->count(),
        ];

        $cancelReasonPresets = self::ADMIN_CANCEL_REASON_PRESETS;

        return view('admin.orders.index', compact('orders', 'stats', 'cancelReasonPresets'));
    }

    // Chi tiết đơn hàng
    public function show($id)
    {
        $order = Order::with([
            'items.variant.product.images',
            'items.variant.images',
            'customer',
            'cancellation',
            'returns.images',
            'payment'
        ])->findOrFail($id);

        return view('admin.orders.detail', compact('order'));
    }

    // Cập nhật trạng thái đơn hàng
    public function updateStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $request->validate([
            'status' => 'required|in:pending,confirmed,shipping,completed'
        ]);
        // Chặn thay đổi nếu đơn đã hoàn thành, đã hủy, hoặc đã hoàn tiền
        if (
            in_array($order->status, ['completed', 'cancelled']) ||
            ($order->payment && $order->payment->status == 'paid' && $order->refund?->status == 'refunded')
        ) {
            return back()->with('error', 'Không thể thay đổi trạng thái đơn này vì đã hoàn tiền hoặc đã hoàn thành');
        }

        $user = Auth::user();
        $oldStatus = $order->status;
        $staffPosition = $user->role === 'staff' ? ($user->staff?->position ?? null) : null;
        $isOrderOperator = $user->role === 'staff' && in_array($staffPosition, ['cashier', 'order_staff'], true);

        if ($user->role == 'admin') {
            $order->status = $request->status;
        } elseif ($isOrderOperator) {

            if (in_array($request->status, ['confirmed', 'shipping', 'completed'])) {
                $order->status = $request->status;
            } else {
                return back()->with('error', 'Bạn không có quyền đổi trạng thái này');
            }
        } else {

            return back()->with('error', 'Không có quyền');
        }

        if ($order->status == 'completed' && $order->payment) {
            if (strtolower($order->payment->method) == 'cod' && $order->payment->status == 'pending') {
                $order->payment->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);
            } elseif (!$order->payment->paid_at) {
                $order->payment->update([
                    'paid_at' => now(),
                ]);
            }
        }
        $order->save();

        if ($oldStatus != $order->status) {
            $historyMessage = $this->historyMessageForStatus($order->status);

            $exists = Notification::where('type', 'order_status_change')
                ->where('related_id', $order->id)
                ->where('user_id', $order->customer->user_id)
                ->where('content', $historyMessage)
                ->exists();

            if (!$exists) {
                Notification::create([
                    'user_id' => $order->customer->user_id,
                    'type' => 'order_status_change',
                    'title' => "Cập nhật đơn hàng #{$order->id}",
                    'content' => $historyMessage,
                    'related_id' => $order->id,
                    'is_read' => false,
                ]);
            }

            $this->notifyCashierStats($order, 'Đơn #' . $order->id . ' chuyển sang trạng thái ' . $historyMessage . '.');
        }

        return back()->with('success', 'Cập nhật trạng thái thành công');
    }

    // Admin hủy đơn
    public function cancel(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|in:' . implode(',', array_keys(self::ADMIN_CANCEL_REASON_PRESETS)),
            'reason_note' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            $order = Order::with(['items', 'payment', 'customer'])
                ->lockForUpdate()
                ->findOrFail($id);

            if (in_array($order->status, ['shipping', 'completed', 'cancelled', 'refund_requested', 'refunded'])) {
                DB::rollBack();
                return back()->with('error', 'Không thể hủy đơn ở trạng thái này');
            }

            $isPaidOnline = $order->payment
                && $order->payment->status == 'paid'
                && in_array(strtolower($order->payment->method), ['momo', 'vnpay']);

            // Với đơn chưa thanh toán online, hoàn kho ngay khi hủy.
            if (!$isPaidOnline) {
                $this->restoreStock($order);
            }

            $reasonCode = $request->input('reason');
            $reasonNote = trim((string) $request->input('reason_note'));
            $storedReason = $reasonCode;
            if ($reasonCode === 'other' && $reasonNote !== '') {
                $storedReason .= ':' . $reasonNote;
            }

            OrderCancellation::create([
                'order_id' => $order->id,
                'reason' => $storedReason,
                'cancelled_by' => 'admin',
                'cancelled_at' => now()
            ]);

            if ($isPaidOnline) {
                $order->update([
                    'previous_status' => $order->status,
                    'status' => 'refund_requested'
                ]);
            } else {
                $order->update([
                    'status' => 'cancelled'
                ]);
            }

            $exists = Notification::where('type', 'order_cancel')
                ->where('related_id', $order->id)
                ->where('user_id', $order->customer->user_id)
                ->exists();

            if (!$exists) {
                Notification::create([
                    'user_id' => $order->customer->user_id,
                    'type' => 'order_cancel',
                    'title' => "Đơn hàng #{$order->id} đã bị hủy",
                    'content' => "Admin đã hủy đơn hàng của bạn",
                    'related_id' => $order->id,
                    'is_read' => false,
                ]);
            }

            $this->notifyCashierStats($order, 'Đơn #' . $order->id . ' đã bị hủy và số liệu thu ngân đã cập nhật.');

            DB::commit();

            return back()->with('success', 'Đã hủy đơn hàng');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function approveRefund($id)
    {
        $order = Order::with(['items', 'payment', 'returns.images', 'cancellation'])->findOrFail($id);

        DB::beginTransaction();

        try {

            $payment = $order->payment;

            if (!$payment || $payment->status != 'paid') {
                return back()->with('error', 'Không hợp lệ');
            }

            if ($order->status != 'refund_requested') {
                return back()->with('error', 'Đơn không ở trạng thái hoàn tiền');
            }

            if ($payment->refund_status == 'completed') {
                return back()->with('error', 'Đơn đã hoàn tiền trước đó');
            }

            $shouldRestoreStockOnApprove = in_array($order->previous_status, ['pending', 'confirmed']);

            if ($shouldRestoreStockOnApprove) {
                // Hoàn kho tại thời điểm duyệt hoàn tiền để tránh lệch kho khi bị từ chối hoàn.
                $this->restoreStock($order);
            }

            foreach ($order->returns as $return) {
                $reason = $return->reason;
                $description = $return->description;
                $images = $return->images;
            }

            $payment->update([
                'refund_amount' => $payment->amount,
                'refund_status' => 'completed',
                'refund_at' => now()
            ]);

            $order->update([
                'status' => 'refunded',
                'previous_status' => null
            ]);
            $exists = Notification::where('type', 'order_refund')
                ->where('related_id', $order->id)
                ->where('user_id', $order->customer->user_id)
                ->exists();

            if (!$exists) {
                Notification::create([
                    'user_id' => $order->customer->user_id,
                    'type' => 'order_refund',
                    'title' => "Đơn hàng #{$order->id} đã được hoàn tiền",
                    'content' => "Yêu cầu hoàn tiền của bạn đã được admin duyệt và hoàn tiền.",
                    'related_id' => $order->id,

                    'is_read' => false,
                ]);
            }

            $this->notifyCashierStats($order, 'Đơn #' . $order->id . ' đã hoàn tiền, số liệu doanh thu đã cập nhật.');

            DB::commit();

            return back()->with('success', 'Đã hoàn tiền');
        } catch (\Exception $e) {

            DB::rollback();

            return back()->with('error', $e->getMessage());
        }
    }

    public function rejectRefund($id)
    {
        DB::beginTransaction();

        try {
            $order = Order::with('payment', 'cancellation', 'returns.images', 'customer')
                ->lockForUpdate()
                ->findOrFail($id);

            if ($order->status != 'refund_requested') {
                DB::rollBack();
                return back()->with('error', 'Không hợp lệ');
            }

            if ($order->payment && $order->payment->refund_status == 'pending') {
                $order->payment->update([
                    'refund_status' => 'failed'
                ]);
            }

            $order->update([
                'status' => $order->previous_status ?? 'pending',
                'previous_status' => null
            ]);

            $exists = Notification::where('type', 'refund_rejected')
                ->where('related_id', $order->id)
                ->where('user_id', $order->customer->user_id)
                ->exists();

            if (!$exists) {
                Notification::create([
                    'user_id' => $order->customer->user_id,
                    'type' => 'refund_rejected',
                    'title' => "Yêu cầu hoàn tiền cho đơn #{$order->id} bị từ chối",
                    'content' => "Yêu cầu hoàn tiền của bạn đã bị admin từ chối.",
                    'related_id' => $order->id,
                    'is_read' => false,
                ]);
            }

            $this->notifyCashierStats($order, 'Yêu cầu hoàn tiền của đơn #' . $order->id . ' đã bị từ chối, số liệu thu ngân đã cập nhật.');

            DB::commit();

            return back()->with('success', 'Đã từ chối yêu cầu hoàn tiền');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }
    private function restoreStock($order)
    {
        foreach ($order->items as $item) {

            $batches = is_string($item->batch_details)
                ? json_decode($item->batch_details, true) ?? []
                : $item->batch_details;

            if (!is_array($batches)) continue;

            foreach ($batches as $batch) {

                if (!isset($batch['batch_id'], $batch['quantity'])) continue;

                $importItem = ImportItem::lockForUpdate()->find($batch['batch_id']);

                if ($importItem) {
                    $batchQty = (int) $batch['quantity'];
                    $maxRestorable = max(0, (int) $importItem->quantity - (int) $importItem->remaining_quantity);
                    $restoreQty = min($batchQty, $maxRestorable);

                    if ($restoreQty <= 0) {
                        continue;
                    }

                    $importItem->increment('remaining_quantity', $restoreQty);

                    $inventory = Inventory::firstOrCreate(
                        ['product_variant_id' => $importItem->product_variant_id],
                        ['quantity' => 0]
                    );

                    $inventory->increment('quantity', $restoreQty);
                }
            }
        }
    }

    private function historyMessageForStatus(string $status): string
    {
        return match ($status) {
            'pending' => 'Đơn hàng đang chờ xử lý',
            'confirmed' => 'Đơn hàng đã được xác nhận',
            'shipping' => 'Đơn hàng đang được giao',
            'completed' => 'Đơn hàng đã giao thành công',
            'cancelled' => 'Đơn hàng đã bị huỷ',
            'refund_requested' => 'Yêu cầu hoàn tiền đang được xử lý',
            'refunded' => 'Đơn hàng đã được hoàn tiền',
            default => 'Trạng thái đơn hàng đã được cập nhật',
        };
    }

    private function notifyCashierStats(Order $order, string $message): void
    {
        $recipientIds = Notification::recipientIdsForGroups(['admin', 'cashier']);

        Notification::createForRecipients($recipientIds, [
            'type' => 'cashier_stats_update',
            'title' => 'Cập nhật dữ liệu thống kê',
            'content' => $message,
            'related_id' => $order->id,
        ]);
    }
}
