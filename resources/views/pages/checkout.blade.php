@extends('layout')

@section('hero')
    @include('pages.components.hero', ['showBanner' => false, 'heroNormal' => true])
@endsection

@section('content')

<section class="checkout spad">
    <div class="container">

        {{-- Thông báo --}}
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
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

            {{-- LEFT SIDE --}}
            <div class="col-lg-8">

                {{-- THÔNG TIN GIAO HÀNG --}}
                <div class="checkout-box mb-4">
                    <h4 class="mb-4">Thông tin giao hàng</h4>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Họ tên người nhận *</label>
                            <input type="text"
                                   name="receiver_name"
                                   class="form-control"
                                   value="{{ old('receiver_name', auth()->user()->name ?? '') }}"
                                   required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Số điện thoại *</label>
                            <input type="text"
                                   name="receiver_phone"
                                   class="form-control"
                                   value="{{ old('receiver_phone') }}"
                                   required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label>Địa chỉ giao hàng *</label>
                        <textarea name="shipping_address"
                                  class="form-control"
                                  rows="3"
                                  required>{{ old('shipping_address') }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label>Ghi chú (không bắt buộc)</label>
                        <textarea name="note"
                                  class="form-control"
                                  rows="2">{{ old('note') }}</textarea>
                    </div>
                </div>

                {{-- DANH SÁCH SẢN PHẨM --}}
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
                                $total = 0;
                                $totalQty = 0;
                            @endphp

                            @foreach($cart as $item)
                                @php
                                    $price = $item['price'];
                                    $quantity = $item['quantity'];
                                    $itemTotal = $price * $quantity;
                                    $total += $itemTotal;
                                    $totalQty += $quantity;
                                @endphp

                                <tr>
                                    <td>
                                        <strong>{{ $item['name'] }}</strong><br>
                                        <small class="text-muted">
                                            {{ $item['variant'] ?? 'Phiên bản mặc định' }}
                                        </small>
                                    </td>
                                    <td class="text-center">{{ $quantity }}</td>
                                    <td class="text-end">
                                        <strong>{{ number_format($itemTotal) }} đ</strong>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>

            {{-- RIGHT SIDE --}}
            <div class="col-lg-4">

                <div class="checkout-box">

                    <h4 class="mb-4">Thanh toán</h4>

                    <ul class="checkout-summary">
                        <li>Tổng sản phẩm
                            <span>{{ $totalQty }}</span>
                        </li>
                        <li>Tạm tính
                            <span>{{ number_format($total) }} đ</span>
                        </li>
                        <li class="total">
                            Tổng thanh toán
                            <span>{{ number_format($total) }} đ</span>
                        </li>
                    </ul>

                    <div class="mb-3">
                        <label class="fw-bold">Phương thức thanh toán</label>

                        <div class="form-check mt-2">
                            <input class="form-check-input"
                                   type="radio"
                                   name="payment_method"
                                   value="COD"
                                   checked>
                            <label class="form-check-label">
                                Thanh toán khi nhận hàng (COD)
                            </label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input"
                                   type="radio"
                                   name="payment_method"
                                   value="VNPAY">
                            <label class="form-check-label">
                                Thanh toán qua VNPAY
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

{{-- ================= STYLE ================= --}}
<style>

.checkout-box {
    background: #fff;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    margin-bottom: 20px;
}

.checkout-summary {
    list-style: none;
    padding: 0;
    margin-bottom: 20px;
}

.checkout-summary li {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
}

.checkout-summary li.total {
    font-size: 18px;
    font-weight: 600;
    color: #7fad39;
}

.checkout-btn {
    width: 100%;
    padding: 12px;
    border: none;
    background: #7fad39;
    color: #fff;
    font-weight: 600;
    border-radius: 6px;
    transition: 0.2s;
}

.checkout-btn:hover {
    background: #6a9e2e;
}

.form-control:focus {
    border-color: #7fad39;
    box-shadow: 0 0 0 0.2rem rgba(127,173,57,0.25);
}

</style>

@endsection
