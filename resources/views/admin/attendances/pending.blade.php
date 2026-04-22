@extends('admin.layouts.layout_admin')

@section('content')
    <div class="container-fluid pt-4 px-4">
        <div class="bg-light rounded p-4">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div>
                    <h5 class="mb-1">Yeu cau cham cong cho duyet</h5>
                    <small class="text-muted">Danh sach ve som dang cho admin xu ly.</small>
                </div>
                <a href="{{ route('admin.attendances.index') }}" class="btn btn-secondary btn-sm">Quay lai bang cham
                    cong</a>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Ngay</th>
                            <th>Nhan vien</th>
                            <th>Ca</th>
                            <th>Gio vao</th>
                            <th>Gio ra</th>
                            <th>Tong gio</th>
                            <th>Luong ca</th>
                            <th>Ve som</th>
                            <th width="300">Xu ly</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendances as $attendance)
                            @php
                                $workedHours = $attendance->worked_minutes
                                    ? round($attendance->worked_minutes / 60, 2)
                                    : null;

                                // Tính pending salary theo rule mới: duyệt thì đến giờ xin về sớm.
                                $displaySalary = $attendance->salary_amount;
                                if ($attendance->is_early_leave && $attendance->early_leave_status === 'pending') {
                                    $rate = $attendance->staff->employment_status === 'official'
                                        ? ($attendance->staff->official_hourly_wage ?? 20000)
                                        : ($attendance->staff->probation_hourly_wage ?? 15000);

                                    $expectedCheckIn = \Carbon\Carbon::parse($attendance->work_date . ' ' . $attendance->expected_check_in);
                                    $expectedCheckOut = \Carbon\Carbon::parse($attendance->work_date . ' ' . $attendance->expected_check_out);

                                    $actualCheckIn = $attendance->check_in ? \Carbon\Carbon::parse($attendance->work_date . ' ' . $attendance->check_in) : null;
                                    $actualCheckOut = $attendance->check_out ? \Carbon\Carbon::parse($attendance->work_date . ' ' . $attendance->check_out) : null;

                                    // Nếu trễ > 15p: tính từ actual_in → actual_out, ngược lại từ expected_in → actual_out.
                                    if ($attendance->is_late && $actualCheckIn && $actualCheckOut) {
                                        $pendingWorkedMinutes = $actualCheckIn->diffInMinutes($actualCheckOut);
                                    } elseif ($actualCheckOut) {
                                        $pendingWorkedMinutes = $expectedCheckIn->diffInMinutes($actualCheckOut);
                                    } else {
                                        $pendingWorkedMinutes = null;
                                    }
                                    $displaySalary = round(($pendingWorkedMinutes / 60) * $rate);
                                }
                            @endphp
                            <tr>
                                <td>{{ \Illuminate\Support\Carbon::parse($attendance->work_date)->format('d/m/Y') }}</td>
                                <td>
                                    <div class="fw-semibold">
                                        {{ $attendance->staff?->user?->name ?? ('#' . $attendance->staff_id) }}
                                    </div>
                                    <small class="text-muted">ID: {{ $attendance->staff_id }}</small>
                                </td>
                                <td>{{ $attendance->shift === 'morning' ? 'Sang' : 'Chieu' }}</td>
                                <td>
                                    @if($attendance->check_in)
                                        <span class="badge bg-success">
                                            {{ \Carbon\Carbon::parse($attendance->check_in)->format('H:i') }}
                                        </span>
                                    @else
                                        <span class="text-muted">--</span>
                                    @endif
                                </td>
                                <td>
                                    @if($attendance->check_out)
                                        <span class="badge bg-danger">
                                            {{ \Carbon\Carbon::parse($attendance->check_out)->format('H:i') }}
                                        </span>
                                    @else
                                        <span class="text-muted">--</span>
                                    @endif
                                </td>
                                <td>
                                    @if($workedHours)
                                        <strong>{{ $workedHours }} gio</strong>
                                    @else
                                        <span class="text-muted">--</span>
                                    @endif
                                </td>
                                <td>
                                    @if($displaySalary)
                                        <strong>{{ number_format($displaySalary, 0, ',', '.') }}đ</strong>
                                    @else
                                        <span class="text-muted">--</span>
                                    @endif
                                </td>
                                <td>
                                    @if($attendance->early_leave_status === 'pending')
                                        <span class="badge bg-warning text-dark">Dang cho duyet</span>
                                        @if($attendance->early_leave_reason)
                                            <div class="small text-muted mt-2">
                                                <strong>Lý do:</strong> {{ $attendance->early_leave_reason }}
                                            </div>
                                        @endif
                                    @else
                                        <span class="badge bg-secondary">{{ $attendance->early_leave_status ?: 'Khong co' }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-2">
                                        @if($attendance->early_leave_status === 'pending')
                                            <form method="POST"
                                                action="{{ route('admin.attendances.approveEarly', $attendance->id) }}">
                                                @csrf
                                                <button class="btn btn-primary btn-sm">Duyet ve som</button>
                                            </form>
                                            <form method="POST"
                                                action="{{ route('admin.attendances.rejectEarly', $attendance->id) }}">
                                                @csrf
                                                <button class="btn btn-outline-danger btn-sm">Tu choi ve som</button>
                                            </form>
                                        @else
                                            <span class="text-muted small">Khong con yeu cau cho duyet</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">Khong co yeu cau nao dang cho duyet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection