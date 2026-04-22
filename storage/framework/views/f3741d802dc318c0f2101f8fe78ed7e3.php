<?php $__env->startSection('hero'); ?>
    <?php echo $__env->make('pages.components.hero', ['showBanner' => true], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>

    <style>
        .voucher-card-system {
            position: relative;
            background: linear-gradient(135deg, #ffffff 0%, #f7fcf5 100%);
            border: 1px solid #d9e7d1;
            border-left: 6px solid #66a84f;
            border-radius: 14px;
            box-shadow: 0 12px 24px rgba(22, 58, 24, 0.12);
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
            min-height: 210px;
        }

        .voucher-card-system:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 30px rgba(22, 58, 24, 0.18);
        }

        .voucher-card-system::after {
            content: "";
            position: absolute;
            top: -30px;
            right: -30px;
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: rgba(102, 168, 79, 0.12);
        }

        .voucher-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            background: rgba(102, 168, 79, 0.08);
            border-bottom: 1px dashed #d9e7d1;
            position: relative;
            z-index: 1;
        }

        .voucher-icon i {
            font-size: 1.5rem;
            color: #2d7a3f;
        }

        .voucher-code {
            font-weight: 700;
            font-size: 1.05rem;
            color: #ffffff;
            background: linear-gradient(135deg, #2d7a3f 0%, #3f944e 100%);
            padding: 6px 12px;
            border-radius: 999px;
            letter-spacing: 0.5px;
            box-shadow: 0 6px 12px rgba(45, 122, 63, 0.25);
        }

        .voucher-status {
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 600;
        }

        .voucher-card-system.active .voucher-status {
            background: #e8f6eb;
            color: #2d7a3f;
        }

        .voucher-card-system.expired .voucher-status {
            background: #fdecef;
            color: #bb2d3b;
        }

        .voucher-card-system.used .voucher-status {
            background: #eceff3;
            color: #4f5d70;
        }

        .voucher-card-system.expired {
            border-left-color: #d9534f;
            background: linear-gradient(135deg, #ffffff 0%, #fff6f6 100%);
        }

        .voucher-card-system.used {
            border-left-color: #8c9aa7;
            background: linear-gradient(135deg, #ffffff 0%, #f6f8fa 100%);
        }

        .voucher-body {
            padding: 15px 20px;
            position: relative;
            z-index: 1;
        }

        .voucher-value,
        .voucher-date {
            margin-bottom: 6px;
            font-size: 0.95rem;
            color: #415048;
        }

        .voucher-footer {
            padding: 15px 20px;
            position: relative;
            z-index: 1;
        }

        /* Product link styling to prevent text from disappearing */
        .latest-product__item__text h6 a {
            color: inherit;
            text-decoration: none;
            display: block;
            transition: color 0.3s ease;
        }

        .latest-product__item__text h6 a:hover {
            color: #7fad39;
            text-decoration: underline;
        }

        .latest-product__item__text h6 a:active,
        .latest-product__item__text h6 a:visited {
            color: inherit;
        }
    </style>

    <!-- Categories Section Begin -->
    <section class="categories">
        <div class="container">
            <div class="row">
                <div class="categories__slider owl-carousel">

                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="categories__item set-bg" data-setbg="<?php echo e($category->image_url
                        ? asset('storage/' . $category->image_url)
                        : asset('frontend/images/categories/cat-1.jpg')); ?>">

                                    <h5>
                                        <a href="<?php echo e(route('categories.show', $category->id)); ?>">
                                            <?php echo e($category->name); ?>

                                        </a>
                                    </h5>

                                </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                </div>
            </div>
        </div>
    </section>
    <!-- Categories Section End -->


    <!-- Vouchers Section Begin -->
    <section class="vouchers spad">
        <div class="container">
            <h2 class="section-title mb-4">Mã giảm giá</h2>

            <div class="voucher-carousel owl-carousel owl-theme">
                <?php $__empty_1 = true; $__currentLoopData = $discounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $discount): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $now = now();
                        $status = 'Đang áp dụng';
                        $statusClass = 'active';
                        $isSaved = in_array($discount->code, $savedDiscountCodes ?? []);
                        if ($discount->start_at && $now->lt($discount->start_at)) {
                            $status = 'Chưa bắt đầu';
                            $statusClass = 'used';
                        } elseif ($discount->end_at && $now->gt($discount->end_at)) {
                            $status = 'Hết hạn';
                            $statusClass = 'expired';
                        }
                    ?>

                    <div class="item">
                        <div class="voucher-card voucher-card-system <?php echo e($statusClass); ?>">
                            <div class="voucher-header">
                                <div class="voucher-icon">
                                    <i class="fa fa-ticket-alt"></i>
                                </div>
                                <div class="voucher-code"><?php echo e($discount->code); ?></div>
                                <div class="voucher-status"><?php echo e($status); ?></div>
                            </div>

                            <div class="voucher-body">
                                <p class="voucher-value">
                                    <strong>Giá trị:</strong>
                                    <?php echo e($discount->value_label); ?>

                                </p>

                                <p class="voucher-date">
                                    <strong>Hạn:</strong> <?php echo e($discount->end_at?->format('d/m/Y') ?? 'Không giới hạn'); ?>

                                </p>

                                <p class="voucher-date mb-0">
                                    <strong>Đối tượng:</strong> <?php echo e($discount->audience_label); ?>

                                </p>
                            </div>

                            <div class="voucher-footer">
                                <?php if($isSaved): ?>
                                    <button class="btn btn-sm btn-success w-100" disabled>
                                        Đã lưu
                                    </button>
                                <?php else: ?>
                                    <form action="<?php echo e(route('cart.save_discount')); ?>" method="POST" class="mb-0">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="code" value="<?php echo e($discount->code); ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-success w-100">
                                            Lưu
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="text-muted">Chưa có mã giảm giá nào</div>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <!-- Vouchers Section End -->

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

        document.addEventListener('DOMContentLoaded', function () {
            // Init Owl Carousel
            $('.voucher-carousel').owlCarousel({
                loop: false,
                margin: 10,
                nav: true,
                dots: false,
                responsive: {
                    0: { items: 1 },
                    576: { items: 2 },
                    768: { items: 3 },
                    992: { items: 4 }
                }
            });
        });
    </script>

    <!-- Featured Section Begin -->
    <section class="featured spad">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="section-title">
                        <h2>Sản phẩm nổi bật</h2>
                    </div>
                    <div class="featured__controls">
                        <ul>
                            <li class="active" data-filter="*">Tất cả</li>

                            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li data-filter=".cat-<?php echo e($category->id); ?>">
                                    <?php echo e($category->name); ?>

                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>

                    <div class="row featured__filter">
                        <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php
                                            $variant = $product->variants->first();
                                            $inWishlist = auth()->check()
                                                && \App\Models\Wishlist::where('user_id', auth()->id())
                                                    ->where('product_id', $product->id)->exists();
                                        ?>

                                        
                                        <form id="wishlist-home-<?php echo e($product->id); ?>" action="<?php echo e(route('wishlist.toggle', $product->id)); ?>"
                                            method="POST" class="d-none">
                                            <?php echo csrf_field(); ?>
                                        </form>
                                        <?php if($variant): ?>
                                            <form id="cart-home-<?php echo e($product->id); ?>" action="<?php echo e(route('cart.add')); ?>" method="POST"
                                                class="d-none">
                                                <?php echo csrf_field(); ?>
                                                <input type="hidden" name="variant_id" value="<?php echo e($variant->id); ?>">
                                                <input type="hidden" name="quantity" value="1">
                                            </form>
                                        <?php endif; ?>

                                        <div class="col-lg-3 col-md-4 col-sm-6 mix cat-<?php echo e($product->category_id); ?>">
                                            <div class="featured__item">

                                                <div class="featured__item__pic set-bg" data-setbg="<?php echo e($product->image
                            ? asset('storage/' . $product->image)
                            : asset('frontend/images/product/product-1.jpg')); ?>"
                                                    style="background-image: url('<?php echo e($product->image ? asset('storage/' . $product->image) : asset('frontend/images/product/product-1.jpg')); ?>'); background-size: cover; background-position: center;">
                                                    <ul class="featured__item__pic__hover">
                                                        <li>
                                                            <a href="javascript:void(0)" onclick="homeWishlist(<?php echo e($product->id); ?>);"
                                                                style="<?php echo e($inWishlist ? 'color:#e74c3c;' : ''); ?>"
                                                                title="<?php echo e($inWishlist ? 'Bỏ yêu thích' : 'Thêm yêu thích'); ?>">
                                                                <i class="fa fa-heart"></i>
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a href="<?php echo e(route('products.show', $product->id)); ?>"
                                                                title="Xem chi tiết sản phẩm">
                                                                <i class="fa fa-shopping-cart"></i>
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>

                                                <div class="featured__item__text">
                                                    <h6>
                                                        <a href="<?php echo e(route('products.show', $product->id)); ?>">
                                                            <?php echo e($product->name); ?>

                                                        </a>
                                                    </h6>
                                                    <?php if($product->display_has_discount): ?>
                                                        <h5 class="mb-0 text-danger"><?php echo e(number_format($product->display_final_price)); ?> đ</h5>
                                                        <small class="text-muted"><del><?php echo e(number_format($product->display_base_price)); ?> đ</del>
                                                            <?php echo e($product->display_discount_label); ?></small>
                                                    <?php else: ?>
                                                        <h5><?php echo e(number_format($variant?->price ?? 0)); ?> đ</h5>
                                                    <?php endif; ?>
                                                </div>

                                            </div>
                                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    </div>

                </div>
            </div>

        </div>
    </section>
    <!-- Featured Section End -->

    <!-- Banner Begin -->
    <div class="banner">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 col-md-6 col-sm-6">
                    <div class="banner__item">
                        <img src="<?php echo e(asset('frontend/images/banner/banner-1.png')); ?>" alt="">
                        <div class="banner__text">
                            <h3>Sản phẩm OCOP Đồng Tháp</h3>
                            <p>Cửa hàng Sen Hồng OCOP</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 col-md-6 col-sm-6">
                    <div class="banner__item">
                        <img src="<?php echo e(asset('frontend/images/banner/banner-2.png')); ?>" alt="">
                        <div class="banner__text">
                            <h3>Đặc sản địa phương</h3>
                            <p>Chất lượng – Uy tín – An toàn</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Banner End -->

    <!-- Latest Product Section Begin -->
    <section class="latest-product spad">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-6">
                    <div class="latest-product__text">
                        <h4>Sản phẩm mới</h4>
                        <div class="latest-product__slider owl-carousel">
                            <?php $__currentLoopData = $latestProducts->chunk(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $chunk): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="latest-prdouct__slider__item">

                                    <?php $__currentLoopData = $chunk; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <?php
                                                            $variant = $product->variants->first();
                                                        ?>

                                                        <a href="<?php echo e(route('products.show', $product->id)); ?>" class="latest-product__item">

                                                            <div class="latest-product__item__pic">
                                                                <img src="<?php echo e($product->image
                                        ? asset('storage/' . $product->image)
                                        : asset('frontend/images/product/product-1.jpg')); ?>" width="60" height="60"
                                                                    class="rounded" style="object-fit: cover" alt="<?php echo e($product->name); ?>"
                                                                    onerror="this.src='<?php echo e(asset('frontend/images/product/product-1.jpg')); ?>';">
                                                            </div>

                                                            <div class="latest-product__item__text">
                                                                <h6><?php echo e($product->name); ?></h6>
                                                                <?php if($product->display_has_discount): ?>
                                                                    <span class="text-danger"><?php echo e(number_format($product->display_final_price)); ?>

                                                                        đ</span>
                                                                    <small class="text-muted d-block"><del><?php echo e(number_format($product->display_base_price)); ?>

                                                                            đ</del> <?php echo e($product->display_discount_label); ?></small>
                                                                <?php else: ?>
                                                                    <span><?php echo e(number_format($variant?->price ?? 0)); ?> đ</span>
                                                                <?php endif; ?>
                                                            </div>

                                                        </a>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="latest-product__text">
                        <h4>Sản phẩm bán chạy</h4>
                        <div class="latest-product__slider owl-carousel">
                            <?php $__empty_1 = true; $__currentLoopData = $bestSellingProducts->chunk(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $chunk): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <div class="latest-prdouct__slider__item">
                                    <?php $__currentLoopData = $chunk; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bsProduct): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php $bsVariant = $bsProduct->variants->first(); ?>
                                        <a href="<?php echo e(route('products.show', $bsProduct->id)); ?>" class="latest-product__item">
                                            <div class="latest-product__item__pic">
                                                <img src="<?php echo e($bsProduct->image ? asset('storage/' . $bsProduct->image) : asset('frontend/images/product/product-1.jpg')); ?>"
                                                    width="60" height="60" class="rounded" style="object-fit:cover"
                                                    alt="<?php echo e($bsProduct->name); ?>"
                                                    onerror="this.src='<?php echo e(asset('frontend/images/product/product-1.jpg')); ?>';">
                                            </div>
                                            <div class="latest-product__item__text">
                                                <h6><?php echo e($bsProduct->name); ?></h6>
                                                <?php if($bsProduct->display_has_discount): ?>
                                                    <span class="text-danger"><?php echo e(number_format($bsProduct->display_final_price)); ?>

                                                        đ</span>
                                                    <small class="text-muted d-block"><del><?php echo e(number_format($bsProduct->display_base_price)); ?>

                                                            đ</del> <?php echo e($bsProduct->display_discount_label); ?></small>
                                                <?php else: ?>
                                                    <span><?php echo e(number_format($bsVariant?->price ?? 0)); ?> đ</span>
                                                <?php endif; ?>
                                            </div>
                                        </a>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <div class="latest-prdouct__slider__item">
                                    <p class="text-muted px-2 py-3">Chưa có dữ liệu</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="latest-product__text">
                        <h4>Đánh giá cao</h4>
                        <div class="latest-product__slider owl-carousel">
                            <?php $__empty_1 = true; $__currentLoopData = $topRatedProducts->chunk(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $chunk): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <div class="latest-prdouct__slider__item">
                                    <?php $__currentLoopData = $chunk; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $trProduct): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php $trVariant = $trProduct->variants->first(); ?>
                                        <a href="<?php echo e(route('products.show', $trProduct->id)); ?>" class="latest-product__item">
                                            <div class="latest-product__item__pic">
                                                <img src="<?php echo e($trProduct->image ? asset('storage/' . $trProduct->image) : asset('frontend/images/product/product-1.jpg')); ?>"
                                                    width="60" height="60" class="rounded" style="object-fit:cover"
                                                    alt="<?php echo e($trProduct->name); ?>"
                                                    onerror="this.src='<?php echo e(asset('frontend/images/product/product-1.jpg')); ?>';">
                                            </div>
                                            <div class="latest-product__item__text">
                                                <h6><?php echo e($trProduct->name); ?></h6>
                                                <?php if($trProduct->display_has_discount): ?>
                                                    <span class="text-danger"><?php echo e(number_format($trProduct->display_final_price)); ?>

                                                        đ</span>
                                                    <small class="text-muted d-block"><del><?php echo e(number_format($trProduct->display_base_price)); ?>

                                                            đ</del> <?php echo e($trProduct->display_discount_label); ?></small>
                                                <?php else: ?>
                                                    <span><?php echo e(number_format($trVariant?->price ?? 0)); ?> đ</span>
                                                <?php endif; ?>
                                            </div>
                                        </a>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <div class="latest-prdouct__slider__item">
                                    <p class="text-muted px-2 py-3">Chưa có dữ liệu</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Latest Product Section End -->

    <section class="featured spad pt-0">
        <div class="container">
            <div class="section-title">
                <h2>Bán chạy và đánh giá cao</h2>
            </div>
            <div class="row">
                <?php $__empty_1 = true; $__currentLoopData = $bestSellerTopRatedProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php $variant = $item->variants->first(); ?>
                    <div class="col-lg-4 col-md-6 col-sm-6 mb-3">
                        <div class="latest-product__item border rounded p-2 h-100">
                            <a href="<?php echo e(route('products.show', $item->id)); ?>" class="latest-product__item__pic">
                                <img src="<?php echo e($item->image ? asset('storage/' . $item->image) : asset('frontend/images/product/product-1.jpg')); ?>"
                                    width="75" height="75" class="rounded" style="object-fit:cover" alt="<?php echo e($item->name); ?>"
                                    onerror="this.src='<?php echo e(asset('frontend/images/product/product-1.jpg')); ?>';">
                            </a>
                            <div class="latest-product__item__text">
                                <h6><a href="<?php echo e(route('products.show', $item->id)); ?>"><?php echo e($item->name); ?></a></h6>
                                <?php if($item->display_has_discount): ?>
                                    <span class="text-danger"><?php echo e(number_format($item->display_final_price)); ?> đ</span>
                                <?php else: ?>
                                    <span><?php echo e(number_format($variant?->price ?? 0)); ?> đ</span>
                                <?php endif; ?>
                                <small class="d-block text-muted">Đã bán: <?php echo e(number_format($item->total_sold ?? 0)); ?></small>
                                <small class="d-block text-warning">Đánh giá:
                                    <?php echo e(number_format($item->avg_rating ?? 0, 1)); ?>/5</small>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="col-12 text-muted text-center">Chưa có dữ liệu sản phẩm nổi bật</div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Blog Section Begin -->
    <section class="from-blog spad">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="section-title from-blog__title">
                        <h2>Tin tức</h2>
                    </div>
                </div>
            </div>

            <div class="row">
                <?php $__empty_1 = true; $__currentLoopData = $blogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $blog): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="col-lg-4 col-md-4 col-sm-6">
                        <div class="blog__item">
                            <div class="blog__item__pic">
                                <img src="<?php echo e($blog->image ? asset('storage/' . $blog->image) : asset('frontend/images/blog/blog-1.jpg')); ?>"
                                    alt="<?php echo e($blog->title); ?>">
                            </div>
                            <div class="blog__item__text">
                                <ul>
                                    <li>
                                        <i class="fa fa-calendar-o"></i>
                                        <?php echo e(\Carbon\Carbon::parse($blog->created_at)->format('d/m/Y')); ?>

                                    </li>
                                    <li>
                                        <i class="fa fa-comment-o"></i> 0
                                    </li>
                                </ul>
                                <h5>
                                    <a href="<?php echo e(route('blogs.show', $blog->slug)); ?>">
                                        <?php echo e($blog->title); ?>

                                    </a>
                                </h5>
                                <p><?php echo e(\Illuminate\Support\Str::limit($blog->summary, 100)); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="col-12 text-center text-muted">
                        Chưa có bài viết nào.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <!-- Blog Section End -->

    <script>
        // Define popup function to support additional options (for wishlist and cart functions)
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

        // ---- Sản phẩm nổi bật: wishlist & cart (đồng bộ với centralized popup system) ----

        function homeWishlist(productId) {
            <?php if(auth()->guard()->check()): ?>
                                                                    // Authenticated: AJAX request
                                                                    const wishlistForm = document.getElementById('wishlist-home-' + productId);
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
                            // Toggle heart icon color
                            const heartBtn = document.querySelector(`[onclick*="homeWishlist(${productId})"]`);
                            if (heartBtn) {
                                const icon = heartBtn.querySelector('i');
                                if (data.isAddedToWishlist) {
                                    if (icon) icon.style.color = '#e74c3c';
                                } else {
                                    if (icon) icon.style.color = '';
                                }
                            }
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
                        console.error('Wishlist error:', error);
                        const errorMsg = error.message || 'Có lỗi xảy ra. Vui lòng thử lại.';
                        popup('error', 'Lỗi', errorMsg, {
                            confirmButtonText: 'Đóng'
                        });
                    });
            <?php else: ?>
                // Not authenticated: Show login popup using centralized system
                popup('warning', 'Bạn chưa đăng nhập', 'Hãy đăng nhập để thêm sản phẩm vào yêu thích.', {
                    showCancelButton: true,
                    confirmButtonText: 'Đăng nhập',
                    cancelButtonText: 'Để sau'
                }).then(result => {
                    if (result.isConfirmed) {
                        window.location.href = '<?php echo e(route("login")); ?>';
                    }
                });
            <?php endif; ?>
                                        }

        function homeAddCart(productId, hasVariant) {
            if (!hasVariant) {
                // Use centralized popup system
                popup('warning', 'Chưa thể thêm', 'Sản phẩm chưa có phiên bản. Vui lòng xem trang chi tiết.', {
                    confirmButtonText: 'Xem chi tiết'
                }).then(result => {
                    if (result.isConfirmed) {
                        window.location.href = '<?php echo e(url("/products")); ?>/' + productId;
                    }
                });
                return;
            }

            // AJAX add to cart
            const cartForm = document.getElementById('cart-home-' + productId);
            if (!cartForm) return;

            const formData = new FormData(cartForm);
            fetch('<?php echo e(route("cart.add")); ?>', {
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
                        // Show popup using centralized system (will detect "thêm vào giỏ" and show with checkout button)
                        window.ocopPopup.notify('success', 'Thêm vào giỏ hàng thành công');
                    } else {
                        const errorMsg = data.message || 'Có lỗi xảy ra';
                        window.ocopPopup.notify('error', errorMsg);
                    }
                })
                .catch(error => {
                    console.error('Cart error:', error);
                    window.ocopPopup.notify('error', 'Có lỗi xảy ra. Vui lòng thử lại.');
                });
        }

    </script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\luanvan\resources\views/pages/home.blade.php ENDPATH**/ ?>