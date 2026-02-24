<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductVariant;
use App\Models\Inventory;
use App\Models\ProductImage;

class CartController extends Controller
{
    // ===============================
    // DANH SÁCH GIỎ HÀNG
    // ===============================
    public function list()
    {
        $cart = session()->get('cart', []);
        $total = 0;

        foreach ($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        return view('pages.cart', compact('cart', 'total'));
    }

    // ===============================
    // THÊM VÀO GIỎ
    // ===============================
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
        $qtyRequest = (int) $request->quantity;

        $currentQty = isset($cart[$variant->id])
            ? $cart[$variant->id]['quantity']
            : 0;

        // 🔥 FIX QUAN TRỌNG
        if ($currentQty + $qtyRequest > $stock) {
            return back()->with('error', 'Số lượng vượt quá tồn kho');
        }

        // Lấy ảnh chính
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

    // CẬP NHẬT SỐ LƯỢNG
    // CẬP NHẬT SỐ LƯỢNG
    public function update(Request $request)
    {
        $cart = session()->get('cart', []);

        // ===== CẬP NHẬT 1 SẢN PHẨM =====
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

        // ===== CẬP NHẬT NHIỀU SẢN PHẨM =====
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


    // XOÁ SẢN PHẨM
    public function remove(Request $request)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$request->variant_id])) {
            unset($cart[$request->variant_id]);
        }

        session()->put('cart', $cart);

        return back()->with('success', 'Đã xoá sản phẩm');
    }
}
