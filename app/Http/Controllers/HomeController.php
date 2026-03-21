<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CategoryProduct;
use App\Models\Product;
use App\Models\Discount;
use Illuminate\Support\Facades\Auth;
use App\Models\DiscountUsage;


class HomeController extends Controller
{
    public function index()
    {
        $categories = CategoryProduct::all();

        $products = Product::where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get();

        $latestProducts = Product::where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->take(6)
            ->get();

        $now = now();
        $userId = Auth::id();

        $discounts = Discount::query()

            ->where(function ($q) use ($now) {
                $q->whereNull('start_at')
                    ->orWhere('start_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('end_at')
                    ->orWhere('end_at', '>=', $now);
            })

            ->where(function ($q) {
                $q->whereNull('usage_limit')
                    ->orWhereColumn('used_count', '<', 'usage_limit');
            })

            ->when($userId, function ($query) use ($userId) {
                $query->whereNotIn('id', function ($sub) use ($userId) {
                    $sub->select('discount_id')
                        ->from('discount_usages')
                        ->where('user_id', $userId);
                });
            })

            ->orderByDesc('id')
            ->get();

        return view('pages.home', [
            'categories' => $categories,
            'products' => $products,
            'latestProducts' => $latestProducts,
            'discounts' => $discounts,
            'showCategories' => true,  
        ]);
    }
    public function showCategory($id)
    {
        return app(\App\Http\Controllers\Client\CategoryController::class)->show($id);
    }

   
}
