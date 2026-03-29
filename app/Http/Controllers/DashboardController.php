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
            $paidOrdersQuery = Order::query()
                ->whereHas('payment', function ($query) {
                    $query->where('status', 'paid');
                });

            $todaySale = (clone $paidOrdersQuery)
                ->whereDate('orders.created_at', $today)
                ->sum('total_amount');

            $totalSale = (clone $paidOrdersQuery)
                ->sum('total_amount');

            $todayRefundAmount = Payment::query()
                ->where('refund_status', 'completed')
                ->whereDate('refund_at', $today)
                ->sum('refund_amount');

            $totalRefundAmount = Payment::query()
                ->where('refund_status', 'completed')
                ->sum('refund_amount');

            $todayRevenue = $todaySale - $todayRefundAmount;
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
                ->leftJoin('customers', 'customers.id', '=', 'orders.customer_id')
                ->leftJoin('users', 'users.id', '=', 'customers.user_id')
                ->where('payments.status', 'paid')
                ->whereNotNull('orders.customer_id')
                ->whereBetween('orders.created_at', [$monthStart, $monthEnd])
                ->selectRaw("orders.customer_id, COALESCE(MAX(users.name), MAX(orders.receiver_name), 'Khách vãng lai') as customer_name, COUNT(orders.id) as orders_count, SUM(orders.total_amount) as total_spent")
                ->groupBy('orders.customer_id')
                ->orderByDesc('orders_count')
                ->orderByDesc('total_spent')
                ->limit(3)
                ->get();

            $topCustomersByValue = DB::table('orders')
                ->join('payments', 'payments.order_id', '=', 'orders.id')
                ->leftJoin('customers', 'customers.id', '=', 'orders.customer_id')
                ->leftJoin('users', 'users.id', '=', 'customers.user_id')
                ->where('payments.status', 'paid')
                ->whereNotNull('orders.customer_id')
                ->whereBetween('orders.created_at', [$monthStart, $monthEnd])
                ->selectRaw("orders.customer_id, COALESCE(MAX(users.name), MAX(orders.receiver_name), 'Khách vãng lai') as customer_name, COUNT(orders.id) as orders_count, SUM(orders.total_amount) as total_spent")
                ->groupBy('orders.customer_id')
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
                'total_shipping_fee' => (clone $paidOrdersQuery)->whereBetween('orders.created_at', [$monthStart, $monthEnd])->sum('shipping_fee'),
                'total_discount_amount' => (clone $paidOrdersQuery)->whereBetween('orders.created_at', [$monthStart, $monthEnd])->sum('discount_amount'),
            ];

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

        return view('admin.dashboard', compact(
            'selectedMonth',
            'monthLabel',
            'todaySale',
            'totalSale',
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
            'dashboardCharts',
            'productCountByCategory'
        ));
    }

    private function calculateNetRevenueByRange(Carbon $start, Carbon $end): float
    {
        $paid = Payment::query()
            ->where('status', 'paid')
            ->whereBetween('paid_at', [$start, $end])
            ->sum('amount');

        $refund = Payment::query()
            ->where('refund_status', 'completed')
            ->whereBetween('refund_at', [$start, $end])
            ->sum('refund_amount');

        return (float) ($paid - $refund);
    }

    public function revenueStatistics(Request $request)
    {
        $reportData = $this->buildRevenueReportData($request);

        return view('admin.revenue_statistics', $reportData);
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

        $grossSale = Payment::where('status', 'paid')
            ->whereBetween('paid_at', [$monthStart, $monthEnd])
            ->sum('amount');
        $refundAmount = Payment::where('refund_status', 'completed')
            ->whereBetween('refund_at', [$monthStart, $monthEnd])
            ->sum('refund_amount');
        $netRevenue = $grossSale - $refundAmount;

        $shippingCost = Order::query()
            ->whereHas('payment', function ($query) {
                $query->where('status', 'paid');
            })
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->sum('shipping_fee');

        $salaryCost = Salary::where('month', $monthStart->month)
            ->where('year', $monthStart->year)
            ->sum('total_salary');
        $importCost = Import::whereBetween('import_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->sum('total_amount');

        $cogs = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('payments', 'payments.order_id', '=', 'orders.id')
            ->where('payments.status', 'paid')
            ->whereBetween('orders.created_at', [$monthStart, $monthEnd])
            ->sum(DB::raw('COALESCE(order_items.cost_price, 0) * order_items.quantity'));

        $estimatedProfit = $netRevenue - $cogs - $salaryCost - $shippingCost;

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

        $importsBySupplier = DB::table('imports')
            ->join('suppliers', 'suppliers.id', '=', 'imports.supplier_id')
            ->whereBetween('imports.import_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->selectRaw('suppliers.name as supplier_name, COUNT(imports.id) as import_count, SUM(imports.total_amount) as total_value')
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
            ->selectRaw('COALESCE(staffs.position, "khac") as department, COUNT(DISTINCT salaries.staff_id) as staff_count, SUM(salaries.total_salary) as total_salary')
            ->groupBy('department')
            ->orderByDesc('total_salary')
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

        $revenueSummary = [
            'gross_sale' => $grossSale,
            'refund_amount' => $refundAmount,
            'net_revenue' => $netRevenue,
            'shipping_cost' => $shippingCost,
            'salary_cost' => $salaryCost,
            'import_cost' => $importCost,
            'cogs' => $cogs,
            'estimated_profit' => $estimatedProfit,
            'returning_customer_rate' => $returningCustomerRate,
            'ship_to_revenue_rate' => $netRevenue > 0 ? round(($shippingCost / $netRevenue) * 100, 2) : 0,
        ];

        return [
            'selectedMonth' => $selectedMonth,
            'revenueSummary' => $revenueSummary,
            'monthlyFinance' => $monthlyFinance,
            'weeklyRevenue' => $weeklyRevenue,
            'paymentMethod' => $paymentMethod,
            'refundByMethod' => $refundByMethod,
            'refundByReason' => $refundByReason,
            'cancelByReason' => $cancelByReason,
            'importsBySupplier' => $importsBySupplier,
            'importsByCategory' => $importsByCategory,
            'salaryByDepartment' => $salaryByDepartment,
            'topMarginHigh' => $topMarginHigh,
            'topMarginLow' => $topMarginLow,
            'monthLabel' => $monthLabel,
            'generatedAt' => now(),
        ];
    }

    private function resolveMonthRange(?string $monthInput): array
    {
        $monthString = $monthInput ?: now()->format('Y-m');

        try {
            $monthStart = Carbon::createFromFormat('Y-m', $monthString)->startOfMonth();
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
            $values[] = $this->calculateNetRevenueByRange($dayStart, $dayEnd);

            $cursor->addDay();
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
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
            ->selectRaw('products.id as product_id, products.name as product_name')
            ->selectRaw('SUM(order_items.quantity) as sold_qty')
            ->selectRaw('SUM(COALESCE(order_items.subtotal, order_items.quantity * order_items.price)) as total_revenue')
            ->groupBy('products.id', 'products.name')
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
            'refund_requested' => 'Yêu cầu hoàn tiền',
            'refunded' => 'Đã hoàn tiền',
            'unknown' => 'Không xác định',
        ];

        $key = strtolower((string) $status);

        return $statusMap[$key] ?? 'Không xác định';
    }

    private function buildPaymentMethodSeries(?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $query = Payment::query();

        if ($startDate && $endDate) {
            $query->where(function ($builder) use ($startDate, $endDate) {
                $builder->whereBetween('paid_at', [$startDate, $endDate])
                    ->orWhereBetween('created_at', [$startDate, $endDate]);
            });
        }

        $stats = $query
            ->selectRaw('method, COUNT(*) as total_count, SUM(CASE WHEN status = "paid" THEN amount ELSE 0 END) as total_revenue')
            ->groupBy('method')
            ->orderBy('method')
            ->get();

        return [
            'labels' => $stats->pluck('method')->map(fn($method) => $this->translatePaymentMethod($method))->values(),
            'counts' => $stats->pluck('total_count')->map(fn($value) => (int) $value)->values(),
            'revenues' => $stats->pluck('total_revenue')->map(fn($value) => (float) $value)->values(),
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
            'damaged_product' => 'Sản phẩm bị hư hỏng',
            'damaged' => 'Sản phẩm bị hư hỏng',
            'delivery_too_long' => 'Giao hàng quá lâu',
            'late_delivery' => 'Giao hàng chậm',
            'changed_mind' => 'Khách đổi ý',
            'duplicate_order' => 'Đặt trùng đơn',
            'out_of_stock' => 'Hết hàng',
            'quality_issue' => 'Chất lượng không đạt',
            'payment_issue' => 'Lỗi thanh toán',
            'hh' => 'Hàng hỏng',
            'Không có lý do' => 'Không có lý do',
        ];

        $key = trim((string) $reason);
        $normalizedKey = strtolower($key);

        return $reasonMap[$key] ?? $reasonMap[$normalizedKey] ?? ($key !== '' ? ucfirst(str_replace('_', ' ', $key)) : 'Không có lý do');
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
