<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderCancellation;
use App\Models\ImportItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class AdminOrderController extends Controller
{
    // Danh sách đơn hàng
    public function index(Request $request)
    {
        $query = Order::with('customer', 'payment');

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

        return view('admin.orders.index', compact('orders'));
    }

    // Chi tiết đơn hàng
    public function show($id)
    {
        $order = Order::with([
            'items.variant.product.images',
            'items.variant.images',
            'customer',
            'cancellation',
            'payment'
        ])->findOrFail($id);

        return view('admin.orders.detail', compact('order'));
    }

    // Cập nhật trạng thái đơn hàng
    public function updateStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $request->validate([
            'status' => 'required'
        ]);
        if (in_array($order->status, ['completed', 'cancelled', 'refund_requested', 'refunded'])) {
            return back()->with('error', 'Không thể thay đổi trạng thái đơn này');
        }

        $user = Auth()->user();

        if ($user->role == 'admin') {

            $order->status = $request->status;
        } elseif ($user->role == 'order_staff') {

            if (in_array($request->status, ['confirmed', 'shipping', 'completed'])) {
                $order->status = $request->status;
            } else {
                return back()->with('error', 'Bạn không có quyền đổi trạng thái này');
            }
        } elseif ($user->role == 'warehouse') {

            if ($request->status == 'pending') {
                $order->status = $request->status;
            } else {
                return back()->with('error', 'Kho chỉ được chuyển sang trạng thái chuẩn bị');
            }
        } else {

            return back()->with('error', 'Không có quyền');
        }

        $order->save();

        return back()->with('success', 'Cập nhật trạng thái thành công');
    }

    // Admin hủy đơn
    public function cancel(Request $request, $id)
    {
        $order = Order::with(['items', 'payment'])->findOrFail($id);

        if (in_array($order->status, ['shipping', 'completed', 'cancelled'])) {
            return back()->with('error', 'Không thể hủy đơn ở trạng thái này');
        }

        foreach ($order->items as $item) {

            $batches = is_string($item->batch_details)
                ? json_decode($item->batch_details, true) ?? []
                : $item->batch_details;

            if (!is_array($batches)) continue;

            foreach ($batches as $batch) {

                if (!isset($batch['batch_id'], $batch['quantity'])) continue;

                $importItem = ImportItem::find($batch['batch_id']);

                if ($importItem) {

                    \App\Models\Inventory::where(
                        'product_variant_id',
                        $importItem->product_variant_id
                    )->increment('quantity', $batch['quantity']);

                    $importItem->increment('remaining_quantity', $batch['quantity']);
                }
            }
        }

        OrderCancellation::create([
            'order_id' => $order->id,
            'reason' => $request->reason,
            'cancelled_by' => 'admin',
            'cancelled_at' => now()
        ]);

        if (
            $order->payment &&
            $order->payment->status == 'paid' &&
            in_array(strtolower($order->payment->method), ['momo', 'vnpay'])
        ) {

            $order->update([
                'previous_status' => $order->status,
                'status' => 'refund_requested'
            ]);
        } else {

            $order->update([
                'status' => 'cancelled'
            ]);
        }

        return back()->with('success', 'Đã hủy đơn hàng');
    }

    public function approveRefund($id)
    {
        $order = Order::with(['items', 'payment'])->findOrFail($id);

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

            $payment->update([
                'refund_amount' => $payment->amount,
                'refund_status' => 'completed',
                'refund_at' => now()
            ]);

            $order->update([
                'status' => 'refunded',
                'previous_status' => null
            ]);

            DB::commit();

            return back()->with('success', 'Đã hoàn tiền');
        } catch (\Exception $e) {

            DB::rollback();

            return back()->with('error', $e->getMessage());
        }
    }

    public function rejectRefund($id)
    {
        $order = Order::with('payment', 'cancellation')->findOrFail($id);

        if ($order->status != 'refund_requested') {
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

        return back()->with('success', 'Đã từ chối yêu cầu hoàn tiền');
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

                $importItem = ImportItem::find($batch['batch_id']);

                if ($importItem) {

                    $importItem->increment('remaining_quantity', $batch['quantity']);

                    \App\Models\Inventory::where(
                        'product_variant_id',
                        $importItem->product_variant_id
                    )->increment('quantity', $batch['quantity']);
                }
            }
        }
    }
}
