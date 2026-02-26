@extends('admin.layouts.layout_admin')

@section('content')
    <div class="container-fluid pt-4 px-4">
        <div class="bg-light rounded p-4">
            <div class="text-center mb-4">
                <h4 class="mb-1 text-uppercase fw-bold">Phiếu nhập kho</h4>
                <div class="text-muted">Mã phiếu: #{{ $import->id }}</div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <p class="mb-1">
                        <strong>Nhà cung cấp:</strong>
                        {{ $import->supplier->name }}
                    </p>
                    <p class="mb-1">
                        <strong>Ngày nhập:</strong>
                        {{ \Carbon\Carbon::parse($import->import_date)->format('d/m/Y') }}
                    </p>
                </div>

                <div class="col-md-6 text-md-end">
                    <p class="mb-1">
                        <strong>Tổng tiền:</strong>
                        <span class="fs-5 text-danger fw-bold">
                            {{ number_format($import->total_amount) }} đ
                        </span>
                    </p>
                </div>
            </div>

            <hr>

            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr class="text-center">
                            <th style="width:60px">STT</th>
                            <th>Sản phẩm</th>
                            <th>Biến thể (SKU)</th>
                            <th style="width:100px">Số lượng</th>
                            <th style="width:140px">Giá nhập</th>
                            <th style="width:160px">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($import->items as $index => $item)
                                            <tr>
                                                <td class="text-center">{{ $index + 1 }}</td>

                                                <td>
                                                    {{ $item->variant?->product?->name ?? '—' }}
                                                </td>

                                                <td>
                                                    <div class="fw-semibold">
                                                        {{ $item->variant?->sku ?? '—' }}
                                                    </div>
                                                    <small class="text-muted">
                                                        {{ collect([
        $item->variant?->color,
        $item->variant?->size,
        $item->variant?->volume,
        $item->variant?->weight,
    ])->filter()->implode(' - ') }}
                                                    </small>
                                                </td>

                                                <td class="text-center">
                                                    {{ $item->quantity }}
                                                </td>

                                                <td class="text-end">
                                                    {{ number_format($item->unit_price) }} đ
                                                </td>

                                                <td class="text-end fw-semibold">
                                                    {{ number_format($item->quantity * $item->unit_price) }} đ
                                                </td>
                                            </tr>
                        @endforeach
                    </tbody>

                    <tfoot>
                        <tr>
                            <td colspan="5" class="text-end fw-bold">
                                Tổng cộng
                            </td>
                            <td class="text-end fw-bold text-danger">
                                {{ number_format($import->total_amount) }} đ
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="row mt-5 text-center">
                <div class="col-md-4">
                    <p class="fw-bold mb-5">Người lập phiếu</p>
                    <p class="text-muted">(Ký, ghi rõ họ tên)</p>
                </div>
                <div class="col-md-4">
                    <p class="fw-bold mb-5">Thủ kho</p>
                    <p class="text-muted">(Ký, ghi rõ họ tên)</p>
                </div>
                <div class="col-md-4">
                    <p class="fw-bold mb-5">Nhà cung cấp</p>
                    <p class="text-muted">(Ký, ghi rõ họ tên)</p>
                </div>
            </div>

            <div class="mt-4">
                <a href="{{ route('admin.imports.list') }}" class="btn btn-secondary btn-sm">
                    Quay lại danh sách
                </a>
                <a href="{{ route('admin.imports.print', $import->id) }}" class="btn btn-success btn-sm" target="_blank">
                    ⬇ Tải phiếu nhập (PDF)
                </a>

            </div>

        </div>
    </div>
@endsection