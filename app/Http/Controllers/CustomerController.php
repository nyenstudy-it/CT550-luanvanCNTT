<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Customer;

class CustomerController extends Controller
{

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

        return view('admin.customers.list', compact('customers'));
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
    public function lock($id)
    {
        $user = User::findOrFail($id);

        $user->update([
            'status' => 'locked'
        ]);

        return redirect()->back()->with('success', 'Đã khóa tài khoản khách hàng');
    }



    // MỞ KHÓA TÀI KHOẢN
    public function unlock($id)
    {
        $user = User::findOrFail($id);

        $user->update([
            'status' => 'active'
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
