@extends('admin.layouts.layout_admin')

@section('content')
    <div class="container-fluid pt-4 px-4">

        <div class="bg-light rounded p-4">

            <form method="GET" class="row g-3 mb-3">

                <div class="col-md-3">
                    <label>Nhà phân phối</label>
                    <select name="supplier_id" class="form-select">
                        <option value="">-- Tất cả --</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label>Từ ngày</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>

                <div class="col-md-2">
                    <label>Đến ngày</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>

                <div class="col-md-2">
                    <label>Tổng tiền từ</label>
                    <input type="number" name="min_total" class="form-control" value="{{ request('min_total') }}">
                </div>

                <div class="col-md-2">
                    <label>Đến</label>
                    <input type="number" name="max_total" class="form-control" value="{{ request('max_total') }}">
                </div>

                <div class="col-md-1 d-flex align-items-end">
                    <button class="btn btn-primary w-100">Lọc</button>
                </div>

            </form>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">Danh sách phiếu nhập kho</h6>
                <a href="{{ route('admin.imports.create') }}" class="btn btn-primary btn-sm">
                    + Nhập kho
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>STT</th>
                            <th>Nhà phân phối</th>
                            <th>Ngày nhập</th>
                            <th>Tổng tiền</th>
                            <th>Số mặt hàng</th>
                            <th width="120">Chi tiết</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($imports as $index => $import)
                            <tr>
                                <td>{{ $imports->firstItem() + $index }}</td>
                                <td>{{ $import->supplier->name ?? '—' }}</td>
                                <td>{{ $import->import_date }}</td>
                                <td>{{ number_format($import->total_amount) }} đ</td>
                                <td>{{ $import->items->count() }}</td>
                                <td>
                                    <a href="{{ route('admin.imports.show', $import->id) }}" class="btn btn-sm btn-info">
                                        Xem
                                    </a>
                                </td>
                            </tr>
                        @endforeach

                        @if ($imports->isEmpty())
                            <tr>
                                <td colspan="6" class="text-center">
                                    Chưa có phiếu nhập nào
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
                <div class="mt-3">
                    {{ $imports->links() }}
                </div>

            </div>

        </div>
    </div>
@endsection