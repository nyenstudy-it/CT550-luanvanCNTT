@extends('admin.layouts.layout_admin')

@section('content')
    <style>
        .staff-summary-value {
            font-size: 1.8rem;
            font-weight: 700;
            line-height: 1;
        }

        .staff-name {
            font-weight: 600;
            color: #191c24;
        }

        .staff-subtext {
            font-size: 12px;
            color: #6c757d;
        }

        .staff-action-group {
            display: grid;
            grid-template-columns: repeat(2, max-content);
            justify-content: start;
            gap: 4px;
        }

        .staff-action-group form {
            margin: 0;
        }

        .staff-action-btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.775rem;
            line-height: 1.2;
        }

        .staff-name-col {
            min-width: 220px;
        }

        .staff-detail-card {
            border: 1px solid #e9ecef;
            border-radius: 10px;
            background: #fff;
            padding: 16px;
            height: 100%;
        }

        .staff-detail-label {
            display: block;
            font-size: 12px;
            color: #6c757d;
            margin-bottom: 4px;
        }

        .staff-detail-value {
            color: #191c24;
            font-weight: 600;
            word-break: break-word;
        }

        .staff-avatar {
            width: 88px;
            height: 88px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #eef2f6;
        }

        .staff-detail-header {
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
                    <h5 class="mb-1">Danh sách nhân viên</h5>
                    <small class="text-muted">Theo dõi trạng thái tài khoản, vai trò và thông tin làm việc của nhân viên trên cùng một màn hình.</small>
                </div>
                <span class="badge bg-primary">Hiển thị {{ $staffs->count() }} / {{ $staffs->total() }} nhân viên</span>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Tổng nhân viên</small>
                        <div class="staff-summary-value text-dark">{{ $stats['total'] ?? 0 }}</div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Đang hoạt động</small>
                        <div class="staff-summary-value text-success">{{ $stats['active'] ?? 0 }}</div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Đang bị khóa</small>
                        <div class="staff-summary-value text-danger">{{ $stats['locked'] ?? 0 }}</div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Mới trong tháng</small>
                        <div class="staff-summary-value text-primary">{{ $stats['new_this_month'] ?? 0 }}</div>
                    </div>
                </div>
            </div>

            <form method="GET" action="{{ route('admin.staff.list') }}" class="border rounded bg-white p-3 mb-4">
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-lg-3">
                        <label class="form-label">Tìm theo tên</label>
                        <input type="text" name="keyword" value="{{ request('keyword') }}" class="form-control"
                            placeholder="Nhập tên nhân viên...">
                    </div>

                    <div class="col-12 col-sm-6 col-lg-2">
                        <label class="form-label">Chức vụ</label>
                        <select name="position" class="form-select">
                            <option value="">-- Tất cả --</option>
                            <option value="cashier" {{ request('position') == 'cashier' ? 'selected' : '' }}>Thu ngân</option>
                            <option value="warehouse" {{ request('position') == 'warehouse' ? 'selected' : '' }}>Nhân viên kho</option>
                            <option value="order_staff" {{ request('position') == 'order_staff' ? 'selected' : '' }}>Xử lý đơn</option>
                        </select>
                    </div>

                    <div class="col-12 col-sm-6 col-lg-2">
                        <label class="form-label">Tình trạng</label>
                        <select name="employment_status" class="form-select">
                            <option value="">-- Tất cả --</option>
                            <option value="probation" {{ request('employment_status') == 'probation' ? 'selected' : '' }}>Thử việc</option>
                            <option value="official" {{ request('employment_status') == 'official' ? 'selected' : '' }}>Chính thức</option>
                            <option value="resigned" {{ request('employment_status') == 'resigned' ? 'selected' : '' }}>Nghỉ việc</option>
                        </select>
                    </div>

                    <div class="col-12 col-sm-6 col-lg-2">
                        <label class="form-label">Tài khoản</label>
                        <select name="account_status" class="form-select">
                            <option value="">-- Tất cả --</option>
                            <option value="active" {{ request('account_status') == 'active' ? 'selected' : '' }}>Hoạt động</option>
                            <option value="locked" {{ request('account_status') == 'locked' ? 'selected' : '' }}>Bị khóa</option>
                        </select>
                    </div>

                    <div class="col-12 col-sm-6 col-lg-1">
                        <label class="form-label">Từ ngày</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
                    </div>

                    <div class="col-12 col-sm-6 col-lg-1">
                        <label class="form-label">Đến ngày</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">
                    </div>

                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Lọc</button>
                        <a href="{{ route('admin.staff.list') }}" class="btn btn-secondary">Đặt lại</a>
                    </div>
                </div>
            </form>

            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div>
                    <h6 class="mb-1">Bảng nhân viên</h6>
                    <small class="text-muted"> Có thể xem nhanh chi tiết, khóa hoặc chỉnh sửa trực tiếp từ danh sách.</small>
                </div>
                <a href="{{ route('admin.staff.create') }}" class="btn btn-primary btn-sm">+ Thêm nhân viên</a>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>STT</th>
                            <th>Tên</th>
                            <th>Email</th>
                            <th width="200">Chức vụ</th>
                            <th>Ngày vào làm</th>
                            <th>Tình trạng làm việc</th>
                            <th>Tài khoản</th>
                            <th width="165">Thao tác</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($staffs as $index => $staff)
                            <tr>
                                <td>{{ $staffs->firstItem() + $index }}</td>

                                <td class="staff-name-col">
                                    <div class="staff-name">{{ $staff->user->name ?? '-' }}</div>
                                    <div class="staff-subtext">Mã NV: #{{ $staff->user_id }}</div>
                                    <div class="staff-subtext">SĐT: {{ $staff->phone ?? '-' }}</div>
                                </td>

                                <td>
                                    <div>{{ $staff->user->email ?? '-' }}</div>
                                    @if (!empty($staff->user?->email_verified_at))
                                        <div class="staff-subtext">Đã xác thực email</div>
                                    @endif
                                </td>

                                <td>
                                    @switch($staff->position)
                                        @case('cashier')
                                            Thu ngân
                                        @break

                                        @case('warehouse')
                                            Nhân viên kho
                                        @break

                                        @case('order_staff')
                                            Nhân viên xử lý đơn hàng
                                        @break

                                        @default
                                            -
                                    @endswitch
                                </td>

                                <td>
                                    <div>{{ $staff->start_date ? $staff->start_date->format('d/m/Y') : '-' }}</div>
                                    
                                </td>

                                <td>
                                    @if ($staff->employment_status === 'probation')
                                        <span class="badge bg-warning">Thử việc</span>
                                    @elseif ($staff->employment_status === 'official')
                                        <span class="badge bg-success">Chính thức</span>
                                    @else
                                        <span class="badge bg-secondary">Nghỉ việc</span>
                                    @endif
                                </td>

                                <td>
                                    @if ($staff->user->status === 'active')
                                        <span class="badge bg-success">Hoạt động</span>
                                    @else
                                        <span class="badge bg-danger">Bị khóa</span>
                                    @endif
                                </td>

                                <td>
                                    <div class="staff-action-group">
                                        <button type="button" class="btn btn-sm btn-primary staff-action-btn"
                                            data-bs-toggle="modal" data-bs-target="#staffDetailModal-{{ $staff->user_id }}">
                                            Xem
                                        </button>

                                        <a href="{{ route('admin.staff.edit', $staff->user_id) }}"
                                            class="btn btn-sm btn-warning staff-action-btn">
                                            Sửa
                                        </a>

                                        @if ($staff->user->status === 'active')
                                            <form method="POST" action="{{ route('admin.staff.lock', $staff->user_id) }}"
                                                onsubmit="return confirm('Khóa nhân viên này?')">
                                                @csrf
                                                <button class="btn btn-sm btn-danger staff-action-btn">Khóa</button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('admin.staff.unlock', $staff->user_id) }}"
                                                onsubmit="return confirm('Mở khóa nhân viên này?')">
                                                @csrf
                                                <button class="btn btn-sm btn-success staff-action-btn">Mở</button>
                                            </form>
                                        @endif

                                        <form method="POST" action="{{ route('admin.staff.destroy', $staff->user_id) }}"
                                            onsubmit="return confirm('Xóa nhân viên này?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-danger staff-action-btn">Xóa</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">Chưa có nhân viên nào</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @foreach ($staffs as $staff)
                    <div class="modal fade" id="staffDetailModal-{{ $staff->user_id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Chi tiết nhân viên</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="staff-detail-header">
                                        <img src="{{ $staff->user?->avatar ? asset('storage/' . $staff->user->avatar) : asset('img/user.jpg') }}"
                                            alt="{{ $staff->user?->name ?? 'Nhân viên' }}" class="staff-avatar">
                                        <div>
                                            <h5 class="mb-1">{{ $staff->user?->name ?? 'Không có tên' }}</h5>
                                            <div class="text-muted small mb-2">Mã nhân viên: #{{ $staff->user_id }}</div>
                                            @if ($staff->user?->status === 'active')
                                                <span class="badge bg-success">Hoạt động</span>
                                            @else
                                                <span class="badge bg-danger">Bị khóa</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-12 col-md-6">
                                            <div class="staff-detail-card">
                                                <span class="staff-detail-label">Email</span>
                                                <div class="staff-detail-value">{{ $staff->user?->email ?? '-' }}</div>
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <div class="staff-detail-card">
                                                <span class="staff-detail-label">Số điện thoại</span>
                                                <div class="staff-detail-value">{{ $staff->phone ?? '-' }}</div>
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <div class="staff-detail-card">
                                                <span class="staff-detail-label">Chức vụ</span>
                                                <div class="staff-detail-value">
                                                    @switch($staff->position)
                                                        @case('cashier')
                                                            Thu ngân
                                                        @break

                                                        @case('warehouse')
                                                            Nhân viên kho
                                                        @break

                                                        @case('order_staff')
                                                            Nhân viên xử lý đơn hàng
                                                        @break

                                                        @default
                                                            -
                                                    @endswitch
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <div class="staff-detail-card">
                                                <span class="staff-detail-label">Tình trạng làm việc</span>
                                                <div class="staff-detail-value">
                                                    @if ($staff->employment_status === 'probation')
                                                        Thử việc
                                                    @elseif ($staff->employment_status === 'official')
                                                        Chính thức
                                                    @else
                                                        Nghỉ việc
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <div class="staff-detail-card">
                                                <span class="staff-detail-label">Ngày sinh</span>
                                                <div class="staff-detail-value">{{ $staff->date_of_birth ? $staff->date_of_birth->format('d/m/Y') : '-' }}</div>
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <div class="staff-detail-card">
                                                <span class="staff-detail-label">Ngày vào làm</span>
                                                <div class="staff-detail-value">{{ $staff->start_date ? $staff->start_date->format('d/m/Y') : '-' }}</div>
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <div class="staff-detail-card">
                                                <span class="staff-detail-label">Bắt đầu thử việc</span>
                                                <div class="staff-detail-value">{{ $staff->probation_start ? $staff->probation_start->format('d/m/Y') : '-' }}</div>
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <div class="staff-detail-card">
                                                <span class="staff-detail-label">Kết thúc thử việc</span>
                                                <div class="staff-detail-value">{{ $staff->probation_end ? $staff->probation_end->format('d/m/Y') : '-' }}</div>
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <div class="staff-detail-card">
                                                <span class="staff-detail-label">Lương giờ thử việc</span>
                                                <div class="staff-detail-value">{{ number_format((float) ($staff->probation_hourly_wage ?? 0), 0, ',', '.') }} đ</div>
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <div class="staff-detail-card">
                                                <span class="staff-detail-label">Lương giờ chính thức</span>
                                                <div class="staff-detail-value">{{ number_format((float) ($staff->official_hourly_wage ?? 0), 0, ',', '.') }} đ</div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="staff-detail-card">
                                                <span class="staff-detail-label">Địa chỉ</span>
                                                <div class="staff-detail-value">{{ $staff->address ?? '-' }}</div>
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <div class="staff-detail-card">
                                                <span class="staff-detail-label">Tạo hồ sơ</span>
                                                <div class="staff-detail-value">{{ $staff->created_at ? \Illuminate\Support\Carbon::parse($staff->created_at)->format('d/m/Y H:i') : '-' }}</div>
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <div class="staff-detail-card">
                                                <span class="staff-detail-label">Email xác thực</span>
                                                <div class="staff-detail-value">{{ $staff->user?->email_verified_at ? $staff->user->email_verified_at->format('d/m/Y H:i') : 'Chưa xác thực' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="d-flex justify-content-center mt-3 small">
                {{ $staffs->onEachSide(1)->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
@endsection
