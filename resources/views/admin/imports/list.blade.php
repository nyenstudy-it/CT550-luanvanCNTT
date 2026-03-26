@extends('admin.layouts.layout_admin')

@section('content')
    <div class="container-fluid pt-4 px-4">

        <div class="bg-light rounded p-4">

            <div class="row g-3 mb-4">
                <div class="col-12 col-sm-4">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Tổng phiếu nhập</small>
                        <h4 class="mb-0">{{ $imports->total() }}</h4>
                    </div>
                </div>
                <div class="col-12 col-sm-4">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Đang hiển thị</small>
                        <h4 class="mb-0 text-primary">{{ $imports->count() }}</h4>
                    </div>
                </div>
                <div class="col-12 col-sm-4">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Trang hiện tại</small>
                        <h4 class="mb-0 text-success">{{ $imports->currentPage() }}/{{ $imports->lastPage() }}</h4>
                    </div>
                </div>
            </div>

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
                <div>
                    <h6 class="mb-1">Danh sách phiếu nhập kho</h6>
                    <small class="text-muted">Ưu tiên đủ thông tin để tìm phiếu, đối chiếu người nhập và mở lại phiếu khi
                        cần in/xem nhanh.</small>
                </div>
                <a href="{{ route('admin.imports.create') }}" class="btn btn-primary btn-sm">
                    + Nhập kho
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>STT</th>
                            <th>Mã phiếu</th>
                            <th>Nhà phân phối</th>
                            <th>Người nhập</th>
                            <th>Ngày nhập</th>
                            <th>Số dòng</th>
                            <th>Tổng SL nhập</th>
                            <th>Tổng tiền</th>
                            <th width="180">Thao tác</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($imports as $index => $import)
                            <tr>
                                <td>{{ $imports->firstItem() + $index }}</td>
                                <td>
                                    <span class="fw-semibold">PN-{{ str_pad($import->id, 5, '0', STR_PAD_LEFT) }}</span>
                                </td>
                                <td>{{ $import->supplier->name ?? '—' }}</td>
                                <td>{{ $import->staff->name ?? '—' }}</td>
                                <td>{{ \Carbon\Carbon::parse($import->import_date)->format('d/m/Y') }}</td>
                                <td>{{ number_format($import->items_count ?? 0) }}</td>
                                <td>{{ number_format($import->total_quantity ?? 0) }}</td>
                                <td>{{ number_format($import->total_amount) }} đ</td>
                                <td>
                                    <div class="d-flex flex-wrap gap-2">
                                        <a href="{{ route('admin.imports.show', $import->id) }}" class="btn btn-sm btn-info">
                                            Xem
                                        </a>
                                        <a href="{{ route('admin.imports.print', $import->id) }}"
                                            class="btn btn-sm btn-outline-secondary">
                                            In phiếu
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach

                        @if ($imports->isEmpty())
                            <tr>
                                <td colspan="9" class="text-center">
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