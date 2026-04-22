<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderCancellation;
use App\Models\ImportItem;
use App\Models\Inventory;
use App\Models\InventoryWriteoff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use App\Services\OrderEmailService;

class AdminOrderController extends Controller
{
    public function __construct(private OrderEmailService $orderEmailService) {}

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

        // Filter by unified search (order_id hoặc phone)
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('id', $request->search)
                    ->orWhere('receiver_phone', 'like', '%' . addcslashes($request->search, '\\%_') . '%');
            });
        }

        if ($request->order_id) {
            $query->where('id', $request->order_id);
        }
        if ($request->phone) {
            $query->where('receiver_phone', 'like', '%' . addcslashes($request->phone, '\\%_') . '%');
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->payment_method) {
            $query->whereHas('payment', function ($q) use ($request) {
                $q->where('method', $request->payment_method);
            });
        }

        if ($request->date_range) {
            $now = now();
            $from = match ($request->date_range) {
                'today' => $now->startOfDay(),
                '7days' => $now->copy()->subDays(7)->startOfDay(),
                '30days' => $now->copy()->subDays(30)->startOfDay(),
                default => null
            };
            if ($from) {
                $query->whereDate('created_at', '>=', $from);
            }
        }

        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->price_from) {
            $query->where('total_amount', '>=', (int)$request->price_from);
        }
        if ($request->price_to) {
            $query->where('total_amount', '<=', (int)$request->price_to);
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

        $cancelReasonPresets = self::ADMIN_CANCEL_REASON_PRESETS;

        return view('admin.orders.detail', compact('order', 'cancelReasonPresets'));
    }

    // Cập nhật trạng thái đơn hàng
    public function updateStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $request->validate([
            'status' => 'required|in:pending,confirmed,shipping,completed'
        ]);
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

            $this->orderEmailService->sendOrderStatusChangedEmail(
                (int) $order->id,
                $oldStatus,
                (string) $order->status
            );
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
            $oldStatus = (string) $order->status;

            if (in_array($order->status, ['shipping', 'completed', 'cancelled', 'refund_requested', 'refunded'])) {
                DB::rollBack();
                return back()->with('error', 'Không thể hủy đơn ở trạng thái này');
            }

            $isPaidOnline = $order->payment
                && $order->payment->status == 'paid'
                && in_array(strtolower($order->payment->method), ['momo', 'vnpay']);

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

            $this->orderEmailService->sendOrderStatusChangedEmail(
                (int) $order->id,
                $oldStatus,
                (string) $order->status
            );

            return back()->with('success', 'Đã hủy đơn hàng');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function approveRefund($id)
    {
        return back()->with('error', 'Luồng duyệt hoàn cũ đã bị khóa. Vui lòng xử lý theo quy trình kiểm tra hàng trong chi tiết đơn.');
    }

    public function rejectRefund($id)
    {
        return back()->with('error', 'Luồng từ chối cũ đã bị khóa. Vui lòng từ chối yêu cầu ở bước duyệt hoàn hàng (có lý do từ chối).');
    }

    private function restoreStock($order)
    {
        foreach ($order->items as $item) {
            $batchDetails = is_string($item->batch_details)
                ? json_decode($item->batch_details, true) ?? []
                : $item->batch_details;

            if (!$batchDetails) {
                continue;
            }

            $batches = [];
            if (is_array($batchDetails)) {
                if (isset($batchDetails[0]) && is_array($batchDetails[0])) {
                    $batches = $batchDetails;
                } elseif (isset($batchDetails['batch_id']) || isset($batchDetails['import_id'])) {
                    $batches = [$batchDetails];
                }
            }

            foreach ($batches as $idx => $batch) {
                $batchId = $batch['batch_id'] ?? null;
                $importId = $batch['import_id'] ?? null;
                $quantity = $batch['quantity'] ?? $item->quantity ?? null;

                if (!$quantity) {
                    continue;
                }

                $importItem = null;

                if ($batchId) {
                    $importItem = ImportItem::lockForUpdate()->find($batchId);
                } elseif ($importId) {
                    $importItem = ImportItem::where('import_id', $importId)
                        ->where('product_variant_id', $item->product_variant_id)
                        ->lockForUpdate()
                        ->first();
                }

                if ($importItem) {
                    $batchQty = (int) $quantity;
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
            'refund_requested' => 'Yêu cầu hoàn hàng đang được xử lý',
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
    public function approveRefundWithChoice(Request $request, $id)
    {
        $action = $request->input('action'); // 'restore_stock' or 'create_writeoff'

        if (!in_array($action, ['restore_stock', 'create_writeoff'])) {
            return back()->with('error', 'Hành động không hợp lệ');
        }

        $order = Order::with(['items', 'payment', 'returns.images', 'customer'])->findOrFail($id);
        $oldStatus = (string) $order->status;

        DB::beginTransaction();

        try {
            $payment = $order->payment;
            $return = $order->returns->sortByDesc('id')->first();

            if (!$payment || $payment->status != 'paid') {
                throw new \RuntimeException('Không hợp lệ');
            }

            // Order status must be refund_requested or completed (for edge cases)
            if (!in_array($order->status, ['refund_requested', 'completed'])) {
                throw new \RuntimeException('Đơn không ở trạng thái hoàn tiền (status: ' . $order->status . ')');
            }

            if ($payment->refund_status == 'completed') {
                throw new \RuntimeException('Đơn đã hoàn tiền trước đó');
            }

            // Có return request thì bắt buộc đi qua bước kiểm tra hàng trước khi hoàn tiền.
            if ($return) {
                $inspectionError = null;
                if (!$this->validateInspectionComplete($return, $inspectionError)) {
                    throw new \RuntimeException($inspectionError ?? 'Hàng chưa được kiểm tra.');
                }

                if ($return->inspection_result === 'defective' && $action !== 'create_writeoff') {
                    throw new \RuntimeException('Hàng đã xác nhận lỗi, vui lòng chọn hủy hàng (writeoff).');
                }

                if ($return->inspection_result === 'good' && $action !== 'restore_stock') {
                    throw new \RuntimeException('Hàng đã xác nhận đạt, vui lòng chọn cộng kho.');
                }
            } elseif ($action !== 'restore_stock') {
                // Trường hợp hoàn tiền do hủy đơn online chưa giao: không có hàng hoàn trả thực tế.
                throw new \RuntimeException('Không có thông tin hàng trả, chỉ hỗ trợ cộng kho cho trường hợp này.');
            }

            // Khi cộng kho: luôn hoàn lại tồn kho theo dữ liệu lô hàng đã trừ,
            // không phụ thuộc `previous_status` (đơn có thể phát sinh hoàn ở nhiều trạng thái).
            if ($action === 'restore_stock') {
                $this->restoreStock($order);
            }

            if ($action === 'create_writeoff') {
                if (!$return) {
                    throw new \RuntimeException('Không tìm thấy yêu cầu hoàn hàng để tạo writeoff.');
                }

                $this->createWriteoffFromReturn($order, $return);
            }

            // Hoàn tiền cho khách
            $payment->update([
                'refund_amount' => $payment->amount,
                'refund_status' => 'completed',
                'refund_at' => now()
            ]);

            $order->update([
                'status' => 'refunded',
                'previous_status' => $oldStatus
            ]);

            if ($return && $return->status !== 'refunded') {
                $return->update(['status' => 'refunded']);
            }

            // Gửi notification
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

            $actionText = $action === 'restore_stock' ? '[Cộng kho]' : '[Hủy hàng]';
            $this->notifyCashierStats($order, 'Đơn #' . $order->id . ' đã hoàn tiền ' . $actionText . ', số liệu doanh thu đã cập nhật.');

            DB::commit();

            $this->orderEmailService->sendOrderStatusChangedEmail(
                (int) $order->id,
                $oldStatus,
                (string) $order->status
            );

            $statusMsg = $action === 'restore_stock' ? 'cộng kho' : 'hủy hàng';
            return back()->with('success', 'Đã hoàn tiền và ' . $statusMsg);
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Lỗi: ' . $e->getMessage());
        }
    }

    // Tạo writeoff từ yêu cầu hoàn hàng (hàng lỗi).
    private function createWriteoffFromReturn($order, $return)
    {
        $writeoffReason = $this->mapWriteoffReasonFromReturn($return->reason);
        $variantWriteoffAmounts = [];

        // Get import items for products in this order
        foreach ($order->items as $item) {
            // Find corresponding import_item using FIFO (earliest batch by ID)
            $importItem = ImportItem::where('product_variant_id', $item->product_variant_id)
                ->where('remaining_quantity', '>', 0)
                ->orderBy('id', 'asc') // FIFO
                ->first();

            if (!$importItem) continue;

            // Calculate quantity to writeoff (based on order item qty)
            $writeoffQty = min($item->quantity, $importItem->remaining_quantity);

            if (!isset($variantWriteoffAmounts[$item->product_variant_id])) {
                $variantWriteoffAmounts[$item->product_variant_id] = 0;
            }
            $variantWriteoffAmounts[$item->product_variant_id] += $writeoffQty;

            $totalCost = $writeoffQty * $importItem->unit_price;

            $writeoffData = [
                'product_variant_id' => $item->product_variant_id,
                'import_item_id' => $importItem->id,
                'quantity_written_off' => $writeoffQty,
                'unit_cost' => $importItem->unit_price,
                'total_cost' => $totalCost,
                'reason' => $writeoffReason,
                'note' => 'Auto: Order #' . $order->id . ' - ' . $return->reason,
                'written_off_by' => Auth::id(),
            ];
            if (Schema::hasColumns('inventory_writeoffs', ['discovered_by', 'discovered_at'])) {
                $writeoffData['discovered_by'] = Auth::id();
                $writeoffData['discovered_at'] = now();
            }
            InventoryWriteoff::create($writeoffData);

            $importItem->decrement('remaining_quantity', $writeoffQty);
        }

        foreach ($variantWriteoffAmounts as $variantId => $amount) {
            Inventory::where('product_variant_id', $variantId)
                ->decrement('quantity', $amount);
        }
    }
    // API tạo writeoff thủ công.
    public function createWriteoffDirect(Request $request, $orderId)
    {
        $validated = $request->validate([
            'product_variant_id' => 'required|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|in:damaged,expired,other',
            'note' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $importItem = ImportItem::where('product_variant_id', $validated['product_variant_id'])
                ->where('remaining_quantity', '>', 0)
                ->orderBy('id', 'asc')
                ->first();

            if (!$importItem) {
                throw new \RuntimeException('Không tìm thấy hàng trong import');
            }

            if ($validated['quantity'] > $importItem->remaining_quantity) {
                throw new \RuntimeException('Số lượng writeoff vượt quá available quantity');
            }

            $totalCost = $validated['quantity'] * $importItem->unit_price;

            $writeoffData = [
                'product_variant_id' => $validated['product_variant_id'],
                'import_item_id' => $importItem->id,
                'quantity_written_off' => $validated['quantity'],
                'unit_cost' => $importItem->unit_price,
                'total_cost' => $totalCost,
                'reason' => $validated['reason'],
                'note' => $validated['note'] ?? 'Manual: Order #' . $orderId,
                'written_off_by' => Auth::id(),
            ];
            if (Schema::hasColumns('inventory_writeoffs', ['discovered_by', 'discovered_at'])) {
                $writeoffData['discovered_by'] = Auth::id();
                $writeoffData['discovered_at'] = now();
            }
            InventoryWriteoff::create($writeoffData);

            $importItem->decrement('remaining_quantity', $validated['quantity']);

            Inventory::where('product_variant_id', $validated['product_variant_id'])
                ->decrement('quantity', $validated['quantity']);

            DB::commit();

            return back()->with('success', 'Đã tạo inventory writeoff thành công. Hàng và tồn kho đã được cập nhật.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Lỗi: ' . $e->getMessage());
        }
    }

    private function mapWriteoffReasonFromReturn(?string $returnReason): string
    {
        return match ((string) $returnReason) {
            'product_defect', 'defective' => 'damaged',
            'wrong_product' => 'other',
            default => 'other',
        };
    }

    // Quy trình kiểm tra hàng hoàn: xác nhận đã nhận hàng.
    public function markGoodsReceived(Request $request, $returnId)
    {
        $return = \App\Models\OrderReturn::findOrFail($returnId);

        if ($return->status !== 'requested') {
            return back()->with('error', 'Trạng thái đơn hoàn không hợp lệ');
        }

        try {
            $return->update([
                'status' => 'goods_received'
            ]);

            // Notification to customer
            Notification::create([
                'user_id' => $return->order->customer->user_id,
                'type' => 'return_update',
                'title' => 'Hàng hoàn của bạn đã được nhận',
                'content' => 'Kho đã nhận hàng hoàn. Chúng tôi sẽ kiểm tra và xử lý trong 1-2 ngày.',
                'related_id' => $return->order_id,
                'is_read' => false,
            ]);

            return back()->with('success', 'Đã mark hàng đã về, chờ kiểm tra');
        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi: ' . $e->getMessage());
        }
    }

    // Quy trình kiểm tra hàng hoàn: cập nhật kết quả kiểm tra (lỗi/đạt).
    public function markInspected(Request $request, $returnId)
    {
        $request->validate([
            'inspection_result' => 'required|in:defective,good',
            'inspection_notes' => 'nullable|string|max:500'
        ]);

        $return = \App\Models\OrderReturn::findOrFail($returnId);

        if ($return->status !== 'goods_received') {
            return back()->with('error', 'Hàng chưa về kho hoặc đã được kiểm tra');
        }

        try {
            $inspectionResult = $request->input('inspection_result');
            $newStatus = $inspectionResult === 'defective' ? 'inspected_defective' : 'inspected_good';

            $return->update([
                'status' => $newStatus,
                'inspected_by' => Auth::id(),
                'inspected_at' => now(),
                'inspection_notes' => $request->input('inspection_notes'),
                'inspection_result' => $inspectionResult
            ]);

            // Notification to customer
            $notificationMsg = $inspectionResult === 'defective'
                ? 'Hàng của bạn được xác nhận lỗi, chúng tôi sẽ hủy hàng và hoàn tiền ngay lập tức.'
                : 'Hàng của bạn kiểm tra bình thường, sẽ cộng lại kho và hoàn tiền cho bạn.';

            Notification::create([
                'user_id' => $return->order->customer->user_id,
                'type' => 'return_inspection',
                'title' => 'Kết quả kiểm tra hàng hoàn',
                'content' => $notificationMsg,
                'related_id' => $return->order_id,
                'is_read' => false,
            ]);

            $resultText = $inspectionResult === 'defective' ? '✓ LỖI' : '✓ OK';
            return back()->with('success', 'Đã mark kiểm tra: ' . $resultText);
        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi: ' . $e->getMessage());
        }
    }

    // Kiểm tra điều kiện: hàng hoàn đã được kiểm tra trước khi hoàn tiền.
    private function validateInspectionComplete($return, &$errorMsg = null)
    {
        if (!$return) {
            $errorMsg = 'Không tìm thấy yêu cầu hoàn hàng';
            return false;
        }

        if (!in_array($return->status, ['inspected_defective', 'inspected_good'])) {
            $errorMsg = 'Hàng chưa được kiểm tra. Vui lòng hoàn tất kiểm tra trước.';
            return false;
        }

        return true;
    }

    // Quy trình: admin duyệt yêu cầu hoàn hàng.
    public function approveReturnRequest(Request $request, $returnId)
    {
        $return = \App\Models\OrderReturn::findOrFail($returnId);
        $order = $return->order;

        if ($return->status !== 'requested') {
            return back()->with('error', 'Trạng thái yêu cầu không hợp lệ');
        }

        try {
            DB::beginTransaction();

            $return->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now()
            ]);

            // Notify customer: Refund approved, waiting for shipment
            Notification::create([
                'user_id' => $order->customer->user_id,
                'type' => 'return_approved',
                'title' => '✓ Yêu cầu hoàn hàng được duyệt',
                'content' => 'Yêu cầu hoàn hàng của bạn đã được duyệt. Shipper sẽ liên hệ để lấy hàng. Vui lòng chuẩn bị sẵn sàng!',
                'related_id' => $order->id,
                'is_read' => false,
            ]);

            DB::commit();

            return back()->with('success', 'Đã duyệt, khách hàng sẽ nhận thông báo');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Lỗi: ' . $e->getMessage());
        }
    }

    // Quy trình: admin từ chối yêu cầu hoàn hàng.
    public function rejectReturnRequest(Request $request, $returnId)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500'
        ]);

        $return = \App\Models\OrderReturn::findOrFail($returnId);
        $order = $return->order;

        if ($return->status !== 'requested') {
            return back()->with('error', 'Trạng thái yêu cầu không hợp lệ');
        }

        try {
            DB::beginTransaction();

            $return->update([
                'status' => 'rejected',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'rejection_reason' => $request->input('rejection_reason')
            ]);

            // Notify customer: Return rejected
            Notification::create([
                'user_id' => $order->customer->user_id,
                'type' => 'return_rejected',
                'title' => '✗ Yêu cầu hoàn hàng bị từ chối',
                'content' => 'Yêu cầu hoàn hàng của bạn đã bị từ chối. Lý do: ' . $request->input('rejection_reason') . '. Vui lòng liên hệ qua tin nhắn để được hỗ trợ thêm.',
                'related_id' => $order->id,
                'is_read' => false,
            ]);

            DB::commit();

            return back()->with('success', 'Đã từ chối yêu cầu hoàn hàng');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Lỗi: ' . $e->getMessage());
        }
    }

    // Quy trình: kho xác nhận đã nhận hàng hoàn từ shipper.
    public function markGoodsReceivedFromShipper(Request $request, $returnId)
    {
        $return = \App\Models\OrderReturn::findOrFail($returnId);

        if ($return->status !== 'given_to_shipper') {
            return back()->with('error', 'Trạng thái không hợp lệ');
        }

        try {
            $return->update([
                'status' => 'goods_received'
            ]);

            // Notification to customer
            Notification::create([
                'user_id' => $return->order->customer->user_id,
                'type' => 'return_goods_received',
                'title' => 'Hàng hoàn của bạn đã được nhận',
                'content' => 'Kho đã nhận hàng hoàn. Chúng tôi sẽ kiểm tra và xử lý trong 1-2 ngày.',
                'related_id' => $return->order_id,
                'is_read' => false,
            ]);

            return back()->with('success', 'Đã mark hàng về, chờ kiểm tra');
        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi: ' . $e->getMessage());
        }
    }
}
