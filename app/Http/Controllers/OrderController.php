<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\Inventory;
use App\Models\ImportItem;
use App\Models\Payment;
use App\Models\OrderCancellation;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function myOrders(Request $request)
    {
        $customer = Auth::user()->customer;

        $query = Order::with('items.variant.product.images')
            ->where('customer_id', $customer->id)
            ->latest();

        // lọc trạng thái
        if ($request->status) {
            $query->where('status', $request->status);
        }

        $orders = $query->get();

        return view('pages.my-orders', compact('orders'));
    }

    public function orderDetail($id)
    {
        $customer = Auth::user()->customer;

        $order = Order::with([
            'items.variant.images',
            'items.variant.product.images',
            'cancellation',
            'payment'
        ])

            ->where('customer_id', $customer->id)
            ->findOrFail($id);

        return view('pages.order-detail', compact('order'));
    }

    public function cancel(Request $request, $id)
    {
        $customer = Auth::user()->customer;

        $request->validate([
            'reason' => 'required|string'
        ]);

        $order = Order::with(['items', 'payment'])
            ->where('customer_id', $customer->id)
            ->findOrFail($id);

        // Chỉ cho phép hủy các trạng thái phù hợp
        if (!in_array($order->status, ['pending'])) {
            return back()->with('error', 'Không thể huỷ đơn này');
        }

        DB::beginTransaction();

        try {

            $isPaidOnline = $order->payment
                && $order->payment->status == 'paid'
                && in_array(strtolower($order->payment->method), ['momo', 'vnpay']);

            // Nếu chưa thanh toán hoặc thất bại → hủy ngay và restore stock
            if (!$isPaidOnline) {
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
                            \App\Models\Inventory::where('product_variant_id', $importItem->product_variant_id)
                                ->increment('quantity', $batch['quantity']);
                        }
                    }
                }

                $order->update(['status' => 'cancelled']);
            }
            // Nếu đã thanh toán online → chuyển sang refund_requested, không restore stock
            else {
                $order->update([
                    'previous_status' => $order->status,
                    'status' => 'refund_requested'
                ]);
            }

            // Tạo record huỷ đơn
            OrderCancellation::create([
                'order_id' => $order->id,
                'cancelled_by' => 'customer',
                'reason' => $request->reason,
                'cancelled_at' => now()
            ]);

            DB::commit();

            return redirect()
                ->route('orders.my')
                ->with('success', $isPaidOnline
                    ? 'Đơn hàng đã được hủy, chờ xử lý hoàn tiền'
                    : 'Đã huỷ đơn hàng');
        } catch (\Exception $e) {

            DB::rollback();

            return back()->with('error', $e->getMessage());
        }
    }

    public function success($id)
    {
        $customer = Auth::user()->customer;

        $order = Order::where('customer_id', $customer->id)
            ->findOrFail($id);

        return redirect()->route('pages.home')
            ->with('order_success', $order->id);
    }

    public function confirmReceived($id)
    {
        $customer = Auth::user()->customer;

        $order = Order::where('customer_id', $customer->id)
            ->findOrFail($id);

        if ($order->status == 'shipping') {

            if ($order->payment && strtolower($order->payment->method) == 'cod' && $order->payment->status == 'pending') {
                $order->payment->update([
                    'status' => 'paid',
                    'paid_at' => now()
                ]);
            }

            $order->update([
                'status' => 'completed',      
                'received_at' => now(),       
                'previous_status' => null
            ]);
        }

        return back()->with('success', 'Cảm ơn bạn đã xác nhận nhận hàng!');
    }

    
    public function requestRefund($id)
    {
        $customer = Auth::user()->customer;

        $order = Order::where('customer_id', $customer->id)
            ->findOrFail($id);

        if ($order->status != 'completed') {
            return back()->with('error', 'Chỉ hoàn khi đơn hoàn thành');
        }

        $order->update([
            'previous_status' => 'completed',
            'status' => 'refund_requested'
        ]);

        return back()->with('success', 'Đã gửi yêu cầu hoàn tiền');
    }
}
