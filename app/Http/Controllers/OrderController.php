<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Order;
use App\Models\Inventory;
use App\Models\ImportItem;
use App\Models\Payment;
use App\Models\OrderCancellation;
use App\Models\Notification;
use App\Models\Review;
use App\Models\User;

class OrderController extends Controller
{
    public function myOrders(Request $request)
    {
        $customer = Auth::user()->customer;
        $reviewFilter = $request->get('review');

        $query = Order::with([
            'items.variant.images',
            'items.variant.product.images',
        ])
            ->where('customer_id', $customer->id)
            ->latest();

        $reviewedProductIds = Review::query()
            ->where('customer_id', $customer->id)
            ->pluck('product_id')
            ->unique()
            ->values()
            ->all();

        $reviewedLookup = array_flip($reviewedProductIds);

        if ($reviewFilter === 'unreviewed') {
            $completedOrders = (clone $query)
                ->where('status', 'completed')
                ->get()
                ->filter(function ($order) use ($reviewedLookup) {
                    $productIdsInOrder = $order->items
                        ->pluck('variant.product_id')
                        ->filter()
                        ->unique();

                    return $productIdsInOrder->contains(fn($productId) => !isset($reviewedLookup[$productId]));
                })
                ->values();

            $perPage = 5;
            $currentPage = LengthAwarePaginator::resolveCurrentPage();
            $itemsForCurrentPage = $completedOrders
                ->slice(($currentPage - 1) * $perPage, $perPage)
                ->values();

            $orders = new LengthAwarePaginator(
                $itemsForCurrentPage,
                $completedOrders->count(),
                $perPage,
                $currentPage,
                [
                    'path' => $request->url(),
                    'query' => $request->query(),
                ]
            );
        } else {
            if ($request->status) {
                $query->where('status', $request->status);
            }

            $orders = $query->paginate(5)->withQueryString();
        }

        return view('pages.my-orders', compact('orders', 'reviewedProductIds', 'reviewFilter'));
    }

    public function orderDetail($id)
    {
        $customer = Auth::user()->customer;

        $order = Order::with([
            'items.variant.images',
            'items.variant.product.images',
            'cancellation',
            'returns.images',
            'payment'
        ])
            ->where('customer_id', $customer->id)
            ->findOrFail($id);

        $orderHistoryNotifications = Notification::where('user_id', $customer->user_id)
            ->where('related_id', $order->id)
            ->whereIn('type', [
                'order_status_change',
                'order_cancel',
                'order_completed',
                'refund_request',
                'order_refund',
                'refund_rejected',
            ])
            ->orderBy('created_at', 'asc')
            ->get();

        return view('pages.order-detail', compact('order', 'orderHistoryNotifications'));
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

        if (!in_array($order->status, ['pending'])) {
            return back()->with('error', 'Không thể huỷ đơn này');
        }

        DB::beginTransaction();

        try {

            $isPaidOnline = $order->payment
                && $order->payment->status == 'paid'
                && in_array(strtolower($order->payment->method), ['momo', 'vnpay']);

            if (!$isPaidOnline) {
                // Trả lại số lượng tồn kho theo batch
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
                            Inventory::where('product_variant_id', $importItem->product_variant_id)
                                ->increment('quantity', $batch['quantity']);
                        }
                    }
                }

                $order->update(['status' => 'cancelled']);
                $exists = Notification::where('type', 'order_cancel')
                    ->where('related_id', $order->id)
                    ->where('user_id', $order->customer->user_id)
                    ->exists();

                if (!$exists) {
                    Notification::create([
                        'user_id' => $order->customer->user_id,
                        'type' => 'order_cancel',
                        'title' => 'Đơn hàng bị hủy',
                        'content' => 'Đơn #' . $order->id . ' đã bị bạn hủy',
                        'related_id' => $order->id,
                        'is_read' => false,
                    ]);
                }
            } else {
                $order->update([
                    'previous_status' => $order->status,
                    'status' => 'refund_requested'
                ]);

                Notification::create([
                    'user_id' => Auth::id(),
                    'type' => 'refund_request',
                    'title' => 'Yêu cầu hoàn tiền',
                    'content' => 'Đơn #' . $order->id . ' yêu cầu hoàn tiền',
                    'related_id' => $order->id,
                    'is_read' => false,

                ]);
            }

            // Lưu lý do hủy
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
        $order = Order::where('customer_id', $customer->id)->findOrFail($id);
        $exists = Notification::where('type', 'order_success')
            ->where('related_id', $order->id)
            ->where('user_id', $order->customer->user_id)
            ->exists();

        if (!$exists) {
            Notification::create([
                'user_id' => $order->customer->user_id,
                'type' => 'order_success',
                'title' => 'Đặt hàng thành công',
                'content' => 'Đơn #' . $order->id . ' đã được tạo',
                'related_id' => $order->id,
                'is_read' => false,

            ]);
        }

        // --- Notification admin ---
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {

            $exists = Notification::where('type', 'new_order')
                ->where('related_id', $order->id)
                ->where('user_id', $admin->id)
                ->exists();

            if (!$exists) {
                Notification::create([
                    'user_id' => $admin->id,
                    'type' => 'new_order',
                    'title' => 'Có đơn hàng mới',
                    'content' => 'Đơn #' . $order->id . ' vừa được tạo',
                    'related_id' => $order->id,
                    'is_read' => false,

                ]);
            }
        }


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
            $exists = Notification::where('type', 'order_completed')
                ->where('related_id', $order->id)
                ->where('user_id', $order->customer->user_id)
                ->exists();

            if (!$exists) {
                Notification::create([
                    'user_id' => $order->customer->user_id,
                    'type' => 'order_completed',
                    'title' => 'Đơn hoàn thành',
                    'content' => 'Đơn #' . $order->id . ' đã được nhận',
                    'related_id' => $order->id,
                    'is_read' => false,

                ]);
            }

            $admins = User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                $existsAdmin = Notification::where('type', 'order_completed')
                    ->where('related_id', $order->id)
                    ->where('user_id', $admin->id)
                    ->exists();

                if (!$existsAdmin) {
                    Notification::create([
                        'user_id' => $admin->id,
                        'type' => 'order_completed',
                        'title' => 'Khách đã nhận hàng',
                        'content' => 'Khách đã xác nhận nhận đơn #' . $order->id,
                        'related_id' => $order->id,
                        'is_read' => false,
                    ]);
                }
            }
        }

        return back()->with('success', 'Cảm ơn bạn đã xác nhận nhận hàng!');
    }

    public function requestRefund(Request $request, $id)
    {
        $customer = Auth::user()->customer;

        $order = Order::where('customer_id', $customer->id)
            ->findOrFail($id);

        $request->validate([
            'reason' => 'required|string|max:255',
            'description' => 'nullable|string',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($order->status != 'completed') {
            return back()->with('error', 'Chỉ hoàn khi đơn hoàn thành');
        }

        DB::beginTransaction();
        try {
            $return = \App\Models\OrderReturn::create([
                'order_id' => $order->id,
                'reason' => $request->reason,
                'description' => $request->description,
            ]);

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $img) {
                    $path = $img->store('returns', 'public'); // lưu trong storage/app/public/returns
                    $return->images()->create([
                        'image_path' => $path
                    ]);
                }
            }

            $order->update([
                'previous_status' => 'completed',
                'status' => 'refund_requested'
            ]);

            $exists = Notification::where('type', 'refund_request')
                ->where('related_id', $order->id)
                ->where('user_id', $order->customer->user_id)
                ->exists();

            if (!$exists) {
                Notification::create([
                    'user_id' => $order->customer->user_id,
                    'type' => 'refund_request',
                    'title' => 'Yêu cầu hoàn tiền',
                    'content' => 'Đơn #' . $order->id . ' yêu cầu hoàn tiền',
                    'related_id' => $order->id,
                    'is_read' => false,
                ]);
            }

            $admins = User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                $existsAdmin = Notification::where('type', 'refund_request')
                    ->where('related_id', $order->id)
                    ->where('user_id', $admin->id)
                    ->exists();

                if (!$existsAdmin) {
                    Notification::create([
                        'user_id' => $admin->id,
                        'type' => 'refund_request',
                        'title' => 'Có yêu cầu hoàn trả hàng',
                        'content' => 'Khách gửi yêu cầu hoàn trả/hoàn tiền cho đơn #' . $order->id,
                        'related_id' => $order->id,
                        'is_read' => false,
                    ]);
                }
            }

            DB::commit();
            return back()->with('success', 'Đã gửi yêu cầu hoàn tiền');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }
}
