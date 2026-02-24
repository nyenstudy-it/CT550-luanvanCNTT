@extends('admin.layouts.layout_admin')

@section('content')
    <div class="container-fluid pt-4 px-4">
        <div class="bg-light rounded p-4">

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
                                <td>{{ $index + 1 }}</td>
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
            </div>

        </div>
    </div>
@endsection