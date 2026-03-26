@extends('admin.layouts.layout_admin')

@section('content')

    <div class="container-fluid pt-4 px-4">

        {{-- HEADER --}}
        <div class="d-flex justify-content-between align-items-center mb-3">

            <h5>Chi tiết đơn hàng #{{ $order->id }}</h5>

            <div>

                <a href="{{ route('admin.orders') }}" class="btn btn-light">
                    ← Quay lại
                </a>

                @if(!in_array($order->status, ['cancelled', 'completed', 'refund_requested', 'refunded']))

                    <form method="POST" action="{{ route('admin.orders.cancel', $order->id) }}" style="display:inline">

                        @csrf

                        <button class="btn btn-danger" onclick="return confirm('Bạn chắc chắn muốn hủy đơn?')">

                            Huỷ đơn

                        </button>

                    </form>

                @endif

                @if($order->status == 'refund_requested')

                    <form method="POST" action="{{ route('admin.orders.approveRefund', $order->id) }}" style="display:inline">
                        @csrf
                        <button class="btn btn-success" onclick="return confirm('Xác nhận hoàn tiền?')">
                            Duyệt hoàn tiền
                        </button>
                    </form>

                    <form method="POST" action="{{ route('admin.orders.rejectRefund', $order->id) }}" style="display:inline">
                        @csrf
                        <button class="btn btn-secondary" onclick="return confirm('Từ chối hoàn tiền?')">
                            Từ chối
                        </button>
                    </form>

                @endif


            </div>

        </div>

        {{-- ORDER PROGRESS --}}
        <div class="order-card mb-4">

            <h6 class="mb-3">Tiến trình đơn hàng</h6>

            @php
                $steps = [
                    'pending' => 1,
                    'confirmed' => 2,
                    'shipping' => 3,
                    'completed' => 4,
                    'cancelled' => 4,
                    'refund_requested' => 4,
                    'refunded' => 4,
                ];

                $current = $steps[$order->status] ?? 1;
            @endphp

            <div class="order-progress">

                <div class="order-step">
                    <div class="order-circle {{ $current >= 1 ? 'active' : '' }}">1</div>
                    <div class="order-label">Chờ xác nhận</div>
                </div>

                <div class="order-step">
                    <div class="order-circle {{ $current >= 2 ? 'active' : '' }}">2</div>
                    <div class="order-label">Đã xác nhận</div>
                </div>

                <div class="order-step">
                    <div class="order-circle {{ $current >= 3 ? 'active' : '' }}">3</div>
                    <div class="order-label">Đang giao</div>
                </div>

                <div class="order-step">
                    <div class="order-circle {{ $current >= 4 ? 'active' : '' }}">4</div>

                    {{-- label động --}}
                    <div class="order-label">
                        @if($order->status == 'refunded')
                            Đã hoàn tiền
                        @elseif($order->status == 'refund_requested')
                            Chờ hoàn tiền
                        @elseif($order->status == 'cancelled')
                            Đã hủy
                        @else
                            Hoàn thành
                        @endif
                    </div>

                </div>

            </div>

        </div>


        <div class="row">

            {{-- LEFT COLUMN --}}
            <div class="col-lg-4">

                {{-- ORDER INFO --}}
                <div class="order-card mb-4">

                    <h6 class="mb-3">Thông tin đơn hàng</h6>

                    <table class="table table-sm">

                        <tr>
                            <td width="130">Mã đơn</td>
                            <td><b>#{{ $order->id }}</b></td>
                        </tr>

                        <tr>
                            <td>Ngày đặt</td>
                            <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                        </tr>

                        <tr>
                            <td>Trạng thái</td>
                            <td>
                                @if($order->status == 'pending')
                                    <span class="badge bg-warning">Chờ xác nhận</span>

                                @elseif($order->status == 'confirmed')
                                    <span class="badge bg-info">Đã xác nhận</span>

                                @elseif($order->status == 'shipping')
                                    <span class="badge bg-primary">Đang giao</span>

                                @elseif($order->status == 'completed')
                                    <span class="badge bg-success">Hoàn thành</span>

                                @elseif($order->status == 'cancelled')
                                    <span class="badge bg-danger">Đã hủy</span>

                                @elseif($order->status == 'refund_requested')
                                    <span class="badge bg-warning text-dark">Chờ hoàn tiền</span>

                                @elseif($order->status == 'refunded')
                                    <span class="badge bg-success">Đã hoàn tiền</span>
                                @endif

                            </td>
                        </tr>

                        <tr>
                            <td>Phương thức TT</td>
                            <td>{{ $order->payment->method ?? 'COD' }}</td>
                        </tr>

                        <tr>
                            <td>Trạng thái TT</td>

                            <td>

                                @if($order->payment)

                                    @if($order->payment->status == 'paid')
                                        <span class="badge bg-success">Đã thanh toán</span>

                                    @elseif($order->payment->status == 'pending')
                                        <span class="badge bg-warning text-dark">Chưa thanh toán</span>

                                    @elseif($order->payment->status == 'failed')
                                        <span class="badge bg-danger">Thanh toán lỗi</span>
                                    @endif

                                @else

                                    <span class="badge bg-warning text-dark">Chưa thanh toán</span>

                                @endif

                            </td>

                        </tr>

                        @if($order->payment && $order->payment->refund_status)

                            <tr>
                                <td>Hoàn tiền</td>
                                <td>

                                    @if($order->payment->refund_status == 'pending')
                                        <span class="badge bg-warning text-dark">Chờ xử lý</span>

                                    @elseif($order->payment->refund_status == 'completed')
                                        <span class="badge bg-success">Đã hoàn</span>

                                    @elseif($order->payment->refund_status == 'failed')
                                        <span class="badge bg-danger">Bị từ chối</span>
                                    @endif

                                </td>
                            </tr>

                            @if($order->payment && $order->payment->refund_amount)

                                <tr>
                                    <td>Số tiền hoàn</td>
                                    <td class="text-success">
                                        {{ number_format($order->payment->refund_amount, 0, ',', '.') }} đ
                                    </td>
                                </tr>

                            @endif


                        @endif


                    </table>

                </div>

                {{-- CUSTOMER INFO --}}
                <div class="order-card mb-4">

                    <h6 class="mb-3">Thông tin khách hàng</h6>

                    <p><b>Tên:</b> {{ $order->receiver_name }}</p>

                    <p><b>SĐT:</b> {{ $order->receiver_phone }}</p>

                    <p><b>Địa chỉ:</b> {{ $order->shipping_address }}</p>

                </div>

                @php
                    $adminReturn = $order->returns->first();
                @endphp

                @if($adminReturn || ($order->payment && $order->payment->refund_status))
                    <div class="order-card mb-4">

                        <h6 class="mb-3">Chi tiết hoàn tiền / hoàn hàng</h6>

                        @if($adminReturn)
                            <p><b>Lý do hoàn hàng:</b> {{ $adminReturn->reason_vn }}</p>
                            <p><b>Mô tả:</b> {{ $adminReturn->description ?: '---' }}</p>

                            @if($adminReturn->images && $adminReturn->images->count())
                                <div class="mb-3">
                                    <b class="d-block mb-2">Hình ảnh khách gửi:</b>
                                    <div class="d-flex flex-wrap" style="gap:10px;">
                                        @foreach($adminReturn->images as $refundImage)
                                            <a href="{{ asset('storage/' . $refundImage->image_path) }}" target="_blank" rel="noopener">
                                                <img src="{{ asset('storage/' . $refundImage->image_path) }}" width="88" height="88"
                                                    class="rounded border" style="object-fit:cover;">
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endif

                        @if($order->payment && $order->payment->refund_status)
                            <table class="table table-sm mb-0">
                                <tr>
                                    <td width="140">Trạng thái hoàn</td>
                                    <td>
                                        @if($order->payment->refund_status == 'pending')
                                            <span class="badge bg-warning text-dark">Chờ xử lý</span>
                                        @elseif($order->payment->refund_status == 'completed')
                                            <span class="badge bg-success">Đã hoàn tiền</span>
                                        @elseif($order->payment->refund_status == 'failed')
                                            <span class="badge bg-danger">Từ chối hoàn tiền</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td>Số tiền hoàn</td>
                                    <td class="text-success">
                                        {{ number_format($order->payment->refund_amount ?? 0, 0, ',', '.') }} đ
                                    </td>
                                </tr>
                                <tr>
                                    <td>Thời gian hoàn</td>
                                    <td>{{ $order->payment->refund_at?->format('d/m/Y H:i') ?? '---' }}</td>
                                </tr>
                            </table>
                        @endif

                    </div>
                @endif


                @if(!in_array($order->status, ['completed', 'cancelled', 'refund_requested', 'refunded']))
                    {{-- UPDATE STATUS --}}
                    <div class="order-card">

                        <h6 class="mb-3">Cập nhật trạng thái</h6>

                        <form method="POST" action="{{ route('admin.orders.updateStatus', $order->id) }}">

                            @csrf

                            <select name="status" class="form-select mb-3">

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

                            <button class="btn btn-primary w-100">

                                Cập nhật trạng thái

                            </button>

                        </form>

                    </div>

                @endif

            </div>

            {{-- RIGHT COLUMN --}}
            <div class="col-lg-8">

                {{-- PRODUCTS --}}
                <div class="order-card">

                    <h6 class="mb-3">Sản phẩm đã đặt</h6>

                    <div class="table-responsive">

                        <table class="table align-middle">

                            <thead>

                                <tr>
                                    <th width="80">Ảnh</th>
                                    <th>Sản phẩm</th>
                                    <th width="120">Đơn giá</th>
                                    <th width="80">SL</th>
                                    <th width="150">Thành tiền</th>
                                </tr>

                            </thead>

                            <tbody>

                                @foreach($order->items as $item)

                                    <tr>

                                        <td>

                                            @php
                                                $variantImage = $item->variant->images->first();
                                                $productImage = $item->variant->product->images->first();
                                            @endphp

                                            @if($variantImage)

                                                <img src="{{ asset('storage/' . $variantImage->image_path) }}" width="60"
                                                    height="60" class="rounded border" style="object-fit:cover">

                                            @elseif($productImage)

                                                <img src="{{ asset('storage/' . $productImage->image_path) }}" width="60"
                                                    height="60" class="rounded border" style="object-fit:cover">

                                            @endif

                                        </td>

                                        <td>

                                            <b>{{ $item->variant->product->name }}</b>

                                            <br>

                                            <small class="text-muted">

                                                @if($item->variant->volume)
                                                    {{ $item->variant->volume }}
                                                @endif

                                                @if($item->variant->weight)
                                                    {{ $item->variant->weight }}
                                                @endif

                                                @if($item->variant->size)
                                                    Size {{ $item->variant->size }}
                                                @endif

                                                @if($item->variant->color)
                                                    {{ $item->variant->color }}
                                                @endif

                                            </small>

                                        </td>

                                        <td>
                                            {{ number_format($item->price, 0, ',', '.') }} đ
                                        </td>

                                        <td>
                                            {{ $item->quantity }}
                                        </td>

                                        <td>
                                            {{ number_format($item->subtotal, 0, ',', '.') }} đ
                                        </td>

                                    </tr>

                                @endforeach

                            </tbody>

                        </table>

                    </div>

                </div>

                {{-- ORDER SUMMARY --}}
                @php
                    // Tổng tiền sản phẩm
                    $subtotal = $order->items->sum('subtotal');

                    // Phí vận chuyển
                    $shipping = $order->shipping_fee ?? 0;

                    // Giảm giá
                    $discount = $order->discount_amount ?? 0;

                    // Tổng thanh toán thực tế
                    $total = $subtotal + $shipping - $discount;
                @endphp

                <div class="order-card mt-4">
                    <h6 class="mb-3">Thanh toán</h6>

                    <div class="d-flex justify-content-between mb-2">
                        <span>Tổng tiền hàng</span>
                        <span>{{ number_format($subtotal, 0, ',', '.') }} đ</span>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span>Phí vận chuyển</span>
                        <span>{{ number_format($shipping, 0, ',', '.') }} đ</span>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span>Giảm giá</span>
                        <span>{{ number_format($discount, 0, ',', '.') }} đ</span>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between">
                        <b>Tổng thanh toán</b>
                        <b class="text-danger fs-5">
                            {{ number_format($total, 0, ',', '.') }} đ
                        </b>
                    </div>
                </div>

                {{-- CANCEL REASON --}}
                @if($order->status == 'cancelled' && $order->cancellation)

                    <div class="order-card mt-4">

                        <h6>Lý do hủy đơn</h6>

                        <p class="text-danger">

                            @php
                                $rawReason = (string) $order->cancellation->reason;
                                [$reasonCode, $reasonNote] = array_pad(explode(':', $rawReason, 2), 2, null);
                                $reasonText = [
                                    'change_mind' => 'Không muốn mua nữa',
                                    'wrong_product' => 'Chọn nhầm sản phẩm',
                                    'wrong_address' => 'Sai địa chỉ giao hàng',
                                    'found_cheaper' => 'Tìm được nơi rẻ hơn',
                                    'delivery_too_long' => 'Thời gian giao quá lâu',
                                    'customer_cancel' => 'Khách hàng tự huỷ đơn',
                                    'admin_cancel' => 'Đơn hàng bị huỷ bởi quản trị viên',
                                    'out_of_stock' => 'Hết hàng trong kho',
                                    'cannot_contact' => 'Không liên hệ được khách hàng',
                                    'delivery_area_unavailable' => 'Khu vực giao hàng tạm ngưng phục vụ',
                                    'suspected_fraud' => 'Đơn hàng có dấu hiệu rủi ro/gian lận',
                                    'system_error' => 'Lỗi hệ thống xử lý đơn hàng',
                                    'other' => 'Lý do khác',
                                ];
                                $reasonLabel = $reasonText[$reasonCode] ?? $rawReason;
                            @endphp

                            {{ $reasonLabel }}{{ $reasonNote ? ' - ' . $reasonNote : '' }}

                        </p>

                    </div>

                @endif

            </div>

        </div>

    </div>

@endsection