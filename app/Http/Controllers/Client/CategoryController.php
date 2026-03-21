<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CategoryProduct;
use App\Models\Product;
use App\Models\Supplier;

class CategoryController extends Controller
{
    public function show($id, Request $request)
    {
        $category = CategoryProduct::findOrFail($id);

        $query = Product::where('status', 'active')
            ->where('category_id', $id)
            ->with('variants');
        if ($request->filled('keyword')) {
            $query->where('name', 'like', '%' . $request->keyword . '%');
        }
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }
        if ($request->filled('min_price') || $request->filled('max_price')) {
            $query->whereHas('variants', function ($q) use ($request) {
                if ($request->min_price) {
                    $q->where('price', '>=', $request->min_price);
                }
                if ($request->max_price) {
                    $q->where('price', '<=', $request->max_price);
                }
            });
        }

        $products = $query->paginate(12)->appends($request->query());

        $suppliers = Supplier::all();

        return view('client.category.show', compact(
            'category',
            'products',
            'suppliers'
        ));
    }
}
