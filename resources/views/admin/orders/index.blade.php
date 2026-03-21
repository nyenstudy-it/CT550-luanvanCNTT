@extends('admin.layouts.layout_admin')

@section('content')

    <div class="container-fluid pt-4 px-2">

        <div class="bg-light rounded p-4">

            {{-- FILTER --}}
            <form method="GET" action="{{ route('admin.orders') }}" class="row g-3 mb-4">

                <div class="col-md-2">
                    <label class="form-label">Mã đơn</label>
                    <input type="text" name="order_id" value="{{ request('order_id') }}" class="form-control">
                </div>

                <div class="col-md-2">
                    <label class="form-label">SĐT</label>
                    <input type="text" name="phone" value="{{ request('phone') }}" class="form-control">
                </div>

                <div class="col-md-2">

                    <label class="form-label">Trạng thái</label>

                    <select name="status" class="form-select">

                        <option value="">-- Tất cả --</option>

                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>
                            Chờ xác nhận
                        </option>

                        <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>
                            Đã xác nhận
                        </option>

                        <option value="shipping" {{ request('status') == 'shipping' ? 'selected' : '' }}>
                            Đang giao
                        </option>

                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>
                            Hoàn thành
                        </option>

                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>
                            Đã hủy
                        </option>

                        <option value="refund_requested" {{ request('status') == 'refund_requested' ? 'selected' : '' }}>
                            Yêu cầu hoàn tiền
                        </option>

                        <option value="refunded" {{ request('status') == 'refunded' ? 'selected' : '' }}>
                            Đã hoàn tiền
                        </option>

                    </select>

                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">
                        Lọc
                    </button>
                </div>

            </form>


            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">Danh sách đơn hàng</h6>
            </div>

            <style>
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

                .table-orders td:nth-child(5) {
                    min-width: 100px;
                }

                .table-orders td:nth-child(6) {
                    min-width: 60px;
                }


                .table-orders td:nth-child(7) {
                    min-width: 150px;
                }

                .table-orders td:nth-child(8) {
                    min-width: 160px;
                }

                .table-orders td:nth-child(9) {
                    min-width: 120px;
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
                            <th width="100">Tổng tiền</th>
                            <th width="60">Thanh toán</th>
                            <th width="170">Trạng thái</th>
                            <th width="180">Ngày đặt</th>
                            <th width="150">Thao tác</th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse ($orders as $index => $order)

                            <tr>

                                <td>
                                    {{ $orders->firstItem() + $index }}
                                </td>

                                <td>
                                    #{{ $order->id }}
                                </td>

                                <td>
                                    {{ $order->receiver_name ?? '—' }}
                                </td>

                                <td>
                                    {{ $order->receiver_phone ?? '—' }}
                                </td>

                                <td>
                                    {{ number_format($order->total_amount, 0, ',', '.') }} đ
                                </td>

                                <td>

                                    @if($order->payment)

                                        @if($order->payment->method == 'COD')
                                            <span class="badge bg-secondary">COD</span>

                                        @elseif($order->payment->method == 'VNPAY')
                                            <span class="badge bg-primary">VNPAY</span>

                                        @elseif($order->payment->method == 'MOMO')
                                            <span class="badge bg-danger">MOMO</span>

                                        @elseif($order->payment->method == 'BANK_TRANSFER')
                                            <span class="badge bg-info text-dark">Chuyển khoản</span>

                                        @endif

                                    @else

                                        <span class="badge bg-light text-dark">—</span>

                                    @endif

                                </td>

                                <td>

                                    @if(in_array($order->status, ['completed', 'cancelled', 'refund_requested', 'refunded']))

                                        {{-- CHỈ HIỂN THỊ BADGE, KHÔNG CHO SỬA --}}

                                        @if($order->status == 'completed')
                                            <span class="badge bg-success">Hoàn thành</span>

                                        @elseif($order->status == 'cancelled')
                                            <span class="badge bg-danger">Đã hủy</span>

                                        @elseif($order->status == 'refund_requested')
                                            <span class="badge bg-warning text-dark">Yêu cầu hoàn tiền</span>

                                        @elseif($order->status == 'refunded')
                                            <span class="badge bg-info">Đã hoàn tiền</span>
                                        @endif

                                    @else

                                        {{-- CHỈ CHO SỬA KHI ĐANG XỬ LÝ --}}
                                        <form method="POST" action="{{ route('admin.orders.updateStatus', $order->id) }}">
                                            @csrf

                                            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">

                                                <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>
                                                    Chờ xác nhận
                                                </option>

                                                <option value="confirmed" {{ $order->status == 'confirmed' ? 'selected' : '' }}>
                                                    Đã xác nhận
                                                </option>

                                                <option value="shipping" {{ $order->status == 'shipping' ? 'selected' : '' }}>
                                                    Đang giao
                                                </option>

                                                <option value="completed" {{ $order->status == 'completed' ? 'selected' : '' }}>
                                                    Hoàn thành
                                                </option>

                                            </select>

                                        </form>

                                    @endif

                                </td>

                                <td>
                                    {{ $order->created_at->format('d/m/Y H:i') }}
                                </td>


                                <td>

                                    <a href="{{ route('admin.orders.detail', $order->id) }}" class="btn btn-sm btn-info mb-1">
                                        Xem
                                    </a>

                                    @if($order->status != 'cancelled' && $order->status != 'completed')

                                        <button class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                            data-bs-target="#cancelModal{{ $order->id }}">
                                            Hủy
                                        </button>

                                    @endif

                                    @if($order->status == 'refund_requested')

                                        <form action="{{ route('admin.orders.approveRefund', $order->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            <button class="btn btn-sm btn-success mb-1">
                                                Duyệt hoàn
                                            </button>
                                        </form>

                                        <form action="{{ route('admin.orders.rejectRefund', $order->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            <button class="btn btn-sm btn-warning mb-1">
                                                Từ chối
                                            </button>
                                        </form>

                                    @endif

                                </td>

                            </tr>


                            {{-- MODAL ĐỔI TRẠNG THÁI --}}

                            <div class="modal fade" id="statusModal{{ $order->id }}">

                                <div class="modal-dialog">
                                    <div class="modal-content">

                                        <div class="modal-header">

                                            <h5 class="modal-title">
                                                Cập nhật trạng thái đơn #{{ $order->id }}
                                            </h5>

                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>

                                        </div>

                                        <form method="POST" action="{{ route('admin.orders.updateStatus', $order->id) }}">

                                            @csrf

                                            <div class="modal-body">

                                                <select name="status" class="form-select">

                                                    <option value="pending">Chờ xác nhận</option>

                                                    <option value="confirmed">Đã xác nhận</option>

                                                    <option value="shipping">Đang giao</option>

                                                    <option value="completed">Hoàn thành</option>

                                                </select>

                                            </div>

                                            <div class="modal-footer">

                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                    Đóng
                                                </button>

                                                <button type="submit" class="btn btn-primary">
                                                    Cập nhật
                                                </button>

                                            </div>

                                        </form>

                                    </div>
                                </div>

                            </div>



                            {{-- MODAL HỦY ĐƠN --}}

                            <div class="modal fade" id="cancelModal{{ $order->id }}">

                                <div class="modal-dialog">
                                    <div class="modal-content">

                                        <div class="modal-header">

                                            <h5 class="modal-title">
                                                Hủy đơn #{{ $order->id }}
                                            </h5>

                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>

                                        </div>


                                        <form method="POST" action="{{ route('admin.orders.cancel', $order->id) }}">

                                            @csrf

                                            <div class="modal-body">

                                                <label class="form-label">
                                                    Lý do hủy đơn
                                                </label>

                                                <textarea name="reason" class="form-control" rows="3" required></textarea>

                                            </div>

                                            <div class="modal-footer">

                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                    Đóng
                                                </button>

                                                <button type="submit" class="btn btn-danger">
                                                    Xác nhận hủy
                                                </button>

                                            </div>

                                        </form>

                                    </div>
                                </div>

                            </div>


                        @empty

                            <tr>

                                <td colspan="9" class="text-center text-muted">
                                    Chưa có đơn hàng
                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>


                {{-- PAGINATION --}}
                {{ $orders->appends(request()->query())->links() }}

            </div>

        </div>

    </div>

@endsection