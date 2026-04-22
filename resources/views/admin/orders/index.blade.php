@extends('admin.layouts.layout_admin')

@section('content')
    <div class="container-fluid pt-4 px-4">
        <div class="bg-light rounded p-4">

            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div>
                    <h5 class="mb-1">Danh sách đơn hàng</h5>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-12 col-sm-6 col-lg-2">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Tổng đơn</small>
                        <h4 class="mb-0">{{ $stats['total'] ?? 0 }}</h4>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-2">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Chờ xử lý</small>
                        <h4 class="mb-0 text-warning">{{ $stats['pending'] ?? 0 }}</h4>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-2">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Đang giao</small>
                        <h4 class="mb-0 text-primary">{{ $stats['shipping'] ?? 0 }}</h4>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Yêu cầu hoàn hàng</small>
                        <h4 class="mb-0 text-info">{{ $stats['refund_requested'] ?? 0 }}</h4>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Đơn mới hôm nay</small>
                        <h4 class="mb-0 text-success">{{ $stats['today'] ?? 0 }}</h4>
                    </div>
                </div>
            </div>

            <!-- Suggestion Card: Refund Requests -->
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="card border-warning">
                        <div class="card-body">
                            <h6 class="card-title mb-3">Đề xuất yêu cầu hoàn trả</h6>
                            <div class="row g-2 mb-3">
                                <div class="col-md-6">
                                    <div class="bg-light p-2 rounded text-center">
                                        <div class="text-muted small">Hoàn trả tháng này</div>
                                        <h5 class="mb-0" id="refundStatTotal">-</h5>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="bg-light p-2 rounded text-center">
                                        <div class="text-muted small">Khách cần xem xét</div>
                                        <h5 class="mb-0" id="refundStatCustomers">-</h5>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-warning btn-sm w-100" onclick="openSuggestLockRefundRequestsModal()">Xem danh sách</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Filters -->
            <div class="btn-group mb-4 d-flex flex-wrap gap-2" role="group">
                <a href="{{ route('admin.orders') }}" class="btn btn-sm btn-outline-secondary {{ !request()->has('date_range') ? 'active' : '' }}">
                    Tất cả
                </a>
                <a href="{{ route('admin.orders', ['date_range' => 'today']) }}" class="btn btn-sm btn-outline-secondary {{ request('date_range') == 'today' ? 'active' : '' }}">
                    Hôm nay
                </a>
                <a href="{{ route('admin.orders', ['date_range' => '7days']) }}" class="btn btn-sm btn-outline-secondary {{ request('date_range') == '7days' ? 'active' : '' }}">
                    7 ngày
                </a>
                <a href="{{ route('admin.orders', ['date_range' => '30days']) }}" class="btn btn-sm btn-outline-secondary {{ request('date_range') == '30days' ? 'active' : '' }}">
                    30 ngày
                </a>
            </div>

            <!-- Main Filters -->
            <form method="GET" action="{{ route('admin.orders') }}" class="border rounded bg-white p-3 mb-4">
                <!-- Row 1: Main Search & Filters -->
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label for="filter_search" class="form-label">Tìm kiếm</label>
                        <input id="filter_search" type="text" name="search" value="{{ request('search') ?? request('order_id') ?? request('phone') }}" class="form-control"
                            placeholder="Mã đơn hoặc SĐT...">
                    </div>
                    
                    <div class="col-md-3">
                        <label for="filter_status" class="form-label">Trạng thái</label>
                        <select id="filter_status" name="status" class="form-select">
                            <option value="">Tất cả</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chờ xác nhận</option>
                            <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Đã xác nhận</option>
                            <option value="shipping" {{ request('status') == 'shipping' ? 'selected' : '' }}>Đang giao</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Hoàn thành</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                            <option value="refund_requested" {{ request('status') == 'refund_requested' ? 'selected' : '' }}>Yêu cầu hoàn hàng</option>
                            <option value="refunded" {{ request('status') == 'refunded' ? 'selected' : '' }}>Đã hoàn tiền</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="filter_payment_method" class="form-label">Phương thức thanh toán</label>
                        <select id="filter_payment_method" name="payment_method" class="form-select">
                            <option value="">Tất cả</option>
                            <option value="COD" {{ request('payment_method') == 'COD' ? 'selected' : '' }}>COD</option>
                            <option value="VNPAY" {{ request('payment_method') == 'VNPAY' ? 'selected' : '' }}>VNPAY</option>
                            <option value="MOMO" {{ request('payment_method') == 'MOMO' ? 'selected' : '' }}>MOMO</option>
                        </select>
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Lọc</button>
                    </div>
                </div>

                <!-- Row 2: Advanced Filters (Collapsible) -->
                <div class="row g-3">
                    <div class="col-12">
                        <button type="button" class="btn btn-link btn-sm p-0 text-muted" data-bs-toggle="collapse" data-bs-target="#advancedFilters">
                            Lọc nâng cao
                        </button>
                        <div class="collapse mt-3" id="advancedFilters">
                            <div class="row g-3 pt-3 border-top">
                                <div class="col-md-3">
                                    <label class="form-label">Từ ngày</label>
                                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Đến ngày</label>
                                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Tiền từ (VND)</label>
                                    <input type="number" name="price_from" value="{{ request('price_from') }}" class="form-control" placeholder="0">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Tiền đến (VND)</label>
                                    <input type="number" name="price_to" value="{{ request('price_to') }}" class="form-control" placeholder="0">
                                </div>
                                <div class="col-12 pt-2">
                                    <a href="{{ route('admin.orders') }}" class="btn btn-outline-secondary btn-sm">Xóa bộ lọc</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">Bảng đơn hàng</h6>
            </div>

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

                .table-orders th,
                .table-orders td {
                    white-space: nowrap;
                    vertical-align: middle;
                }

                .table-orders td:nth-child(3) {
                    min-width: 150px;
                }

                .table-orders td:nth-child(4) {
                    min-width: 120px;
                }

                .table-orders td:nth-child(7) {
                    min-width: 170px;
                }

                .table-orders td:nth-child(8) {
                    min-width: 160px;
                }

                .table-orders td:nth-child(9) {
                    min-width: 170px;
                }
            </style>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle table-orders">
                    <thead class="table-light">
                        <tr>
                            <th width="60">STT</th>
                            <th width="90">Mã đơn</th>
                            <th width="160">Khách hàng</th>
                            <th width="130">SĐT</th>
                            <th width="130">Tổng tiền</th>
                            <th width="90">Thanh toán</th>
                            <th width="170">Trạng thái</th>
                            <th width="180">Ngày đặt</th>
                            <th width="170">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $index => $order)
                            <tr>
                                <td>{{ $orders->firstItem() + $index }}</td>
                                <td>#{{ $order->id }}</td>
                                <td>{{ $order->receiver_name ?? '—' }}</td>
                                <td>{{ $order->receiver_phone ?? '—' }}</td>
                                <td>{{ number_format($order->total_amount, 0, ',', '.') }} đ</td>
                                <td>
                                    @if($order->payment)
                                        @php $paymentMethod = strtoupper($order->payment->method); @endphp
                                        @if($paymentMethod == 'COD')
                                            <span class="badge bg-secondary admin-badge">COD</span>
                                        @elseif($paymentMethod == 'VNPAY')
                                            <span class="badge bg-primary admin-badge">VNPAY</span>
                                        @elseif($paymentMethod == 'MOMO')
                                            <span class="badge bg-danger admin-badge">MOMO</span>
                                        @else
                                            <span class="badge bg-light text-dark admin-badge">{{ $paymentMethod }}</span>
                                        @endif
                                    @else
                                        <span class="badge bg-light text-dark admin-badge">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if(!in_array($order->status, ['pending', 'confirmed', 'shipping']))
                                        @php
                                            $statusLabel = [
                                                'completed' => 'Hoàn thành',
                                                'cancelled' => 'Đã hủy',
                                                'refund_requested' => 'Yêu cầu hoàn hàng',
                                                'refunded' => 'Đã hoàn tiền',
                                            ][$order->status] ?? $order->status;

                                            $statusBadge = [
                                                'completed' => 'success',
                                                'cancelled' => 'danger',
                                                'refund_requested' => 'warning text-dark',
                                                'refunded' => 'info',
                                            ][$order->status] ?? 'secondary';
                                        @endphp
                                        <span class="badge bg-{{ $statusBadge }} admin-badge">{{ $statusLabel }}</span>
                                    @else
                                        <form method="POST" action="{{ route('admin.orders.updateStatus', $order->id) }}">
                                            @csrf
                                            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                                <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Chờ xác nhận</option>
                                                <option value="confirmed" {{ $order->status == 'confirmed' ? 'selected' : '' }}>Đã xác nhận</option>
                                                <option value="shipping" {{ $order->status == 'shipping' ? 'selected' : '' }}>Đang giao</option>
                                                <option value="completed" {{ $order->status == 'completed' ? 'selected' : '' }}>Hoàn thành</option>
                                            </select>
                                        </form>
                                    @endif
                                </td>
                                <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('admin.orders.detail', $order->id) }}" class="btn btn-sm btn-outline-primary mb-1 admin-action-btn">
                                        Xem
                                    </a>

                                    @php
                                        $firstReturn = $order->returns->sortByDesc('id')->first() ?? null;
                                        $isRefunded = $order->status === 'refunded'
                                            || ($order->payment && $order->payment->refund_status === 'completed');
                                    @endphp

                                    @if(
                                            $order->status != 'cancelled' &&
                                            $order->status != 'completed' &&
                                            $order->status != 'refund_requested' &&
                                            $order->status != 'shipping' &&
                                            !$isRefunded
                                        )
                                        <button class="btn btn-sm btn-outline-danger mb-1 admin-action-btn" data-bs-toggle="modal"
                                            data-bs-target="#cancelModal{{ $order->id }}">
                                            Hủy
                                        </button>
                                    @endif

                                    @if($order->status == 'refund_requested' && $firstReturn)
                                        <button class="btn btn-sm btn-outline-info mb-1 admin-action-btn" data-bs-toggle="modal"
                                            data-bs-target="#refundModalAdmin{{ $order->id }}">
                                            Xem yêu cầu
                                        </button>

                                        <div class="modal fade" id="refundModalAdmin{{ $order->id }}">
                                            <div class="modal-dialog">
                                                <div class="modal-content p-3">
                                                    <div class="modal-header">
                                                        <h5>Yêu cầu hoàn hàng đơn #{{ $order->id }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p><strong>Lý do:</strong> {{ $firstReturn->reason_vn }}</p>
                                                        <p><strong>Mô tả:</strong> {{ $firstReturn->description ?? '---' }}</p>

                                                        @if($firstReturn->images && $firstReturn->images->count())
                                                            <div class="refund-images d-flex flex-wrap" style="gap:8px; max-height:300px; overflow-y:auto;">
                                                                @foreach($firstReturn->images as $img)
                                                                    <img src="{{ asset('storage/' . $img->image_path) }}" width="100">
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="modal-footer d-flex justify-content-between">
                                                        <a href="{{ route('admin.orders.detail', $order->id) }}" class="btn btn-primary">
                                                            Xử lý theo quy trình mới
                                                        </a>

                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </td>
                            </tr>

                            <div class="modal fade" id="cancelModal{{ $order->id }}">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Hủy đơn #{{ $order->id }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>

                                        <form method="POST" action="{{ route('admin.orders.cancel', $order->id) }}">
                                            @csrf
                                            <div class="modal-body">
                                                <label for="cancel_reason_{{ $order->id }}" class="form-label">Lý do hủy đơn</label>

                                                    <select id="cancel_reason_{{ $order->id }}" name="reason" class="form-select mb-2" required>
                                                    <option value="">Chọn lý do hủy</option>
                                                    @foreach(($cancelReasonPresets ?? []) as $reasonCode => $reasonLabel)
                                                        <option value="{{ $reasonCode }}">{{ $reasonLabel }}</option>
                                                    @endforeach
                                                </select>

                                                <input id="cancel_reason_note_{{ $order->id }}" type="text" name="reason_note" class="form-control" maxlength="255"
                                                    placeholder="Ghi chú thêm (tuỳ chọn, đặc biệt khi chọn Lý do khác)">
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                                <button type="submit" class="btn btn-danger">Xác nhận hủy</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted">Chưa có đơn hàng</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $orders->appends(request()->query())->links() }}
            </div>
        </div>
    </div>

    <!-- Modal: Yêu cầu hoàn trả -->
    <div class="modal fade" id="refundRequestsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title">Yêu cầu hoàn trả</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <div class="bg-light p-2 rounded text-center">
                                <div class="text-muted small">Hoàn trả tháng này</div>
                                <h5 class="mb-0" id="refundStatTotal">0</h5>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="bg-light p-2 rounded text-center">
                                <div class="text-muted small">Khách cần xem xét</div>
                                <h5 class="mb-0" id="refundStatCustomers">0</h5>
                            </div>
                        </div>
                    </div>

                    <div id="refundLoading" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Đang tải...</span>
                        </div>
                    </div>

                    <div id="refundList" style="display: none;">
                        <div class="list-group list-group-flush" id="refundCustomersList"></div>
                        <nav id="refundPagination" class="mt-3" style="display: none;">
                            <ul class="pagination pagination-sm justify-content-center"></ul>
                        </nav>
                    </div>

                    <div id="refundEmpty" style="display: none;" class="text-center py-4">
                        <div style="font-size: 2rem; color: #28a745; margin-bottom: 10px;">✓</div>
                        <h6>Không có khách hàng cần xem xét</h6>
                        <small class="text-muted">Tất cả khách hàng đều bình thường.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        function openSuggestLockRefundRequestsModal() {
            const modal = new bootstrap.Modal(document.getElementById('refundRequestsModal'));
            modal.show();
            loadRefundRequestsData(1);
        }

        function loadRefundRequestsData(page = 1) {
            const loading = document.getElementById('refundLoading');
            const list = document.getElementById('refundList');
            const empty = document.getElementById('refundEmpty');

            loading.style.display = 'block';
            list.style.display = 'none';
            empty.style.display = 'none';

            fetch(`{{ route('admin.api.suggest-lock-refund-requests') }}?page=${page}`)
                .then(r => r.json())
                .then(data => {
                    loading.style.display = 'none';
                    document.getElementById('refundStatTotal').textContent = data.stats.total_refunds_this_month || 0;
                    document.getElementById('refundStatCustomers').textContent = data.stats.customers_flagged || 0;

                    if (data.suggestedCustomers.length === 0) {
                        empty.style.display = 'block';
                        return;
                    }

                    list.style.display = 'block';
                    renderRefundRequestsList(data.suggestedCustomers);
                    renderRefundRequestsPagination(data.pagination);
                })
                .catch(err => {
                    loading.style.display = 'none';
                    document.getElementById('refundCustomersList').innerHTML = '<div class="alert alert-danger">Lỗi tải dữ liệu</div>';
                    list.style.display = 'block';
                });
        }

        function renderRefundRequestsList(customers) {
            const container = document.getElementById('refundCustomersList');
            container.innerHTML = '';

            // keep a reference for detail modal
            window.refundSuggestedCustomers = customers;

            customers.forEach((item, idx) => {
                const customerName = item?.customer?.user?.name || item?.customer?.name || 'Khách hàng';
                const customerEmail = item?.customer?.user?.email || '-';
                const html = `
                    <div class="list-group-item py-3 px-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div class="flex-grow-1">
                                <div class="fw-bold">${escapeHtml(customerName)}</div>
                                <small class="text-muted">${escapeHtml(customerEmail)}</small>
                                <div class="mt-2">
                                    <span class="badge bg-danger">${item.refund_count} hoàn trả</span>
                                    <span class="badge bg-secondary">${item.customer.orders_count || 0} đơn</span>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-warning" onclick="openRefundDetailModal(${idx})">Xem</button>
                            </div>
                        </div>
                    </div>
                `;
                container.innerHTML += html;
            });
        }

        function openRefundDetailModal(index) {
            const item = (window.refundSuggestedCustomers || [])[index];
            if (!item) return;
            const customerName = item?.customer?.user?.name || item?.customer?.name || 'Khách hàng';
            const customerEmail = item?.customer?.user?.email || '-';
            const customerRouteId = item?.customer?.id || '';
            const userIdForRefund = item?.customer?.user_id || item?.customer?.user?.id || item?.customer?.id;

            const modalId = 'refundDetailModal';
            const existing = document.getElementById(modalId);
            if (existing) existing.remove();

            // table rows will be populated from API (to use VN translations)

            const modalHtml = `
                <div class="modal fade" id="${modalId}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header bg-warning text-dark">
                                <h5 class="modal-title">Yêu cầu hoàn trả - ${escapeHtml(customerName)}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p><strong>Email:</strong> ${escapeHtml(customerEmail)}</p>
                                <p><strong>Tổng yêu cầu:</strong> ${item.refund_count} hoàn trả</p>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Mã đơn</th>
                                                <th>Ngày yêu cầu</th>
                                                <th>Tổng tiền</th>
                                                <th>Trạng thái hoàn trả</th>
                                                <th>Số tiền hoàn</th>
                                            </tr>
                                        </thead>
                                        <tbody id="refundDetailTbody">
                                            <tr><td colspan="5" class="text-center text-muted">Đang tải...</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                <a class="btn btn-primary" href="/admin/customers/${customerRouteId}">Mở chi tiết khách</a>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            document.body.insertAdjacentHTML('beforeend', modalHtml);
            const bsModalEl = document.getElementById(modalId);
            const bsModal = new bootstrap.Modal(bsModalEl);
            bsModal.show();

            // Fetch detailed returns (with images) and render into modal (use VN fields)
            fetch(`/admin/api/refund-details/${userIdForRefund}`)
                .then(r => r.json())
                .then(data => {
                    const body = bsModalEl.querySelector('.modal-body');
                    const tbody = bsModalEl.querySelector('#refundDetailTbody');
                    if (!tbody) return;

                    // clear loading placeholder
                    tbody.innerHTML = '';

                    if (!data.returns || data.returns.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Không có yêu cầu chi tiết</td></tr>';
                    } else {
                        data.returns.forEach(ret => {
                            const orderTotal = ret.order_total ? (ret.order_total + ' đ') : '-';
                            const refundAmt = ret.refund_amount ? (ret.refund_amount + ' đ') : '-';
                            const created = ret.created_at || '';
                            const statusVN = ret.status_vn || '-';

                            const row = `
                                <tr>
                                    <td><a href="/admin/orders/${ret.order_id}">#${ret.order_id}</a></td>
                                    <td>${escapeHtml(created)}</td>
                                    <td>${escapeHtml(orderTotal)}</td>
                                    <td>${escapeHtml(statusVN)}</td>
                                    <td>${escapeHtml(refundAmt)}</td>
                                </tr>
                            `;
                            tbody.innerHTML += row;
                        });
                    }

                    // render detail blocks with images above the table
                    const section = document.createElement('div');
                    section.className = 'mb-3';
                    section.innerHTML = '<h6>Yêu cầu hoàn trả (chi tiết)</h6>';

                    (data.returns || []).forEach(ret => {
                        const retDiv = document.createElement('div');
                        retDiv.className = 'border rounded p-2 mb-2';
                        let imgsHtml = '';
                        if (ret.images && ret.images.length) {
                            imgsHtml = '<div class="d-flex flex-wrap gap-2 mb-2">';
                            ret.images.forEach(img => {
                                imgsHtml += `<a href="${img}" target="_blank"><img src="${img}" style="width:100px; height:auto; object-fit:cover; border:1px solid #ddd; padding:2px"></a>`;
                            });
                            imgsHtml += '</div>';
                        }

                        retDiv.innerHTML = `
                            <div class="d-flex justify-content-between"><div><strong>Đơn hàng:</strong> <a href="/admin/orders/${ret.order_id}">#${ret.order_id}</a></div><div><small class="text-muted">${ret.created_at || ''}</small></div></div>
                            <div class="mt-1"><strong>Lý do:</strong> ${escapeHtml(ret.reason_vn || '')}</div>
                            <div class="mt-1"><strong>Trạng thái:</strong> ${escapeHtml(ret.status_vn || '')}</div>
                            <div class="mt-1"><strong>Mô tả:</strong> ${escapeHtml(ret.description || '')}</div>
                            <div class="mt-2">${imgsHtml}</div>
                        `;

                        section.appendChild(retDiv);
                    });

                    const table = body.querySelector('.table-responsive');
                    if (table) body.insertBefore(section, table);
                })
                .catch(() => {
                    const tbody = bsModalEl.querySelector('#refundDetailTbody');
                    if (tbody) tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Lỗi tải dữ liệu</td></tr>';
                });

            bsModalEl.addEventListener('hidden.bs.modal', function () {
                bsModalEl.remove();
            });
        }

        function renderRefundRequestsPagination(pagination) {
            const container = document.getElementById('refundPagination');
            if (pagination.total <= pagination.per_page) {
                container.style.display = 'none';
                return;
            }

            let html = '';
            if (pagination.current_page > 1) {
                html += `<li class="page-item"><a class="page-link" href="#" onclick="loadRefundRequestsData(${pagination.current_page - 1}); return false;">Trước</a></li>`;
            } else {
                html += `<li class="page-item disabled"><span class="page-link">Trước</span></li>`;
            }

            for (let i = 1; i <= pagination.last_page; i++) {
                if (i === pagination.current_page) {
                    html += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
                } else {
                    html += `<li class="page-item"><a class="page-link" href="#" onclick="loadRefundRequestsData(${i}); return false;">${i}</a></li>`;
                }
            }

            if (pagination.current_page < pagination.last_page) {
                html += `<li class="page-item"><a class="page-link" href="#" onclick="loadRefundRequestsData(${pagination.current_page + 1}); return false;">Sau</a></li>`;
            } else {
                html += `<li class="page-item disabled"><span class="page-link">Sau</span></li>`;
            }

            container.querySelector('ul').innerHTML = html;
            container.style.display = 'block';
        }

        function escapeHtml(text) {
            const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
            return (text || '').replace(/[&<>"']/g, m => map[m]);
        }

        // Load stats when page loads
        document.addEventListener('DOMContentLoaded', function () {
            loadRefundRequestsData(1);
        });
    </script>
@endpush
