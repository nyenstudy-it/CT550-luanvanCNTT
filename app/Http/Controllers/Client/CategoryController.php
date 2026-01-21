<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\CategoryProduct;

class CategoryController extends Controller
{
    public function show($id)
    {
        $category = CategoryProduct::findOrFail($id);

        $products = $category->products()->paginate(12);

        return view('client.category.show', compact('category', 'products'));
    }
}
