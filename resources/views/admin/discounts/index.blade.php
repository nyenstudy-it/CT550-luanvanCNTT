@extends('admin.layouts.layout_admin')

@section('content')
    <div class="container-fluid pt-4 px-2">
        <div class="bg-light rounded p-4">

            <style>
                .admin-action-btn {
                    min-width: 78px;
                    font-weight: 600;
                }

                .admin-badge {
                    font-size: 11px;
                    font-weight: 600;
                    padding: 6px 10px;
                    border-radius: 999px;
                }
            </style>

            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div>
                    <h5 class="mb-1">Danh sách mã giảm giá</h5>
                    <small class="text-muted">Quản lý mã toàn shop và mã theo từng sản phẩm.</small>
                </div>
                <a href="{{ route('admin.discounts.create') }}" class="btn btn-success btn-sm">+ Thêm mã giảm giá</a>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Tổng mã</small>
                        <h4 class="mb-0">{{ $summary['total'] }}</h4>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Đang hiệu lực</small>
                        <h4 class="mb-0 text-success">{{ $summary['active'] }}</h4>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Theo sản phẩm</small>
                        <h4 class="mb-0 text-primary">{{ $summary['product_scoped'] }}</h4>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Toàn shop</small>
                        <h4 class="mb-0 text-dark">{{ $summary['global'] }}</h4>
                    </div>
                </div>
            </div>

            <form method="GET" action="{{ route('admin.discounts.index') }}"
                class="row g-3 mb-4 border rounded bg-white p-3">
                <div class="col-md-3">
                    <label class="form-label">Mã giảm giá</label>
                    <input type="text" name="code" value="{{ request('code') }}" class="form-control" placeholder="Nhập mã">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Loại</label>
                    <select name="type" class="form-select">
                        <option value="">-- Tất cả --</option>
                        <option value="percent" {{ request('type') == 'percent' ? 'selected' : '' }}>Phần trăm (%)</option>
                        <option value="fixed" {{ request('type') == 'fixed' ? 'selected' : '' }}>Tiền cố định</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Phạm vi</label>
                    <select name="scope" class="form-select">
                        <option value="">-- Tất cả --</option>
                        <option value="all" {{ request('scope') == 'all' ? 'selected' : '' }}>Toàn shop</option>
                        <option value="product" {{ request('scope') == 'product' ? 'selected' : '' }}>Theo sản phẩm</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="">-- Tất cả --</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Đang áp dụng</option>
                        <option value="upcoming" {{ request('status') == 'upcoming' ? 'selected' : '' }}>Chưa bắt đầu</option>
                        <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Hết hạn</option>
                    </select>
                </div>

                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">Lọc</button>
                    <a href="{{ route('admin.discounts.index') }}" class="btn btn-outline-secondary">Đặt lại</a>
                </div>
            </form>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">Bảng mã giảm giá</h6>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="50">STT</th>
                            <th width="150">Mã giảm giá</th>
                            <th width="100">Loại</th>
                            <th width="100">Giá trị</th>
                            <th width="220">Phạm vi áp dụng</th>
                            <th width="100">Đã sử dụng / Giới hạn</th>
                            <th width="100">Trạng thái</th>
                            <th width="150">Ngày tạo</th>
                            <th width="150">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($discounts as $index => $discount)
                            <tr>
                                <td>{{ $discounts->firstItem() + $index }}</td>
                                <td>{{ $discount->code }}</td>
                                <td>
                                    @if($discount->type == 'percent')
                                        Phần trăm (%)
                                    @else
                                        Tiền cố định
                                    @endif
                                </td>

                                <td>
                                    @if($discount->type == 'percent')
                                        {{ $discount->value }} %
                                    @else
                                        {{ number_format($discount->value, 0, ',', '.') }} đ
                                    @endif
                                </td>
                                <td>
                                    @if($discount->products->isEmpty())
                                        <span class="badge bg-dark admin-badge">Toàn shop</span>
                                    @else
                                        <span class="badge bg-primary admin-badge">{{ $discount->products->count() }} sản
                                            phẩm</span>
                                        <div class="small text-muted mt-1">
                                            {{ $discount->products->pluck('name')->take(2)->implode(', ') }}
                                            @if($discount->products->count() > 2)
                                                ...
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    {{ $discount->used_count }} /
                                    {{ $discount->usage_limit ?? '∞' }}
                                </td>
                                <td>
                                    @php
                                        $now = now();
                                        if ($discount->start_at && $now->lt($discount->start_at)) {
                                            $status = 'Chưa bắt đầu';
                                            $badge = 'secondary';
                                        } elseif ($discount->end_at && $now->gt($discount->end_at)) {
                                            $status = 'Hết hạn';
                                            $badge = 'danger';
                                        } else {
                                            $status = 'Đang áp dụng';
                                            $badge = 'success';
                                        }
                                    @endphp
                                    <span class="badge bg-{{ $badge }} admin-badge">{{ $status }}</span>
                                </td>
                                <td>{{ $discount->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('admin.discounts.edit', $discount->id) }}"
                                        class="btn btn-sm btn-outline-primary admin-action-btn mb-1">Sửa</a>

                                    <!-- Nút Chi tiết -->
                                    <button type="button" class="btn btn-sm btn-outline-info admin-action-btn mb-1"
                                        data-bs-toggle="modal" data-bs-target="#discountDetailModal{{ $discount->id }}">
                                        Chi tiết
                                    </button>

                                    <form action="{{ route('admin.discounts.destroy', $discount->id) }}" method="POST"
                                        style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger admin-action-btn mb-1"
                                            onclick="return confirm('Xác nhận xóa?')">Xóa</button>
                                    </form>

                                    <!-- Modal Chi tiết -->
                                    <div class="modal fade" id="discountDetailModal{{ $discount->id }}" tabindex="-1"
                                        aria-labelledby="discountDetailModalLabel{{ $discount->id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="discountDetailModalLabel{{ $discount->id }}">
                                                        Chi tiết mã giảm giá
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Đóng"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p><strong>Mã giảm giá:</strong> {{ $discount->code }}</p>
                                                    <p><strong>Loại:</strong>
                                                        @if($discount->type == 'percent')
                                                            Phần trăm (%)
                                                        @else
                                                            Tiền cố định
                                                        @endif
                                                    </p>

                                                    <p><strong>Giá trị:</strong>
                                                        @if($discount->type == 'percent')
                                                            {{ $discount->value }} %
                                                        @else
                                                            {{ number_format($discount->value, 0, ',', '.') }} đ
                                                        @endif
                                                    </p>
                                                    <p><strong>Số lần sử dụng tối đa:</strong>
                                                        {{ $discount->usage_limit ?? '∞' }}</p>
                                                    <p><strong>Số lần đã sử dụng:</strong> {{ $discount->used_count }}</p>
                                                    <p><strong>Đơn tối thiểu áp dụng:</strong>
                                                        {{ number_format($discount->min_order_value, 0, ',', '.') }} đ</p>
                                                    <p><strong>Phạm vi:</strong>
                                                        @if($discount->products->isEmpty())
                                                            Toàn shop
                                                        @else
                                                            Theo {{ $discount->products->count() }} sản phẩm
                                                        @endif
                                                    </p>
                                                    @if($discount->products->isNotEmpty())
                                                        <p><strong>Danh sách sản phẩm:</strong></p>
                                                        <ul class="mb-2">
                                                            @foreach($discount->products as $product)
                                                                <li>{{ $product->name }}</li>
                                                            @endforeach
                                                        </ul>
                                                    @endif
                                                    <p><strong>Ngày bắt đầu:</strong>
                                                        {{ $discount->start_at?->format('d/m/Y H:i') ?? '-' }}</p>
                                                    <p><strong>Ngày kết thúc:</strong>
                                                        {{ $discount->end_at?->format('d/m/Y H:i') ?? '-' }}</p>
                                                    <p><strong>Ngày tạo:</strong>
                                                        {{ $discount->created_at->format('d/m/Y H:i') }}</p>
                                                    <p><strong>Ngày cập nhật:</strong>
                                                        {{ $discount->updated_at->format('d/m/Y H:i') }}</p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">Đóng</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted">Chưa có mã giảm giá</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{-- PAGINATION --}}
                {{ $discounts->appends(request()->query())->links() }}
            </div>

        </div>
    </div>
@endsection