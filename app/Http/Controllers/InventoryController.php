<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inventory;

class InventoryController extends Controller
{
    public function list()
    {
        $inventories = Inventory::with([
            'variant.product'
        ])->get();

        return view('admin.inventories.list', compact('inventories'));
    }
}
