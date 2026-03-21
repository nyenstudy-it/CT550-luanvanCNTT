@extends('layout')

@section('hero')
@include('pages.components.hero', ['showBanner' => false, 'heroNormal' => true])
@endsection

@section('content')

                <section class="checkout spad">
                <div class="container">

                @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if ($errors->any())
                <div class="alert alert-danger">
                <ul class="mb-0">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
                </ul>
                </div>
                @endif


                <form action="{{ route('checkout.store') }}" method="POST">
                @csrf

                <div class="row">

                {{-- ================= LEFT SIDE ================= --}}

                <div class="col-lg-8">

                {{-- ===== THÔNG TIN GIAO HÀNG ===== --}}

                <div class="checkout-box mb-4">

                <h4 class="mb-4">Thông tin giao hàng</h4>

                <div class="row">

                <div class="col-md-6 mb-3">
                <label>Họ tên người nhận *</label>

                <input
                type="text"
                name="receiver_name"
                class="form-control"
                value="{{ old('receiver_name', auth()->user()->name ?? '') }}"
                required>

                </div>


                <div class="col-md-6 mb-3">

                <label>Số điện thoại *</label>

                <input
                type="text"
                name="receiver_phone"
                class="form-control"
                value="{{ old('receiver_phone', auth()->user()?->customer?->phone ?? '') }}"
                required>

                </div>

                </div>


                <div class="mb-3">

                <label>Địa chỉ giao hàng *</label>

                <textarea name="shipping_address" class="form-control" rows="3"
                    required>{{ old('shipping_address', optional(auth()->user()->customer)->full_address) }}</textarea>

                <small class="text-muted">
                Địa chỉ mặc định được lấy từ hồ sơ khách hàng. Bạn có thể chỉnh sửa nếu muốn.
                </small>

                <br>

                <a href="{{ route('customer.profile', ['redirect' => 'checkout']) }}" class="text-success">
                    Cập nhật địa chỉ trong hồ sơ
                </a>

                </div>


                <div class="mb-3">

                <label>Ghi chú (không bắt buộc)</label>

                <textarea
                name="note"
                class="form-control"
                rows="2">{{ old('note') }}</textarea>

                </div>

                </div>


                {{-- ===== DANH SÁCH SẢN PHẨM ===== --}}

                <div class="checkout-box">

                <h4 class="mb-4">Đơn hàng của bạn</h4>

                <table class="table">

                <thead>
                <tr>
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
                @endphp

                <tr>

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


                {{-- ================= RIGHT SIDE ================= --}}

                <div class="col-lg-4">

                <div class="checkout-box">

                <h4 class="mb-4">Thanh toán</h4>

        @php
            $discountValue = session('cart_discount', 0);
            $discountType = session('cart_discount_type', 'fixed');
            $discountCode = session('cart_discount_code');

            $totalDiscount = $discountType == 'percent'
                ? $total * $discountValue / 100
                : $discountValue;

            $finalTotal = $total + $shippingFee - $totalDiscount;
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

            {{-- ✅ HIỂN THỊ MÃ GIẢM GIÁ --}}
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
                        -{{ number_format($totalDiscount) }} đ
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


                {{-- ===== PHƯƠNG THỨC THANH TOÁN ===== --}}

                <div class="mb-3">

                <label class="fw-bold">Phương thức thanh toán</label>


                <div class="form-check mt-2">

                <input
                class="form-check-input"
                type="radio"
                name="payment_method"
                value="COD"
                {{ old('payment_method', 'COD') == 'COD' ? 'checked' : '' }}>

                <label class="form-check-label">
                💵 Thanh toán khi nhận hàng (COD)
                </label>

                </div>


                <div class="form-check">

                <input
                class="form-check-input"
                type="radio"
                name="payment_method"
                value="VNPAY"
                {{ old('payment_method') == 'VNPAY' ? 'checked' : '' }}>

                <label class="form-check-label">
                🏦 Thanh toán qua VNPAY
                </label>

                </div>


                <div class="form-check">

                <input
                class="form-check-input"
                type="radio"
                name="payment_method"
                value="MOMO"
                {{ old('payment_method') == 'MOMO' ? 'checked' : '' }}>

                <label class="form-check-label">
                📱 Thanh toán ví MoMo
                </label>

                </div>


                <div class="form-check">

                <input
                class="form-check-input"
                type="radio"
                name="payment_method"
                value="BANK_TRANSFER"
                {{ old('payment_method') == 'BANK_TRANSFER' ? 'checked' : '' }}>

                <label class="form-check-label">
                🏧 Chuyển khoản ngân hàng
                </label>

                </div>
                </div>


                <button type="submit" class="checkout-btn">
                XÁC NHẬN ĐẶT HÀNG
                </button>

                </div>

                </div>

                </div>

                </form>

                </div>
                </section>

@endsection
