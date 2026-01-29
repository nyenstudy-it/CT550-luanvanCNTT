<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Supplier;
use App\Models\CategoryProduct;
use App\Models\Product;
use App\Models\ProductImage;

use function Symfony\Component\String\u;

class ProductController extends Controller
{
   public function list()
   {
       $products = Product::with(['supplier', 'category'])->get();
       return view('admin.products.list', compact('products'));
   }

   public function create()
   {
       $suppliers = Supplier::all();
       $categories = CategoryProduct::all();
       return view('admin.products.create', compact('suppliers', 'categories'));
   }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'category_id' => 'required|exists:category_products,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'description' => 'nullable|string',
            'usage_instructions' => 'nullable|string',
            'manufacture_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'storage_instructions' => 'nullable|string',
            'weight_volume' => 'nullable|string|max:100',
            'ocop_star' => 'nullable|integer|min:0|max:5',
            'ocop_year' => 'nullable|integer|min:1900|max:' . date('Y'),
            'image' => 'nullable|image|max:2048',
            'status' => 'required|in:active,inactive',
            'images.*' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $validatedData['image'] = $request->file('image')->store('products', 'public');
        }

        $product = Product::create($validatedData);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');

                ProductImage::create([
                    'product_id' => $product->id,
                    'image' => $path
                ]);
            }
        }
        return redirect()->route('admin.products.list')
            ->with('success', 'Sản phẩm đã được tạo thành công.');
    }


    public function edit($id)
   {
        $product = Product::with('images')->findOrFail($id);
        $suppliers = Supplier::all();
       $categories = CategoryProduct::all();
       return view('admin.products.edit', compact('product', 'suppliers', 'categories'));
   }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validatedData = $request->validate([
            'category_id' => 'required|exists:category_products,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'description' => 'nullable|string',
            'usage_instructions' => 'nullable|string',
            'manufacture_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'storage_instructions' => 'nullable|string',
            'weight_volume' => 'nullable|string|max:100',
            'ocop_star' => 'nullable|integer|min:0|max:5',
            'ocop_year' => 'nullable|integer|min:1900|max:' . date('Y'),
            'image' => 'nullable|image|max:2048',
            'status' => 'required|in:active,inactive',
            'images.*' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $validatedData['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($validatedData);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');

                ProductImage::create([
                    'product_id' => $product->id,
                    'image' => $path
                ]);
            }
        }

        return redirect()->route('admin.products.list')
            ->with('success', 'Sản phẩm đã được cập nhật thành công.');
    }


    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->update(['status' => 'inactive']);

        return redirect()->route('admin.products.list')
            ->with('success', 'San phẩm đã được vô hiệu hóa.');
    }
}
