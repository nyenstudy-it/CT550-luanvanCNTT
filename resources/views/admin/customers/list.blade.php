@extends('admin.layouts.layout_admin')

@section('content')

    <style>
        .customer-summary-value {
            font-size: 1.8rem;
            font-weight: 700;
            line-height: 1;
        }

        .customer-name {
            font-weight: 600;
            color: #191c24;
        }

        .customer-subtext,
        .lock-reason {
            font-size: 12px;
            color: #6c757d;
        }

        .lock-reason {
            margin-top: 4px;
            line-height: 1.5;
        }

        .customer-action-group {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .customer-action-group form {
            margin: 0;
        }

        .customer-action-btn {
            min-width: 84px;
        }

        .customer-detail-card {
            border: 1px solid #e9ecef;
            border-radius: 10px;
            background: #fff;
            padding: 16px;
            height: 100%;
        }

        .customer-detail-label {
            display: block;
            font-size: 12px;
            color: #6c757d;
            margin-bottom: 4px;
        }

        .customer-detail-value {
            color: #191c24;
            font-weight: 600;
            word-break: break-word;
        }

        .customer-avatar {
            width: 88px;
            height: 88px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #eef2f6;
        }

        .customer-detail-header {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 20px;
        }
    </style>

    <div class="container-fluid pt-4 px-4">
        <div class="bg-light rounded p-4">

            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div>
                    <h5 class="mb-1">Danh sách khách hàng</h5>
                    <small class="text-muted">Theo dõi trạng thái tài khoản, lịch sử đăng ký và thao tác quản trị viên.</small>
                </div>
                <span class="badge bg-primary">Hiển thị {{ $customers->count() }} / {{ $customers->total() }} khách
                    hàng</span>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Tổng khách hàng</small>
                        <div class="customer-summary-value text-dark">{{ $stats['total'] ?? 0 }}</div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Đang hoạt động</small>
                        <div class="customer-summary-value text-success">{{ $stats['active'] ?? 0 }}</div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Đang bị khóa</small>
                        <div class="customer-summary-value text-danger">{{ $stats['locked'] ?? 0 }}</div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Mới trong tháng</small>
                        <div class="customer-summary-value text-primary">{{ $stats['new_this_month'] ?? 0 }}</div>
                    </div>
                </div>
            </div>

            <form method="GET" action="{{ route('admin.customers.list') }}" class="border rounded bg-white p-3 mb-4">
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-lg-4">
                        <label class="form-label">Tìm theo tên</label>
                        <input type="text" name="keyword" value="{{ request('keyword') }}" class="form-control"
                            placeholder="Nhập tên khách hàng...">
                    </div>

                    <div class="col-12 col-sm-6 col-lg-3">
                        <label class="form-label">Tài khoản</label>
                        <select name="status" class="form-select">
                            <option value="">-- Tất cả --</option>

                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>
                                Hoạt động
                            </option>

                            <option value="locked" {{ request('status') == 'locked' ? 'selected' : '' }}>
                                Bị khóa
                            </option>
                        </select>
                    </div>

                    <div class="col-12 col-sm-6 col-lg-2">
                        <label class="form-label">Từ ngày</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
                    </div>

                    <div class="col-12 col-sm-6 col-lg-2">
                        <label class="form-label">Đến ngày</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">
                    </div>

                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Lọc</button>
                        <a href="{{ route('admin.customers.list') }}" class="btn btn-secondary">Đặt lại</a>
                    </div>
                </div>
            </form>

            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div>
                    <h6 class="mb-1">Bảng khách hàng</h6>
                    <small class="text-muted">Trang {{ $customers->currentPage() }}/{{ $customers->lastPage() }}. Có thể
                        khóa nhanh tài khoản ngay trong danh sách.</small>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">

                    <thead class="table-light">
                        <tr>
                            <th>STT</th>
                            <th>Tên</th>
                            <th>Email</th>
                            <th>SĐT</th>
                            <th>Số đơn hàng</th>
                            <th>Ngày đăng ký</th>
                            <th>Trạng thái</th>
                            <th width="250">Thao tác</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse ($customers as $index => $customer)

                            <tr>

                                <td>
                                    {{ $customers->firstItem() + $index }}
                                </td>

                                <td>
                                    <div class="customer-name">{{ $customer->user->name ?? '-' }}</div>
                                    <div class="customer-subtext">Mã KH: #{{ $customer->id }}</div>
                                </td>

                                <td>
                                    <div>{{ $customer->user->email ?? '-' }}</div>
                                    @if (!empty($customer->user?->email_verified_at))
                                        <div class="customer-subtext">Đã xác thực email</div>
                                    @endif
                                </td>

                                <td>
                                    {{ $customer->phone ?? '-' }}
                                </td>

                                <td>
                                    <span class="badge bg-info">{{ $customer->orders_count ?? 0 }} đơn</span>
                                </td>

                                <td>
                                    <div>{{ $customer->created_at->format('d/m/Y') }}</div>
                                    <div class="customer-subtext">{{ $customer->created_at->format('H:i') }}</div>
                                </td>

                                <td>

                                    @if ($customer->user?->status === 'active')

                                        <span class="badge bg-success">
                                            Hoạt động
                                        </span>

                                    @else

                                        <span class="badge bg-danger">
                                            Bị khóa
                                        </span>

                                        @if (!empty($customer->user?->locked_reason))
                                            <div class="lock-reason" title="{{ $customer->user->locked_reason }}">
                                                Lý do: {{ $customer->user->locked_reason }}
                                            </div>
                                        @endif

                                        @if (!empty($customer->user?->locked_at))
                                            <div class="lock-reason">
                                                Khóa lúc:
                                                {{ \Illuminate\Support\Carbon::parse($customer->user->locked_at)->format('d/m/Y H:i') }}
                                            </div>
                                        @endif

                                    @endif

                                </td>

                                <td>
                                    <div class="customer-action-group">
                                        <button type="button" class="btn btn-sm btn-primary customer-action-btn"
                                            data-bs-toggle="modal" data-bs-target="#customerDetailModal-{{ $customer->id }}">
                                            Xem
                                        </button>

                                        @if ($customer->user?->status === 'active')
                                            <button type="button" class="btn btn-sm btn-danger customer-action-btn"
                                                data-bs-toggle="modal" data-bs-target="#lockModal-{{ $customer->id }}">
                                                Khóa
                                            </button>
                                        @else
                                            <form method="POST" action="{{ route('admin.customers.unlock', $customer->user_id) }}"
                                                onsubmit="return confirm('Mở khóa khách hàng này?')">
                                                @csrf
                                                <button class="btn btn-sm btn-success customer-action-btn">
                                                    Mở
                                                </button>
                                            </form>
                                        @endif

                                        <form method="POST" action="{{ route('admin.customers.destroy', $customer->id) }}"
                                            onsubmit="return confirm('Xóa khách hàng này?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-danger customer-action-btn">
                                                Xóa
                                            </button>
                                        </form>
                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="8" class="text-center">
                                    Chưa có khách hàng nào
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

                @foreach ($customers as $customer)
                    <div class="modal fade" id="customerDetailModal-{{ $customer->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Chi tiết khách hàng</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="customer-detail-header">
                                        <img src="{{ $customer->user?->avatar ? asset('storage/' . $customer->user->avatar) : asset('img/user.jpg') }}"
                                            alt="{{ $customer->user?->name ?? 'Khách hàng' }}" class="customer-avatar">
                                        <div>
                                            <h5 class="mb-1">{{ $customer->user?->name ?? 'Không có tên' }}</h5>
                                            <div class="text-muted small mb-2">Mã khách hàng: #{{ $customer->id }}</div>
                                            @if ($customer->user?->status === 'active')
                                                <span class="badge bg-success">Hoạt động</span>
                                            @else
                                                <span class="badge bg-danger">Bị khóa</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-12 col-md-6">
                                            <div class="customer-detail-card">
                                                <span class="customer-detail-label">Email</span>
                                                <div class="customer-detail-value">{{ $customer->user?->email ?? '-' }}</div>
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <div class="customer-detail-card">
                                                <span class="customer-detail-label">Số điện thoại</span>
                                                <div class="customer-detail-value">{{ $customer->phone ?? '-' }}</div>
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <div class="customer-detail-card">
                                                <span class="customer-detail-label">Ngày sinh</span>
                                                <div class="customer-detail-value">
                                                    {{ $customer->date_of_birth ? \Illuminate\Support\Carbon::parse($customer->date_of_birth)->format('d/m/Y') : '-' }}
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <div class="customer-detail-card">
                                                <span class="customer-detail-label">Giới tính</span>
                                                <div class="customer-detail-value">
                                                    @if ($customer->gender === 'male')
                                                        Nam
                                                    @elseif ($customer->gender === 'female')
                                                        Nữ
                                                    @elseif ($customer->gender === 'other')
                                                        Khác
                                                    @else
                                                        -
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <div class="customer-detail-card">
                                                <span class="customer-detail-label">Số đơn hàng</span>
                                                <div class="customer-detail-value">{{ $customer->orders_count ?? 0 }} đơn</div>
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <div class="customer-detail-card">
                                                <span class="customer-detail-label">Email xác thực</span>
                                                <div class="customer-detail-value">
                                                    {{ $customer->user?->email_verified_at ? $customer->user->email_verified_at->format('d/m/Y H:i') : 'Chưa xác thực' }}
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="customer-detail-card">
                                                <span class="customer-detail-label">Địa chỉ mặc định</span>
                                                <div class="customer-detail-value">{{ $customer->full_address ?: '-' }}</div>
                                                <div class="customer-subtext mt-2">
                                                    {{ $customer->is_default_address ? 'Đang đặt làm địa chỉ mặc định' : 'Chưa đặt làm địa chỉ mặc định' }}
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <div class="customer-detail-card">
                                                <span class="customer-detail-label">Ngày đăng ký</span>
                                                <div class="customer-detail-value">
                                                    {{ $customer->created_at->format('d/m/Y H:i') }}</div>
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <div class="customer-detail-card">
                                                <span class="customer-detail-label">Cập nhật gần nhất</span>
                                                <div class="customer-detail-value">
                                                    {{ $customer->updated_at->format('d/m/Y H:i') }}</div>
                                            </div>
                                        </div>

                                        @if (!empty($customer->user?->locked_reason) || !empty($customer->user?->locked_at))
                                            <div class="col-12">
                                                <div class="customer-detail-card">
                                                    <span class="customer-detail-label">Thông tin khóa tài khoản</span>
                                                    <div class="customer-detail-value">
                                                        {{ $customer->user?->locked_reason ?? 'Không có lý do' }}
                                                    </div>
                                                    @if (!empty($customer->user?->locked_at))
                                                        <div class="customer-subtext mt-2">
                                                            Khóa lúc:
                                                            {{ \Illuminate\Support\Carbon::parse($customer->user->locked_at)->format('d/m/Y H:i') }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if ($customer->user && $customer->user->status === 'active')
                        <div class="modal fade" id="lockModal-{{ $customer->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="POST" action="{{ route('admin.customers.lock', $customer->user_id) }}">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title">Khóa tài khoản: {{ $customer->user->name }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">Lý do khóa</label>
                                                <select name="reason_key" class="form-select" required>
                                                    @foreach(($lockReasonPresets ?? []) as $reasonKey => $reasonLabel)
                                                        <option value="{{ $reasonKey }}">{{ $reasonLabel }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div>
                                                <label class="form-label">Ghi chú thêm (tuỳ chọn)</label>
                                                <input type="text" name="reason_note" class="form-control" maxlength="255"
                                                    placeholder="Ví dụ: phát hiện 3 đơn bất thường trong ngày">
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                                            <button type="submit" class="btn btn-danger">Xác nhận khóa</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>


            {{-- PAGINATION --}}
            <div class="d-flex justify-content-center mt-3 small">

                {{ $customers->onEachSide(1)
        ->appends(request()->query())
        ->links('pagination::bootstrap-5') }}

            </div>

        </div>

    </div>
@endsection