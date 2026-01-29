@extends('admin.layouts.layout_admin')

@section('content')
    <div class="container-fluid pt-4 px-4">
        <div class="bg-light rounded p-4">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">Danh sách sản phẩm</h6>
                <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm">
                    + Thêm sản phẩm
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>STT</th>
                            <th>Ảnh</th>
                            <th>Tên sản phẩm</th>
                            <th>Danh mục</th>
                            <th>Nhà cung cấp</th>
                            <th>Giá</th>
                            <th>OCOP</th>
                            <th>Trạng thái</th>
                            <th width="170">Thao tác</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($products as $index => $product)
                            <tr>
                                <td>{{ $index + 1 }}</td>

                                <td>
                                    @if ($product->image)
                                        <img src="{{ asset('storage/' . $product->image) }}" width="60" height="60"
                                            class="rounded object-fit-cover">
                                    @else
                                        —
                                    @endif
                                </td>

                                <td>{{ $product->name }}</td>

                                <td>{{ $product->category->name ?? '—' }}</td>

                                <td>{{ $product->supplier->name ?? '—' }}</td>

                                <td>{{ number_format($product->price, 0, ',', '.') }} đ</td>

                                <td>
                                    @if ($product->ocop_star)
                                        {{ $product->ocop_star }} ⭐
                                        ({{ $product->ocop_year }})
                                    @else
                                        —
                                    @endif
                                </td>

                                <td>
                                    @if ($product->status === 'active')
                                        <span class="badge bg-success">Đang bán</span>
                                    @else
                                        <span class="badge bg-secondary">Ngừng bán</span>
                                    @endif
                                </td>

                                <td>
                                    <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-sm btn-warning">
                                        Sửa
                                    </a>

                                    <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST"
                                        class="d-inline" onsubmit="return confirm('Vô hiệu hóa sản phẩm này?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger">
                                            Ẩn
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach

                        @if ($products->isEmpty())
                            <tr>
                                <td colspan="9" class="text-center">
                                    Chưa có sản phẩm nào
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

        </div>
    </div>
@endsection