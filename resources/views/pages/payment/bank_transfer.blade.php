@extends('layout')

@section('content')

    <div class="container">

        <h3>Thanh toán chuyển khoản ngân hàng</h3>

        <p>Mã đơn hàng: <strong>#{{ $order->id }}</strong></p>

        <p>Số tiền cần thanh toán:</p>
        <h4>{{ number_format($order->total_amount) }} VND</h4>

        <hr>

        <h4>Thông tin chuyển khoản</h4>

        <p>Ngân hàng: Vietcombank</p>
        <p>Số tài khoản: 0123456789</p>
        <p>Chủ tài khoản: OCOP SEN HONG</p>

        <p>Nội dung chuyển khoản:</p>
        <h4>DH{{ $order->id }}</h4>

        <hr>

        <h4>Quét mã QR để chuyển khoản</h4>

        <img src="https://img.vietqr.io/image/VCB-0123456789-compact2.png?amount={{ $order->total_amount }}&addInfo=DH{{ $order->id }}"
            width="250">

        <p class="mt-3">
            Nội dung chuyển khoản:
            <strong>DH{{ $order->id }}</strong>
        </p>

        <p>
            Số tiền:
            <strong>{{ number_format($order->total_amount) }} VND</strong>
        </p>

        <hr>

        {{-- TRẠNG THÁI THANH TOÁN --}}
        <div class="alert alert-warning mt-3" id="paymentStatus">
            ⏳ Đang chờ thanh toán...
        </div>

        <p>Sau khi chuyển khoản, hệ thống sẽ tự động kiểm tra trạng thái thanh toán.</p>

        <a href="{{ route('orders.detail', $order->id) }}" class="btn btn-primary">
            Xem đơn hàng
        </a>

    </div>

@endsection


{{-- SCRIPT KIỂM TRA THANH TOÁN --}}
@section('scripts')

    <script>

        function checkPayment() {

            fetch("{{ route('payment.status', $order->id) }}")
                .then(response => response.json())
                .then(data => {

                    if (data.status === 'paid') {

                        document.getElementById("paymentStatus").classList.remove("alert-warning");
                        document.getElementById("paymentStatus").classList.add("alert-success");

                        document.getElementById("paymentStatus").innerHTML =
                            "Thanh toán thành công! Đang chuyển trang...";

                        setTimeout(function () {

                            window.location.href =
                                "{{ route('orders.detail', $order->id) }}";

                        }, 2000);

                    }

                });

        }

        setInterval(checkPayment, 5000);

    </script>

@endsection