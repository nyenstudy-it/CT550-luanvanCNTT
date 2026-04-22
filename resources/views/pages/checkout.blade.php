@extends('layout')

@section('hero')
    @include('pages.components.hero', ['showBanner' => false, 'heroNormal' => true])
@endsection

@section('content')

            <!-- Breadcrumb Section Begin -->
            <section class="breadcrumb-section set-bg" data-setbg="{{ asset('frontend/images/breadcrumb.jpg') }}">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-12 text-center">
                            <div class="breadcrumb__text">
                                <h2>Thanh toán</h2>
                                <div class="breadcrumb__option">
                                    <a href="{{ route('pages.home') }}">Trang chủ</a>
                                    <span>Thanh toán</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <!-- Breadcrumb Section End -->

        <style>
            .policy-modal-overlay {
                position: fixed;
                inset: 0;
                background: rgba(0, 0, 0, 0.45);
                display: none;
                align-items: center;
                justify-content: center;
                z-index: 2050;
                padding: 16px;
            }

            .policy-modal-card {
                width: 100%;
                max-width: 760px;
                max-height: 90vh;
                overflow: auto;
                background: #fff;
                border-radius: 14px;
                box-shadow: 0 18px 40px rgba(0, 0, 0, 0.22);
            }

            .policy-modal-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 14px 18px;
                border-bottom: 1px solid #ececec;
            }

            .policy-modal-title {
                margin: 0;
                font-size: 20px;
                font-weight: 700;
            }

            .policy-close-btn {
                border: none;
                background: transparent;
                font-size: 28px;
                line-height: 1;
                color: #666;
                cursor: pointer;
            }

            .policy-modal-body {
                padding: 18px 22px 10px;
                text-align: left;
            }

            .policy-rules {
                margin: 0;
                padding-left: 20px;
            }

            .policy-rules li {
                margin-bottom: 10px;
                line-height: 1.5;
            }

            .policy-modal-footer {
                display: flex;
                justify-content: flex-end;
                gap: 10px;
                padding: 12px 18px 18px;
                border-top: 1px solid #ececec;
            }
        </style>

        <section class="checkout spad">
            <div class="container">

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <h5 class="alert-heading">Lỗi xác thực:</h5>
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <form id="checkoutForm" action="{{ route('checkout.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="order_policy_agree" id="orderPolicyAgreeInput" value="{{ old('order_policy_agree', '0') }}">

                    <div class="row">

                        <div class="col-lg-8">

                            <div class="checkout-box mb-4">

                                <h4 class="mb-4">Thông tin giao hàng</h4>

                                <div class="row">

                                    <div class="col-md-6 mb-3">
                                        <label>Họ tên người nhận *</label>

                                        <input type="text" name="receiver_name" class="form-control"
                                            value="{{ old('receiver_name', auth()->user()->name ?? '') }}" required>

                                    </div>


                                    <div class="col-md-6 mb-3">

                                        <label>Số điện thoại *</label>

                                        <input type="text" name="receiver_phone" class="form-control"
                                            value="{{ old('receiver_phone', auth()->user()?->customer?->phone ?? '') }}"
                                            required>

                                    </div>

                                </div>


                                <div class="mb-3">

                                    @php
    $customer = auth()->user()?->customer;
    $savedFullAddress = trim((string) ($customer?->full_address ?? ''));
    $canUseDefaultAddress = (bool) ($customer?->is_default_address) && $savedFullAddress !== '';
    $defaultAddress = $canUseDefaultAddress ? $savedFullAddress : '';
    $initialShippingAddress = old('shipping_address', $defaultAddress);
                                    @endphp

                                    <label>Địa chỉ giao hàng *</label>

                                    @if($canUseDefaultAddress)
                                        <div class="mb-2 p-2"
                                            style="background:#f8fff0;border:1px solid #d8ebbf;border-radius:8px;">
                                            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                                <span class="badge bg-success">Mặc định</span>
                                                <small class="text-muted">{{ $defaultAddress }}</small>
                                            </div>
                                            <div class="d-flex flex-wrap gap-2">
                                                <button type="button" id="use-default-address"
                                                    class="btn btn-sm btn-outline-success">
                                                    Dùng địa chỉ mặc định
                                                </button>
                                                <button type="button" id="clear-shipping-address"
                                                    class="btn btn-sm btn-outline-secondary">
                                                    Nhập địa chỉ khác
                                                </button>
                                            </div>
                                        </div>
                                    @else
                                        <div class="mb-2 p-2"
                                            style="background:#fff8e1;border:1px solid #f3df9b;border-radius:8px;">
                                            <small class="text-muted">
                                                Bạn chưa có địa chỉ mặc định hoặc địa chỉ còn trống. Vui lòng nhập địa chỉ giao hàng
                                                hoặc cập nhật hồ sơ.
                                            </small>
                                        </div>
                                    @endif

                                    <textarea id="shipping_address" name="shipping_address" class="form-control" rows="3"
                                        required>{{ $initialShippingAddress }}</textarea>

                                    <small id="shipping-address-mode" class="text-muted">
                                        {{ $canUseDefaultAddress && $initialShippingAddress === $defaultAddress ? 'Đang dùng địa chỉ mặc định đã lưu.' : 'Bạn có thể nhập địa chỉ nhận hàng khác với địa chỉ mặc định.' }}
                                    </small>

                                    <br>

                                    <a href="{{ route('customer.profile', ['redirect' => 'checkout']) }}" class="text-success">
                                        Cập nhật địa chỉ trong hồ sơ
                                    </a>

                                </div>


                                <div class="mb-3">

                                    <label>Ghi chú (không bắt buộc)</label>

                                    <textarea name="note" class="form-control" rows="2">{{ old('note') }}</textarea>

                                </div>

                            </div>


                            <div class="checkout-box">

                                <h4 class="mb-4">Đơn hàng của bạn</h4>

                                <table class="table">

                                    <thead>
                                        <tr>
                                            <th width="100">Ảnh</th>
                                            <th>Sản phẩm</th>
                                            <th class="text-center">SL</th>
                                            <th class="text-end">Thành tiền</th>
                                        </tr>
                                    </thead>

                                    <tbody>

                                        @php
    $totalQty = 0;
                                        @endphp

                                        @foreach($cart as $item)

                                            @php
        $price = $item['price'];
        $quantity = $item['quantity'];
        $itemTotal = $price * $quantity;

        $totalQty += $quantity;
        // Check if image path is already a full URL or starts with 'frontend/'
        if (!empty($item['image']) && (strpos($item['image'], 'frontend/') === 0)) {
            $image = asset($item['image']);
        } elseif (!empty($item['image'])) {
            $image = asset('storage/' . $item['image']);
        } else {
            $image = asset('frontend/images/product/product-1.jpg');
        }
                                            @endphp

                                            <tr>

                                                <td class="text-center">
                                                    <img src="{{ $image }}" alt="{{ $item['name'] ?? 'Sản phẩm' }}" width="80"
                                                        height="80" style="border-radius:8px; object-fit:cover;"
                                                        onerror="this.src='{{ asset('frontend/images/product/product-1.jpg') }}';">
                                                </td>

                                                <td>
                                                    <strong>{{ $item['name'] }}</strong>

                                                    <br>

                                                    <small class="text-muted">
                                                        {{ $item['variant'] ?? 'Phiên bản mặc định' }}
                                                    </small>

                                                </td>


                                                <td class="text-center">
                                                    {{ $quantity }}
                                                </td>


                                                <td class="text-end">
                                                    <strong>{{ number_format($itemTotal) }} đ</strong>
                                                </td>

                                            </tr>

                                        @endforeach

                                    </tbody>
                                </table>

                            </div>

                        </div>


                        <div class="col-lg-4">

                            <div class="checkout-box">

                                <h4 class="mb-4">Thanh toán</h4>

                                @php
    $finalTotal = $total + $shippingFee - $discountAmount;
                                @endphp

                                <ul class="checkout-summary">
                                    <li>
                                        Tổng sản phẩm
                                        <span>{{ $totalQty }}</span>
                                    </li>

                                    <li>
                                        Tạm tính
                                        <span>{{ number_format($total) }} đ</span>
                                    </li>

                                    <li>
                                        Phí vận chuyển
                                        <span>{{ number_format($shippingFee) }} đ</span>
                                    </li>

                                    @if($discountCode)
                                        <li>
                                            Mã giảm giá
                                            <span style="color:#28a745; font-weight:500;">
                                                {{ $discountCode }}
                                            </span>
                                        </li>

                                        <li>
                                            Giảm giá
                                            <span style="color:#1abc9c;">
                                                -{{ number_format($discountAmount) }} đ
                                            </span>
                                        </li>
                                    @endif

                                    <li class="total">
                                        Tổng thanh toán
                                        <span style="color:#ee4d2d; font-weight:bold;">
                                            {{ number_format($finalTotal) }} đ
                                        </span>
                                    </li>
                                </ul>


                                <div class="mb-3">

                                    <label class="fw-bold">Phương thức thanh toán</label>


                                    <div class="form-check mt-2">

                                        <input class="form-check-input" type="radio" name="payment_method" value="COD" {{ old('payment_method', 'COD') == 'COD' ? 'checked' : '' }}>

                                        <label class="form-check-label">
                                            💵 Thanh toán khi nhận hàng (COD)
                                        </label>

                                    </div>


                                    <div class="form-check">

                                        <input class="form-check-input" type="radio" name="payment_method" value="VNPAY" {{ old('payment_method') == 'VNPAY' ? 'checked' : '' }}>

                                        <label class="form-check-label">
                                            🏦 Thanh toán qua VNPAY
                                        </label>

                                    </div>


                                    <div class="form-check">

                                        <input class="form-check-input" type="radio" name="payment_method" value="MOMO" {{ old('payment_method') == 'MOMO' ? 'checked' : '' }}>

                                        <label class="form-check-label">
                                            📱 Thanh toán ví MoMo
                                        </label>

                                    </div>
                                </div>


                                <button type="submit" class="checkout-btn" id="placeOrderBtn">
                                    XÁC NHẬN ĐẶT HÀNG
                                </button>

                            </div>

                        </div>

                    </div>

                </form>

                <div id="orderPolicyModal" class="policy-modal-overlay" style="display:none;" aria-hidden="true">
                    <div class="policy-modal-card" role="dialog" aria-modal="true" aria-labelledby="orderPolicyTitle">
                        <div class="policy-modal-header">
                            <h5 class="policy-modal-title" id="orderPolicyTitle">Chính sách đặt hàng và hoàn hàng/hoàn tiền</h5>
                            <button type="button" class="policy-close-btn" id="closePolicyModalBtn"
                                aria-label="Đóng">&times;</button>
                        </div>
                        <div class="policy-modal-body">
                            <div class="alert alert-light border mb-3" role="alert">
                                Vui lòng đọc kỹ chính sách dưới đây trước khi xác nhận đặt hàng.
                            </div>

                            <ul class="policy-rules">
                                @foreach(($orderPolicyRules ?? []) as $rule)
                                    <li>{{ $rule }}</li>
                                @endforeach
                            </ul>

                            <div class="form-check mt-3">
                                <input class="form-check-input" type="checkbox" id="orderPolicyAgree" value="1">
                                <label class="form-check-label" for="orderPolicyAgree">
                                    Tôi đã đọc và đồng ý với chính sách đặt hàng, hủy đơn và hoàn hàng/hoàn tiền.
                                </label>
                            </div>

                            <small id="orderPolicyError" class="text-danger d-none d-block mt-2">
                                Bạn cần tích đồng ý để tiếp tục đặt hàng.
                            </small>
                        </div>
                        <div class="policy-modal-footer">
                            <button type="button" class="btn btn-outline-secondary" id="closePolicyModalFooterBtn">Đóng</button>
                            <button type="button" class="btn btn-success" id="confirmPolicyBtn">Đồng ý và tiếp tục đặt
                                hàng</button>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const checkoutForm = document.getElementById('checkoutForm');
                const orderPolicyModalEl = document.getElementById('orderPolicyModal');
                const orderPolicyAgree = document.getElementById('orderPolicyAgree');
                const orderPolicyAgreeInput = document.getElementById('orderPolicyAgreeInput');
                const orderPolicyError = document.getElementById('orderPolicyError');
                const closePolicyModalBtn = document.getElementById('closePolicyModalBtn');
                const closePolicyModalFooterBtn = document.getElementById('closePolicyModalFooterBtn');
                const confirmPolicyBtn = document.getElementById('confirmPolicyBtn');

                let canSubmitOrder = false;

                function openPolicyModal() {
                    if (!orderPolicyModalEl) return;
                    orderPolicyError.classList.add('d-none');
                    orderPolicyAgree.checked = false;
                    orderPolicyModalEl.style.display = 'flex';
                    orderPolicyModalEl.setAttribute('aria-hidden', 'false');
                    document.body.style.overflow = 'hidden';
                }

                function closePolicyModal() {
                    if (!orderPolicyModalEl) return;
                    orderPolicyModalEl.style.display = 'none';
                    orderPolicyModalEl.setAttribute('aria-hidden', 'true');
                    document.body.style.overflow = '';
                    orderPolicyAgree.checked = false;
                }

                if (checkoutForm && orderPolicyModalEl) {
                    checkoutForm.addEventListener('submit', function (e) {
                        if (canSubmitOrder) {
                            canSubmitOrder = false;
                            return;
                        }

                        e.preventDefault();
                        openPolicyModal();
                    });
                }

                if (confirmPolicyBtn && checkoutForm && orderPolicyAgree) {
                    confirmPolicyBtn.addEventListener('click', function () {
                        if (!orderPolicyAgree.checked) {
                            orderPolicyError.classList.remove('d-none');
                            return;
                        }

                        if (orderPolicyAgreeInput) {
                            orderPolicyAgreeInput.value = '1';
                        }
                        
                        canSubmitOrder = true;
                        closePolicyModal();
                        
                        setTimeout(function() {
                            checkoutForm.submit();
                        }, 100);
                    });
                }

                if (closePolicyModalBtn) {
                    closePolicyModalBtn.addEventListener('click', function() {
                        canSubmitOrder = false;
                        closePolicyModal();
                    });
                }

                if (closePolicyModalFooterBtn) {
                    closePolicyModalFooterBtn.addEventListener('click', function() {
                        canSubmitOrder = false;
                        closePolicyModal();
                    });
                }

                if (orderPolicyModalEl) {
                    orderPolicyModalEl.addEventListener('click', function (event) {
                        if (event.target === orderPolicyModalEl) {
                            canSubmitOrder = false;
                            closePolicyModal();
                        }
                    });
                }

                document.addEventListener('keydown', function (event) {
                    if (event.key === 'Escape' && orderPolicyModalEl && orderPolicyModalEl.style.display !== 'none') {
                        canSubmitOrder = false;
                        closePolicyModal();
                    }
                });
            });
        </script>

@endsection