<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CategoryProductController extends Controller
{
    public function list()
    {
        return view('admin.categories.list');
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function edit($id)
    {
        return view('admin.categories.edit', compact('id'));
    }
}
