<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Customer;
use App\Models\Import;
use App\Models\ImportItem;
use App\Models\Payment;
use App\Models\Review;
use App\Models\Salary;
use App\Models\Notification;
use App\Models\Wishlist;
use App\Models\User;
use App\Models\Attendance;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $isCashierStaff = $user->role === 'staff' && ($user->staff?->position === 'cashier');
        $today = Carbon::today();
        [$monthStart, $monthEnd, $selectedMonth, $monthLabel] = $this->resolveMonthRange($request->input('month'));

        // --- Thống kê doanh số ---
        if ($user->role === 'admin' || $isCashierStaff) {
            // FIX: Calculate revenue based on order COMPLETION date (updated_at), not creation date
            $paidOrdersQuery = Order::query()
                ->where('status', 'completed')
                ->whereHas('payment', function ($query) {
                    $query->where('status', 'paid');
                });

            // TODAY'S REVENUE: All completed & paid orders updated today
            $todayRevenue = (clone $paidOrdersQuery)
                ->whereDate('orders.updated_at', $today)
                ->sum('total_amount');

            // TOTAL REVENUE
            $totalSale = (clone $paidOrdersQuery)
                ->sum('total_amount');

            $todayRefundAmount = Payment::query()
                ->where('refund_status', 'completed')
                ->whereDate('refund_at', $today)
                ->sum('refund_amount');

            $totalRefundAmount = Payment::query()
                ->where('refund_status', 'completed')
                ->sum('refund_amount');

            $totalRevenue = $totalSale - $totalRefundAmount;
            $totalRevenue = $totalSale - $totalRefundAmount;
            $weekRevenue = $this->calculateNetRevenueByRange($today->copy()->startOfWeek(), $today->copy()->endOfWeek());
            $monthRevenue = $this->calculateNetRevenueByRange($monthStart, $monthEnd);

            $recentOrders = Order::with('customer.user')
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->latest()
                ->take(5)
                ->get();
            $newOrdersToday = Order::whereDate('created_at', $today)->count();
            $newOrdersThisMonth = Order::whereBetween('created_at', [$monthStart, $monthEnd])->count();

            $accountStats = [
                'new_accounts' => User::where('role', 'customer')->whereBetween('created_at', [$monthStart, $monthEnd])->count(),
                'locked_accounts' => User::where('role', 'customer')->where('status', 'locked')->count(),
                'active_accounts' => User::where('role', 'customer')->where('status', 'active')->count(),
            ];

            $totalCustomers     = Customer::count();
            $newCustomersToday  = Customer::whereDate('created_at', $today)->count();
            $newCustomersThisMonth  = Customer::whereBetween('created_at', [$monthStart, $monthEnd])->count();
            $wishlistInteractions = Wishlist::whereBetween('created_at', [$monthStart, $monthEnd])->count();
            $recentReviews = Review::query()
                ->with(['product', 'customer.user'])
                ->approved()
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->latest()
                ->take(5)
                ->get();
            $reviewsToday = Review::query()->approved()->whereDate('created_at', $today)->count();
            $reviewsThisMonth = Review::query()->approved()->whereBetween('created_at', [$monthStart, $monthEnd])->count();

            // --- Tổng hợp tài chính ---
            $totalImportCost = Import::sum('total_amount');
            $totalSalaryCost = Salary::sum('total_salary');
            $totalShippingCost = (clone $paidOrdersQuery)->sum('shipping_fee');

            $totalCogs = DB::table('order_items')
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->join('payments', 'payments.order_id', '=', 'orders.id')
                ->where('payments.status', 'paid')
                ->sum(DB::raw('COALESCE(order_items.cost_price, 0) * order_items.quantity'));

            $grossProfit = $totalRevenue - $totalCogs;
            $netProfitEstimate = $grossProfit - $totalSalaryCost - $totalShippingCost;

            // --- Khách hàng top ---
            $topCustomersByOrders = DB::table('orders')
                ->join('payments', 'payments.order_id', '=', 'orders.id')
                ->join('customers', 'customers.id', '=', 'orders.customer_id')
                ->join('users', 'users.id', '=', 'customers.user_id')
                ->where('payments.status', 'paid')
                ->whereBetween('orders.created_at', [$monthStart, $monthEnd])
                ->selectRaw("orders.customer_id, users.name as customer_name, COUNT(orders.id) as orders_count, SUM(orders.total_amount) as total_spent")
                ->groupBy('orders.customer_id', 'users.name')
                ->orderByDesc('orders_count')
                ->orderByDesc('total_spent')
                ->limit(3)
                ->get();

            $topCustomersByValue = DB::table('orders')
                ->join('payments', 'payments.order_id', '=', 'orders.id')
                ->join('customers', 'customers.id', '=', 'orders.customer_id')
                ->join('users', 'users.id', '=', 'customers.user_id')
                ->where('payments.status', 'paid')
                ->whereBetween('orders.created_at', [$monthStart, $monthEnd])
                ->selectRaw("orders.customer_id, users.name as customer_name, COUNT(orders.id) as orders_count, SUM(orders.total_amount) as total_spent")
                ->groupBy('orders.customer_id', 'users.name')
                ->orderByDesc('total_spent')
                ->orderByDesc('orders_count')
                ->limit(3)
                ->get();

            $highestValueOrder = Order::with('customer.user')
                ->whereHas('payment', function ($query) {
                    $query->where('status', 'paid');
                })
                ->orderByDesc('total_amount')
                ->first();

            // --- Sản phẩm bán chạy ---
            $topProductsWeek = collect();
            $topProductsMonth = $this->getTopProductsByPeriod($monthStart, $monthEnd);

            $bestSellerTopRatedProducts = DB::table('products')
                ->leftJoin('product_variants', 'product_variants.product_id', '=', 'products.id')
                ->leftJoin('order_items', 'order_items.product_variant_id', '=', 'product_variants.id')
                ->leftJoin('orders', function ($join) use ($monthStart, $monthEnd) {
                    $join->on('orders.id', '=', 'order_items.order_id')
                        ->whereBetween('orders.created_at', [$monthStart, $monthEnd]);
                })
                ->leftJoin('payments', function ($join) {
                    $join->on('payments.order_id', '=', 'orders.id')
                        ->where('payments.status', '=', 'paid');
                })
                ->leftJoin('reviews', function ($join) {
                    $join->on('reviews.product_id', '=', 'products.id')
                        ->where('reviews.status', '=', 'approved');
                })
                ->where('products.status', 'active')
                ->selectRaw('products.id as product_id, products.name as product_name')
                ->selectRaw('SUM(CASE WHEN payments.id IS NOT NULL THEN COALESCE(order_items.quantity, 0) ELSE 0 END) as sold_qty')
                ->selectRaw('ROUND(AVG(reviews.rating), 1) as avg_rating')
                ->groupBy('products.id', 'products.name')
                ->havingRaw('sold_qty > 0')
                ->orderByDesc('sold_qty')
                ->orderByDesc('avg_rating')
                ->limit(8)
                ->get();

            // --- Thu ngân / hóa đơn ---
            $invoiceSummary = [
                'total_invoices' => Order::whereBetween('created_at', [$monthStart, $monthEnd])->count(),
                'paid_invoices' => Payment::where('status', 'paid')->whereBetween('paid_at', [$monthStart, $monthEnd])->count(),
                'refund_invoices' => Payment::where('refund_status', 'completed')->whereBetween('refund_at', [$monthStart, $monthEnd])->count(),
                'today_paid_invoices' => Payment::where('status', 'paid')->whereDate('paid_at', $today)->count(),
                'avg_invoice_value' => Payment::where('status', 'paid')->whereBetween('paid_at', [$monthStart, $monthEnd])->avg('amount') ?? 0,
                'total_shipping_fee' => (clone $paidOrdersQuery)->whereBetween('orders.updated_at', [$monthStart, $monthEnd])->sum('shipping_fee'),
                'total_discount_amount' => (clone $paidOrdersQuery)->whereBetween('orders.updated_at', [$monthStart, $monthEnd])->sum('discount_amount'),
            ];

            // --- Nhập hàng theo nhà cung cấp & danh mục ---
            $importsBySupplier = DB::table('imports')
                ->join('suppliers', 'suppliers.id', '=', 'imports.supplier_id')
                ->join('import_items', 'import_items.import_id', '=', 'imports.id')
                ->whereBetween('imports.import_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                ->selectRaw('suppliers.name as supplier_name, COUNT(DISTINCT imports.id) as import_count, SUM(import_items.quantity * import_items.unit_price) as total_value')
                ->groupBy('suppliers.name')
                ->orderByDesc('total_value')
                ->limit(5)
                ->get();

            $importsByCategory = DB::table('import_items')
                ->join('imports', 'imports.id', '=', 'import_items.import_id')
                ->join('product_variants', 'product_variants.id', '=', 'import_items.product_variant_id')
                ->join('products', 'products.id', '=', 'product_variants.product_id')
                ->leftJoin('category_products', 'category_products.id', '=', 'products.category_id')
                ->whereBetween('imports.import_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                ->selectRaw('COALESCE(category_products.name, "Khác") as category_name, SUM(import_items.quantity) as total_qty, SUM(import_items.quantity * import_items.unit_price) as total_value')
                ->groupBy('category_name')
                ->orderByDesc('total_value')
                ->limit(5)
                ->get();

            // --- Thanh lý hàng sắp hết hạn / hết hạn ---
            $expiringSoonDays = 30;
            $expiringSoonRate = 0.8;
            $expiredRate = 0.4;

            $liquidationExpiringSoon = DB::table('import_items')
                ->join('product_variants', 'product_variants.id', '=', 'import_items.product_variant_id')
                ->where('import_items.remaining_quantity', '>', 0)
                ->whereDate('product_variants.expired_at', '>=', $today)
                ->whereDate('product_variants.expired_at', '<=', $today->copy()->addDays($expiringSoonDays))
                ->selectRaw('SUM(import_items.remaining_quantity) as total_qty')
                ->selectRaw('SUM(import_items.remaining_quantity * import_items.unit_price) as total_cost')
                ->selectRaw('SUM(import_items.remaining_quantity * product_variants.price) as total_retail_value')
                ->first();

            $liquidationExpired = DB::table('import_items')
                ->join('product_variants', 'product_variants.id', '=', 'import_items.product_variant_id')
                ->where('import_items.remaining_quantity', '>', 0)
                ->whereDate('product_variants.expired_at', '<', $today)
                ->selectRaw('SUM(import_items.remaining_quantity) as total_qty')
                ->selectRaw('SUM(import_items.remaining_quantity * import_items.unit_price) as total_cost')
                ->selectRaw('SUM(import_items.remaining_quantity * product_variants.price) as total_retail_value')
                ->first();

            $liquidationSummary = [
                'expiring_days' => $expiringSoonDays,
                'expiring_soon_qty' => (int) ($liquidationExpiringSoon->total_qty ?? 0),
                'expiring_soon_cost' => (float) ($liquidationExpiringSoon->total_cost ?? 0),
                'expiring_soon_retail' => (float) ($liquidationExpiringSoon->total_retail_value ?? 0),
                'expiring_soon_recovery' => (float) ($liquidationExpiringSoon->total_retail_value ?? 0) * $expiringSoonRate,
                'expiring_soon_rate' => $expiringSoonRate,
                'expired_qty' => (int) ($liquidationExpired->total_qty ?? 0),
                'expired_cost' => (float) ($liquidationExpired->total_cost ?? 0),
                'expired_retail' => (float) ($liquidationExpired->total_retail_value ?? 0),
                'expired_recovery' => (float) ($liquidationExpired->total_retail_value ?? 0) * $expiredRate,
                'expired_rate' => $expiredRate,
            ];

            $liquidationSummary['expiring_soon_profit'] = $liquidationSummary['expiring_soon_recovery'] - $liquidationSummary['expiring_soon_cost'];
            $liquidationSummary['expired_profit'] = $liquidationSummary['expired_recovery'] - $liquidationSummary['expired_cost'];

            $customersWithPaidOrders = DB::table('orders')
                ->join('payments', 'payments.order_id', '=', 'orders.id')
                ->where('payments.status', 'paid')
                ->whereNotNull('orders.customer_id')
                ->whereBetween('orders.created_at', [$monthStart, $monthEnd])
                ->distinct('orders.customer_id')
                ->count('orders.customer_id');

            $repeatCustomers = DB::table('orders')
                ->join('payments', 'payments.order_id', '=', 'orders.id')
                ->where('payments.status', 'paid')
                ->whereNotNull('orders.customer_id')
                ->whereBetween('orders.created_at', [$monthStart, $monthEnd])
                ->select('orders.customer_id')
                ->groupBy('orders.customer_id')
                ->havingRaw('COUNT(orders.id) >= 2')
                ->get()
                ->count();

            $returningCustomerRate = $customersWithPaidOrders > 0
                ? round(($repeatCustomers / $customersWithPaidOrders) * 100, 2)
                : 0;

            $categoryMix = DB::table('order_items')
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->join('payments', 'payments.order_id', '=', 'orders.id')
                ->join('product_variants', 'product_variants.id', '=', 'order_items.product_variant_id')
                ->join('products', 'products.id', '=', 'product_variants.product_id')
                ->leftJoin('category_products', 'category_products.id', '=', 'products.category_id')
                ->where('payments.status', 'paid')
                ->whereBetween('orders.created_at', [$monthStart, $monthEnd])
                ->selectRaw('COALESCE(category_products.name, "Khác") as category_name, SUM(order_items.quantity) as sold_qty')
                ->groupBy('category_name')
                ->orderByDesc('sold_qty')
                ->limit(6)
                ->get();

            $productCountByCategory = DB::table('products')
                ->leftJoin('category_products', 'category_products.id', '=', 'products.category_id')
                ->selectRaw('COALESCE(category_products.name, "Khác") as category_name, COUNT(products.id) as product_count')
                ->groupBy('category_name')
                ->orderByDesc('product_count')
                ->limit(8)
                ->get();

            $orderStatusMap = Order::query()
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status');

            $orderStatusSummary = [
                'pending' => (int) ($orderStatusMap['pending'] ?? 0),
                'confirmed' => (int) ($orderStatusMap['confirmed'] ?? 0),
                'shipping' => (int) ($orderStatusMap['shipping'] ?? 0),
                'completed' => (int) ($orderStatusMap['completed'] ?? 0),
                'cancelled' => (int) ($orderStatusMap['cancelled'] ?? 0),
                'refund_requested' => (int) ($orderStatusMap['refund_requested'] ?? 0),
                'refunded' => (int) ($orderStatusMap['refunded'] ?? 0),
            ];

            $totalOrderCount = array_sum($orderStatusSummary);
            $cancelOrRefundRate = $totalOrderCount > 0
                ? round((($orderStatusSummary['cancelled'] + $orderStatusSummary['refunded']) / $totalOrderCount) * 100, 2)
                : 0;
            $cancelRate = $totalOrderCount > 0
                ? round(($orderStatusSummary['cancelled'] / $totalOrderCount) * 100, 2)
                : 0;

            $cancellationByCustomer = DB::table('order_cancellations')
                ->join('orders', 'orders.id', '=', 'order_cancellations.order_id')
                ->leftJoin('customers', 'customers.id', '=', 'orders.customer_id')
                ->leftJoin('users', 'users.id', '=', 'customers.user_id')
                ->where('order_cancellations.cancelled_by', 'customer')
                ->whereBetween('order_cancellations.cancelled_at', [$monthStart, $monthEnd])
                ->whereNotNull('orders.customer_id')
                ->selectRaw('orders.customer_id, COALESCE(MAX(users.name), "Khách vãng lai") as customer_name, COUNT(order_cancellations.id) as cancelled_count')
                ->groupBy('orders.customer_id')
                ->orderByDesc('cancelled_count')
                ->limit(5)
                ->get();

            $topCanceller = $cancellationByCustomer->first();
            $topCancellerAlert = $topCanceller && (int) $topCanceller->cancelled_count >= 3
                ? 'Cảnh báo: khách ' . $topCanceller->customer_name . ' đã hủy ' . $topCanceller->cancelled_count . ' đơn trong tháng.'
                : null;

            $totalPaidOrdersMonth = DB::table('orders')
                ->join('payments', 'payments.order_id', '=', 'orders.id')
                ->where('payments.status', 'paid')
                ->whereBetween('orders.created_at', [$monthStart, $monthEnd])
                ->count();

            $voucherCampaignStats = DB::table('orders')
                ->join('payments', 'payments.order_id', '=', 'orders.id')
                ->where('payments.status', 'paid')
                ->whereBetween('orders.created_at', [$monthStart, $monthEnd])
                ->whereNotNull('orders.discount_code')
                ->where('orders.discount_code', '<>', '')
                ->selectRaw('orders.discount_code as campaign_code, COUNT(*) as applied_orders, SUM(orders.discount_amount) as total_discount')
                ->groupBy('orders.discount_code')
                ->orderByDesc('applied_orders')
                ->get()
                ->map(function ($item) use ($totalPaidOrdersMonth) {
                    $item->apply_rate = $totalPaidOrdersMonth > 0
                        ? round(($item->applied_orders / $totalPaidOrdersMonth) * 100, 2)
                        : 0;
                    return $item;
                });

            $expiringProductsToPush = DB::table('import_items')
                ->join('product_variants', 'product_variants.id', '=', 'import_items.product_variant_id')
                ->join('products', 'products.id', '=', 'product_variants.product_id')
                ->where('import_items.remaining_quantity', '>', 0)
                ->whereBetween('product_variants.expired_at', [$monthStart->toDateString(), $monthEnd->toDateString()])
                ->selectRaw('products.name as product_name, product_variants.sku as sku, product_variants.expired_at as expired_at, SUM(import_items.remaining_quantity) as remaining_qty')
                ->groupBy('products.name', 'product_variants.sku', 'product_variants.expired_at')
                ->orderBy('product_variants.expired_at')
                ->limit(8)
                ->get();

            $financeSummary = [
                'gross_sale' => $totalSale,
                'refund_amount' => $totalRefundAmount,
                'net_revenue' => $totalRevenue,
                'import_cost' => $totalImportCost,
                'salary_cost' => $totalSalaryCost,
                'shipping_cost' => $totalShippingCost,
                'cogs' => $totalCogs,
                'gross_profit' => $grossProfit,
                'net_profit_estimate' => $netProfitEstimate,
            ];

            $dashboardCharts = [
                'daily_revenue' => $this->buildDailyRevenueSeries($monthStart, $monthEnd),
                'order_status' => $this->buildOrderStatusSeries($monthStart, $monthEnd),
                'category_mix' => [
                    'labels' => $categoryMix->pluck('category_name')->values(),
                    'data' => $categoryMix->pluck('sold_qty')->map(fn($v) => (int) $v)->values(),
                ],
                'product_count_by_category' => [
                    'labels' => $productCountByCategory->pluck('category_name')->values(),
                    'data' => $productCountByCategory->pluck('product_count')->map(fn($v) => (int) $v)->values(),
                ],
            ];
        } else {
            // Bảng orders không có cột staff_id — hiển thị 0 cho nhân viên
            $todaySale    = 0;
            $totalSale    = 0;
            $todayRevenue = 0;
            $totalRevenue = 0;
            $weekRevenue = 0;
            $monthRevenue = 0;
            $recentOrders = collect();
            $newOrdersToday = 0;
            $newOrdersThisMonth = 0;

            $totalCustomers    = 0;
            $newCustomersToday = 0;
            $newCustomersThisMonth = 0;
            $wishlistInteractions = 0;
            $recentReviews = collect();
            $reviewsToday = 0;
            $reviewsThisMonth = 0;
            $returningCustomerRate = 0;
            $cancelOrRefundRate = 0;
            $cancelRate = 0;
            $voucherCampaignStats = collect();
            $expiringProductsToPush = collect();
            $cancellationByCustomer = collect();
            $topCancellerAlert = null;
            $bestSellerTopRatedProducts = collect();
            $accountStats = [
                'new_accounts' => 0,
                'locked_accounts' => 0,
                'active_accounts' => 0,
            ];
            $orderStatusSummary = [
                'pending' => 0,
                'confirmed' => 0,
                'shipping' => 0,
                'completed' => 0,
                'cancelled' => 0,
                'refund_requested' => 0,
                'refunded' => 0,
            ];

            $financeSummary = [
                'gross_sale' => 0,
                'refund_amount' => 0,
                'net_revenue' => 0,
                'import_cost' => 0,
                'salary_cost' => 0,
                'shipping_cost' => 0,
                'cogs' => 0,
                'gross_profit' => 0,
                'net_profit_estimate' => 0,
            ];

            $topCustomersByOrders = collect();
            $topCustomersByValue = collect();
            $highestValueOrder = null;

            $topProductsWeek = collect();
            $topProductsMonth = collect();

            $invoiceSummary = [
                'total_invoices' => 0,
                'paid_invoices' => 0,
                'refund_invoices' => 0,
                'today_paid_invoices' => 0,
                'avg_invoice_value' => 0,
                'total_shipping_fee' => 0,
                'total_discount_amount' => 0,
            ];

            $liquidationSummary = [
                'expiring_days' => 30,
                'expiring_soon_qty' => 0,
                'expiring_soon_cost' => 0,
                'expiring_soon_retail' => 0,
                'expiring_soon_recovery' => 0,
                'expiring_soon_rate' => 0.8,
                'expiring_soon_profit' => 0,
                'expired_qty' => 0,
                'expired_cost' => 0,
                'expired_retail' => 0,
                'expired_recovery' => 0,
                'expired_rate' => 0.4,
                'expired_profit' => 0,
            ];

            $dashboardCharts = [
                'daily_revenue' => ['labels' => [], 'values' => []],
                'order_status' => ['labels' => [], 'data' => []],
                'category_mix' => ['labels' => [], 'data' => []],
                'product_count_by_category' => ['labels' => [], 'data' => []],
            ];

            $productCountByCategory = collect();

            // Initialize imports data (empty for non-admin)
            $importsBySupplier = collect();
            $importsByCategory = collect();
            $totalImportCost = 0;

            $thisWeekProfitDetails = [
                'net_profit' => 0,
                'gross_profit' => 0,
                'cogs' => 0,
                'shipping_cost' => 0,
                'staff_cost' => 0,
                'discounts' => 0,
                'inventory_shrinkage' => 0,
            ];

            $thisMonthProfitDetails = [
                'net_profit' => 0,
                'gross_profit' => 0,
                'cogs' => 0,
                'shipping_cost' => 0,
                'staff_cost' => 0,
                'discounts' => 0,
                'inventory_shrinkage' => 0,
            ];

            // Initialize staff & labor stats
            $thisWeekSalary = 0;
            $thisWeekHours = 0;
            $thisWeekRevenue = 0;
            $thisWeekProfit = 0;
            $thisMonthSalary = 0;
            $thisMonthHours = 0;
            $thisMonthRevenue = 0;
            $thisMonthProfit = 0;
            $weeklyComparison = [];
            $monthlyComparison = [];
        }

        // --- Lấy thông báo cho dropdown của người dùng hiện tại ---
        $notifications = Notification::query()
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();
        $unreadCount = Notification::query()
            ->where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        // Task demo
        $tasks = [
            ['title' => 'Check new orders', 'done' => false],
            ['title' => 'Update product stock', 'done' => true],
            ['title' => 'Approve staff requests', 'done' => false],
        ];

        // --- Thống kê lương & doanh thu theo tuần --- 
        $startOfWeek = $today->copy()->startOfWeek();
        $endOfWeek = $today->copy()->endOfWeek();
        $weekDateRange = $startOfWeek->format('d/m/Y') . ' - ' . $endOfWeek->format('d/m/Y'); // Khoảng thời gian tuần

        // Tuần này - Lấy lương cuối (đã cộng thưởng/phạt)
        $weekShifts = Attendance::whereDate('work_date', '>=', $startOfWeek)
            ->whereDate('work_date', '<=', $endOfWeek)
            ->count();
        $monthShifts = Attendance::whereRaw('MONTH(work_date) = ? AND YEAR(work_date) = ?', [$startOfWeek->month, $startOfWeek->year])
            ->count();
        $salaryRatio = ($monthShifts > 0) ? ($weekShifts / $monthShifts) : 0;

        $monthSalaryCost = Salary::where('month', $startOfWeek->month)
            ->where('year', $startOfWeek->year)
            ->sum('final_salary') ?? 0;
        $thisWeekSalary = $monthSalaryCost * $salaryRatio;

        $thisWeekMinutes = Attendance::whereDate('work_date', '>=', $startOfWeek)
            ->whereDate('work_date', '<=', $endOfWeek)
            ->sum('worked_minutes');
        $thisWeekHours = $thisWeekMinutes ? round($thisWeekMinutes / 60, 2) : 0;

        $thisWeekRevenue = $weekRevenue; // Doanh thu tuần này (từ orders)
        $thisWeekProfitDetails = $this->calculateComprehensiveProfit($startOfWeek, $endOfWeek);
        $thisWeekProfit = $thisWeekProfitDetails['net_profit'];

        // Tháng này - Lấy lương cuối (đã cộng thưởng/phạt)
        $monthDateRange = $monthStart->format('d/m/Y') . ' - ' . $monthEnd->format('d/m/Y');
        $thisMonthSalary = Salary::where('month', $monthStart->month)
            ->where('year', $monthStart->year)
            ->sum('final_salary') ?? 0;

        $thisMonthMinutes = Attendance::whereDate('work_date', '>=', $monthStart)
            ->whereDate('work_date', '<=', $monthEnd)
            ->sum('worked_minutes');
        $thisMonthHours = $thisMonthMinutes ? round($thisMonthMinutes / 60, 2) : 0;

        $thisMonthRevenue = $monthRevenue; // Doanh thu tháng này
        $thisMonthProfitDetails = $this->calculateComprehensiveProfit($monthStart, $monthEnd);
        $thisMonthProfit = $thisMonthProfitDetails['net_profit'];

        // Lịch sử 4 tuần gần nhất
        $weeklyComparison = [];
        for ($i = 3; $i >= 0; $i--) {
            $weekStart = $today->copy()->subWeeks($i)->startOfWeek();
            $weekEnd = $today->copy()->subWeeks($i)->endOfWeek();

            $weeklySalary = Salary::where('month', $weekStart->month)
                ->where('year', $weekStart->year)
                ->sum('final_salary');

            // Tính tỉ lệ ca làm trong tuần
            $weeklyShifts = Attendance::whereDate('work_date', '>=', $weekStart)
                ->whereDate('work_date', '<=', $weekEnd)
                ->count();
            $totalMonthlyShifts = Attendance::whereRaw('MONTH(work_date) = ? AND YEAR(work_date) = ?', [$weekStart->month, $weekStart->year])
                ->count();
            $weekShiftRatio = ($totalMonthlyShifts > 0) ? ($weeklyShifts / $totalMonthlyShifts) : 0;
            $weeklySalary = $weeklySalary * $weekShiftRatio;

            $weeklyMinutes = Attendance::whereDate('work_date', '>=', $weekStart)
                ->whereDate('work_date', '<=', $weekEnd)
                ->sum('worked_minutes');
            $weeklyHours = $weeklyMinutes ? round($weeklyMinutes / 60, 2) : 0;

            $weeklyRevenue = $this->calculateNetRevenueByRange($weekStart, $weekEnd);
            $weeklyProfitDetails = $this->calculateComprehensiveProfit($weekStart, $weekEnd);

            $weeklyShifts = Attendance::whereDate('work_date', '>=', $weekStart)
                ->whereDate('work_date', '<=', $weekEnd)
                ->count();

            $weeklyComparison[] = [
                'week' => $weekStart->format('d/m') . ' - ' . $weekEnd->format('d/m'),
                'salary' => $weeklySalary,
                'hours' => $weeklyHours,
                'revenue' => $weeklyRevenue,
                'profit' => $weeklyProfitDetails['net_profit'],
                'gross_profit' => $weeklyProfitDetails['gross_profit'],
                'cogs' => $weeklyProfitDetails['cogs'],
                'shipping_cost' => $weeklyProfitDetails['shipping_cost'],
                'staff_cost' => $weeklyProfitDetails['staff_cost'],
                'discounts' => $weeklyProfitDetails['discounts'],
                'inventory_shrinkage' => $weeklyProfitDetails['inventory_shrinkage'],
                'shifts' => $weeklyShifts,
                'profit_margin' => $weeklyRevenue > 0 ? round(($weeklyProfitDetails['net_profit'] / $weeklyRevenue) * 100, 2) : 0,
            ];
        }

        // Lịch sử 12 tháng gần nhất
        $monthlyComparison = [];
        for ($i = 11; $i >= 0; $i--) {
            $monthStart_iter = $today->copy()->subMonths($i)->startOfMonth();
            $monthEnd_iter = $today->copy()->subMonths($i)->endOfMonth();
            $monthLabel_iter = $monthStart_iter->format('m/Y');

            $monthlySalary = Salary::where('month', $monthStart_iter->month)
                ->where('year', $monthStart_iter->year)
                ->sum('final_salary') ?? 0;

            $monthlyMinutes = Attendance::whereDate('work_date', '>=', $monthStart_iter)
                ->whereDate('work_date', '<=', $monthEnd_iter)
                ->sum('worked_minutes');
            $monthlyHours = $monthlyMinutes ? round($monthlyMinutes / 60, 2) : 0;

            $monthlyRevenue = $this->calculateNetRevenueByRange($monthStart_iter, $monthEnd_iter);
            $monthlyProfitDetails = $this->calculateComprehensiveProfit($monthStart_iter, $monthEnd_iter);

            $monthlyShifts = Attendance::whereDate('work_date', '>=', $monthStart_iter)
                ->whereDate('work_date', '<=', $monthEnd_iter)
                ->count();

            $monthlyComparison[] = [
                'month' => $monthLabel_iter,
                'salary' => $monthlySalary,
                'hours' => $monthlyHours,
                'revenue' => $monthlyRevenue,
                'profit' => $monthlyProfitDetails['net_profit'],
                'gross_profit' => $monthlyProfitDetails['gross_profit'],
                'cogs' => $monthlyProfitDetails['cogs'],
                'shipping_cost' => $monthlyProfitDetails['shipping_cost'],
                'staff_cost' => $monthlyProfitDetails['staff_cost'],
                'discounts' => $monthlyProfitDetails['discounts'],
                'inventory_shrinkage' => $monthlyProfitDetails['inventory_shrinkage'],
                'shifts' => $monthlyShifts,
                'profit_margin' => $monthlyRevenue > 0 ? round(($monthlyProfitDetails['net_profit'] / $monthlyRevenue) * 100, 2) : 0,
            ];
        }

        // ===== ƯU TIÊN 4: Dashboard alerts - Inventory Writeoff Metrics =====
        $writeoffMetrics = [
            'total_writeoff_cost_month' => DB::table('inventory_writeoffs')
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->sum('total_cost'),
            'total_writeoff_cost_week' => DB::table('inventory_writeoffs')
                ->whereBetween('created_at', [$today->copy()->startOfWeek(), $today->copy()->endOfWeek()])
                ->sum('total_cost'),
            'total_writeoff_cost_today' => DB::table('inventory_writeoffs')
                ->whereDate('created_at', $today)
                ->sum('total_cost'),
            'writeoff_count_month' => DB::table('inventory_writeoffs')
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->sum('quantity_written_off'),
            'writeoff_count_today' => DB::table('inventory_writeoffs')
                ->whereDate('created_at', $today)
                ->sum('quantity_written_off'),
            'writeoff_by_reason' => DB::table('inventory_writeoffs')
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->selectRaw('reason, COUNT(*) as count, SUM(total_cost) as cost')
                ->groupBy('reason')
                ->get()
                ->map(function ($item) {
                    $reasons = [
                        'expired' => 'Hết hạn',
                        'damaged' => 'Hư hỏng',
                        'broken_packaging' => 'Bao bì phá',
                        'water_damage' => 'Ẩm/Mốc/Nước',
                        'manufacturing_flaw' => 'Lỗi sản xuất',
                        'color_fading' => 'Phai màu',
                        'contaminated' => 'Bị nhiễm bẩn',
                        'stock_adjustment' => 'Điều chỉnh kho',
                        'other' => 'Khác',
                    ];
                    return [
                        'reason' => $reasons[$item->reason] ?? $item->reason,
                        'count' => $item->count,
                        'cost' => $item->cost,
                    ];
                }),
        ];

        return view('admin.dashboard', compact(
            'selectedMonth',
            'monthLabel',
            'todayRevenue',
            'totalRevenue',
            'weekRevenue',
            'monthRevenue',
            'recentOrders',
            'newOrdersToday',
            'newOrdersThisMonth',
            'tasks',
            'totalCustomers',
            'newCustomersToday',
            'newCustomersThisMonth',
            'wishlistInteractions',
            'recentReviews',
            'reviewsToday',
            'reviewsThisMonth',
            'returningCustomerRate',
            'orderStatusSummary',
            'cancelOrRefundRate',
            'cancelRate',
            'cancellationByCustomer',
            'topCancellerAlert',
            'accountStats',
            'voucherCampaignStats',
            'bestSellerTopRatedProducts',
            'expiringProductsToPush',
            'notifications',
            'unreadCount',
            'financeSummary',
            'topCustomersByOrders',
            'topCustomersByValue',
            'highestValueOrder',
            'topProductsWeek',
            'topProductsMonth',
            'invoiceSummary',
            'liquidationSummary',
            'importsBySupplier',
            'importsByCategory',
            'totalImportCost',
            'dashboardCharts',
            'productCountByCategory',
            'thisWeekSalary',
            'thisWeekHours',
            'thisWeekRevenue',
            'thisWeekProfit',
            'thisWeekProfitDetails',
            'thisMonthSalary',
            'thisMonthHours',
            'thisMonthRevenue',
            'thisMonthProfit',
            'thisMonthProfitDetails',
            'weeklyComparison',
            'monthlyComparison',
            'weekDateRange',
            'monthDateRange',
            'writeoffMetrics'
        ));
    }

    private function calculateNetRevenueByRange(Carbon $start, Carbon $end): float
    {
        // FIX: Dùng payments.paid_at (khi tiền thực tế được nhận)
        // Đảm bảo khớp với COGS calculation
        $paid = DB::table('orders')
            ->join('payments', 'payments.order_id', '=', 'orders.id')
            ->where('orders.status', 'completed')
            ->where('payments.status', 'paid')
            ->whereBetween('payments.paid_at', [$start->startOfDay(), $end->endOfDay()])
            ->sum('orders.total_amount');

        $refund = Payment::query()
            ->join('orders', 'orders.id', '=', 'payments.order_id')
            ->where('payments.refund_status', 'completed')
            ->whereBetween('payments.refund_at', [$start->startOfDay(), $end->endOfDay()])
            ->sum('payments.refund_amount');

        return (float) ($paid - $refund);
    }

    private function calculateComprehensiveProfit(Carbon $start, Carbon $end): array
    {
        // 1. Doanh Thu = Tổng đơn đã thanh toán - Hoàn tiền (đã bao gồm giảm giá)
        $revenue = $this->calculateNetRevenueByRange($start, $end);

        // 2. FIX ISSUE #1: Tính Giảm giá RIÊNG (để tracking, không trừ từ revenue)
        // Revenue đã = total_amount (khách trả sau giảm giá), nên KHÔNG trừ lại
        $discounts = DB::table('orders')
            ->join('payments', 'payments.order_id', '=', 'orders.id')
            ->where('orders.status', 'completed')
            ->where('payments.status', 'paid')
            ->whereBetween('payments.paid_at', [$start, $end])
            ->sum('orders.discount_amount') ?? 0;

        // 3. Giá vốn hàng bán (COGS)
        // FIX: order_items.cost_price đã là TỔNG chi phí, không phải đơn vị
        // Nên chỉ cần SUM trực tiếp, không nhân quantity
        $cogs = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('payments', 'payments.order_id', '=', 'orders.id')
            ->where('orders.status', 'completed')
            ->where('payments.status', 'paid')
            ->whereBetween('payments.paid_at', [$start, $end])
            ->sum('order_items.cost_price') ?? 0;

        // 4. Chi phí vận chuyển
        $shippingCost = DB::table('orders')
            ->join('payments', 'payments.order_id', '=', 'orders.id')
            ->where('orders.status', 'completed')
            ->where('payments.status', 'paid')
            ->whereBetween('payments.paid_at', [$start, $end])
            ->sum('orders.shipping_fee') ?? 0;

        // 5. Tiền nhân công (tính theo TỈ LỆ ca làm trong khoảng thời gian)
        $totalMonthlySalary = Salary::where('month', $start->month)
            ->where('year', $start->year)
            ->sum('final_salary') ?? 0;

        // Tính số ca trong range và trong tháng
        $shiftsInRange = Attendance::whereBetween('work_date', [$start, $end])->count();
        $totalShiftsInMonth = Attendance::whereRaw('MONTH(work_date) = ? AND YEAR(work_date) = ?', [$start->month, $start->year])->count();

        // Tính ratio và áp dụng vào salary
        $salaryRatio = ($totalShiftsInMonth > 0) ? ($shiftsInRange / $totalShiftsInMonth) : 1;
        $staffCost = $totalMonthlySalary * $salaryRatio;

        // 6. FIX ISSUE #3: Hao hụt hàng - Dùng import_items.created_at thay vì product_variants.created_at
        // Lý do: product_variants.created_at là ngày tạo biến thể, không phải ngày nhập
        $inventoryShrinkage = DB::table('import_items')
            ->join('product_variants', 'product_variants.id', '=', 'import_items.product_variant_id')
            ->where('import_items.remaining_quantity', '>', 0)
            ->whereDate('product_variants.expired_at', '<', $end)
            ->whereBetween('import_items.created_at', [$start, $end])  // FIX: Dùng import_items.created_at
            ->sum(DB::raw('import_items.remaining_quantity * import_items.unit_price')) ?? 0;

        // 7. Tính tổn thất từ hàng hủy (inventory writeoff)
        $writeoffCost = DB::table('inventory_writeoffs')
            ->whereBetween('created_at', [$start, $end])
            ->sum('total_cost') ?? 0;

        // FIX ISSUE #2: CÔNG THỨC LỢI NHUẬN ĐÚNG
        // Lợi nhuận gộp = Doanh thu - Vốn hàng (KHÔNG trừ giảm giá ở đây)
        $grossProfit = $revenue - $cogs;

        // Lợi nhuận vận hành = Lợi nhuận gộp - Chi phí vận chuyển
        $operatingProfit = $grossProfit - $shippingCost;

        // Lợi nhuận ròng = Lợi nhuận vận hành - Chi phí nhân công - Hao hụt - Hủy hàng
        $netProfit = $operatingProfit - $staffCost - $inventoryShrinkage - $writeoffCost;

        return [
            'revenue' => $revenue,
            'discounts' => $discounts,  // Tracking riêng, không dùng để tính lợi nhuận
            'cogs' => $cogs,
            'shipping_cost' => $shippingCost,
            'staff_cost' => $staffCost,
            'inventory_shrinkage' => $inventoryShrinkage,
            'writeoff_cost' => $writeoffCost,
            'gross_profit' => $grossProfit,  // Revenue - COGS
            'operating_profit' => $operatingProfit,  // Gross Profit - Shipping
            'net_profit' => $netProfit,  // Operating Profit - All Expenses
        ];
    }

    public function revenueStatistics(Request $request)
    {
        $reportData = $this->buildRevenueReportData($request);

        return view('admin.revenue_statistics', $reportData);
    }

    public function revenueTodayDetail(Request $request)
    {
        $today = Carbon::today();
        $todayStart = $today->copy()->startOfDay();
        $todayEnd = $today->copy()->endOfDay();

        // Doanh thu theo giờ trong ngày hôm nay
        $hourlyRevenue = [];
        for ($hour = 0; $hour < 24; $hour++) {
            $hourStart = $todayStart->copy()->addHours($hour);
            $hourEnd = $hourStart->copy()->addMinutes(59)->addSeconds(59);

            $revenue = $this->calculateNetRevenueByRange($hourStart, $hourEnd);
            $hourlyRevenue[] = [
                'hour' => str_pad($hour, 2, '0', STR_PAD_LEFT) . ':00',
                'revenue' => $revenue
            ];
        }

        // Tổng doanh thu hôm nay
        $todayTotalRevenue = $this->calculateNetRevenueByRange($todayStart, $todayEnd);

        // Chi tiết các đơn hàng hoàn thành hôm nay
        $completedOrdersToday = Order::query()
            ->join('payments', 'payments.order_id', '=', 'orders.id')
            ->with(['customer.user', 'payment'])
            ->where('orders.status', 'completed')
            ->where('payments.status', 'paid')
            ->whereBetween('payments.paid_at', [$todayStart, $todayEnd])
            ->orderByDesc('payments.paid_at')
            ->get();

        // Thống kê theo phương thức thanh toán
        $paymentMethodStats = Order::query()
            ->join('payments', 'payments.order_id', '=', 'orders.id')
            ->where('orders.status', 'completed')
            ->where('payments.status', 'paid')
            ->whereBetween('payments.paid_at', [$todayStart, $todayEnd])
            ->selectRaw('payments.method, COUNT(*) as count, SUM(orders.total_amount) as total')
            ->groupBy('payments.method')
            ->get();

        // Top sản phẩm bán hôm nay
        $topProducts = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('payments', 'payments.order_id', '=', 'orders.id')
            ->join('product_variants', 'product_variants.id', '=', 'order_items.product_variant_id')
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->where('payments.status', 'paid')
            ->whereBetween('payments.paid_at', [$todayStart, $todayEnd])
            ->selectRaw('products.id, products.name, products.image')
            ->selectRaw('SUM(order_items.quantity) as sold_qty')
            ->selectRaw('SUM(order_items.subtotal) as total_revenue')
            ->selectRaw('SUM(COALESCE(order_items.cost_price, 0) * order_items.quantity) as total_cogs')
            ->groupBy('products.id', 'products.name', 'products.image')
            ->orderByDesc('sold_qty')
            ->limit(5)
            ->get();

        // Tính COGS tổng hôm nay
        $totalCogs = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('payments', 'payments.order_id', '=', 'orders.id')
            ->where('orders.status', 'completed')
            ->where('payments.status', 'paid')
            ->whereBetween('payments.paid_at', [$todayStart, $todayEnd])
            ->sum(DB::raw('COALESCE(order_items.cost_price, 0) * order_items.quantity'));

        // Tính shipping fee tổng
        $totalShipping = Order::query()
            ->whereHas('payment', function ($query) use ($todayStart, $todayEnd) {
                $query->where('status', 'paid')
                    ->whereBetween('paid_at', [$todayStart, $todayEnd]);
            })
            ->where('status', 'completed')
            ->sum('shipping_fee');

        // Tính refund hôm nay
        $refundAmount = Payment::query()
            ->where('refund_status', 'completed')
            ->whereBetween('refund_at', [$todayStart, $todayEnd])
            ->sum('refund_amount');

        // Gross sales
        $grossSale = Order::query()
            ->whereHas('payment', function ($query) use ($todayStart, $todayEnd) {
                $query->where('status', 'paid')
                    ->whereBetween('paid_at', [$todayStart, $todayEnd]);
            })
            ->where('status', 'completed')
            ->sum('total_amount');

        $netRevenue = $grossSale - $refundAmount;

        return view('admin.revenue_today_detail', [
            'today' => $today,
            'todayTotalRevenue' => $todayTotalRevenue,
            'hourlyRevenue' => $hourlyRevenue,
            'completedOrdersToday' => $completedOrdersToday,
            'paymentMethodStats' => $paymentMethodStats,
            'topProducts' => $topProducts,
            'grossSale' => $grossSale,
            'refundAmount' => $refundAmount,
            'netRevenue' => $netRevenue,
            'totalCogs' => $totalCogs,
            'totalShipping' => $totalShipping,
            'profit' => $netRevenue - $totalCogs,
        ]);
    }

    public function exportRevenueExcel(Request $request)
    {
        $reportData = $this->buildRevenueReportData($request);
        $fileName = 'bao-cao-doanh-thu-' . ($reportData['selectedMonth'] ?? now()->format('Y-m')) . '-' . now()->format('Ymd_His') . '.xls';
        $content = view('admin.reports.revenue_excel', $reportData)->render();

        return response($content, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    public function exportRevenuePdf(Request $request)
    {
        $reportData = $this->buildRevenueReportData($request);
        $fileName = 'bao-cao-doanh-thu-' . ($reportData['selectedMonth'] ?? now()->format('Y-m')) . '-' . now()->format('Ymd_His') . '.pdf';

        return Pdf::loadView('admin.reports.revenue_pdf', $reportData)
            ->setPaper('A4', 'portrait')
            ->download($fileName);
    }

    private function buildRevenueReportData(Request $request): array
    {
        [$monthStart, $monthEnd, $selectedMonth, $monthLabel] = $this->resolveMonthRange($request->input('month'));

        $monthlyFinance = $this->buildDailyRevenueSeries($monthStart, $monthEnd);
        $weeklyRevenue = $this->buildWeeklyRevenueSeriesByMonth($monthStart, $monthEnd);
        $paymentMethod = $this->buildPaymentMethodSeries($monthStart, $monthEnd);

        // Calculate revenue based on ORDER COMPLETION (updated_at), not payment date
        $grossSale = DB::table('orders')
            ->join('payments', 'payments.order_id', '=', 'orders.id')
            ->where('orders.status', 'completed')
            ->where('payments.status', 'paid')
            ->whereBetween('orders.updated_at', [$monthStart, $monthEnd])
            ->sum('orders.total_amount');

        $refundAmount = Payment::query()
            ->join('orders', 'orders.id', '=', 'payments.order_id')
            ->where('payments.refund_status', 'completed')
            ->whereBetween('orders.updated_at', [$monthStart, $monthEnd])
            ->sum('payments.refund_amount');

        $netRevenue = $grossSale - $refundAmount;

        $shippingCost = DB::table('orders')
            ->join('payments', 'payments.order_id', '=', 'orders.id')
            ->where('orders.status', 'completed')
            ->where('payments.status', 'paid')
            ->whereBetween('orders.updated_at', [$monthStart, $monthEnd])
            ->sum('orders.shipping_fee');

        $salaryCost = Salary::where('month', $monthStart->month)
            ->where('year', $monthStart->year)
            ->sum('final_salary') ?? 0;

        // Calculate discount from completed paid orders
        $discounts = DB::table('orders')
            ->join('payments', 'payments.order_id', '=', 'orders.id')
            ->where('orders.status', 'completed')
            ->where('payments.status', 'paid')
            ->whereBetween('orders.updated_at', [$monthStart, $monthEnd])
            ->sum('orders.discount_amount') ?? 0;

        // Fix: Calculate import cost from import_items instead of imports.total_amount (which is 0)
        $importCost = DB::table('import_items')
            ->join('imports', 'imports.id', '=', 'import_items.import_id')
            ->whereBetween('imports.import_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->selectRaw('SUM(import_items.quantity * import_items.unit_price) as total')
            ->first()
            ->total ?? 0;

        $cogs = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('payments', 'payments.order_id', '=', 'orders.id')
            ->where('orders.status', 'completed')
            ->where('payments.status', 'paid')
            ->whereBetween('orders.updated_at', [$monthStart, $monthEnd])
            ->sum(DB::raw('COALESCE(order_items.cost_price, 0) * order_items.quantity'));

        // Chết kho hết hạn + thiệt hại hoàn trả hư hỏng
        $writeoffCost = DB::table('inventory_writeoffs')
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->where('reason', 'expired')
            ->sum('total_cost');

        $damagedLoss = DB::table('inventory_writeoffs')
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->where('reason', 'damaged')
            ->sum('total_cost');

        $totalWriteoffCost = $writeoffCost + $damagedLoss;

        // Writeoff detail rows for the report view
        $writeoffDetails = DB::table('inventory_writeoffs')
            ->join('product_variants', 'product_variants.id', '=', 'inventory_writeoffs.product_variant_id')
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->whereBetween('inventory_writeoffs.created_at', [$monthStart, $monthEnd])
            ->selectRaw('products.name as product_name, product_variants.sku as sku, inventory_writeoffs.reason, SUM(inventory_writeoffs.quantity_written_off) as total_qty, SUM(inventory_writeoffs.total_cost) as total_cost')
            ->groupBy('products.name', 'product_variants.sku', 'inventory_writeoffs.reason')
            ->orderByDesc('total_cost')
            ->get()
            ->map(function ($item) {
                $item->reason_label = match ($item->reason) {
                    'expired'  => 'Hết hạn',
                    'damaged'  => 'Hư hỏng',
                    default    => 'Khác',
                };
                return $item;
            });

        // FIX: Tính lợi nhuận ước tính (đã thêm discount)
        $estimatedProfit = $netRevenue - $discounts - $cogs - $salaryCost - $shippingCost - $totalWriteoffCost;

        $customersWithPaidOrders = DB::table('orders')
            ->join('payments', 'payments.order_id', '=', 'orders.id')
            ->where('payments.status', 'paid')
            ->whereNotNull('orders.customer_id')
            ->whereBetween('orders.created_at', [$monthStart, $monthEnd])
            ->distinct('orders.customer_id')
            ->count('orders.customer_id');

        $repeatCustomers = DB::table('orders')
            ->join('payments', 'payments.order_id', '=', 'orders.id')
            ->where('payments.status', 'paid')
            ->whereNotNull('orders.customer_id')
            ->whereBetween('orders.created_at', [$monthStart, $monthEnd])
            ->select('orders.customer_id')
            ->groupBy('orders.customer_id')
            ->havingRaw('COUNT(orders.id) >= 2')
            ->get()
            ->count();

        $returningCustomerRate = $customersWithPaidOrders > 0
            ? round(($repeatCustomers / $customersWithPaidOrders) * 100, 2)
            : 0;

        $paidByMethod = Payment::query()
            ->where('status', 'paid')
            ->whereBetween('paid_at', [$monthStart, $monthEnd])
            ->selectRaw('method, COUNT(*) as paid_count')
            ->groupBy('method')
            ->pluck('paid_count', 'method');

        $refundByMethod = Payment::query()
            ->where('refund_status', 'completed')
            ->whereBetween('refund_at', [$monthStart, $monthEnd])
            ->selectRaw('method, COUNT(*) as refund_count, SUM(refund_amount) as refund_amount')
            ->groupBy('method')
            ->get()
            ->map(function ($item) use ($paidByMethod) {
                $paid = (int) ($paidByMethod[$item->method] ?? 0);
                $item->paid_count = $paid;
                $item->method_label = $this->translatePaymentMethod($item->method);
                $item->refund_rate = $paid > 0 ? round(((int) $item->refund_count / $paid) * 100, 2) : 0;
                return $item;
            });

        $refundByReason = DB::table('order_returns')
            ->join('orders', 'orders.id', '=', 'order_returns.order_id')
            ->join('payments', 'payments.order_id', '=', 'orders.id')
            ->where('payments.refund_status', 'completed')
            ->whereBetween('payments.refund_at', [$monthStart, $monthEnd])
            ->selectRaw('order_returns.reason, COUNT(*) as total')
            ->groupBy('order_returns.reason')
            ->orderByDesc('total')
            ->get()
            ->map(function ($item) {
                $item->reason_label = $this->translateSupportReason($item->reason);
                return $item;
            });

        $cancelByReason = DB::table('order_cancellations')
            ->join('orders', 'orders.id', '=', 'order_cancellations.order_id')
            ->whereBetween('order_cancellations.cancelled_at', [$monthStart, $monthEnd])
            ->selectRaw('COALESCE(NULLIF(order_cancellations.reason, ""), "Không có lý do") as reason_label, COUNT(*) as total')
            ->groupBy('reason_label')
            ->orderByDesc('total')
            ->get();

        $totalCancelled = (int) $cancelByReason->sum('total');
        $cancelByReason = $cancelByReason->map(function ($item) use ($totalCancelled) {
            $item->reason_label = $this->translateSupportReason($item->reason_label);
            $item->rate = $totalCancelled > 0 ? round(($item->total / $totalCancelled) * 100, 2) : 0;
            return $item;
        });

        $importsBySupplier = DB::table('import_items')
            ->join('imports', 'imports.id', '=', 'import_items.import_id')
            ->join('suppliers', 'suppliers.id', '=', 'imports.supplier_id')
            ->whereBetween('imports.import_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->selectRaw('suppliers.name as supplier_name, COUNT(DISTINCT imports.id) as import_count, SUM(import_items.quantity * import_items.unit_price) as total_value')
            ->groupBy('suppliers.name')
            ->orderByDesc('total_value')
            ->get();

        $importsByCategory = DB::table('import_items')
            ->join('imports', 'imports.id', '=', 'import_items.import_id')
            ->join('product_variants', 'product_variants.id', '=', 'import_items.product_variant_id')
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->leftJoin('category_products', 'category_products.id', '=', 'products.category_id')
            ->whereBetween('imports.import_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->selectRaw('COALESCE(category_products.name, "Khác") as category_name, SUM(import_items.quantity) as total_qty, SUM(import_items.quantity * import_items.unit_price) as total_value')
            ->groupBy('category_name')
            ->orderByDesc('total_value')
            ->get();

        $salaryByDepartment = DB::table('salaries')
            ->join('staffs', 'staffs.user_id', '=', 'salaries.staff_id')
            ->where('salaries.month', $monthStart->month)
            ->where('salaries.year', $monthStart->year)
            ->selectRaw('COALESCE(staffs.position, "khac") as department, COUNT(DISTINCT salaries.staff_id) as staff_count, SUM(salaries.final_salary) as final_salary')
            ->groupBy('department')
            ->orderByDesc('final_salary')
            ->get()
            ->map(function ($item) {
                $item->department_label = $this->translateDepartment($item->department);
                return $item;
            });

        $productMarginBase = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('payments', 'payments.order_id', '=', 'orders.id')
            ->join('product_variants', 'product_variants.id', '=', 'order_items.product_variant_id')
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->where('payments.status', 'paid')
            ->whereBetween('orders.created_at', [$monthStart, $monthEnd])
            ->selectRaw('products.name as product_name, SUM(COALESCE(order_items.subtotal, order_items.quantity * order_items.price)) as revenue, SUM(COALESCE(order_items.cost_price, 0) * order_items.quantity) as cost')
            ->groupBy('products.name')
            ->get()
            ->map(function ($item) {
                $item->profit = (float) $item->revenue - (float) $item->cost;
                $item->margin_rate = (float) $item->revenue > 0
                    ? round(($item->profit / $item->revenue) * 100, 2)
                    : 0;
                return $item;
            });

        $topMarginHigh = $productMarginBase->sortByDesc('margin_rate')->take(5)->values();
        $topMarginLow = $productMarginBase->sortBy('margin_rate')->take(5)->values();

        // --- Thanh lý hàng sắp hết hạn / hết hạn ---
        $today = Carbon::today();
        $expiringSoonDays = 30;
        $expiringSoonRate = 0.8;
        $expiredRate = 0.4;

        $liquidationExpiringSoon = DB::table('import_items')
            ->join('product_variants', 'product_variants.id', '=', 'import_items.product_variant_id')
            ->where('import_items.remaining_quantity', '>', 0)
            ->whereDate('product_variants.expired_at', '>=', $today)
            ->whereDate('product_variants.expired_at', '<=', $today->copy()->addDays($expiringSoonDays))
            ->selectRaw('SUM(import_items.remaining_quantity) as total_qty')
            ->selectRaw('SUM(import_items.remaining_quantity * import_items.unit_price) as total_cost')
            ->selectRaw('SUM(import_items.remaining_quantity * product_variants.price) as total_retail_value')
            ->first();

        $liquidationExpired = DB::table('import_items')
            ->join('product_variants', 'product_variants.id', '=', 'import_items.product_variant_id')
            ->where('import_items.remaining_quantity', '>', 0)
            ->whereDate('product_variants.expired_at', '<', $today)
            ->selectRaw('SUM(import_items.remaining_quantity) as total_qty')
            ->selectRaw('SUM(import_items.remaining_quantity * import_items.unit_price) as total_cost')
            ->selectRaw('SUM(import_items.remaining_quantity * product_variants.price) as total_retail_value')
            ->first();

        $liquidationSummary = [
            'expiring_days' => $expiringSoonDays,
            'expiring_soon_qty' => (int) ($liquidationExpiringSoon->total_qty ?? 0),
            'expiring_soon_cost' => (float) ($liquidationExpiringSoon->total_cost ?? 0),
            'expiring_soon_retail' => (float) ($liquidationExpiringSoon->total_retail_value ?? 0),
            'expiring_soon_recovery' => (float) ($liquidationExpiringSoon->total_retail_value ?? 0) * $expiringSoonRate,
            'expiring_soon_rate' => $expiringSoonRate,
            'expired_qty' => (int) ($liquidationExpired->total_qty ?? 0),
            'expired_cost' => (float) ($liquidationExpired->total_cost ?? 0),
            'expired_retail' => (float) ($liquidationExpired->total_retail_value ?? 0),
            'expired_recovery' => (float) ($liquidationExpired->total_retail_value ?? 0) * $expiredRate,
            'expired_rate' => $expiredRate,
        ];

        $liquidationSummary['expiring_soon_profit'] = $liquidationSummary['expiring_soon_recovery'] - $liquidationSummary['expiring_soon_cost'];
        $liquidationSummary['expired_profit'] = $liquidationSummary['expired_recovery'] - $liquidationSummary['expired_cost'];

        $revenueSummary = [
            'gross_sale'               => $grossSale,
            'refund_amount'            => $refundAmount,
            'net_revenue'              => $netRevenue,
            'shipping_cost'            => $shippingCost,
            'salary_cost'              => $salaryCost,
            'import_cost'              => $importCost,
            'cogs'                     => $cogs,
            'writeoff_cost'            => $writeoffCost,
            'damaged_loss'             => $damagedLoss,
            'total_writeoff_cost'      => $totalWriteoffCost,
            'estimated_profit'         => $estimatedProfit,
            'returning_customer_rate'  => $returningCustomerRate,
            'ship_to_revenue_rate'     => $netRevenue > 0 ? round(($shippingCost / $netRevenue) * 100, 2) : 0,
        ];

        return [
            'selectedMonth'     => $selectedMonth,
            'revenueSummary'    => $revenueSummary,
            'monthlyFinance'    => $monthlyFinance,
            'weeklyRevenue'     => $weeklyRevenue,
            'paymentMethod'     => $paymentMethod,
            'refundByMethod'    => $refundByMethod,
            'refundByReason'    => $refundByReason,
            'cancelByReason'    => $cancelByReason,
            'importsBySupplier' => $importsBySupplier,
            'importsByCategory' => $importsByCategory,
            'liquidationSummary' => $liquidationSummary,
            'salaryByDepartment' => $salaryByDepartment,
            'topMarginHigh'     => $topMarginHigh,
            'topMarginLow'      => $topMarginLow,
            'writeoffDetails'   => $writeoffDetails,
            'monthLabel' => $monthLabel,
            'generatedAt' => now(),
        ];
    }

    private function resolveMonthRange(?string $monthInput): array
    {
        $monthString = trim((string) ($monthInput ?: now()->format('Y-m')));

        try {
            // Parse with explicit day=01 to avoid overflow (e.g. 2026-02 on day 30 becoming March).
            $monthStart = Carbon::createFromFormat('!Y-m-d', $monthString . '-01')->startOfMonth();
        } catch (\Throwable $exception) {
            $monthStart = now()->startOfMonth();
            $monthString = $monthStart->format('Y-m');
        }

        $monthEnd = $monthStart->copy()->endOfMonth();

        return [$monthStart, $monthEnd, $monthString, $monthStart->format('m/Y')];
    }

    private function buildWeeklyRevenueSeriesByMonth(Carbon $monthStart, Carbon $monthEnd): array
    {
        $labels = [];
        $values = [];

        $weekCursor = $monthStart->copy()->startOfWeek();

        while ($weekCursor->lte($monthEnd)) {
            $weekStart = $weekCursor->copy()->startOfWeek();
            $weekEnd = $weekCursor->copy()->endOfWeek();

            $rangeStart = $weekStart->copy()->lt($monthStart) ? $monthStart->copy() : $weekStart;
            $rangeEnd = $weekEnd->copy()->gt($monthEnd) ? $monthEnd->copy() : $weekEnd;

            $labels[] = 'Tuần ' . $weekStart->format('W') . ' (' . $rangeStart->format('d/m') . '-' . $rangeEnd->format('d/m') . ')';
            $values[] = $this->calculateNetRevenueByRange($rangeStart, $rangeEnd);

            $weekCursor->addWeek();
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    private function buildDailyRevenueSeries(Carbon $startDate, Carbon $endDate): array
    {
        $labels = [];
        $values = [];

        $cursor = $startDate->copy()->startOfDay();
        $end = $endDate->copy()->endOfDay();

        while ($cursor->lte($end)) {
            $dayStart = $cursor->copy()->startOfDay();
            $dayEnd = $cursor->copy()->endOfDay();

            $labels[] = $cursor->format('d/m');
            // Calculate revenue based on ORDER COMPLETION (updated_at), not creation date
            // This is the final state - completed orders reflect actual revenue received
            $values[] = $this->calculateDailyRevenueByUpdatedDate($dayStart, $dayEnd);

            $cursor->addDay();
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    private function calculateDailyRevenueByUpdatedDate(Carbon $start, Carbon $end): float
    {
        // Calculate revenue based on ORDER COMPLETION DATE (updated_at)
        // This represents when the order actually reached 'completed' status
        // Ensures accurate revenue reporting: only completed orders count
        $paid = DB::table('orders')
            ->join('payments', 'payments.order_id', '=', 'orders.id')
            ->where('orders.status', 'completed')
            ->where('payments.status', 'paid')
            ->whereBetween('orders.updated_at', [$start->startOfDay(), $end->endOfDay()])
            ->sum('orders.total_amount');

        $refund = Payment::query()
            ->join('orders', 'orders.id', '=', 'payments.order_id')
            ->where('payments.refund_status', 'completed')
            ->whereBetween('orders.updated_at', [$start->startOfDay(), $end->endOfDay()])
            ->sum('payments.refund_amount');

        return (float) ($paid - $refund);
    }

    private function getTopProductsByPeriod(Carbon $startDate, Carbon $endDate)
    {
        return DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('payments', 'payments.order_id', '=', 'orders.id')
            ->join('product_variants', 'product_variants.id', '=', 'order_items.product_variant_id')
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->where('payments.status', 'paid')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->selectRaw('products.id as product_id, products.name as product_name, products.image as product_image')
            ->selectRaw('SUM(order_items.quantity) as sold_qty')
            ->selectRaw('SUM(COALESCE(order_items.subtotal, order_items.quantity * order_items.price)) as total_revenue')
            ->selectRaw('SUM(COALESCE(order_items.cost_price, 0) * order_items.quantity) as total_cogs')
            ->selectRaw('SUM(COALESCE(order_items.subtotal, order_items.quantity * order_items.price)) - SUM(COALESCE(order_items.cost_price, 0) * order_items.quantity) as total_profit')
            ->groupBy('products.id', 'products.name', 'products.image')
            ->orderByDesc('sold_qty')
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->get();
    }

    private function buildMonthlyFinanceSeries(int $months = 6): array
    {
        $labels = [];
        $netRevenue = [];
        $cogs = [];
        $profit = [];

        $start = Carbon::now()->startOfMonth()->subMonths($months - 1);

        for ($i = 0; $i < $months; $i++) {
            $monthStart = $start->copy()->addMonths($i)->startOfMonth();
            $monthEnd = $monthStart->copy()->endOfMonth();

            $monthPaid = Payment::query()
                ->where('status', 'paid')
                ->whereBetween('paid_at', [$monthStart, $monthEnd])
                ->sum('amount');

            $monthRefund = Payment::query()
                ->where('refund_status', 'completed')
                ->whereBetween('refund_at', [$monthStart, $monthEnd])
                ->sum('refund_amount');

            $monthNetRevenue = $monthPaid - $monthRefund;

            $monthCogs = DB::table('order_items')
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->join('payments', 'payments.order_id', '=', 'orders.id')
                ->where('payments.status', 'paid')
                ->whereBetween('orders.created_at', [$monthStart, $monthEnd])
                ->sum(DB::raw('COALESCE(order_items.cost_price, 0) * order_items.quantity'));

            $labels[] = $monthStart->format('m/Y');
            $netRevenue[] = (float) $monthNetRevenue;
            $cogs[] = (float) $monthCogs;
            $profit[] = (float) ($monthNetRevenue - $monthCogs);
        }

        return [
            'labels' => $labels,
            'net_revenue' => $netRevenue,
            'cogs' => $cogs,
            'profit' => $profit,
        ];
    }

    private function buildWeeklyRevenueSeries(int $weeks = 8): array
    {
        $labels = [];
        $values = [];

        $start = Carbon::now()->startOfWeek()->subWeeks($weeks - 1);

        for ($i = 0; $i < $weeks; $i++) {
            $weekStart = $start->copy()->addWeeks($i)->startOfWeek();
            $weekEnd = $weekStart->copy()->endOfWeek();

            $weekPaid = Payment::query()
                ->where('status', 'paid')
                ->whereBetween('paid_at', [$weekStart, $weekEnd])
                ->sum('amount');

            $weekRefund = Payment::query()
                ->where('refund_status', 'completed')
                ->whereBetween('refund_at', [$weekStart, $weekEnd])
                ->sum('refund_amount');

            $labels[] = 'W' . $weekStart->format('W') . ' (' . $weekStart->format('d/m') . ')';
            $values[] = (float) ($weekPaid - $weekRefund);
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    private function buildOrderStatusSeries(?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $query = Order::query();

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $stats = $query
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->orderBy('status')
            ->get();

        return [
            'labels' => $stats->pluck('status')->map(fn($status) => $this->translateOrderStatus($status))->values(),
            'data' => $stats->pluck('total')->map(fn($value) => (int) $value)->values(),
        ];
    }

    private function translateOrderStatus(?string $status): string
    {
        $statusMap = [
            'pending' => 'Chờ xử lý',
            'confirmed' => 'Đã xác nhận',
            'shipping' => 'Đang giao',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy',
            'refund_requested' => 'Yêu cầu hoàn hàng',
            'refunded' => 'Đã hoàn tiền',
            'unknown' => 'Không xác định',
        ];

        $key = strtolower((string) $status);

        return $statusMap[$key] ?? 'Không xác định';
    }

    private function buildPaymentMethodSeries(?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        // Count by payment method based on order completion (updated_at), not payment date
        $query = DB::table('payments')
            ->join('orders', 'orders.id', '=', 'payments.order_id');

        if ($startDate && $endDate) {
            $query->whereBetween('orders.updated_at', [$startDate, $endDate]);
        }

        $stats = $query
            ->where('payments.status', 'paid')
            ->selectRaw('payments.method, COUNT(*) as total_count, SUM(orders.total_amount) as total_revenue')
            ->groupBy('payments.method')
            ->orderBy('payments.method')
            ->get();

        return [
            'labels' => collect($stats)->pluck('method')->map(fn($method) => $this->translatePaymentMethod($method))->values(),
            'counts' => collect($stats)->pluck('total_count')->map(fn($value) => (int) $value)->values(),
            'revenues' => collect($stats)->pluck('total_revenue')->map(fn($value) => (float) $value)->values(),
        ];
    }

    private function translatePaymentMethod(?string $method): string
    {
        $methodMap = [
            'cod' => 'Thanh toán khi nhận hàng',
            'cash' => 'Tiền mặt',
            'cash_on_delivery' => 'Thanh toán khi nhận hàng',
            'bank' => 'Chuyển khoản ngân hàng',
            'bank_transfer' => 'Chuyển khoản ngân hàng',
            'vnpay' => 'VNPay',
            'momo' => 'MoMo',
            'wallet' => 'Ví điện tử',
            'zalopay' => 'ZaloPay',
            'paypal' => 'PayPal',
            'stripe' => 'Thẻ thanh toán',
        ];

        $key = strtolower(trim((string) $method));

        return $methodMap[$key] ?? ((string) $method !== '' ? (string) $method : 'Không xác định');
    }

    private function translateDepartment(?string $department): string
    {
        $departmentMap = [
            'admin' => 'Quản trị',
            'staff' => 'Nhân viên',
            'cashier' => 'Thu ngân',
            'order_staff' => 'Nhân viên xử lý đơn',
            'warehouse' => 'Kho',
            'sales' => 'Kinh doanh',
            'khac' => 'Khác',
        ];

        $key = strtolower(trim((string) $department));

        return $departmentMap[$key] ?? ((string) $department !== '' ? ucfirst((string) $department) : 'Khác');
    }

    private function translateSupportReason(?string $reason): string
    {
        $reasonMap = [
            'wrong_product' => 'Giao sai sản phẩm',
            'wrong_item' => 'Giao sai mặt hàng',
            'product_defect' => 'Sản phẩm lỗi',
            'damaged_product' => 'Sản phẩm bị hư hỏng',
            'damaged' => 'Sản phẩm bị hư hỏng',
            'delivery_too_long' => 'Giao hàng quá lâu',
            'late_delivery' => 'Giao hàng chậm',
            'changed_mind' => 'Khách đổi ý',
            'refund_request_retroactive' => 'Giao sai sản phẩm',
            'duplicate_order' => 'Đặt trùng đơn',
            'out_of_stock' => 'Hết hàng',
            'quality_issue' => 'Chất lượng không đạt',
            'payment_issue' => 'Lỗi thanh toán',
            'hh' => 'Hàng hỏng',
            'other' => 'Khác',
            'Không có lý do' => 'Không có lý do',
        ];

        $key = trim((string) $reason);
        $normalizedKey = strtolower($key);

        // If reason not in mapping, return the DB value unchanged so UI matches stored data exactly
        return $reasonMap[$key] ?? $reasonMap[$normalizedKey] ?? ($key !== '' ? $key : 'Không có lý do');
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
