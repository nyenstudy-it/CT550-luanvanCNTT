@php
    $isEdit = isset($staff);
    $positionOptions = [
        'cashier' => 'Thu ngân',
        'warehouse' => 'Nhân viên kho',
        'order_staff' => 'Nhân viên xử lý đơn hàng',
    ];
    $statusOptions = [
        'probation' => 'Thử việc',
        'official' => 'Chính thức',
        'resigned' => 'Nghỉ việc',
    ];
    $avatarUrl = $isEdit && $staff->user?->avatar
        ? asset('storage/' . $staff->user->avatar)
        : asset('img/user.jpg');
@endphp

@once
    <style>
        .sf-card {
            border: 1px solid #e9ecef;
            border-radius: 12px;
            background: #fff;
            padding: 20px 22px;
            margin-bottom: 20px;
        }

        .sf-card-title {
            font-size: 0.9rem;
            font-weight: 700;
            color: #191c24;
            margin-bottom: 2px;
        }

        .sf-card-note {
            font-size: 12px;
            color: #6c757d;
            margin-bottom: 16px;
        }

        .sf-avatar-preview {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #e9ecef;
            display: block;
            margin: 0 auto 12px;
        }

        .sf-hint {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 10px 13px;
            font-size: 12.5px;
            color: #495057;
        }

        .sf-divider {
            border: none;
            border-top: 1px solid #e9ecef;
            margin: 18px 0;
        }
    </style>
@endonce

<div class="row g-4">
    {{-- ===== CỘT TRÁI: toàn bộ trường nhập liệu ===== --}}
    <div class="col-12 col-lg-8">

        {{-- Tài khoản --}}
        <div class="sf-card">
            <div class="sf-card-title">Tài khoản đăng nhập</div>
            <div class="sf-card-note">Thông tin nhân viên dùng để đăng nhập vào hệ thống.</div>
            <div class="row g-3">
                <div class="col-sm-6">
                    <label class="form-label fw-semibold">Họ và tên <span class="text-danger">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $isEdit ? $staff->user->name : '') }}"
                        class="form-control" required placeholder="Nguyễn Văn A">
                </div>
                <div class="col-sm-6">
                    <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $isEdit ? $staff->user->email : '') }}"
                        class="form-control" required placeholder="nhanvien@email.com">
                </div>
                <div class="col-sm-6">
                    <label class="form-label fw-semibold">{{ $isEdit ? 'Mật khẩu mới' : 'Mật khẩu' }}
                        {{ $isEdit ? '' : '*' }}</label>
                    <input type="password" name="password" class="form-control" {{ $isEdit ? '' : 'required' }}
                        placeholder="{{ $isEdit ? 'Để trống nếu không đổi' : 'Tối thiểu 8 ký tự' }}">
                </div>
                <div class="col-sm-6">
                    <label class="form-label fw-semibold">Số điện thoại</label>
                    <input type="text" name="phone" value="{{ old('phone', $isEdit ? $staff->phone : '') }}"
                        class="form-control" placeholder="0901234567">
                </div>
            </div>
        </div>

        {{-- Thông tin cá nhân --}}
        <div class="sf-card">
            <div class="sf-card-title">Thông tin cá nhân & công việc</div>
            <div class="sf-card-note">Phục vụ quản lý hồ sơ, tình trạng làm việc và tính lương theo ca.</div>
            <div class="row g-3">
                <div class="col-sm-6">
                    <label class="form-label fw-semibold">Ngày sinh</label>
                    <input type="date" name="date_of_birth"
                        value="{{ old('date_of_birth', $isEdit && $staff->date_of_birth ? $staff->date_of_birth->format('Y-m-d') : '') }}"
                        class="form-control">
                </div>
                <div class="col-sm-6">
                    <label class="form-label fw-semibold">Ngày vào làm</label>
                    <input type="date" name="start_date"
                        value="{{ old('start_date', $isEdit && $staff->start_date ? $staff->start_date->format('Y-m-d') : '') }}"
                        class="form-control">
                </div>
                <div class="col-sm-6">
                    <label class="form-label fw-semibold">Chức vụ <span class="text-danger">*</span></label>
                    <select name="position" class="form-select" required>
                        <option value="">-- Chọn chức vụ --</option>
                        @foreach ($positionOptions as $val => $lbl)
                            <option value="{{ $val }}" {{ old('position', $isEdit ? $staff->position : '') === $val ? 'selected' : '' }}>
                                {{ $lbl }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-6">
                    <label class="form-label fw-semibold">Tình trạng <span class="text-danger">*</span></label>
                    <select name="employment_status" class="form-select" required>
                        @foreach ($statusOptions as $val => $lbl)
                            <option value="{{ $val }}" {{ old('employment_status', $isEdit ? $staff->employment_status : 'probation') === $val ? 'selected' : '' }}>
                                {{ $lbl }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Địa chỉ</label>
                    <textarea name="address" rows="2" class="form-control"
                        placeholder="Địa chỉ liên hệ">{{ old('address', $isEdit ? $staff->address : '') }}</textarea>
                </div>
            </div>
        </div>

        {{-- Thời gian thử việc & lương --}}
        <div class="sf-card mb-0">
            <div class="sf-card-title">Thời gian thử việc & lương theo giờ</div>
            <div class="sf-card-note">Ảnh hưởng trực tiếp tới việc tính lương ca làm việc.</div>
            <div class="row g-3">
                <div class="col-sm-6">
                    <label class="form-label fw-semibold">Bắt đầu thử việc</label>
                    <input type="date" name="probation_start"
                        value="{{ old('probation_start', $isEdit && $staff->probation_start ? $staff->probation_start->format('Y-m-d') : '') }}"
                        class="form-control">
                </div>
                <div class="col-sm-6">
                    <label class="form-label fw-semibold">Kết thúc thử việc</label>
                    <input type="date" name="probation_end"
                        value="{{ old('probation_end', $isEdit && $staff->probation_end ? $staff->probation_end->format('Y-m-d') : '') }}"
                        class="form-control">
                </div>
                <div class="col-sm-6">
                    <label class="form-label fw-semibold">Lương giờ thử việc (đ)</label>
                    <input type="number" name="probation_hourly_wage"
                        value="{{ old('probation_hourly_wage', $isEdit ? $staff->probation_hourly_wage : 20000) }}"
                        class="form-control" min="0" step="1000">
                </div>
                <div class="col-sm-6">
                    <label class="form-label fw-semibold">Lương giờ chính thức (đ)</label>
                    <input type="number" name="official_hourly_wage"
                        value="{{ old('official_hourly_wage', $isEdit ? $staff->official_hourly_wage : 30000) }}"
                        class="form-control" min="0" step="1000">
                </div>
            </div>
        </div>
    </div>

    {{-- ===== CỘT PHẢI: ảnh + ghi chú ===== --}}
    <div class="col-12 col-lg-4">

        {{-- Ảnh đại diện --}}
        <div class="sf-card text-center">
            <div class="sf-card-title">Ảnh đại diện</div>
            <div class="sf-card-note">Hỗ trợ nhận diện nhanh trong danh sách nhân viên.</div>
            <img id="staffAvatarPreview" src="{{ $avatarUrl }}" alt="Avatar" class="sf-avatar-preview">
            <input type="file" name="avatar" id="staffAvatarInput" class="form-control form-control-sm"
                accept="image/*">
            <div class="sf-hint mt-2 text-start">Tối đa 2 MB · JPG, PNG, WEBP</div>
        </div>

        {{-- Ghi chú --}}
        <div class="sf-card mb-0">
            <div class="sf-card-title">Lưu ý</div>
            <div class="sf-hint mb-2">
                Trạng thái <strong>Thử việc</strong> dùng lương giờ thử việc khi tính lương ca.
            </div>
            <div class="sf-hint mb-2">
                Chuyển sang <strong>Chính thức</strong> để áp dụng lương giờ chính thức.
            </div>
            <div class="sf-hint">
                Nếu nhân viên <strong>Nghỉ việc</strong>, giữ hồ sơ để đối chiếu chấm công cũ.
            </div>
        </div>
    </div>

    {{-- Nút submit --}}
    <div class="col-12">
        <hr class="mt-0 mb-3">
        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('admin.staff.list') }}" class="btn btn-secondary">Quay lại</a>
            <button type="submit" class="btn btn-primary px-4">{{ $submitLabel }}</button>
        </div>
    </div>
</div>

<script>
    (function () {
        const input = document.getElementById('staffAvatarInput');
        const preview = document.getElementById('staffAvatarPreview');
        if (input && preview) {
            input.addEventListener('change', function () {
                if (this.files && this.files[0]) {
                    preview.src = URL.createObjectURL(this.files[0]);
                }
            });
        }
    })();
</script>