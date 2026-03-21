@extends('layout')

@section('content')

    <div class="container">

        <h3>Thanh toán MoMo</h3>

        <div class="card p-4">

            <p>Mã đơn hàng: <strong>#{{ $order->id }}</strong></p>

            <p>Số tiền cần thanh toán:</p>

            <h4 style="color:#d82d8b">
                {{ number_format($order->total_amount) }} VND
            </h4>

            <form action="{{ route('momo.process', $order->id) }}" method="POST">
                @csrf

                <button class="site-btn" style="background:#d82d8b">
                    Thanh toán với MoMo
                </button>

            </form>

        </div>

    </div>

@endsection