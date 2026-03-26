@extends('admin.layouts.layout_admin')

@section('content')
    <div class="container-fluid pt-4 px-4">

        <div class="bg-light rounded p-4">

            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div>
                    <h5 class="mb-1">Bảng lương nhân viên</h5>
                    <small class="text-muted">Theo dõi giờ làm và tổng lương theo tháng.</small>
                </div>
            </div>

            @php
                $summaryTotal = $salaries->count();
                $summaryHours = (float) $salaries->sum('total_hours');
                $summarySalary = (float) $salaries->sum('total_salary');
            @endphp

            <div class="row g-3 mb-4">
                <div class="col-12 col-sm-4">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Số bản ghi lương</small>
                        <h4 class="mb-0">{{ $summaryTotal }}</h4>
                    </div>
                </div>
                <div class="col-12 col-sm-4">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Tổng giờ làm</small>
                        <h4 class="mb-0 text-primary">{{ number_format($summaryHours, 2) }} giờ</h4>
                    </div>
                </div>
                <div class="col-12 col-sm-4">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Tổng lương</small>
                        <h4 class="mb-0 text-success">{{ number_format($summarySalary) }} đ</h4>
                    </div>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form method="GET" class="row g-3 mb-4 border rounded bg-white p-3">
                <div class="col-md-3">
                    <label class="form-label">Tháng</label>
                    <select name="month" class="form-select">
                        <option value="">-- Tháng --</option>
                        @for($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" {{ request('month') == $i ? 'selected' : '' }}>
                                Tháng {{ $i }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Năm</label>
                    <select name="year" class="form-select">
                        <option value="">-- Năm --</option>
                        @for($y = date('Y'); $y >= 2020; $y--)
                            <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>
                                {{ $y }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button class="btn btn-primary">Lọc</button>
                    <a href="{{ route('admin.salaries.index') }}" class="btn btn-secondary">Đặt lại</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Nhân viên</th>
                            <th>Tháng</th>
                            <th>Tổng giờ</th>
                            <th>Lương</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($salaries as $salary)
                            <tr>
                                <td>{{ $salary->staff->name ?? 'N/A' }}</td>
                                <td>{{ $salary->month }}/{{ $salary->year }}</td>
                                <td>{{ $salary->total_hours }} giờ</td>
                                <td class="text-success fw-bold">
                                    {{ number_format($salary->total_salary) }} VNĐ
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">
                                    Chưa có dữ liệu lương
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
@endsection