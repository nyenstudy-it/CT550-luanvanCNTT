<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Wishlist;
use App\Models\Product;

class WishlistController extends Controller
{
    public function index()
    {
        $items = Wishlist::with(['product.images', 'product.variants'])
            ->where('user_id', auth()->id())
            ->get();

        return view('pages.wishlist', compact('items'));
    }

    public function toggle($productId)
    {
        $userId = auth()->id();

        $exist = Wishlist::where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();

        if ($exist) {
            $exist->delete();
            return back()->with('success', 'Đã xoá khỏi yêu thích');
        }

        Wishlist::create([
            'user_id' => $userId,
            'product_id' => $productId
        ]);

        return back()->with('success', 'Đã thêm vào yêu thích');
    }
}
