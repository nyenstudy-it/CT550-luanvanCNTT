@extends('admin.layouts.layout_admin')

@section('content')
    <div class="container-fluid pt-4 px-4">
        <div class="bg-light rounded p-4">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div>
                    <h5 class="mb-1">Yeu cau cham cong cho duyet</h5>
                    <small class="text-muted">Danh sach di tre/ve som dang cho admin xu ly.</small>
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
                            <th>Di tre</th>
                            <th>Ve som</th>
                            <th width="260">Xu ly</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendances as $attendance)
                            <tr>
                                <td>{{ \Illuminate\Support\Carbon::parse($attendance->work_date)->format('d/m/Y') }}</td>
                                <td>
                                    <div class="fw-semibold">
                                        {{ $attendance->staff?->user?->name ?? ('#' . $attendance->staff_id) }}</div>
                                    <small class="text-muted">ID: {{ $attendance->staff_id }}</small>
                                </td>
                                <td>{{ $attendance->shift === 'morning' ? 'Sang' : 'Chieu' }}</td>
                                <td>
                                    @if($attendance->late_status === 'pending')
                                        <span class="badge bg-warning text-dark">Dang cho duyet</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $attendance->late_status ?: 'Khong co' }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($attendance->early_leave_status === 'pending')
                                        <span class="badge bg-warning text-dark">Dang cho duyet</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $attendance->early_leave_status ?: 'Khong co' }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-2">
                                        @if($attendance->late_status === 'pending')
                                            <form method="POST"
                                                action="{{ route('admin.attendances.approveLate', $attendance->id) }}">
                                                @csrf
                                                <button class="btn btn-success btn-sm">Duyet di tre</button>
                                            </form>
                                            <form method="POST"
                                                action="{{ route('admin.attendances.rejectLate', $attendance->id) }}">
                                                @csrf
                                                <button class="btn btn-outline-danger btn-sm">Tu choi di tre</button>
                                            </form>
                                        @endif

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
                                        @endif

                                        @if($attendance->late_status !== 'pending' && $attendance->early_leave_status !== 'pending')
                                            <span class="text-muted small">Khong con yeu cau cho duyet</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Khong co yeu cau nao dang cho duyet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection