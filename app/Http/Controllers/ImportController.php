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
    // DANH SÁCH PHIẾU NHẬP
    public function list()
    {
        $imports = Import::with(['supplier', 'staff'])
            ->orderByDesc('id')
            ->get();

        return view('admin.imports.list', compact('imports'));
    }

    // FORM TẠO PHIẾU NHẬP
    public function create()
    {
        return view('admin.imports.create', [
            'suppliers' => Supplier::all(),
            'variants'  => ProductVariant::with('product')->get(),
        ]);
    }

    // LƯU PHIẾU NHẬP + CỘNG KHO
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

            // Lấy staff theo user đang đăng nhập
            $staff = Staff::where('user_id', Auth::id())->first();
            if (!$staff) {
                abort(403, 'Tài khoản này không phải nhân viên');
            }

            // Tạo phiếu nhập
            $import = Import::create([
                'supplier_id' => $request->supplier_id,
                'staff_id'    => $staff->user_id,
                'import_date' => $request->import_date,
                'total_amount' => 0,
            ]);

            $total = 0;

            foreach ($request->items as $item) {

                // Lưu chi tiết phiếu nhập
                ImportItem::create([
                    'import_id' => $import->id,
                    'product_variant_id' => $item['product_variant_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                ]);

                // Cộng tồn kho
                $inventory = Inventory::firstOrCreate(
                    ['product_variant_id' => $item['product_variant_id']],
                    ['quantity' => 0]
                );

                $inventory->increment('quantity', $item['quantity']);

                $total += $item['quantity'] * $item['unit_price'];
            }

            // Cập nhật tổng tiền
            $import->update([
                'total_amount' => $total
            ]);
        });

        return redirect()
            ->route('admin.imports.list')
            ->with('success', 'Nhập kho thành công');
    }

    // CHI TIẾT PHIẾU NHẬP
    public function show($id)
    {
        $import = Import::with([
            'supplier',
            'staff',
            'items.variant.product'
        ])->findOrFail($id);

        return view('admin.imports.show', compact('import'));
    }

    // IN / TẢI PDF PHIẾU NHẬP
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
