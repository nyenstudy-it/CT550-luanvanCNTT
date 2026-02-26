@extends('admin.layouts.layout_admin')

@section('content')
    <div class="container-fluid pt-4 px-4">
        <div class="bg-light rounded h-100 p-4">
            <h6 class="mb-4">Thêm nhân viên</h6>
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.staff.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label">Tên nhân viên</label>
                    <div class="col-sm-10">
                        <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label">Email</label>
                    <div class="col-sm-10">
                        <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label">Mật khẩu</label>
                    <div class="col-sm-10">
                        <input type="password" name="password" class="form-control" required>
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label">Số điện thoại</label>
                    <div class="col-sm-10">
                        <input type="text" name="phone" value="{{ old('phone') }}" class="form-control">
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label">Ngày sinh</label>
                    <div class="col-sm-10">
                        <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}" class="form-control">
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label">Địa chỉ</label>
                    <div class="col-sm-10">
                        <input type="text" name="address" value="{{ old('address') }}" class="form-control">
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label">Chức vụ</label>
                    <div class="col-sm-10">
                        <select name="position" class="form-select" required>
                            <option value="">-- Chọn chức vụ --</option>
                            <option value="cashier" {{ old('position') == 'cashier' ? 'selected' : '' }}>
                                Thu ngân
                            </option>
                            <option value="warehouse" {{ old('position') == 'warehouse' ? 'selected' : '' }}>
                                Nhân viên kho
                            </option>
                            <option value="order_staff" {{ old('position') == 'order_staff' ? 'selected' : '' }}>
                                Nhân viên xử lý đơn hàng
                            </option>
                        </select>
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label">Trạng thái</label>
                    <div class="col-sm-10">
                        <select name="employment_status" class="form-select" required>
                            <option value="probation" {{ old('employment_status') == 'probation' ? 'selected' : '' }}>
                                Thử việc
                            </option>
                            <option value="official" {{ old('employment_status') == 'official' ? 'selected' : '' }}>
                                Chính thức
                            </option>
                            <option value="resigned" {{ old('employment_status') == 'resigned' ? 'selected' : '' }}>
                                Nghỉ việc
                            </option>
                        </select>
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label">Ngày vào làm</label>
                    <div class="col-sm-10">
                        <input type="date" name="start_date" value="{{ old('start_date') }}" class="form-control">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-6">
                        <label class="form-label">Bắt đầu thử việc</label>
                        <input type="date" name="probation_start" value="{{ old('probation_start') }}" class="form-control">
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label">Kết thúc thử việc</label>
                        <input type="date" name="probation_end" value="{{ old('probation_end') }}" class="form-control">
                    </div>
                </div>
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