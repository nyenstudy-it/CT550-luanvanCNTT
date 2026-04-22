<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Wishlist;
use App\Models\Product;
use App\Services\ProductPricingService;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    public function __construct(private ProductPricingService $productPricingService) {}

    public function index()
    {
        $items = Wishlist::with(['product.images', 'product.variants'])
            ->where('user_id', Auth::id())
            ->get();

        $items = $items->map(function ($item) {
            if ($item->product) {
                $basePrice = (float) optional($item->product->variants->first())->price;
                $pricing = $this->productPricingService->pricingForProduct($item->product, $basePrice);

                $item->product->setAttribute('display_base_price', $pricing['base_price']);
                $item->product->setAttribute('display_final_price', $pricing['final_price']);
                $item->product->setAttribute('display_has_discount', $pricing['has_discount']);
                $item->product->setAttribute('display_discount_label', $pricing['discount_label']);
            }

            return $item;
        });

        return view('pages.wishlist', compact('items'));
    }

    public function toggle($productId)
    {
        $userId = Auth::id();

        $exist = Wishlist::where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();

        $isAddedToWishlist = false;
        $message = '';

        if ($exist) {
            $exist->delete();
            $isAddedToWishlist = false;
            $message = 'Đã xoá khỏi yêu thích';
        } else {
            Wishlist::create([
                'user_id' => $userId,
                'product_id' => $productId
            ]);
            $isAddedToWishlist = true;
            $message = 'Đã thêm vào yêu thích';
        }

        // If AJAX request, return JSON
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'isAddedToWishlist' => $isAddedToWishlist,
                'productId' => $productId
            ]);
        }

        // Otherwise, redirect with flash message
        return back()->with('success', $message);
    }
}
