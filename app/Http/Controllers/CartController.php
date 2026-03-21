<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductVariant;
use App\Models\Inventory;
use App\Models\ProductImage;
use App\Models\Discount;
use App\Models\DiscountUsage;


class CartController extends Controller
{
    public function list()
    {
        $cart = session()->get('cart', []);
        $total = 0;

        foreach ($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        $shippingFee = 20000;
        if ($total >= 300000) {
            $shippingFee = 0;
        }

        $totalPayment = $total + $shippingFee;

        $discounts = Discount::where(function ($q) {
            $q->whereNull('start_at')
                ->orWhere('start_at', '<=', now());
        })
            ->where(function ($q) {
                $q->whereNull('end_at')
                    ->orWhere('end_at', '>=', now());
            })
            ->get();

        return view('pages.cart', compact('cart', 'total', 'shippingFee', 'totalPayment', 'discounts'));
    }


    public function add(Request $request)
    {
        $request->validate([
            'variant_id' => 'required|exists:product_variants,id',
            'quantity'   => 'required|integer|min:1'
        ]);

        $variant = ProductVariant::with('product')
            ->findOrFail($request->variant_id);

        $inventory = Inventory::where('product_variant_id', $variant->id)->first();
        $stock = $inventory->quantity ?? 0;

        if ($stock <= 0) {
            return back()->with('error', 'Sản phẩm đã hết hàng');
        }

        $cart = session()->get('cart', []);
        $qtyRequest = $request->quantity ? (int)$request->quantity : 1;

        $currentQty = isset($cart[$variant->id])
            ? $cart[$variant->id]['quantity']
            : 0;

        if ($currentQty + $qtyRequest > $stock) {
            return back()->with('error', 'Số lượng vượt quá tồn kho');
        }

        $image = ProductImage::where('product_variant_id', $variant->id)
            ->orderByDesc('is_primary')
            ->first();

        $imagePath = $image ? $image->image_path : null;

        $variantText = collect([
            $variant->color,
            $variant->size,
            $variant->volume,
            $variant->weight,
        ])->filter()->implode(' - ');

        if (empty($variantText)) {
            $variantText = 'Mặc định';
        }

        if (isset($cart[$variant->id])) {
            $cart[$variant->id]['quantity'] += $qtyRequest;
        } else {
            $cart[$variant->id] = [
                'variant_id' => $variant->id,
                'name'       => $variant->product->name,
                'price'      => $variant->price,
                'image'      => $imagePath,
                'variant'    => $variantText,
                'quantity'   => $qtyRequest,
                'stock'      => $stock,
            ];
        }

        session()->put('cart', $cart);

        return back()->with('success', 'Đã thêm vào giỏ hàng');
    }

    public function update(Request $request)
    {
        $cart = session()->get('cart', []);

        if ($request->has('variant_id')) {

            $variantId = $request->variant_id;
            $quantity  = max(1, (int)$request->quantity);

            if (isset($cart[$variantId])) {

                $inventory = Inventory::where('product_variant_id', $variantId)->first();
                $stock = $inventory->quantity ?? 0;

                if ($quantity > $stock) {
                    return back()->with(
                        'error',
                        'Sản phẩm trong giỏ đã đạt số lượng tối đa trong kho'
                    );
                }

                if ($quantity == $stock) {
                    session()->flash(
                        'warning',
                        'Sản phẩm đã đạt số lượng tối đa trong kho'
                    );
                }

                $cart[$variantId]['quantity'] = $quantity;
            }
        }

        if ($request->has('quantities')) {

            foreach ($request->quantities as $variantId => $quantity) {

                if (!isset($cart[$variantId])) continue;

                $inventory = Inventory::where('product_variant_id', $variantId)->first();
                $stock = $inventory->quantity ?? 0;

                $quantity = max(1, (int)$quantity);

                if ($quantity > $stock) {
                    $quantity = $stock;

                    session()->flash(
                        'warning',
                        'Một số sản phẩm đã đạt số lượng tối đa trong kho'
                    );
                }

                $cart[$variantId]['quantity'] = $quantity;
            }
        }

        session()->put('cart', $cart);

        return back()->with('success', 'Cập nhật giỏ hàng thành công');
    }

    public function remove(Request $request)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$request->variant_id])) {
            unset($cart[$request->variant_id]);
        }

        session()->put('cart', $cart);

        return back()->with('success', 'Đã xoá sản phẩm');
    }

    public function applyDiscount(Request $request)
    {
        $code = trim($request->input('code'));

        if ($code === '' || $code === null) {

            session()->forget([
                'cart_discount',
                'cart_discount_type',
                'cart_discount_code',
                'cart_discount_id'
            ]);

            return redirect()->route('cart.list')
                ->with('discount_success', 'Đã bỏ áp dụng mã giảm giá');
        }

        $discount = Discount::where('code', $code)->first();

        if (!$discount) {
            return redirect()->route('cart.list')
                ->with('discount_error', 'Mã không tồn tại!');
        }

        if (!$discount->isActive()) {
            return redirect()->route('cart.list')
                ->with('discount_error', 'Mã không hợp lệ!');
        }

        if (auth()->check()) {
            $used = DiscountUsage::where('discount_id', $discount->id)
                ->where('user_id', auth()->id())
                ->exists();

            if ($used) {
                return redirect()->route('cart.list')
                    ->with('discount_error', 'Bạn đã dùng mã này rồi!');
            }
        }

        session([
            'cart_discount' => $discount->value,
            'cart_discount_type' => $discount->type,
            'cart_discount_code' => $discount->code,
            'cart_discount_id' => $discount->id,
        ]);

        return redirect()->route('cart.list')
            ->with('discount_success', 'Áp dụng mã thành công!');
    }
}
