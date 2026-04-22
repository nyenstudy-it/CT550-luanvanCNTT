@extends('admin.layouts.layout_admin')

@section('content')
    <div class="container-fluid pt-4 px-4">

        <div class="bg-light rounded p-4">

            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div>
                    <h5 class="mb-1">Bảng lương nhân viên</h5>
                </div>
            </div>

            @php
                $summaryTotal = $salaries->count();
                $summaryHours = (float) $salaries->sum('total_hours');
                $summarySalary = (float) $salaries->sum('total_salary');
                $summaryPenalties = (float) $salaries->sum('penalty_amount');
                $summaryBonuses = (float) $salaries->sum('bonus_amount');
                $summaryFinalSalary = (float) $salaries->sum('final_salary');
            @endphp

            <form method="GET" class="row g-3 mb-4 border rounded bg-white p-3">
                <div class="col-md-3">
                    <label class="form-label">Tháng</label>
                    <select name="month" class="form-select">
                        <option value="">-- Tháng --</option>
                        @for($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" {{ request('month', now()->month) == $i ? 'selected' : '' }}>
                                Tháng {{ $i }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Năm</label>
                    <select name="year" class="form-select">
                        <option value="">-- Năm --</option>
                        @for($y = date('Y'); $y >= 2020; $y--)
                            <option value="{{ $y }}" {{ request('year', date('Y')) == $y ? 'selected' : '' }}>
                                {{ $y }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button class="btn btn-primary">Lọc</button>
                    <a href="{{ route('admin.salaries.index') }}" class="btn btn-secondary">Đặt lại</a>
                </div>
            </form>

            {{-- Bảng lương tổng hợp theo tháng --}}
            <h6 class="mb-2">Lương tổng hợp theo tháng (Bao gồm phạt & thưởng)</h6>
            <div class="table-responsive mb-4">
                <table class="table table-bordered table-hover table-sm">
                    <thead class="table-light">
                        <tr>
                            <th width="140">Nhân viên</th>
                            <th width="100">Tháng</th>
                            <th width="80">Ngày làm</th>
                            <th width="80">Giờ làm</th>
                            <th width="110">Lương cơ bản</th>
                            <th width="60">Đi trễ</th>
                            <th width="60">Về sớm</th>
                            <th width="110">Phạt</th>
                            <th width="130">Thưởng</th>
                            <th width="120">Lương cuối</th>
                            <th width="80">Chi tiết</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($salaries as $salary)
                            <tr>
                                <td><strong>{{ $salary->staff->user->name ?? 'N/A' }}</strong></td>
                                <td class="text-center">{{ $salary->month }}/{{ $salary->year }}</td>
                                <td class="text-center"><strong>{{ $salary->work_days ?? 0 }}</strong></td>
                                <td class="text-center">{{ number_format($salary->total_hours, 1) }}h</td>
                                <td class="text-end">{{ number_format($salary->total_salary, 0, ',', '.') }}đ</td>
                                <td class="text-center">
                                    <span class="badge bg-warning">{{ $salary->late_count }}</span>
                                    @if($salary->late_count > 5)
                                        <small class="d-block text-danger">-200k</small>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info">{{ $salary->early_leave_count }}</span>
                                    @if($salary->early_leave_count > 3)
                                        <small class="d-block text-danger">-200k</small>
                                    @endif
                                </td>
                                <td class="text-end text-danger fw-bold">
                                    @if($salary->penalty_amount > 0)
                                        -{{ number_format($salary->penalty_amount, 0, ',', '.') }}đ
                                    @else
                                        <span class="text-muted">0đ</span>
                                    @endif
                                </td>
                                <td class="text-end text-success fw-bold">
                                    @if($salary->bonus_amount > 0)
                                        +{{ number_format($salary->bonus_amount, 0, ',', '.') }}đ
                                        <small class="d-block text-success">(Chuyên cần)</small>
                                    @else
                                        <span class="text-muted">0đ</span>
                                    @endif
                                </td>
                                <td class="text-end fw-bold text-primary">
                                    {{ number_format($salary->final_salary ?? $salary->total_salary, 0, ',', '.') }}đ
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal"
                                        data-bs-target="#detailModal{{ $salary->id }}">
                                        Xem
                                    </button>
                                </td>
                            </tr>

                            <!-- Detail Modal -->
                            <div class="modal fade" id="detailModal{{ $salary->id }}" tabindex="-1"
                                data-salary-total-hours="{{ $salary->total_hours }}"
                                data-salary-base="{{ $salary->total_salary }}"
                                data-salary-penalty="{{ $salary->penalty_amount }}"
                                data-salary-bonus="{{ $salary->bonus_amount }}"
                                data-salary-final="{{ $salary->final_salary ?? $salary->total_salary }}"
                                data-salary-late="{{ $salary->late_count }}"
                                data-salary-early="{{ $salary->early_leave_count }}"
                                data-salary-absent="{{ $salary->absent_count }}">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Chi tiết lương — {{ $salary->staff->user->name ?? 'N/A' }}
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <p><strong>Tháng:</strong> {{ $salary->month }}/{{ $salary->year }}</p>
                                                    <p><strong>Tổng giờ làm:</strong>
                                                        {{ number_format($salary->total_hours, 1) }}h
                                                        ({{ $salary->total_minutes }}p)</p>
                                                    <p><strong>Lương cơ bản:</strong> <span
                                                            class="text-primary fw-bold">{{ number_format($salary->total_salary, 0, ',', '.') }}đ</span>
                                                    </p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p><strong>Đi trễ:</strong> {{ $salary->late_count }}/5 lần</p>
                                                    <p><strong>Về sớm:</strong> {{ $salary->early_leave_count }}/3 lần</p>
                                                    <p><strong>Vắng:</strong> {{ $salary->absent_count }} lần</p>
                                                </div>
                                            </div>
                                            <hr>
                                            <h6 class="mb-2">Phạt & Thưởng:</h6>
                                            <div class="alert alert-warning mb-2" role="alert">
                                                Phạt đi trễ (> 5 lần):
                                                @if($salary->late_count > 5)
                                                    <span class="text-danger fw-bold">-200.000đ</span>
                                                @else
                                                    <span class="text-muted">Không phạt</span>
                                                @endif
                                            </div>
                                            <div class="alert alert-warning mb-2" role="alert">
                                                Phạt về sớm (> 3 lần):
                                                @if($salary->early_leave_count > 3)
                                                    <span class="text-danger fw-bold">-200.000đ</span>
                                                @else
                                                    <span class="text-muted">Không phạt</span>
                                                @endif
                                            </div>
                                            <div class="alert {{ $salary->bonus_amount > 0 ? 'alert-success' : 'alert-info' }} mb-2"
                                                role="alert">
                                                Thưởng chuyên cần (≤3 trễ, 0 vắng, ≤3 sớm):
                                                @if($salary->bonus_amount > 0)
                                                    <span class="text-success fw-bold">+300.000đ ✓</span>
                                                @else
                                                    <span class="text-muted">Không đủ điều kiện</span>
                                                @endif
                                            </div>
                                            <hr>
                                            <div class="row text-center">
                                                <div class="col-md-6">
                                                    <p class="small text-muted mb-1">Tổng phạt</p>
                                                    <p class="text-danger fw-bold fs-5">
                                                        -{{ number_format($salary->penalty_amount, 0, ',', '.') }}đ</p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p class="small text-muted mb-1">Tổng thưởng</p>
                                                    <p class="text-success fw-bold fs-5">
                                                        +{{ number_format($salary->bonus_amount, 0, ',', '.') }}đ</p>
                                                </div>
                                            </div>
                                            <hr>
                                            <div class="alert alert-info mb-0">
                                                <h6 class="mb-2">Lương cuối cùng:</h6>
                                                <h5 class="text-primary fw-bold mb-2">
                                                    {{ number_format($salary->final_salary ?? $salary->total_salary, 0, ',', '.') }}đ
                                                </h5>
                                                <small class="text-muted">
                                                    = {{ number_format($salary->total_salary, 0, ',', '.') }}đ
                                                    - {{ number_format($salary->penalty_amount, 0, ',', '.') }}đ
                                                    + {{ number_format($salary->bonus_amount, 0, ',', '.') }}đ
                                                </small>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-primary" 
                                                onclick="printSalary({{ $salary->id }}, '{{ $salary->staff->user->name }}', {{ $salary->month }}, {{ $salary->year }})">
                                                <i class="fa fa-print me-1"></i>In bảng lương
                                            </button>
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">Đóng</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center text-muted py-4">
                                    Chưa có dữ liệu lương
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Thông tin làm việc theo tháng --}}
            <div class="border rounded bg-white p-3 mt-2">
                <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                    <h6 class="mb-0">
                        📊 Thông tin làm việc — Tháng {{ $selectedMonth }}/{{ $selectedYear }}
                    </h6>
                </div>

                <form method="GET" class="mb-3">
                    <div class="row g-2">
                        <div class="col-md-2">
                            <select name="month" class="form-select form-select-sm" onchange="this.form.submit()">
                                @for($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ request('month', $selectedMonth) == $i ? 'selected' : '' }}>
                                        Tháng {{ $i }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="year" class="form-select form-select-sm" onchange="this.form.submit()">
                                @for($y = date('Y'); $y >= 2020; $y--)
                                    <option value="{{ $y }}" {{ request('year', $selectedYear) == $y ? 'selected' : '' }}>
                                        {{ $y }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select name="staff_filter" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">-- Tất cả nhân viên --</option>
                                @foreach($salaries->groupBy('staff.user.name') as $staffName => $group)
                                    <option value="{{ $staffName }}" {{ request('staff_filter') == $staffName ? 'selected' : '' }}>
                                        {{ $staffName }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </form>

                @if($salaries->isEmpty())
                    <p class="text-muted mb-0">Chưa có dữ liệu lương trong tháng này</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="180">Nhân viên</th>
                                    <th width="80" class="text-center">Ngày làm</th>
                                    <th width="80" class="text-center">Giờ làm</th>
                                    <th width="100" class="text-center">Phút làm</th>
                                    <th width="70" class="text-center">Đi trễ</th>
                                    <th width="70" class="text-center">Về sớm</th>
                                    <th width="110" class="text-end">Lương cơ bản</th>
                                    <th width="90" class="text-end">Phạt</th>
                                    <th width="100" class="text-end">Thưởng</th>
                                    <th width="120" class="text-end">Lương cuối</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $filteredSalaries = $salaries;
                                    if (request('staff_filter')) {
                                        $filteredSalaries = $salaries->filter(function($s) {
                                            return $s->staff->user->name === request('staff_filter');
                                        });
                                    }
                                    $filteredSalaries = $filteredSalaries->sortBy('staff.user.name');
                                @endphp
                                @foreach($filteredSalaries as $salary)
                                    <tr>
                                        <td><strong>{{ $salary->staff->user->name ?? 'N/A' }}</strong></td>
                                        <td class="text-center">
                                            @php
                                                $workDays = \App\Models\Attendance::where('staff_id', $salary->staff->user_id)
                                                    ->whereMonth('work_date', $selectedMonth)
                                                    ->whereYear('work_date', $selectedYear)
                                                    ->where('is_completed', 1)
                                                    ->select('work_date')
                                                    ->distinct()
                                                    ->count();
                                            @endphp
                                            {{ $workDays }}
                                        </td>
                                        <td class="text-center">{{ number_format($salary->total_hours, 2) }}</td>
                                        <td class="text-center">{{ $salary->total_minutes ?? 0 }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-warning">{{ $salary->late_count }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info">{{ $salary->early_leave_count }}</span>
                                        </td>
                                        <td class="text-end">{{ number_format($salary->total_salary, 0, ',', '.') }}đ</td>
                                        <td class="text-end text-danger">
                                            @if($salary->penalty_amount > 0)
                                                -{{ number_format($salary->penalty_amount, 0, ',', '.') }}đ
                                            @else
                                                <span class="text-muted">0đ</span>
                                            @endif
                                        </td>
                                        <td class="text-end text-success">
                                            @if($salary->bonus_amount > 0)
                                                +{{ number_format($salary->bonus_amount, 0, ',', '.') }}đ
                                            @else
                                                <span class="text-muted">0đ</span>
                                            @endif
                                        </td>
                                        <td class="text-end fw-bold text-primary">
                                            {{ number_format($salary->final_salary ?? $salary->total_salary, 0, ',', '.') }}đ
                                        </td>
                                    </tr>
                                @endforeach
                                <tr class="table-primary fw-bold">
                                    <td>Tổng</td>
                                    <td class="text-center">
                                        @php
                                            if (request('staff_filter')) {
                                                $staffName = request('staff_filter');
                                                $totalWorkDays = \App\Models\Attendance::whereHas('staff.user', function($q) use ($staffName) {
                                                    $q->where('name', $staffName);
                                                })
                                                    ->whereMonth('work_date', $selectedMonth)
                                                    ->whereYear('work_date', $selectedYear)
                                                    ->where('is_completed', 1)
                                                    ->select('work_date')
                                                    ->distinct()
                                                    ->count();
                                            } else {
                                                $totalWorkDays = \App\Models\Attendance::whereMonth('work_date', $selectedMonth)
                                                    ->whereYear('work_date', $selectedYear)
                                                    ->where('is_completed', 1)
                                                    ->select('work_date')
                                                    ->distinct()
                                                    ->count();
                                            }
                                        @endphp
                                        {{ $totalWorkDays }}
                                    </td>
                                    <td class="text-center">{{ number_format($filteredSalaries->sum('total_hours'), 2) }}</td>
                                    <td class="text-center">{{ $filteredSalaries->sum('total_minutes') }}</td>
                                    <td class="text-center"><strong>{{ $filteredSalaries->sum('late_count') }}</strong></td>
                                    <td class="text-center"><strong>{{ $filteredSalaries->sum('early_leave_count') }}</strong></td>
                                    <td class="text-end">{{ number_format($filteredSalaries->sum('total_salary'), 0, ',', '.') }}đ</td>
                                    <td class="text-end">{{ number_format($filteredSalaries->sum('penalty_amount'), 0, ',', '.') }}đ</td>
                                    <td class="text-end">{{ number_format($filteredSalaries->sum('bonus_amount'), 0, ',', '.') }}đ</td>
                                    <td class="text-end">{{ number_format($filteredSalaries->sum('final_salary'), 0, ',', '.') }}đ</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

        </div>
    </div>

    <!-- Phần in bảng lương (ẩn) -->
    <div id="printContent" style="display: none;"></div>

    <style>
        @media print {
            body * {
                visibility: hidden;
            }
            #printContent, #printContent * {
                visibility: visible;
            }
            #printContent {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
        }
    </style>

    <script>
        function printSalary(salaryId, staffName, month, year) {
            // Lấy dữ liệu từ modal
            const modal = document.getElementById('detailModal' + salaryId);
            const modalBody = modal.querySelector('.modal-body');
            
            // Tạo HTML để in
            const printHTML = `
                <!DOCTYPE html>
                <html>
                <head>
                    <meta charset="utf-8">
                    <style>
                        * { 
                            font-family: 'Times New Roman', Times, serif;
                            margin: 0;
                            padding: 0;
                        }
                        body {
                            padding: 20px;
                        }
                        .header {
                            text-align: center;
                            margin-bottom: 20px;
                            border-bottom: 2px solid #000;
                            padding-bottom: 10px;
                        }
                        .header h2 {
                            font-size: 18px;
                            font-weight: bold;
                            margin-bottom: 5px;
                        }
                        .header p {
                            font-size: 12px;
                        }
                        .info-section {
                            margin-bottom: 20px;
                            display: grid;
                            grid-template-columns: 1fr 1fr;
                            gap: 20px;
                        }
                        .info-group {
                            font-size: 13px;
                        }
                        .info-group p {
                            margin-bottom: 8px;
                        }
                        .info-label {
                            font-weight: bold;
                            display: inline-block;
                            width: 120px;
                        }
                        .info-value {
                            display: inline;
                        }
                        .salary-table {
                            width: 100%;
                            border-collapse: collapse;
                            margin-bottom: 20px;
                            font-size: 13px;
                        }
                        .salary-table th {
                            border: 1px solid #000;
                            padding: 8px;
                            background-color: #f0f0f0;
                            font-weight: bold;
                            text-align: left;
                        }
                        .salary-table td {
                            border: 1px solid #000;
                            padding: 8px;
                        }
                        .text-right {
                            text-align: right;
                        }
                        .text-center {
                            text-align: center;
                        }
                        .footer {
                            margin-top: 30px;
                            display: grid;
                            grid-template-columns: 1fr 1fr 1fr;
                            gap: 20px;
                            text-align: center;
                            font-size: 12px;
                        }
                        .footer-item {
                            border-top: 1px solid #000;
                            padding-top: 20px;
                            min-height: 60px;
                        }
                        .alert-box {
                            border: 1px solid #999;
                            padding: 10px;
                            margin-bottom: 10px;
                            font-size: 13px;
                        }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <h2>BẢNG TÍNH LƯƠNG</h2>
                        <p>Tháng ${month}/${year}</p>
                    </div>

                    <div class="info-section">
                        <div class="info-group">
                            <p><span class="info-label">Nhân viên:</span> <span class="info-value">${staffName}</span></p>
                            <p><span class="info-label">Tháng:</span> <span class="info-value">${month}/${year}</span></p>
                        </div>
                        <div class="info-group">
                            <p><span class="info-label">Ngày lập:</span> <span class="info-value">${new Date().toLocaleDateString('vi-VN')}</span></p>
                        </div>
                    </div>

                    <table class="salary-table">
                        <thead>
                            <tr>
                                <th>Chỉ tiêu</th>
                                <th class="text-right">Số liệu</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${getDetailRows(salaryId)}
                        </tbody>
                    </table>

                    <div class="footer">
                        <div class="footer-item">
                            <strong>Kế toán</strong>
                        </div>
                        <div class="footer-item">
                            <strong>Giám đốc</strong>
                        </div>
                        <div class="footer-item">
                            <strong>Nhân viên xác nhận</strong>
                        </div>
                    </div>
                </body>
                </html>
            `;

            // Mở cửa sổ in
            const printWindow = window.open('', '', 'height=800,width=1000');
            printWindow.document.write(printHTML);
            printWindow.document.close();
            
            setTimeout(() => {
                printWindow.print();
                // printWindow.close();
            }, 250);
        }

        function getDetailRows(salaryId) {
            const modal = document.getElementById('detailModal' + salaryId);
            if (!modal) return '';
            
            // Lấy dữ liệu từ data attributes
            const totalHours = modal.getAttribute('data-salary-total-hours') || '0';
            const baseSalary = modal.getAttribute('data-salary-base') || '0';
            const penalty = modal.getAttribute('data-salary-penalty') || '0';
            const bonus = modal.getAttribute('data-salary-bonus') || '0';
            const finalSalary = modal.getAttribute('data-salary-final') || '0';
            const lateCount = modal.getAttribute('data-salary-late') || '0';
            const earlyCount = modal.getAttribute('data-salary-early') || '0';
            const absentCount = modal.getAttribute('data-salary-absent') || '0';

            // Format currency
            const formatCurrency = (num) => {
                return Intl.NumberFormat('vi-VN').format(num) + ' đ';
            };

            let html = `
                <tr>
                    <td>Tổng giờ làm</td>
                    <td class="text-right">${parseFloat(totalHours).toFixed(2)} giờ</td>
                </tr>
                <tr>
                    <td>Lương cơ bản</td>
                    <td class="text-right">${formatCurrency(baseSalary)}</td>
                </tr>
                <tr style="border-top: 1px solid #ccc;">
                    <td><strong>Khoản phạt & Thưởng</strong></td>
                    <td></td>
                </tr>
                <tr>
                    <td> - Đi trễ (${lateCount} lần > 5)</td>
                    <td class="text-right">${lateCount > 5 ? formatCurrency(200000) : 'Không phạt'}</td>
                </tr>
                <tr>
                    <td> - Về sớm (${earlyCount} lần > 3)</td>
                    <td class="text-right">${earlyCount > 3 ? formatCurrency(200000) : 'Không phạt'}</td>
                </tr>
                <tr>
                    <td> - Vắng</td>
                    <td class="text-right">${absentCount > 0 ? formatCurrency(absentCount * 300000) : 'Không phạt'}</td>
                </tr>
                <tr>
                    <td> + Thưởng chuyên cần</td>
                    <td class="text-right">${bonus > 0 ? formatCurrency(bonus) : 'Không'}</td>
                </tr>
                <tr style="border-top: 2px solid #000;">
                    <td><strong>Tổng phạt</strong></td>
                    <td class="text-right" style="font-weight: bold;">${formatCurrency(penalty)}</td>
                </tr>
                <tr>
                    <td><strong>Tổng thưởng</strong></td>
                    <td class="text-right" style="font-weight: bold;">+${formatCurrency(bonus)}</td>
                </tr>
                <tr style="background-color: #e8f4f8; font-weight: bold; font-size: 14px;">
                    <td>LƯƠNG CUỐI CÙNG</td>
                    <td class="text-right">${formatCurrency(finalSalary)}</td>
                </tr>
            `;
            
            return html;
        }

        function getValueFromModal(salaryId, field) {
            const modal = document.getElementById('detailModal' + salaryId);
            if (!modal) return '---';
            
            const map = {
                'total_hours': 'data-salary-total-hours',
                'base_salary': 'data-salary-base',
                'penalty': 'data-salary-penalty',
                'bonus': 'data-salary-bonus',
                'final_salary': 'data-salary-final',
                'late_count': 'data-salary-late',
                'early_count': 'data-salary-early',
                'absent_count': 'data-salary-absent',
            };
            
            return modal.getAttribute(map[field]) || '0';
        }
    </script>
@endsection