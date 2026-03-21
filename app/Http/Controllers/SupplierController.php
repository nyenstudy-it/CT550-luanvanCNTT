<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Supplier;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::query();

        if ($request->name) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        if ($request->phone) {
            $query->where('phone', 'like', '%' . $request->phone . '%');
        }

        if ($request->address) {
            $query->where('address', 'like', '%' . $request->address . '%');
        }

        $suppliers = $query
            ->orderBy('id', 'asc')
            ->paginate(10);

        return view('admin.suppliers.list', compact('suppliers'));
    }


    public function create()
    {
        return view('admin.suppliers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'description' => 'nullable|string',
        ]);

        Supplier::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address,
            'description' => $request->description,
        ]);

        return redirect()->route('admin.suppliers.list')->with('success', 'Tạo nhà cung cấp thành công.');
    }

    public function edit($id)
    {
        $supplier = Supplier::findOrFail($id);
        return view('admin.suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'description' => 'nullable|string',
        ]);

        $supplier = Supplier::findOrFail($id);
        $supplier->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address,
            'description' => $request->description,
        ]);

        return redirect()->route('admin.suppliers.list')->with('success', 'Cập nhật nhà cung cấp thành công.');
    }

    public function destroy($id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->delete();

        return redirect()->route('admin.suppliers.list')->with('success', 'Xóa nhà cung cấp thành công.');
    }
}
