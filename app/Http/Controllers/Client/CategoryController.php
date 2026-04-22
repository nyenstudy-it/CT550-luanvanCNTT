<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CategoryProduct;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\ProductPricingService;

class CategoryController extends Controller
{
    public function __construct(private ProductPricingService $productPricingService) {}

    public function show($id, Request $request)
    {
        $category = CategoryProduct::findOrFail($id);
        $userId = Auth::id();

        $query = Product::query()
            ->where('status', 'active')
            ->where('category_id', $id)
            ->with('variants')
            ->withAvg('approvedReviews as avg_rating', 'rating')
            ->withCount('approvedReviews as review_count')
            ->withCount([
                'wishlists as is_favorited' => function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                }
            ]);

        if ($request->filled('keyword')) {
            $keyword = trim((string) $request->keyword);
            $query->where(function ($q) use ($keyword) {
                $escaped = addcslashes($keyword, '\\%_');
                $q->where('products.name', 'like', '%' . $escaped . '%')
                    ->orWhere('products.description', 'like', '%' . $escaped . '%');
            });
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('min_price') || $request->filled('max_price')) {
            $min = $request->min_price;
            $max = $request->max_price;
            $query->whereHas('variants', function ($q) use ($min, $max) {
                if (!is_null($min)) $q->where('price', '>=', $min);
                if (!is_null($max)) $q->where('price', '<=', $max);
            });
        }

        if ($request->filled('price_range')) {
            [$min, $max] = explode('-', $request->price_range);
            $query->whereHas('variants', function ($q) use ($min, $max) {
                $q->whereBetween('price', [$min, $max]);
            });
        }

        if ($request->filled('sort')) {
            if (in_array($request->sort, ['price_asc', 'price_desc'])) {
                $query->join('product_variants', 'products.id', '=', 'product_variants.product_id')
                    ->select('products.*', DB::raw('MIN(product_variants.price) as min_price'))
                    ->groupBy('products.id');

                if ($request->sort == 'price_asc') {
                    $query->orderBy('min_price', 'asc');
                } else {
                    $query->orderBy('min_price', 'desc');
                }
            } elseif ($request->sort == 'newest') {
                $query->orderBy('products.created_at', 'desc');
            }
        } else {
            $query->orderByDesc('products.id');
        }

        $products = $query->paginate(12)->withQueryString();
        $products->setCollection($this->productPricingService->enrichProducts($products->getCollection()));

        $categories = CategoryProduct::all();
        $suppliers = Supplier::all();

        return view('client.category.show', compact(
            'category',
            'products',
            'categories',
            'suppliers'
        ));
    }
}
