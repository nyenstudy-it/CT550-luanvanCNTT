@extends('admin.layouts.layout_admin')

@section('content')
            <div class="container-fluid pt-4 px-4">
                <div class="bg-light rounded p-4">

                    @php
    $totalShifts = $attendances->count();
    $totalMinutes = $attendances->sum('worked_minutes');
    $totalHours = $totalMinutes ? round($totalMinutes / 60, 2) : 0;

    $hourlyRate = 25000;
    $totalSalary = $totalMinutes ? round(($totalMinutes / 60) * $hourlyRate, 0) : 0;
                    @endphp

                    {{-- ====== THỐNG KÊ ====== --}}
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

                    {{-- ====== HEADER ====== --}}
                    <div class="d-flex justify-content-between mb-3">
                        <h6>Phân ca nhân viên</h6>
                        <a href="{{ route('admin.staff.attendances.create') }}" class="btn btn-primary btn-sm">
                            + Phân ca
                        </a>
                    </div>

                    {{-- ====== TABLE ====== --}}
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>STT</th>
                                <th>Nhân viên</th>
                                <th>Ngày</th>
                                <th>Ca</th>
                                <th>Giờ vào</th>
                                <th>Giờ ra</th>
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

        $workedHours = $attendance->worked_minutes
            ? round($attendance->worked_minutes / 60, 2)
            : null;
                                @endphp

                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $attendance->staff->user->name }}</td>
                                    <td>{{ $attendance->work_date }}</td>
                                    <td>{{ $shiftText }}</td>
                                    <td>{{ $attendance->expected_check_in }}</td>
                                    <td>{{ $attendance->expected_check_out }}</td>

                                    {{-- Trạng thái --}}
                                    <td>
                                        <span class="badge {{ $status['class'] }}">
                                            {{ $status['label'] }}
                                        </span>

                                        @if($attendance->is_late)
                                            <span class="badge bg-danger mt-1">Đi trễ</span>
                                        @endif

                                        @if($attendance->is_early_leave)
                                            <span class="badge bg-warning mt-1">Về sớm</span>
                                        @endif
                                    </td>

                                    {{-- Giờ làm --}}
                                    <td>
                                        @if($workedHours)
                                            {{ $workedHours }} giờ
                                        @else
                                            <span class="text-muted">--</span>
                                        @endif
                                    </td>

                                    {{-- Lương --}}
                                    <td>
                                        @if($attendance->salary_amount)
                                            {{ number_format($attendance->salary_amount, 0, ',', '.') }} đ
                                        @else
                                            <span class="text-muted">--</span>
                                        @endif
                                    </td>

                                    {{-- Chấm công --}}
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

                                    {{-- Thao tác --}}
                                    <td class="text-center">

                                        @if(!$attendance->check_in)
                                            <a href="{{ route('admin.staff.attendances.edit', $attendance->id) }}"
                                                class="btn btn-sm btn-warning">
                                                Sửa
                                            </a>

                                            <form action="{{ route('admin.staff.attendances.destroy', $attendance->id) }}" method="POST"
                                                class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xoá ca này?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    Xoá
                                                </button>
                                            </form>
                                        @endif

                                        {{-- Nút xem chi tiết khi đã hoàn thành --}}
                                        @if($attendance->check_out)

                                            <button type="button" class="btn btn-sm btn-info mt-1" data-bs-toggle="modal"
                                                data-bs-target="#detailModal{{ $attendance->id }}">
                                                Xem chi tiết
                                            </button>

                                            {{-- Modal --}}
                                            <div class="modal fade" id="detailModal{{ $attendance->id }}" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">

                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Chi tiết ca làm</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>

                                                        <div class="modal-body">

                                                            <p><strong>Nhân viên:</strong>
                                                                {{ $attendance->staff->user->name }}
                                                            </p>

                                                            <p><strong>Ngày làm:</strong>
                                                                {{ $attendance->work_date }}
                                                            </p>

                                                            <p><strong>Ca:</strong>
                                                                {{ $shiftText }}
                                                            </p>

                                                            <hr>

                                                            <p><strong>Giờ vào:</strong>
                                                                {{ $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i') : '--' }}
                                                            </p>

                                                            <p><strong>Giờ ra:</strong>
                                                                {{ $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('H:i') : '--' }}
                                                            </p>

                                                            <p><strong>Tổng giờ làm:</strong>
                                                                {{ $workedHours ?? '--' }} giờ
                                                            </p>

                                                            <p><strong>Lương ca:</strong>
                                                                {{ $attendance->salary_amount ? number_format($attendance->salary_amount, 0, ',', '.') : '--' }}
                                                                đ
                                                            </p>

                                                            @if($attendance->is_late)
                                                                <p class="text-danger"><strong>Đi trễ</strong></p>
                                                            @endif

                                                            @if($attendance->is_early_leave)
                                                                <p class="text-warning"><strong>Về sớm</strong></p>
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

        {{-- FullCalendar --}}
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