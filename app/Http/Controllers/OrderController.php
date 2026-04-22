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
use App\Services\OrderEmailService;

class OrderController extends Controller
{
    public function __construct(private OrderEmailService $orderEmailService) {}

    public function myOrders(Request $request)
    {
        $customer = Auth::user()->customer;
        $reviewFilter = $request->get('review');

        $query = Order::with([
            'items.variant.images',
            'items.variant.product.images',
        ])
            ->where('customer_id', $customer->user_id)
            ->latest();

        $reviewedProductIds = Review::query()
            ->where('customer_id', $customer->user_id)
            ->whereIn('status', ['pending', 'approved'])
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
            'payment',
            'customer'
        ])
            ->where('customer_id', $customer->user_id)
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
            ->where('customer_id', $customer->user_id)
            ->findOrFail($id);
        $oldStatus = (string) $order->status;

        if (!in_array($order->status, ['pending'])) {
            return back()->with('error', 'Không thể huỷ đơn này');
        }

        DB::beginTransaction();

        try {

            $isPaidOnline = $order->payment
                && $order->payment->status == 'paid'
                && in_array(strtolower($order->payment->method), ['momo', 'vnpay']);

            if (!$isPaidOnline) {
                foreach ($order->items as $item) {
                    $batchDetails = is_string($item->batch_details)
                        ? json_decode($item->batch_details, true) ?? []
                        : $item->batch_details;

                    if (!$batchDetails) continue;

                    // Hỗ trợ dữ liệu `batch_details` ở cả định dạng mới và cũ:
                    // - Mới: mảng các phần tử `{ batch_id: ImportItem.id, quantity, ... }`
                    // - Cũ: object đơn `{ import_id: Import.id, ... }`

                    $batches = [];
                    if (is_array($batchDetails)) {
                        if (isset($batchDetails[0]) && is_array($batchDetails[0])) {
                            $batches = $batchDetails;
                        } elseif (isset($batchDetails['batch_id']) || isset($batchDetails['import_id'])) {
                            $batches = [$batchDetails];
                        }
                    }

                    foreach ($batches as $batch) {
                        // Hỗ trợ cả `batch_id` (mới) và `import_id` (cũ)
                        $batchId = $batch['batch_id'] ?? null;
                        $importId = $batch['import_id'] ?? null;
                        $quantity = $batch['quantity'] ?? $item->quantity ?? null;

                        if (!$quantity) continue;

                        $importItem = null;

                        if ($batchId) {
                            $importItem = ImportItem::find($batchId);
                        } elseif ($importId) {
                            $importItem = ImportItem::where('import_id', $importId)
                                ->where('product_variant_id', $item->product_variant_id)
                                ->first();
                        }

                        if ($importItem) {
                            $importItem->increment('remaining_quantity', $quantity);
                            Inventory::where('product_variant_id', $importItem->product_variant_id)
                                ->increment('quantity', $quantity);
                        }
                    }
                }

                $order->update(['status' => 'cancelled']);

                Notification::firstOrCreate(
                    [
                        'type' => 'order_cancel',
                        'related_id' => $order->id,
                        'user_id' => $order->customer->user_id
                    ],
                    [
                        'title' => 'Đơn hàng bị hủy',
                        'content' => 'Đơn #' . $order->id . ' đã bị bạn hủy',
                        'is_read' => false,
                    ]
                );
            } else {
                $order->update([
                    'previous_status' => $order->status,
                    'status' => 'refund_requested'
                ]);

                Notification::firstOrCreate(
                    [
                        'type' => 'refund_request',
                        'related_id' => $order->id,
                        'user_id' => $order->customer->user_id,
                    ],
                    [
                        'title' => 'Yêu cầu hoàn hàng',
                        'content' => 'Đơn #' . $order->id . ' yêu cầu hoàn hàng',
                        'is_read' => false,
                    ]
                );

                $recipientIds = Notification::recipientIdsForGroups(['admin', 'order_staff', 'warehouse', 'cashier']);
                Notification::createForRecipients($recipientIds, [
                    'type' => 'refund_request',
                    'title' => 'Có yêu cầu hoàn hàng mới',
                    'content' => 'Đơn #' . $order->id . ' đã hủy thanh toán online và cần xử lý hoàn hàng.',
                    'related_id' => $order->id,
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

            $this->orderEmailService->sendOrderStatusChangedEmail(
                (int) $order->id,
                $oldStatus,
                (string) $order->status
            );

            return redirect()
                ->route('orders.my')
                ->with('success', $isPaidOnline
                    ? 'Đơn hàng đã được hủy, chờ xử lý hoàn hàng'
                    : 'Đã huỷ đơn hàng');
        } catch (\Exception $e) {

            DB::rollback();

            return back()->with('error', $e->getMessage());
        }
    }

    public function success($id)
    {
        try {
            $customer = Auth::user()->customer;

            $order = Order::where('customer_id', $customer->user_id)->findOrFail($id);

            Notification::firstOrCreate(
                [
                    'type' => 'order_success',
                    'related_id' => $order->id,
                    'user_id' => $order->customer->user_id
                ],
                [
                    'title' => 'Đặt hàng thành công',
                    'content' => 'Đơn #' . $order->id . ' đã được tạo',
                    'is_read' => false,
                ]
            );

            $newOrderRecipients = Notification::recipientIdsForGroups(['admin', 'order_staff']);
            Notification::createForRecipients($newOrderRecipients, [
                'type' => 'new_order',
                'title' => 'Có đơn hàng mới',
                'content' => 'Đơn #' . $order->id . ' vừa được tạo',
                'related_id' => $order->id,
            ]);

            $cashierRecipients = Notification::recipientIdsForGroups(['admin', 'cashier']);
            Notification::createForRecipients($cashierRecipients, [
                'type' => 'cashier_stats_update',
                'title' => 'Cập nhật dữ liệu thống kê',
                'content' => 'Đơn #' . $order->id . ' vừa phát sinh và đã cập nhật số liệu bán hàng.',
                'related_id' => $order->id,
            ]);

            return redirect()->route('orders.detail', $order->id)
                ->with('order_success', $order->id)
                ->with('success', 'Đặt hàng thành công');
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function confirmReceived($id)
    {
        $customer = Auth::user()->customer;

        $order = Order::where('customer_id', $customer->user_id)
            ->findOrFail($id);
        $oldStatus = (string) $order->status;

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
            Notification::firstOrCreate(
                [
                    'type' => 'order_completed',
                    'related_id' => $order->id,
                    'user_id' => $order->customer->user_id
                ],
                [
                    'title' => 'Đơn hoàn thành',
                    'content' => 'Đơn #' . $order->id . ' đã được nhận',
                    'is_read' => false,
                ]
            );

            $admins = User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                Notification::firstOrCreate(
                    [
                        'type' => 'order_completed',
                        'related_id' => $order->id,
                        'user_id' => $admin->id
                    ],
                    [
                        'title' => 'Khách đã nhận hàng',
                        'content' => 'Khách đã xác nhận nhận đơn #' . $order->id,
                        'is_read' => false,
                    ]
                );
            }

            $this->orderEmailService->sendOrderStatusChangedEmail(
                (int) $order->id,
                $oldStatus,
                (string) $order->status
            );
        }

        return back()->with('success', 'Cảm ơn bạn đã xác nhận nhận hàng!');
    }

    public function requestRefund(Request $request, $id)
    {
        $customer = Auth::user()->customer;

        $order = Order::where('customer_id', $customer->user_id)
            ->findOrFail($id);
        $oldStatus = (string) $order->status;

        $request->validate([
            // Keep allowed return reason codes in-sync with frontend modal and model mappings
            'reason' => 'required|string|in:wrong_product,product_defect,other,defective,changed_mind,change_mind',
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

            Notification::firstOrCreate(
                [
                    'type' => 'refund_request',
                    'related_id' => $order->id,
                    'user_id' => $order->customer->user_id
                ],
                [
                    'title' => 'Yêu cầu hoàn hàng',
                    'content' => 'Đơn #' . $order->id . ' yêu cầu hoàn hàng',
                    'is_read' => false,
                ]
            );

            $admins = User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                Notification::firstOrCreate(
                    [
                        'type' => 'refund_request',
                        'related_id' => $order->id,
                        'user_id' => $admin->id
                    ],
                    [
                        'title' => 'Có yêu cầu hoàn trả hàng',
                        'content' => 'Khách gửi yêu cầu hoàn hàng cho đơn #' . $order->id,
                        'is_read' => false,
                    ]
                );
            }

            DB::commit();

            $this->orderEmailService->sendOrderStatusChangedEmail(
                (int) $order->id,
                $oldStatus,
                (string) $order->status
            );

            return back()->with('success', 'Đã gửi yêu cầu hoàn hàng');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }

    // Khách xác nhận đã gửi hàng hoàn cho shipper.
    public function markReturnGivenToShipper(Request $request, $returnId)
    {
        $return = \App\Models\OrderReturn::findOrFail($returnId);
        $order = $return->order;

        // Verify this is the customer's order
        if ($order->customer_id !== Auth::user()->customer->user_id) {
            return back()->with('error', 'Không có quyền truy cập');
        }

        if ($return->status !== 'approved') {
            return back()->with('error', 'Trạng thái không hợp lệ');
        }

        try {
            $return->update([
                'status' => 'given_to_shipper'
            ]);

            $recipientIds = Notification::recipientIdsForGroups(['admin', 'warehouse', 'order_staff']);
            Notification::createForRecipients($recipientIds, [
                'type' => 'return_shipped',
                'title' => 'Khách hàng đã gửi hàng hoàn',
                'content' => 'Đơn #' . $order->id . ' - Khách hàng đã giao hàng cho shipper, chờ nhận hàng.',
                'related_id' => $order->id,
            ]);

            return back()->with('success', 'Đã xác nhận, chúng tôi sẽ theo dõi đơn hoàn của bạn');
        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi: ' . $e->getMessage());
        }
    }
}
