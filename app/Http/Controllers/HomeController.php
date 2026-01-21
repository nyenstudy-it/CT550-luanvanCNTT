<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CategoryProduct;

class HomeController extends Controller
{
    public function index()
    {
        $categories = CategoryProduct::all();
        return view('pages.home', compact('categories'));
    }

    public function showCategory($id)
    {
        return app(\App\Http\Controllers\Client\CategoryController::class)->show($id);
    }
}
