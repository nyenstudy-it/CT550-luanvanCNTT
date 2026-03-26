@extends('admin.layouts.layout_admin')

@section('content')
    <div class="container-fluid pt-4 px-4">
        <div class="bg-light rounded p-4">

            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div>
                    <h5 class="mb-1">Danh sách đơn hàng</h5>
                    <small class="text-muted">Theo dõi trạng thái xử lý, thanh toán và yêu cầu hoàn tiền.</small>
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
                        <small class="text-muted d-block mb-1">Yêu cầu hoàn tiền</small>
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

            <form method="GET" action="{{ route('admin.orders') }}" class="row g-3 mb-4 border rounded bg-white p-3">
                <div class="col-md-3">
                    <label class="form-label">Mã đơn</label>
                    <input type="text" name="order_id" value="{{ request('order_id') }}" class="form-control"
                        placeholder="Nhập mã đơn">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Số điện thoại</label>
                    <input type="text" name="phone" value="{{ request('phone') }}" class="form-control"
                        placeholder="Nhập số điện thoại">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="">-- Tất cả --</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chờ xác nhận</option>
                        <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Đã xác nhận</option>
                        <option value="shipping" {{ request('status') == 'shipping' ? 'selected' : '' }}>Đang giao</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Hoàn thành</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                        <option value="refund_requested" {{ request('status') == 'refund_requested' ? 'selected' : '' }}>Yêu cầu hoàn tiền</option>
                        <option value="refunded" {{ request('status') == 'refunded' ? 'selected' : '' }}>Đã hoàn tiền</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">Lọc</button>
                    <a href="{{ route('admin.orders') }}" class="btn btn-outline-secondary">Đặt lại</a>
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
                                                'refund_requested' => 'Yêu cầu hoàn tiền',
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
                                        $firstReturn = $order->returns->first() ?? null;
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
                                                        <h5>Yêu cầu hoàn tiền đơn #{{ $order->id }}</h5>
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
                                                        <form action="{{ route('admin.orders.approveRefund', $order->id) }}" method="POST">
                                                            @csrf
                                                            <button type="submit" class="btn btn-success">Duyệt hoàn</button>
                                                        </form>

                                                        <form action="{{ route('admin.orders.rejectRefund', $order->id) }}" method="POST">
                                                            @csrf
                                                            <button type="submit" class="btn btn-warning text-dark">Từ chối</button>
                                                        </form>

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
                                                <label class="form-label">Lý do hủy đơn</label>

                                                <select name="reason" class="form-select mb-2" required>
                                                    <option value="">Chọn lý do hủy</option>
                                                    @foreach(($cancelReasonPresets ?? []) as $reasonCode => $reasonLabel)
                                                        <option value="{{ $reasonCode }}">{{ $reasonLabel }}</option>
                                                    @endforeach
                                                </select>

                                                <input type="text" name="reason_note" class="form-control" maxlength="255"
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
@endsection
