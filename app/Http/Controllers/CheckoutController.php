<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Inventory;
use App\Models\Payment;
use App\Models\ImportItem;
use App\Models\Discount;
use App\Models\DiscountUsage;
use App\Models\ProductVariant;
use App\Services\OrderEmailService;
use Illuminate\Support\Collection;

class CheckoutController extends Controller
{
    public function __construct(private OrderEmailService $orderEmailService) {}

    public function index()
    {
        $cart = session()->get('cart', []);
        $completedOrdersCount = $this->getCompletedOrdersCount();

        if (empty($cart)) {
            return redirect()->route('cart.list')
                ->with('error', 'Giỏ hàng đang trống');
        }

        $total = 0;
        foreach ($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        $shippingFee = $total >= 300000 ? 0 : 20000;

        $discountAmount = 0;
        $discountCode = null;
        $discountType = 'fixed';
        $discountValue = 0;

        if (!empty(session('cart_discount_code'))) {
            $discount = Discount::with('products:id,name')
                ->where('code', session('cart_discount_code'))
                ->first();

            if (
                $discount
                && $discount->isActive()
                && $discount->isEligibleForCompletedOrdersCount($completedOrdersCount)
            ) {
                $cartItems = $this->normalizeCartItems($cart);
                $discountAmount = $this->calculateDiscountAmount($discount, $cartItems);

                $discountCode = $discount->code;
                $discountType = $discount->type;
                $discountValue = (float) $discount->value;
            } else {
                session()->forget([
                    'cart_discount',
                    'cart_discount_type',
                    'cart_discount_code',
                    'cart_discount_id',
                    'cart_discount_amount'
                ]);
            }
        }

        $totalPayment = $total + $shippingFee - $discountAmount;
        $orderPolicyRules = $this->getOrderPolicyRules();

        return view('pages.checkout', compact(
            'cart',
            'total',
            'shippingFee',
            'totalPayment',
            'discountAmount',
            'discountCode',
            'discountType',
            'discountValue',
            'orderPolicyRules'
        ));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $customer = $user->customer;

        if (!$customer) {
            return redirect()->route('customer.profile')
                ->with('error', 'Vui lòng cập nhật thông tin khách hàng.');
        }

        $request->validate([
            'receiver_name'      => 'required|string|max:255',
            'receiver_phone'     => 'required|string|max:20',
            'shipping_address'   => 'required|string',
            'note'               => 'nullable|string|max:500',
            'payment_method'     => 'required|in:COD,VNPAY,MOMO',
            'order_policy_agree' => 'in:1',
        ], [
            'order_policy_agree.in' => 'Bạn cần đồng ý chính sách đặt hàng và hoàn tiền để tiếp tục.',
        ]);

        $cart = session()->get('cart', []);
        $completedOrdersCount = $this->getCompletedOrdersCount();

        if (empty($cart)) {
            return redirect()->route('cart.list')
                ->with('error', 'Giỏ hàng đang trống');
        }

        foreach ($cart as $item) {
            if ($item['quantity'] > 10) {
                return redirect()->route('cart.list')
                    ->with('error', 'Số lượng đặt hàng tối đa là 10 sản phẩm/loại. Vui lòng liên hệ cửa hàng cho đơn hàng lớn hơn.');
            }
        }

        DB::beginTransaction();

        try {
            $totalAmount = 0;

            foreach ($cart as $item) {

                $inventory = Inventory::where(
                    'product_variant_id',
                    $item['variant_id']
                )->lockForUpdate()->first();

                if (!$inventory || $inventory->quantity < $item['quantity']) {
                    throw new \Exception('Sản phẩm không đủ số lượng trong kho');
                }

                $batchStock = ImportItem::where('product_variant_id', $item['variant_id'])
                    ->where('remaining_quantity', '>', 0)
                    ->sum('remaining_quantity');

                if ($batchStock < $item['quantity']) {
                    // Tự khôi phục dữ liệu lô bị lệch (còn quantity nhưng remaining_quantity = 0).
                    $brokenBatches = ImportItem::where('product_variant_id', $item['variant_id'])
                        ->where('remaining_quantity', 0)
                        ->where('quantity', '>', 0)
                        ->get();

                    $repaired = 0;
                    foreach ($brokenBatches as $batch) {
                        /** @var ImportItem $batch */
                        $batch->update(['remaining_quantity' => $batch->quantity]);
                        $repaired += $batch->quantity;
                    }

                    $batchStock += $repaired;

                    if ($batchStock < $item['quantity']) {
                        throw new \Exception("Sản phẩm không đủ batch để cấp phát. Hệ thống lỗi, vui lòng thử lại hoặc liên hệ hỗ trợ.");
                    }
                }

                $totalAmount += $item['price'] * $item['quantity'];
            }

            $shippingFee = $totalAmount >= 300000 ? 0 : 20000;

            $discountAmount = 0;
            $discountCode   = null;
            $discountId     = null;

            if (!empty(session('cart_discount_code'))) {

                $discount = Discount::with('products:id,name')->where('code', session('cart_discount_code'))->first();

                if (!$discount || !$discount->isActive()) {
                    throw new \Exception('Mã giảm giá không hợp lệ hoặc đã hết hạn');
                }

                if (!$discount->isEligibleForCompletedOrdersCount($completedOrdersCount)) {
                    throw new \Exception($discount->audience_restriction_message ?? 'Mã giảm giá này không áp dụng cho tài khoản của bạn.');
                }

                $used = DiscountUsage::where('discount_id', $discount->id)
                    ->where('user_id', Auth::id())
                    ->exists();

                if ($used) {
                    throw new \Exception('Bạn đã sử dụng mã giảm giá này rồi');
                }

                $discountId   = $discount->id;
                $discountCode = $discount->code;

                $cartItems = $this->normalizeCartItems($cart);
                $discountAmount = $this->calculateDiscountAmount($discount, $cartItems);

                if ($discountAmount <= 0) {
                    throw new \Exception('Mã giảm giá không áp dụng cho sản phẩm trong giỏ hàng.');
                }
            }

            $totalPayment = $totalAmount + $shippingFee - $discountAmount;

            $order = Order::create([
                'customer_id'      => $customer->user_id,
                'receiver_name'    => $request->receiver_name,
                'receiver_phone'   => $request->receiver_phone,
                'shipping_address' => $request->shipping_address,
                'note'             => $request->note,
                'total_amount'     => $totalPayment,
                'shipping_fee'     => $shippingFee,
                'discount_amount'  => $discountAmount,
                'discount_code'    => $discountCode,
                'status'           => 'pending',
            ]);

            if (!empty($discountId)) {
                DiscountUsage::create([
                    'discount_id' => $discountId,
                    'user_id'     => Auth::id(),
                    'order_id'    => $order->id,
                ]);
                $discount->incrementUsed();
            }

            foreach ($cart as $item) {

                $result = $this->deductStockFIFO(
                    $item['variant_id'],
                    $item['quantity']
                );

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_variant_id' => $item['variant_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['price'] * $item['quantity'],
                    'cost_price' => $result['total_cost'],
                    'batch_details' => $result['batches'],
                ]);

                Inventory::where(
                    'product_variant_id',
                    $item['variant_id']
                )->decrement('quantity', $item['quantity']);
            }

            Payment::create([
                'order_id' => $order->id,
                'method'   => $request->payment_method,
                'amount'   => $totalPayment,
                'status'   => 'pending'
            ]);

            DB::commit();

            try {
                $this->orderEmailService->sendOrderCreatedEmail((int) $order->id);
            } catch (\Exception $emailError) {
                //
            }

            session()->forget([
                'cart',
                'cart_discount',
                'cart_discount_type',
                'cart_discount_code',
                'cart_discount_id'
            ]);

            if ($request->payment_method === 'VNPAY') {
                if (!env('VNP_TMN_CODE') || !env('VNP_HASH_SECRET')) {
                    return redirect()->route('orders.detail', $order->id)
                        ->with('order_success', $order->id)
                        ->with('warning', 'Cấu hình VNPAY không khả dụng, đơn hàng đã được tạo. Vui lòng thanh toán sau hoặc liên hệ hỗ trợ.');
                }
                return redirect()->route('vnpay.payment', $order->id);
            }

            if ($request->payment_method === 'MOMO') {
                return redirect()->route('momo.payment', $order->id);
            }

            return redirect()->route('orders.detail', $order->id)
                ->with('order_success', $order->id)
                ->with('success', 'Đặt hàng thành công');
        } catch (\Exception $e) {

            DB::rollBack();

            return back()->with('error', $e->getMessage());
        }
    }

    private function deductStockFIFO($variantId, $quantity)
    {
        $brokenBatches = ImportItem::where('product_variant_id', $variantId)
            ->where('remaining_quantity', 0)
            ->where('quantity', '>', 0)
            ->lockForUpdate()
            ->get();

        foreach ($brokenBatches as $batch) {
            /** @var ImportItem $batch */
            $batch->remaining_quantity = $batch->quantity;
            $batch->save();
        }

        $batches = ImportItem::where('product_variant_id', $variantId)
            ->where('remaining_quantity', '>', 0)
            ->orderBy('id', 'asc')
            ->lockForUpdate()
            ->get();

        $totalAvailable = $batches->sum('remaining_quantity');
        if ($totalAvailable < $quantity) {
            throw new \Exception("Sản phẩm không đủ số lượng. Còn lại: {$totalAvailable}, yêu cầu: {$quantity}");
        }

        $remaining = $quantity;
        $totalCost = 0;
        $batchDetails = [];

        foreach ($batches as $batch) {
            /** @var ImportItem $batch */

            if ($remaining <= 0) break;

            $deduct = min($batch->remaining_quantity, $remaining);

            $totalCost += $deduct * $batch->unit_price;

            $batchDetails[] = [
                'batch_id'   => $batch->id,
                'quantity'   => $deduct,
                'unit_price' => $batch->unit_price
            ];

            $batch->remaining_quantity -= $deduct;
            $batch->save();

            $remaining -= $deduct;
        }

        if ($remaining > 0) {
            throw new \Exception('Lỗi: Không thể trừ đủ hàng từ batch (hệ thống lỗi, vui lòng thử lại)');
        }

        return [
            'total_cost' => $totalCost,
            'batches'    => $batchDetails
        ];
    }

    public function success($id)
    {
        $user = Auth::user();
        $customer = $user->customer;

        $order = Order::where('customer_id', $customer->user_id)
            ->findOrFail($id);

        return redirect()->route('pages.home')
            ->with('order_success', $order->id);
    }

    private function normalizeCartItems(array $cart): Collection
    {
        $items = collect($cart)->map(function ($item, $variantId) {
            $item['variant_id'] = (int) ($item['variant_id'] ?? $variantId);
            return $item;
        })->values();

        $missingProductVariantIds = $items
            ->filter(fn($item) => empty($item['product_id']))
            ->pluck('variant_id')
            ->unique()
            ->values();

        if ($missingProductVariantIds->isNotEmpty()) {
            $variantProductMap = ProductVariant::query()
                ->whereIn('id', $missingProductVariantIds)
                ->pluck('product_id', 'id');

            $items = $items->map(function ($item) use ($variantProductMap) {
                if (empty($item['product_id'])) {
                    $item['product_id'] = (int) ($variantProductMap[$item['variant_id']] ?? 0);
                }
                return $item;
            });
        }

        return $items;
    }

    private function calculateDiscountAmount(Discount $discount, Collection $cartItems): float
    {
        $eligibleSubtotal = $discount->getEligibleSubtotal($cartItems);

        if ($eligibleSubtotal <= 0) {
            return 0;
        }

        if ($discount->min_order_value && $eligibleSubtotal < (float) $discount->min_order_value) {
            return 0;
        }

        return $discount->getDiscountAmount($eligibleSubtotal);
    }

    private function getOrderPolicyRules(): array
    {
        return [
            'Đơn hàng sau khi đặt sẽ ở trạng thái chờ xử lý.',
            'Khách chỉ có thể tự hủy đơn khi đơn đang ở trạng thái chờ xử lý.',
            'Với đơn đã thanh toán online (MoMo/VNPAY), khi hủy sẽ chuyển sang chờ xử lý hoàn hàng.',
            'Yêu cầu hoàn hàng chỉ áp dụng cho đơn đã hoàn thành.',
            'Yêu cầu hoàn hàng sẽ được admin duyệt và kiểm tra hàng; khi hoàn tất hoàn tiền, đơn chuyển sang đã hoàn tiền.',
            'Nếu yêu cầu hoàn hàng bị từ chối, đơn sẽ quay lại trạng thái trước đó.',
        ];
    }

    private function getCompletedOrdersCount(): int
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        return $user->orders()
            ->where('status', 'completed')
            ->count();
    }
}
