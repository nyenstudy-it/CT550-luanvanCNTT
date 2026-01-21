@extends('admin.layouts.layout_admin')

@section('content')
    <div class="container-fluid pt-4 px-4">
        <div class="bg-light rounded h-100 p-4">
            <h6 class="mb-4">Chỉnh sửa nhân viên</h6>

            <form method="POST" action="{{ route('admin.staff.update', $staff->user_id) }}" enctype="multipart/form-data">
                @csrf

                {{-- Tên --}}
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label">Tên</label>
                    <div class="col-sm-10">
                        <input type="text" name="name" class="form-control" value="{{ old('name', $staff->user->name) }}"
                            required>
                    </div>
                </div>

                {{-- SĐT --}}
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label">SĐT</label>
                    <div class="col-sm-10">
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $staff->phone) }}">
                    </div>
                </div>

                {{-- Địa chỉ --}}
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label">Địa chỉ</label>
                    <div class="col-sm-10">
                        <input type="text" name="address" class="form-control"
                            value="{{ old('address', $staff->address) }}">
                    </div>
                </div>

                {{-- Chức vụ --}}
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label">Chức vụ</label>
                    <div class="col-sm-10">
                        <select name="position" class="form-select">
                            <option value="sales" {{ $staff->position == 'sales' ? 'selected' : '' }}>Bán hàng</option>
                            <option value="warehouse" {{ $staff->position == 'warehouse' ? 'selected' : '' }}>Kho</option>
                            <option value="import" {{ $staff->position == 'import' ? 'selected' : '' }}>Nhập hàng</option>
                            <option value="support" {{ $staff->position == 'support' ? 'selected' : '' }}>Hỗ trợ</option>
                        </select>
                    </div>
                </div>

                {{-- Trạng thái --}}
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label">Trạng thái</label>
                    <div class="col-sm-10">
                        <select name="employment_status" class="form-select">
                            <option value="probation" {{ $staff->employment_status == 'probation' ? 'selected' : '' }}>Thử
                                việc
                            </option>
                            <option value="official" {{ $staff->employment_status == 'official' ? 'selected' : '' }}>Chính
                                thức
                            </option>
                            <option value="resigned" {{ $staff->employment_status == 'resigned' ? 'selected' : '' }}>Nghỉ việc
                            </option>
                        </select>
                    </div>
                </div>

                {{-- Ngày vào làm --}}
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label">Ngày vào làm</label>
                    <div class="col-sm-10">
                        <input type="date" name="start_date" class="form-control"
                            value="{{ old('start_date', $staff->start_date) }}">
                    </div>
                </div>
                {{-- Avatar --}}
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label">Ảnh đại diện</label>
                    <div class="col-sm-10">
                        <div class="mb-2">
                            <img src="{{ $staff->user->avatar
        ? asset('storage/' . $staff->user->avatar)
        : asset('img/user.jpg') }}" width="80" class="rounded-circle">
                        </div>

                        <input type="file" name="avatar" class="form-control">
                    </div>
                </div>


                <button class="btn btn-primary">Cập nhật</button>
                <a href="{{ route('admin.staff.list') }}" class="btn btn-secondary">
                    Quay lại
                </a>
            </form>
        </div>
    </div>
@endsection