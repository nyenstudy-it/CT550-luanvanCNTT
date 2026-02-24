{{-- {{ dd($cart[13]) }} --}}

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
                        <div class="shoping__checkout">
                            <h5>Cart Total</h5>
                            <ul>
                                <li>Subtotal
                                    <span>{{ number_format($total) }} đ</span>
                                </li>
                                <li>Total
                                    <span style="color:#ee4d2d; font-weight:600;">
                                        {{ number_format($total) }} đ
                                    </span>
                                </li>
                            </ul>

                            @if(!empty($cart) && count($cart) > 0)
                                <a href="{{ route('checkout') }}" class="primary-btn"
                                    style="background:#7fad39 margin-top:15px; display:block; text-align:center;">
                                    TIẾN HÀNH THANH TOÁN
                                </a>
                            @else
                                <button class="primary-btn" disabled style="background:#ccc; margin-top:15px; width:100%;">
                                    Giỏ hàng trống
                                </button>
                            @endif

                        </div>
                    </div>
                </div>

            </div>
        </section>

        {{-- ================= CSS ================= --}}
        <style>
            .shoping__cart__table table tbody tr {
                transition: 0.2s;
            }

            .shoping__cart__table table tbody tr:hover {
                background: #fafafa;
            }

            .qty-wrapper {
                display: inline-flex;
                align-items: center;
                border: 1px solid #ddd;
                border-radius: 8px;
                overflow: hidden;
                background: #fff;
            }

            .qty-btn {
                width: 32px;
                height: 32px;
                border: none;
                background: #f5f5f5;
                font-size: 18px;
                cursor: pointer;
                transition: 0.2s;
            }

            .qty-btn:hover {
                background: #e0e0e0;
            }

            .qty-input {
                width: 50px;
                height: 32px;
                text-align: center;
                border: none;
                outline: none;
                font-weight: 600;
            }
        </style>

      <script>
    document.addEventListener("DOMContentLoaded", function () {

        document.querySelectorAll(".qty-form").forEach(function (form) {

            const minus = form.querySelector(".minus");
            const plus = form.querySelector(".plus");
            const input = form.querySelector(".qty-input");

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

        // ===== Auto dismiss alert =====
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