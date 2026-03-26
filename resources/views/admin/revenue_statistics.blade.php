@extends('admin.layouts.layout_admin')

@section('content')
    <div class="container-fluid pt-4 px-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
            <h5 class="mb-0">Báo cáo doanh thu tháng {{ $monthLabel }}</h5>
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <form method="GET" action="{{ route('admin.revenue.stats') }}" class="d-flex gap-2 align-items-center">
                    <input name="month" type="month" class="form-control form-control-sm" value="{{ $selectedMonth }}">
                    <button type="submit" class="btn btn-sm btn-primary">Xem</button>
                </form>
                <a href="{{ route('admin.revenue.export.excel', ['month' => $selectedMonth]) }}"
                    class="btn btn-sm btn-outline-success">Xuất Excel</a>
                <a href="{{ route('admin.revenue.export.pdf', ['month' => $selectedMonth]) }}"
                    class="btn btn-sm btn-outline-danger">Xuất PDF</a>
                <a href="{{ route('admin.dashboard', ['month' => $selectedMonth]) }}"
                    class="btn btn-sm btn-outline-primary">Quay lại tổng quan</a>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-sm-6 col-xl-3">
                <div class="bg-primary text-white rounded p-4 h-100">
                    <p class="mb-2">Tổng tiền bán ra</p>
                    <h6 class="mb-0 text-white">{{ number_format($revenueSummary['gross_sale'], 0, ',', '.') }} ₫</h6>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="bg-light rounded p-4 h-100">
                    <p class="mb-2">Tiền đã hoàn cho khách</p>
                    <h6 class="mb-0 text-danger">{{ number_format($revenueSummary['refund_amount'], 0, ',', '.') }} ₫</h6>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="bg-light rounded p-4 h-100">
                    <p class="mb-2">Tiền thực nhận</p>
                    <h6 class="mb-0 text-primary">{{ number_format($revenueSummary['net_revenue'], 0, ',', '.') }} ₫</h6>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="bg-light rounded p-4 h-100">
                    <p class="mb-2">Lãi ước tính sau chi phí</p>
                    <h6 class="mb-0 {{ $revenueSummary['estimated_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ number_format($revenueSummary['estimated_profit'], 0, ',', '.') }} ₫
                    </h6>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid pt-4 px-4">
        <div class="row g-4">
            <div class="col-lg-12">
                <div class="bg-light rounded p-4 h-100">
                    <h6 class="mb-3">Tiền thực nhận theo từng ngày trong tháng</h6>
                    <canvas id="revenueMonthlyChart" style="max-height: 340px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid pt-4 px-4">
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="bg-light rounded p-4 h-100">
                    <h6 class="mb-3">Tỷ lệ hoàn tiền theo phương thức thanh toán</h6>
                    <div class="row g-3 align-items-stretch h-100">
                        <div class="col-lg-6 d-flex align-items-center">
                            <div class="w-100" style="height: 320px;">
                                <canvas id="revenueWeeklyChart"></canvas>
                            </div>
                        </div>
                        <div class="col-lg-6 d-flex flex-column justify-content-center">
                            @forelse($refundByMethod as $item)
                                <div class="d-flex justify-content-between border-bottom py-2 small">
                                    <span>{{ $item->method_label }}</span>
                                    <span>
                                        {{ number_format($item->refund_count) }}/{{ number_format($item->paid_count) }} đơn
                                        ({{ number_format($item->refund_rate, 2) }}%)
                                    </span>
                                </div>
                            @empty
                                <p class="mb-0 text-muted">Không có dữ liệu hoàn tiền trong tháng.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="bg-light rounded p-4 h-100">
                    <h6 class="mb-3">Số đơn theo phương thức thanh toán</h6>
                    <canvas id="revenuePaymentChart" style="max-height: 320px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid pt-4 px-4 pb-4">
        <div class="row g-4">
            <div class="col-lg-6">
                @php
                    $totalMonthlyCost =
                        (float) $revenueSummary['import_cost'] +
                        (float) $revenueSummary['salary_cost'] +
                        (float) $revenueSummary['shipping_cost'] +
                        (float) $revenueSummary['cogs'];
                    $shipRate = (float) ($revenueSummary['ship_to_revenue_rate'] ?? 0);
                    $costItems = [
                        ['label' => 'Nhập hàng', 'value' => (float) $revenueSummary['import_cost'], 'color' => '#0d6efd'],
                        ['label' => 'Lương nhân sự', 'value' => (float) $revenueSummary['salary_cost'], 'color' => '#6f42c1'],
                        ['label' => 'Phí giao hàng', 'value' => (float) $revenueSummary['shipping_cost'], 'color' => '#fd7e14'],
                        ['label' => 'Giá vốn đã bán', 'value' => (float) $revenueSummary['cogs'], 'color' => '#dc3545'],
                    ];
                @endphp
                <div class="bg-light rounded p-4 h-100 border border-2 border-primary">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="mb-0">Các khoản chi trong tháng</h6>
                        <span
                            class="badge {{ $shipRate >= 8 ? 'bg-danger' : ($shipRate >= 4 ? 'bg-warning text-dark' : 'bg-success') }}">
                            Phí giao hàng/Doanh thu: {{ number_format($shipRate, 2) }}%
                        </span>
                    </div>

                    <div class="bg-primary rounded p-3 mb-3 text-white">
                        <p class="mb-1">Tổng chi phí tháng {{ $monthLabel }}</p>
                        <h4 class="mb-0 text-white">{{ number_format($totalMonthlyCost, 0, ',', '.') }} ₫</h4>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <div class="border rounded p-2 h-100">
                                <small class="text-muted d-block">Tiền nhập hàng</small>
                                <strong>{{ number_format($revenueSummary['import_cost'], 0, ',', '.') }} ₫</strong>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-2 h-100">
                                <small class="text-muted d-block">Tiền lương nhân sự</small>
                                <strong>{{ number_format($revenueSummary['salary_cost'], 0, ',', '.') }} ₫</strong>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-2 h-100">
                                <small class="text-muted d-block">Phí giao hàng</small>
                                <strong>{{ number_format($revenueSummary['shipping_cost'], 0, ',', '.') }} ₫</strong>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-2 h-100">
                                <small class="text-muted d-block">Giá vốn hàng đã bán</small>
                                <strong>{{ number_format($revenueSummary['cogs'], 0, ',', '.') }} ₫</strong>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 align-items-center">
                        <div class="col-lg-5">
                            <canvas id="monthlyCostBreakdownChart" style="max-height: 240px;"></canvas>
                        </div>
                        <div class="col-lg-7">
                            @foreach($costItems as $item)
                                @php
                                    $ratio = $totalMonthlyCost > 0 ? round(($item['value'] / $totalMonthlyCost) * 100, 2) : 0;
                                @endphp
                                <div class="mb-2">
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span>{{ $item['label'] }}</span>
                                        <span>{{ number_format($ratio, 2) }}%</span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar" role="progressbar"
                                            style="width: {{ $ratio }}%; background-color: {{ $item['color'] }};"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="bg-light rounded p-4 h-100">
                    <h6 class="mb-3">Lý do hủy đơn và hoàn tiền</h6>
                    <div class="row g-4">
                        <div class="col-lg-6">
                            <p class="small text-muted mb-2">Lý do hủy đơn</p>
                            @if($cancelByReason->isNotEmpty())
                                <canvas id="cancelReasonChart" style="max-height: 240px;"></canvas>
                                <div class="mt-3">
                                    @foreach($cancelByReason as $item)
                                        <div class="d-flex justify-content-between border-bottom py-2 small">
                                            <span>{{ $item->reason_label }}</span>
                                            <span>{{ number_format($item->rate, 2) }}%</span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="mb-0 text-muted">Tháng này chưa có đơn hủy.</p>
                            @endif
                        </div>
                        <div class="col-lg-6">
                            <p class="small text-muted mb-2">Lý do hoàn tiền</p>
                            @if($refundByReason->isNotEmpty())
                                <canvas id="refundReasonChart" style="max-height: 240px;"></canvas>
                                <div class="mt-3">
                                    @foreach($refundByReason as $item)
                                        <div class="d-flex justify-content-between border-bottom py-2 small">
                                            <span>{{ $item->reason_label }}</span>
                                            <span>{{ number_format($item->total) }} lượt</span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="mb-0 text-muted">Tháng này chưa có lý do hoàn tiền.</p>
                            @endif
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
                                                <td>{{ number_format($item->import_count) }}</td>
                                                <td>{{ number_format($item->total_value, 0, ',', '.') }} ₫</td>
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
                                                <td>{{ number_format($item->total_qty) }}</td>
                                                <td>{{ number_format($item->total_value, 0, ',', '.') }} ₫</td>
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
                                        <td>{{ number_format($item->staff_count) }}</td>
                                        <td>{{ number_format($item->total_salary, 0, ',', '.') }} ₫</td>
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