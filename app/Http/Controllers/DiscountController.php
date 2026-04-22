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
use App\Models\Product;


class DiscountController extends Controller
{
    public function index(Request $request)
    {
        $query = Discount::with('products:id,name');

        if ($request->filled('code')) {
            $escaped = addcslashes($request->code, '\\%_');
            $query->where('code', 'like', '%' . $escaped . '%');
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('scope')) {
            if ($request->scope === 'product') {
                $query->whereHas('products');
            }

            if ($request->scope === 'all') {
                $query->whereDoesntHave('products');
            }
        }

        if ($request->filled('audience')) {
            $query->where('audience', $request->audience);
        }

        if ($request->filled('status')) {
            $now = now();

            if ($request->status === 'active') {
                $query->where(function ($builder) use ($now) {
                    $builder->whereNull('start_at')->orWhere('start_at', '<=', $now);
                })->where(function ($builder) use ($now) {
                    $builder->whereNull('end_at')->orWhere('end_at', '>=', $now);
                })->where(function ($builder) {
                    $builder->whereNull('usage_limit')->orWhereColumn('used_count', '<', 'usage_limit');
                });
            }

            if ($request->status === 'upcoming') {
                $query->whereNotNull('start_at')->where('start_at', '>', $now);
            }

            if ($request->status === 'expired') {
                $query->whereNotNull('end_at')->where('end_at', '<', $now);
            }
        }

        $discounts = $query->orderByDesc('id')->paginate(15);

        $summary = [
            'total' => Discount::count(),
            'active' => Discount::query()
                ->where(function ($builder) {
                    $builder->whereNull('start_at')->orWhere('start_at', '<=', now());
                })
                ->where(function ($builder) {
                    $builder->whereNull('end_at')->orWhere('end_at', '>=', now());
                })
                ->where(function ($builder) {
                    $builder->whereNull('usage_limit')->orWhereColumn('used_count', '<', 'usage_limit');
                })
                ->count(),
            'product_scoped' => Discount::whereHas('products')->count(),
            'global' => Discount::whereDoesntHave('products')->count(),
        ];

        return view('admin.discounts.index', compact('discounts', 'summary'));
    }

    public function create()
    {
        $products = Product::query()->orderBy('name')->select('id', 'name')->get();

        return view('admin.discounts.create', compact('products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50|unique:discounts,code',
            'type' => ['required', Rule::in(['percent', 'fixed'])],
            'value' => 'required|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'audience' => ['required', Rule::in(array_keys(Discount::audienceOptions()))],
            'usage_limit' => 'nullable|integer|min:1',
            'min_order_value' => 'nullable|numeric|min:0',
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
            'scope' => ['required', Rule::in(['all', 'product'])],
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'integer|exists:products,id',
        ]);

        $discount = Discount::create([
            'code' => $request->code,
            'type' => $request->type,
            'value' => $request->value,
            'max_discount' => $request->type === 'percent' ? $request->max_discount : null,
            'audience' => $request->audience,
            'usage_limit' => $request->usage_limit,
            'min_order_value' => $request->min_order_value,
            'start_at' => $request->start_at,
            'end_at' => $request->end_at,
        ]);

        if ($request->scope === 'product') {
            $discount->products()->sync($request->product_ids ?? []);
        }

        return redirect()->route('admin.discounts.index')
            ->with('success', 'Tạo mã giảm giá thành công.');
    }

    public function edit(Discount $discount)
    {
        $products = Product::query()->orderBy('name')->select('id', 'name')->get();
        $discount->load('products:id,name');

        return view('admin.discounts.edit', compact('discount', 'products'));
    }

    public function update(Request $request, Discount $discount)
    {
        $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('discounts', 'code')->ignore($discount->id)],
            'type' => ['required', Rule::in(['percent', 'fixed'])],
            'value' => 'required|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'audience' => ['required', Rule::in(array_keys(Discount::audienceOptions()))],
            'usage_limit' => 'nullable|integer|min:1',
            'min_order_value' => 'nullable|numeric|min:0',
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
            'used_count' => 'nullable|integer|min:0',
            'scope' => ['required', Rule::in(['all', 'product'])],
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'integer|exists:products,id',
        ]);

        $discount->update([
            'code' => $request->code,
            'type' => $request->type,
            'value' => $request->value,
            'max_discount' => $request->type === 'percent' ? $request->max_discount : null,
            'audience' => $request->audience,
            'usage_limit' => $request->usage_limit,
            'used_count' => $request->used_count ?? $discount->used_count,
            'min_order_value' => $request->min_order_value,
            'start_at' => $request->start_at,
            'end_at' => $request->end_at,
        ]);

        if ($request->scope === 'product') {
            $discount->products()->sync($request->product_ids ?? []);
        } else {
            $discount->products()->detach();
        }

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
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        $completedOrdersCount = $user
            ? $user->orders()->where('status', 'completed')->count()
            : null;

        $now = Carbon::now();

        // Lấy tất cả discount + info đã sử dụng bởi user này
        $discounts = Discount::with(['products:id,name', 'usages' => function ($q) use ($customerId) {
            $q->where('user_id', $customerId);
        }])
            ->whereDoesntHave('products')
            ->orderByDesc('id')
            ->get()
            ->filter(fn(Discount $discount) => $discount->isEligibleForCompletedOrdersCount($completedOrdersCount))
            ->values();

        return view('pages.my-discounts', compact('discounts', 'now'));
    }
}
