<!-- Modal Thống Kê Chi Tiết -->
<div class="modal fade" id="statsDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <!-- Header -->
            <div class="modal-header bg-primary text-white">
                <div>
                    <h5 class="modal-title mb-0">Thống Kê Chi Tiết</h5>
                    <small class="text-white-50" id="statsModalPeriod">Tháng {{ $monthLabel ?? '' }}</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>

            <div class="modal-body p-0">
                <!-- Tabs Navigation -->
                <ul class="nav nav-tabs border-0 sticky-top bg-light" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="tab-revenue-btn" data-bs-toggle="tab"
                            data-bs-target="#tab-revenue" type="button" role="tab" aria-controls="tab-revenue"
                            aria-selected="true">
                            Doanh Thu
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-orders-btn" data-bs-toggle="tab" data-bs-target="#tab-orders"
                            type="button" role="tab" aria-controls="tab-orders" aria-selected="false">
                            Đơn Hàng
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-customers-btn" data-bs-toggle="tab"
                            data-bs-target="#tab-customers" type="button" role="tab" aria-controls="tab-customers"
                            aria-selected="false">
                            Khách Hàng
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-products-btn" data-bs-toggle="tab"
                            data-bs-target="#tab-products" type="button" role="tab" aria-controls="tab-products"
                            aria-selected="false">
                            Sản Phẩm
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-accounts-btn" data-bs-toggle="tab"
                            data-bs-target="#tab-accounts" type="button" role="tab" aria-controls="tab-accounts"
                            aria-selected="false">
                            Tài Khoản
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-inventory-btn" data-bs-toggle="tab"
                            data-bs-target="#tab-inventory" type="button" role="tab" aria-controls="tab-inventory"
                            aria-selected="false">
                            Kho Hàng
                        </button>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content p-3">
                    <!-- TAB 1: DOANH THU -->
                    <div class="tab-pane fade show active" id="tab-revenue" role="tabpanel"
                        aria-labelledby="tab-revenue-btn">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded">
                                    <small class="text-muted">Tổng tiền bán ra</small>
                                    <h5 class="text-primary mb-0">
                                        {{ number_format($revenueSummary['gross_sale'] ?? 0, 0, ',', '.') }} ₫</h5>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded">
                                    <small class="text-muted">Tiền đã hoàn cho khách</small>
                                    <h5 class="text-danger mb-0">
                                        -{{ number_format($revenueSummary['refund_amount'] ?? 0, 0, ',', '.') }} ₫</h5>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded">
                                    <small class="text-muted">Tiền thực nhận</small>
                                    <h5 class="text-success mb-0">
                                        {{ number_format($revenueSummary['net_revenue'] ?? 0, 0, ',', '.') }} ₫</h5>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded">
                                    <small class="text-muted">Lợi nhuận ước tính</small>
                                    <h5 class="mb-0"
                                        style="color: {{ ($revenueSummary['estimated_profit'] ?? 0) >= 0 ? '#28a745' : '#dc3545' }}">
                                        {{ number_format($revenueSummary['estimated_profit'] ?? 0, 0, ',', '.') }} ₫
                                    </h5>
                                </div>
                            </div>
                        </div>

                        <!-- Accordion: Chi tiết theo phương thức thanh toán -->
                        <div class="accordion mt-3" id="accordionPayment">
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapsePayment">
                                        Theo Phương Thức Thanh Toán
                                    </button>
                                </h2>
                                <div id="collapsePayment" class="accordion-collapse collapse show"
                                    data-bs-parent="#accordionPayment">
                                    <div class="accordion-body p-2">
                                        <table class="table table-sm table-borderless mb-0">
                                            <thead>
                                                <tr class="border-bottom">
                                                    <th>Phương thức</th>
                                                    <th class="text-end">Số đơn</th>
                                                    <th class="text-end">Doanh thu</th>
                                                    <th class="text-end">Hoàn tiền</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($refundByMethod ?? [] as $item)
                                                    <tr class="border-bottom">
                                                        <td><small>{{ $item->method_label ?? 'N/A' }}</small></td>
                                                        <td class="text-end"><small>{{ $item->paid_count ?? 0 }}</small>
                                                        </td>
                                                        <td class="text-end text-success">
                                                            <small>{{ number_format($item->total_revenue ?? 0, 0, ',', '.') }}
                                                                ₫</small></td>
                                                        <td class="text-end text-danger">
                                                            <small>{{ number_format($item->refund_count ?? 0, 0, ',', '.') }}</small>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="4" class="text-center text-muted py-3">Không có dữ liệu
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 2: ĐƠN HÀNG -->
                    <div class="tab-pane fade" id="tab-orders" role="tabpanel" aria-labelledby="tab-orders-btn">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="p-3 bg-light rounded text-center">
                                    <small class="text-muted d-block mb-1">Tổng số đơn</small>
                                    <h4 class="text-primary mb-0">{{ $orderStats['total'] ?? 0 }}</h4>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 bg-light rounded text-center">
                                    <small class="text-muted d-block mb-1">Hoàn thành</small>
                                    <h4 class="text-success mb-0">{{ $orderStats['completed'] ?? 0 }}</h4>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 bg-light rounded text-center">
                                    <small class="text-muted d-block mb-1">Đang xử lý</small>
                                    <h4 class="text-info mb-0">
                                        {{ ($orderStats['pending'] ?? 0) + ($orderStats['confirmed'] ?? 0) }}</h4>
                                </div>
                            </div>
                        </div>

                        <!-- Accordion: Chi tiết trạng thái -->
                        <div class="accordion mt-3" id="accordionOrders">
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapseOrders">
                                        Theo Trạng Thái
                                    </button>
                                </h2>
                                <div id="collapseOrders" class="accordion-collapse collapse show"
                                    data-bs-parent="#accordionOrders">
                                    <div class="accordion-body p-2">
                                        <table class="table table-sm table-borderless mb-0">
                                            <tbody>
                                                @php
                                                    $statusLabels = [
                                                        'pending' => ['Chờ xử lý', 'warning'],
                                                        'confirmed' => ['Đã xác nhận', 'info'],
                                                        'shipping' => ['Đang giao', 'primary'],
                                                        'completed' => ['Hoàn thành', 'success'],
                                                        'cancelled' => ['Đã hủy', 'secondary'],
                                                        'refund_requested' => ['Yêu cầu hoàn hàng', 'warning'],
                                                        'refunded' => ['Đã hoàn tiền', 'danger'],
                                                    ];
                                                @endphp
                                                @foreach($statusLabels as $status => $label)
                                                    @if(isset($orderStats[$status]))
                                                        <tr class="border-bottom">
                                                            <td>
                                                                <span class="badge bg-{{ $label[1] }}">{{ $label[0] }}</span>
                                                            </td>
                                                            <td class="text-end"><strong>{{ $orderStats[$status] }}</strong>
                                                            </td>
                                                        </tr>
                                                    @endif
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 3: KHÁCH HÀNG -->
                    <div class="tab-pane fade" id="tab-customers" role="tabpanel" aria-labelledby="tab-customers-btn">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="p-3 bg-light rounded text-center">
                                    <small class="text-muted d-block mb-1">Khách mới</small>
                                    <h4 class="text-primary mb-0">{{ $newCustomersThisMonth ?? 0 }}</h4>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 bg-light rounded text-center">
                                    <small class="text-muted d-block mb-1">Khách quay lại</small>
                                    <h4 class="text-success mb-0">{{ number_format($returningCustomerRate ?? 0, 2) }}%
                                    </h4>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 bg-light rounded text-center">
                                    <small class="text-muted d-block mb-1">Tài khoản hoạt động</small>
                                    <h4 class="text-success mb-0">{{ $accountStats['active_accounts'] ?? 0 }}</h4>
                                </div>
                            </div>
                        </div>

                        <!-- Accordion: Chi tiết tài khoản -->
                        <div class="accordion mt-3" id="accordionAccounts">
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapseAccountsDetail">
                                        Trạng Thái Tài Khoản
                                    </button>
                                </h2>
                                <div id="collapseAccountsDetail" class="accordion-collapse collapse show"
                                    data-bs-parent="#accordionAccounts">
                                    <div class="accordion-body p-2">
                                        <table class="table table-sm table-borderless mb-0">
                                            <tbody>
                                                <tr class="border-bottom">
                                                    <td><span class="badge bg-success">Hoạt động</span></td>
                                                    <td class="text-end">
                                                        <strong>{{ $accountStats['active_accounts'] ?? 0 }}</strong>
                                                    </td>
                                                </tr>
                                                <tr class="border-bottom">
                                                    <td><span class="badge bg-danger">Bị khóa</span></td>
                                                    <td class="text-end">
                                                        <strong>{{ $accountStats['locked_accounts'] ?? 0 }}</strong>
                                                    </td>
                                                </tr>
                                                <tr class="border-bottom">
                                                    <td><span class="badge bg-primary">Mới tạo</span></td>
                                                    <td class="text-end">
                                                        <strong>{{ $accountStats['new_accounts'] ?? 0 }}</strong></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 4: SẢN PHẨM -->
                    <div class="tab-pane fade" id="tab-products" role="tabpanel" aria-labelledby="tab-products-btn">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded text-center">
                                    <small class="text-muted d-block mb-1">Đánh giá mới</small>
                                    <h4 class="text-warning mb-0">{{ $reviewsThisMonth ?? 0 }}</h4>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded text-center">
                                    <small class="text-muted d-block mb-1">Yêu thích</small>
                                    <h4 class="text-info mb-0">{{ $wishlistInteractions ?? 0 }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 5: TÀI KHOẢN -->
                    <div class="tab-pane fade" id="tab-accounts" role="tabpanel" aria-labelledby="tab-accounts-btn">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="p-3 bg-light rounded text-center">
                                    <small class="text-muted d-block mb-1">Tài khoản mới</small>
                                    <h4 class="text-primary mb-0">{{ $accountStats['new_accounts'] ?? 0 }}</h4>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 bg-light rounded text-center">
                                    <small class="text-muted d-block mb-1">Đang hoạt động</small>
                                    <h4 class="text-success mb-0">{{ $accountStats['active_accounts'] ?? 0 }}</h4>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 bg-light rounded text-center">
                                    <small class="text-muted d-block mb-1">Bị khóa</small>
                                    <h4 class="text-danger mb-0">{{ $accountStats['locked_accounts'] ?? 0 }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 6: KHO HÀNG -->
                    <div class="tab-pane fade" id="tab-inventory" role="tabpanel" aria-labelledby="tab-inventory-btn">
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <div class="p-3 bg-light rounded text-center">
                                    <small class="text-muted d-block mb-1">Chi phí hủy tháng này</small>
                                    <h4 class="text-danger mb-0">
                                        {{ number_format($writeoffMetrics['total_writeoff_cost_month'] ?? 0, 0, ',', '.') }}
                                        ₫</h4>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 bg-light rounded text-center">
                                    <small class="text-muted d-block mb-1">Số lượng hủy</small>
                                    <h4 class="text-warning mb-0">{{ $writeoffMetrics['writeoff_count_month'] ?? 0 }}
                                    </h4>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 bg-light rounded text-center">
                                    <small class="text-muted d-block mb-1">Hàng sắp hết hạn</small>
                                    <h4 class="text-info mb-0">{{ $writeoffMetrics['expiring_soon_count'] ?? 0 }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="modal-footer bg-light">
                <small class="text-muted me-auto">
                    <i class="fa fa-info-circle"></i>
                    Cập nhật lần cuối: <span id="lastUpdated">{{ now()->format('H:i') }}</span>
                </small>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Đóng</button>
                @if(Auth::user()->role === 'admin')
                    <a href="{{ route('admin.revenue.stats', ['month' => $selectedMonth ?? '']) }}"
                        class="btn btn-sm btn-primary">
                        Xem báo cáo đầy đủ
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Cập nhật thời gian tự động
        function updateTime() {
            const now = new Date();
            document.getElementById('lastUpdated').textContent =
                now.getHours().toString().padStart(2, '0') + ':' +
                now.getMinutes().toString().padStart(2, '0');
        }
        updateTime();
        setInterval(updateTime, 60000);

        // Hỗ trợ mở tab cụ thể từ JS
        window.openStatsTab = function (tabName) {
            const tab = document.getElementById('tab-' + tabName + '-btn');
            if (tab) {
                const modal = new bootstrap.Modal(document.getElementById('statsDetailModal'));
                modal.show();
                setTimeout(() => tab.click(), 100);
            }
        };
    });
</script>