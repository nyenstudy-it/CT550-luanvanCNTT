@extends('layout')

@section('hero')
    @include('pages.components.hero', ['showBanner' => false, 'heroNormal' => true])
@endsection

@section('content')

    <style>
        .return-policy-card {
            border: 1px solid #e8efe0;
            border-left: 4px solid #7fad39;
            background: #fbfef7;
        }

        .return-policy-list {
            margin: 0;
            padding-left: 18px;
        }

        .return-policy-list li {
            margin-bottom: 8px;
            line-height: 1.55;
        }
    </style>

    <section class="breadcrumb-section set-bg" data-setbg="{{ asset('frontend/images/breadcrumb.jpg') }}">
        <div class="container">
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            <div class="row">
                <div class="col-lg-12 text-center">
                    <div class="breadcrumb__text">

                        <h2>Chi tiết đơn hàng</h2>

                        <div class="breadcrumb__option">
                            <a href="{{ route('pages.home') }}">Trang chủ</a>
                            <a href="{{ route('orders.my') }}">Đơn mua</a>
                            <span>#{{ $order->id }}</span>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </section>

    <section class="order-detail spad">
        <div class="container">


            <div class="row">

                <div class="col-lg-10 offset-lg-1">

                    <!-- ORDER INFO -->
                    <div class="order-card mb-4">

                        <div class="order-header">

                            <h4>
                                Đơn hàng #{{ $order->id }}
                            </h4>

                            @php
                                $statusText = [
                                    'pending' => 'Chờ xử lý',
                                    'confirmed' => 'Đã xác nhận',
                                    'shipping' => 'Đang giao',
                                    'completed' => 'Hoàn thành',
                                    'cancelled' => 'Đã huỷ',
                                    'refund_requested' => 'Đang yêu cầu hoàn tiền',
                                    'refunded' => 'Đã hoàn tiền'
                                ];

                            @endphp

                            <span class="order-status status-{{ $order->status }}">
                                {{ $statusText[$order->status] ?? $order->status }}
                            </span>


                        </div>

                        <div class="order-meta">

                            <div>
                                <strong>Ngày đặt:</strong>
                                {{ $order->created_at->format('d/m/Y H:i') }}
                            </div>

                            <div>
                                <strong>Thanh toán:</strong>

                                @if($order->payment)

                                    @php
                                        $method = $order->payment->method;
                                        $status = $order->payment->status;

                                        $paymentStatus = [
                                            'pending' => 'Chưa thanh toán',
                                            'paid' => 'Đã thanh toán',
                                            'failed' => 'Thanh toán thất bại'
                                        ];
                                    @endphp

                                    <span
                                        class="payment-status 
                                                                                                                                                                                                    @if(strtolower($method) == 'cod') 
                                                                                                                                                                                                        status-cod 
                                                                                                                                                                                                    @else 
                                                                                                                                                                                                        status-{{ $status }} 
                                                                                                                                                                                                    @endif
                                                                                                                                                                                                ">
                                        @if(strtolower($method) == 'cod')
                                            @if($order->payment->status == 'paid')
                                                Đã thanh toán
                                            @else
                                                Chưa thanh toán
                                            @endif
                                        @else
                                            {{ $paymentStatus[$status] ?? $status }}
                                        @endif
                                    </span>


                                @else
                                    <span class="payment-status status-pending">
                                        Chưa thanh toán
                                    </span>
                                @endif
                            </div>

                        </div>
                        @if($order->payment)

                            <div class="order-meta mt-2">

                                <div>
                                    <strong>Phương thức:</strong>
                                    @php
                                        $methodText = [
                                            'momo' => 'Ví MoMo',
                                            'cod' => 'Thanh toán khi nhận hàng',
                                            'vnpay' => 'VNPay'
                                        ];
                                    @endphp

                                    {{ $methodText[strtolower($order->payment->method)] ?? strtoupper($order->payment->method) }}

                                </div>

                                <div>
                                    <strong>Mã giao dịch:</strong>
                                    <span style="color:#ee4d2d;font-weight:600">
                                        {{ $order->payment->transaction_code ?? '---' }}


                                    </span>
                                </div>

                            </div>

                            <div class="order-meta mt-2">

                                <div>
                                    <strong>Số tiền:</strong>
                                    {{ number_format($order->payment->amount) }} đ
                                </div>

                                <div>
                                    <strong>Thời gian thanh toán:</strong>
                                    {{ $order->payment->paid_at ? $order->payment->paid_at->format('d/m/Y H:i') : '---' }}

                                </div>
                            </div>
                        @endif

                        <div class="order-meta mt-2">

                            <div>
                                <strong>Mã đơn:</strong> #{{ $order->id }}
                            </div>

                            @if($order->payment && $order->payment->refund_status == 'completed')

                                <div class="alert alert-success mt-3">
                                    Đơn hàng đã được hoàn tiền thành công.
                                </div>

                            @endif

                            @if($order->status == 'refund_requested')

                                <div class="alert alert-warning mt-3">
                                    Yêu cầu hoàn tiền đang được xử lý.
                                </div>

                            @endif
                        </div>


                        <div class="alert alert-info mt-3">

                            @if($order->status == 'pending')

                                Đơn hàng sẽ được <b>xác nhận trong 1 - 2 ngày</b>.
                                Vui lòng chờ cửa hàng xử lý đơn hàng của bạn.

                            @elseif($order->status == 'confirmed')

                                Đơn hàng đã được xác nhận.
                                Thời gian giao hàng dự kiến <b>3 - 5 ngày</b>.

                            @elseif($order->status == 'shipping')

                                Đơn hàng đang được giao.
                                Vui lòng chú ý điện thoại để nhận hàng.

                            @elseif($order->status == 'completed')

                                Đơn hàng đã giao thành công.
                                Nếu có vấn đề, bạn có thể <b>yêu cầu hoàn tiền</b>.


                            @elseif($order->status == 'refund_requested')

                                Yêu cầu hoàn tiền đang được xử lý.
                                Vui lòng chờ cửa hàng phản hồi.

                            @elseif($order->status == 'refunded')

                                Đơn hàng đã được hoàn tiền thành công.

                            @endif


                        </div>

                        @if(in_array($order->status, ['completed', 'refund_requested', 'refunded']))
                            <div class="order-card mb-4 return-policy-card">
                                <h5 class="mb-3">Chính sách hoàn trả sau khi đã nhận hàng</h5>

                                <ul class="return-policy-list">
                                    <li>Đơn hàng chỉ được gửi yêu cầu hoàn tiền sau khi đã ở trạng thái hoàn thành.</li>
                                    <li>Yêu cầu hoàn tiền cần có lý do; bạn có thể bổ sung mô tả và hình ảnh để đối soát nhanh hơn.</li>
                                    <li>Yêu cầu sẽ chuyển sang trạng thái đang xử lý hoàn tiền và chờ quản trị viên duyệt.</li>
                                    <li>Nếu được duyệt, đơn sẽ chuyển sang trạng thái đã hoàn tiền.</li>
                                    <li>Nếu bị từ chối, đơn sẽ quay về trạng thái trước đó theo kết quả xử lý của cửa hàng.</li>
                                </ul>

                                @if(
                                    $order->status == 'completed'
                                    && $order->payment
                                    && $order->payment->status == 'paid'
                                    && !$order->payment->refund_status
                                )
                                    <div class="alert alert-success mt-3 mb-0">
                                        Đơn hàng của bạn hiện đủ điều kiện để gửi yêu cầu hoàn tiền nếu phát sinh vấn đề.
                                    </div>
                                @elseif($order->status == 'refund_requested')
                                    <div class="alert alert-warning mt-3 mb-0">
                                        Bạn đã gửi yêu cầu hoàn tiền. Cửa hàng đang kiểm tra và phản hồi sớm.
                                    </div>
                                @elseif($order->status == 'refunded')
                                    <div class="alert alert-info mt-3 mb-0">
                                        Yêu cầu hoàn tiền đã hoàn tất cho đơn hàng này.
                                    </div>
                                @endif
                            </div>
                        @endif

                        @if(in_array($order->status, ['confirmed']))
                            <div class="order-card mb-4">

                                <h5 class="mb-3">Thông tin vận chuyển</h5>

                                <p>
                                    <strong>Đơn vị vận chuyển:</strong> Giao hàng nhanh (Demo)
                                </p>

                                <p>
                                    <strong>Dự kiến giao:</strong>
                                    {{ $order->created_at->addDays(3)->format('d/m/Y') }} -
                                    {{ $order->created_at->addDays(5)->format('d/m/Y') }}
                                </p>

                                <p>
                                    <strong>Trạng thái:</strong>
                                    @if($order->status == 'confirmed')
                                        Đang chuẩn bị hàng tại kho
                                    @elseif($order->status == 'shipping')
                                        Đang giao đến bạn
                                    @endif
                                </p>

                            </div>
                        @endif


                        <div class="order-card mb-4">

                            <h5 class="mb-3">Lịch sử đơn hàng</h5>

                            <ul class="order-history">

                                <li>
                                    <span>{{ $order->created_at->format('d/m/Y H:i') }}</span>
                                    <p>Đặt hàng thành công</p>
                                </li>

                                @if(!empty($orderHistoryNotifications) && $orderHistoryNotifications->isNotEmpty())
                                    @foreach($orderHistoryNotifications as $historyNoti)
                                        <li>
                                            <span>{{ $historyNoti->created_at->format('d/m/Y H:i') }}</span>
                                            <p>{{ $historyNoti->display_content }}</p>
                                        </li>
                                    @endforeach
                                @else
                                    @if(in_array($order->status, ['confirmed', 'shipping', 'completed']))
                                        <li>
                                            <span>{{ $order->updated_at->format('d/m/Y H:i') }}</span>
                                            <p>Đơn hàng đã được xác nhận</p>
                                        </li>
                                    @endif

                                    @if(in_array($order->status, ['shipping', 'completed']))
                                        <li>
                                            <span>{{ $order->updated_at->format('d/m/Y H:i') }}</span>
                                            <p>Đơn hàng đang được giao</p>
                                        </li>
                                    @endif

                                    @if($order->status == 'completed')
                                        <li>
                                            <span>{{ $order->updated_at->format('d/m/Y H:i') }}</span>
                                            <p>Đơn hàng đã giao thành công</p>
                                        </li>
                                    @endif

                                    @if($order->status == 'refund_requested')
                                        <li>
                                            <span>{{ $order->updated_at->format('d/m/Y H:i') }}</span>
                                            <p>Yêu cầu hoàn tiền đang được xử lý</p>
                                        </li>
                                    @endif

                                    @if($order->status == 'refunded')
                                        <li>
                                            <span>{{ $order->payment->refund_at?->format('d/m/Y H:i') }}</span>
                                            <p>Đơn hàng đã được hoàn tiền</p>
                                        </li>
                                    @endif
                                @endif


                            </ul>

                        </div>



                    </div>

                    @if(!in_array($order->status, ['cancelled']))

                        <div class="order-card mb-4">

                            <h5>Trạng thái đơn hàng</h5>

                            <div class="order-timeline">

                                {{-- Đặt hàng --}}
                                <div
                                    class="timeline-item 
                                                                                                                                                                                    {{ in_array($order->status, ['pending', 'confirmed', 'shipping', 'completed', 'cancelled', 'refund_requested', 'refunded']) ? 'active' : '' }}">
                                    <div class="timeline-dot"></div>
                                    <p>Đặt hàng</p>
                                </div>

                                {{-- Xác nhận --}}
                                <div
                                    class="timeline-item 
                                                                                                                                                                                    {{ in_array($order->status, ['confirmed', 'shipping', 'completed', 'cancelled', 'refund_requested', 'refunded']) ? 'active' : '' }}">
                                    <div class="timeline-dot"></div>
                                    <p>Xác nhận</p>
                                </div>

                                {{-- Đang giao --}}
                                <div
                                    class="timeline-item 
                                                                                                                                                                                    {{ in_array($order->status, ['shipping', 'completed', 'refund_requested', 'refunded']) ? 'active' : '' }}">
                                    <div class="timeline-dot"></div>
                                    <p>Đang giao</p>
                                </div>

                                {{-- Step cuối --}}
                                <div
                                    class="timeline-item 
                                                                                                                                                                                    {{ in_array($order->status, ['completed', 'cancelled', 'refund_requested', 'refunded']) ? 'active' : '' }}">
                                    <div class="timeline-dot"></div>

                                    <p>
                                        @if($order->status == 'cancelled')
                                            Đã huỷ
                                        @elseif($order->status == 'refund_requested')
                                            Chờ hoàn tiền
                                        @elseif($order->status == 'refunded')
                                            Đã hoàn tiền
                                        @else
                                            Hoàn thành
                                        @endif
                                    </p>

                                </div>

                            </div>


                        </div>

                    @endif

                    @if($order->status == 'cancelled' && $order->cancellation)

                        <div class="order-card mb-4 cancel-info">

                            <h5 class="mb-3">Thông tin huỷ đơn</h5>

                            @php
                                $reasonText = [
                                    'change_mind' => 'Không muốn mua nữa',
                                    'wrong_product' => 'Chọn nhầm sản phẩm',
                                    'wrong_address' => 'Sai địa chỉ giao hàng',
                                    'found_cheaper' => 'Tìm được nơi rẻ hơn',
                                    'delivery_too_long' => 'Thời gian giao quá lâu',
                                    'other' => 'Lý do khác',
                                    'admin_cancel' => 'Huỷ bởi quản trị viên'
                                ];
                            @endphp

                            <p>
                                <strong>Người huỷ:</strong>
                                {{ $order->cancellation->cancelled_by == 'admin' ? 'Quản trị viên' : 'Khách hàng' }}
                            </p>

                            <p>
                                <strong>Lý do:</strong>

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


                            <p>
                                <strong>Thời gian huỷ:</strong>
                                {{ \Carbon\Carbon::parse($order->cancellation->cancelled_at)->format('d/m/Y H:i') }}
                            </p>

                        </div>

                    @endif


                    <!-- SHIPPING INFO -->
                    <div class="order-card mb-4">

                        <h5 class="mb-3">Thông tin giao hàng</h5>

                        <p><strong>Người nhận:</strong> {{ $order->receiver_name }}</p>

                        <p><strong>SĐT:</strong> {{ $order->receiver_phone }}</p>

                        <p><strong>Địa chỉ:</strong> {{ $order->shipping_address }}</p>

                        @if($order->note)
                            <p><strong>Ghi chú:</strong> {{ $order->note }}</p>
                        @endif
                    </div>



                    <!-- PRODUCT LIST -->

                    <div class="order-card">

                        <h5 class="mb-3">Sản phẩm</h5>

                        @foreach($order->items as $item)
                                        @php
                                            $product = $item->variant->product;
                                            $image = $item->variant->images->first() ?? $product->images->first();
                                        @endphp
                                        <div class="order-product">

                                            <img src="{{ $image ? asset('storage/' . $image->image_path) : asset('img/no-image.png') }}"
                                                width="70">

                                            <div class="product-info">

                                                <div class="product-name">
                                                    {{ $product->name }}
                                                </div>

                                                <div class="product-variant">
                                                    Phân loại:
                                                    {{ $item->variant?->size
                            ?? $item->variant?->volume
                            ?? $item->variant?->weight
                            ?? $item->variant?->color
                            ?? '---' }}

                                                </div>

                                                <div class="product-qty">
                                                    x{{ $item->quantity }}
                                                </div>

                                            </div>

                                            @if($order->status == 'completed')
                                                <div class="mt-2">
                                                    <a href="{{ route('reviews.form', ['product' => $product->id, 'order' => $order->id]) }}"
                                                        class="btn btn-sm btn-outline-primary">Đánh giá</a>
                                                </div>
                                            @endif

                                            <div class="product-price">

                                                <div>
                                                    {{ number_format($item->price) }} đ
                                                </div>

                                                <div class="product-subtotal">
                                                    {{ number_format($item->subtotal) }} đ
                                                </div>

                                            </div>

                                        </div>

                        @endforeach

                        @php
                            // Lấy tổng tiền sản phẩm
                            $subtotal = $order->items->sum('subtotal');

                            // Phí vận chuyển (giả sử order có trường shipping_fee)
                            $shipping = $order->shipping_fee ?? 0;

                            // Giảm giá (giả sử order có trường discount_amount)
                            $discount = $order->discount_amount ?? 0;

                            // Tổng thanh toán thực tế
                            $total = $subtotal + $shipping - $discount;
                        @endphp

                        <div class="order-summary">
                            <div>Tạm tính: {{ number_format($subtotal) }} đ</div>
                            <div>Phí vận chuyển: {{ number_format($shipping) }} đ</div>
                            <div>Giảm giá: {{ number_format($discount) }} đ</div>
                            <hr>
                            <div style="font-size:22px">
                                Tổng thanh toán:
                                <span>
                                    {{ number_format($total) }} đ
                                </span>
                            </div>
                        </div>

                    </div>

                    <div class="text-end mt-4">

                        <a href="{{ route('orders.my') }}" class="btn-back">
                            Quay lại đơn mua
                        </a>

                        {{-- Huỷ đơn khi pending --}}
                        @if($order->status == 'pending')
                            <button class="btn-cancel" onclick="cancelOrder({{ $order->id }})">
                                Huỷ đơn hàng
                            </button>
                        @endif

                        {{-- Xác nhận đã nhận khi đang giao --}}
                        @if($order->status == 'shipping')

                            <form method="POST" action="{{ route('orders.received', $order->id) }}" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn btn-success">
                                    Đã nhận hàng
                                </button>
                            </form>

                        @endif

                        {{-- Hoàn tiền --}}
                        @if(
                                $order->status == 'completed'
                                && $order->payment
                                && $order->payment->status == 'paid'
                                && !$order->payment->refund_status
                            )
                            <button class="btn btn-warning" onclick="showRefundModal()">Yêu cầu hoàn tiền</button>

                            <!-- Refund Modal -->
                            <div id="refundModal" class="refund-modal-overlay" style="display:none;">
                                <div class="refund-modal-card">
                                    <button type="button" class="refund-modal-close" onclick="closeRefundModal()"
                                        aria-label="Đóng">&times;</button>

                                    <h5 class="refund-modal-title">Yêu cầu hoàn tiền</h5>
                                    <p class="refund-modal-subtitle">Vui lòng cung cấp thông tin để cửa hàng xử lý nhanh hơn.
                                    </p>

                                    <form method="POST" action="{{ route('orders.refund', $order->id) }}"
                                        enctype="multipart/form-data">
                                        @csrf
                                        <div class="mb-3">
                                            <label class="refund-label">Lý do hoàn hàng <span
                                                    style="color:red;">*</span></label>
                                            <select name="reason" class="form-control" required>
                                                <option value="">Chọn lý do</option>
                                                <option value="wrong_product">Nhận nhầm sản phẩm</option>
                                                <option value="product_defect">Sản phẩm lỗi</option>
                                                <option value="other">Khác</option>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label class="refund-label">Mô tả thêm (tùy chọn)</label>
                                            <textarea name="description" class="form-control" rows="3"></textarea>
                                        </div>

                                        <div class="mb-3">
                                            <label class="refund-label">Hình ảnh (tối đa 5 ảnh)</label>
                                            <input type="file" name="images[]" class="form-control" accept="image/*" multiple>
                                        </div>

                                        <div class="refund-actions">
                                            <button type="button" class="btn btn-outline-secondary"
                                                onclick="closeRefundModal()">Hủy</button>
                                            <button type="submit" class="btn btn-warning">Gửi yêu cầu</button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <style>
                                .refund-modal-overlay {
                                    position: fixed;
                                    inset: 0;
                                    background: rgba(0, 0, 0, 0.45);
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                    z-index: 2000;
                                    padding: 16px;
                                }

                                .refund-modal-card {
                                    width: 100%;
                                    max-width: 560px;
                                    background: #fff;
                                    border-radius: 14px;
                                    box-shadow: 0 16px 36px rgba(0, 0, 0, 0.2);
                                    padding: 22px;
                                    position: relative;
                                }

                                .refund-modal-title {
                                    margin-bottom: 6px;
                                    font-weight: 700;
                                }

                                .refund-modal-subtitle {
                                    margin-bottom: 16px;
                                    color: #666;
                                    font-size: 14px;
                                }

                                .refund-modal-close {
                                    position: absolute;
                                    top: 8px;
                                    right: 12px;
                                    border: none;
                                    background: transparent;
                                    font-size: 28px;
                                    line-height: 1;
                                    color: #888;
                                    cursor: pointer;
                                }

                                .refund-label {
                                    font-weight: 600;
                                    margin-bottom: 6px;
                                    display: inline-block;
                                }

                                .refund-actions {
                                    display: flex;
                                    justify-content: flex-end;
                                    gap: 10px;
                                    margin-top: 4px;
                                }
                            </style>

                            <script>
                                function showRefundModal() {
                                    document.getElementById('refundModal').style.display = 'block';
                                }
                                function closeRefundModal() {
                                    document.getElementById('refundModal').style.display = 'none';
                                }

                                document.addEventListener('click', function (e) {
                                    const modal = document.getElementById('refundModal');
                                    if (!modal || modal.style.display === 'none') return;
                                    if (e.target === modal) {
                                        closeRefundModal();
                                    }
                                });

                                document.addEventListener('keydown', function (e) {
                                    if (e.key === 'Escape') {
                                        closeRefundModal();
                                    }
                                });
                            </script>
                        @endif


                        {{-- Thanh toán lại VNPay/MoMo nếu chưa thanh toán hoặc thất bại --}}
                        @if($order->payment && in_array($order->payment->status, ['pending', 'failed']))
                            @if(in_array($order->payment->method, ['VNPAY', 'MOMO']))
                                <a href="{{ route(strtolower($order->payment->method) . '.pay', $order->id) }}"
                                    class="btn btn-primary">
                                    Thanh toán lại {{ strtoupper($order->payment->method) }}
                                </a>
                            @endif
                        @endif

                    </div>


                    <form id="cancelForm" method="POST" action="{{ route('orders.cancel', $order->id) }}">
                        @csrf
                        <input type="hidden" name="reason" id="cancel_reason">
                    </form>
                    <script>

                        function cancelOrder(orderId) {

                            Swal.fire({

                                title: 'Huỷ đơn hàng',

                                text: 'Vui lòng chọn lý do huỷ đơn',

                                input: 'select',

                                inputOptions: {
                                    change_mind: 'Không muốn mua nữa',
                                    wrong_product: 'Chọn nhầm sản phẩm',
                                    wrong_address: 'Sai địa chỉ giao hàng',
                                    found_cheaper: 'Tìm được nơi rẻ hơn',
                                    delivery_too_long: 'Thời gian giao quá lâu',
                                    other: 'Lý do khác'
                                },

                                inputPlaceholder: 'Chọn lý do',

                                showCancelButton: true,

                                confirmButtonText: 'Huỷ đơn',

                                cancelButtonText: 'Đóng',

                                confirmButtonColor: '#ee4d2d',

                                cancelButtonColor: '#6c757d',

                                inputValidator: (value) => {
                                    if (!value) {
                                        return 'Bạn phải chọn lý do huỷ!'
                                    }
                                }

                            }).then((result) => {

                                if (result.isConfirmed) {

                                    document.getElementById('cancel_reason').value = result.value;

                                    document.getElementById('cancelForm').submit();

                                }

                            });

                        }

                    </script>


                </div>

            </div>

        </div>
    </section>

@endsection