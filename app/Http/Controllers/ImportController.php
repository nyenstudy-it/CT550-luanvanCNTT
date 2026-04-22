<?php

namespace App\Http\Controllers;

use App\Models\Import;
use App\Models\ImportItem;
use App\Models\ProductVariant;
use App\Models\Inventory;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Staff;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class ImportController extends Controller
{
    public function list(Request $request)
    {
        $query = Import::with(['supplier', 'staff'])
            ->withCount('items')
            ->withSum('items as total_quantity', 'quantity');

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
        $imports = $query->orderBy('import_date', 'desc')
            ->orderBy('id', 'desc')
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
            'items.*.manufacture_date' => 'nullable|date',
            'items.*.expired_at' => 'nullable|date',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $staff = Staff::where('user_id', Auth::id())->first();

        if (!$staff) {
            return back()->with('error', 'Không tìm thấy nhân viên.');
        }

        try {
            $createdImportId = DB::transaction(function () use ($request, $staff) {

                $import = Import::create([
                    'supplier_id' => $request->supplier_id,
                    'staff_id'    => $staff->user_id,
                    'import_date' => $request->import_date,
                    'total_amount' => 0,
                ]);

                $total = 0;

                foreach ($request->items as $item) {
                    $manufactureDate = !empty($item['manufacture_date']) ? $item['manufacture_date'] : null;
                    $expiredAt = !empty($item['expired_at']) ? $item['expired_at'] : null;

                    if ($manufactureDate && $expiredAt && strtotime($expiredAt) <= strtotime($manufactureDate)) {
                        throw new \Exception('Hạn sử dụng phải lớn hơn ngày sản xuất cho từng biến thể nhập.');
                    }

                    $variant = ProductVariant::with('product')
                        ->findOrFail($item['product_variant_id']);

                    if ($variant->product->supplier_id != $request->supplier_id) {
                        throw new \Exception('Biến thể không thuộc nhà phân phối đã chọn');
                    }

                    // Tạo lô nhập (batch) để phục vụ FIFO và quản lý hạn dùng theo từng lần nhập.
                    ImportItem::create([
                        'import_id' => $import->id,
                        'product_variant_id' => $variant->id,
                        'manufacture_date' => $manufactureDate,
                        'expired_at' => $expiredAt,
                        'quantity' => $item['quantity'],
                        'remaining_quantity' => $item['quantity'], // FIFO
                        'unit_price' => $item['unit_price'],
                    ]);

                    // Không lưu ngày sản xuất/hạn dùng ở ProductVariant để tránh ghi đè giữa các lần nhập.
                    // Thông tin ngày được quản lý ở ImportItem (theo batch).
                    // $variant->update([
                    //     'manufacture_date' => $manufactureDate,
                    //     'expired_at' => $expiredAt,
                    // ]);

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

                return $import->id;
            });

            $import = Import::with('supplier')->find($createdImportId);

            if ($import) {
                $recipientIds = Notification::recipientIdsForGroups(['admin', 'warehouse']);

                Notification::createForRecipients($recipientIds, [
                    'type' => 'new_import',
                    'title' => 'Có phiếu nhập kho mới',
                    'content' => 'Phiếu nhập #' . $import->id . ' từ nhà cung cấp ' . ($import->supplier->name ?? 'không xác định') . ' vừa được tạo.',
                    'related_id' => $import->id,
                ]);
            }

            return redirect()
                ->route('admin.imports.list')
                ->with('success', 'Nhập kho thành công');
        } catch (\Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }

    public function getProductsBySupplier($supplierId)
    {
        $products = Product::with(['variants.inventory'])
            ->where('supplier_id', $supplierId)
            ->select('id', 'name')
            ->get();

        $products = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'variant_count' => $product->variants->count(),
                'total_stock' => $product->variants->sum(fn($variant) => $variant->inventory->quantity ?? 0),
            ];
        })->values();

        return response()->json($products);
    }

    public function getVariantsByProduct($productId)
    {
        $variants = ProductVariant::with('inventory')
            ->where('product_id', $productId)
            ->select('id', 'sku', 'color', 'size', 'volume', 'weight', 'price')
            ->addSelect([
                'latest_import_price' => ImportItem::select('unit_price')
                    ->whereColumn('product_variant_id', 'product_variants.id')
                    ->latest('id')
                    ->limit(1),
            ])
            ->get();

        $variants = $variants->map(function ($variant) {
            $parts = collect([
                $variant->sku,
                $variant->color,
                $variant->size ? 'Size ' . $variant->size : null,
                $variant->volume,
                $variant->weight,
            ])->filter()->values();

            return [
                'id' => $variant->id,
                'sku' => $variant->sku,
                'label' => $parts->implode(' | '),
                'stock' => $variant->inventory->quantity ?? 0,
                'price' => (float) $variant->price,
                'latest_import_price' => $variant->latest_import_price !== null
                    ? (float) $variant->latest_import_price
                    : null,
            ];
        })->values();

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
