@extends('admin.layouts.layout_admin')

@section('content')
    <div class="container-fluid pt-4 px-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
            <div>
                <h5 class="mb-1">Báo Cáo Doanh Thu</h5>
                <small class="text-muted">Tháng {{ $monthLabel }}</small>
            </div>
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <form method="GET" action="{{ route('admin.revenue.stats') }}" class="d-flex gap-2 align-items-center">
                    <input name="month" type="month" class="form-control form-control-sm" value="{{ $selectedMonth }}">
                    <button type="submit" class="btn btn-sm btn-primary">Xem</button>
                </form>
                <a href="{{ route('admin.revenue.export.excel', ['month' => $selectedMonth]) }}"
                    class="btn btn-sm btn-outline-success">Excel</a>
                <a href="{{ route('admin.revenue.export.pdf', ['month' => $selectedMonth]) }}"
                    class="btn btn-sm btn-outline-danger">PDF</a>
                <a href="{{ route('admin.dashboard', ['month' => $selectedMonth]) }}"
                    class="btn btn-sm btn-outline-primary">Quay Lại</a>
            </div>
        </div>

        <!-- 4 KPI - Simple Bootstrap Colors -->
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded overflow-hidden">
                    <div class="card-body p-4 light-primary text-dark">
                        <small class="d-block mb-1">Tổng Tiền Bán Ra</small>
                        <h5 class="mb-0">{{ number_format($revenueSummary['gross_sale'], 0, ',', '.') }} ₫</h5>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded overflow-hidden">
                    <div class="card-body p-4 light-danger text-dark">
                        <small class="d-block mb-1">Tiền Đã Hoàn Khách</small>
                        <h5 class="mb-0">{{ number_format($revenueSummary['refund_amount'], 0, ',', '.') }} ₫</h5>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded overflow-hidden">
                    <div class="card-body p-4 light-info text-dark">
                        <small class="d-block mb-1">Tiền Thực Nhận</small>
                        <h5 class="mb-0">{{ number_format($revenueSummary['net_revenue'], 0, ',', '.') }} ₫</h5>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded overflow-hidden">
                    <div class="card-body p-4 text-dark"
                        style="background: {{ $revenueSummary['estimated_profit'] >= 0 ? '#e8f5e9' : '#ffe7e7' }};">
                        <small class="d-block mb-1">Lãi Ước Tính</small>
                        <h5 class="mb-0">{{ number_format($revenueSummary['estimated_profit'], 0, ',', '.') }} ₫</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart: Daily Revenue -->
    <div class="container-fluid pt-2 px-4 pb-4">
        <div class="row g-3">
            <div class="col-lg-12">
                <div class=\"card border-0 shadow-sm\">
                    <div class=\"card-header light-primary text-dark\">
                        <h6 class="\" mb-0\">Tiền Thực Nhận Theo Ngày Trong Tháng</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="revenueMonthlyChart" style="max-height: 320px;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts: Refund Ratio & Payment Methods -->
    <div class="container-fluid pt-2 px-4 pb-4">
        <div class="row g-3">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-danger text-white">
                        <h6 class="mb-0">Tỷ Lệ Hoàn Tiền Theo Phương Thức Thanh Toán</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 h-100">
                            <div class="col-lg-6 d-flex align-items-center justify-content-center">
                                <div style="height: 280px; width: 100%;">
                                    <canvas id="revenueWeeklyChart"></canvas>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                @forelse($refundByMethod as $item)
                                    <div class="d-flex justify-content-between border-bottom py-2 small mb-2">
                                        <span class="fw-bold">{{ $item->method_label }}</span>
                                        <span>
                                            <span
                                                class="badge bg-info">{{ number_format((int) max(0, $item->refund_count ?? 0), 0) }}/{{ number_format((int) max(0, $item->paid_count ?? 0), 0) }}</span>
                                            <span
                                                class="badge {{ $item->refund_rate > 5 ? 'bg-danger' : 'bg-success' }}">{{ number_format($item->refund_rate, 2) }}%</span>
                                        </span>
                                    </div>
                                @empty
                                    <p class="mb-0 text-muted text-center">Không có dữ liệu hoàn tiền</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">Số Đơn Theo Phương Thức Thanh Toán</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="revenuePaymentChart" style="max-height: 310px;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cost Breakdown Section -->
    <div class="container-fluid pt-2 px-4 pb-4">
        <div class="row g-3">
            <div class="col-lg-6">
                @php
                    $totalMonthlyCost =
                        (float) $revenueSummary['import_cost'] +
                        (float) $revenueSummary['salary_cost'] +
                        (float) $revenueSummary['shipping_cost'] +
                        (float) $revenueSummary['cogs'] +
                        (float) ($revenueSummary['total_writeoff_cost'] ?? 0);
                    $shipRate = (float) ($revenueSummary['ship_to_revenue_rate'] ?? 0);
                    $costItems = [
                        ['label' => 'Nhập hàng', 'value' => (float) $revenueSummary['import_cost'], 'color' => '#0d6efd'],
                        ['label' => 'Lương nhân sự', 'value' => (float) $revenueSummary['salary_cost'], 'color' => '#6f42c1'],
                        ['label' => 'Phí giao hàng', 'value' => (float) $revenueSummary['shipping_cost'], 'color' => '#fd7e14'],
                        ['label' => 'Giá vốn đã bán', 'value' => (float) $revenueSummary['cogs'], 'color' => '#dc3545'],
                        ['label' => 'Lỗ hàng hết hạn/hư hỏng', 'value' => (float) ($revenueSummary['total_writeoff_cost'] ?? 0), 'color' => '#6c757d'],
                    ];
                @endphp
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">Chi Tiết Chi Phí Kinh Doanh</h6>
                    </div>
                    <div class="card-body">
                        <div class="bg-primary text-white rounded p-3 mb-3">
                            <small class="d-block mb-1">Tổng Chi Phí Tháng {{ $monthLabel }}</small>
                            <h4 class="mb-0">{{ number_format($totalMonthlyCost, 0, ',', '.') }} ₫</h4>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="border rounded p-2 text-center">
                                    <small class="text-muted d-block mb-1">Nhập Hàng</small>
                                    <strong
                                        class="text-primary">{{ number_format($revenueSummary['import_cost'], 0, ',', '.') }}
                                        ₫</strong>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-2 text-center">
                                    <small class="text-muted d-block mb-1">Lương NV</small>
                                    <strong
                                        class="text-primary">{{ number_format($revenueSummary['salary_cost'], 0, ',', '.') }}
                                        ₫</strong>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-2 text-center">
                                    <small class="text-muted d-block mb-1">Phí Giao</small>
                                    <strong
                                        class="text-primary">{{ number_format($revenueSummary['shipping_cost'], 0, ',', '.') }}
                                        ₫</strong>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-2 text-center">
                                    <small class="text-muted d-block mb-1">Giá Vốn</small>
                                    <strong class="text-primary">{{ number_format($revenueSummary['cogs'], 0, ',', '.') }}
                                        ₫</strong>
                                </div>
                            </div>
                            @if(($revenueSummary['total_writeoff_cost'] ?? 0) > 0)
                                <div class="col-12">
                                    <div class="border border-2 border-danger rounded p-2 bg-white text-center">
                                        <small class="text-muted d-block mb-1">Hàng Hết Hạn & Hư Hỏng</small>
                                        <strong
                                            class="text-danger">{{ number_format($revenueSummary['total_writeoff_cost'], 0, ',', '.') }}
                                            ₫</strong>
                                        <div class="mt-2">
                                            <span class="badge bg-warning text-dark me-2">Hết hạn:
                                                {{ number_format($revenueSummary['writeoff_cost'], 0, ',', '.') }} ₫</span>
                                            <span class="badge bg-danger">Hư:
                                                {{ number_format($revenueSummary['damaged_loss'], 0, ',', '.') }} ₫</span>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <p class="small mb-2"><strong>Tỷ Lệ Chi Phí:</strong></p>
                        <div class="row g-2 align-items-center">
                            <div class="col-lg-5">
                                <canvas id="monthlyCostBreakdownChart" style="max-height: 220px;"></canvas>
                            </div>
                            <div class="col-lg-7">
                                @foreach($costItems as $item)
                                    @php
                                        $ratio = $totalMonthlyCost > 0 ? round(($item['value'] / $totalMonthlyCost) * 100, 2) : 0;
                                    @endphp
                                    <div class="mb-2">
                                        <div class="d-flex justify-content-between small mb-1">
                                            <span>{{ $item['label'] }}</span>
                                            <strong>{{ number_format($ratio, 2) }}%</strong>
                                        </div>
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar" role="progressbar"
                                                style="width: {{ $ratio }}%; background-color: {{ $item['color'] }};"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="mt-3 p-2 rounded" style="background: #f0f8ff;">
                            <small class="text-muted">Phí giao/Doanh thu:</small>
                            <span
                                class="badge {{ $shipRate >= 8 ? 'bg-danger' : ($shipRate >= 4 ? 'bg-warning text-dark' : 'bg-success') }}">
                                {{ number_format($shipRate, 2) }}%
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-danger text-white">
                        <h6 class="mb-0">Phân Tích Hủy & Hoàn Tiền</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-lg-6">
                                <p class="small fw-bold mb-2">Lý Do Hủy Đơn</p>
                                @if($cancelByReason->isNotEmpty())
                                    <canvas id="cancelReasonChart" style="max-height: 200px;"></canvas>
                                    <div class="mt-2">
                                        @foreach($cancelByReason as $item)
                                            <div class="d-flex justify-content-between border-bottom py-1 small">
                                                <span>{{ $item->reason_label }}</span>
                                                <span class="badge bg-warning text-dark">{{ number_format($item->rate, 2) }}%</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="mb-0 text-muted text-center py-3">Không có đơn hủy</p>
                                @endif
                            </div>
                            <div class="col-lg-6">
                                <p class="small fw-bold mb-2">Lý Do Hoàn Tiền</p>
                                @if($refundByReason->isNotEmpty())
                                    <canvas id="refundReasonChart" style="max-height: 200px;"></canvas>
                                    <div class="mt-2">
                                        @foreach($refundByReason as $item)
                                            <div class="d-flex justify-content-between border-bottom py-1 small">
                                                <span>{{ $item->reason_label }}</span>
                                                <span
                                                    class="badge bg-danger">{{ number_format((int) max(0, $item->total ?? 0), 0) }}
                                                    lượt</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="mb-0 text-muted text-center py-3">Không có hoàn tiền</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid pt-4 px-4 pb-4">
        <div class="row g-4">
            <div class="col-lg-12">
                <div class="row g-4">
                    <div class="col-lg-6">
                        <div class="bg-light rounded p-4 h-100">
                            <h6 class="mb-3">Báo cáo nhập hàng theo nhà cung cấp</h6>
                            <div class="table-responsive">
                                <table class="table text-start align-middle table-bordered table-hover mb-0">
                                    <thead>
                                        <tr class="text-dark">
                                            <th>Nhà cung cấp</th>
                                            <th>Số lần nhập</th>
                                            <th>Giá trị nhập</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($importsBySupplier as $item)
                                            <tr>
                                                <td>{{ $item->supplier_name }}</td>
                                                <td>{{ number_format((int) max(0, $item->import_count ?? 0), 0) }}</td>
                                                <td>{{ number_format((int) max(0, $item->total_value ?? 0), 0, ',', '.') }} ₫
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3">Tháng này chưa có dữ liệu nhập theo nhà cung cấp</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="bg-light rounded p-4 h-100">
                            <h6 class="mb-3">Báo cáo nhập hàng theo nhóm sản phẩm</h6>
                            <div class="table-responsive">
                                <table class="table text-start align-middle table-bordered table-hover mb-0">
                                    <thead>
                                        <tr class="text-dark">
                                            <th>Nhóm sản phẩm</th>
                                            <th>Số lượng nhập</th>
                                            <th>Giá trị nhập</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($importsByCategory as $item)
                                            <tr>
                                                <td>{{ $item->category_name }}</td>
                                                <td>{{ number_format((int) max(0, $item->total_qty ?? 0), 0) }}</td>
                                                <td>{{ number_format((int) max(0, $item->total_value ?? 0), 0, ',', '.') }} ₫
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3">Tháng này chưa có dữ liệu nhập theo nhóm</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Bảng lỗ hàng hết hạn / hư hỏng --}}
    @if(isset($writeoffDetails) && $writeoffDetails->isNotEmpty())
        <div class="container-fluid pt-4 px-4">
            <div class="row g-4">
                <div class="col-lg-12">
                    <div class="bg-light rounded p-4 border border-danger">
                        <h6 class="mb-3 text-danger">

                            Chi tiết lỗ hàng hết hạn &amp; hư hỏng tháng {{ $monthLabel }}
                        </h6>
                        <div class="table-responsive">
                            <table class="table text-start align-middle table-bordered table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Sản phẩm</th>
                                        <th>SKU</th>
                                        <th>Lý do</th>
                                        <th>Số lượng xuất kho</th>
                                        <th>Thiệt hại (tiền nhập)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($writeoffDetails as $row)
                                        <tr>
                                            <td>{{ $row->product_name }}</td>
                                            <td>{{ $row->sku }}</td>
                                            <td>
                                                <span
                                                    class="badge {{ $row->reason === 'expired' ? 'bg-danger' : 'bg-warning text-dark' }}">
                                                    {{ $row->reason_label }}
                                                </span>
                                            </td>
                                            <td>{{ number_format((int) max(0, $row->total_qty ?? 0), 0) }}</td>
                                            <td class="text-danger fw-bold">
                                                {{ number_format((int) max(0, $row->total_cost ?? 0), 0, ',', '.') }} ₫
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="4" class="text-end fw-bold">Tổng thiệt hại:</td>
                                        <td class="text-danger fw-bold">
                                            {{ number_format($writeoffDetails->sum('total_cost'), 0, ',', '.') }} ₫
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="container-fluid pt-4 px-4 pb-4">
        <div class="row g-4">
            <div class="col-lg-12">
                <div class="bg-light rounded p-4 h-100">
                    <h6 class="mb-3">Chi phí nhân sự theo bộ phận (tháng {{ $monthLabel }})</h6>
                    <div class="table-responsive">
                        <table class="table text-start align-middle table-bordered table-hover mb-0">
                            <thead>
                                <tr class="text-dark">
                                    <th>Bộ phận</th>
                                    <th>Số nhân sự</th>
                                    <th>Chi phí lương</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($salaryByDepartment as $item)
                                    <tr>
                                        <td>{{ $item->department_label }}</td>
                                        <td>{{ number_format((int) max(0, $item->staff_count ?? 0), 0) }}</td>
                                        <td>{{ number_format((int) max(0, $item->final_salary ?? 0), 0, ',', '.') }} ₫</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3">Tháng này chưa có dữ liệu lương theo bộ phận</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        (function () {
            const monthlyLabels = @json($monthlyFinance['labels']);
            const monthlyNetRevenue = @json($monthlyFinance['values']);

            const refundMethodLabels = @json($refundByMethod->pluck('method_label'));
            const refundMethodRates = @json($refundByMethod->pluck('refund_rate'));

            const paymentLabels = @json($paymentMethod['labels']);
            const paymentCounts = @json($paymentMethod['counts']);
            const cancelReasonLabels = @json($cancelByReason->pluck('reason_label'));
            const cancelReasonData = @json($cancelByReason->pluck('total'));
            const refundReasonLabels = @json($refundByReason->pluck('reason_label'));
            const refundReasonData = @json($refundByReason->pluck('total'));

            const costLabels = ['Nhập hàng', 'Lương nhân sự', 'Phí giao hàng', 'Giá vốn đã bán'];
            const costData = [
                @json((float) $revenueSummary['import_cost']),
                @json((float) $revenueSummary['salary_cost']),
                @json((float) $revenueSummary['shipping_cost']),
                @json((float) $revenueSummary['cogs'])
            ];

            const moneyFormat = (value) => {
                return new Intl.NumberFormat('vi-VN').format(Math.round(value || 0)) + ' ₫';
            };

            const monthlyEl = document.getElementById('revenueMonthlyChart');
            if (monthlyEl && monthlyLabels.length) {
                new Chart(monthlyEl, {
                    type: 'line',
                    data: {
                        labels: monthlyLabels,
                        datasets: [{
                            label: 'Tiền thực nhận theo ngày',
                            data: monthlyNetRevenue,
                            borderColor: '#0d6efd',
                            backgroundColor: 'rgba(13, 110, 253, 0.14)',
                            fill: true,
                            tension: 0.35
                        }
                        ]
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
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: (context) => `${context.dataset.label}: ${moneyFormat(context.raw)}`
                                }
                            }
                        }
                    }
                });
            }

            const weeklyEl = document.getElementById('revenueWeeklyChart');
            if (weeklyEl && refundMethodLabels.length) {
                new Chart(weeklyEl, {
                    type: 'bar',
                    data: {
                        labels: refundMethodLabels,
                        datasets: [{
                            label: 'Tỷ lệ hoàn (%)',
                            data: refundMethodRates,
                            backgroundColor: 'rgba(220, 53, 69, 0.75)'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                ticks: {
                                    callback: (value) => `${value}%`
                                }
                            }
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: (context) => `${context.dataset.label}: ${context.raw}%`
                                }
                            }
                        }
                    }
                });
            }

            const paymentEl = document.getElementById('revenuePaymentChart');
            if (paymentEl && paymentLabels.length) {
                new Chart(paymentEl, {
                    type: 'pie',
                    data: {
                        labels: paymentLabels,
                        datasets: [{
                            data: paymentCounts,
                            backgroundColor: ['#0d6efd', '#198754', '#ffc107', '#6f42c1', '#dc3545']
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
            }

            const monthlyCostEl = document.getElementById('monthlyCostBreakdownChart');
            if (monthlyCostEl) {
                new Chart(monthlyCostEl, {
                    type: 'doughnut',
                    data: {
                        labels: costLabels,
                        datasets: [{
                            data: costData,
                            backgroundColor: ['#0d6efd', '#6f42c1', '#fd7e14', '#dc3545']
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: (context) => `${context.label}: ${moneyFormat(context.raw)}`
                                }
                            }
                        }
                    }
                });
            }

            const cancelReasonEl = document.getElementById('cancelReasonChart');
            if (cancelReasonEl && cancelReasonLabels.length) {
                new Chart(cancelReasonEl, {
                    type: 'doughnut',
                    data: {
                        labels: cancelReasonLabels,
                        datasets: [{
                            data: cancelReasonData,
                            backgroundColor: ['#dc3545', '#fd7e14', '#ffc107', '#20c997', '#0d6efd', '#6f42c1']
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: (context) => `${context.label}: ${context.raw} đơn`
                                }
                            }
                        }
                    }
                });
            }

            const refundReasonEl = document.getElementById('refundReasonChart');
            if (refundReasonEl && refundReasonLabels.length) {
                new Chart(refundReasonEl, {
                    type: 'doughnut',
                    data: {
                        labels: refundReasonLabels,
                        datasets: [{
                            data: refundReasonData,
                            backgroundColor: ['#0d6efd', '#20c997', '#ffc107', '#fd7e14', '#6f42c1', '#dc3545']
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: (context) => `${context.label}: ${context.raw} lượt`
                                }
                            }
                        }
                    }
                });
            }

        })();
    </script>
@endpush