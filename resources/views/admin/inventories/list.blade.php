@extends('admin.layouts.layout_admin')

@section('content')
    <div class="container-fluid pt-4 px-4">
        <div class="bg-light rounded p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <div>
                    <h5 class="mb-1">Danh sách tồn kho</h5>
                    <small class="text-muted">
                        Theo dõi tồn kho, hạn dùng và tuổi tồn để quản lý chủ động hơn.
                    </small>
                </div>
                <span class="badge bg-primary">Tổng biến thể: {{ $summary['total_variants'] }}</span>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-12 col-sm-6 col-xl-2">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Hết hàng</small>
                        <h4 class="mb-0 text-danger">{{ $summary['out_of_stock'] }}</h4>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-2">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Sắp hết hàng</small>
                        <h4 class="mb-0 text-warning">{{ $summary['low_stock'] }}</h4>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-2">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Đã hết hạn</small>
                        <h4 class="mb-0 text-danger">{{ $summary['expired'] }}</h4>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-2">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Sắp hết hạn</small>
                        <h4 class="mb-0 text-warning">{{ $summary['expiring_soon'] }}</h4>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-2">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Tồn kho lâu</small>
                        <h4 class="mb-0 text-secondary">{{ $summary['stale_stock'] }}</h4>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-2">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Ổn định</small>
                        <h4 class="mb-0 text-success">{{ $summary['normal_stock'] }}</h4>
                    </div>
                </div>
            </div>

            <div class="border rounded bg-white p-3 mb-4">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                    <h6 class="mb-0">
                        <i class="fa fa-bell text-warning me-2"></i>
                        Cảnh báo đã đẩy lên chuông cho Admin/Staff
                    </h6>
                    <small class="text-muted">Tự động cập nhật theo dữ liệu tồn kho hiện tại</small>
                </div>
                @if ($alertPreview->isNotEmpty())
                    <div class="row g-2">
                        @foreach ($alertPreview as $alert)
                            <div class="col-12 col-lg-6">
                                <div class="border rounded p-2 h-100">
                                    <div class="fw-semibold text-dark small">
                                        {{ $alert->variant->product->name ?? 'Sản phẩm' }}
                                        <span class="text-muted">({{ $alert->variant->sku ?? 'N/A' }})</span>
                                    </div>
                                    <div class="small text-muted mt-1">
                                        @if ($alert->is_out_of_stock)
                                            <span class="badge bg-danger me-1">Hết hàng</span>
                                        @endif
                                        @if ($alert->is_low_stock)
                                            <span class="badge bg-warning text-dark me-1">Sắp hết hàng</span>
                                        @endif
                                        @if ($alert->is_expired)
                                            <span class="badge bg-danger me-1">Đã hết hạn</span>
                                        @endif
                                        @if ($alert->is_expiring_soon)
                                            <span class="badge bg-warning text-dark me-1">Sắp hết hạn</span>
                                        @endif
                                        @if ($alert->is_stale)
                                            <span class="badge bg-secondary me-1">Tồn kho lâu</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-success small">Hiện chưa có cảnh báo tồn kho cần đẩy lên chuông.</div>
                @endif
            </div>

            <form method="GET" action="{{ route('admin.inventories.list') }}" class="border rounded bg-white p-3 mb-4">
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-lg-4">
                        <label class="form-label mb-1">Tìm kiếm</label>
                        <input type="text" name="keyword" class="form-control" value="{{ request('keyword') }}"
                            placeholder="Tên sản phẩm, SKU, màu, size...">
                    </div>
                    <div class="col-12 col-sm-6 col-lg-2">
                        <label class="form-label mb-1">Trạng thái tồn</label>
                        <select name="stock_status" class="form-select">
                            <option value="all" {{ request('stock_status', 'all') === 'all' ? 'selected' : '' }}>Tất cả
                            </option>
                            <option value="out" {{ request('stock_status') === 'out' ? 'selected' : '' }}>Hết hàng</option>
                            <option value="low" {{ request('stock_status') === 'low' ? 'selected' : '' }}>Sắp hết hàng
                            </option>
                            <option value="ok" {{ request('stock_status') === 'ok' ? 'selected' : '' }}>Ổn định</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-2">
                        <label class="form-label mb-1">Hạn sử dụng</label>
                        <select name="expiry_status" class="form-select">
                            <option value="all" {{ request('expiry_status', 'all') === 'all' ? 'selected' : '' }}>Tất cả
                            </option>
                            <option value="expired" {{ request('expiry_status') === 'expired' ? 'selected' : '' }}>Đã hết hạn
                            </option>
                            <option value="expiring" {{ request('expiry_status') === 'expiring' ? 'selected' : '' }}>Sắp hết
                                hạn</option>
                            <option value="safe" {{ request('expiry_status') === 'safe' ? 'selected' : '' }}>An toàn</option>
                            <option value="no_expiry" {{ request('expiry_status') === 'no_expiry' ? 'selected' : '' }}>Không
                                có hạn</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-2">
                        <label class="form-label mb-1">Tồn kho lâu</label>
                        <select name="stale_status" class="form-select">
                            <option value="all" {{ request('stale_status', 'all') === 'all' ? 'selected' : '' }}>Tất cả
                            </option>
                            <option value="stale" {{ request('stale_status') === 'stale' ? 'selected' : '' }}>Tồn kho lâu
                            </option>
                            <option value="fresh" {{ request('stale_status') === 'fresh' ? 'selected' : '' }}>Bán nhanh
                            </option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-2">
                        <label class="form-label mb-1">Sắp xếp</label>
                        <select name="sort_by" class="form-select">
                            <option value="risk_desc" {{ request('sort_by', 'risk_desc') === 'risk_desc' ? 'selected' : '' }}>
                                Ưu tiên rủi ro</option>
                            <option value="quantity_asc" {{ request('sort_by') === 'quantity_asc' ? 'selected' : '' }}>Tồn kho
                                tăng dần</option>
                            <option value="quantity_desc" {{ request('sort_by') === 'quantity_desc' ? 'selected' : '' }}>Tồn
                                kho giảm dần</option>
                            <option value="expiry_asc" {{ request('sort_by') === 'expiry_asc' ? 'selected' : '' }}>Hạn gần
                                nhất</option>
                            <option value="stock_age_desc" {{ request('sort_by') === 'stock_age_desc' ? 'selected' : '' }}>Tồn
                                kho lâu nhất</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-4 col-lg-2">
                        <label class="form-label mb-1">Ngưỡng sắp hết</label>
                        <input type="number" min="1" name="low_stock_threshold" class="form-control"
                            value="{{ request('low_stock_threshold', $lowStockThreshold) }}">
                    </div>
                    <div class="col-12 col-md-4 col-lg-2">
                        <label class="form-label mb-1">Sắp hết hạn (ngày)</label>
                        <input type="number" min="1" name="expiring_days" class="form-control"
                            value="{{ request('expiring_days', $expiringInDays) }}">
                    </div>
                    <div class="col-12 col-md-4 col-lg-2">
                        <label class="form-label mb-1">Tồn kho lâu (ngày)</label>
                        <input type="number" min="1" name="stale_days" class="form-control"
                            value="{{ request('stale_days', $staleDays) }}">
                    </div>

                    <div class="col-12 col-lg-6 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-filter me-1"></i> Lọc dữ liệu
                        </button>
                        <a href="{{ route('admin.inventories.list') }}" class="btn btn-outline-secondary">
                            Đặt lại
                        </a>
                    </div>
                </div>
            </form>

            <div class="alert alert-info py-2 px-3 mb-3" role="alert">
                <strong>Giải thích nhanh:</strong>
                Ngày tồn kho là số ngày tính từ lần nhập lô cũ nhất còn hàng.
                "Bán nhanh" nghĩa là ngày tồn kho nhỏ hơn ngưỡng "Tồn kho lâu" bạn đã chọn.
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
                            <th>Hạn sử dụng</th>
                            <th>Ngày tồn kho</th>
                            <th>Giá bán</th>
                            <th>Giá nhập theo lô</th>
                            <th>Cảnh báo</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($inventories as $index => $inv)
                                            <tr>
                                                <td>{{ $inventories->firstItem() + $index }}</td>

                                                <td>
                                                    <div class="fw-semibold">{{ $inv->variant->product->name ?? 'Sản phẩm không xác định' }}
                                                    </div>
                                                </td>

                                                <td>{{ $inv->variant->sku ?? 'N/A' }}</td>

                                                <td>
                                                    {{ collect([
                                $inv->variant->color,
                                $inv->variant->size,
                                $inv->variant->volume,
                                $inv->variant->weight,
                            ])->filter()->implode(' - ') ?: '—' }}
                                                </td>

                                                <td>
                                                    <div
                                                        class="fw-semibold {{ $inv->is_out_of_stock || $inv->is_low_stock ? 'text-danger' : 'text-dark' }}">
                                                        {{ $inv->quantity }}
                                                    </div>
                                                    <small class="text-muted">Theo lô còn lại:
                                                        {{ $inv->total_remaining_batch_quantity }}</small>
                                                </td>

                                                <td>
                                                    @if ($inv->expiry_date)
                                                        <div>{{ $inv->expiry_date->format('d/m/Y') }}</div>
                                                        @if ($inv->is_expired)
                                                            <small class="text-danger">Đã hết hạn</small>
                                                        @elseif ($inv->is_expiring_soon)
                                                            <small class="text-warning">Còn {{ $inv->days_to_expire }} ngày</small>
                                                        @else
                                                            <small class="text-success">An toàn</small>
                                                        @endif
                                                    @else
                                                        <span class="text-muted">Không có hạn</span>
                                                    @endif
                                                </td>

                                                <td>
                                                    @if (!is_null($inv->stock_age_days))
                                                        <div>{{ $inv->stock_age_days }} ngày</div>
                                                        <small class="{{ $inv->is_stale ? 'text-danger' : 'text-muted' }}">
                                                            {{ $inv->is_stale ? 'Tồn kho lâu' : 'Bán nhanh' }}
                                                        </small>
                                                    @else
                                                        <span class="text-muted">Chưa có dữ liệu lô</span>
                                                    @endif
                                                </td>

                                                <td>
                                                    <span class="fw-semibold">{{ number_format((float) ($inv->variant->price ?? 0)) }} đ</span>
                                                </td>

                                                <td>
                                                    <button type="button" class="btn btn-sm btn-outline-primary btn-show-batch-prices"
                                                        data-url="{{ route('admin.inventories.batches', $inv->product_variant_id) }}"
                                                        data-bs-toggle="modal" data-bs-target="#inventoryBatchModal">
                                                        Xem lô & giá
                                                    </button>
                                                </td>

                                                <td>
                                                    @if ($inv->is_out_of_stock)
                                                        <span class="badge bg-danger me-1 mb-1">Hết hàng</span>
                                                    @endif
                                                    @if ($inv->is_low_stock)
                                                        <span class="badge bg-warning text-dark me-1 mb-1">Sắp hết hàng</span>
                                                    @endif
                                                    @if ($inv->is_expired)
                                                        <span class="badge bg-danger me-1 mb-1">Đã hết hạn</span>
                                                    @endif
                                                    @if ($inv->is_expiring_soon)
                                                        <span class="badge bg-warning text-dark me-1 mb-1">Sắp hết hạn</span>
                                                    @endif
                                                    @if ($inv->is_stale)
                                                        <span class="badge bg-secondary me-1 mb-1">Tồn kho lâu</span>
                                                    @endif
                                                    @if (empty($inv->alert_tags))
                                                        <span class="badge bg-success">Ổn định</span>
                                                    @endif
                                                </td>
                                            </tr>
                        @endforeach

                        @if ($inventories->isEmpty())
                            <tr>
                                <td colspan="10" class="text-center">
                                    Không có dữ liệu phù hợp với bộ lọc hiện tại
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $inventories->links() }}
            </div>

        </div>
    </div>

    <div class="modal fade" id="inventoryBatchModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Chi tiết lô nhập & giá</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="inventoryBatchModalBody">
                    <div class="text-center py-4 text-muted">Đang tải dữ liệu...</div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('click', function (event) {
            const button = event.target.closest('.btn-show-batch-prices');

            if (!button) {
                return;
            }

            const url = button.dataset.url;
            const modalBody = document.getElementById('inventoryBatchModalBody');

            modalBody.innerHTML = '<div class="text-center py-4 text-muted">Đang tải dữ liệu...</div>';

            fetch(url)
                .then((response) => {
                    if (!response.ok) {
                        throw new Error('Không thể tải dữ liệu lô nhập.');
                    }
                    return response.text();
                })
                .then((html) => {
                    modalBody.innerHTML = html;
                })
                .catch((error) => {
                    modalBody.innerHTML = '<div class="alert alert-danger mb-0">' + error.message + '</div>';
                });
        });
    </script>
@endpush