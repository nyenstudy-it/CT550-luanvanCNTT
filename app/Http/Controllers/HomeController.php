<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CategoryProduct;
use App\Models\Product;

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

        return view('pages.home', compact('categories', 'products', 'latestProducts'));
    }

    public function showCategory($id)
    {
        return app(\App\Http\Controllers\Client\CategoryController::class)->show($id);
    }

   
}
