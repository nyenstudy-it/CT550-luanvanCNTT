<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Discount;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use App\Models\DiscountUsage;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;


class DiscountController extends Controller
{
    public function index(Request $request)
    {
        $query = Discount::query();

        if ($request->filled('code')) {
            $query->where('code', 'like', '%' . $request->code . '%');
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $discounts = $query->orderByDesc('id')->paginate(15);

        return view('admin.discounts.index', compact('discounts'));
    }

    public function create()
    {
        return view('admin.discounts.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50|unique:discounts,code',
            'type' => ['required', Rule::in(['percent', 'fixed'])],
            'value' => 'required|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'min_order_value' => 'nullable|numeric|min:0',
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
        ]);

        Discount::create($request->all());

        return redirect()->route('admin.discounts.index')
            ->with('success', 'Tạo mã giảm giá thành công.');
    }

    public function edit(Discount $discount)
    {
        return view('admin.discounts.edit', compact('discount'));
    }

    public function update(Request $request, Discount $discount)
    {
        $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('discounts', 'code')->ignore($discount->id)],
            'type' => ['required', Rule::in(['percent', 'fixed'])],
            'value' => 'required|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'min_order_value' => 'nullable|numeric|min:0',
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
        ]);

        $discount->update($request->all());

        return redirect()->route('admin.discounts.index')
            ->with('success', 'Cập nhật mã giảm giá thành công.');
    }

    public function destroy(Discount $discount)
    {
        $discount->delete();

        return redirect()->route('admin.discounts.index')
            ->with('success', 'Xóa mã giảm giá thành công.');
    }

    public function customerIndex()
    {
        $customerId = Auth::id();

        $now = Carbon::now();

        // Lấy tất cả discount + info đã sử dụng bởi user này
        $discounts = Discount::with(['usages' => function ($q) use ($customerId) {
            $q->where('user_id', $customerId);
        }])->orderByDesc('id')->get();

        return view('pages.my-discounts', compact('discounts', 'now'));
    }
}
