@extends('admin.layouts.layout_admin')

@section('content')
    <div class="container-fluid pt-4 px-4">
        <div class="bg-light rounded p-4">

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
                            <th>Email</th>
                            <th>SĐT</th>
                            <th>Chức vụ</th>
                            <th>Ngày vào làm</th>
                            <th>Trạng thái</th>
                            <th width="150">Thao tác</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($staffs as $index => $staff)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $staff->user->name }}</td>
                                <td>{{ $staff->user->email }}</td>
                                <td>{{ $staff->phone }}</td>
                                <td>{{ $staff->position }}</td>
                                <td>{{ $staff->start_date }}</td>

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
                                    <a href="{{ route('admin.staff.edit', $staff->user_id) }}" class="btn btn-sm btn-warning">
                                        Sửa
                                    </a>
                                    @if ($staff->user->status === 'active')
                                        <form method="POST" action="{{ route('admin.staff.lock', $staff->user_id) }}" class="d-inline"
                                            onsubmit="return confirm('Khóa nhân viên này?')">
                                            @csrf
                                            <button class="btn btn-sm btn-danger">Khóa</button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('admin.staff.unlock', $staff->user_id) }}" class="d-inline"
                                            onsubmit="return confirm('Mở khóa nhân viên này?')">
                                            @csrf
                                            <button class="btn btn-sm btn-success">Mở khóa</button>
                                        </form>
                                    @endif

                                    <a href="{{ route('admin.staff.destroy', $staff->user_id) }}" class="btn btn-sm btn-danger">
                                        Xóa
                                    </a>

                                </td>

                            </tr>
                        @endforeach

                        @if ($staffs->isEmpty())
                            <tr>
                                <td colspan="8" class="text-center">
                                    Chưa có nhân viên nào
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

        </div>
    </div>
@endsection