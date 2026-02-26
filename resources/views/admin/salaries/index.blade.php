@extends('admin.layouts.layout_admin')

@section('content')
    <div class="container-fluid pt-4 px-4">

        <div class="bg-light rounded p-4">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4>BẢNG LƯƠNG NHÂN VIÊN</h4>
            </div>
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            <form method="GET" class="row mb-4">
                <div class="col-md-3">
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
                    <select name="year" class="form-select">
                        <option value="">-- Năm --</option>
                        @for($y = date('Y'); $y >= 2020; $y--)
                            <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>
                                {{ $y }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div class="col-md-3">
                    <button class="btn btn-primary">
                        Lọc
                    </button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
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