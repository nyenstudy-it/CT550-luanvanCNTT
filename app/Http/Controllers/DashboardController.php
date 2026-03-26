<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Notification;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // --- Thống kê doanh số ---
        if ($user->role === 'admin') {
            $todaySale    = Order::whereDate('created_at', now())->sum('total_amount');
            $totalSale    = Order::sum('total_amount');
            $todayRevenue = $todaySale;
            $totalRevenue = $totalSale;
            $recentOrders = Order::with('customer')->latest()->take(5)->get();

            $totalCustomers     = Customer::count();
            $newCustomersToday  = Customer::whereDate('created_at', now())->count();
        } else {
            // Bảng orders không có cột staff_id — hiển thị 0 cho nhân viên
            $todaySale    = 0;
            $totalSale    = 0;
            $todayRevenue = 0;
            $totalRevenue = 0;
            $recentOrders = collect();

            $totalCustomers    = 0;
            $newCustomersToday = 0;
        }

        // --- Lấy thông báo admin ---
        $notifications = Notification::orderBy('created_at', 'desc')->get();
        $unreadCount   = $notifications->where('is_read', false)->count();

        // Task demo
        $tasks = [
            ['title' => 'Check new orders', 'done' => false],
            ['title' => 'Update product stock', 'done' => true],
            ['title' => 'Approve staff requests', 'done' => false],
        ];

        return view('admin.dashboard', compact(
            'todaySale',
            'totalSale',
            'todayRevenue',
            'totalRevenue',
            'recentOrders',
            'tasks',
            'totalCustomers',
            'newCustomersToday',
            'notifications',
            'unreadCount'
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
