@extends('admin.layouts.layout_admin')

@section('content')
    <div class="container-fluid pt-4 px-4">
        <div class="bg-light rounded p-4">

            <h6 class="mb-3">Chấm công của tôi</h6>
            <div class="row g-3 mb-4">

                <div class="col-md-3">
                    <div class="bg-white rounded shadow-sm p-3 text-center h-100">
                        <h6 class="text-muted">Tổng ca</h6>
                        <h4 class="fw-bold text-primary">
                            {{ $attendances->count() }}
                        </h4>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="bg-white rounded shadow-sm p-3 text-center h-100">
                        <h6 class="text-muted">Tổng giờ làm</h6>
                        <h4 class="fw-bold text-success">
                            {{ round($totalWorkedMinutes / 60, 2) }}giờ
                        </h4>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="bg-white rounded shadow-sm p-3 text-center h-100">
                        <h6 class="text-muted">Lương tạm tính</h6>
                        <h4 class="fw-bold text-warning">
                            {{ number_format($totalSalary) }}đ
                        </h4>
                    </div>
                </div>

            </div>

            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th width="60">STT</th>
                        <th>Ngày</th>
                        <th>Ca</th>
                        <th>Giờ vào</th>
                        <th>Giờ ra</th>
                        <th>Trạng thái</th>
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

                                    $workedHours = $attendance->worked_minutes
                                        ? round($attendance->worked_minutes / 60, 2)
                                        : null;
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
                                        @endif
                                    </td>

                                    <td>
                                        @if(!$attendance->check_in)
                                            <form method="POST" action="{{ route('staff.attendances.check_in', $attendance->id) }}">
                                                @csrf
                                                <button type="button" class="btn btn-success btn-sm"
                                                    onclick="handleCheckin(
                                                                                                                                                                                                                    '{{ $attendance->id }}',
                                                                                                                                                                                                                    '{{ $attendance->work_date }}',
                                                                                                                                                                                                                    '{{ $attendance->expected_check_in }}'
                                                                                                                                                                                                                )">
                                                    Check in
                                                </button>

                                            </form>

                                        @elseif(!$attendance->check_out)
                                            <form method="POST" action="{{ route('staff.attendances.check_out', $attendance->id) }}">
                                                @csrf
                                                <button type="button" class="btn btn-danger btn-sm"
                                                    onclick="handleCheckout(
                                                                                                                                                            '{{ $attendance->id }}',
                                                                                                                                                            '{{ $attendance->work_date }}',
                                                                                                                                                            '{{ $attendance->expected_check_out }}'
                                                                                                                                                        )">
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

                                                        <p>
                                                            <strong>Giờ vào:</strong>
                                                            {{ $attendance->check_in
                                    ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i')
                                    : '--' }}
                                                        </p>

                                                        <p>
                                                            <strong>Giờ ra:</strong>
                                                            {{ $attendance->check_out
                                    ? \Carbon\Carbon::parse($attendance->check_out)->format('H:i')
                                    : '--' }}
                                                        </p>

                                                        <p>
                                                            <strong>Tổng giờ làm:</strong>
                                                            {{ $attendance->worked_minutes !== null
                                    ? round($attendance->worked_minutes / 60, 2) . ' giờ'
                                    : 'Chưa tính' }}
                                                        </p>

                                                        <p>
                                                            <strong>Lương ca:</strong>
                                                            @if($attendance->salary_amount)
                                                                {{ number_format($attendance->salary_amount, 0, '.', ',') . 'đ' }}
                                                            @else
                                                                Chưa tính
                                                            @endif
                                                        </p>
                                                        <hr>

                                                        @if($attendance->is_late)
                                                            <p class="text-danger">
                                                                <strong>Đi trễ:</strong> Có
                                                            </p>

                                                        @endif

                                                        @if($attendance->is_early_leave)
                                                            <p class="text-warning">
                                                                <strong>Về sớm:</strong> Có
                                                            </p>

                                                            @if($attendance->early_leave_reason)
                                                                <p>
                                                                    <strong>Lý do về sớm:</strong>
                                                                    {{ $attendance->early_leave_reason }}
                                                                </p>
                                                            @endif

                                                            @if($attendance->early_leave_status)
                                                                <p>
                                                                    <strong>Trạng thái duyệt:</strong>
                                                                    @if($attendance->early_leave_status === 'pending')
                                                                        <span class="badge bg-warning">Chờ duyệt</span>
                                                                    @elseif($attendance->early_leave_status === 'approved')
                                                                        <span class="badge bg-success">Đã duyệt</span>
                                                                    @elseif($attendance->early_leave_status === 'rejected')
                                                                        <span class="badge bg-danger">Từ chối</span>
                                                                    @endif
                                                                </p>
                                                            @endif
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
                {{ $attendances->links() }}
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
                                    <label>Lý do *</label>
                                    <textarea name="reason" class="form-control" required></textarea>
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
                            <li>Lương thử việc: <strong>20.000 đ / giờ</strong></li>
                            <li>Lương chính thức: <strong>30.000 đ / giờ</strong></li>
                        </ul>
                        <h6 class="fw-bold">Phạt & Thưởng</h6>
                        <ul class="mb-3">
                            <li>Trễ quá 5 lần/tháng: <strong class="text-danger">-200.000đ</strong></li>
                            <li>Về sớm quá 3 lần/tháng: <strong class="text-danger">-200.000đ</strong></li>
                            <li>Không trễ quá 3 lần, không nghỉ không phép, không về sớm quá 3 lần:
                                <strong class="text-success">+500.000đ chuyên cần</strong>
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

    <script>

        function buildExpectedDate(workDate, timeString) {
            const [year, month, day] = workDate.split('-');
            const [hour, minute] = timeString.split(':');

            return new Date(
                parseInt(year),
                parseInt(month) - 1,
                parseInt(day),
                parseInt(hour),
                parseInt(minute),
                0
            );
        }

        function getNetworkType() {
            const connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;

            if (!connection) {
                return null;
            }

            return connection.effectiveType || connection.type || null;
        }

        function getCurrentPositionAsync() {
            return new Promise((resolve) => {
                if (!navigator.geolocation) {
                    resolve(null);
                    return;
                }

                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        resolve({
                            latitude: position.coords.latitude,
                            longitude: position.coords.longitude,
                        });
                    },
                    () => resolve(null),
                    {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 0,
                    }
                );
            });
        }

        async function collectAttendanceContext() {
            const position = await getCurrentPositionAsync();

            return {
                latitude: position ? position.latitude : null,
                longitude: position ? position.longitude : null,
                networkType: getNetworkType(),
            };
        }

        function appendHiddenInput(form, name, value) {
            if (value === null || value === undefined || value === '') {
                return;
            }

            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            input.value = value;

            form.appendChild(input);
        }

        async function handleCheckin(attendanceId, workDate, expectedIn) {

            const now = new Date();
            const expectedDate = buildExpectedDate(workDate, expectedIn);

            const lateMinutes = Math.floor((now - expectedDate) / 60000);

            await submitDirect(attendanceId, 'check-in');
        }

        async function handleCheckout(attendanceId, workDate, expectedOut) {

            if (!expectedOut) {
                await submitDirect(attendanceId, 'check-out');
                return;
            }

            const now = new Date();
            const expectedDate = buildExpectedDate(workDate, expectedOut);

            const earlyMinutes = Math.floor((expectedDate - now) / 60000);

            if (earlyMinutes > 0) {

                const form = document.getElementById('reasonForm');
                form.action = `/staff/attendances/${attendanceId}/check-out`;

                document.getElementById('reasonType').value = 'early';
                document.getElementById('modeType').value = 'checkout';

                document.getElementById('reasonModalTitle').innerText = 'Về sớm';

                const alertBox = document.getElementById('reasonAlert');
                alertBox.classList.remove('d-none');
                alertBox.innerHTML =
                    `Bạn đang về sớm <strong>${earlyMinutes} phút</strong>. Vui lòng nhập lý do.`;

                const modal = new bootstrap.Modal(
                    document.getElementById('reasonModal')
                );
                modal.show();

            } else {
                await submitDirect(attendanceId, 'check-out');
            }
        }

        // ===== SUBMIT KHÔNG CẦN LÝ DO =====
        async function submitDirect(attendanceId, type) {

            const context = await collectAttendanceContext();

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/staff/attendances/${attendanceId}/${type}`;

            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = '{{ csrf_token() }}';

            form.appendChild(csrf);

            appendHiddenInput(form, 'latitude', context.latitude);
            appendHiddenInput(form, 'longitude', context.longitude);
            appendHiddenInput(form, 'network_type', context.networkType);

            document.body.appendChild(form);
            form.submit();
        }

        document.getElementById('reasonForm').addEventListener('submit', async function (event) {
            if (this.dataset.submitting === '1') {
                return;
            }

            event.preventDefault();

            const context = await collectAttendanceContext();

            document.getElementById('reasonLatitude').value = context.latitude ?? '';
            document.getElementById('reasonLongitude').value = context.longitude ?? '';
            document.getElementById('reasonNetworkType').value = context.networkType ?? '';

            this.dataset.submitting = '1';
            this.submit();
        });

    </script>

@endsection