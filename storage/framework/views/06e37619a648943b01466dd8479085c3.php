

<?php $__env->startSection('hero'); ?>
    <?php echo $__env->make('pages.components.hero', ['showBanner' => false, 'heroNormal' => true], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

    <section class="breadcrumb-section set-bg" data-setbg="<?php echo e(asset('frontend/images/breadcrumb.jpg')); ?>">
        <div class="container">

            <div class="row">
                <div class="col-lg-12 text-center">
                    <div class="breadcrumb__text">

                        <h2>Hồ sơ cá nhân</h2>

                        <div class="breadcrumb__option">
                            <a href="<?php echo e(route('pages.trangchu')); ?>">Trang chủ</a>
                            <span>Hồ sơ cá nhân</span>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </section>

        <?php
    $avatarSrc = $customer->user->avatar
        ? asset('storage/' . $customer->user->avatar)
        : asset('images/default-avatar.png');
    $genderLabel = match ($customer->gender) {
        'male' => 'Nam',
        'female' => 'Nữ',
        'other' => 'Khác',
        default => null,
    };
    $joinedAt = $customer->created_at?->format('d/m/Y') ?? '';
    $isDefaultAddressChecked = old('is_default_address', $customer->is_default_address ? '1' : '0') == '1';
        ?>

        <section class="py-5" style="background:#f8f9fa;">
            <div class="container">

                
                <div class="d-flex align-items-center mb-4 gap-2">
                    <i class="fa fa-user-circle fa-lg" style="color:#7fad39;"></i>
                    <h5 class="mb-0 fw-semibold">Thông tin cá nhân</h5>
                </div>

                <?php if(session('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
                        <i class="fa fa-check-circle"></i>
                        <span><?php echo e(session('success')); ?></span>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if($errors->any()): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li><?php echo e($error); ?></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?php echo e(route('customer.profile.update')); ?>" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="redirect" value="<?php echo e(request('redirect')); ?>">

                    <div class="row g-4 align-items-start">

                        
                        <div class="col-xl-3 col-lg-4">
                            <div class="card border-0 shadow-sm text-center">
                                
                                <div class="rounded-top"
                                    style="height:80px; background:linear-gradient(135deg,#7fad39 0%,#3aad6e 100%);"></div>

                                <div class="card-body pt-0">
                                    
                                    <div class="position-relative d-inline-block" style="margin-top:-48px;">
                                        <img id="avatarPreview" src="<?php echo e($avatarSrc); ?>"
                                            class="rounded-circle border border-4 border-white shadow"
                                            style="width:96px;height:96px;object-fit:cover;" alt="Avatar">
                                        <span
                                            class="position-absolute bottom-0 end-0 bg-success rounded-circle border border-2 border-white"
                                            style="width:18px;height:18px;"></span>
                                    </div>

                                    <h5 class="mt-3 mb-0 fw-bold"><?php echo e($customer->user->name); ?></h5>
                                    <span class="badge mt-1" style="background:#7fad39;">Khách hàng</span>

                                    <hr class="my-3">

                                    <ul class="list-unstyled text-start small mb-0">
                                        <li class="d-flex align-items-center gap-2 mb-2">
                                            <i class="fa fa-envelope text-muted" style="width:16px;"></i>
                                            <span class="text-muted text-truncate"><?php echo e($customer->user->email); ?></span>
                                        </li>
                                        <?php if($customer->phone): ?>
                                            <li class="d-flex align-items-center gap-2 mb-2">
                                                <i class="fa fa-phone text-muted" style="width:16px;"></i>
                                                <span class="text-muted"><?php echo e($customer->phone); ?></span>
                                            </li>
                                        <?php endif; ?>
                                        <?php if($genderLabel): ?>
                                            <li class="d-flex align-items-center gap-2 mb-2">
                                                <i class="fa fa-venus-mars text-muted" style="width:16px;"></i>
                                                <span class="text-muted"><?php echo e($genderLabel); ?></span>
                                            </li>
                                        <?php endif; ?>
                                        <?php if($joinedAt): ?>
                                            <li class="d-flex align-items-center gap-2 mb-0">
                                                <i class="fa fa-calendar-alt text-muted" style="width:16px;"></i>
                                                <span class="text-muted">Tham gia: <?php echo e($joinedAt); ?></span>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </div>

                                <div class="card-footer bg-transparent border-0 pb-3">
                                    <span class="badge bg-success">
                                        <i class="fa fa-circle me-1" style="font-size:.55rem;"></i>Đang hoạt động
                                    </span>
                                </div>
                            </div>
                        </div>

                        
                        <div class="col-xl-9 col-lg-8">

                            
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-white border-bottom py-3 d-flex align-items-center gap-2">
                                    <i class="fa fa-id-card" style="color:#7fad39;"></i>
                                    <h6 class="mb-0 fw-semibold">Thông tin cá nhân</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Họ và tên <span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0"><i
                                                        class="fa fa-user text-muted"></i></span>
                                                <input type="text" name="name"
                                                    class="form-control border-start-0 <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                                    value="<?php echo e(old('name', $customer->user->name)); ?>" required>
                                                <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Email <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0"><i
                                                        class="fa fa-envelope text-muted"></i></span>
                                                <input type="email" name="email"
                                                    class="form-control border-start-0 <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                                    value="<?php echo e(old('email', $customer->user->email)); ?>" required>
                                                <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Số điện thoại</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0"><i
                                                        class="fa fa-phone text-muted"></i></span>
                                                <input type="text" name="phone" class="form-control border-start-0 <?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                                    value="<?php echo e(old('phone', $customer->phone)); ?>"
                                                    placeholder="0xxxxxxxxx" pattern="0\d{9}">
                                                <?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Ngày sinh</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0"><i
                                                        class="fa fa-birthday-cake text-muted"></i></span>
                                                <input type="date" name="date_of_birth" class="form-control border-start-0"
                                                    value="<?php echo e(old('date_of_birth', $customer->date_of_birth)); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Giới tính</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0"><i
                                                        class="fa fa-venus-mars text-muted"></i></span>
                                                <select name="gender" class="form-select border-start-0">
                                                    <option value="">-- Chọn --</option>
                                                    <option value="male" <?php echo e(old('gender', $customer->gender) == 'male' ? 'selected' : ''); ?>>Nam</option>
                                                    <option value="female" <?php echo e(old('gender', $customer->gender) == 'female' ? 'selected' : ''); ?>>Nữ</option>
                                                    <option value="other" <?php echo e(old('gender', $customer->gender) == 'other' ? 'selected' : ''); ?>>Khác</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-white border-bottom py-3 d-flex align-items-center gap-2">
                                    <i class="fa fa-map-marker-alt" style="color:#7fad39;"></i>
                                    <h6 class="mb-0 fw-semibold">Địa chỉ</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label fw-medium">Tỉnh / Thành</label>
                                            <select id="province" name="province" class="form-select"></select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-medium">Quận / Huyện</label>
                                            <select id="district" name="district" class="form-select"></select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-medium">Phường / Xã</label>
                                            <select id="ward" name="ward" class="form-select"></select>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-medium">Địa chỉ chi tiết</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0"><i
                                                        class="fa fa-home text-muted"></i></span>
                                                <input type="text" name="address" class="form-control border-start-0"
                                                    value="<?php echo e(old('address', $customer->address)); ?>">
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-medium">Địa chỉ đầy đủ</label>
                                            <input type="text" id="full_address_display" class="form-control bg-light" readonly
                                                placeholder="Tự động tổng hợp từ các lựa chọn bên trên">
                                        </div>
                                        <div class="col-12">
                                            <div class="form-check">
                                                <input type="hidden" name="is_default_address" value="0">
                                                <input class="form-check-input" type="checkbox" name="is_default_address"
                                                    value="1" <?php echo e($isDefaultAddressChecked ? 'checked' : ''); ?>>
                                                <label class="form-check-label fw-medium">
                                                    Sử dụng làm địa chỉ mặc định khi đặt hàng
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-white border-bottom py-3 d-flex align-items-center gap-2">
                                    <i class="fa fa-camera" style="color:#7fad39;"></i>
                                    <h6 class="mb-0 fw-semibold">Ảnh đại diện</h6>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex align-items-center gap-4 flex-wrap">
                                        <img id="avatarPreviewSmall" src="<?php echo e($avatarSrc); ?>" class="rounded-circle shadow-sm"
                                            style="width:72px;height:72px;object-fit:cover;" alt="Preview">
                                        <div class="flex-grow-1">
                                            <label class="form-label fw-medium mb-1">Chọn ảnh mới</label>
                                            <input type="file" name="avatar" id="avatarInput"
                                                class="form-control <?php $__errorArgs = ['avatar'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" accept="image/*">
                                            <div class="form-text text-muted">Định dạng JPG, PNG, GIF. Tối đa 2MB.</div>
                                            <?php $__errorArgs = ['avatar'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback d-block"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-white border-bottom py-3 d-flex align-items-center gap-2">
                                    <i class="fa fa-lock" style="color:#7fad39;"></i>
                                    <h6 class="mb-0 fw-semibold">Đổi mật khẩu</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label fw-medium">Mật khẩu hiện tại</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0"><i
                                                        class="fa fa-key text-muted"></i></span>
                                                <input type="password" name="current_password"
                                                    class="form-control border-start-0 <?php $__errorArgs = ['current_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                                    placeholder="••••••••">
                                                <?php $__errorArgs = ['current_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div>
                                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-medium">Mật khẩu mới</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0"><i
                                                        class="fa fa-lock text-muted"></i></span>
                                                <input type="password" name="password"
                                                    class="form-control border-start-0 <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                                    placeholder="••••••••"
                                                    pattern="^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$"
                                                    title="Mật khẩu phải chứa tối thiểu 8 ký tự, bao gồm: chữ hoa, chữ thường, số và ký tự đặc biệt (@$!%*?&)">
                                                <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback d-block"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </div>
                                            <small class="text-muted d-block mt-1">
                                                Để trống nếu không muốn đổi mật khẩu
                                            </small>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-medium">Xác nhận mật khẩu</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0"><i
                                                        class="fa fa-lock text-muted"></i></span>
                                                <input type="password" name="password_confirmation"
                                                    class="form-control border-start-0 <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                                    placeholder="••••••••">
                                                <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback d-block"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            
                            <div class="d-flex justify-content-end gap-2">
                                <a href="<?php echo e(route('pages.home')); ?>" class="btn btn-light px-4">
                                    <i class="fa fa-times me-1"></i>Hủy
                                </a>
                                <button type="submit" class="btn px-4 text-white fw-semibold"
                                    style="background:#7fad39; border-color:#7fad39;">
                                    <i class="fa fa-save me-1"></i>Lưu thay đổi
                                </button>
                            </div>

                        </div>
                    </div>
                </form>
            </div>
        </section>

        <style>
            /* Focus ring matches brand green */
            .form-control:focus,
            .form-select:focus {
                border-color: #7fad39;
                box-shadow: 0 0 0 0.2rem rgba(127, 173, 57, .2);
            }

            .nice-select {
                width: 100%;
            }

            .nice-select .list {
                max-height: 250px;
                overflow-y: auto;
                z-index: 9999;
            }
        </style>

        <script>
            document.addEventListener("DOMContentLoaded", function () {

                const dbProvince = "<?php echo e($customer->province); ?>";
                const dbDistrict = "<?php echo e($customer->district); ?>";
                const dbWard = "<?php echo e($customer->ward); ?>";
                const dbDetail = "<?php echo e($customer->address); ?>";


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
            // Live avatar preview — sync both the sidebar card and the section thumbnail
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

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\luanvan\resources\views/pages/customer/profile.blade.php ENDPATH**/ ?>