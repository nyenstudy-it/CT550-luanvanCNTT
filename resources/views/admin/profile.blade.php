@extends('admin.layouts.layout_admin')

@section('content')
    <div class="container-fluid pt-4 px-4">
        <div class="bg-light rounded p-4">
            <h6 class="mb-4">Thông tin cá nhân</h6>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label>Họ tên</label>
                    <input type="text" name="name" class="form-control" value="{{ $user->name }}" required>
                </div>

                <div class="mb-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
                </div>

                @if($user->role === 'staff')
                    <div class="mb-3">
                        <label>Số điện thoại</label>
                        <input type="text" name="phone" class="form-control" value="{{ $staff->phone ?? '' }}">
                    </div>

                    <div class="mb-3">
                        <label>Ngày sinh</label>
                        <input type="date" name="date_of_birth" class="form-control" value="{{ $staff->date_of_birth ?? '' }}">
                    </div>

                    <div class="mb-3">
                        <label>Địa chỉ</label>
                        <input type="text" name="address" class="form-control" value="{{ $staff->address ?? '' }}">
                    </div>
                @endif

                <div class="mb-3">
                    <label>Avatar</label>
                    <input type="file" name="avatar" class="form-control">
                </div>

                <button class="btn btn-primary">Cập nhật</button>
            </form>
        </div>
    </div>
@endsection