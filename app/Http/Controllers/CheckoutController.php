<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Inventory;
use App\Models\Payment;
use App\Models\OrderCancellation;

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

        return view('pages.checkout', compact('cart', 'total'));
    }

    public function store(Request $request)
    {
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
                )->first();

                if (!$inventory || $inventory->quantity < $item['quantity']) {
                    return back()->with(
                        'error',
                        'Một số sản phẩm không đủ số lượng trong kho'
                    );
                }

                $totalAmount += $item['price'] * $item['quantity'];
            }

            $order = Order::create([
                'customer_id' => Auth::id(),
                'total_amount' => $totalAmount,
                'status' => 'pending'
            ]);

            foreach ($cart as $item) {

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_variant_id' => $item['variant_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);

                Inventory::where(
                    'product_variant_id',
                    $item['variant_id']
                )->decrement('quantity', $item['quantity']);
            }

            Payment::create([
                'order_id' => $order->id,
                'method' => 'COD',
                'status' => 'pending'
            ]);

            DB::commit();
            session()->forget('cart');

            return redirect()->route('orders.detail', $order->id)
                ->with('success', 'Đặt hàng thành công');
        } catch (\Exception $e) {

            DB::rollBack();

            return back()->with('error', 'Có lỗi xảy ra khi đặt hàng');
        }
    }

    public function myOrders()
    {
        $orders = Order::where('customer_id', Auth::id())
            ->orderByDesc('id')
            ->get();

        return view('pages.my-orders', compact('orders'));
    }

    public function orderDetail($id)
    {
        $order = Order::with('items')
            ->where('customer_id', Auth::id())
            ->findOrFail($id);

        return view('pages.order-detail', compact('order'));
    }

    public function cancel($id)
    {
        $order = Order::where('customer_id', Auth::id())
            ->findOrFail($id);

        if ($order->status !== 'pending') {
            return back()->with('error', 'Không thể huỷ đơn này');
        }

        DB::beginTransaction();

        try {
            $order->update([
                'status' => 'cancelled'
            ]);

            foreach ($order->items as $item) {
                Inventory::where(
                    'product_variant_id',
                    $item->product_variant_id
                )->increment('quantity', $item->quantity);
            }

            OrderCancellation::create([
                'order_id' => $order->id,
                'cancelled_by' => 'customer',
                'reason' => 'Khách hàng huỷ đơn',
                'cancelled_at' => now()
            ]);

            DB::commit();

            return back()->with('success', 'Đã huỷ đơn hàng');
        } catch (\Exception $e) {

            DB::rollBack();

            return back()->with('error', 'Có lỗi xảy ra');
        }
    }
}
