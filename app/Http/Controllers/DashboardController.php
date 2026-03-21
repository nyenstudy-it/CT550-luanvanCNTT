<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\Customer;

class DashboardController extends Controller
{
    public function index()
    {
        // Lấy người dùng đang đăng nhập
        $user = Auth::user();

        // Thống kê doanh số
        if ($user->role === 'admin') {
            $todaySale    = Order::whereDate('created_at', now())->sum('total_amount');
            $totalSale    = Order::sum('total_amount');
            $todayRevenue = $todaySale;
            $totalRevenue = $totalSale;
            $recentOrders = Order::with('customer')
                ->latest()
                ->take(5)
                ->get();

            // --- Thống kê khách hàng ---
            $totalCustomers     = Customer::count();                      // Tổng số khách hàng
            $newCustomersToday  = Customer::whereDate('created_at', now())->count(); // Khách mới hôm nay
        } else {
            // Staff chỉ xem đơn liên quan
            $staffId      = $user->staff->id ?? null;
            $todaySale    = Order::where('staff_id', $staffId)
                ->whereDate('created_at', now())
                ->sum('total_amount');
            $totalSale    = Order::where('staff_id', $staffId)->sum('total_amount');
            $todayRevenue = $todaySale;
            $totalRevenue = $totalSale;
            $recentOrders = Order::where('staff_id', $staffId)
                ->latest()
                ->take(5)
                ->get();

            // Staff không xem thống kê khách hàng, có thể để 0
            $totalCustomers = 0;
            $newCustomersToday = 0;
        }

        // Task demo
        $tasks = [
            ['title' => 'Check new orders', 'done' => false],
            ['title' => 'Update product stock', 'done' => true],
            ['title' => 'Approve staff requests', 'done' => false],
        ];

        // Truyền dữ liệu vào view
        return view('admin.dashboard', compact(
            'todaySale',
            'totalSale',
            'todayRevenue',
            'totalRevenue',
            'recentOrders',
            'tasks',
            'totalCustomers',
            'newCustomersToday'
        ));
    }
    public function recentOrders()
    {
        // Lấy 5 đơn hàng gần đây, kèm thông tin khách hàng
        $recentOrders = Order::with('customer')
            ->latest()
            ->take(5)
            ->get();

        return $recentOrders;
    }



}
