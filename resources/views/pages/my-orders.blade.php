@extends('layout')

@section('hero')
    @include('pages.components.hero', ['showBanner' => false, 'heroNormal' => true])
@endsection

@section('content')

    <section class="breadcrumb-section set-bg" data-setbg="{{ asset('frontend/images/breadcrumb.jpg') }}">
        <div class="container">

            <div class="row">
                <div class="col-lg-12 text-center">
                    <div class="breadcrumb__text">

                        <h2>Đơn hàng của tôi</h2>

                        <div class="breadcrumb__option">
                            <a href="{{ route('pages.home') }}">Trang chủ</a>
                            <a href="{{ route('orders.my') }}">Đơn hàng của tôi</a>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </section>

        <section class="checkout spad">
            <div class="container">

                <div class="checkout-box">

                    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">

                        <h4 class="m-0">Đơn hàng của tôi</h4>

                        <div class="d-flex flex-wrap gap-2">

                            <a href="{{ route('orders.my') }}"
                                class="btn btn-sm {{ request()->status == null ? 'btn-success' : 'btn-outline-success' }}">
                                Tất cả
                            </a>

                            <a href="{{ route('orders.my', ['status' => 'pending']) }}"
                                class="btn btn-sm {{ request()->status == 'pending' ? 'btn-success' : 'btn-outline-success' }}">
                                Chờ xử lý
                            </a>

                            <a href="{{ route('orders.my', ['status' => 'confirmed']) }}"
                                class="btn btn-sm {{ request()->status == 'confirmed' ? 'btn-success' : 'btn-outline-success' }}">
                                Đã xác nhận
                            </a>

                            <a href="{{ route('orders.my', ['status' => 'shipping']) }}"
                                class="btn btn-sm {{ request()->status == 'shipping' ? 'btn-success' : 'btn-outline-success' }}">
                                Đang giao
                            </a>

                            <a href="{{ route('orders.my', ['status' => 'completed']) }}"
                                class="btn btn-sm {{ request()->status == 'completed' ? 'btn-success' : 'btn-outline-success' }}">
                                Hoàn thành
                            </a>

                            <a href="{{ route('orders.my', ['status' => 'cancelled']) }}"
                                class="btn btn-sm {{ request()->status == 'cancelled' ? 'btn-danger' : 'btn-outline-danger' }}">
                                Đã huỷ
                            </a>

                            <a href="{{ route('orders.my', ['status' => 'refund_requested']) }}"
                                class="btn btn-sm {{ request()->status == 'refund_requested' ? 'btn-warning' : 'btn-outline-warning' }}">
                                Chờ hoàn tiền
                            </a>

                            <a href="{{ route('orders.my', ['status' => 'refunded']) }}"
                                class="btn btn-sm {{ request()->status == 'refunded' ? 'btn-success' : 'btn-outline-success' }}">
                                Đã hoàn tiền
                            </a>


                        </div>

                    </div>

                    @if($orders->count() == 0)

                        <div class="text-center p-4">
                            <p>Bạn chưa có đơn hàng nào.</p>

                            <a href="{{ route('pages.home') }}" class="btn btn-success">
                                Mua sắm ngay
                            </a>
                        </div>

                    @else

                        <table class="table">

                            <thead>
                                <tr>
                                    <th>Mã đơn</th>
                                    <th>Ngày đặt</th>
                                    <th>Tổng tiền</th>
                                    <th>Trạng thái</th>
                                    <th class="text-end">Chi tiết</th>
                                </tr>
                            </thead>

                            <tbody>

                                @foreach($orders as $order)

                                    <tr>

                                        <td>#{{ $order->id }}</td>

                                        <td>{{ $order->created_at->format('d/m/Y') }}</td>

                                        <td>{{ number_format($order->total_amount) }} đ</td>

                                        <td>

                                            @if($order->status == 'pending')
                                                <span class="badge bg-warning text-dark">Chờ xử lý</span>

                                            @elseif($order->status == 'confirmed')
                                                <span class="badge bg-info text-dark">Đã xác nhận</span>

                                            @elseif($order->status == 'shipping')
                                                <span class="badge bg-primary">Đang giao</span>

                                            @elseif($order->status == 'completed')
                                                <span class="badge bg-success">Hoàn thành</span>

                                            @elseif($order->status == 'cancelled')
                                                <span class="badge bg-danger">Đã huỷ</span>

                                            @elseif($order->status == 'refund_requested')
                                                <span class="badge bg-warning text-dark">Chờ hoàn tiền</span>

                                            @elseif($order->status == 'refunded')
                                                <span class="badge bg-success">Đã hoàn tiền</span>

                                            @endif

                                        </td>


                                        <td class="text-end">

                                            <a href="{{ route('orders.detail', $order->id) }}" class="btn btn-sm btn-outline-success">
                                                Xem
                                            </a>

                                        </td>

                                    </tr>

                                @endforeach

                            </tbody>

                        </table>

                    @endif

                </div>

            </div>
        </section>

        <style>
            .checkout-box {
                background: #fff;
                padding: 25px;
                border-radius: 10px;
                box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            }
        </style>

@endsection