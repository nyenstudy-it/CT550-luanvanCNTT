@extends('admin.layouts.layout_admin')

@section('content')
    @php
        $staff = $user->staff;
        $positionLabel = match ($staff?->position ?? null) {
            'cashier' => 'Thu ngân',
            'warehouse' => 'Nhân viên kho',
            'order_staff' => 'Xử lý đơn hàng',
            default => $user->role === 'admin' ? 'Quản trị viên' : ucfirst($user->role),
        };
        $positionColor = match ($staff?->position ?? null) {
            'cashier' => 'success',
            'warehouse' => 'warning',
            'order_staff' => 'info',
            default => $user->role === 'admin' ? 'danger' : 'secondary',
        };
        $joinedAt = $staff?->start_date
            ? \Carbon\Carbon::parse($staff->start_date)->format('d/m/Y')
            : $user->created_at->format('d/m/Y');
    @endphp

    <div class="container-fluid pt-4 px-4">

        {{-- Page header --}}
        <div class="d-flex align-items-center mb-4 gap-2">
            <i class="fa fa-user-circle fa-lg text-primary"></i>
            <h5 class="mb-0 fw-semibold">Thông tin cá nhân</h5>
        </div>

        <div class="row g-4 align-items-start">

            {{-- ===================== LEFT: Profile card ===================== --}}
            <div class="col-xl-3 col-lg-4">
                <div class="card border-0 shadow-sm text-center">
                    {{-- Banner strip --}}
                    <div class="rounded-top"
                        style="height:80px; background: linear-gradient(135deg,#0d6efd 0%,#6610f2 100%);"></div>

                    <div class="card-body pt-0">
                        {{-- Avatar --}}
                        <div class="position-relative d-inline-block" style="margin-top:-48px;">
                            <img id="avatarPreview"
                                src="{{ $user->avatar ? asset('storage/' . $user->avatar) : asset('img/user.jpg') }}"
                                class="rounded-circle border border-4 border-white shadow"
                                style="width:96px;height:96px;object-fit:cover;" alt="Avatar">
                            <span
                                class="position-absolute bottom-0 end-0 bg-{{ $positionColor }} rounded-circle border border-2 border-white"
                                style="width:18px;height:18px;"></span>
                        </div>

                        <h5 class="mt-3 mb-0 fw-bold">{{ $user->name }}</h5>
                        <span class="badge bg-{{ $positionColor }} mt-1">{{ $positionLabel }}</span>

                        <hr class="my-3">

                        {{-- Info rows --}}
                        <ul class="list-unstyled text-start small mb-0">
                            <li class="d-flex align-items-center gap-2 mb-2">
                                <i class="fa fa-envelope text-muted" style="width:16px;"></i>
                                <span class="text-truncate text-muted">{{ $user->email }}</span>
                            </li>
                            @if($staff?->phone)
                                <li class="d-flex align-items-center gap-2 mb-2">
                                    <i class="fa fa-phone text-muted" style="width:16px;"></i>
                                    <span class="text-muted">{{ $staff->phone }}</span>
                                </li>
                            @endif
                            @if($staff?->address)
                                <li class="d-flex align-items-center gap-2 mb-2">
                                    <i class="fa fa-map-marker-alt text-muted" style="width:16px;"></i>
                                    <span class="text-muted">{{ $staff->address }}</span>
                                </li>
                            @endif
                            <li class="d-flex align-items-center gap-2 mb-0">
                                <i class="fa fa-calendar-alt text-muted" style="width:16px;"></i>
                                <span class="text-muted">Ngày vào: {{ $joinedAt }}</span>
                            </li>
                        </ul>
                    </div>

                    {{-- Status badge --}}
                    <div class="card-footer bg-transparent border-0 pb-3">
                        <span class="badge {{ $user->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                            <i class="fa fa-circle me-1" style="font-size:.55rem;"></i>
                            {{ $user->status === 'active' ? 'Đang hoạt động' : 'Bị khoá' }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- ===================== RIGHT: Edit form ===================== --}}
            <div class="col-xl-9 col-lg-8">
                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                    @csrf

                    {{-- Section: Tài khoản --}}
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom py-3 d-flex align-items-center gap-2">
                            <i class="fa fa-id-card text-primary"></i>
                            <h6 class="mb-0 fw-semibold">Thông tin tài khoản</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Họ tên <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0"><i
                                                class="fa fa-user text-muted"></i></span>
                                        <input type="text" name="name"
                                            class="form-control border-start-0 @error('name') is-invalid @enderror"
                                            value="{{ old('name', $user->name) }}" required>
                                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Email <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0"><i
                                                class="fa fa-envelope text-muted"></i></span>
                                        <input type="email" name="email"
                                            class="form-control border-start-0 @error('email') is-invalid @enderror"
                                            value="{{ old('email', $user->email) }}" required>
                                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Section: Thông tin nhân viên (staff only) --}}
                    @if($user->role === 'staff')
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white border-bottom py-3 d-flex align-items-center gap-2">
                                <i class="fa fa-address-book text-primary"></i>
                                <h6 class="mb-0 fw-semibold">Thông tin nhân viên</h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-medium">Số điện thoại</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0"><i
                                                    class="fa fa-phone text-muted"></i></span>
                                            <input type="text" name="phone" class="form-control border-start-0"
                                                value="{{ old('phone', $staff?->phone) }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-medium">Ngày sinh</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0"><i
                                                    class="fa fa-birthday-cake text-muted"></i></span>
                                            <input type="date" name="date_of_birth" class="form-control border-start-0"
                                                value="{{ old('date_of_birth', $staff?->date_of_birth) }}">
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-medium">Địa chỉ</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0"><i
                                                    class="fa fa-map-marker-alt text-muted"></i></span>
                                            <input type="text" name="address" class="form-control border-start-0"
                                                value="{{ old('address', $staff?->address) }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Section: Ảnh đại diện --}}
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom py-3 d-flex align-items-center gap-2">
                            <i class="fa fa-camera text-primary"></i>
                            <h6 class="mb-0 fw-semibold">Ảnh đại diện</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-4 flex-wrap">
                                <img id="avatarPreviewSmall"
                                    src="{{ $user->avatar ? asset('storage/' . $user->avatar) : asset('img/user.jpg') }}"
                                    class="rounded-circle shadow-sm" style="width:72px;height:72px;object-fit:cover;"
                                    alt="Preview">
                                <div class="flex-grow-1">
                                    <label class="form-label fw-medium mb-1">Chọn ảnh mới</label>
                                    <input type="file" name="avatar" id="avatarInput"
                                        class="form-control @error('avatar') is-invalid @enderror" accept="image/*">
                                    <div class="form-text text-muted">Định dạng JPG, PNG, GIF. Tối đa 2MB.</div>
                                    @error('avatar')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    @php
                        $isAdmin = $user->role === 'admin';
                        $isStaff = $user->role === 'staff';
                        $position = $isStaff ? ($user->staff?->position ?? null) : null;
                        $cancelRoute = ($isAdmin || ($isStaff && $position === 'cashier'))
                            ? route('admin.dashboard')
                            : route('profile.show');
                    @endphp

                    {{-- Submit --}}
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ $cancelRoute }}" class="btn btn-light px-4">
                            <i class="fa fa-times me-1"></i>Hủy
                        </a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fa fa-save me-1"></i>Lưu thay đổi
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <script>
        // Live avatar preview
        document.getElementById('avatarInput').addEventListener('change', function () {
            const file = this.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = e => {
                document.getElementById('avatarPreview').src = e.target.result;
                document.getElementById('avatarPreviewSmall').src = e.target.result;
            };
            reader.readAsDataURL(file);
        });
    </script>
@endsection