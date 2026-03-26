<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductImage;
use App\Models\Supplier;
use App\Models\CategoryProduct;
use App\Models\Order;
use App\Models\Review;
use App\Services\ProductPricingService;

class ProductController extends Controller
{
    public function __construct(private ProductPricingService $productPricingService) {}

    public function list(Request $request)
    {
        $query = Product::with([
            'supplier',
            'category',
            'variants.images' => function ($q) {
                $q->where('is_primary', 1);
            }
        ])->withCount('variants');

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('min_price') || $request->filled('max_price')) {

            $min = $request->min_price;
            $max = $request->max_price;

            $query->whereHas('variants', function ($q) use ($min, $max) {

                if (!is_null($min)) {
                    $q->where('price', '>=', $min);
                }

                if (!is_null($max)) {
                    $q->where('price', '<=', $max);
                }
            });
        }

        $products = $query
            ->orderByDesc('id')
            ->paginate(10)
            ->appends($request->query());

        $categories = CategoryProduct::orderBy('name')->get();
        $suppliers  = Supplier::orderBy('name')->get();

        return view('admin.products.list', compact(
            'products',
            'categories',
            'suppliers'
        ));
    }

    public function create()
    {
        return view('admin.products.create', [
            'suppliers'  => Supplier::all(),
            'categories' => CategoryProduct::all(),
        ]);
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id' => 'required',
            'supplier_id' => 'required',
            'name'        => 'required|string|max:255',
            'status'      => 'required|in:active,inactive',

            'price'       => 'required|numeric|min:0',

            'images'      => 'required|array|min:1',
            'images.*'    => 'image|max:2048',
        ]);

        $product = Product::create([
            'category_id' => $data['category_id'],
            'supplier_id' => $data['supplier_id'],
            'name'        => $data['name'],
            'status'      => $data['status'],
            'description' => $request->description,
            'usage_instructions' => $request->usage_instructions,
            'storage_instructions' => $request->storage_instructions,
            'ocop_star'   => $request->ocop_star,
            'ocop_year'   => $request->ocop_year,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'price'      => $data['price'],
        ]);

        foreach ($request->file('images') as $index => $file) {

            $path = $file->store('products', 'public');

            if ($index === 0) {
                $product->update([
                    'image' => $path
                ]);
                continue;
            }

            ProductImage::create([
                'product_variant_id' => $variant->id,
                'image_path' => $path,
                'is_primary' => $index === 1,
            ]);
        }

        return redirect()
            ->route('admin.products.list')
            ->with('success', 'Đã tạo sản phẩm');
    }

    public function edit($id)
    {
        return view('admin.products.edit', [
            'product'    => Product::findOrFail($id),
            'suppliers'  => Supplier::all(),
            'categories' => CategoryProduct::all(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $data = $request->validate([
            'category_id' => 'required',
            'supplier_id' => 'required',
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'usage_instructions'   => 'nullable|string',
            'storage_instructions' => 'nullable|string',
            'ocop_star'   => 'nullable|integer|min:0|max:5',
            'ocop_year'   => 'nullable|integer|min:1900|max:' . date('Y'),
            'status'      => 'required|in:active,inactive',
            'image'       => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {

            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }

            $data['image'] = $request->file('image')
                ->store('products', 'public');
        }

        $product->update($data);

        return redirect()
            ->route('admin.products.list')
            ->with('success', 'Đã cập nhật sản phẩm');
    }


    public function destroy($id)
    {
        Product::where('id', $id)
            ->update(['status' => 'inactive']);

        return redirect()
            ->route('admin.products.list')
            ->with('success', 'Sản phẩm đã được ngừng bán');
    }

    public function show($id)
    {
        $product = Product::with([
            'images',
            'variants.inventory',
            'variants.images',
            'variants.primaryImage',
            'category',
            'supplier',
            // eager-load approved reviews + reviewer user and replies/likes counts
            'approvedReviews' => function ($q) {
                $q->where('status', 'approved')
                    ->with(['customer.user', 'replies' => function ($qr) {
                        $qr->where('status', 'approved')->with('user');
                    }])
                    ->withCount(['likes', 'replies']);
            }
        ])
            ->withCount(['approvedReviews as approved_reviews_count'])
            ->withAvg('approvedReviews as approved_reviews_avg', 'rating')
            ->findOrFail($id);

        $firstVariantPrice = (float) ($product->variants->first()?->price ?? 0);
        $productPricing = $this->productPricingService->pricingForProduct($product, $firstVariantPrice);

        $variantPricing = $product->variants->mapWithKeys(function ($variant) use ($product) {
            $pricing = $this->productPricingService->pricingForProduct($product, (float) $variant->price);

            return [
                $variant->id => $pricing,
            ];
        });

        // Lưu lịch sử sản phẩm đã xem vào session (mới nhất đứng đầu).
        $historyKey = 'recently_viewed_products';
        $historyIds = collect(session($historyKey, []))
            ->map(fn($value) => (int) $value)
            ->filter(fn($value) => $value > 0)
            ->reject(fn($value) => $value === (int) $product->id);

        $updatedHistory = collect([(int) $product->id])
            ->merge($historyIds)
            ->unique()
            ->take(20)
            ->values();

        session([$historyKey => $updatedHistory->all()]);

        $recentViewedIds = $updatedHistory
            ->reject(fn($value) => $value === (int) $product->id)
            ->take(5)
            ->values();

        $recentViewedProducts = collect();
        if ($recentViewedIds->isNotEmpty()) {
            $ids = $recentViewedIds->implode(',');
            $recentViewedProducts = Product::query()
                ->where('status', 'active')
                ->whereIn('id', $recentViewedIds)
                ->with([
                    'variants.images' => function ($query) {
                        $query->where('is_primary', 1);
                    },
                    'variants.inventory'
                ])
                ->orderByRaw("FIELD(id, {$ids})")
                ->get();

            $recentViewedProducts = $this->productPricingService->enrichProducts($recentViewedProducts);
        }

        return view('pages.product_detail', compact(
            'product',
            'productPricing',
            'variantPricing',
            'recentViewedProducts'
        ));
    }


    public function showPopup($id)
    {
        $product = Product::with([
            'category',
            'supplier',

            'variants.images',
            'variants.inventory'
        ])->findOrFail($id);

        return view('admin.products.popup', compact('product'));
    }

    public function search(Request $request)
    {
        $query = $request->get('query', '');

        if ($query == '') {
            return response()->json([]);
        }

        // Tìm sản phẩm theo tên, chỉ lấy sản phẩm active
        $products = Product::where('status', 'active')
            ->where('name', 'LIKE', "%{$query}%")
            ->with(['variants' => function ($q) {
                $q->with(['images' => function ($q2) {
                    $q2->where('is_primary', 1);
                }]);
            }])
            ->take(10)
            ->get();

        $products = $this->productPricingService->enrichProducts($products);

        // Trả về dữ liệu JSON gồm id, name, image, giá variant đầu tiên
        $result = $products->map(function ($product) {
            $variant = $product->variants->first();
            $image = optional($variant->images->first())->image_path ?? $product->image;

            return [
                'id' => $product->id,
                'name' => $product->name,
                'price' => optional($variant)->price ?? 0,
                'final_price' => (float) ($product->display_final_price ?? (optional($variant)->price ?? 0)),
                'has_discount' => (bool) ($product->display_has_discount ?? false),
                'image' => $image ? asset('storage/' . $image) : null,
            ];
        });

        return response()->json($result);
    }

    public function index(Request $request)
    {
        $userId = Auth::id();

        $query = Product::query()
            ->with('variants')
            ->where('status', 'active')
            ->withAvg('approvedReviews as avg_rating', 'rating')
            ->withCount('approvedReviews as review_count')
            ->withCount([
                'wishlists as is_favorited' => function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                }
            ]);

        if ($request->filled('keyword')) {
            $query->where('name', 'like', '%' . $request->keyword . '%');
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
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
        $suppliers  = Supplier::all();

        return view('pages.all-products', compact(
            'products',
            'categories',
            'suppliers'
        ));
    }
}
