@extends('admin.layouts.layout_admin')

@section('content')
    <div class="container-fluid pt-4 px-4">
        <div class="bg-light rounded p-4">

            <h6 class="mb-3">Chấm công của tôi</h6>

            <!-- Flash Messages -->
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert" style="margin-bottom: 20px;">
                    <strong>❌ Lỗi!</strong>
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert"
                    style="margin-bottom: 20px; animation: slideIn 0.5s ease-in-out;" id="successAlert">
                    <strong>✅ Thành công!</strong> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert"
                    style="margin-bottom: 20px; animation: slideIn 0.5s ease-in-out;" id="errorAlert">
                    <strong>❌ Lỗi!</strong> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Dynamic Alert Container for AJAX Responses -->
            <div id="dynamicAlertContainer" style="margin-bottom: 20px;"></div>

            <style>
                @keyframes slideIn {
                    from {
                        opacity: 0;
                        transform: translateY(-20px);
                    }

                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }
            </style>

            <script>
                // Auto-hide alerts sau 8 giây
                document.addEventListener('DOMContentLoaded', function () {
                    const alerts = document.querySelectorAll('.alert');
                    alerts.forEach(alert => {
                        setTimeout(() => {
                            const bsAlert = new bootstrap.Alert(alert);
                            bsAlert.close();
                        }, 8000);
                    });
                });
            </script>

            <!-- Script phải được định nghĩa SỚM trước khi HTML render -->
            <script>
                let isSubmitting = false;
                // 🟢 FIX: Server time in UTC+7 (Asia/Ho_Chi_Minh)
                const SERVER_NOW = new Date('{{ $serverNow->toIso8601String() }}');

                function handleCheckin(attendanceId, workDate, expectedIn) {
                    submitAttendanceFormAjax(attendanceId, 'check-in');
                }

                function handleCheckout(attendanceId, workDate, expectedOut) {
                    // 🟢 FIX: Use server time (UTC+7) for NOW comparison
                    const now = new Date(SERVER_NOW);  // Current server time UTC+7

                    // 🟢 FIX: Parse workDate + expectedOut with UTC+7 timezone
                    // Add '+07:00' to ensure it uses Asia/Ho_Chi_Minh timezone
                    const expectedTime = new Date(workDate + 'T' + expectedOut + '+07:00');

                    if (now < expectedTime) {
                        // Early leave detected → Show modal
                        const modal = new bootstrap.Modal(document.getElementById('earlyLeaveModal'));
                        document.getElementById('earlyLeaveAttendanceId').value = attendanceId;
                        modal.show();
                    } else {
                        // Normal checkout
                        submitAttendanceFormAjax(attendanceId, 'check-out', null, null);
                    }
                }

                async function submitAttendanceFormAjax(attendanceId, action, reasonType = null, reason = null) {
                    if (isSubmitting) {
                        console.warn('Already submitting, ignoring duplicate request');
                        return;
                    }

                    try {
                        isSubmitting = true;
                        const url = `/staff/attendances/${attendanceId}/${action}`;

                        // Prepare form data
                        const formData = new FormData();
                        formData.append('_token', '{{ csrf_token() }}');

                        // Add early leave reason if provided
                        if (reasonType) {
                            formData.append('reason_type', reasonType);
                        }
                        if (reason) {
                            formData.append('reason', reason);
                        }

                        // Add network type
                        const networkType = getNetworkType();
                        if (networkType) {
                            formData.append('network_type', networkType);
                        }

                        // Try to get position with callback and then submit
                        let submitted = false;
                        getPosition((position) => {
                            if (position) {
                                formData.append('latitude', position.latitude);
                                formData.append('longitude', position.longitude);
                            }

                            // Submit after position is collected
                            if (!submitted) {
                                submitted = true;
                                performSubmit(url, formData);
                            }
                        });

                        // Timeout fallback - submit without position if geolocation takes too long
                        // 🔴 FIX: Increased from 1000ms to 3000ms
                        // Reason: navigator.geolocation needs ~2000ms timeout
                        // 1000ms was TOO FAST → submitted before GPS callback
                        setTimeout(() => {
                            if (!submitted) {
                                submitted = true;
                                performSubmit(url, formData);
                            }
                        }, 3000);  // ← 3 SECONDS (GPS timeout 2s + buffer 1s)

                    } catch (error) {
                        console.error('Error in submitAttendanceFormAjax:', error);
                        isSubmitting = false;
                        showAlert('Lỗi: ' + error.message, 'danger');
                    }
                }

                async function performSubmit(url, formData) {
                    try {
                        const response = await fetch(url, {
                            method: 'POST',
                            body: formData
                        });

                        const data = await response.json();

                        if (data.status === 'success') {
                            showAlert(data.message, 'success');
                            // Reload page after showing success message - wait longer to see alert
                            setTimeout(() => window.location.reload(), 3000);
                        } else {
                            showAlert(data.message, 'danger');
                            isSubmitting = false;
                        }
                    } catch (error) {
                        console.error('Fetch error:', error);
                        showAlert('Lỗi khi gửi yêu cầu: ' + error.message, 'danger');
                        isSubmitting = false;
                    }
                }

                function getPosition(callback) {
                    if (!navigator.geolocation) {
                        callback(null);
                        return;
                    }

                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            callback({
                                latitude: position.coords.latitude,
                                longitude: position.coords.longitude,
                            });
                        },
                        () => callback(null),
                        {
                            enableHighAccuracy: false,
                            timeout: 2000,
                            maximumAge: 30000,
                        }
                    );
                }

                function showAlert(message, type) {
                    const container = document.getElementById('dynamicAlertContainer');
                    const alertDiv = document.createElement('div');
                    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
                    alertDiv.setAttribute('role', 'alert');
                    alertDiv.style.animation = 'slideIn 0.5s ease-in-out';

                    const icon = type === 'success' ? '✅ Thành công!' : '❌ Lỗi!';
                    alertDiv.innerHTML = `
                                                                                    <strong>${icon}</strong> ${message}
                                                                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                                                                `;

                    container.innerHTML = '';
                    container.appendChild(alertDiv);

                    // Auto-hide after 8 seconds
                    setTimeout(() => {
                        const bsAlert = new bootstrap.Alert(alertDiv);
                        bsAlert.close();
                    }, 8000);
                }

                function getNetworkType() {
                    const connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
                    if (!connection) {
                        return null;
                    }
                    return connection.effectiveType || connection.type || null;
                }
            </script>

            @php
                // Lấy month/year từ request hoặc mặc định hiện tại
                $selectedMonth = request('month', \Carbon\Carbon::now()->month);
                $selectedYear = request('year', \Carbon\Carbon::now()->year);
            @endphp

            <!-- Filter by Month/Year -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('staff.staff_attendances') }}" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Tháng</label>
                            <select name="month" class="form-select">
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ $selectedMonth == $m ? 'selected' : '' }}>
                                        Tháng {{ $m }}
                                    </option>
                                @endfor
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Năm</label>
                            <select name="year" class="form-select">
                                @for($y = 2024; $y <= 2026; $y++)
                                    <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>
                                        Năm {{ $y }}
                                    </option>
                                @endfor
                            </select>
                        </div>

                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100">Lọc</button>
                        </div>
                    </form>
                </div>
            </div>

            @php
                // Tính toán dữ liệu cho tháng được chọn
                $currentMonth = $selectedMonth;
                $currentYear = $selectedYear;

                // Tính toán dữ liệu TOÀN BỘ tháng hiện tại (không phụ thuộc pagination)
                $monthlyAttendances = \App\Models\Attendance::where('staff_id', Auth::id())
                    ->whereMonth('work_date', $currentMonth)
                    ->whereYear('work_date', $currentYear)
                    ->get();

                $totalShifts = $monthlyAttendances->count();
                $totalMinutes = $monthlyAttendances->sum('worked_minutes');
                $totalHours = $totalMinutes ? round($totalMinutes / 60, 1) : 0;

                // Tính tổng lương với xem xét pending salary
                $totalSalary = 0;
                foreach ($monthlyAttendances as $att) {
                    if ($att->is_early_leave && $att->early_leave_status === 'pending') {
                        // Pending salary theo trường hợp DUYỆT (tính đến giờ xin về sớm).
                        $staffRate = $att->staff->employment_status === 'official'
                            ? ($att->staff->official_hourly_wage ?? 20000)
                            : ($att->staff->probation_hourly_wage ?? 15000);

                        $checkIn = \Carbon\Carbon::parse($att->work_date . ' ' . $att->check_in);
                        $checkOut = \Carbon\Carbon::parse($att->work_date . ' ' . $att->check_out);
                        $expectedIn = \Carbon\Carbon::parse($att->work_date . ' ' . $att->expected_check_in);

                        $lateMin = $expectedIn->diffInMinutes($checkIn);

                        $pendingMin = $lateMin <= 15
                            ? $expectedIn->diffInMinutes($checkOut)
                            : $checkIn->diffInMinutes($checkOut);

                        $totalSalary += round(($pendingMin / 60) * $staffRate);
                    } else {
                        $totalSalary += ($att->salary_amount ?? 0);
                    }
                }

                // Load monthly salary for current month
                $monthlySalary = \App\Models\Salary::where('staff_id', Auth::id())
                    ->where('month', $currentMonth)
                    ->where('year', $currentYear)
                    ->first();
            @endphp

            <!-- Daily Summary -->
            <div class="row mb-4 g-3">
                <div class="col-lg-4 col-md-6">
                    <div class="bg-white rounded shadow-sm p-4 border-start border-primary border-4">
                        <h6 class="text-muted small mb-2"> Tổng số ca (tháng {{ $currentMonth }}/{{ $currentYear }})</h6>
                        <h3 class="fw-bold text-primary mb-0">{{ $totalShifts }}</h3>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="bg-white rounded shadow-sm p-4 border-start border-info border-4">
                        <h6 class="text-muted small mb-2"> Tổng giờ làm</h6>
                        <h3 class="fw-bold text-info mb-0">{{ $totalHours }}<span class="small ms-1">giờ</span></h3>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="bg-white rounded shadow-sm p-4 border-start border-success border-4">
                        <h6 class="text-muted small mb-2">Tổng tiền lương</h6>
                        <h3 class="fw-bold text-success mb-0">{{ number_format($totalSalary, 0, ',', '.') }}<span
                                class="small ms-1">đ</span></h3>
                    </div>
                </div>
            </div>

            <!-- Monthly Salary Summary -->
            @if($monthlySalary)
                <div class="border border-info rounded p-3 mb-4" style="border-left: 4px solid #17a2b8;">
                    <div class="row g-4 align-items-center">
                        <div class="col-auto">
                            <small class="text-muted">Lương tháng
                                {{ $monthlySalary->month }}/{{ $monthlySalary->year }}</small><br>
                            <strong class="text-dark">Cơ bản:</strong>
                            <span
                                class="text-primary fw-bold">{{ number_format($monthlySalary->total_salary, 0, ',', '.') }}đ</span>
                        </div>
                        <div class="col-auto">
                            <small class="text-muted">Chấm công</small><br>
                            <small>Đi trễ: <strong>{{ $monthlySalary->late_count }}x</strong> | Về sớm:
                                <strong>{{ $monthlySalary->early_leave_count }}x</strong> | Vắng:
                                <strong>{{ $monthlySalary->absent_count }}x</strong></small>
                        </div>
                        <div class="col-auto">
                            <small class="text-muted">Tính lương</small><br>
                            <small>
                                <span
                                    class="text-danger">-{{ number_format($monthlySalary->penalty_amount, 0, ',', '.') }}đ</span>
                                <span
                                    class="text-success">+{{ number_format($monthlySalary->bonus_amount, 0, ',', '.') }}đ</span>
                            </small>
                        </div>
                        <div class="col-auto ms-auto">
                            <small class="text-muted d-block text-end">Lương cuối cùng</small>
                            <h6 class="text-info fw-bold mb-0">{{ number_format($monthlySalary->final_salary, 0, ',', '.') }}đ
                            </h6>
                        </div>
                    </div>
                </div>
            @endif

            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th width="60">STT</th>
                        <th>Ngày</th>
                        <th>Ca</th>
                        <th>Giờ vào</th>
                        <th>Giờ ra</th>
                        <th>Trạng thái</th>
                        <th>Lương ca</th>
                        <th>Chấm công</th>
                        <th>Chi tiết</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($attendances as $index => $attendance)

                                @php
                                    $shiftText = match ($attendance->shift) {
                                        'morning' => 'Ca sáng',
                                        'afternoon' => 'Ca chiều',
                                        default => 'Không xác định',
                                    };

                                    $badgeMap = [
                                        'scheduled' => ['label' => 'Đã phân ca', 'class' => 'bg-info'],
                                        'working' => ['label' => 'Đang làm', 'class' => 'bg-warning'],
                                        'completed' => ['label' => 'Đã hoàn thành', 'class' => 'bg-success'],
                                        'absent' => ['label' => 'Vắng mặt', 'class' => 'bg-danger'],
                                    ];

                                    $statusKey = $attendance->computed_status;
                                    $status = $badgeMap[$statusKey] ?? ['label' => '--', 'class' => 'bg-secondary'];

                                    // === CALCULATE ALL VALUES UPFRONT FOR BOTH TABLE & MODAL ===
                                    $rate = $attendance->staff->employment_status === 'official'
                                        ? ($attendance->staff->official_hourly_wage ?? 20000)
                                        : ($attendance->staff->probation_hourly_wage ?? 15000);
                                    $rateDisplay = number_format($rate, 0, ',', '.');

                                    // Parse times
                                    $actualCheckIn = \Carbon\Carbon::parse($attendance->work_date . ' ' . $attendance->check_in);
                                    $actualCheckOut = \Carbon\Carbon::parse($attendance->work_date . ' ' . $attendance->check_out);
                                    $expectedCheckIn = \Carbon\Carbon::parse($attendance->work_date . ' ' . $attendance->expected_check_in);
                                    $expectedCheckOut = \Carbon\Carbon::parse($attendance->work_date . ' ' . $attendance->expected_check_out);

                                    // Calculate late minutes (positive when check-in after expected)
                                    $lateMinutes = $expectedCheckIn->diffInMinutes($actualCheckIn);

                                    // === PENDING WORKED MINUTES ===
                                    // Pending = Dự tính khi DUYỆT (đến giờ xin về sớm).
                                    $pendingWorkedMinutes = $attendance->worked_minutes ?? null;
                                    if ($attendance->is_early_leave) {
                                        if ($lateMinutes <= 15) {
                                            $pendingWorkedMinutes = $expectedCheckIn->diffInMinutes($actualCheckOut);
                                        } else {
                                            $pendingWorkedMinutes = $actualCheckIn->diffInMinutes($actualCheckOut);
                                        }
                                    }

                                    // === APPROVED WORKED MINUTES & SALARY ===
                                    if ($lateMinutes <= 15) {
                                        $approvedWorkedMinutes = $expectedCheckIn->diffInMinutes($actualCheckOut);
                                    } else {
                                        $approvedWorkedMinutes = $actualCheckIn->diffInMinutes($actualCheckOut);
                                    }
                                    $approvedSalary = round(($approvedWorkedMinutes / 60) * $rate);

                                    // === DISPLAY VALUES FOR TABLE ===
                                    $displaySalary = $attendance->salary_amount;
                                    $displayWorkedMinutes = $attendance->worked_minutes;

                                    // Get computed (non-stored) values for comparison and display
                                    $computed = $attendance->computedValues();
                                    $computedWorked = $computed['worked_minutes'];
                                    $computedSalary = $computed['salary_amount'];

                                    if ($attendance->is_early_leave && $attendance->early_leave_status === 'pending') {
                                        if ($pendingWorkedMinutes !== null) {
                                            $displaySalary = round(($pendingWorkedMinutes / 60) * $rate);
                                            $displayWorkedMinutes = $pendingWorkedMinutes;
                                        }
                                    }
                                @endphp

                                <tr>
                                    <td class="text-center fw-bold">
                                        {{ $attendances->firstItem() + $index }}
                                    </td>

                                    <td>
                                        {{ \Carbon\Carbon::parse($attendance->work_date)->format('d/m/Y') }}
                                    </td>

                                    <td>{{ $shiftText }}</td>

                                    <td>
                                        {{ $attendance->check_in
                        ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i')
                        : '--' }}
                                    </td>

                                    <td>
                                        {{ $attendance->check_out
                        ? \Carbon\Carbon::parse($attendance->check_out)->format('H:i')
                        : '--' }}
                                    </td>

                                    <td>
                                        <span class="badge {{ $status['class'] }}">
                                            {{ $status['label'] }}
                                        </span>

                                        @if($attendance->is_late)
                                            <span class="badge bg-danger mt-1">Đi trễ</span>
                                        @endif

                                        @if($attendance->is_early_leave)
                                            <span class="badge bg-warning mt-1">Về sớm</span>
                                            @if($attendance->early_leave_reason)
                                                <small class="d-block text-muted">({{ $attendance->early_leave_reason }})</small>
                                            @endif
                                        @endif
                                    </td>

                                    <td>
                                        @php
                                            // If computed differs from stored, show both for admin clarity
                                            $storedSalary = $attendance->salary_amount;
                                            $showComputedDiff = (!is_null($computedSalary) && (int) $computedSalary !== (int) $storedSalary);
                                        @endphp

                                        @if(!is_null($displaySalary) && $attendance->computed_status === 'completed')
                                            <strong>{{ number_format($displaySalary, 0, ',', '.') }}đ</strong>
                                            @if($showComputedDiff)
                                                <div class="small text-muted">(Computed: {{ number_format($computedSalary, 0, ',', '.') }}đ)</div>
                                            @endif
                                        @elseif($displaySalary && $attendance->is_completed)
                                            <strong>{{ number_format($displaySalary, 0, ',', '.') }}đ</strong>
                                            @if($showComputedDiff)
                                                <div class="small text-muted">(Computed: {{ number_format($computedSalary, 0, ',', '.') }}đ)</div>
                                            @endif
                                        @else
                                            <span class="text-muted">--</span>
                                            @if($showComputedDiff)
                                                <div class="small text-muted">(Computed: {{ number_format($computedSalary, 0, ',', '.') }}đ)</div>
                                            @endif
                                        @endif
                                    </td>

                                    <td>
                                        @if(!$attendance->check_in)
                                            <form method="POST" action="{{ route('staff.attendances.check_in', $attendance->id) }}">
                                                @csrf
                                                <button type="button" class="btn btn-success btn-sm"
                                                    onclick="handleCheckin('{{ $attendance->id }}', '{{ $attendance->work_date }}', '{{ $attendance->expected_check_in }}')">
                                                    Check in
                                                </button>
                                            </form>

                                        @elseif(!$attendance->check_out)
                                            <form method="POST" action="{{ route('staff.attendances.check_out', $attendance->id) }}">
                                                @csrf
                                                <button type="button" class="btn btn-danger btn-sm"
                                                    onclick="handleCheckout('{{ $attendance->id }}', '{{ $attendance->work_date }}', '{{ $attendance->expected_check_out }}')">
                                                    Check out
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-muted">Hoàn thành</span>
                                        @endif
                                    </td>
                                    {{-- Chi tiết --}}
                                    <td class="text-center">
                                        @if($attendance->check_out)
                                            <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal"
                                                data-bs-target="#detailModal{{ $attendance->id }}">
                                                Xem
                                            </button>
                                        @else
                                            <span class="text-muted">--</span>
                                        @endif
                                    </td>
                                </tr>
                                @if($attendance->check_out)
                                    <div class="modal fade" id="detailModal{{ $attendance->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">

                                                <div class="modal-header">
                                                    <h5 class="modal-title">Chi tiết ca làm</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>

                                                <div class="modal-body">

                                                    <p>
                                                        <strong>Ngày:</strong>
                                                        {{ \Carbon\Carbon::parse($attendance->work_date)->format('d/m/Y') }}
                                                    </p>

                                                    <p><strong>Ca:</strong> {{ $shiftText }}</p>

                                                    <hr>

                                                    <div class="mb-3">
                                                        <h6 class="text-muted">Kỳ vọng</h6>
                                                        <p class="mb-1"><small class="text-muted">Từ:</small>
                                                            <strong>{{ $attendance->expected_check_in }}</strong>
                                                        </p>
                                                        <p><small class="text-muted">Đến:</small>
                                                            <strong>{{ $attendance->expected_check_out }}</strong>
                                                        </p>
                                                    </div>

                                                    <div class="mb-3">
                                                        <h6 class="text-muted">Thực tế</h6>
                                                        <p class="mb-1">
                                                            <small class="text-muted">Giờ vào:</small>
                                                            @if($attendance->check_in)
                                                                <strong>{{ \Carbon\Carbon::parse($attendance->check_in)->format('H:i') }}</strong>
                                                            @else
                                                                <span class="text-muted">--</span>
                                                            @endif
                                                        </p>
                                                        <p>
                                                            <small class="text-muted">Giờ ra:</small>
                                                            @if($attendance->check_out)
                                                                <strong>{{ \Carbon\Carbon::parse($attendance->check_out)->format('H:i') }}</strong>
                                                            @else
                                                                <span class="text-muted">--</span>
                                                            @endif
                                                        </p>
                                                    </div>

                                                    <hr>

                                                    <p>
                                                        <strong>Tổng giờ làm:</strong>
                                                        @if($displayWorkedMinutes !== null)
                                                            {{ round($displayWorkedMinutes / 60, 2) . ' giờ (' . $displayWorkedMinutes . ' phút)' }}
                                                            @if(isset($computedWorked) && (int) $computedWorked !== (int) ($attendance->worked_minutes ?? 0))
                                                                <br><small class="text-muted">(Computed: {{ $computedWorked }} phút)</small>
                                                            @endif
                                                        @else
                                                            Chưa tính
                                                        @endif
                                                    </p>

                                                    <p>
                                                        <strong>Lương ca:</strong>
                                                        @php $storedSalary = $attendance->salary_amount;
                                                        $showSalaryDiff = isset($computedSalary) && (int) $computedSalary !== (int) ($storedSalary ?? 0); @endphp
                                                        @if($attendance->salary_amount)
                                                                @if($attendance->is_early_leave && $attendance->early_leave_status === 'pending')
                                                                        <!-- Về sớm, chưa duyệt -->
                                                                        <span
                                                                            class="badge bg-warning text-dark">{{ number_format($displaySalary, 0, ',', '.') }}đ</span>
                                                                        <small class="text-muted d-block mt-1">
                                                                            ({{ $rateDisplay }}đ/giờ × {{ round($displayWorkedMinutes / 60, 2) }}h)
                                                                        </small>
                                                                    <div class="alert alert-warning mt-2 p-2 mb-0" style="font-size: 0.85rem;">
                                                                        <strong>Chưa duyệt:</strong> Lương chưa được duyệt. Sẽ cập nhật thành
                                                                        <strong>{{ number_format($approvedSalary, 0, ',', '.') }}đ</strong>
                                                                        (đến giờ xin về sớm) khi
                                                                        admin duyệt.
                                                                    </div>
                                                                @elseif($attendance->is_early_leave && $attendance->early_leave_status === 'approved')
                                                                <!-- Về sớm, đã duyệt -->
                                                                <span
                                                                    class="badge bg-success">{{ number_format($attendance->salary_amount, 0, ',', '.') }}đ</span>
                                                                <small class="text-muted d-block mt-1">
                                                                    ({{ $rateDisplay }}đ/giờ ×
                                                                    {{ round($approvedWorkedMinutes / 60, 2) }}h)
                                                                </small>
                                                                <div class="alert alert-success mt-2 p-2 mb-0" style="font-size: 0.85rem;">
                                                                    <strong>Đã duyệt:</strong> Tính lương đến thời điểm nhân viên xin về sớm.
                                                                </div>
                                                            @else
                                                                <!-- Bình thường hoặc về sớm bị từ chối -->
                                                                <span
                                                                    class="badge bg-success">{{ number_format($attendance->salary_amount, 0, ',', '.') }}đ</span>
                                                                <small class="text-muted d-block mt-1">
                                                                    ({{ $rateDisplay }}đ/giờ × {{ round(($attendance->worked_minutes ?? 0) / 60, 2) }}h)
                                                                </small>
                                                                @if($showSalaryDiff)
                                                                    <div class="small text-muted mt-1">(Computed:
                                                                        {{ number_format($computedSalary, 0, ',', '.') }}đ)</div>
                                                                @endif
                                                                @if($attendance->is_early_leave && $attendance->early_leave_status === 'rejected')
                                                                    <div class="alert alert-info mt-2 p-2 mb-0" style="font-size: 0.85rem;">
                                                                        <strong>Bị từ chối:</strong> Tính lương đến hết ca.
                                                                    </div>
                                                                @endif
                                                            @endif
                                                        @else
                                                        Chưa tính
                                                    @endif
                                                    </p>

                                                    <hr>

                                                    <!-- Trạng thái: Đi trễ / về sớm -->
                                                    <div class="mb-3">
                                                        @if($attendance->is_late || $attendance->is_early_leave)
                                                            <strong class="d-block mb-2">Trạng thái:</strong>

                                                            @if($attendance->is_late)
                                                                <div class="mb-2">
                                                                    <span class="badge bg-danger">Đi trễ</span>
                                                                </div>
                                                            @endif

                                                            @if($attendance->is_early_leave)
                                                                <div>
                                                                    <span class="badge bg-warning text-dark">Về sớm</span>
                                                                    @if($attendance->early_leave_status === 'pending')
                                                                        <span class="badge bg-secondary ms-1">Chưa duyệt</span>
                                                                    @elseif($attendance->early_leave_status === 'approved')
                                                                        <span class="badge bg-success ms-1">Đã duyệt</span>
                                                                    @elseif($attendance->early_leave_status === 'rejected')
                                                                        <span class="badge bg-danger ms-1">Bị từ chối</span>
                                                                    @endif
                                                                    @if($attendance->early_leave_reason)
                                                                        <p class="small mt-1 mb-0"><strong>Lý do:</strong>
                                                                            {{ $attendance->early_leave_reason }}</p>
                                                                    @endif
                                                                </div>
                                                            @endif
                                                        @endif
                                                    </div>

                                                    <hr>

                                                    @if($attendance->check_in_latitude && $attendance->check_in_longitude)
                                                        <hr>
                                                        <p><strong> Vị trí Check-in:</strong></p>
                                                        <p style="font-size: 0.9rem;" class="mb-1">
                                                            <code>{{ $attendance->check_in_latitude }}, {{ $attendance->check_in_longitude }}</code>
                                                        </p>
                                                        @if($attendance->check_in_verification_method)
                                                            @php
                                                                $methodMap = [
                                                                    'wifi' => ' WiFi',
                                                                    'radius' => 'GPS (' . round($attendance->check_in_distance_meters, 1) . 'm)',
                                                                    'both' => ' WiFi + GPS (' . round($attendance->check_in_distance_meters, 1) . 'm)',
                                                                ];
                                                            @endphp
                                                            <span
                                                                class="badge bg-info">{{ $methodMap[$attendance->check_in_verification_method] ?? 'Unknown' }}</span>
                                                        @endif
                                                        @if($attendance->check_in_network_type)
                                                            <span class="badge bg-secondary">{{ $attendance->check_in_network_type }}</span>
                                                        @endif
                                                    @endif
                                                    <hr>

                                                </div>

                                                <div class="modal-footer">
                                                    @if($attendance->is_early_leave && $attendance->early_leave_status === 'pending' && Auth::user()->role === 'admin')
                                                        <form method="POST"
                                                            action="{{ route('admin.attendances.approveEarly', $attendance->id) }}"
                                                            style="display: inline;">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" class="btn btn-success"
                                                                onclick="return confirm('Duyệt về sớm và cập nhật lương?')">
                                                                ✓ Duyệt
                                                            </button>
                                                        </form>
                                                        <form method="POST"
                                                            action="{{ route('admin.attendances.rejectEarly', $attendance->id) }}"
                                                            style="display: inline;">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" class="btn btn-danger"
                                                                onclick="return confirm('Từ chối về sớm?')">
                                                                ✗ Từ chối
                                                            </button>
                                                        </form>
                                                    @endif
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                        Đóng
                                                    </button>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                @endif
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">
                                Chưa có ca làm nào
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-3">
                {{ $attendances->appends(request()->query())->links() }}
            </div>

            <div class="modal fade" id="reasonModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">

                        <form id="reasonForm" method="POST">
                            @csrf

                            <div class="modal-header">
                                <h5 class="modal-title" id="reasonModalTitle">
                                    Nhập lý do
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body">

                                <div class="alert alert-warning d-none" id="reasonAlert"></div>

                                <input type="hidden" name="mode_type" id="modeType">
                                <input type="hidden" name="reason_type" id="reasonType">
                                <input type="hidden" name="latitude" id="reasonLatitude">
                                <input type="hidden" name="longitude" id="reasonLongitude">
                                <input type="hidden" name="network_type" id="reasonNetworkType">

                                <div class="mb-3">
                                    <label for="reason">Lý do *</label>
                                    <textarea id="reason" name="reason" class="form-control" required></textarea>
                                </div>

                            </div>

                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary">
                                    Xác nhận
                                </button>
                            </div>

                        </form>

                    </div>
                </div>
            </div>

        </div>
        <div class="card mb-4 shadow-sm mt-4">
            <div class="card-header bg-primary text-white">
                <strong>Thông báo quy định chấm công & lương</strong>
            </div>
            <div class="card-body">

                <div class="row">
                    <div class="col-md-6">
                        <h6 class="fw-bold">Quy định ca làm</h6>
                        <ul class="mb-3">
                            <li>Ca sáng: <strong>8:00 - 11:00</strong></li>
                            <li>Ca chiều: <strong>13:00 - 16:00</strong></li>
                        </ul>
                        <h6 class="fw-bold">Mức lương quy định</h6>
                        <ul class="mb-3">
                            <li>Lương thử việc: <strong>15.000 đ / giờ</strong></li>
                            <li>Lương chính thức: <strong>20.000 đ / giờ</strong></li>
                        </ul>
                        <h6 class="fw-bold">Phạt & Thưởng</h6>
                        <ul class="mb-3">
                            <li>Trễ quá 5 lần/tháng: <strong class="text-danger">-200.000đ</strong></li>
                            <li>Về sớm quá 3 lần/tháng: <strong class="text-danger">-200.000đ</strong></li>
                            <li>Không trễ quá 3 lần, không nghỉ không phép, không về sớm quá 3 lần:
                                <strong class="text-success">+300.000đ chuyên cần</strong>
                            </li>
                        </ul>
                    </div>

                    <div class="col-md-6">
                        <h6 class="fw-bold">Chính sách tính lương</h6>
                        <ul class="mb-3">
                            <li>Lương tính theo phút làm thực tế của ca.</li>
                            <li>Trễ > 15 phút: tính theo thời gian thực tế làm việc.</li>
                            <li>Checkout trễ: chỉ tính lương đến hết giờ của ca.</li>
                            <li>Đi trễ hoặc về sớm bắt buộc nhập lý do để xét duyệt.</li>
                            <li>Check-in/check-out hợp lệ khi cùng mạng hợp lệ hoặc trong bán kính 50m từ vị trí gốc.</li>
                            <li>Mức lương theo giờ được áp dụng cố định theo quy định công ty.</li>
                        </ul>
                    </div>



                </div>

            </div>
        </div>

    </div>

    <!-- Modal nhập lý do về sớm -->
    <style>
        #earlyLeaveModal .modal-body {
            overflow: visible !important;
        }

        #earlyLeaveModal .form-select {
            position: relative;
            z-index: 1050;
            width: 100%;
            padding: 0.375rem 0.75rem;
            font-size: 1rem;
            font-weight: 400;
            line-height: 1.5;
            color: #212529;
            background-color: #fff;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 16px 12px;
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
            appearance: none;
        }

        #earlyLeaveModal .form-select option {
            display: block;
            padding: 0.5rem;
        }
    </style>

    <div class="modal fade" id="earlyLeaveModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title text-dark"> Bạn đang check-out sớm</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="overflow: visible !important;">
                    <p class="mb-3">
                        Bạn check-out trước giờ kết thúc ca. <strong>Vui lòng chọn lý do:</strong>
                    </p>
                    <div class="mb-3">
                        <label for="earlyLeaveReason" class="form-label">Lý do về sớm *</label>
                        <select class="form-select" id="earlyLeaveReason" required style="appearance: auto; width: 100%;">
                            <option value="">-- Chọn lý do --</option>
                            <option value="sick">Bị ốm / Cần khám bệnh</option>
                            <option value="family_emergency">Việc gia đình khẩn cấp</option>
                            <option value="business">Đi gặp khách / Việc công ty</option>
                            <option value="traffic">Sự cố giao thông</option>
                            <option value="personal">Việc cá nhân</option>
                            <option value="other">Lý do khác</option>
                        </select>
                    </div>
                    <input type="hidden" id="earlyLeaveAttendanceId">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-warning" onclick="submitEarlyCheckout()">Xác nhận &
                        Check-out</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function submitEarlyCheckout() {
            const attendanceId = document.getElementById('earlyLeaveAttendanceId').value;
            const reason = document.getElementById('earlyLeaveReason').value;

            if (!reason.trim()) {
                alert('Vui lòng chọn lý do về sớm');
                return;
            }

            // Map dropdown value to display text
            const reasonMap = {
                'sick': 'Bị ốm / Cần khám bệnh',
                'family_emergency': 'Việc gia đình khẩn cấp',
                'business': 'Đi gặp khách / Việc công ty',
                'traffic': 'Sự cố giao thông',
                'personal': 'Việc cá nhân',
                'other': 'Lý do khác'
            };

            const reasonDisplay = reasonMap[reason] || reason;

            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('earlyLeaveModal'));
            modal.hide();

            // Submit with reason
            submitAttendanceFormAjax(attendanceId, 'check-out', 'early', reasonDisplay);
        }
    </script>

@endsection