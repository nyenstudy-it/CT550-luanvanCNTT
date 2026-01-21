@extends('admin.layouts.layout_admin')

@section('content')
    <div class="container-fluid pt-4 px-4">
        <div class="bg-light rounded p-4">

            <div class="d-flex justify-content-between mb-3">
                <h6>Phân ca nhân viên</h6>
                <a href="{{ route('admin.staff.attendances.create') }}" class="btn btn-primary btn-sm">
                    + Phân ca
                </a>
            </div>

            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Nhân viên</th>
                        <th>Ngày</th>
                        <th>Ca</th>
                        <th>Giờ vào</th>
                        <th>Giờ ra</th>
                        <th>Trạng thái</th>
                        <th>Chấm công</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendances as $attendance)
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
                            $status = $badgeMap[$statusKey];
                        @endphp

                        <tr>
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
                            </td>

                            {{-- Chấm công --}}
                            <td class="text-center">
                                @if($attendance->check_in)
                                    <span class="badge bg-success mb-1">
                                        <i class="bi bi-box-arrow-in-right"></i>
                                        {{ \Carbon\Carbon::parse($attendance->check_in)->format('H:i') }}
                                    </span>
                                @endif

                                @if($attendance->check_out)
                                    <br>
                                    <span class="badge bg-danger mt-1">
                                        <i class="bi bi-box-arrow-left"></i>
                                        {{ \Carbon\Carbon::parse($attendance->check_out)->format('H:i') }}
                                    </span>
                                @endif

                                @if(!$attendance->check_in)
                                    <span class="text-muted fst-italic">
                                        Chưa chấm công
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">
                                Chưa có ca làm nào
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>

        </div>
    </div>
@endsection