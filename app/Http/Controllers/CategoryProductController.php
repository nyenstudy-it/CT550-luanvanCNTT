<?php

namespace App\Http\Controllers;

use App\Models\CategoryProduct;
use Illuminate\Http\Request;

class CategoryProductController extends Controller
{
    public function list(Request $request)
    {
        $query = CategoryProduct::query();

        if ($request->filled('keyword')) {
            $keyword = trim((string) $request->keyword);
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', '%' . $keyword . '%')
                    ->orWhere('description', 'like', '%' . $keyword . '%');
            });
        }

        if ($request->filled('has_image')) {
            if ($request->has_image === 'yes') {
                $query->whereNotNull('image_url')->where('image_url', '!=', '');
            } elseif ($request->has_image === 'no') {
                $query->where(function ($q) {
                    $q->whereNull('image_url')->orWhere('image_url', '');
                });
            }
        }

        $categories = $query
            ->orderBy('id', 'asc')
            ->paginate(10)
            ->appends($request->query());

        $summary = [
            'total' => CategoryProduct::count(),
            'with_image' => CategoryProduct::whereNotNull('image_url')->where('image_url', '!=', '')->count(),
            'without_image' => CategoryProduct::where(function ($q) {
                $q->whereNull('image_url')->orWhere('image_url', '');
            })->count(),
        ];

        return view('admin.categories.list', compact('categories', 'summary'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function edit($id)
    {
        $category = CategoryProduct::findOrFail($id);
        return view('admin.categories.edit', compact('category'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $data = $request->only(['name', 'description']);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('category_images', 'public');
            $data['image_url'] = $path;
        }

        CategoryProduct::create($data);

        return redirect()->route('admin.categories.list')->with('success', 'Danh mục đã được tạo thành công.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $category = CategoryProduct::findOrFail($id);
        $data = $request->only(['name', 'description']);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('category_images', 'public');
            $data['image_url'] = $path;
        }

        $category->update($data);

        return redirect()->route('admin.categories.list')->with('success', 'Danh mục đã được cập nhật thành công.');
    }

    public function destroy($id)
    {
        $category = CategoryProduct::findOrFail($id);
        $category->delete();

        return redirect()->route('admin.categories.list')->with('success', 'Danh mục đã được xóa thành công.');
    }
}
