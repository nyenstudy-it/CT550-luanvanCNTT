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

class CheckoutController extends Controller
{
    public function index()
    {
        $cart = session()->get('cart', []);

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

        if (!empty(session('cart_discount_code'))) {

            $discountValue = session('cart_discount', 0);
            $discountType  = session('cart_discount_type', 'fixed');

            if ($discountType == 'percent') {
                $discountAmount = $total * $discountValue / 100;
            } else {
                $discountAmount = $discountValue;
            }

            $discountAmount = min($discountAmount, $total);
        }

        $totalPayment = $total + $shippingFee - $discountAmount;

        return view('pages.checkout', compact(
            'cart',
            'total',
            'shippingFee',
            'totalPayment',
            'discountAmount'
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

        if (!$customer->province || !$customer->district || !$customer->ward) {
            return redirect()->route('customer.profile')
                ->with('error', 'Vui lòng cập nhật địa chỉ trước khi đặt hàng.');
        }

        $request->validate([
            'receiver_name'    => 'required|string|max:255',
            'receiver_phone'   => 'required|string|max:20',
            'shipping_address' => 'required|string',
            'payment_method'   => 'required|in:COD,VNPAY,MOMO'
        ]);

        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart.list')
                ->with('error', 'Giỏ hàng đang trống');
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

                $totalAmount += $item['price'] * $item['quantity'];
            }

            // ===== SHIPPING =====
            $shippingFee = $totalAmount >= 300000 ? 0 : 20000;

            // ===== DISCOUNT =====
            $discountAmount = 0;
            $discountCode   = null;
            $discountId     = null;

            if (!empty(session('cart_discount_code'))) {

                $discount = Discount::where('code', session('cart_discount_code'))->first();

                if (!$discount || !$discount->isActive()) {
                    throw new \Exception('Mã giảm giá không hợp lệ hoặc đã hết hạn');
                }

                $used = DiscountUsage::where('discount_id', $discount->id)
                    ->where('user_id', Auth::id())
                    ->exists();

                if ($used) {
                    throw new \Exception('Bạn đã sử dụng mã giảm giá này rồi');
                }

                $discountId   = $discount->id;
                $discountCode = $discount->code;

                if ($discount->type == 'percent') {
                    $discountAmount = $totalAmount * $discount->value / 100;
                } else {
                    $discountAmount = $discount->value;
                }

                $discountAmount = min($discountAmount, $totalAmount);
            }

            $totalPayment = $totalAmount + $shippingFee - $discountAmount;

            $order = Order::create([
                'customer_id'      => $customer->id,
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

            session()->forget([
                'cart',
                'cart_discount',
                'cart_discount_type',
                'cart_discount_code',
                'cart_discount_id'
            ]);

            if ($request->payment_method === 'VNPAY') {
                return redirect()->route('vnpay.payment', $order->id);
            }

            if ($request->payment_method === 'MOMO') {
                return redirect()->route('momo.payment', $order->id);
            }

            return redirect()->route('orders.success', $order->id)
                ->with('success', 'Đặt hàng thành công');
        } catch (\Exception $e) {

            DB::rollBack();

            return back()->with('error', $e->getMessage());
        }
    }

    private function deductStockFIFO($variantId, $quantity)
    {
        $batches = ImportItem::where('product_variant_id', $variantId)
            ->where('remaining_quantity', '>', 0)
            ->orderBy('created_at', 'asc')
            ->lockForUpdate()
            ->get();

        $remaining = $quantity;
        $totalCost = 0;
        $batchDetails = [];

        foreach ($batches as $batch) {

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
            throw new \Exception('Không đủ hàng theo batch');
        }

        return [
            'total_cost' => $totalCost,
            'batches'    => $batchDetails
        ];
    }

    public function success($id)
    {
        $customer = Auth::user()->customer;

        $order = Order::where('customer_id', $customer->id)
            ->findOrFail($id);

        return redirect()->route('pages.home')
            ->with('order_success', $order->id);
    }
}
