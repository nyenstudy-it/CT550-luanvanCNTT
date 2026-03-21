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


                        @php
    $totalShifts = $attendances->count();
    $totalMinutes = $attendances->sum('worked_minutes');
    $totalHours = $totalMinutes ? round($totalMinutes / 60, 2) : 0;
    $totalSalary = $attendances->sum('salary_amount');
                        @endphp

                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="bg-white p-3 rounded border">
                                    <h6>Tổng số ca</h6>
                                    <h4 class="text-primary">{{ $totalShifts }}</h4>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="bg-white p-3 rounded border">
                                    <h6>Tổng giờ làm</h6>
                                    <h4 class="text-success">{{ $totalHours }} giờ</h4>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="bg-white p-3 rounded border">
                                    <h6>Tổng tiền lương</h6>
                                    <h4 class="text-danger">
                                        {{ number_format($totalSalary, 0, ',', '.') }} đ
                                    </h4>
                                </div>
                            </div>
                        </div>

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

                                    $workedHours = !is_null($attendance->worked_minutes)
                                        ? round($attendance->worked_minutes / 60, 2)
                                        : null;

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
                                                                                        </td>

                                                                                        <td>
                                                                                            @if(!is_null($workedHours))
                                                                                                {{ $workedHours }} giờ
                                                                                            @else
                                                                                                <span class="text-muted">--</span>
                                                                                            @endif
                                                                                        </td>

                                                                                        <td>
                                                                                            @if(!is_null($attendance->salary_amount))
                                                                                                {{ number_format($attendance->salary_amount, 0, ',', '.') }} đ
                                                                                            @else
                                                                                                <span class="text-muted">--</span>
                                                                                            @endif
                                                                                        </td>

                                                                                        <td class="text-center">
                                                                                            @if($attendance->check_in)
                                                                                                <span class="badge bg-success mb-1">
                                                                                                    {{ \Carbon\Carbon::parse($attendance->check_in)->format('H:i') }}
                                                                                                </span>
                                                                                            @endif

                                                                                            @if($attendance->check_out)
                                                                                                <br>
                                                                                                <span class="badge bg-danger mt-1">
                                                                                                    {{ \Carbon\Carbon::parse($attendance->check_out)->format('H:i') }}
                                                                                                </span>
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

                                                                                                                            <p><strong>Giờ vào:</strong>
                                                                                                                                {{ $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i') : '--' }}
                                                                                                                            </p>

                                                                                                                            <p><strong>Giờ ra:</strong>
                                                                                                                                {{ $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('H:i') : '--' }}
                                                                                                                            </p>

                                                                                                                            <p><strong>Tổng giờ làm:</strong>
                                                                                                                                {{ $attendance->worked_minutes
                                            ? round($attendance->worked_minutes / 60, 2) . ' giờ'
                                            : '--' }}
                                                                                                                            </p>

                                                                                                                            <p><strong>Lương ca:</strong>
                                                                                                                                @if(!is_null($attendance->salary_amount))
                                                                                                                                    {{ number_format($attendance->salary_amount, 0, ',', '.') }} đ
                                                                                                                                @else
                                                                                                                                    <span class="text-muted">--</span>
                                                                                                                                @endif
                                                                                                                            </p>

                                                                                                                            @if($attendance->is_late)
                                                                                                                                <hr>
                                                                                                                                <p class="text-danger"><strong>Đi trễ</strong></p>

                                                                                                                                @if($attendance->late_status === 'pending')
                                                                                                                                    <span class="badge bg-secondary">Chờ duyệt</span>
                                                                                                                                @elseif($attendance->late_status === 'approved')
                                                                                                                                    <span class="badge bg-success">Đã duyệt</span>
                                                                                                                                @elseif($attendance->late_status === 'rejected')
                                                                                                                                    <span class="badge bg-danger">Từ chối</span>
                                                                                                                                @endif
                                                                                                                            @endif

                                                                                                                            @if($attendance->late_reason)
                                                                                                                                <p class="mt-2"><strong>Lý do đi trễ:</strong></p>
                                                                                                                                <div class="border p-2 rounded bg-light">
                                                                                                                                    {{ $attendance->late_reason }}
                                                                                                                                </div>
                                                                                                                            @endif

                                                                                                                            @if($attendance->is_late && $attendance->late_status === 'pending')
                                                                                                                                <div class="mt-3">
                                                                                                                                    <form action="{{ route('admin.attendances.approveLate', $attendance->id) }}"
                                                                                                                                        method="POST" class="d-inline">
                                                                                                                                        @csrf
                                                                                                                                        <button type="submit" class="btn btn-success btn-sm">
                                                                                                                                            Duyệt
                                                                                                                                        </button>
                                                                                                                                    </form>

                                                                                                                                    <form action="{{ route('admin.attendances.rejectLate', $attendance->id) }}"
                                                                                                                                        method="POST" class="d-inline ms-2">
                                                                                                                                        @csrf
                                                                                                                                        <button type="submit" class="btn btn-danger btn-sm">
                                                                                                                                            Từ chối
                                                                                                                                        </button>
                                                                                                                                    </form>
                                                                                                                                </div>
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
                        const events = @json($calendarEvents);

                        const calendar = new FullCalendar.Calendar(calendarEl, {
                            initialView: 'timeGridWeek',
                            locale: 'vi',
                            height: 600,
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