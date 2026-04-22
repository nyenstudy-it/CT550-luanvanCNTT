@extends('admin.layouts.layout_admin')

@section('content')
    <div class="container-fluid pt-4 px-4">
        <div class="bg-light rounded p-4">

            @php
                $selectedMonth = request('month', date('m'));
                $selectedYear = request('year', date('Y'));

                $salaries = \App\Models\Salary::where('month', $selectedMonth)
                    ->where('year', $selectedYear)
                    ->with('staff.user')
                    ->get();

                $grandTotal = $salaries->sum('final_salary');
                $totalBase = $salaries->sum('total_salary');
                $totalPenalties = $salaries->sum('penalty_amount');
                $totalBonuses = $salaries->sum('bonus_amount');
            @endphp

            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div>
                    <h5 class="mb-1">Bảng lương tổng hợp</h5>
                    <small class="text-muted">Lương tháng {{ $selectedMonth }}/{{ $selectedYear }}</small>
                </div>
            </div>

            {{-- Filters --}}
            <form method="GET" class="row g-3 mb-4 border rounded bg-white p-3">
                <div class="col-md-3">
                    <label class="form-label">Tháng</label>
                    <select name="month" class="form-select">
                        @for($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" {{ $selectedMonth == $i ? 'selected' : '' }}>
                                Tháng {{ $i }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Năm</label>
                    <select name="year" class="form-select">
                        @for($y = date('Y'); $y >= 2020; $y--)
                            <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>
                                {{ $y }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div class="col-md-6 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">Lọc</button>
                    <a href="{{ route('admin.salaries.monthly', ['month' => date('m'), 'year' => date('Y')]) }}"
                        class="btn btn-secondary">Đặt lại</a>
                </div>
            </form>

            <div class="row mb-4 g-3">
                <div class="col-md-3">
                    <div class="bg-white rounded shadow-sm p-3 text-center">
                        <h6 class="text-muted small">Lương cơ bản</h6>
                        <h4 class="fw-bold text-primary">{{ number_format($totalBase, 0, ',', '.') }}đ</h4>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="bg-white rounded shadow-sm p-3 text-center">
                        <h6 class="text-muted small">Tổng phạt</h6>
                        <h4 class="fw-bold text-danger">-{{ number_format($totalPenalties, 0, ',', '.') }}đ</h4>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="bg-white rounded shadow-sm p-3 text-center">
                        <h6 class="text-muted small">Tổng thưởng</h6>
                        <h4 class="fw-bold text-success">+{{ number_format($totalBonuses, 0, ',', '.') }}đ</h4>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="bg-white rounded shadow-sm p-3 text-center">
                        <h6 class="text-muted small">Lương cuối cùng</h6>
                        <h4 class="fw-bold text-info">{{ number_format($grandTotal, 0, ',', '.') }}đ</h4>
                    </div>
                </div>
            </div>

            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th width="120">Nhân viên</th>
                        <th width="100">Số ca</th>
                        <th width="130">Lương cơ bản</th>
                        <th width="100">Đi trễ</th>
                        <th width="100">Về sớm</th>
                        <th width="100">Vắng</th>
                        <th width="130">Phạt</th>
                        <th width="150">Thưởng chuyên cần</th>
                        <th width="130">Lương cuối cùng</th>
                        <th width="120">Chi tiết</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($salaries as $salary)
                        <tr>
                            <td class="fw-bold">{{ $salary->staff ? $salary->staff->user->name : 'N/A' }}</td>
                            <td class="text-center">
                                {{ intval($salary->total_hours) . 'h' }}
                            </td>
                            <td class="text-end">
                                {{ number_format($salary->total_salary, 0, ',', '.') }}đ
                            </td>
                            <td class="text-center">
                                <span class="badge bg-danger">{{ $salary->late_count }}</span>
                                @if ($salary->late_count > 5)
                                    <small class="d-block text-danger fw-bold">-200kđ</small>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-warning">{{ $salary->early_leave_count }}</span>
                                @if ($salary->early_leave_count > 3)
                                    <small class="d-block text-danger fw-bold">-200kđ</small>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary">{{ $salary->absent_count }}</span>
                            </td>
                            <td class="text-end">
                                @if ($salary->penalty_amount > 0)
                                    <span
                                        class="text-danger fw-bold">-{{ number_format($salary->penalty_amount, 0, ',', '.') }}đ</span>
                                @else
                                    <span class="text-muted">0đ</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if ($salary->bonus_amount > 0)
                                    <span
                                        class="text-success fw-bold">+{{ number_format($salary->bonus_amount, 0, ',', '.') }}đ</span>
                                    <small class="d-block text-success">(Chuyên cần)</small>
                                @else
                                    <span class="text-muted">0đ</span>
                                @endif
                            </td>
                            <td class="text-end fw-bold">
                                <span class="text-primary fs-6">{{ number_format($salary->final_salary, 0, ',', '.') }}đ</span>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal"
                                    data-bs-target="#detailModal{{ $salary->id }}">
                                    Chi tiết
                                </button>
                            </td>
                        </tr>

                        <!-- Detail Modal -->
                        <div class="modal fade" id="detailModal{{ $salary->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Lương -
                                            {{ $salary->staff ? $salary->staff->user->name : 'N/A' }}
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label"><strong>Tháng:</strong>
                                                {{ $salary->month }}/{{ $salary->year }}</label>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label"><strong>Số ca làm việc:</strong>
                                                {{ intval($salary->total_hours) }}h ({{ $salary->total_minutes }}p)</label>
                                        </div>
                                        <hr>
                                        <div class="mb-3">
                                            <label class="form-label"><strong>Lương cơ bản:</strong></label>
                                            <p class="fs-5 text-primary fw-bold">
                                                {{ number_format($salary->total_salary, 0, ',', '.') }}đ
                                            </p>
                                        </div>
                                        <hr>
                                        <div class="mb-3">
                                            <label class="form-label"><strong>Chi tiết phạt/thưởng:</strong></label>
                                            <ul class="list-unstyled">
                                                <li class="mb-2">
                                                    <span class="badge bg-danger">Đi trễ: {{ $salary->late_count }}x</span>
                                                    @if ($salary->late_count > 5)
                                                        <span class="ms-2 text-danger fw-bold">→ Phạt: -200.000đ</span>
                                                    @else
                                                        <span class="ms-2 text-success">(≤ 5, không phạt)</span>
                                                    @endif
                                                </li>
                                                <li class="mb-2">
                                                    <span class="badge bg-warning">Về sớm:
                                                        {{ $salary->early_leave_count }}x</span>
                                                    @if ($salary->early_leave_count > 3)
                                                        <span class="ms-2 text-danger fw-bold">→ Phạt: -200.000đ</span>
                                                    @else
                                                        <span class="ms-2 text-success">(≤ 3, không phạt)</span>
                                                    @endif
                                                </li>
                                                <li class="mb-2">
                                                    <span class="badge bg-secondary">Vắng: {{ $salary->absent_count }}x</span>
                                                </li>
                                            </ul>
                                        </div>
                                        <hr>
                                        <div class="mb-3">
                                            <label class="form-label"><strong>Thưởng chuyên cần:</strong></label>
                                            @php
                                                $isDiligent = $salary->late_count <= 3 && $salary->absent_count == 0 && $salary->early_leave_count <= 3;
                                            @endphp
                                            @if ($isDiligent)
                                                <p class="fs-5 text-success fw-bold">+300.000đ ✓</p>
                                                <small class="text-muted">Điều kiện: ≤ 3 lần trễ, không vắng, ≤ 3 lần về sớm</small>
                                            @else
                                                <p class="fs-5 text-danger">0đ (Không đủ điều kiện)</p>
                                                <small class="text-muted">Cần: ≤ 3 lần trễ, không vắng, ≤ 3 lần về sớm</small>
                                            @endif
                                        </div>
                                        <hr>
                                        <div class="mb-3">
                                            <label class="form-label"><strong>Tổng phạt:</strong></label>
                                            <p class="fs-5 text-danger fw-bold">
                                                -{{ number_format($salary->penalty_amount, 0, ',', '.') }}đ</p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label"><strong>Tổng thưởng:</strong></label>
                                            <p class="fs-5 text-success fw-bold">
                                                +{{ number_format($salary->bonus_amount, 0, ',', '.') }}đ</p>
                                        </div>
                                        <hr>
                                        <div class="mb-3">
                                            <label class="form-label"><strong>Lương cuối cùng:</strong></label>
                                            <p class="fs-4 text-info fw-bold">
                                                {{ number_format($salary->final_salary, 0, ',', '.') }}đ
                                            </p>
                                            <small class="text-muted d-block">
                                                = {{ number_format($salary->total_salary, 0, ',', '.') }}đ -
                                                {{ number_format($salary->penalty_amount, 0, ',', '.') }}đ +
                                                {{ number_format($salary->bonus_amount, 0, ',', '.') }}đ
                                            </small>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">
                                Không có dữ liệu lương cho tháng này
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-4 p-3 bg-info bg-opacity-10 rounded">
                <strong class="d-block mb-2">📋 Quy tắc tính phạt & thưởng:</strong>
                <ul class="list-unstyled small">
                    <li>✗ Đi trễ quá 5 lần/tháng: <span class="text-danger fw-bold">-200.000đ</span></li>
                    <li>✗ Về sớm quá 3 lần/tháng: <span class="text-danger fw-bold">-200.000đ</span></li>
                    <li>✓ Không trễ (≤3), không vắng (0), không về sớm (≤3): <span
                            class="text-success fw-bold">+300.000đ</span> thưởng chuyên cần</li>
                </ul>
            </div>

        </div>
    </div>
@endsection