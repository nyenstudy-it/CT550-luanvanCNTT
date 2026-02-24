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
                                    <th width="50">STT</th>
                                    <th width="80">Ảnh</th>
                                    <th>Tên sản phẩm</th>
                                    <th>Danh mục</th>
                                    <th>Nhà cung cấp</th>
                                    <th>Giá</th>
                                    {{-- <th>OCOP</th> --}}
                                    <th>Trạng thái</th>
                                    <th class="text-center">Số biến thể</th>
                                    <th width="180">Thao tác</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($products as $index => $product)
                                                            <tr>
                                                                <td>{{ $index + 1 }}</td>

                                                                {{-- ẢNH ĐẠI DIỆN SẢN PHẨM --}}
                                                        
                                                                <td class="text-center">
                                                                    @if ($product->image)
                                                                        <img src="{{ asset('storage/' . $product->image) }}" width="60" height="60"
                                                                            class="rounded border" style="object-fit: cover">
                                                                    @else
                                                                        <span class="text-muted">—</span>
                                                                    @endif
                                                                </td>


                                                                <td>{{ $product->name }}</td>

                                                                <td>{{ $product->category->name ?? '—' }}</td>

                                                                <td>{{ $product->supplier->name ?? '—' }}</td>

                                                                {{-- GIÁ (THEO VARIANT) --}}
                                                                <td>
                                                                    @if ($product->variants->count())
                                                                        @php
                                    $minPrice = $product->variants->min('price');
                                    $maxPrice = $product->variants->max('price');
                                                                        @endphp

                                                                        @if ($minPrice == $maxPrice)
                                                                            {{ number_format($minPrice, 0, ',', '.') }} đ
                                                                        @else
                                                                            {{ number_format($minPrice, 0, ',', '.') }}
                                                                            –
                                                                            {{ number_format($maxPrice, 0, ',', '.') }} đ
                                                                        @endif
                                                                    @else
                                                                        <span class="text-muted">Chưa có giá</span>
                                                                    @endif
                                                                </td>

                                                                {{-- OCOP --}}
                                                                {{-- <td>
                                                                    @if ($product->ocop_star)
                                                                    {{ $product->ocop_star }} ⭐
                                                                    <br>
                                                                    <small class="text-muted">({{ $product->ocop_year }})</small>
                                                                    @else
                                                                    —
                                                                    @endif
                                                                </td> --}}

                                                                {{-- TRẠNG THÁI --}}
                                                                <td>
                                                                    @if ($product->status === 'active')
                                                                        <span class="badge bg-success">Đang bán</span>
                                                                    @else
                                                                        <span class="badge bg-secondary">Ngừng bán</span>
                                                                    @endif
                                                                </td>

                                                                {{-- SỐ BIẾN THỂ --}}
                                                                <td class="text-center">
                                                                    {{ $product->variants_count }}
                                                                </td>

                                                                {{-- THAO TÁC --}}
                                                                <td>
                                                                    <a href="{{ route('admin.products.edit', $product->id) }}"
                                                                        class="btn btn-sm btn-warning mb-1">
                                                                        Sửa
                                                                    </a>

                                                                    <a href="{{ route('admin.products.variants.index', $product->id) }}"
                                                                        class="btn btn-sm btn-info mb-1">
                                                                        Biến thể
                                                                    </a>

                                                                    <button class="btn btn-sm btn-secondary mb-1 btn-show-product" data-id="{{ $product->id }}">
                                                                        Xem chi tiết
                                                                    </button>


                                                                    <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST"
                                                                        class="d-inline" onsubmit="return confirm('Vô hiệu hoá sản phẩm này?')">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button class="btn btn-sm btn-danger">
                                                                            Ẩn
                                                                        </button>
                                                                    </form>
                                                                </td>
                                                            </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center text-muted">
                                            Chưa có sản phẩm nào
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>

                        </table>
                    </div>
                </div>
            </div>


            <div class="modal fade" id="productDetailModal" tabindex="-1">
                <div class="modal-dialog modal-xl modal-dialog-scrollable">
                    <div class="modal-content">

                        <div class="modal-header">
                            <h5 class="modal-title">Chi tiết sản phẩm</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body" id="productDetailContent">
                            <div class="text-center text-muted py-5">
                                Đang tải dữ liệu...
                            </div>
                        </div>

                    </div>
                </div>
            </div>
    @push('scripts')
        <script>
            document.addEventListener('click', function (e) {
                if (e.target.classList.contains('btn-show-product')) {

                    let productId = e.target.dataset.id;

                    let modalEl = document.getElementById('productDetailModal');
                    let modal = new bootstrap.Modal(modalEl);

                    modal.show();

                    document.getElementById('productDetailContent').innerHTML =
                        '<div class="text-center py-5">Đang tải...</div>';

                    fetch('/admin/products/' + productId + '/popup')
                        .then(res => res.text())
                        .then(html => {
                            document.getElementById('productDetailContent').innerHTML = html;
                        });
                }
            });
        </script>
    @endpush

@endsection