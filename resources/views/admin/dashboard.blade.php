@extends('admin.layouts.layout_admin')

@section('navbar')
    @include('admin.layouts.navbar')
@endsection

@section('content')

    <!-- Content Start -->

    <!-- Doanh số & Doanh thu Start -->
    <div class="container-fluid pt-4 px-4">
        <div class="row g-4">
            <div class="col-sm-6 col-xl-3">
                <div class="bg-light rounded d-flex align-items-center justify-content-between p-4">
                    <i class="fa fa-chart-line fa-3x text-primary"></i>
                    <div class="ms-3">
                        <p class="mb-2">Doanh số hôm nay</p>
                        <h6 class="mb-0">{{ number_format($todaySale, 0, ',', '.') }} ₫</h6>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="bg-light rounded d-flex align-items-center justify-content-between p-4">
                    <i class="fa fa-chart-bar fa-3x text-primary"></i>
                    <div class="ms-3">
                        <p class="mb-2">Tổng doanh số</p>
                        <h6 class="mb-0">{{ number_format($totalSale, 0, ',', '.') }} ₫</h6>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="bg-light rounded d-flex align-items-center justify-content-between p-4">
                    <i class="fa fa-chart-area fa-3x text-primary"></i>
                    <div class="ms-3">
                        <p class="mb-2">Doanh thu hôm nay</p>
                        <h6 class="mb-0">{{ number_format($todayRevenue, 0, ',', '.') }} ₫</h6>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="bg-light rounded d-flex align-items-center justify-content-between p-4">
                    <i class="fa fa-chart-pie fa-3x text-primary"></i>
                    <div class="ms-3">
                        <p class="mb-2">Tổng doanh thu</p>
                        <h6 class="mb-0">{{ number_format($totalRevenue, 0, ',', '.') }} ₫</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Doanh số & Doanh thu End -->

    <!-- Đơn hàng gần đây Start -->
    <div class="container-fluid pt-4 px-4">
        <div class="bg-light text-center rounded p-4">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h6 class="mb-0">Đơn hàng gần đây</h6>
                <a href="{{ route('admin.orders') }}">Xem tất cả</a>
            </div>
            <div class="table-responsive">
                <table class="table text-start align-middle table-bordered table-hover mb-0">
                    <thead>
                        <tr class="text-dark">
                            <th scope="col"><input class="form-check-input" type="checkbox"></th>
                            <th scope="col">Ngày tạo</th>
                            <th scope="col">Hóa đơn</th>
                            <th scope="col">Khách hàng</th>
                            <th scope="col">Tổng tiền</th>
                            <th scope="col">Trạng thái</th>
                            <th scope="col">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentOrders as $order)
                            <tr>
                                <td><input class="form-check-input" type="checkbox"></td>
                                <td>{{ $order->created_at->format('d/m/Y') }}</td>
                                <td>{{ $order->invoice_number ?? 'INV-' . $order->id }}</td>
                                <td>{{ $order->customer->name ?? 'Khách vãng lai' }}</td>
                                <td>{{ number_format($order->total_amount, 0, ',', '.') }} ₫</td>
                                <td>
                                    @if($order->status === 'paid')
                                        <span class="badge bg-success">Đã thanh toán</span>
                                    @elseif($order->status === 'pending')
                                        <span class="badge bg-warning">Đang xử lý</span>
                                    @elseif($order->status === 'canceled')
                                        <span class="badge bg-danger">Đã hủy</span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($order->status) }}</span>
                                    @endif
                                </td>
                                <td>
                                    <a class="btn btn-sm btn-primary" href="{{ route('admin.orders.detail', $order->id) }}">Chi
                                        tiết</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">Chưa có đơn hàng gần đây</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- Đơn hàng gần đây End -->

    <!-- Khách hàng Start -->
    <div class="container-fluid pt-4 px-4">
        <div class="row g-4">
            <div class="col-sm-6 col-xl-3">
                <div class="bg-light rounded d-flex align-items-center justify-content-between p-4">
                    <i class="fa fa-users fa-3x text-success"></i>
                    <div class="ms-3">
                        <p class="mb-2">Tổng khách hàng</p>
                        <h6 class="mb-0">{{ $totalCustomers }}</h6>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="bg-light rounded d-flex align-items-center justify-content-between p-4">
                    <i class="fa fa-user-plus fa-3x text-success"></i>
                    <div class="ms-3">
                        <p class="mb-2">Khách mới hôm nay</p>
                        <h6 class="mb-0">{{ $newCustomersToday }}</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Khách hàng End -->


    <!-- Task List Start -->
    <div class="container-fluid pt-4 px-4">
        <div class="h-100 bg-light rounded p-4">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h6 class="mb-0">Danh sách việc cần làm</h6>
            </div>
            <div class="d-flex mb-2">
                <input class="form-control bg-transparent" type="text" placeholder="Nhập công việc mới">
                <button type="button" class="btn btn-primary ms-2">Thêm</button>
            </div>
            @foreach($tasks as $task)
                <div class="d-flex align-items-center border-bottom py-2">
                    <input class="form-check-input m-0" type="checkbox" {{ $task['done'] ? 'checked' : '' }}>
                    <div class="w-100 ms-3">
                        <div class="d-flex w-100 align-items-center justify-content-between">
                            <span @if($task['done']) style="text-decoration: line-through;" @endif>{{ $task['title'] }}</span>
                            <button class="btn btn-sm text-primary"><i class="fa fa-times"></i></button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    <!-- Task List End -->

    <!-- Content End -->

@endsection