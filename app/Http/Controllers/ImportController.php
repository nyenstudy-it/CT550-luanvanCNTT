<?php

namespace App\Http\Controllers;

use App\Models\Import;
use App\Models\ImportItem;
use App\Models\ProductVariant;
use App\Models\Inventory;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class ImportController extends Controller
{
    public function list(Request $request)
    {
        $query = Import::with(['supplier', 'items']);

        if ($request->supplier_id) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->date_from) {
            $query->whereDate('import_date', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('import_date', '<=', $request->date_to);
        }
        if ($request->min_total) {
            $query->where('total_amount', '>=', $request->min_total);
        }

        if ($request->max_total) {
            $query->where('total_amount', '<=', $request->max_total);
        }
        $imports = $query->orderBy('import_date', 'asc')
            ->orderBy('id', 'asc')
            ->paginate(10)
            ->withQueryString();

        $suppliers = Supplier::all();

        return view('admin.imports.list', compact('imports', 'suppliers'));
    }


    public function create()
    {
        return view('admin.imports.create', [
            'suppliers' => Supplier::all(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'import_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_variant_id' => 'required|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $staff = Staff::where('user_id', Auth::id())->first();

        if (!$staff) {
            return back()->with('error', 'Không tìm thấy nhân viên.');
        }

        try {

            DB::transaction(function () use ($request, $staff) {

                $import = Import::create([
                    'supplier_id' => $request->supplier_id,
                    'staff_id'    => $staff->user_id,
                    'import_date' => $request->import_date,
                    'total_amount' => 0,
                ]);

                $total = 0;

                foreach ($request->items as $item) {

                    $variant = ProductVariant::with('product')
                        ->findOrFail($item['product_variant_id']);

                    if ($variant->product->supplier_id != $request->supplier_id) {
                        throw new \Exception('Biến thể không thuộc nhà phân phối đã chọn');
                    }

                    // 🔥 TẠO BATCH (QUAN TRỌNG)
                    ImportItem::create([
                        'import_id' => $import->id,
                        'product_variant_id' => $variant->id,
                        'quantity' => $item['quantity'],
                        'remaining_quantity' => $item['quantity'], // FIFO
                        'unit_price' => $item['unit_price'],
                    ]);

                    // 🔥 CẬP NHẬT TỔNG TỒN
                    $inventory = Inventory::firstOrCreate(
                        ['product_variant_id' => $variant->id],
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
        } catch (\Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }

    public function getProductsBySupplier($supplierId)
    {
        $products = Product::where('supplier_id', $supplierId)
            ->select('id', 'name')
            ->get();

        return response()->json($products);
    }

    public function getVariantsByProduct($productId)
    {
        $variants = ProductVariant::where('product_id', $productId)
            ->select('id', 'sku')
            ->get();

        return response()->json($variants);
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
