@extends('admin.layouts.layout_admin')

@section('content')
    <div class="container-fluid pt-4 px-4">
        <div class="bg-light rounded h-100 p-4">
            <h6 class="mb-4">Thêm nhân viên</h6>

            <form method="POST" action="{{ route('admin.staff.store') }}" enctype="multipart/form-data">
                @csrf

                {{-- Tên --}}
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label">Tên nhân viên</label>
                    <div class="col-sm-10">
                        <input type="text" name="name" class="form-control" required>
                    </div>
                </div>

                {{-- Email --}}
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label">Email</label>
                    <div class="col-sm-10">
                        <input type="email" name="email" class="form-control" required>
                    </div>
                </div>

                {{-- Mật khẩu --}}
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label">Mật khẩu</label>
                    <div class="col-sm-10">
                        <input type="password" name="password" class="form-control" required>
                    </div>
                </div>

                {{-- SĐT --}}
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label">Số điện thoại</label>
                    <div class="col-sm-10">
                        <input type="text" name="phone" class="form-control">
                    </div>
                </div>

                {{-- Ngày sinh --}}
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label">Ngày sinh</label>
                    <div class="col-sm-10">
                        <input type="date" name="date_of_birth" class="form-control">
                    </div>
                </div>

                {{-- Địa chỉ --}}
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label">Địa chỉ</label>
                    <div class="col-sm-10">
                        <input type="text" name="address" class="form-control">
                    </div>
                </div>

                {{-- Chức vụ --}}
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label">Chức vụ</label>
                    <div class="col-sm-10">
                        <select name="position" class="form-select" required>
                            <option value="">-- Chọn chức vụ --</option>
                            <option value="cashier">Thu ngân</option>
                            <option value="warehouse">Nhân viên kho</option>
                            <option value="delivery">Nhân viên giao hàng</option>
                        </select>
                    </div>
                </div>

                {{-- Trạng thái làm việc --}}
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label">Trạng thái</label>
                    <div class="col-sm-10">
                        <select name="employment_status" class="form-select" required>
                            <option value="probation">Thử việc</option>
                            <option value="official">Chính thức</option>
                            <option value="resigned">Nghỉ việc</option>
                        </select>
                    </div>
                </div>

                {{-- Ngày vào làm --}}
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label">Ngày vào làm</label>
                    <div class="col-sm-10">
                        <input type="date" name="start_date" class="form-control">
                    </div>
                </div>

                {{-- Thời gian thử việc --}}
                <div class="row mb-3">
                    <div class="col-sm-6">
                        <label class="form-label">Bắt đầu thử việc</label>
                        <input type="date" name="probation_start" class="form-control">
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label">Kết thúc thử việc</label>
                        <input type="date" name="probation_end" class="form-control">
                    </div>
                </div>

                {{-- Avatar --}}
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label">Ảnh đại diện</label>
                    <div class="col-sm-10">
                        <input type="file" name="avatar" class="form-control">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    Thêm nhân viên
                </button>
            </form>
        </div>
    </div>
@endsection