<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Customer;

class CustomerController extends Controller
{
    private const LOCK_REASON_PRESETS = [
        'spam' => 'Nghi ngờ spam hoặc lạm dụng hệ thống',
        'fraud' => 'Nghi ngờ gian lận trong giao dịch',
        'policy' => 'Vi phạm chính sách sử dụng',
        'chargeback' => 'Phát sinh tranh chấp/hoàn tiền bất thường',
        'security' => 'Rủi ro bảo mật tài khoản',
        'other' => 'Lý do khác',
    ];

    // DANH SÁCH KHÁCH HÀNG
    public function list(Request $request)
    {
        $query = Customer::with('user')
            ->withCount('orders');

        // TÌM KIẾM TÊN / EMAIL
        if ($request->keyword) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->keyword . '%')
                    ->orWhere('email', 'like', '%' . $request->keyword . '%');
            });
        }

        // LỌC TRẠNG THÁI TÀI KHOẢN
        if ($request->status) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('status', $request->status);
            });
        }

        // LỌC NGÀY ĐĂNG KÝ
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $customers = $query
            ->latest()
            ->paginate(10);

        $stats = [
            'total' => Customer::count(),
            'active' => Customer::whereHas('user', fn($q) => $q->where('status', 'active'))->count(),
            'locked' => Customer::whereHas('user', fn($q) => $q->where('status', 'locked'))->count(),
            'new_this_month' => Customer::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];

        $lockReasonPresets = self::LOCK_REASON_PRESETS;

        return view('admin.customers.list', compact('customers', 'stats', 'lockReasonPresets'));
    }



    // XEM CHI TIẾT KHÁCH HÀNG
    public function show($id)
    {
        $customer = Customer::with('user')
            ->withCount('orders')
            ->findOrFail($id);

        return view('admin.customers.show', compact('customer'));
    }



    // KHÓA TÀI KHOẢN
    public function lock(Request $request, $id)
    {
        $request->validate([
            'reason_key' => 'required|string|in:' . implode(',', array_keys(self::LOCK_REASON_PRESETS)),
            'reason_note' => 'nullable|string|max:255',
        ]);

        $user = User::findOrFail($id);

        $reasonKey = $request->input('reason_key');
        $reasonLabel = self::LOCK_REASON_PRESETS[$reasonKey] ?? 'Lý do khác';
        $reasonNote = trim((string) $request->input('reason_note'));

        $lockedReason = $reasonLabel;
        if ($reasonNote !== '') {
            $lockedReason .= ' - ' . $reasonNote;
        }

        $user->update([
            'status' => 'locked',
            'locked_reason' => $lockedReason,
            'locked_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Đã khóa tài khoản khách hàng');
    }



    // MỞ KHÓA TÀI KHOẢN
    public function unlock($id)
    {
        $user = User::findOrFail($id);

        $user->update([
            'status' => 'active',
            'locked_reason' => null,
            'locked_at' => null,
        ]);

        return redirect()->back()->with('success', 'Đã mở khóa tài khoản');
    }



    // XÓA KHÁCH HÀNG
    public function destroy($id)
    {
        $customer = Customer::with('user')->findOrFail($id);

        if ($customer->user) {
            $customer->user->delete();
        }

        $customer->delete();

        return redirect()->back()->with('success', 'Đã xóa khách hàng');
    }
}
