


<?php $__env->startSection('hero'); ?>
    <?php echo $__env->make('pages.components.hero', ['showBanner' => false, 'heroNormal' => true], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
        <!-- Breadcrumb Section Begin -->
        <section class="breadcrumb-section set-bg" data-setbg="<?php echo e(asset('frontend/images/breadcrumb.jpg')); ?>">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12 text-center">
                        <div class="breadcrumb__text">
                            <h2>Giỏ hàng</h2>
                            <div class="breadcrumb__option">
                                <a href="<?php echo e(route('pages.home')); ?>">Trang chủ</a>
                                <span>Giỏ hàng</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- Breadcrumb Section End -->


        <style>
            .voucher-panel {
                border: 1px solid #e5efe0;
                border-radius: 12px;
                background: #fff;
                padding: 12px;
            }

            .voucher-tag {
                border: 1px solid #d9e7d1;
                border-radius: 999px;
                background: #f7fcf5;
                color: #245b33;
                padding: 7px 12px;
                font-size: 12px;
                font-weight: 600;
                display: inline-flex;
                align-items: center;
                gap: 6px;
            }

            .voucher-applied-note {
                color: #2d7a3f;
                font-size: 14px;
                font-weight: 600;
            }

            .quantity-warning-alert {
                background-color: #fff3cd;
                border: 1px solid #ffc107;
                color: #856404;
                padding: 12px 15px;
                border-radius: 8px;
                margin-bottom: 20px;
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .quantity-warning-alert i {
                font-size: 18px;
                flex-shrink: 0;
            }
        </style>

                    <section class="shoping-cart spad">
                        <div class="container">

                            <div class="row">
                                <div class="col-lg-12">
                                    
                                    <?php
                                        $hasExceededQty = false;
                                        if (!empty($cart)) {
                                            foreach ($cart as $item) {
                                                if ($item['quantity'] > 10) {
                                                    $hasExceededQty = true;
                                                    break;
                                                }
                                            }
                                        }
                                    ?>

                                    <?php if($hasExceededQty): ?>
                                        <div class="quantity-warning-alert">
                                            <i class="bi bi-exclamation-triangle-fill"></i>
                                            <div>
                                                <strong>⚠️ Cảnh báo số lượng đặt hàng!</strong><br>
                                                Số lượng một số sản phẩm trong giỏ vượt quá 10 cái. Để tiếp tục đặt hàng, vui lòng <strong>giảm số lượng xuống dưới 10</strong>, hoặc liên hệ với chúng tôi qua tin nhắn hoặc gửi liên hệ cho những đơn hàng số lượng lớn.
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <div class="shoping__cart__table">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th width="120">Ảnh</th>
                                                    <th>Tên sản phẩm</th>
                                                    <th class="text-center">Đơn giá</th>
                                                    <th class="text-center">Số lượng</th>
                                                    <th class="text-center">Thành tiền</th>
                                                    <th class="text-center">Xóa</th>
                                                </tr>
                                            </thead>

                                            <tbody>
                                                <?php $total = 0; ?>

                                                <?php if(empty($cart) || count($cart) == 0): ?>
                                                    <tr>
                                                        <td colspan="6" class="text-center py-5">
                                                            <h5>🛒 Giỏ hàng của bạn đang trống</h5>
                                                        </td>
                                                    </tr>
                                                <?php else: ?>

                                                    <?php $__currentLoopData = $cart; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variantId => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                                                                                        <?php
            $price = $item['price'] ?? 0;
            $quantity = $item['quantity'] ?? 1;
            $itemTotal = $price * $quantity;
            $total += $itemTotal;

            // Check if image path is already a full URL or starts with 'frontend/'
            if (!empty($item['image']) && (strpos($item['image'], 'frontend/') === 0)) {
                $image = asset($item['image']);
            } elseif (!empty($item['image'])) {
                $image = asset('storage/' . $item['image']);
            } else {
                $image = asset('frontend/images/product/product-1.jpg');
            }
                                                                                        ?>

                                                                                        <tr>

                                                                                            
                                                                                            <td class="text-center">
                                                                                                <img src="<?php echo e($image); ?>" width="90" style="border-radius:12px; object-fit:cover;" alt="<?php echo e($item['name'] ?? 'Sản phẩm'); ?>" onerror="this.src='<?php echo e(asset('frontend/images/product/product-1.jpg')); ?>';">
                                                                                            </td>

                                                                                            
                                                                                            <td>
                                                                                                <h6 style="font-weight:600; margin-bottom:6px;">
                                                                                                    <?php echo e($item['name'] ?? ''); ?>

                                                                                                </h6>

                                                                                                <div style="font-size:14px; color:#777;">
                                                                                                    <?php echo e($item['variant'] ?? 'Phiên bản mặc định'); ?>

                                                                                                </div>

                                                                                                <div style="font-size:12px; color:#aaa;">
                                                                                                    Mã: #<?php echo e($variantId); ?>

                                                                                                </div>
                                                                                            </td>

                                                                                            
                                                                                            <td class="text-center">
                                                                                                <strong><?php echo e(number_format($price)); ?> đ</strong>
                                                                                            </td>

                                                                                            
                                                                                            <td class="text-center">
                                                                                                <form action="<?php echo e(route('cart.update')); ?>" method="POST" class="qty-form">
                                                                                                    <?php echo csrf_field(); ?>
                                                                                                    <input type="hidden" name="variant_id" value="<?php echo e($variantId); ?>">

                                                                                                    <div class="qty-wrapper">
                                                                                                        <button type="button" class="qty-btn minus">−</button>

                                                                                                        <input type="number" name="quantity" value="<?php echo e($quantity); ?>" min="1" max="<?php echo e($item['stock'] ?? 1); ?>"
                                                                                                            class="qty-input">

                                                                                                        <button type="button" class="qty-btn plus">+</button>
                                                                                                    </div>
                                                                                                </form>
                                                                                            </td>

                                                                                            
                                                                                            <td class="text-center">
                                                                                                <strong style="color:#ee4d2d; font-size:16px;">
                                                                                                    <?php echo e(number_format($itemTotal)); ?> đ
                                                                                                </strong>
                                                                                            </td>

                                                                                            
                                                                                            <td class="text-center">
                                                                                                <form action="<?php echo e(route('cart.remove')); ?>" method="POST">
                                                                                                    <?php echo csrf_field(); ?>
                                                                                                    <input type="hidden" name="variant_id" value="<?php echo e($variantId); ?>">
                                                                                                    <button type="submit"
                                                                                                        style="border:none;background:none;color:#999;font-size:20px;">
                                                                                                        <span class="icon_close"></span>
                                                                                                    </button>
                                                                                                </form>
                                                                                            </td>

                                                                                        </tr>

                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        
                        <div class="row mt-4">
                            <div class="col-lg-6"></div>

                            <div class="col-lg-6">
                                <div class="shoping__checkout p-3" style="border:1px solid #ddd; border-radius:12px; background:#fff;">

                                    <h5 class="mb-3">Tổng giỏ hàng</h5>

                                    
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Mã giảm giá</label>

                                        <div id="discount-apply-form" class="d-flex gap-2">
                                            <select id="discount-code" name="code" class="form-select">
                                                <option value="">-- Không sử dụng mã --</option>

                                                <?php $__empty_1 = true; $__currentLoopData = $savedDiscounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                                                    <option value="<?php echo e($d->code); ?>" <?php echo e(session('cart_discount_code') == $d->code ? 'selected' : ''); ?>>
                                                                                        <?php echo e($d->code); ?> -
                                                                                        <?php echo e($d->value_label); ?>

                                                                                    </option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                    <option value="" disabled>Chưa có mã nào được lưu</option>
                                                <?php endif; ?>
                                            </select>

                                            <button type="button" id="btn-apply-discount" class="btn btn-success">
                                                Áp dụng
                                            </button>
                                        </div>

                                        <div class="voucher-panel mt-3">
                                            <div class="fw-bold mb-2" style="font-size:14px; color:#245b33;">Mã đang có (bấm Lưu để dùng)</div>

                                            <?php if($suggestedDiscounts->isEmpty()): ?>
                                                <div class="text-muted" style="font-size:13px;">Bạn đã lưu hết các mã hiện có.</div>
                                            <?php else: ?>
                                                <div class="d-flex flex-wrap gap-2">
                                                    <?php $__currentLoopData = $suggestedDiscounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <div class="d-flex align-items-center gap-2 mb-0">
                                                            <span class="voucher-tag">
                                                                <?php echo e($sg->code); ?>

                                                                (<?php echo e($sg->value_label); ?>)
                                                                <?php if($sg->products->isNotEmpty()): ?>
                                                                    - áp dụng cho <?php echo e($sg->products->count()); ?> sản phẩm
                                                                <?php else: ?>
                                                                    - toàn shop
                                                                <?php endif; ?>
                                                            </span>
                                                            <button type="button" class="btn btn-sm btn-outline-success btn-save-discount" data-code="<?php echo e($sg->code); ?>">Lưu</button>
                                                        </div>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        
                                        <?php if(!empty(session('cart_discount_code'))): ?>
                                            <div class="voucher-applied-note mt-2">
                                                ✔ Đang áp dụng: <strong><?php echo e(session('cart_discount_code')); ?></strong>
                                                <?php if($appliedDiscount && $appliedDiscount->products->isNotEmpty()): ?>
                                                    <span class="text-muted">(mã theo sản phẩm)</span>
                                                <?php elseif($appliedDiscount): ?>
                                                    <span class="text-muted">(<?php echo e($appliedDiscount->audience_label); ?>)</span>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="mt-2 text-muted">
                                                Chưa áp dụng mã giảm giá
                                            </div>
                                        <?php endif; ?>
                                    </div>


                                    
                                    <?php
    $finalTotal = $total + $shippingFee - $discountAmount;
                                    ?>

                                    <ul class="list-unstyled">
                                        <li class="d-flex justify-content-between mb-2">
                                            <span>Tổng tiền sản phẩm</span>
                                            <span><?php echo e(number_format($total)); ?> đ</span>
                                        </li>

                                        <li class="d-flex justify-content-between mb-2">
                                            <span>Phí vận chuyển</span>
                                            <span><?php echo e(number_format($shippingFee)); ?> đ</span>
                                        </li>

                                        <li class="d-flex justify-content-between mb-2">
                                            <span>Giảm giá</span>
                                            <span style="color:#1abc9c;">
                                                -<?php echo e(number_format($discountAmount)); ?> đ
                                            </span>
                                        </li>

                                        <li class="d-flex justify-content-between mt-3 fw-bold" style="font-size:1.2rem;">
                                            <span>Tổng cộng</span>
                                            <span style="color:#ee4d2d;">
                                                <?php echo e(number_format($finalTotal)); ?> đ
                                            </span>
                                        </li>
                                    </ul>

                                    
                                    <?php if(!empty($cart) && count($cart) > 0): ?>
                                        <?php if($hasExceededQty): ?>
                                            <button type="button" class="primary-btn mt-3" disabled
                                                style="background:#ccc; width:100%; border-radius:8px; border:none;">
                                                TIẾN HÀNH THANH TOÁN (Vui lòng giảm số lượng xuống <10)
                                            </button>
                                        <?php else: ?>
                                            <?php if(auth()->guard()->check()): ?>
                                                <a href="<?php echo e(route('checkout')); ?>" class="primary-btn mt-3"
                                                    style="background:#7fad39; display:block; text-align:center; border-radius:8px;">
                                                    TIẾN HÀNH THANH TOÁN
                                                </a>
                                            <?php else: ?>
                                                <button type="button" id="btn-checkout-login" class="primary-btn mt-3"
                                                    style="background:#7fad39; display:block; text-align:center; border-radius:8px; width:100%; border:none;">
                                                    TIẾN HÀNH THANH TOÁN
                                                </button>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <button class="primary-btn mt-3" disabled style="background:#ccc; width:100%; border-radius:8px;">
                                            Giỏ hàng trống
                                        </button>
                                    <?php endif; ?>

                                </div>
                            </div>

                        </div>
                    </section>
            <script>
                // Define popup function to support additional options
                function popup(icon, title, text, additionalOptions) {
                    if (window.ocopPopup && typeof window.ocopPopup.fire === 'function') {
                        return window.ocopPopup.fire(Object.assign({
                            icon: icon,
                            title: title,
                            text: text,
                            confirmButtonColor: '#7fad39'
                        }, additionalOptions || {}));
                    }

                    if (typeof Swal !== 'undefined') {
                        return Swal.fire(Object.assign({
                            icon: icon,
                            title: title,
                            text: text,
                            confirmButtonColor: '#7fad39'
                        }, additionalOptions || {}));
                    }

                    return Promise.resolve({ isConfirmed: false, isDismissed: true });
                }

                document.addEventListener("DOMContentLoaded", function () {

                    // ================= QUANTITY =================
                    document.querySelectorAll(".qty-form").forEach(function (form) {

                        const minus = form.querySelector(".minus");
                        const plus = form.querySelector(".plus");
                        const input = form.querySelector(".qty-input");

                        if (!minus || !plus || !input) return;

                        minus.addEventListener("click", function () {
                            let value = parseInt(input.value);
                            if (value > 1) {
                                input.value = value - 1;
                                form.submit();
                            }
                        });

                        plus.addEventListener("click", function () {
                            let value = parseInt(input.value);
                            let max = parseInt(input.getAttribute("max"));

                            if (value < max) {
                                input.value = value + 1;
                                form.submit();
                            } else {
                                popup('warning', 'Vượt quá tồn kho', 'Sản phẩm chỉ còn ' + max + ' sản phẩm trong kho');
                            }
                        });

                        input.addEventListener("change", function () {
                            if (input.value < 1) input.value = 1;
                            form.submit();
                        });
                    });

                    // ================= DISCOUNT =================
                    const discountSelect = document.getElementById("discount-code");
                    const btnApplyDiscount = document.getElementById("btn-apply-discount");

                    // Apply discount AJAX
                    if (btnApplyDiscount && discountSelect) {
                        btnApplyDiscount.addEventListener("click", function () {
                            const code = discountSelect.value;

                            fetch("<?php echo e(route('cart.apply_discount')); ?>", {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/json",
                                    "X-CSRF-TOKEN": "<?php echo e(csrf_token()); ?>",
                                    "X-Requested-With": "XMLHttpRequest"
                                },
                                body: JSON.stringify({ code: code })
                            })
                            .then(response => response.json()
                                .then(data => ({
                                    ok: response.ok,
                                    status: response.status,
                                    data: data
                                }))
                            )
                            .then(result => {
                                const { ok, status, data } = result;
                                if (!ok && !data.success) {
                                    throw new Error(data.message || `HTTP error! status: ${status}`);
                                }
                                if (data.success) {
                                    popup('success', 'Thành công', data.message, {
                                        confirmButtonText: 'Đóng'
                                    }).then(() => {
                                        setTimeout(() => location.reload(), 500);
                                    });
                                } else {
                                    popup('warning', 'Cảnh báo', data.message, {
                                        confirmButtonText: 'Đóng'
                                    });
                                }
                            })
                            .catch(error => {
                                console.error('Discount error:', error);
                                const errorMsg = error.message || 'Có lỗi xảy ra. Vui lòng thử lại.';
                                popup('error', 'Lỗi', errorMsg, {
                                    confirmButtonText: 'Đóng'
                                });
                            });
                        });
                    } else {
                        console.warn("Discount elements not found: btnApplyDiscount=" + !!btnApplyDiscount + ", discountSelect=" + !!discountSelect);
                    }

                    // Save discount AJAX
                    document.querySelectorAll(".btn-save-discount").forEach(btn => {
                        btn.addEventListener("click", function () {
                            const code = this.getAttribute("data-code");

                            fetch("<?php echo e(route('cart.save_discount')); ?>", {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/json",
                                    "X-CSRF-TOKEN": "<?php echo e(csrf_token()); ?>",
                                    "X-Requested-With": "XMLHttpRequest"
                                },
                                body: JSON.stringify({ code: code })
                            })
                            .then(response => response.json()
                                .then(data => ({
                                    ok: response.ok,
                                    status: response.status,
                                    data: data
                                }))
                            )
                            .then(result => {
                                const { ok, status, data } = result;
                                if (!ok && !data.success) {
                                    throw new Error(data.message || `HTTP error! status: ${status}`);
                                }
                                if (data.success) {
                                    popup('success', 'Thành công', data.message, {
                                        confirmButtonText: 'Đóng'
                                    }).then(() => {
                                        setTimeout(() => location.reload(), 500);
                                    });
                                } else {
                                    popup('warning', 'Cảnh báo', data.message, {
                                        confirmButtonText: 'Đóng'
                                    });
                                }
                            })
                            .catch(error => {
                                console.error('Save discount error:', error);
                                const errorMsg = error.message || 'Có lỗi xảy ra. Vui lòng thử lại.';
                                popup('error', 'Lỗi', errorMsg, {
                                    confirmButtonText: 'Đóng'
                                });
                            });
                        });
                    });

                    // ================= LOGIN REQUIRED CHECKOUT =================
                    const checkoutBtn = document.getElementById("btn-checkout-login");
                    if (checkoutBtn) {
                        checkoutBtn.addEventListener("click", function () {
                            popup('warning', 'Bạn chưa đăng nhập', 'Vui lòng đăng nhập để tiến hành thanh toán.', {
                                showCancelButton: true,
                                confirmButtonText: 'Đăng nhập',
                                cancelButtonText: 'Ở lại giỏ hàng'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = "<?php echo e(route('login')); ?>";
                                }
                            });
                        });
                    }

                    // ================= AUTO DISMISS ALERT =================
                    setTimeout(function () {
                        document.querySelectorAll(".auto-dismiss").forEach(function (alert) {
                            alert.classList.remove("show");
                            alert.classList.add("fade");
                            setTimeout(() => alert.remove(), 500);
                        });
                    }, 3000);

                });
            </script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\luanvan\resources\views/pages/cart.blade.php ENDPATH**/ ?>