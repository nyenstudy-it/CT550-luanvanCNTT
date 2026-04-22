@extends('admin.layouts.layout_admin')

@section('content')
    <div class="container-fluid pt-4 px-4">
        <div class="bg-light rounded p-4">

            <form method="GET" action="{{ route('admin.attendances.index') }}" class="row g-3 mb-3">

                <div class="col-md-3">
                    <label class="form-label">Từ ngày</label>
                    <input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Đến ngày</label>
                    <input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Ca</label>
                    <select name="shift" class="form-select">
                        <option value="">-- Tất cả --</option>
                        <option value="morning" {{ request('shift') == 'morning' ? 'selected' : '' }}>Ca sáng</option>
                        <option value="afternoon" {{ request('shift') == 'afternoon' ? 'selected' : '' }}>Ca chiều</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="">-- Tất cả --</option>
                        <option value="scheduled">Đã phân ca</option>
                        <option value="working">Đang làm</option>
                        <option value="completed">Hoàn thành</option>
                        <option value="absent">Vắng mặt</option>
                    </select>
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Lọc</button>
                </div>

            </form>



            <div class="d-flex justify-content-between mb-3">
                <h6>Phân ca nhân viên</h6>
                <a href="{{ route('admin.attendances.create') }}" class="btn btn-primary btn-sm">
                    + Phân ca
                </a>
            </div>

            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>STT</th>
                        <th>Nhân viên</th>
                        <th>Chức vụ</th>
                        <th>Ngày</th>
                        <th>Ca</th>
                        <th>Trạng thái</th>
                        <th>Giờ làm</th>
                        <th>Lương ca</th>
                        <th>Chấm công</th>
                        <th>Thao tác</th>
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

                            // Use worked_minutes - accessor will prefer DB value
                            $workedHours = !is_null($attendance->worked_minutes)
                                ? round($attendance->worked_minutes / 60, 2)
                                : null;

                            // Tính pending salary nếu early_leave && pending
                            $displaySalary = $attendance->salary_amount;
                            if ($attendance->is_early_leave && $attendance->early_leave_status === 'pending') {
                                $rate = $attendance->staff->employment_status === 'official'
                                    ? ($attendance->staff->official_hourly_wage ?? 20000)
                                    : ($attendance->staff->probation_hourly_wage ?? 15000);

                                $actualCheckIn = \Carbon\Carbon::parse($attendance->work_date . ' ' . $attendance->check_in);
                                $actualCheckOut = \Carbon\Carbon::parse($attendance->work_date . ' ' . $attendance->check_out);
                                $expectedCheckIn = \Carbon\Carbon::parse($attendance->work_date . ' ' . $attendance->expected_check_in);
                                $expectedCheckOut = \Carbon\Carbon::parse($attendance->work_date . ' ' . $attendance->expected_check_out);

                                // Tính trễ (dương = trễ, âm = sớm) — expected->diffInMinutes(checkIn)
                                $lateMinutes = $expectedCheckIn->diffInMinutes($actualCheckIn, false);

                                // Pending salary = dự tính khi DUYỆT (đến giờ nhân viên xin về sớm).
                                if ($lateMinutes <= 15) {
                                    $pendingWorkedMinutes = $expectedCheckIn->diffInMinutes($actualCheckOut);
                                } else {
                                    $pendingWorkedMinutes = $actualCheckIn->diffInMinutes($actualCheckOut);
                                }

                                $displaySalary = round(($pendingWorkedMinutes / 60) * $rate);
                            }

                        @endphp

                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $attendance->staff->user->name }}</td>
                            <td>
                                @php
                                    $positionMap = [
                                        'cashier' => 'Thu ngân',
                                        'warehouse' => 'Nhân viên kho',
                                        'order_staff' => 'Nhân viên đơn hàng',
                                    ];
                                @endphp

                                {{ $positionMap[$attendance->staff->position] ?? $attendance->staff->position }}
                            </td>
                            <td>{{ $attendance->work_date }}</td>
                            <td>{{ $shiftText }}</td>


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
                                @if(!is_null($workedHours))
                                    {{ $workedHours }} giờ
                                @else
                                    <span class="text-muted">--</span>
                                @endif
                            </td>

                            <td>
                                @if(!is_null($displaySalary))
                                    {{ number_format($displaySalary, 0, ',', '.') }} đ
                                @else
                                    <span class="text-muted">--</span>
                                @endif
                            </td>

                            <td class="text-center">
                                @if($attendance->check_in)
                                    <div class="mb-2">
                                        <span class="text-muted small">Vào:</span>
                                        <span class="badge bg-success">
                                            {{ \Carbon\Carbon::parse($attendance->check_in)->format('H:i') }}
                                        </span>
                                    </div>
                                @else
                                    <span class="text-muted small">Chưa vào</span>
                                @endif

                                @if($attendance->check_out)
                                    <div>
                                        <span class="text-muted small">Ra:</span>
                                        <span class="badge bg-danger">
                                            {{ \Carbon\Carbon::parse($attendance->check_out)->format('H:i') }}
                                        </span>
                                    </div>
                                @elseif($attendance->check_in)
                                    <div class="text-muted small">
                                        Chưa ra
                                    </div>
                                @endif

                                @if(!$attendance->check_in)
                                    <span class="text-muted fst-italic">
                                        Chưa chấm công
                                    </span>
                                @endif
                            </td>

                            <td class="text-center">

                                @if(!$attendance->check_in)
                                    <a href="{{ route('admin.attendances.edit', $attendance->id) }}" class="btn btn-sm btn-warning">
                                        Sửa
                                    </a>

                                    <form action="{{ route('admin.attendances.destroy', $attendance->id) }}" method="POST"
                                        class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xoá ca này?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            Xoá
                                        </button>
                                    </form>
                                @endif

                                @if($attendance->check_out)

                                                <button type="button" class="btn btn-sm btn-info mt-1" data-bs-toggle="modal"
                                                    data-bs-target="#detailModal{{ $attendance->id }}">
                                                    Xem chi tiết
                                                </button>
                                                <div class="modal fade" id="detailModal{{ $attendance->id }}" tabindex="-1">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">

                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Chi tiết ca làm</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>

                                                            <div class="modal-body">

                                                                <p><strong>Nhân viên:</strong> {{ $attendance->staff->user->name }}</p>
                                                                <p><strong>Ngày làm:</strong> {{ $attendance->work_date }}</p>
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

                                                                <p><strong>Tổng giờ làm:</strong>
                                                                    {{ $attendance->worked_minutes
                                    ? round($attendance->worked_minutes / 60, 2) . ' giờ (' . $attendance->worked_minutes . ' phút)'
                                    : '--' }}
                                                                </p>

                                                                <p><strong>Lương ca:</strong>
                                                                    @if(!is_null($attendance->salary_amount))
                                                                        <span
                                                                            class="badge bg-success">{{ number_format($attendance->salary_amount, 0, ',', '.') }}
                                                                            đ</span>
                                                                        @php
                                                                            $rate = $attendance->staff->employment_status === 'official' ? '20.000' : '15.000';
                                                                        @endphp
                                                                        <small class="text-muted d-block mt-1">
                                                                            ({{ $rate }}đ/giờ ×
                                                                            {{ round((($attendance->attributes['worked_minutes'] ?? 0) / 60), 2) }}h)
                                                                        </small>
                                                                    @else
                                                                        <span class="text-muted">--</span>
                                                                    @endif
                                                                </p>

                                                                <hr>

                                                                @if($attendance->check_in_latitude && $attendance->check_in_longitude)
                                                                    <p><strong> Vị trí Check-in:</strong></p>
                                                                    <p class="mb-1" style="font-size: 0.9rem;">
                                                                        <code>{{ $attendance->check_in_latitude }}, {{ $attendance->check_in_longitude }}</code>
                                                                    </p>
                                                                    @if($attendance->check_in_verification_method)
                                                                        @php
                                                                            $methodMap = [
                                                                                'wifi' => '✓ WiFi',
                                                                                'radius' => '✓ GPS (' . round($attendance->check_in_distance_meters, 1) . 'm)',
                                                                                'both' => '✓ WiFi + GPS (' . round($attendance->check_in_distance_meters, 1) . 'm)',
                                                                            ];
                                                                        @endphp
                                                                        <span
                                                                            class="badge bg-info">{{ $methodMap[$attendance->check_in_verification_method] ?? 'Unknown' }}</span>
                                                                    @endif
                                                                    @if($attendance->check_in_network_type)
                                                                        <span class="badge bg-secondary">{{ $attendance->check_in_network_type }}</span>
                                                                    @endif

                                                                    <hr>
                                                                @endif

                                                                @if($attendance->is_early_leave)
                                                                    <hr>
                                                                    <p class="text-warning"><strong>Về sớm</strong></p>

                                                                    @if($attendance->early_leave_status === 'pending')
                                                                        <span class="badge bg-secondary">Chờ duyệt</span>
                                                                    @elseif($attendance->early_leave_status === 'approved')
                                                                        <span class="badge bg-success">Đã duyệt</span>
                                                                    @elseif($attendance->early_leave_status === 'rejected')
                                                                        <span class="badge bg-danger">Từ chối</span>
                                                                    @endif
                                                                @endif

                                                                @if($attendance->early_leave_reason)
                                                                    <p class="mt-2"><strong>Lý do về sớm:</strong></p>
                                                                    <div class="border p-2 rounded bg-light">
                                                                        {{ $attendance->early_leave_reason }}
                                                                    </div>
                                                                @endif

                                                                @if($attendance->is_early_leave && $attendance->early_leave_status === 'pending')
                                                                    <div class="mt-3">
                                                                        <form
                                                                            action="{{ route('admin.attendances.approveEarly', $attendance->id) }}"
                                                                            method="POST" class="d-inline">
                                                                            @csrf
                                                                            <button type="submit" class="btn btn-success btn-sm">
                                                                                Duyệt
                                                                            </button>
                                                                        </form>

                                                                        <form action="{{ route('admin.attendances.rejectEarly', $attendance->id) }}"
                                                                            method="POST" class="d-inline ms-2">
                                                                            @csrf
                                                                            <button type="submit" class="btn btn-danger btn-sm">
                                                                                Từ chối
                                                                            </button>
                                                                        </form>
                                                                    </div>
                                                                @endif

                                                            </div>

                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                                    Đóng
                                                                </button>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>


                                @endif

                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="11" class="text-center text-muted">
                                Chưa có ca làm nào
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $attendances->links() }}
            </div>

        </div>
        <hr class="my-4">

        <h6 class="mb-3">Lịch ca làm việc</h6>

        <div id="calendar"></div>

    </div>
    <script>
        document.getElementById('shiftSelect').addEventListener('change', function () {

            const shift = this.value;
            const checkInInput = document.getElementById('checkInInput');
            const checkOutInput = document.getElementById('checkOutInput');

            if (shift === 'morning') {
                checkInInput.value = '08:00';
                checkOutInput.value = '11:00';
            }
            else if (shift === 'afternoon') {
                checkInInput.value = '13:00';
                checkOutInput.value = '16:00';
            }
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const calendarEl = document.getElementById('calendar');
            const events = @json($allCalendarEvents ?? []);

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'vi',
                height: 'auto',
                slotMinTime: '07:00:00',
                slotMaxTime: '17:00:00',
                allDaySlot: false,
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                events: events,
            });

            calendar.render();
        });
    </script>

@endsection