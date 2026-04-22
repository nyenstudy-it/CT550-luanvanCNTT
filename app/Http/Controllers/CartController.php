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
        $completedOrdersCount = $this->getCompletedOrdersCount();
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
            ->get()
            ->filter(fn(Discount $discount) => $discount->isEligibleForCompletedOrdersCount($completedOrdersCount))
            ->values();

        $cartItems = $this->normalizeCartItems($cart);

        $appliedDiscount = null;
        $discountAmount = 0;
        if (!empty(session('cart_discount_code'))) {
            $appliedDiscount = Discount::with('products:id,name')
                ->where('code', session('cart_discount_code'))
                ->first();

            if (
                $appliedDiscount
                && $appliedDiscount->isActive()
                && $appliedDiscount->isEligibleForCompletedOrdersCount($completedOrdersCount)
            ) {
                $discountAmount = $this->calculateDiscountAmount($appliedDiscount, $cartItems);
            } else {
                $this->forgetAppliedDiscount();
                $appliedDiscount = null;
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
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy mã để lưu.'
                ], 400);
            }
            return redirect()->back()
                ->with('warning', 'Không tìm thấy mã để lưu.');
        }

        $discount = Discount::where('code', $code)
            ->whereDoesntHave('products')
            ->first();

        if (!$discount || !$discount->isActive()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mã giảm giá không hợp lệ hoặc đã hết hạn.'
                ], 400);
            }
            return redirect()->back()
                ->with('warning', 'Mã giảm giá không hợp lệ hoặc đã hết hạn.');
        }

        if (!$discount->isEligibleForCompletedOrdersCount($this->getCompletedOrdersCount())) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $discount->audience_restriction_message ?? 'Mã giảm giá này không áp dụng cho tài khoản của bạn.'
                ], 400);
            }
            return redirect()->back()
                ->with('warning', $discount->audience_restriction_message ?? 'Mã giảm giá này không áp dụng cho tài khoản của bạn.');
        }

        $savedCodes = collect(session('cart_saved_discount_codes', []));

        if ($savedCodes->contains($discount->code)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Mã này đã được lưu trước đó.'
                ], 200);
            }
            return redirect()->back()
                ->with('success', 'Mã này đã được lưu trước đó.');
        }

        $savedCodes->push($discount->code);

        session(['cart_saved_discount_codes' => $savedCodes->unique()->values()->all()]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã lưu mã giảm giá thành công.'
            ], 200);
        }
        return redirect()->back()
            ->with('success', 'Đã lưu mã giảm giá thành công.');
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
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Sản phẩm đã hết hàng'], 400);
            }
            return back()->with('showCartPopup', true)->with('error', 'Sản phẩm đã hết hàng');
        }

        $cart = session()->get('cart', []);
        $qtyRequest = $request->quantity ? (int)$request->quantity : 1;

        $currentQty = isset($cart[$variant->id])
            ? $cart[$variant->id]['quantity']
            : 0;

        if ($currentQty + $qtyRequest > $stock) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Số lượng vượt quá tồn kho'], 400);
            }
            return back()->with('showCartPopup', true)->with('error', 'Số lượng vượt quá tồn kho');
        }

        // Check if quantity > 10 to show warning
        $hasQuantityWarning = false;
        if ($qtyRequest > 10) {
            $hasQuantityWarning = true;
        }

        // Get variant image if exists, otherwise get product's primary image
        $image = ProductImage::where('product_variant_id', $variant->id)
            ->orderByDesc('is_primary')
            ->first();

        if (!$image) {
            $image = ProductImage::where('product_id', $variant->product_id)
                ->whereNull('product_variant_id')
                ->orderByDesc('is_primary')
                ->first();
        }

        // Get image path with fallback to default if null
        $imagePath = null;
        if ($image) {
            $imagePath = $image->image_path;
        } elseif ($variant->product->image) {
            $imagePath = $variant->product->image;
        }

        // Ensure we have a valid path, default to 'frontend/images/product/product-1.jpg' if empty
        if (empty($imagePath)) {
            $imagePath = 'frontend/images/product/product-1.jpg';
        }

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

        // Handle AJAX requests
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã thêm vào giỏ hàng',
                'cart_count' => count(session()->get('cart', []))
            ]);
        }

        // Use ->with() method to ensure flash data persists for non-AJAX requests
        $response = back()
            ->with('showCartPopup', true);

        if ($hasQuantityWarning) {
            $response = $response->with('warning', 'Số lượng sản phẩm lớn hơn 10. Nếu cần đặt số lượng lớn, vui lòng liên hệ qua tin nhắn hoặc gửi liên hệ với chúng tôi.');
        } else {
            $response = $response->with('success', 'Đã thêm vào giỏ hàng');
        }

        return $response;
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

                // Check if quantity > 10 to show warning
                if ($quantity > 10) {
                    session()->flash(
                        'warning',
                        'Số lượng sản phẩm lớn hơn 10. Nếu cần đặt số lượng lớn, vui lòng liên hệ qua tin nhắn hoặc gửi liên hệ với chúng tôi.'
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

                // Check if quantity > 10 to show warning
                if ($quantity > 10) {
                    session()->flash(
                        'warning',
                        'Một số sản phẩm có số lượng lớn hơn 10. Nếu cần đặt số lượng lớn, vui lòng liên hệ qua tin nhắn hoặc gửi liên hệ với chúng tôi.'
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

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Đã bỏ áp dụng mã giảm giá'
                ], 200);
            }
            return redirect()->route('cart.list')
                ->with('success', 'Đã bỏ áp dụng mã giảm giá');
        }

        $discount = Discount::where('code', $code)->first();

        if (!$discount) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mã không tồn tại!'
                ], 400);
            }
            return redirect()->route('cart.list')
                ->with('warning', 'Mã không tồn tại!');
        }

        if (!$discount->isActive()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mã không hợp lệ!'
                ], 400);
            }
            return redirect()->route('cart.list')
                ->with('warning', 'Mã không hợp lệ!');
        }

        if (!$discount->isEligibleForCompletedOrdersCount($this->getCompletedOrdersCount())) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $discount->audience_restriction_message ?? 'Mã giảm giá này không áp dụng cho tài khoản của bạn.'
                ], 400);
            }
            return redirect()->route('cart.list')
                ->with('warning', $discount->audience_restriction_message ?? 'Mã giảm giá này không áp dụng cho tài khoản của bạn.');
        }

        $cart = session()->get('cart', []);
        $cartItems = $this->normalizeCartItems($cart);
        $discount->load('products:id,name');

        $discountAmount = $this->calculateDiscountAmount($discount, $cartItems);
        if ($discountAmount <= 0) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mã không áp dụng cho sản phẩm hiện có trong giỏ hàng.'
                ], 400);
            }
            return redirect()->route('cart.list')
                ->with('warning', 'Mã không áp dụng cho sản phẩm hiện có trong giỏ hàng.');
        }

        $savedCodes = collect(session('cart_saved_discount_codes', []));
        if (!$savedCodes->contains($discount->code)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng lưu mã trước khi áp dụng.'
                ], 400);
            }
            return redirect()->route('cart.list')
                ->with('warning', 'Vui lòng lưu mã trước khi áp dụng.');
        }

        if (Auth::check()) {
            $used = DiscountUsage::where('discount_id', $discount->id)
                ->where('user_id', Auth::id())
                ->exists();

            if ($used) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Bạn đã dùng mã này rồi!'
                    ], 400);
                }
                return redirect()->route('cart.list')
                    ->with('warning', 'Bạn đã dùng mã này rồi!');
            }
        }

        session([
            'cart_discount' => $discount->value,
            'cart_discount_type' => $discount->type,
            'cart_discount_code' => $discount->code,
            'cart_discount_id' => $discount->id,
            'cart_discount_amount' => $discountAmount,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Áp dụng mã thành công!'
            ], 200);
        }
        return redirect()->route('cart.list')
            ->with('success', 'Áp dụng mã thành công!');
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

    private function getCompletedOrdersCount(): ?int
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (!$user || !$user->isCustomer()) {
            return null;
        }

        return $user->orders()
            ->where('status', 'completed')
            ->count();
    }

    private function forgetAppliedDiscount(): void
    {
        session()->forget([
            'cart_discount',
            'cart_discount_type',
            'cart_discount_code',
            'cart_discount_id',
            'cart_discount_amount',
        ]);
    }
}
