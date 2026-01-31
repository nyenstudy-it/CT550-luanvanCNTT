<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductImage;
use App\Models\Supplier;
use App\Models\CategoryProduct;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function list()
    {
        $products = Product::with([
            'supplier',
            'category',
            'variants.images' => function ($q) {
                $q->where('is_primary', 1);
            }
        ])
            ->withCount('variants')
            ->get();

        return view('admin.products.list', compact('products'));
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

            // variant mặc định
            'price'       => 'required|numeric|min:0',

            // images
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

        $sku = strtoupper(
            Str::slug($product->name, '')
                . '-' . Str::random(4)
        );

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'price'      => $data['price'],
            'sku'        => $sku,
        ]);

        foreach ($request->file('images') as $index => $file) {

            $path = $file->store('products', 'public');

            ProductImage::create([
                'product_variant_id' => $variant->id,
                'image_path' => $path,
                'is_primary' => $index === 0,
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

        // xử lý đổi ảnh đại diện
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
            'variants.images',
            'supplier',
            'category',
        ])
            ->where('status', 'active')
            ->findOrFail($id);

        return view('pages.product.show', compact('product'));
    }
}
