@extends('admin.layouts.layout_admin')

@section('navbar')
    @include('admin.layouts.navbar')
@endsection

@section('content')
    @php
        $authUser = Auth::user();
        $isAdmin = $authUser->role === 'admin';
        $isStaff = $authUser->role === 'staff';
        $position = $isStaff ? ($authUser->staff?->position ?? null) : null;
        $canViewDashboard = $isAdmin || ($isStaff && $position === 'cashier');

        $statusLabels = [
            'pending' => 'Chờ xử lý',
            'confirmed' => 'Đã xác nhận',
            'shipping' => 'Đang giao',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy',
            'refund_requested' => 'Yêu cầu hoàn tiền',
            'refunded' => 'Đã hoàn tiền',
            'unknown' => 'Không xác định',
        ];
    @endphp

    <div class="container-fluid pt-4 px-4">
        @if(!$canViewDashboard)
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="fa fa-exclamation-triangle me-2"></i>
                <strong>Không có quyền xem!</strong> Bạn không có quyền truy cập trang Thống kê. Vui lòng liên hệ quản trị viên
                nếu cần hỗ trợ.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @else
            <!-- Header + Month Selector -->
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                <h5 class="mb-0">Tổng quan hoạt động tháng {{ $monthLabel }}</h5>
                <form method="GET" action="{{ route('admin.dashboard') }}" class="d-flex align-items-center gap-2">
                    <label for="month" class="mb-0 small text-muted">Chọn tháng</label>
                    <input id="month" name="month" type="month" class="form-control form-control-sm"
                        value="{{ $selectedMonth }}">
                    <button type="submit" class="btn btn-sm btn-primary">Xem</button>
                </form>
            </div>

            <!-- KPI Cards - Main Stats (4 columns) -->
            <div class="row g-4">
                <div class="col-sm-6 col-xl-3">
                    <div class="bg-primary text-white rounded d-flex align-items-center justify-content-between p-4 h-100">
                        <i class="fa fa-bolt fa-3x"></i>
                        <div class="ms-3">
                            <p class="mb-2">Doanh thu hôm nay</p>
                            <h6 class="mb-0 text-white">{{ number_format($todayRevenue, 0, ',', '.') }} ₫</h6>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div
                        class="bg-light rounded d-flex align-items-center justify-content-between p-4 h-100 border-start border-4 border-info">
                        <i class="fa fa-calendar-week fa-3x text-info"></i>
                        <div class="ms-3">
                            <p class="mb-2">Doanh thu tuần này</p>
                            <h6 class="mb-0">{{ number_format($weekRevenue, 0, ',', '.') }} ₫</h6>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div
                        class="bg-light rounded d-flex align-items-center justify-content-between p-4 h-100 border-start border-4 border-success">
                        <i class="fa fa-calendar-alt fa-3x text-success"></i>
                        <div class="ms-3">
                            <p class="mb-2">Doanh thu tháng đã chọn</p>
                            <h6 class="mb-0">{{ number_format($monthRevenue, 0, ',', '.') }} ₫</h6>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div
                        class="bg-light rounded d-flex align-items-center justify-content-between p-4 h-100 border-start border-4 border-warning">
                        <i class="fa fa-redo-alt fa-3x text-warning"></i>
                        <div class="ms-3">
                            <p class="mb-2">Tỷ lệ khách quay lại</p>
                            <h6 class="mb-0">{{ number_format($returningCustomerRate, 2) }}%</h6>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Secondary Stats -->
            <div class="row g-4 mt-1">
                <div class="col-sm-6 col-xl-3">
                    <div class="bg-light rounded d-flex align-items-center justify-content-between p-3 h-100">
                        <div>
                            <p class="mb-1">Đơn mới trong tháng</p>
                            <h6 class="mb-0">{{ number_format($newOrdersThisMonth) }}</h6>
                        </div>
                        <i class="fa fa-shopping-bag text-primary"></i>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="bg-light rounded d-flex align-items-center justify-content-between p-3 h-100">
                        <div>
                            <p class="mb-1">Khách mới trong tháng</p>
                            <h6 class="mb-0">{{ number_format($newCustomersThisMonth) }}</h6>
                        </div>
                        <i class="fa fa-user-plus text-success"></i>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="bg-light rounded d-flex align-items-center justify-content-between p-3 h-100">
                        <div>
                            <p class="mb-1">Đánh giá mới trong tháng</p>
                            <h6 class="mb-0">{{ number_format($reviewsThisMonth) }}</h6>
                        </div>
                        <i class="fa fa-star text-warning"></i>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="bg-light rounded d-flex align-items-center justify-content-between p-3 h-100">
                        <div>
                            <p class="mb-1">Lượt quan tâm trong tháng</p>
                            <h6 class="mb-0">{{ number_format($wishlistInteractions) }}</h6>
                        </div>
                        <i class="fa fa-eye text-info"></i>
                    </div>
                </div>
            </div>
            <!-- Account Stats -->
            <div class="row g-4 mt-1">
                <div class="col-sm-6 col-xl-4">
                    <div class="bg-light rounded p-3 h-100 border-start border-4 border-primary">
                        <p class="mb-1">Tài khoản mới trong tháng</p>
                        <h6 class="mb-0">{{ number_format($accountStats['new_accounts']) }}</h6>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-4">
                    <div class="bg-light rounded p-3 h-100 border-start border-4 border-danger">
                        <p class="mb-1">Tài khoản bị khóa</p>
                        <h6 class="mb-0">{{ number_format($accountStats['locked_accounts']) }}</h6>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-4">
                    <div class="bg-light rounded p-3 h-100 border-start border-4 border-success">
                        <p class="mb-1">Tài khoản đang hoạt động</p>
                        <h6 class="mb-0">{{ number_format($accountStats['active_accounts']) }}</h6>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="row g-4 mt-1">
                <div class="col-lg-5">
                    <div class="bg-light rounded p-4 h-100">
                        <h6 class="mb-3">Tiền bán theo từng ngày trong tháng</h6>
                        <canvas id="dailyRevenueChart" style="max-height: 280px;"></canvas>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="bg-light rounded p-4 h-100">
                        <h6 class="mb-3">Tình trạng đơn trong tháng</h6>
                        <canvas id="orderStatusChart" style="max-height: 280px;"></canvas>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="bg-light rounded p-4 h-100">
                        <h6 class="mb-3">Nhóm sản phẩm bán nhiều</h6>
                        <canvas id="categoryMixChart" style="max-height: 280px;"></canvas>
                    </div>
                </div>
            </div>

            <!-- Top Customer Stats -->
            <div class="row g-4 mt-1">
                <div class="col-lg-6">
                    <div class="bg-light rounded p-4 h-100 border-start border-4 border-primary">
                        <h6 class="mb-3">Top 3 người đặt nhiều đơn nhất</h6>
                        <div class="table-responsive">
                            <table class="table text-start align-middle table-bordered table-hover mb-0">
                                <thead>
                                    <tr class="text-dark">
                                        <th>#</th>
                                        <th>Khách hàng</th>
                                        <th>Số đơn</th>
                                        <th>Tổng chi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($topCustomersByOrders as $index => $customer)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $customer->customer_name }}</td>
                                            <td>{{ number_format($customer->orders_count) }}</td>
                                            <td>{{ number_format($customer->total_spent, 0, ',', '.') }} ₫</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4">Chưa có dữ liệu trong tháng đã chọn</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="bg-light rounded p-4 h-100 border-start border-4 border-success">
                        <h6 class="mb-3">Top 3 người có giá trị đơn cao nhất</h6>
                        <div class="table-responsive">
                            <table class="table text-start align-middle table-bordered table-hover mb-0">
                                <thead>
                                    <tr class="text-dark">
                                        <th>#</th>
                                        <th>Khách hàng</th>
                                        <th>Tổng chi</th>
                                        <th>Số đơn</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($topCustomersByValue as $index => $customer)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $customer->customer_name }}</td>
                                            <td>{{ number_format($customer->total_spent, 0, ',', '.') }} ₫</td>
                                            <td>{{ number_format($customer->orders_count) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4">Chưa có dữ liệu trong tháng đã chọn</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Top Products & Promotions Table -->
            <div class="row g-4 mt-1">
                <div class="col-lg-6">
                    <div class="bg-light text-center rounded p-4 h-100">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="mb-0">Top sản phẩm bán tốt trong tháng</h6>
                        </div>
                        <div class="table-responsive">
                            <table class="table text-start align-middle table-bordered table-hover mb-0">
                                <thead>
                                    <tr class="text-dark">
                                        <th>#</th>
                                        <th>Sản phẩm</th>
                                        <th>SL bán</th>
                                        <th>Doanh thu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($topProductsMonth as $index => $product)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $product->product_name }}</td>
                                            <td>{{ $product->sold_qty }}</td>
                                            <td>{{ number_format($product->total_revenue, 0, ',', '.') }} ₫</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4">Chưa có dữ liệu tháng này</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="bg-light rounded p-4 h-100">
                        <h6 class="mb-3">Mức độ dùng mã giảm giá theo chiến dịch</h6>
                        <div class="table-responsive">
                            <table class="table text-start align-middle table-bordered table-hover mb-0">
                                <thead>
                                    <tr class="text-dark">
                                        <th>Mã/chiến dịch</th>
                                        <th>Đơn áp dụng</th>
                                        <th>Tỷ lệ áp dụng</th>
                                        <th>Tổng giảm</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($voucherCampaignStats as $item)
                                        <tr>
                                            <td>{{ $item->campaign_code }}</td>
                                            <td>{{ number_format($item->applied_orders) }}</td>
                                            <td>{{ number_format($item->apply_rate, 2) }}%</td>
                                            <td>{{ number_format($item->total_discount, 0, ',', '.') }} ₫</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4">Tháng này chưa có đơn dùng mã giảm giá</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Category Analytics & Customer Cancellation Tracking -->
            <div class="row g-4 mt-1">
                <div class="col-lg-6">
                    <div class="bg-light rounded p-4 h-100">
                        <h6 class="mb-3">Theo dõi khách hủy đơn nhiều (tháng {{ $monthLabel }})</h6>
                        @if($topCancellerAlert)
                            <div class="alert alert-danger py-2">{{ $topCancellerAlert }}</div>
                        @endif
                        <div class="table-responsive">
                            <table class="table text-start align-middle table-bordered table-hover mb-0">
                                <thead>
                                    <tr class="text-dark">
                                        <th>Khách hàng</th>
                                        <th>Số đơn đã hủy</th>
                                        <th>Đề xuất</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($cancellationByCustomer as $item)
                                        <tr>
                                            <td>{{ $item->customer_name }}</td>
                                            <td>{{ number_format($item->cancelled_count) }}</td>
                                            <td>
                                                @if($item->cancelled_count >= 3)
                                                    <span class="badge bg-danger">Nên xem xét khóa</span>
                                                @else
                                                    <span class="badge bg-secondary">Theo dõi thêm</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3">Không có dữ liệu hủy đơn bởi khách trong tháng</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="bg-light rounded p-4 h-100">
                        <h6 class="mb-3">Sản phẩm bán chạy và đánh giá cao</h6>
                        <div class="table-responsive">
                            <table class="table text-start align-middle table-bordered table-hover mb-0">
                                <thead>
                                    <tr class="text-dark">
                                        <th>Sản phẩm</th>
                                        <th>Đã bán</th>
                                        <th>Điểm TB</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($bestSellerTopRatedProducts as $item)
                                        <tr>
                                            <td>{{ $item->product_name }}</td>
                                            <td>{{ number_format($item->sold_qty) }}</td>
                                            <td>{{ number_format($item->avg_rating ?? 0, 1) }}/5</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3">Chưa có dữ liệu sản phẩm phù hợp trong tháng</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Category Analytics Charts -->
            <div class="row g-4 mt-1">
                <div class="col-lg-6">
                    <div class="bg-light rounded p-4 h-100">
                        <h6 class="mb-3">Thống kê số lượng sản phẩm theo danh mục</h6>
                        <canvas id="productCountByCategoryChart" style="max-height: 320px;"></canvas>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="bg-light rounded p-4 h-100">
                        <h6 class="mb-3">Chi tiết số lượng sản phẩm theo danh mục</h6>
                        <div class="table-responsive">
                            <table class="table text-start align-middle table-bordered table-hover mb-0">
                                <thead>
                                    <tr class="text-dark">
                                        <th>Danh mục</th>
                                        <th>Số sản phẩm</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($productCountByCategory as $item)
                                        <tr>
                                            <td>{{ $item->category_name }}</td>
                                            <td>{{ number_format($item->product_count) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2">Chưa có dữ liệu danh mục sản phẩm</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Orders & Reviews -->
            <div class="row g-4 mt-1">
                <div class="col-lg-6">
                    <div class="bg-light rounded p-4 h-100">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="mb-0">Tóm tắt đơn hàng trong tháng</h6>
                            <span
                                class="badge {{ $cancelOrRefundRate >= 20 ? 'bg-danger' : ($cancelOrRefundRate >= 10 ? 'bg-warning text-dark' : 'bg-success') }}">
                                Hủy/hoàn: {{ number_format($cancelOrRefundRate, 2) }}%
                            </span>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="border rounded p-2 h-100">
                                    <small class="text-muted d-block">Tổng đơn trong tháng</small>
                                    <strong>{{ number_format(array_sum($orderStatusSummary)) }}</strong>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-2 h-100">
                                    <small class="text-muted d-block">Đơn hoàn thành</small>
                                    <strong class="text-success">{{ number_format($orderStatusSummary['completed']) }}</strong>
                                </div>
                            </div>
                        </div>
                        <canvas id="monthlyOrderSummaryChart" style="max-height: 300px;"></canvas>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="bg-light rounded p-4 h-100">
                        <h6 class="mb-3">Đánh giá gần đây</h6>
                        @forelse($recentReviews as $review)
                            <div class="border-bottom py-2">
                                <p class="mb-1"><strong>{{ $review->customer->user->name ?? 'Khách hàng' }}</strong> -
                                    {{ $review->product->name ?? 'Sản phẩm' }}
                                </p>
                                <p class="mb-1">Điểm: {{ number_format($review->rating, 1) }}/5</p>
                                <small class="text-muted">{{ $review->content ?: 'Không có nội dung.' }}</small>
                            </div>
                        @empty
                            <p class="mb-0 text-muted">Chưa có đánh giá.</p>
                        @endforelse
                        <p class="mt-3 mb-0">Đánh giá được duyệt tháng này:
                            <strong>{{ number_format($reviewsThisMonth) }}</strong>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Expiring Products Table -->
            <div class="row g-4 mt-1">
                <div class="col-12">
                    <div class="bg-light rounded p-4">
                        <h6 class="mb-3">Sản phẩm hết hạn trong tháng {{ $monthLabel }} cần ưu tiên xử lý</h6>
                        <div class="table-responsive">
                            <table class="table text-start align-middle table-bordered table-hover mb-0">
                                <thead>
                                    <tr class="text-dark">
                                        <th>Sản phẩm</th>
                                        <th>Mã sản phẩm</th>
                                        <th>Hạn dùng</th>
                                        <th>Tồn còn</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($expiringProductsToPush as $item)
                                        <tr>
                                            <td>{{ $item->product_name }}</td>
                                            <td>{{ $item->sku ?: 'Không có' }}</td>
                                            <td>{{ \Carbon\Carbon::parse($item->expired_at)->format('d/m/Y') }}</td>
                                            <td>{{ number_format($item->remaining_qty) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4">Không có sản phẩm hết hạn trong tháng đã chọn</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const dailyRevenueLabels = @json($dashboardCharts['daily_revenue']['labels']);
            const dailyRevenueValues = @json($dashboardCharts['daily_revenue']['values']);

            const orderStatusLabels = @json($dashboardCharts['order_status']['labels']);
            const orderStatusData = @json($dashboardCharts['order_status']['data']);

            const categoryMixLabels = @json($dashboardCharts['category_mix']['labels']);
            const categoryMixData = @json($dashboardCharts['category_mix']['data']);

            const productCountByCategoryLabels = @json($dashboardCharts['product_count_by_category']['labels']);
            const productCountByCategoryData = @json($dashboardCharts['product_count_by_category']['data']);

            const monthlyOrderSummaryLabels = [
                'Chờ xử lý',
                'Đã xác nhận',
                'Đang giao',
                'Hoàn thành',
                'Đã hủy',
                'Đã hoàn tiền'
            ];
            const monthlyOrderSummaryData = [
                @json((int) $orderStatusSummary['pending']),
                @json((int) $orderStatusSummary['confirmed']),
                @json((int) $orderStatusSummary['shipping']),
                @json((int) $orderStatusSummary['completed']),
                @json((int) $orderStatusSummary['cancelled']),
                @json((int) $orderStatusSummary['refunded'])
            ];

            const moneyFormat = (value) => {
                return new Intl.NumberFormat('vi-VN').format(Math.round(value || 0)) + ' ₫';
            };

            const dailyRevenueEl = document.getElementById('dailyRevenueChart');
            if (dailyRevenueEl && dailyRevenueLabels.length) {
                new Chart(dailyRevenueEl, {
                    type: 'line',
                    data: {
                        labels: dailyRevenueLabels,
                        datasets: [{
                            label: 'Tiền bán trong ngày',
                            data: dailyRevenueValues,
                            borderColor: '#0d6efd',
                            backgroundColor: 'rgba(13,110,253,0.16)',
                            fill: true,
                            tension: 0.3
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                ticks: {
                                    callback: (value) => moneyFormat(value)
                                }
                            }
                        }
                    }
                });
            }

            const orderStatusEl = document.getElementById('orderStatusChart');
            if (orderStatusEl && orderStatusLabels.length) {
                new Chart(orderStatusEl, {
                    type: 'doughnut',
                    data: {
                        labels: orderStatusLabels,
                        datasets: [{
                            data: orderStatusData,
                            backgroundColor: ['#0d6efd', '#20c997', '#ffc107', '#dc3545', '#6c757d', '#0dcaf0']
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
            }

            const categoryMixEl = document.getElementById('categoryMixChart');
            if (categoryMixEl && categoryMixLabels.length) {
                new Chart(categoryMixEl, {
                    type: 'pie',
                    data: {
                        labels: categoryMixLabels,
                        datasets: [{
                            data: categoryMixData,
                            backgroundColor: ['#0d6efd', '#20c997', '#ffc107', '#fd7e14', '#6f42c1', '#dc3545']
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
            }

            const productCountByCategoryEl = document.getElementById('productCountByCategoryChart');
            if (productCountByCategoryEl && productCountByCategoryLabels.length) {
                new Chart(productCountByCategoryEl, {
                    type: 'bar',
                    data: {
                        labels: productCountByCategoryLabels,
                        datasets: [{
                            label: 'Số lượng sản phẩm',
                            data: productCountByCategoryData,
                            backgroundColor: 'rgba(13, 110, 253, 0.72)'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
            }

            const monthlyOrderSummaryEl = document.getElementById('monthlyOrderSummaryChart');
            if (monthlyOrderSummaryEl) {
                new Chart(monthlyOrderSummaryEl, {
                    type: 'bar',
                    data: {
                        labels: monthlyOrderSummaryLabels,
                        datasets: [{
                            label: 'Số đơn',
                            data: monthlyOrderSummaryData,
                            backgroundColor: ['#ffc107', '#0d6efd', '#0dcaf0', '#198754', '#dc3545', '#6c757d'],
                            borderRadius: 6,
                            barThickness: 18,
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: (context) => `${context.raw} đơn`
                                }
                            }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            }
                        }
                    }
                });
            }
        })();
    </script>
@endpush