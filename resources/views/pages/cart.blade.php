
@extends('layout')

@section('hero')
    @include('pages.components.hero', ['showBanner' => false, 'heroNormal' => true])
@endsection

@section('content')

                <section class="shoping-cart spad">
                    <div class="container">

                        {{-- Thông báo --}}
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show auto-dismiss" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show auto-dismiss" role="alert">
                                {{ session('error') }}
                                <button type="button" class="close" data-dismiss="alert">
                                    <span>&times;</span>
                                </button>
                            </div>
                        @endif


                        <div class="row">
                            <div class="col-lg-12">
                                <div class="shoping__cart__table">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th width="120">Ảnh</th>
                                                <th>Tên sản phẩm</th>
                                                <th class="text-center">Đơn giá</th>
                                                <th class="text-center">Số lượng</th>
                                                <th class="text-center">Thành tiền</th>
                                                <th class="text-center">Xóa</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            @php $total = 0; @endphp

                                            @if(empty($cart) || count($cart) == 0)
                                                <tr>
                                                    <td colspan="6" class="text-center py-5">
                                                        <h5>🛒 Giỏ hàng của bạn đang trống</h5>
                                                    </td>
                                                </tr>
                                            @else

                                                @foreach($cart as $variantId => $item)

                                                                                    @php
            $price = $item['price'] ?? 0;
            $quantity = $item['quantity'] ?? 1;
            $itemTotal = $price * $quantity;
            $total += $itemTotal;

            $image = !empty($item['image'])
                ? asset('storage/' . $item['image'])
                : asset('images/no-image.png');
                                                                                    @endphp

                                                                                    <tr>

                                                                                        {{-- Ảnh --}}
                                                                                        <td class="text-center">
                                                                                            <img src="{{ $image }}" width="90" style="border-radius:12px; object-fit:cover;">
                                                                                        </td>

                                                                                        {{-- Tên --}}
                                                                                        <td>
                                                                                            <h6 style="font-weight:600; margin-bottom:6px;">
                                                                                                {{ $item['name'] ?? '' }}
                                                                                            </h6>

                                                                                            <div style="font-size:14px; color:#777;">
                                                                                                {{ $item['variant'] ?? 'Phiên bản mặc định' }}
                                                                                            </div>

                                                                                            <div style="font-size:12px; color:#aaa;">
                                                                                                Mã: #{{ $variantId }}
                                                                                            </div>
                                                                                        </td>

                                                                                        {{-- Đơn giá --}}
                                                                                        <td class="text-center">
                                                                                            <strong>{{ number_format($price) }} đ</strong>
                                                                                        </td>

                                                                                        {{-- Số lượng kiểu Shopee --}}
                                                                                        <td class="text-center">
                                                                                            <form action="{{ route('cart.update') }}" method="POST" class="qty-form">
                                                                                                @csrf
                                                                                                <input type="hidden" name="variant_id" value="{{ $variantId }}">

                                                                                                <div class="qty-wrapper">
                                                                                                    <button type="button" class="qty-btn minus">−</button>

                                                                                                    <input type="number" name="quantity" value="{{ $quantity }}" min="1" max="{{ $item['stock'] ?? 1 }}"
                                                                                                        class="qty-input">

                                                                                                    <button type="button" class="qty-btn plus">+</button>
                                                                                                </div>
                                                                                            </form>
                                                                                        </td>

                                                                                        {{-- Thành tiền --}}
                                                                                        <td class="text-center">
                                                                                            <strong style="color:#ee4d2d; font-size:16px;">
                                                                                                {{ number_format($itemTotal) }} đ
                                                                                            </strong>
                                                                                        </td>

                                                                                        {{-- Xóa --}}
                                                                                        <td class="text-center">
                                                                                            <form action="{{ route('cart.remove') }}" method="POST">
                                                                                                @csrf
                                                                                                <input type="hidden" name="variant_id" value="{{ $variantId }}">
                                                                                                <button type="submit"
                                                                                                    style="border:none;background:none;color:#999;font-size:20px;">
                                                                                                    <span class="icon_close"></span>
                                                                                                </button>
                                                                                            </form>
                                                                                        </td>

                                                                                    </tr>

                                                @endforeach
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    {{-- Cart Total --}}
                    <div class="row mt-4">
                        <div class="col-lg-6"></div>

                        <div class="col-lg-6">
                            <div class="shoping__checkout p-3" style="border:1px solid #ddd; border-radius:12px; background:#fff;">

                                <h5 class="mb-3">Tổng giỏ hàng</h5>

                                {{-- ================= MÃ GIẢM GIÁ ================= --}}
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Mã giảm giá</label>

                                    <form action="{{ route('cart.apply_discount') }}" method="POST" class="d-flex gap-2">
                                        @csrf

                                        <select name="code" class="form-select">
                                            <option value="">-- Không sử dụng mã --</option>

                                            @foreach($discounts as $d)
                                                                                <option value="{{ $d->code }}" {{ session('cart_discount_code') == $d->code ? 'selected' : '' }}>
                                                                                    {{ $d->code }} -
                                                                                    {{ $d->type == 'percent'
                                                ? $d->value . '%'
                                                : number_format($d->value) . ' đ' }}
                                                                                </option>
                                            @endforeach
                                        </select>

                                        <button type="submit" class="btn btn-success">
                                            Áp dụng
                                        </button>
                                    </form>

                                    {{-- trạng thái --}}
                                    @if(!empty(session('cart_discount_code')))
                                        <div class="mt-2 text-success">
                                            ✔ Đang áp dụng: <strong>{{ session('cart_discount_code') }}</strong>
                                        </div>
                                    @else
                                        <div class="mt-2 text-muted">
                                            Chưa áp dụng mã giảm giá
                                        </div>
                                    @endif
                                </div>


                                {{-- ================= TÍNH TIỀN ================= --}}
                                @php
    $discountAmount = 0;

    if (!empty(session('cart_discount_code'))) {

        $value = session('cart_discount');
        $type = session('cart_discount_type');

        if ($type == 'percent') {
            $discountAmount = $total * $value / 100;
        } else {
            $discountAmount = $value;
        }

        // 🔥 chống giảm quá tiền
        $discountAmount = min($discountAmount, $total);
    }

    $finalTotal = $total + $shippingFee - $discountAmount;
                                @endphp

                                <ul class="list-unstyled">
                                    <li class="d-flex justify-content-between mb-2">
                                        <span>Tổng tiền sản phẩm</span>
                                        <span>{{ number_format($total) }} đ</span>
                                    </li>

                                    <li class="d-flex justify-content-between mb-2">
                                        <span>Phí vận chuyển</span>
                                        <span>{{ number_format($shippingFee) }} đ</span>
                                    </li>

                                    <li class="d-flex justify-content-between mb-2">
                                        <span>Giảm giá</span>
                                        <span style="color:#1abc9c;">
                                            -{{ number_format($discountAmount) }} đ
                                        </span>
                                    </li>

                                    <li class="d-flex justify-content-between mt-3 fw-bold" style="font-size:1.2rem;">
                                        <span>Tổng cộng</span>
                                        <span style="color:#ee4d2d;">
                                            {{ number_format($finalTotal) }} đ
                                        </span>
                                    </li>
                                </ul>

                                {{-- ================= BUTTON ================= --}}
                                @if(!empty($cart) && count($cart) > 0)
                                    <a href="{{ route('checkout') }}" class="primary-btn mt-3"
                                        style="background:#7fad39; display:block; text-align:center; border-radius:8px;">
                                        TIẾN HÀNH THANH TOÁN
                                    </a>
                                @else
                                    <button class="primary-btn mt-3" disabled style="background:#ccc; width:100%; border-radius:8px;">
                                        Giỏ hàng trống
                                    </button>
                                @endif

                            </div>
                        </div>

                    </div>
                </section>
        <script>
            document.addEventListener("DOMContentLoaded", function () {

                // ================= QUANTITY =================
                document.querySelectorAll(".qty-form").forEach(function (form) {

                    const minus = form.querySelector(".minus");
                    const plus = form.querySelector(".plus");
                    const input = form.querySelector(".qty-input");

                    if (!minus || !plus || !input) return;

                    minus.addEventListener("click", function () {
                        let value = parseInt(input.value);
                        if (value > 1) {
                            input.value = value - 1;
                            form.submit();
                        }
                    });

                    plus.addEventListener("click", function () {
                        let value = parseInt(input.value);
                        let max = parseInt(input.getAttribute("max"));

                        if (value < max) {
                            input.value = value + 1;
                            form.submit();
                        } else {
                            alert("Sản phẩm chỉ còn " + max + " sản phẩm trong kho");
                        }
                    });

                    input.addEventListener("change", function () {
                        if (input.value < 1) input.value = 1;
                        form.submit();
                    });
                });

                // ================= DISCOUNT =================
                const discountSelect = document.getElementById("discount-code");

                // ================= AUTO DISMISS ALERT =================
                setTimeout(function () {
                    document.querySelectorAll(".auto-dismiss").forEach(function (alert) {
                        alert.classList.remove("show");
                        alert.classList.add("fade");
                        setTimeout(() => alert.remove(), 500);
                    });
                }, 3000);

            });
        </script>

@endsection