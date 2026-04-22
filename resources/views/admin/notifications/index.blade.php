@extends('admin.layouts.layout_admin')

@php
    use App\Helpers\DateHelper;
@endphp

@section('navbar')
    @include('admin.layouts.navbar')
@endsection

@section('content')
    <div class="container-fluid pt-4 px-4">
        <div class="bg-light rounded p-4">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                <div>
                    <h5 class="mb-1">Tất cả thông báo</h5>
                    <small class="text-muted">Quản lý và xem chi tiết các thông báo hệ thống</small>
                </div>
                @if($unreadCount > 0)
                    <form method="POST" action="{{ route('admin.notifications.markAllRead') }}">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fa fa-check me-2"></i>Đọc tất cả
                        </button>
                    </form>
                @endif
            </div>

            <!-- Statistics Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-6 col-lg-3">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Thông báo chưa đọc</small>
                        <div class="fw-semibold fs-5">{{ $unreadCount }}</div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Tổng số thông báo</small>
                        <div class="fw-semibold fs-5">{{ $totalNotifications }}</div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Đã đọc</small>
                        <div class="fw-semibold fs-5">{{ $totalNotifications - $unreadCount }}</div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Trạng thái</small>
                        <div class="fw-semibold fs-5">
                            @if($unreadCount > 0)
                                <span class="badge bg-danger">{{ $unreadCount }} chưa đọc</span>
                            @else
                                <span class="badge bg-success">Đã đọc hết</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notification List -->
            <div class="border rounded bg-white p-3">
                @forelse($notifications as $noti)
                    <div class="admin-notif-card mb-3 pb-3 border-bottom">
                        <div class="d-flex align-items-start">
                            <div class="flex-grow-1">
                                <a href="{{ route('admin.notifications.read', $noti->id) }}"
                                    class="text-decoration-none {{ !$noti->is_read ? 'fw-bold' : '' }}" style="color: #333;">
                                    <h6 class="mb-2">{{ $noti->title }}</h6>
                                </a>
                                <p class="text-muted mb-2" style="font-size: 14px;">
                                    {{ $noti->display_content }}
                                </p>
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <small class="text-muted">
                                        <i class="fa fa-clock me-1"></i>{{ DateHelper::diffForHumansVi($noti->created_at) }}
                                    </small>
                                    <small class="text-muted">
                                        <i class="fa fa-tag me-1"></i><span class="badge bg-light text-dark">
                                            @php
                                                $notificationTypes = [
                                                    'order_created' => 'Đơn hàng mới',
                                                    'order_confirmed' => 'Xác nhận đơn hàng',
                                                    'order_processing' => 'Đơn hàng đang xử lý',
                                                    'order_shipped' => 'Đơn hàng đã gửi',
                                                    'order_delivered' => 'Đơn hàng đã giao',
                                                    'order_cancelled' => 'Đơn hàng bị hủy',
                                                    'payment_received' => 'Thanh toán đã nhận',
                                                    'payment_failed' => 'Thanh toán thất bại',
                                                    'review_submitted' => 'Đánh giá mới',
                                                    'review_approved' => 'Đánh giá được chấp nhận',
                                                    'review_rejected' => 'Đánh giá bị từ chối',
                                                    'new_review' => 'Đánh giá mới',
                                                    'inventory_low' => 'Hàng tồn kho thấp',
                                                    'inventory_low_stock' => 'Kho hàng sắp hết',
                                                    'inventory_out_of_stock' => 'Hết hàng',
                                                    'customer_registered' => 'Khách hàng mới đăng ký',
                                                    'staff_action' => 'Hành động nhân viên',
                                                    'system_alert' => 'Cảnh báo hệ thống',
                                                    'attendance_check_in' => 'Điểm danh check-in',
                                                    'new_import' => 'Nhập hàng mới',
                                                ];
                                                echo $notificationTypes[$noti->type] ?? $noti->type;
                                            @endphp
                                        </span>
                                    </small>
                                    @if(!$noti->is_read)
                                        <small>
                                            <span class="badge bg-primary">Chưa đọc</span>
                                        </small>
                                    @endif
                                </div>
                            </div>
                            <a href="{{ route('admin.notifications.read', $noti->id) }}"
                                class="btn btn-sm btn-outline-primary ms-3">
                                <i class="fa fa-chevron-right"></i>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5 text-muted">
                        <i class="fa fa-inbox" style="font-size: 48px; margin-bottom: 15px; opacity: 0.5; display: block;"></i>
                        <p>Không có thông báo nào</p>
                    </div>
                @endforelse

                @if($notifications->count() > 0)
                    <div class="last-item" style="border: none;"></div>
                @endif
            </div>

            <!-- Pagination -->
            @if($totalNotifications > 10)
                <div class="mt-4 d-flex justify-content-center">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    </div>

    <style>
        .admin-notif-card {
            transition: all 0.2s ease;
            padding: 12px;
            margin-left: -12px;
            margin-right: -12px;
            padding-left: 12px;
            padding-right: 12px;
        }

        .admin-notif-card:hover {
            background: #f9f9f9;
            border-radius: 4px;
        }

        .admin-notif-card:last-child {
            border-bottom: none !important;
        }
    </style>
@endsection