@extends('layout')

@section('hero')
    @include('pages.components.hero', ['showBanner' => false, 'heroNormal' => true])
@endsection

@section('content')

    <section class="profile-section py-5">
        <div class="container">

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('customer.profile.update') }}" enctype="multipart/form-data">
                @csrf

                <div class="row g-4">

                    {{-- LEFT: AVATAR --}}
                    <div class="col-lg-3">
                        <div class="profile-sidebar text-center">

                            <img id="avatarPreview" src="{{ $customer->user->avatar
    ? asset('storage/' . $customer->user->avatar)
    : asset('images/default-avatar.png') }}" class="profile-avatar">

                            <h5 class="mt-3 mb-1">{{ $customer->user->name }}</h5>
                            <small class="text-muted">{{ $customer->user->email }}</small>

                            <div class="mt-3">
                                <label class="btn btn-outline-success btn-sm w-100">
                                    Chọn ảnh
                                    <input type="file" name="avatar" hidden onchange="previewAvatar(this)">
                                </label>
                            </div>

                        </div>
                    </div>

                    {{-- RIGHT: FORM --}}
                    <div class="col-lg-9">
                        <div class="profile-card">

                            <h4 class="mb-4">Thông tin cá nhân</h4>

                            <div class="row">

                                <div class="col-md-6 mb-3">
                                    <label>Họ và tên</label>
                                    <input type="text" name="name" class="form-control"
                                        value="{{ old('name', $customer->user->name) }}">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label>Email</label>
                                    <input type="email" name="email" class="form-control"
                                        value="{{ old('email', $customer->user->email) }}">
                                </div>


                                <div class="col-md-6 mb-3">
                                    <label>Số điện thoại</label>
                                    <input type="text" name="phone" class="form-control"
                                        value="{{ old('phone', $customer->phone) }}">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label>Ngày sinh</label>
                                    <input type="date" name="date_of_birth" class="form-control"
                                        value="{{ old('date_of_birth', $customer->date_of_birth) }}">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label>Giới tính</label>
                                    <select name="gender" class="form-select  shadow-sm">
                                        <option value="">-- Chọn --</option>
                                        <option value="male" {{ $customer->gender == 'male' ? 'selected' : '' }}>Nam</option>
                                        <option value="female" {{ $customer->gender == 'female' ? 'selected' : '' }}>Nữ
                                        </option>
                                        <option value="other" {{ $customer->gender == 'other' ? 'selected' : '' }}>Khác
                                        </option>
                                    </select>
                                </div>

                            </div>

                            <hr class="my-4">

                            <h5 class="mb-3">Địa chỉ</h5>

                            <div class="row">

                                <div class="col-md-4 mb-3">
                                    <label>Tỉnh / Thành</label>
                                    <select id="province" name="province" class="form-select"></select>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label>Quận / Huyện</label>
                                    <select id="district" name="district" class="form-select"></select>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label>Phường / Xã</label>
                                    <select id="ward" name="ward" class="form-select"></select>
                                </div>

                                <div class="col-12 mb-3">
                                    <label>Địa chỉ chi tiết</label>
                                    <input type="text" name="address" class="form-control"
                                        value="{{ old('address', $customer->address) }}">
                                </div>
                                <div class="col-12 mb-3">
                                    <label>Địa chỉ đầy đủ</label>
                                    <input type="text" id="full_address_display" class="form-control bg-light" readonly>
                                </div>


                                <div class="col-12 mb-3">
                                    <div class="form-check">
                                        <input type="hidden" name="is_default_address" value="0">
                                        <input class="form-check-input" type="checkbox" name="is_default_address" value="1" {{ $customer->is_default_address ? 'checked' : '' }}>
                                        <label class="form-check-label">
                                            Sử dụng làm địa chỉ mặc định khi đặt hàng
                                        </label>
                                    </div>
                                </div>

                            </div>

                            <hr class="my-4">

                            <h5 class="mb-3">Đổi mật khẩu</h5>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <input type="password" name="current_password" class="form-control"
                                        placeholder="Mật khẩu hiện tại">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <input type="password" name="password" class="form-control" placeholder="Mật khẩu mới">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <input type="password" name="password_confirmation" class="form-control"
                                        placeholder="Xác nhận mật khẩu">
                                </div>
                            </div>

                            <button type="submit" class="btn btn-success px-4">
                                LƯU THAY ĐỔI
                            </button>

                        </div>
                    </div>

                </div>
            </form>
        </div>
    </section>

    {{-- STYLE --}}
    <style>
        .profile-section {
            background: #f8f9fa;
        }

        .profile-sidebar {
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #7fad39;
        }

        .profile-card {
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.05);
        }

        .profile-card label {
            font-weight: 500;
            margin-bottom: 6px;
            font-size: 14px;
        }

        /* KHÔNG ép height */
        .profile-card .form-control,
        .profile-card .form-select {
            border-radius: 8px;
            font-size: 14px;
            padding: 8px 12px;
        }

        .profile-card .form-control:focus,
        .profile-card .form-select:focus {
            border-color: #7fad39;
            box-shadow: 0 0 0 0.2rem rgba(127, 173, 57, 0.15);
        }

        .btn-success {
            background-color: #7fad39;
            border-color: #7fad39;
        }

        .btn-success:hover {
            background-color: #6c9931;
            border-color: #6c9931;
        }

        .nice-select {
            width: 100%;
        }

        .nice-select .list {
            max-height: 250px;
            overflow-y: auto;
            z-index: 9999;
        }

        .profile-card .nice-select {
            height: 42px;
            line-height: 40px;
            border-radius: 8px;
        }

        .profile-card .nice-select .current {
            font-size: 14px;
        }

        .profile-card .nice-select:after {
            right: 12px;
        }
    </style>

    <script>
        document.addEventListener("DOMContentLoaded", function () {

            const dbProvince = "{{ $customer->province }}";
            const dbDistrict = "{{ $customer->district }}";
            const dbWard = "{{ $customer->ward }}";
            const dbDetail = "{{ $customer->address }}";


            const provinceSelect = document.getElementById("province");
            const districtSelect = document.getElementById("district");
            const wardSelect = document.getElementById("ward");

            let provincesData = {};

            function refreshNiceSelect() {
                if (typeof $ !== 'undefined' && $.fn.niceSelect) {
                    $('select').niceSelect('destroy');
                    //$('select').niceSelect();
                }
            }

            fetch("/data/vietnam.json")
                .then(res => res.json())
                .then(data => {

                    provincesData = data;

                    provinceSelect.innerHTML = `<option value="">Chọn tỉnh</option>`;
                    districtSelect.innerHTML = `<option value="">Chọn huyện</option>`;
                    wardSelect.innerHTML = `<option value="">Chọn xã</option>`;

                    // Load tỉnh
                    Object.keys(data).forEach(code => {

                        const selected = (code == dbProvince) ? "selected" : "";

                        provinceSelect.innerHTML +=
                            `<option value="${code}" ${selected}>
                    ${data[code].name_with_type}
                </option>`;
                    });

                    // Nếu có tỉnh
                    if (dbProvince && data[dbProvince]) {

                        const districts = data[dbProvince]["quan-huyen"];

                        Object.keys(districts).forEach(code => {

                            const selected = (code == dbDistrict) ? "selected" : "";

                            districtSelect.innerHTML +=
                                `<option value="${code}" ${selected}>
                        ${districts[code].name_with_type}
                    </option>`;
                        });

                        // Nếu có huyện
                        if (dbDistrict && districts[dbDistrict]) {

                            const wards = districts[dbDistrict]["xa-phuong"];

                            Object.keys(wards).forEach(code => {

                                const selected = (code == dbWard) ? "selected" : "";

                                wardSelect.innerHTML +=
                                    `<option value="${code}" ${selected}>
                            ${wards[code].name_with_type}
                        </option>`;
                            });
                        }
                    }

                    refreshNiceSelect();
                    updateFullAddress();
                })
                .catch(err => console.error("Lỗi JSON:", err));

            provinceSelect.addEventListener("change", function () {

                districtSelect.innerHTML = `<option value="">Chọn huyện</option>`;
                wardSelect.innerHTML = `<option value="">Chọn xã</option>`;

                const province = provincesData[this.value];
                if (!province) {
                    refreshNiceSelect();
                    return;
                }

                const districts = province["quan-huyen"];
                if (!districts) {
                    refreshNiceSelect();
                    return;
                }

                Object.keys(districts).forEach(code => {
                    districtSelect.innerHTML +=
                        `<option value="${code}">
                                    ${districts[code].name_with_type}
                                </option>`;
                });

                refreshNiceSelect();
            });

            districtSelect.addEventListener("change", function () {

                wardSelect.innerHTML = `<option value="">Chọn xã</option>`;

                const province = provincesData[provinceSelect.value];
                if (!province) {
                    refreshNiceSelect();
                    return;
                }

                const district = province["quan-huyen"][this.value];
                if (!district) {
                    refreshNiceSelect();
                    return;
                }

                const wards = district["xa-phuong"];
                if (!wards) {
                    refreshNiceSelect();
                    return;
                }

                Object.keys(wards).forEach(code => {
                    wardSelect.innerHTML +=
                        `<option value="${code}">
                                    ${wards[code].name_with_type}
                                </option>`;
                });

                refreshNiceSelect();
            });


            function updateFullAddress() {

                const province = provincesData[provinceSelect.value];
                const district = province?.["quan-huyen"]?.[districtSelect.value];
                const ward = district?.["xa-phuong"]?.[wardSelect.value];

                const provinceText = province?.name_with_type || '';
                const districtText = district?.name_with_type || '';
                const wardText = ward?.name_with_type || '';

                const detail = document.querySelector('input[name="address"]').value.trim();

                let parts = [];

                if (detail) parts.push(detail);
                if (wardText) parts.push(wardText);
                if (districtText) parts.push(districtText);
                if (provinceText) parts.push(provinceText);

                document.getElementById('full_address_display').value = parts.join(', ');
            }

            provinceSelect.addEventListener("change", updateFullAddress);
            districtSelect.addEventListener("change", updateFullAddress);
            wardSelect.addEventListener("change", updateFullAddress);
            document.querySelector('input[name="address"]').addEventListener("input", updateFullAddress);

        });
    </script>

    <script>
        function previewAvatar(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    document.getElementById('avatarPreview').src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>

@endsection