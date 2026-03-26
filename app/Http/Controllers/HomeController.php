<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CategoryProduct;
use App\Models\Product;
use App\Models\Discount;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\DiscountUsage;
use App\Models\Blog;
use App\Services\ProductPricingService;

class HomeController extends Controller
{
    public function __construct(private ProductPricingService $productPricingService) {}

    public function index()
    {
        $categories = CategoryProduct::all();

        $products = Product::where('status', 'active')
            ->with('variants')
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get();

        $latestProducts = Product::where('status', 'active')
            ->with('variants')
            ->orderBy('created_at', 'desc')
            ->take(6)
            ->get();

        $now = now();
        $userId = Auth::id();

        $discounts = Discount::query()
            ->whereDoesntHave('products')

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

        $blogs = Blog::latest()->take(3)->get();

        $bestSellingProducts = Product::where('status', 'active')
            ->with('variants')
            ->addSelect([
                'total_sold' => DB::table('order_items')
                    ->join('product_variants', 'product_variants.id', '=', 'order_items.product_variant_id')
                    ->whereColumn('product_variants.product_id', 'products.id')
                    ->selectRaw('COALESCE(SUM(order_items.quantity), 0)'),
            ])
            ->orderByDesc('total_sold')
            ->take(6)
            ->get();

        $topRatedProducts = Product::where('status', 'active')
            ->with('variants')
            ->withAvg('approvedReviews as avg_rating', 'rating')
            ->orderByDesc('avg_rating')
            ->take(6)
            ->get();

        $savedDiscountCodes = collect(session('cart_saved_discount_codes', []))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $products = $this->productPricingService->enrichProducts($products);
        $latestProducts = $this->productPricingService->enrichProducts($latestProducts);
        $bestSellingProducts = $this->productPricingService->enrichProducts($bestSellingProducts);
        $topRatedProducts = $this->productPricingService->enrichProducts($topRatedProducts);

        return view('pages.home', [
            'categories' => $categories,
            'products' => $products,
            'latestProducts' => $latestProducts,
            'discounts' => $discounts,
            'showCategories' => true,
            'blogs' => $blogs,
            'bestSellingProducts' => $bestSellingProducts,
            'topRatedProducts' => $topRatedProducts,
            'savedDiscountCodes' => $savedDiscountCodes,
        ]);
    }
    public function showCategory($id, Request $request)
    {
        return app(\App\Http\Controllers\Client\CategoryController::class)->show($id, $request);
    }
}
