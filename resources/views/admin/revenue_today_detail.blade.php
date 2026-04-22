@extends('admin.layouts.layout_admin')

@section('content')
    <div class="container-fluid pt-4 px-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
            <h5 class="mb-0">Doanh thu hôm nay ({{ $today->format('d/m/Y (l)') }})</h5>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-outline-primary">← Quay lại</a>
            </div>
        </div>

        <!-- Thống kê tổng hợp -->
        <div class="row g-4 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="bg-primary text-white rounded p-4 h-100">
                    <p class="mb-2">Tổng tiền bán ra</p>
                    <h6 class="mb-0 text-white">{{ number_format($grossSale, 0, ',', '.') }} ₫</h6>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="bg-light rounded p-4 h-100">
                    <p class="mb-2">Tiền đã hoàn</p>
                    <h6 class="mb-0 text-danger">{{ number_format($refundAmount, 0, ',', '.') }} ₫</h6>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="bg-light rounded p-4 h-100">
                    <p class="mb-2">Doanh thu thực nhận</p>
                    <h6 class="mb-0 text-primary">{{ number_format($netRevenue, 0, ',', '.') }} ₫</h6>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="bg-light rounded p-4 h-100">
                    <p class="mb-2">Lãi (sau COGS)</p>
                    <h6 class="mb-0 {{ $profit >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ number_format($profit, 0, ',', '.') }} ₫
                    </h6>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-xl-6">
                <div class="bg-light rounded p-4">
                    <h6 class="mb-3">Chi phí chi tiết</h6>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <p class="text-muted mb-1">Tổng giá vốn (COGS)</p>
                            <h6 class="text-danger">{{ number_format($totalCogs, 0, ',', '.') }} ₫</h6>
                        </div>
                        <div class="col-6 mb-3">
                            <p class="text-muted mb-1">Chi phí vận chuyển</p>
                            <h6>{{ number_format($totalShipping, 0, ',', '.') }} ₫</h6>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="bg-light rounded p-4">
                    <h6 class="mb-3">Thống kê theo phương thức thanh toán</h6>
                    @foreach($paymentMethodStats as $method)
                        <div class="d-flex justify-content-between mb-2">
                            <span>
                                @switch($method->method)
                                    @case('VNPAY')
                                        <i class="fas fa-mobile-alt"></i> VNPay
                                        @break
                                    @case('MOMO')
                                        <i class="fas fa-wallet"></i> Momo
                                        @break
                                    @case('COD')
                                        <i class="fas fa-truck"></i> COD
                                        @break
                                    @default
                                        {{ $method->method }}
                                @endswitch
                                ({{ $method->count }} đơn)
                            </span>
                            <strong>{{ number_format($method->total, 0, ',', '.') }} ₫</strong>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Top sản phẩm bán hôm nay -->
        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="bg-light rounded p-4">
                    <h6 class="mb-3">Top sản phẩm bán hôm nay</h6>
                    @if($topProducts->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;">STT</th>
                                        <th>Sản phẩm</th>
                                        <th class="text-end">SL bán</th>
                                        <th class="text-end">Doanh thu</th>
                                        <th class="text-end">COGS</th>
                                        <th class="text-end">Lãi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($topProducts as $idx => $product)
                                        <tr>
                                            <td>{{ $idx + 1 }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($product->image)
                                                        <img src="{{ asset('storage/' . $product->image) }}" 
                                                             alt="{{ $product->name }}" 
                                                             style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px; margin-right: 8px;">
                                                    @else
                                                        <img src="https://via.placeholder.com/40" 
                                                             alt="No image" 
                                                             style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px; margin-right: 8px;">
                                                    @endif
                                                    {{ $product->name }}
                                                </div>
                                            </td>
                                            <td class="text-end">{{ $product->sold_qty }}</td>
                                            <td class="text-end">{{ number_format($product->total_revenue, 0, ',', '.') }} ₫</td>
                                            <td class="text-end text-danger">{{ number_format($product->total_cogs, 0, ',', '.') }} ₫</td>
                                            <td class="text-end text-success">{{ number_format($product->total_revenue - $product->total_cogs, 0, ',', '.') }} ₫</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted mb-0">Không có dữ liệu</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Chi tiết đơn hàng -->
        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="bg-light rounded p-4">
                    <h6 class="mb-3">Các đơn hàng hoàn thành hôm nay</h6>
                    @if($completedOrdersToday->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th>Mã đơn</th>
                                        <th>Khách hàng</th>
                                        <th>Phương thức</th>
                                        <th class="text-end">Số lượng</th>
                                        <th class="text-end">Thành tiền</th>
                                        <th class="text-end">Giờ thanh toán</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($completedOrdersToday as $order)
                                        <tr>
                                            <td>
                                                <a href="{{ route('admin.orders.detail', $order->id) }}" class="text-primary">
                                                    #{{ $order->id }}
                                                </a>
                                            </td>
                                            <td>
                                                {{ $order->customer->user->full_name ?? 'N/A' }}
                                                <br><small class="text-muted">{{ $order->customer->phone ?? 'N/A' }}</small>
                                            </td>
                                            <td>
                                                @switch($order->payment->method)
                                                    @case('VNPAY')
                                                        <span class="badge bg-info">VNPay</span>
                                                        @break
                                                    @case('MOMO')
                                                        <span class="badge bg-warning">Momo</span>
                                                        @break
                                                    @case('COD')
                                                        <span class="badge bg-secondary">COD</span>
                                                        @break
                                                @endswitch
                                            </td>
                                            <td class="text-end">
                                                @php
                                                    $totalItems = $order->orderItems->sum('quantity');
                                                @endphp
                                                {{ $totalItems }}
                                            </td>
                                            <td class="text-end">
                                                <strong>{{ number_format($order->total_amount, 0, ',', '.') }} ₫</strong>
                                            </td>
                                            <td class="text-end">
                                                <small>{{ $order->payment->paid_at ? $order->payment->paid_at->format('H:i') : 'N/A' }}</small>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted mb-0">Chưa có đơn hàng hoàn thành hôm nay</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Biểu đồ doanh thu theo giờ -->
        <div class="row g-4">
            <div class="col-12">
                <div class="bg-light rounded p-4">
                    <h6 class="mb-3">Doanh thu theo từng giờ hôm nay</h6>
                    <div style="overflow-x: auto;">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    @foreach($hourlyRevenue as $hour)
                                        <th class="text-center" style="min-width: 60px;">{{ $hour['hour'] }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    @foreach($hourlyRevenue as $hour)
                                        <td class="text-center">
                                            @if($hour['revenue'] > 0)
                                                <strong class="text-success">{{ number_format($hour['revenue'] / 1000, 0) }}k</strong>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
