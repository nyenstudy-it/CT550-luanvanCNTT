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
                            <option value="promo" {{ request('expiry_status') === 'promo' ? 'selected' : '' }}>Gợi ý KM (<=
                                6 tháng)</option>
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
                                Ưu tiên gợi ý KM 6 tháng</option>
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

            <style>
                .inventory-table {
                    min-width: 1120px;
                    margin-bottom: 0;
                }

                .inventory-table th,
                .inventory-table td {
                    font-size: 14px;
                    vertical-align: top;
                }

                .inventory-table thead th {
                    white-space: nowrap;
                    font-size: 12px;
                    text-transform: uppercase;
                    letter-spacing: 0.03em;
                }

                .inventory-product-name {
                    font-weight: 700;
                    color: #1f2937;
                    line-height: 1.45;
                }

                .inventory-subtext {
                    font-size: 12px;
                    color: #6b7280;
                }

                .inventory-variant-meta {
                    display: flex;
                    flex-wrap: wrap;
                    gap: 6px;
                    margin-top: 8px;
                }

                .inventory-chip {
                    display: inline-flex;
                    align-items: center;
                    border-radius: 999px;
                    padding: 4px 10px;
                    font-size: 12px;
                    font-weight: 600;
                    background: #f3f4f6;
                    color: #374151;
                }

                .inventory-stock-value {
                    font-size: 24px;
                    font-weight: 700;
                    line-height: 1;
                }

                .inventory-batch-bar {
                    display: flex;
                    height: 8px;
                    border-radius: 999px;
                    overflow: hidden;
                    background: #e5e7eb;
                    margin: 10px 0 8px;
                }

                .inventory-batch-segment.expired {
                    background: #dc3545;
                }

                .inventory-batch-segment.expiring {
                    background: #ffc107;
                }

                .inventory-batch-segment.promotion {
                    background: #0d6efd;
                }

                .inventory-batch-segment.safe {
                    background: #198754;
                }

                .inventory-batch-segment.no-expiry {
                    background: #6c757d;
                }

                .inventory-summary-card {
                    border: 1px solid #e5e7eb;
                    border-radius: 12px;
                    padding: 10px 12px;
                    background: #fff;
                }

                .inventory-summary-card.is-expired {
                    background: #fff5f5;
                    border-color: #f1b0b7;
                }

                .inventory-summary-card.is-expiring {
                    background: #fffaf0;
                    border-color: #f7d58a;
                }

                .inventory-summary-card.is-safe {
                    background: #f3fff7;
                    border-color: #badbcc;
                }

                .inventory-actions {
                    min-width: 150px;
                }
            </style>

            <div class="table-responsive border rounded bg-white">
                <table class="table table-bordered table-hover align-middle inventory-table">
                    <colgroup>
                        <col style="width: 5%">
                        <col style="width: 22%">
                        <col style="width: 18%">
                        <col style="width: 20%">
                        <col style="width: 17%">
                        <col style="width: 10%">
                        <col style="width: 8%">
                    </colgroup>
                    <thead class="table-light">
                        <tr>
                            <th>STT</th>
                            <th>Sản phẩm</th>
                            <th>Biến thể</th>
                            <th>Tồn kho</th>
                            <th>Tóm tắt hạn dùng</th>
                            <th>Cảnh báo</th>
                            <th>Xử lý</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($inventories as $index => $inv)
                            @php
                                $variantMeta = collect([
                                    $inv->variant->color,
                                    $inv->variant->size ? 'Size ' . $inv->variant->size : null,
                                    $inv->variant->volume,
                                    $inv->variant->weight,
                                ])->filter()->values();

                                $totalBatchQuantity = max(1, (int) $inv->total_remaining_batch_quantity);
                                $breakdown = $inv->batch_status_breakdown ?? [];
                                $deadlineCardClass = $inv->is_expired
                                    ? 'is-expired'
                                    : ($inv->is_expiring_soon ? 'is-expiring' : ($inv->expiry_date ? 'is-safe' : ''));
                            @endphp
                            <tr>
                                <td>{{ $inventories->firstItem() + $index }}</td>

                                <td>
                                    <div class="inventory-product-name">{{ $inv->variant->product->name ?? 'Sản phẩm không xác định' }}</div>
                                    <div class="inventory-subtext mt-1">SKU chính: {{ $inv->variant->sku ?? 'N/A' }}</div>
                                </td>

                                <td>
                                    <div class="fw-semibold">{{ $inv->variant->sku ?? 'N/A' }}</div>
                                    @if ($variantMeta->isNotEmpty())
                                        <div class="inventory-variant-meta">
                                            @foreach ($variantMeta as $meta)
                                                <span class="inventory-chip">{{ $meta }}</span>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="inventory-subtext mt-2">Chưa có thuộc tính phân loại.</div>
                                    @endif
                                </td>

                                <td>
                                    <div class="inventory-stock-value {{ $inv->is_out_of_stock || $inv->is_low_stock ? 'text-danger' : 'text-dark' }}">
                                        {{ number_format($inv->quantity) }}
                                    </div>
                                    <div class="inventory-subtext mt-1">
                                        {{ $inv->active_batch_count }} lô còn hàng | Theo lô: {{ number_format($inv->total_remaining_batch_quantity) }}
                                    </div>

                                    <div class="inventory-batch-bar" title="Phân bổ tồn theo trạng thái hạn dùng">
                                        @if (($breakdown['expired'] ?? 0) > 0)
                                            <span class="inventory-batch-segment expired" style="width: {{ (($breakdown['expired'] ?? 0) / $totalBatchQuantity) * 100 }}%"></span>
                                        @endif
                                        @if (($breakdown['expiring'] ?? 0) > 0)
                                            <span class="inventory-batch-segment expiring" style="width: {{ (($breakdown['expiring'] ?? 0) / $totalBatchQuantity) * 100 }}%"></span>
                                        @endif
                                        @if (($breakdown['promotion'] ?? 0) > 0)
                                            <span class="inventory-batch-segment promotion" style="width: {{ (($breakdown['promotion'] ?? 0) / $totalBatchQuantity) * 100 }}%"></span>
                                        @endif
                                        @if (($breakdown['safe'] ?? 0) > 0)
                                            <span class="inventory-batch-segment safe" style="width: {{ (($breakdown['safe'] ?? 0) / $totalBatchQuantity) * 100 }}%"></span>
                                        @endif
                                        @if (($breakdown['no_expiry'] ?? 0) > 0)
                                            <span class="inventory-batch-segment no-expiry" style="width: {{ (($breakdown['no_expiry'] ?? 0) / $totalBatchQuantity) * 100 }}%"></span>
                                        @endif
                                    </div>

                                    <div class="inventory-subtext">
                                        @if (($breakdown['expired'] ?? 0) > 0)
                                            Hết hạn: {{ number_format($breakdown['expired']) }}
                                        @endif
                                        @if (($breakdown['expiring'] ?? 0) > 0)
                                            @if (($breakdown['expired'] ?? 0) > 0) • @endif
                                            Sắp hết hạn: {{ number_format($breakdown['expiring']) }}
                                        @endif
                                        @if (($breakdown['promotion'] ?? 0) > 0)
                                            @if ((($breakdown['expired'] ?? 0) > 0) || (($breakdown['expiring'] ?? 0) > 0)) • @endif
                                            Nên đẩy bán: {{ number_format($breakdown['promotion']) }}
                                        @endif
                                        @if (($breakdown['safe'] ?? 0) > 0 && ($breakdown['expired'] ?? 0) === 0 && ($breakdown['expiring'] ?? 0) === 0 && ($breakdown['promotion'] ?? 0) === 0)
                                            An toàn: {{ number_format($breakdown['safe']) }}
                                        @endif
                                        @if (($breakdown['no_expiry'] ?? 0) > 0)
                                            @if ((($breakdown['expired'] ?? 0) + ($breakdown['expiring'] ?? 0) + ($breakdown['promotion'] ?? 0) + ($breakdown['safe'] ?? 0)) > 0) • @endif
                                            Không HSD: {{ number_format($breakdown['no_expiry']) }}
                                        @endif
                                    </div>
                                </td>

                                <td>
                                    <div class="inventory-summary-card {{ $deadlineCardClass }}">
                                        @if ($inv->expiry_date)
                                            <div class="fw-semibold">{{ $inv->expiry_date->format('d/m/Y') }}</div>
                                            @if ($inv->is_expired)
                                                <div class="inventory-subtext text-danger mt-1">Đã có lô quá hạn, cần xử lý ngay.</div>
                                            @elseif ($inv->is_expiring_soon)
                                                <div class="inventory-subtext text-warning mt-1">Có lô sắp hết hạn trong {{ $inv->days_to_expire }} ngày.</div>
                                            @elseif ($inv->is_promotion_candidate)
                                                <div class="inventory-subtext text-primary mt-1">Có lô nằm trong cửa sổ khuyến mãi {{ $promotionWindowDays }} ngày.</div>
                                            @else
                                                <div class="inventory-subtext text-success mt-1">Các lô có HSD vẫn đang an toàn.</div>
                                            @endif
                                        @else
                                            <div class="fw-semibold">Không có hạn sử dụng</div>
                                            <div class="inventory-subtext mt-1">Biến thể này đang còn các lô không quản lý HSD.</div>
                                        @endif

                                        <div class="inventory-subtext mt-3">
                                            @if (!is_null($inv->stock_age_days))
                                                Lô còn hàng lâu nhất đã nằm kho {{ $inv->stock_age_days }} ngày.
                                            @else
                                                Chưa xác định được tuổi lô tồn.
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    @if ($inv->is_out_of_stock)
                                        <span class="badge bg-danger me-1 mb-1">Hết hàng</span>
                                    @endif
                                    @if ($inv->is_low_stock)
                                        <span class="badge bg-warning text-dark me-1 mb-1">Sắp hết hàng</span>
                                    @endif
                                    @if ($inv->is_expired)
                                        <span class="badge bg-danger me-1 mb-1">Có lô hết hạn</span>
                                    @endif
                                    @if ($inv->is_expiring_soon)
                                        <span class="badge bg-warning text-dark me-1 mb-1">Có lô sắp hết hạn</span>
                                    @endif
                                    @if ($inv->is_promotion_candidate)
                                        <span class="badge bg-primary me-1 mb-1">Nên đẩy bán</span>
                                    @endif
                                    @if ($inv->is_stale)
                                        <span class="badge bg-secondary me-1 mb-1">Tồn kho lâu</span>
                                    @endif
                                    @if (empty($inv->alert_tags))
                                        <span class="badge bg-success">Ổn định</span>
                                    @endif
                                </td>

                                <td>
                                    <div class="inventory-actions">
                                        <button type="button" class="btn btn-sm btn-outline-primary w-100 btn-show-batch-prices"
                                            data-url="{{ route('admin.inventories.batches', $inv->product_variant_id) }}"
                                            data-bs-toggle="modal" data-bs-target="#inventoryBatchModal">
                                            Xem lô chi tiết
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach

                        @if ($inventories->isEmpty())
                            <tr>
                                <td colspan="7" class="text-center">
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