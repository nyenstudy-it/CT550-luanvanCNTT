@extends('admin.layouts.layout_admin')

@section('content')
    <div class="container-fluid pt-4 px-4">
        <div class="bg-light rounded p-4">

            <h6 class="mb-3">Chấm công của tôi</h6>

            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Ngày</th>
                        <th>Ca</th>
                        <th>Giờ vào (dự kiến)</th>
                        <th>Giờ ra (dự kiến)</th>
                        <th>Trạng thái</th>
                        <th>Chấm công</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendances as $attendance)
                        <tr>
                            <td>{{ $attendance->work_date }}</td>
                            @php
                                $shiftText = match ($attendance->shift) {
                                    'morning' => 'Ca sáng',
                                    'afternoon' => 'Ca chiều',
                                    default => 'Không xác định',
                                };
                            @endphp

                            <td>{{ $shiftText }}</td>

                            <td>{{ $attendance->expected_check_in }}</td>
                            <td>{{ $attendance->expected_check_out }}</td>

                            <td>
                                @php
                                    $badgeMap = [
                                        'scheduled' => ['label' => 'Đã phân ca', 'class' => 'bg-info'],
                                        'working' => ['label' => 'Đang làm', 'class' => 'bg-warning'],
                                        'completed' => ['label' => 'Đã hoàn thành', 'class' => 'bg-success'],
                                        'absent' => ['label' => 'Vắng mặt', 'class' => 'bg-danger'],
                                    ];

                                    $statusKey = $attendance->computed_status;
                                    $status = $badgeMap[$statusKey];
                                @endphp

                                <span class="badge {{ $status['class'] }}">
                                    {{ $status['label'] }}
                                </span>

                            </td>

                            <td>
                                {{-- Check in --}}
                                @if(!$attendance->check_in)
                                    <form method="POST" action="{{ route('staff.attendances.check_in', $attendance->id) }}">
                                        @csrf
                                        <button class="btn btn-success btn-sm">
                                            Check in
                                        </button>
                                    </form>

                                    {{-- Check out --}}
                                @elseif(!$attendance->check_out)
                                    <form method="POST" action="{{ route('staff.attendances.check_out', $attendance->id) }}">
                                        @csrf
                                        <button class="btn btn-danger btn-sm">
                                            Check out
                                        </button>
                                    </form>

                                @else
                                    <span class="text-muted">Hoàn thành</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">
                                Bạn chưa có ca làm nào
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

        </div>
    </div>
@endsection