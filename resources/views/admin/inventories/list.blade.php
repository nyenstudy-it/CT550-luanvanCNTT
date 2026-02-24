@extends('admin.layouts.layout_admin')

@section('content')
    <div class="container-fluid pt-4 px-4">
        <div class="bg-light rounded p-4">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">Danh sách tồn kho</h6>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>STT</th>
                            <th>Sản phẩm</th>
                            <th>SKU</th>
                            <th>Phân loại</th>
                            <th>Tồn kho</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($inventories as $index => $inv)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>

                                                <td>
                                                    {{ $inv->variant->product->name }}
                                                </td>

                                                <td>
                                                    {{ $inv->variant->sku }}
                                                </td>

                                                <td>
                                                    {{ collect([
                                $inv->variant->color,
                                $inv->variant->size,
                                $inv->variant->volume,
                                $inv->variant->weight,
                            ])->filter()->implode(' - ') ?: '—' }}
                                                </td>


                                                <td>
                                                    <span class="{{ $inv->quantity <= 5 ? 'text-danger fw-bold' : '' }}">
                                                        {{ $inv->quantity }}
                                                    </span>
                                                </td>
                                            </tr>
                        @endforeach

                        @if ($inventories->isEmpty())
                            <tr>
                                <td colspan="5" class="text-center">
                                    Chưa có dữ liệu tồn kho
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

        </div>
    </div>
@endsection