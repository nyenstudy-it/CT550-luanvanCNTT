<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductVariantController extends Controller
{
    public function index($productId)
    {
        $product = Product::with(['variants.images'])->findOrFail($productId);

        return view('admin.products.variants.index', compact('product'));
    }

    public function create($productId)
    {
        $product = Product::findOrFail($productId);

        return view('admin.products.variants.create', compact('product'));
    }
    public function store(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);

        $data = $request->validate([
            'volume'           => 'nullable|string|max:100',
            'weight'           => 'nullable|string|max:100',
            'color'            => 'nullable|string|max:100',
            'size'             => 'nullable|string|max:100',
            'price'            => 'required|numeric|min:0',
            'manufacture_date' => 'nullable|date',
            'expired_at'       => 'nullable|date|after_or_equal:manufacture_date',
            'images.*'         => 'nullable|image|max:2048',
        ]);

        // Bắt buộc có ít nhất 1 thuộc tính
        if (
            empty($data['volume']) &&
            empty($data['weight']) &&
            empty($data['color']) &&
            empty($data['size'])
        ) {
            return back()
                ->withErrors(['variant' => 'Phải nhập ít nhất 1 thuộc tính biến thể'])
                ->withInput();
        }

        // Tạo SKU
        $skuParts = array_filter([
            $data['color']  ?? null,
            $data['size']   ?? null,
            $data['volume'] ?? null,
            $data['weight'] ?? null,
        ]);

        $data['sku'] = strtoupper(
            Str::slug($product->name . '-' . implode('-', $skuParts), '')
                . '-' . Str::random(4)
        );

        $data['product_id'] = $product->id;

        $variant = ProductVariant::create($data);

        // Lưu ảnh biến thể
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $file) {

                $path = $file->store('variants', 'public');

                ProductImage::create([
                    'product_id'         => null,          // ✅ NHẤT QUÁN
                    'product_variant_id' => $variant->id,
                    'image_path'         => $path,
                    'is_primary'         => $index === 0,   // ảnh đầu là chính
                ]);
            }
        }

        return redirect()
            ->route('admin.products.variants.index', $product->id)
            ->with('success', 'Đã thêm biến thể');
    }

    public function edit($id)
    {
        $variant = ProductVariant::with(['product', 'images'])->findOrFail($id);
        $product = $variant->product;

        return view('admin.products.variants.edit', compact('variant', 'product'));
    }

    public function update(Request $request, $id)
    {
        $variant = ProductVariant::with('images')->findOrFail($id);
        $variant->update([
            'color'            => $request->color,
            'size'             => $request->size,
            'volume'           => $request->volume,
            'weight'           => $request->weight,
            'price'            => $request->price,
            'manufacture_date' => $request->manufacture_date,
            'expired_at'       => $request->expired_at,
        ]);
        if ($request->filled('primary_image_id')) {

            // reset toàn bộ ảnh về không primary
            $variant->images()->update([
                'is_primary' => 0
            ]);

            // set ảnh được chọn làm primary
            ProductImage::where('id', $request->primary_image_id)
                ->where('product_variant_id', $variant->id)
                ->update([
                    'is_primary' => 1
                ]);
        }

        if ($request->hasFile('images')) {

            $hasPrimary = $variant->images()
                ->where('is_primary', 1)
                ->exists();

            foreach ($request->file('images') as $index => $file) {

                $path = $file->store('variants', 'public');

                ProductImage::create([
                    'product_id'         => null,          // ✅ chỉ dành cho variant
                    'product_variant_id' => $variant->id,
                    'image_path'         => $path,
                    'is_primary'         => !$hasPrimary && $index === 0,
                ]);
            }
        }

        return redirect()
            ->route('admin.products.variants.index', $variant->product_id)
            ->with('success', 'Cập nhật biến thể thành công');
    }
    public function destroy($id)
    {
        $variant = ProductVariant::with('images')->findOrFail($id);
        $productId = $variant->product_id;

        foreach ($variant->images as $image) {
            if ($image->image_path && Storage::disk('public')->exists($image->image_path)) {
                Storage::disk('public')->delete($image->image_path);
            }
            $image->delete();
        }

        $variant->delete();

        return redirect()
            ->route('admin.products.variants.index', $productId)
            ->with('success', 'Đã xoá biến thể');
    }
}
