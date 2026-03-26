<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductVariant;
use App\Models\Inventory;
use App\Models\ProductImage;
use App\Models\Discount;
use App\Models\DiscountUsage;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;


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
            ->whereDoesntHave('products')
            ->with('products:id,name')
            ->get();

        $cartItems = $this->normalizeCartItems($cart);

        $appliedDiscount = null;
        $discountAmount = 0;
        if (!empty(session('cart_discount_code'))) {
            $appliedDiscount = Discount::with('products:id,name')
                ->where('code', session('cart_discount_code'))
                ->first();

            if ($appliedDiscount && $appliedDiscount->isActive()) {
                $discountAmount = $this->calculateDiscountAmount($appliedDiscount, $cartItems);
            }
        }

        $savedDiscountCodes = collect(session('cart_saved_discount_codes', []))
            ->filter()
            ->unique()
            ->values();

        $savedDiscounts = $discounts
            ->whereIn('code', $savedDiscountCodes)
            ->values();

        $suggestedDiscounts = $discounts
            ->whereNotIn('code', $savedDiscountCodes)
            ->values();

        return view('pages.cart', compact(
            'cart',
            'total',
            'shippingFee',
            'totalPayment',
            'discounts',
            'savedDiscounts',
            'suggestedDiscounts',
            'savedDiscountCodes',
            'discountAmount',
            'appliedDiscount'
        ));
    }

    public function saveDiscount(Request $request)
    {
        $code = trim((string) $request->input('code'));

        if ($code === '') {
            return redirect()->back()
                ->with('discount_error', 'Không tìm thấy mã để lưu.');
        }

        $discount = Discount::where('code', $code)
            ->whereDoesntHave('products')
            ->first();

        if (!$discount || !$discount->isActive()) {
            return redirect()->back()
                ->with('discount_error', 'Mã giảm giá không hợp lệ hoặc đã hết hạn.');
        }

        $savedCodes = collect(session('cart_saved_discount_codes', []));

        if ($savedCodes->contains($discount->code)) {
            return redirect()->back()
                ->with('discount_success', 'Mã này đã được lưu trước đó.');
        }

        $savedCodes->push($discount->code);

        session(['cart_saved_discount_codes' => $savedCodes->unique()->values()->all()]);

        return redirect()->back()
            ->with('discount_success', 'Đã lưu mã giảm giá thành công.');
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
                'product_id' => $variant->product_id,
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
                'cart_discount_id',
                'cart_discount_amount'
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

        $cart = session()->get('cart', []);
        $cartItems = $this->normalizeCartItems($cart);
        $discount->load('products:id,name');

        $discountAmount = $this->calculateDiscountAmount($discount, $cartItems);
        if ($discountAmount <= 0) {
            return redirect()->route('cart.list')
                ->with('discount_error', 'Mã không áp dụng cho sản phẩm hiện có trong giỏ hàng.');
        }

        $savedCodes = collect(session('cart_saved_discount_codes', []));
        if (!$savedCodes->contains($discount->code)) {
            return redirect()->route('cart.list')
                ->with('discount_error', 'Vui lòng lưu mã trước khi áp dụng.');
        }

        if (Auth::check()) {
            $used = DiscountUsage::where('discount_id', $discount->id)
                ->where('user_id', Auth::id())
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
            'cart_discount_amount' => $discountAmount,
        ]);

        return redirect()->route('cart.list')
            ->with('discount_success', 'Áp dụng mã thành công!');
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
}
