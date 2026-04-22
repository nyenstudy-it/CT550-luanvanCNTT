

<?php $__env->startSection('hero'); ?>
    <?php echo $__env->make('pages.components.hero', ['showBanner' => false, 'heroNormal' => true], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <section class="breadcrumb-section set-bg" data-setbg="<?php echo e(asset('frontend/images/breadcrumb.jpg')); ?>">
        <div class="container">

            <div class="row">
                <div class="col-lg-12 text-center">
                    <div class="breadcrumb__text">

                        <h2>Sản phẩm</h2>

                        <div class="breadcrumb__option">
                            <a href="<?php echo e(route('pages.trangchu')); ?>">Trang chủ</a>
                            <span>Sản phẩm</span>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </section>

    <?php if(!empty($keyword)): ?>
        <section class="product spad" style="padding-top: 20px; padding-bottom: 0;">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <p style="font-size: 16px; color: #555; margin: 0;">
                            Đây là kết quả tìm kiếm cho "<strong><?php echo e($keyword); ?></strong>" - Tìm thấy
                            <strong><?php echo e($products->total()); ?></strong> sản phẩm
                        </p>
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <section class="product spad">
        <div class="container">

            <div class="row">

                <div class="col-lg-3 col-md-4">

                    <div class="filter-box">

                        <!-- DANH MỤC -->
                        <div class="sidebar__item">
                            <h4 class="price-title">Danh mục</h4>
                            <ul>
                                <li>
                                    <a href="<?php echo e(route('products.index')); ?>">Tất cả</a>
                                </li>
                                <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li>
                                        <a href="<?php echo e(route('products.index', ['category_id' => $c->id])); ?>">
                                            <?php echo e($c->name); ?>

                                        </a>
                                    </li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </div>

                        <!-- GIÁ -->
                        <div class="sidebar__item">
                            <h4 class="price-title">Khoảng giá</h4>

                            <ul class="price-filter">

                                <li>
                                    <a
                                        href="<?php echo e(route('products.index', array_merge(request()->query(), ['price_range' => '0-100000']))); ?>">
                                        Dưới 100.000₫
                                    </a>
                                </li>

                                <li>
                                    <a
                                        href="<?php echo e(route('products.index', array_merge(request()->query(), ['price_range' => '100000-300000']))); ?>">
                                        100.000₫ - 300.000₫
                                    </a>
                                </li>

                                <li>
                                    <a
                                        href="<?php echo e(route('products.index', array_merge(request()->query(), ['price_range' => '300000-500000']))); ?>">
                                        300.000₫ - 500.000₫
                                    </a>
                                </li>

                                <li>
                                    <a
                                        href="<?php echo e(route('products.index', array_merge(request()->query(), ['price_range' => '500000-1000000']))); ?>">
                                        500.000₫ - 1.000.000₫
                                    </a>
                                </li>

                                <li>
                                    <a
                                        href="<?php echo e(route('products.index', array_merge(request()->query(), ['price_range' => '1000000-99999999']))); ?>">
                                        Trên 1.000.000₫
                                    </a>
                                </li>

                            </ul>
                        </div>
                        <!-- NHÀ CUNG CẤP -->
                        <div class="sidebar__item">
                            <h4 class="price-title">Nhà cung cấp</h4>
                            <ul>
                                <?php $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li>
                                        <a href="<?php echo e(route('products.index', ['supplier_id' => $s->id])); ?>">
                                            <?php echo e($s->name); ?>

                                        </a>
                                    </li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </div>

                    </div>

                </div>
                <div class="col-lg-9 col-md-8">
                    <div class="row">

                        <?php $__empty_1 = true; $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                            <?php
                                $price = optional($product->variants->first())->price ?? 0;
                                $finalPrice = $product->display_final_price ?? $price;
                                $basePrice = $product->display_base_price ?? $price;
                                $hasDiscount = (bool) ($product->display_has_discount ?? false);
                                $image = $product->image
                                    ? asset('storage/' . $product->image)
                                    : asset('frontend/images/product/product-1.jpg');
                            ?>

                            <div class="col-lg-4 col-md-6 col-sm-6 mb-4">

                                <div class="product__item custom-card">

                                    <!-- ẢNH -->
                                    <div class="product__item__pic">
                                        <a href="<?php echo e(route('products.show', $product->id)); ?>">
                                            <img src="<?php echo e($image); ?>" alt="<?php echo e($product->name); ?>"
                                                onerror="this.src='<?php echo e(asset('frontend/images/product/product-1.jpg')); ?>';">
                                        </a>

                                        
                                        <form id="wishlist-all-<?php echo e($product->id); ?>"
                                            action="<?php echo e(route('wishlist.toggle', $product->id)); ?>" method="POST" class="d-none">
                                            <?php echo csrf_field(); ?>
                                        </form>

                                        <a href="javascript:void(0)" onclick="allProductsWishlist(<?php echo e($product->id); ?>);"
                                            style="position: absolute; top: 10px; right: 10px; font-size: 20px; <?php echo e($product->is_favorited ? 'color:#e74c3c;' : 'color:#333;'); ?> z-index: 10; background: rgba(255, 255, 255, 0.9); width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;"
                                            title="<?php echo e($product->is_favorited ? 'Bỏ yêu thích' : 'Thêm yêu thích'); ?>"
                                            onmouseover="this.style.background='rgba(255, 255, 255, 1)'; this.style.transform='scale(1.1)';"
                                            onmouseout="this.style.background='rgba(255, 255, 255, 0.9)'; this.style.transform='scale(1)';">
                                            <i class="fa fa-heart"></i>
                                        </a>

                                    </div>

                                    <!-- TEXT -->
                                    <div class="product__item__text">

                                        <?php $ocop = (int) ($product->ocop_star ?? 0); ?>
                                        <!-- TÊN + SAO OCOP -->
                                        <div class="d-flex align-items-start justify-content-between gap-2 mb-1">
                                            <h6 class="product-name mb-0" style="line-height:1.35;">
                                                <a href="<?php echo e(route('products.show', $product->id)); ?>"
                                                    style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;text-overflow:ellipsis;">
                                                    <?php echo e($product->name); ?>

                                                </a>
                                            </h6>
                                            <?php if($ocop > 0): ?>
                                                <span
                                                    style="flex-shrink:0;font-size:11px;font-weight:700;color:#92600a;background:#fef9c3;border:1px solid #fde68a;border-radius:999px;padding:2px 8px;white-space:nowrap;display:inline-flex;align-items:center;gap:3px;">
                                                    <i class="fa fa-star" style="font-size:10px;color:#f59e0b;"></i><?php echo e($ocop); ?> sao
                                                </span>
                                            <?php endif; ?>
                                        </div>

                                        <!-- ⭐ Đánh giá khách hàng -->
                                        <div class="product__rating" style="margin-bottom:6px;">
                                            <?php
                                                $avgRating = round((float) ($product->avg_rating ?? 0));
                                                $reviewCount = (int) ($product->review_count ?? 0);
                                            ?>

                                            <?php for($i = 1; $i <= 5; $i++): ?>
                                                <i
                                                    class="fa fa-star <?php echo e($i <= $avgRating ? 'text-warning' : 'text-secondary'); ?>"></i>
                                            <?php endfor; ?>

                                            <span class="ocop-label">(<?php echo e($reviewCount); ?>)</span>
                                        </div>

                                        <!-- GIÁ -->
                                        <?php if($hasDiscount): ?>
                                            <h5 class="text-danger mb-0"><?php echo e(number_format((int) max(0, $finalPrice ?? 0), 0)); ?>đ
                                            </h5>
                                            <small
                                                class="text-muted"><del><?php echo e(number_format((int) max(0, $basePrice ?? 0), 0)); ?>đ</del>
                                                <?php echo e($product->display_discount_label); ?></small>
                                        <?php else: ?>
                                            <h5><?php echo e(number_format((int) max(0, $price ?? 0), 0)); ?>đ</h5>
                                        <?php endif; ?>

                                        <!-- 🔥 MUA NGAY -->
                                        <a href="<?php echo e(route('products.show', $product->id)); ?>" class="buy-now-btn">
                                            <i class="fa fa-bolt"></i> Mua ngay
                                        </a>

                                    </div>

                                </div>

                            </div>



                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="col-12 text-center">
                                <h5>Không có sản phẩm nào</h5>
                            </div>
                        <?php endif; ?>

                    </div>

                    <?php if($products->hasPages()): ?>
                        <div class="shop__pagination__footer mt-4 pt-3 border-top">
                            <?php echo e($products->appends(request()->query())->links()); ?>

                        </div>
                    <?php endif; ?>

                </div>

            </div>
        </div>
    </section>

    
    <script>
        setTimeout(function () {
            document.querySelectorAll(".auto-dismiss").forEach(function (el) {
                el.remove();
            });
        }, 3000);

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

        // ================= WISHLIST AJAX (POPUP VERSION) =================
        function allProductsWishlist(productId) {
            <?php if(auth()->guard()->check()): ?>
                                                                const wishlistForm = document.getElementById('wishlist-all-' + productId);
                if (!wishlistForm) return;

                const formData = new FormData(wishlistForm);
                fetch('<?php echo e(route("wishlist.toggle", ":id")); ?>'.replace(':id', productId), {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                    .then(response => {
                        if (!response.ok) throw new Error('Network error');
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            // Find the product item and update the heart icon color
                            const allWishlistLinks = document.querySelectorAll(`[onclick*="allProductsWishlist(${productId})"]`);
                            allWishlistLinks.forEach(link => {
                                if (data.isAddedToWishlist) {
                                    link.style.color = '#e74c3c';
                                } else {
                                    link.style.color = 'inherit';
                                }
                            });

                            // Show popup using centralized system
                            const message = data.isAddedToWishlist
                                ? 'Thêm vào yêu thích'
                                : 'Xoá khỏi yêu thích';
                            popup('success', 'Thành công', message, {
                                confirmButtonText: 'Đóng'
                            });
                        } else {
                            popup('error', 'Lỗi', data.message || 'Có lỗi xảy ra', {
                                confirmButtonText: 'Đóng'
                            });
                        }
                    })
                    .catch(error => {
                        const errorMsg = error.message || 'Có lỗi xảy ra. Vui lòng thử lại.';
                        popup('error', 'Lỗi', errorMsg, {
                            confirmButtonText: 'Đóng'
                        });
                        console.error("Error:", error);
                    });
            <?php endif; ?>
            <?php if(auth()->guard()->guest()): ?>
                popup('warning', 'Bạn chưa đăng nhập', 'Hãy đăng nhập để thêm sản phẩm vào yêu thích.', {
                    confirmButtonText: 'Đăng nhập',
                    cancelButtonText: 'Để sau',
                    showCancelButton: true
                }).then(result => {
                    if (result.isConfirmed) {
                        window.location.href = '<?php echo e(route("login")); ?>';
                    }
                });
            <?php endif; ?>
                                }
    </script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\luanvan\resources\views/pages/all-products.blade.php ENDPATH**/ ?>