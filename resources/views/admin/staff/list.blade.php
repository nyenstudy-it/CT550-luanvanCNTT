@extends('admin.layouts.layout_admin')

@section('content')
<div class="container-fluid pt-4 px-4">
    <div class="bg-light rounded p-4">

        <form method="GET" class="row g-3 mb-3">

    <div class="col-md-3">
        <label>Tìm theo tên</label>
        <input type="text" name="keyword"
               value="{{ request('keyword') }}"
               class="form-control"
               placeholder="Nhập tên...">
    </div>

    <div class="col-md-2">
        <label>Chức vụ</label>
        <select name="position" class="form-select">
            <option value="">-- Tất cả --</option>
            <option value="cashier" {{ request('position')=='cashier'?'selected':'' }}>Thu ngân</option>
            <option value="warehouse" {{ request('position')=='warehouse'?'selected':'' }}>Nhân viên kho</option>
            <option value="order_staff" {{ request('position')=='order_staff'?'selected':'' }}>Xử lý đơn</option>
        </select>
    </div>

    <div class="col-md-2">
        <label>Tình trạng</label>
        <select name="employment_status" class="form-select">
            <option value="">-- Tất cả --</option>
            <option value="probation" {{ request('employment_status')=='probation'?'selected':'' }}>Thử việc</option>
            <option value="official" {{ request('employment_status')=='official'?'selected':'' }}>Chính thức</option>
            <option value="resigned" {{ request('employment_status')=='resigned'?'selected':'' }}>Nghỉ việc</option>
        </select>
    </div>

    <div class="col-md-2">
        <label>Tài khoản</label>
        <select name="account_status" class="form-select">
            <option value="">-- Tất cả --</option>
            <option value="active" {{ request('account_status')=='active'?'selected':'' }}>Hoạt động</option>
            <option value="locked" {{ request('account_status')=='locked'?'selected':'' }}>Bị khóa</option>
        </select>
    </div>

    <div class="col-md-1">
        <label>Từ</label>
        <input type="date" name="date_from"
               value="{{ request('date_from') }}"
               class="form-control">
    </div>

    <div class="col-md-1">
        <label>Đến</label>
        <input type="date" name="date_to"
               value="{{ request('date_to') }}"
               class="form-control">
    </div>

    <div class="col-md-1 d-flex align-items-end">
        <button class="btn btn-primary w-100">Lọc</button>
    </div>

</form>


        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0">Danh sách nhân viên</h6>
            <a href="{{ route('admin.staff.create') }}" class="btn btn-primary btn-sm">
                + Thêm nhân viên
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>STT</th>
                        <th>Tên</th>
                        {{-- <th>Email</th> --}}
                        <th>SĐT</th>
                        <th>Chức vụ</th>
                        <th>Ngày vào làm</th>
                        <th>Tình trạng làm việc</th>
                        <th>Tài khoản</th>
                        <th width="200">Thao tác</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($staffs as $index => $staff)
                        <tr>
                            <td>{{ $staffs->firstItem() + $index }}</td>
                            <td>{{ $staff->user->name }}</td>
                            {{-- <td>{{ $staff->user->email }}</td> --}}
                            <td>{{ $staff->phone }}</td>

                            <td>
                                @switch($staff->position)
                                    @case('cashier') Thu ngân @break
                                    @case('warehouse') Nhân viên kho @break
                                    @case('order_staff') Nhân viên xử lý đơn hàng @break
                                    @default -
                                @endswitch
                            </td>

                            <td>
    {{ $staff->start_date ? $staff->start_date->format('d/m/Y') : '-' }}
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
                                <a href="{{ route('admin.staff.edit', $staff->user_id) }}"
                                   class="btn btn-sm btn-warning">
                                    Sửa
                                </a>

                                @if ($staff->user->status === 'active')
                                    <form method="POST"
                                          action="{{ route('admin.staff.lock', $staff->user_id) }}"
                                          class="d-inline"
                                          onsubmit="return confirm('Khóa nhân viên này?')">
                                        @csrf
                                        <button class="btn btn-sm btn-danger">Khóa</button>
                                    </form>
                                @else
                                    <form method="POST"
                                          action="{{ route('admin.staff.unlock', $staff->user_id) }}"
                                          class="d-inline"
                                          onsubmit="return confirm('Mở khóa nhân viên này?')">
                                        @csrf
                                        <button class="btn btn-sm btn-success">Mở</button>
                                    </form>
                                @endif

                                <form method="POST"
                                      action="{{ route('admin.staff.destroy', $staff->user_id) }}"
                                      class="d-inline"
                                      onsubmit="return confirm('Xóa nhân viên này?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Xóa</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">
                                Chưa có nhân viên nào
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-center mt-3 small">
    {{ $staffs->onEachSide(1)->appends(request()->query())->links('pagination::bootstrap-5') }}

</div>


    </div>
</div>
@endsection
