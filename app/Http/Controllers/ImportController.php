<?php

namespace App\Http\Controllers;

use App\Models\Import;
use App\Models\ImportItem;
use App\Models\ProductVariant;
use App\Models\Inventory;
use App\Models\Supplier;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class ImportController extends Controller
{
    public function list()
    {
        $imports = Import::with(['supplier', 'staff'])
            ->orderByDesc('id')
            ->get();

        return view('admin.imports.list', compact('imports'));
    }

    public function create()
    {
        return view('admin.imports.create', [
            'suppliers' => Supplier::all(),
            'variants'  => ProductVariant::with('product')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'import_date' => 'required|date',
            'items.*.product_variant_id' => 'required|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {

            $staff = Staff::where('user_id', Auth::id())->first();
            if (!$staff) {
                abort(403, 'Tài khoản này không phải nhân viên');
            }
            $import = Import::create([
                'supplier_id' => $request->supplier_id,
                'staff_id'    => $staff->user_id,
                'import_date' => $request->import_date,
                'total_amount' => 0,
            ]);

            $total = 0;

            foreach ($request->items as $item) {

                ImportItem::create([
                    'import_id' => $import->id,
                    'product_variant_id' => $item['product_variant_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                ]);

                $inventory = Inventory::firstOrCreate(
                    ['product_variant_id' => $item['product_variant_id']],
                    ['quantity' => 0]
                );

                $inventory->increment('quantity', $item['quantity']);

                $total += $item['quantity'] * $item['unit_price'];
            }

            $import->update([
                'total_amount' => $total
            ]);
        });

        return redirect()
            ->route('admin.imports.list')
            ->with('success', 'Nhập kho thành công');
    }

    public function show($id)
    {
        $import = Import::with([
            'supplier',
            'staff',
            'items.variant.product'
        ])->findOrFail($id);

        return view('admin.imports.show', compact('import'));
    }

    public function print($id)
    {
        $import = Import::with([
            'supplier',
            'staff',
            'items.variant.product'
        ])->findOrFail($id);

        return Pdf::loadView('admin.imports.print', compact('import'))
            ->setPaper('A4', 'portrait')
            ->download('phieu-nhap-' . $import->id . '.pdf');
    }
}
