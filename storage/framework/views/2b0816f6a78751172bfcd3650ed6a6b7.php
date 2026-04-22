

<?php $__env->startSection('hero'); ?>
    <?php echo $__env->make('pages.components.hero', ['showBanner' => false, 'heroNormal' => true], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

    <style>
        .recently-viewed-card {
            display: block;
            border: 1px solid #e8e8e8;
            border-radius: 10px;
            overflow: hidden;
            background: #fff;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .recently-viewed-card:hover {
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.12);
            transform: translateY(-3px);
            text-decoration: none;
        }

        .recently-viewed-card__image {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }

        .recently-viewed-card__body {
            padding: 10px 12px 12px;
        }

        .recently-viewed-card__name {
            color: #1c1c1c;
            font-weight: 600;
            font-size: 14px;
            line-height: 1.35;
            min-height: 38px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            margin-bottom: 6px;
        }

        .recently-viewed-card__price {
            color: #dd2222;
            font-size: 15px;
            font-weight: 700;
        }

        .recently-viewed-card__base {
            font-size: 12px;
            color: #8d8d8d;
            display: block;
        }

        .keep-line-breaks {
            white-space: pre-line;
        }

        .product-detail-note {
            margin: 18px 0 22px;
            padding: 14px 16px;
            border: 1px solid #ebebeb;
            border-radius: 8px;
            background: #fafcf7;
        }

        .product-detail-note__title {
            margin-bottom: 8px;
            font-size: 15px;
            font-weight: 700;
            color: #1c1c1c;
        }

        .product-detail-note__content {
            margin: 0;
            color: #555;
            line-height: 1.7;
        }

    </style>

    <section class="breadcrumb-section set-bg" data-setbg="<?php echo e(asset('frontend/images/breadcrumb.jpg')); ?>">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <div class="breadcrumb__text">
                        <h2><?php echo e($product->name); ?></h2>
                        <div class="breadcrumb__option">
                            <a href="<?php echo e(route('pages.home')); ?>">Trang chủ</a>
                            <a href="#"><?php echo e($product->category?->name); ?></a>
                            <span><?php echo e($product->name); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="product-details spad">
        <div class="container">
            <div class="row">
                
                <div class="col-lg-6 col-md-6">
                    <div class="product__details__pic">
                        <div class="product__details__pic__item">
                            <img id="mainImage"
                                src="<?php echo e($product->image ? asset('storage/' . $product->image) : asset('frontend/images/product/product-1.jpg')); ?>"
                                alt="<?php echo e($product->name); ?>"
                                style="width:100%; height:420px; object-fit:cover; border-radius:6px;"
                                onerror="this.src='<?php echo e(asset('frontend/images/product/product-1.jpg')); ?>';">
                        </div>
                        <div class="product__details__pic__slider owl-carousel">
                            
                            <?php $__currentLoopData = $product->variants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php $__empty_1 = true; $__currentLoopData = $variant->images; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $img): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <img src="<?php echo e(asset('storage/' . $img->image_path)); ?>"
                                        data-imgbigurl="<?php echo e(asset('storage/' . $img->image_path)); ?>"
                                        data-variant-id="<?php echo e($variant->id); ?>"
                                        data-is-primary="<?php echo e($img->is_primary ? 1 : 0); ?>"
                                        class="variant-image"
                                        alt="<?php echo e($product->name); ?> - <?php echo e($variant->color ?? $variant->volume ?? $variant->weight ?? $variant->size ?? $variant->sku); ?>"
                                        onerror="this.src='<?php echo e(asset('frontend/images/product/product-1.jpg')); ?>';">
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    
                                    <?php if($product->image): ?>
                                        <img src="<?php echo e(asset('storage/' . $product->image)); ?>"
                                            data-imgbigurl="<?php echo e(asset('storage/' . $product->image)); ?>"
                                            data-variant-id="<?php echo e($variant->id); ?>"
                                            data-is-primary="1"
                                            class="variant-image"
                                            alt="<?php echo e($product->name); ?> - <?php echo e($variant->color ?? $variant->volume ?? $variant->weight ?? $variant->size ?? $variant->sku); ?>"
                                            onerror="this.src='<?php echo e(asset('frontend/images/product/product-1.jpg')); ?>';">
                                    <?php endif; ?>
                                <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            
                            <?php $__currentLoopData = ($product->images ?? collect())->whereNull('product_variant_id'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $img): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <img src="<?php echo e(asset('storage/' . $img->image_path)); ?>"
                                    data-imgbigurl="<?php echo e(asset('storage/' . $img->image_path)); ?>"
                                    data-variant-id=""
                                    data-is-primary="<?php echo e($img->is_primary ? 1 : 0); ?>"
                                    class="variant-image"
                                    alt="<?php echo e($product->name); ?>"
                                    onerror="this.src='<?php echo e(asset('frontend/images/product/product-1.jpg')); ?>';">
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                </div>

                
                <div class="col-lg-6 col-md-6">
                    <div class="product__details__text">
                        <h3><?php echo e($product->name); ?></h3>

                        
                        <div class="product__details__rating">
                            
                            <div>
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <i class="fa fa-star<?php echo e($i <= ($product->ocop_star ?? 0) ? '' : '-o'); ?>"></i>
                                <?php endfor; ?>
                                <?php if($product->ocop_star): ?>
                                    <small class="ms-2"><?php echo e($product->ocop_star); ?> OCOP</small>
                                <?php endif; ?>
                            </div>

                            
                            <div class="mt-1">
                                <?php $avg = $product->avg_rating ?? 0;
                                $count = $product->approvedReviews()->count(); ?>
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <i class="fa <?php echo e($i <= round($avg) ? 'fa-star text-warning' : 'fa-star-o text-muted'); ?>"></i>
                                <?php endfor; ?>
                                <small class="ms-2"><?php echo e(number_format($avg, 1)); ?>/5 (<?php echo e($count); ?>)</small>
                            </div>
                        </div>

                        <div class="product__details__price" id="priceWrap">
                            <?php if($productPricing['has_discount']): ?>
                                <span id="price" class="text-danger"><?php echo e(number_format((int) max(0, $productPricing['final_price'] ?? 0), 0)); ?> đ</span>
                                <small class="text-muted d-block"><del id="basePrice"><?php echo e(number_format((int) max(0, $productPricing['base_price'] ?? 0), 0)); ?> đ</del> <span id="discountBadge"><?php echo e($productPricing['discount_label']); ?></span></small>
                            <?php else: ?>
                                <span id="price"><?php echo e(number_format((int) max(0, $productPricing['base_price'] ?? 0), 0)); ?> đ</span>
                            <?php endif; ?>
                        </div>

                        <p class="keep-line-breaks"><?php echo e($product->description ?? 'Chưa có mô tả.'); ?></p>

                        
                        <div class="product__details__option mb-3">
                            <span>Chọn loại:</span>
                            <div class="d-flex flex-wrap gap-2 mt-2">
                                <?php $__currentLoopData = $product->variants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $variantPriceData = $variantPricing[$variant->id] ?? [
                                            'base_price' => (float) $variant->price,
                                            'final_price' => (float) $variant->price,
                                            'has_discount' => false,
                                            'discount_label' => null,
                                        ];
                                        $variantMainImage = optional($variant->primaryImage)->image_path
                                            ?? optional($variant->images->first())->image_path
                                            ?? $product->image;
                                    ?>
                                    <button type="button" class="variant-btn btn btn-outline-secondary btn-sm"
                                        data-id="<?php echo e($variant->id); ?>"
                                        data-price="<?php echo e((float) ($variantPriceData['base_price'] ?? 0)); ?>"
                                        data-final-price="<?php echo e((float) ($variantPriceData['final_price'] ?? 0)); ?>"
                                        data-has-discount="<?php echo e($variantPriceData['has_discount'] ? 1 : 0); ?>"
                                        data-discount-label="<?php echo e($variantPriceData['discount_label'] ?? ''); ?>"
                                        data-stock="<?php echo e(max(0, (int) ($variant->inventory?->quantity ?? 0))); ?>"
                                        data-manufacture-date="<?php echo e($variant->display_manufacture_date ? \Carbon\Carbon::parse($variant->display_manufacture_date)->toDateString() : ''); ?>"
                                        data-expired-at="<?php echo e($variant->display_expired_at ? \Carbon\Carbon::parse($variant->display_expired_at)->toDateString() : ''); ?>"
                                        data-image="<?php echo e($variantMainImage ? asset('storage/' . $variantMainImage) : asset('frontend/images/product/product-1.jpg')); ?>">
                                        <?php echo e($variant->color ?? $variant->volume ?? $variant->weight ?? $variant->size ?? $variant->sku); ?>

                                    </button>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>

                        
                        <?php
                            $inWishlist = auth()->check()
                                && \App\Models\Wishlist::where('user_id', auth()->id())
                                    ->where('product_id', $product->id)->exists();
                        ?>

                        <small id="stockText" class="d-block mb-2 text-muted"></small>

                        <form id="addToCartForm" action="<?php echo e(route('cart.add')); ?>" method="POST">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="variant_id" id="selectedVariant">

                            <div style="display:flex;align-items:stretch;gap:8px;flex-wrap:wrap;">

                                
                                <div style="display:flex;align-items:center;height:44px;border:2px solid #e0e0e0;border-radius:4px;overflow:hidden;background:#fff;">
                                    <button type="button"
                                        onclick="var q=document.getElementById('quantityInput');if(parseInt(q.value)>1)q.value=parseInt(q.value)-1;"
                                        style="width:36px;height:44px;border:none;background:none;font-size:18px;color:#555;cursor:pointer;">−</button>
                                    <input type="number" name="quantity" id="quantityInput" value="1" min="1"
                                        style="width:40px;height:44px;border:none;border-left:1px solid #e0e0e0;border-right:1px solid #e0e0e0;text-align:center;font-size:15px;outline:none;-moz-appearance:textfield;appearance:textfield;">
                                    <button type="button"
                                        onclick="var q=document.getElementById('quantityInput');q.value=parseInt(q.value)+1;"
                                        style="width:36px;height:44px;border:none;background:none;font-size:18px;color:#555;cursor:pointer;">+</button>
                                </div>

                                
                                <button type="button" id="addToCartBtn"
                                    style="height:44px;padding:0 22px;background:#7fad39;color:#fff;border:2px solid #7fad39;border-radius:4px;font-size:13px;font-weight:700;letter-spacing:.6px;cursor:pointer;white-space:nowrap;">
                                    THÊM GIỎ HÀNG
                                </button>

                                
                                <?php if(auth()->guard()->check()): ?>
                                    <button type="button" id="wishlist-btn"
                                        title="<?php echo e($inWishlist ? 'Bỏ yêu thích' : 'Thêm yêu thích'); ?>"
                                        style="width:44px;height:44px;border:2px solid <?php echo e($inWishlist ? '#e74c3c' : '#e0e0e0'); ?>;border-radius:4px;background:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                                        <i class="fa <?php echo e($inWishlist ? 'fa-heart' : 'fa-heart-o'); ?>"
                                            id="wishlist-icon"
                                            style="font-size:18px;color:<?php echo e($inWishlist ? '#e74c3c' : '#aaa'); ?>;"></i>
                                    </button>
                                <?php else: ?>
                                    <a href="<?php echo e(route('login')); ?>" class="js-login-required-wishlist" title="Đăng nhập để thêm yêu thích"
                                        style="width:44px;height:44px;border:2px solid #e0e0e0;border-radius:4px;background:#fff;display:flex;align-items:center;justify-content:center;text-decoration:none;">
                                        <i class="fa fa-heart-o" style="font-size:18px;color:#aaa;"></i>
                                    </a>
                                <?php endif; ?>

                            </div>
                        </form>

                        
                        <ul>
                            <li><b>Danh mục</b> <span><?php echo e($product->category?->name); ?></span></li>
                            <li><b>Nhà cung cấp</b> <span><?php echo e($product->supplier?->name); ?></span></li>
                            <li><b>Trọng lượng</b> <span><?php echo e($product->weight ?? '0.5 kg'); ?></span></li>
                            <li><b>NSX</b> <span id="manufactureInfo">Chưa cập nhật</span></li>
                            <li><b>HSD</b> <span id="expiryInfo">Chưa cập nhật</span></li>
                            <li><b>Tình trạng</b>
                                <span id="stockStatus" class="fw-semibold"><?php echo e(max(0, (int) ($product->inventory_total ?? 0)) > 0 ? 'Còn hàng' : 'Hết hàng'); ?></span>
                            </li>
                            <li><b>Chia sẻ</b>
                                <div class="share">
                                    <a href="#"><i class="fa fa-facebook"></i></a>
                                    <a href="#"><i class="fa fa-twitter"></i></a>
                                    <a href="#"><i class="fa fa-instagram"></i></a>
                                    <a href="#"><i class="fa fa-pinterest"></i></a>
                                </div>
                            </li>
                        </ul>

                    </div>
                </div>

                
                <div class="col-lg-12">
                    <div class="product__details__tab">
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-toggle="tab" href="#tabs-1" role="tab">Thông tin sản
                                    phẩm</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#tabs-3" role="tab">Đánh giá
                                    (<?php echo e($product->approvedReviews()->count()); ?>)</a>
                            </li>
                        </ul>
                        <div class="tab-content">

                            
                            <div class="tab-pane active" id="tabs-1" role="tabpanel">
                                <div class="product__details__tab__desc">
                                    <p class="keep-line-breaks"><?php echo e($product->description); ?></p>

                                    <?php if(!empty($product->usage_instructions)): ?>
                                        <div class="product-detail-note">
                                            <div class="product-detail-note__title">Hướng dẫn sử dụng</div>
                                            <p class="product-detail-note__content keep-line-breaks"><?php echo e($product->usage_instructions); ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            
                            <div class="tab-pane" id="tabs-3" role="tabpanel">
                                <div class="product__details__tab__desc">

                                    
                                    <?php $__empty_1 = true; $__currentLoopData = $product->approvedReviews()->latest()->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <div class="review-item p-3 mb-3 border rounded" id="review-<?php echo e($review->id); ?>">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <div>
                                                    <?php
                                                        $reviewerName = $review->is_anonymous 
                                                            ? 'Khách hàng ẩn danh'
                                                            : ($review->customer?->user?->name ?? $review->customer?->name ?? 'Khách');
                                                    ?>
                                                    <strong><?php echo e($reviewerName); ?></strong>
                                                    <?php if($review->is_anonymous): ?>
                                                        <span class="badge bg-info ms-2" style="font-size:11px;">Ẩn danh</span>
                                                    <?php endif; ?>
                                                    <div>
                                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                                            <i class="fa fa-star<?php echo e($i <= $review->rating ? '' : '-o'); ?>" style="color:#ff9900;"></i>
                                                        <?php endfor; ?>
                                                    </div>
                                                </div>
                                                <div class="text-end">
                                                    <small class="text-muted d-block"><?php echo e($review->created_at->format('d/m/Y H:i')); ?></small>
                                                    <button class="btn btn-sm btn-link text-decoration-none review-like-btn" data-id="<?php echo e($review->id); ?>">
                                                        <i class="fa fa-heart text-danger"></i>
                                                        <span class="like-count"><?php echo e($review->likes()->count()); ?></span>
                                                    </button>
                                                </div>
                                            </div>

                                            <p class="mb-1 keep-line-breaks"><?php echo e($review->content); ?></p>

                                            
                                            <div class="review-replies ps-3">
                                                <?php $__currentLoopData = $review->replies()->where('status','approved')->latest()->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rep): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <div class="reply-item mb-2">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <strong><?php echo e($rep->author_name); ?></strong>
                                                                <?php if($rep->is_admin): ?>
                                                                    <span class="badge bg-primary ms-2">Admin</span>
                                                                <?php endif; ?>
                                                                <div class="text-muted small"><?php echo e($rep->created_at->format('d/m/Y H:i')); ?></div>
                                                            </div>
                                                            <div></div>
                                                        </div>
                                                        <p class="mb-0 keep-line-breaks"><?php echo e($rep->content); ?></p>
                                                    </div>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </div>

                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <div class="text-center text-muted py-3">
                                            Hiện chưa có đánh giá nào, hãy mua hàng và chia sẻ cảm nhận của bạn!
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>


                        </div>
                    </div>
                </div>

                <?php if(isset($recentViewedProducts) && $recentViewedProducts->count()): ?>
                    <div class="col-lg-12 mt-4">
                        <div class="section-title mb-3">
                            <h4>Sản phẩm vừa xem</h4>
                        </div>

                        <div class="recently-viewed-carousel owl-carousel">
                            <?php $__currentLoopData = $recentViewedProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $recentProduct): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $recentVariant = $recentProduct->variants->first();
                                    $recentImage = optional(optional($recentVariant)->images->first())->image_path ?? $recentProduct->image;
                                    $recentBasePrice = (float) ($recentProduct->display_base_price ?? optional($recentVariant)->price ?? 0);
                                    $recentFinalPrice = (float) ($recentProduct->display_final_price ?? $recentBasePrice);
                                    $recentHasDiscount = (bool) ($recentProduct->display_has_discount ?? false);
                                ?>

                                <div class="item">
                                    <a href="<?php echo e(route('products.show', $recentProduct->id)); ?>" class="recently-viewed-card">
                                        <img class="recently-viewed-card__image"
                                            src="<?php echo e($recentImage ? asset('storage/' . $recentImage) : asset('frontend/images/product/product-1.jpg')); ?>"
                                            alt="<?php echo e($recentProduct->name); ?>">

                                        <div class="recently-viewed-card__body">
                                            <div class="recently-viewed-card__name"><?php echo e($recentProduct->name); ?></div>

                                            <?php if($recentHasDiscount): ?>
                                                <div class="recently-viewed-card__price"><?php echo e(number_format($recentFinalPrice)); ?> đ</div>
                                                <span class="recently-viewed-card__base"><del><?php echo e(number_format($recentBasePrice)); ?> đ</del></span>
                                            <?php else: ?>
                                                <div class="recently-viewed-card__price"><?php echo e(number_format($recentBasePrice)); ?> đ</div>
                                            <?php endif; ?>
                                        </div>
                                    </a>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </section>

<?php $__env->stopSection(); ?>

<script>
    document.addEventListener('DOMContentLoaded', function () {

        const loginUrl = <?php echo json_encode(route('login'), 15, 512) ?>;

        const mainImage = document.getElementById('mainImage');
        const priceWrapEl = document.getElementById('priceWrap');
        const selectedVariantInput = document.getElementById('selectedVariant');
        const quantityInput = document.getElementById('quantityInput');
        const stockText = document.getElementById('stockText');
        const manufactureInfo = document.getElementById('manufactureInfo');
        const expiryInfo = document.getElementById('expiryInfo');
        const addToCartForm = document.getElementById('addToCartForm');
        const addToCartBtn = document.getElementById('addToCartBtn');

        const variantBtns = document.querySelectorAll('.variant-btn');
        const variantImgs = document.querySelectorAll('.variant-image');
        const $slider = $('.product__details__pic__slider');
        const variantBtnMap = Array.from(variantBtns).reduce((acc, btn) => {
            acc[btn.dataset.id] = btn;
            return acc;
        }, {});

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

        document.querySelectorAll('.js-login-required-wishlist').forEach(function (el) {
            el.addEventListener('click', function (e) {
                e.preventDefault();
                popup('info', 'Bạn chưa đăng nhập', 'Hãy đăng nhập để thêm sản phẩm vào yêu thích.', {
                    showCancelButton: true,
                    confirmButtonText: 'Đăng nhập',
                    cancelButtonText: 'Để sau'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = loginUrl;
                    }
                });
            });
        });

        // Wishlist button AJAX
        const wishlistBtn = document.getElementById('wishlist-btn');
        if (wishlistBtn) {
            wishlistBtn.addEventListener('click', function (e) {
                e.preventDefault();

                const productId = <?php echo json_encode($product->id, 15, 512) ?>;
                const wishlistUrl = <?php echo json_encode(route('wishlist.toggle', $product->id), 512) ?>;
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                fetch(wishlistUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({})
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const icon = document.getElementById('wishlist-icon');
                        const btn = document.getElementById('wishlist-btn');

                        if (data.isAddedToWishlist) {
                            // Added to wishlist
                            icon.classList.remove('fa-heart-o');
                            icon.classList.add('fa-heart');
                            icon.style.color = '#e74c3c';
                            btn.style.borderColor = '#e74c3c';
                            btn.title = 'Bỏ yêu thích';

                            popup('success', 'Đã thêm vào yêu thích', 'Sản phẩm đã được lưu vào danh sách yêu thích của bạn.');
                        } else {
                            // Removed from wishlist
                            icon.classList.remove('fa-heart');
                            icon.classList.add('fa-heart-o');
                            icon.style.color = '#aaa';
                            btn.style.borderColor = '#e0e0e0';
                            btn.title = 'Thêm yêu thích';

                            popup('success', 'Đã xoá khỏi yêu thích', 'Sản phẩm đã được xoá khỏi danh sách yêu thích của bạn.');
                        }
                    } else {
                        popup('error', 'Lỗi', data.message || 'Không thể xử lý yêu cầu');
                    }
                })
                .catch(error => {
                    console.error('Wishlist AJAX error:', error);
                    const errorMsg = error.message || 'Lỗi khi xử lý yêu cầu';
                    popup('error', 'Lỗi', errorMsg);
                });
            });
        }

        $slider.owlCarousel({
            items: 4,
            margin: 10,
            dots: false,
            nav: true,
            smartSpeed: 300
        });

        const $recentlyViewedCarousel = $('.recently-viewed-carousel');
        if ($recentlyViewedCarousel.length) {
            $recentlyViewedCarousel.owlCarousel({
                loop: false,
                margin: 14,
                dots: false,
                nav: true,
                smartSpeed: 350,
                responsive: {
                    0: { items: 1 },
                    576: { items: 2 },
                    768: { items: 3 },
                    992: { items: 4 },
                    1200: { items: 5 }
                }
            });
        }

        function changeMainImage(src) {
            if (!src) return;
            if (!mainImage) return;
            mainImage.style.opacity = 0;
            setTimeout(() => {
                mainImage.src = src;
                mainImage.style.opacity = 1;
            }, 200);
        }

        function formatDateToVN(dateValue) {
            if (!dateValue) return 'Chưa cập nhật';

            const normalizedValue = String(dateValue).trim();

            if (/^\d{2}\/\d{2}\/\d{4}$/.test(normalizedValue)) {
                return normalizedValue;
            }

            const isoMatch = normalizedValue.match(/^(\d{4})-(\d{2})-(\d{2})/);
            if (isoMatch) {
                const [, year, month, day] = isoMatch;
                return `${day}/${month}/${year}`;
            }

            const parts = normalizedValue.split('-');
            if (parts.length !== 3) return 'Chưa cập nhật';

            const [year, month, day] = parts;
            return `${day}/${month}/${year}`;
        }

        function updateManufactureAndExpiry(btn) {
            if (!btn) return;
            if (manufactureInfo) {
                manufactureInfo.textContent = formatDateToVN(btn.dataset.manufactureDate);
            }
            if (expiryInfo) {
                expiryInfo.textContent = formatDateToVN(btn.dataset.expiredAt);
            }
        }

        // Like button AJAX
        document.querySelectorAll('.review-like-btn').forEach(function(btn){
            btn.addEventListener('click', function(e){
                e.preventDefault();
                const reviewId = this.getAttribute('data-id');
                fetch("/reviews/"+reviewId+"/like", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({})
                }).then(r => r.json()).then(data => {
                    if (!data) return;
                    btn.querySelector('.like-count').textContent = data.count;
                }).catch(()=>{});
            });
        });

        function updateStock(stock) {
            stock = parseInt(stock) || 0;
            if (!quantityInput || !stockText || !addToCartBtn) {
                console.error('Missing required elements:', { quantityInput: !!quantityInput, stockText: !!stockText, addToCartBtn: !!addToCartBtn });
                return;
            }
            quantityInput.max = stock;

            if (stock <= 0) {
                stockText.innerText = "Hết hàng";
                stockText.classList.add("text-danger");
                addToCartBtn.disabled = true;
                quantityInput.value = 0;
            } else {
                stockText.innerText = "Còn " + stock + " sản phẩm";
                stockText.classList.remove("text-danger");
                addToCartBtn.disabled = false;

                if (parseInt(quantityInput.value) > stock) {
                    quantityInput.value = stock;
                }

                if (parseInt(quantityInput.value) < 1) {
                    quantityInput.value = 1;
                }
            }
        }

        if (addToCartForm) {
            if (!addToCartBtn) {
                console.error('❌ addToCartBtn not found!');
            } else {
                addToCartBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    
                    const stock = parseInt(quantityInput.max || '0', 10);
                    const selectedVariant = selectedVariantInput.value;
                    const quantity = parseInt(quantityInput.value || '0', 10);
                    
                    // Validation: variant selected
                    if (!selectedVariant) {
                        popup('warning', 'Chưa chọn phân loại', 'Vui lòng chọn loại sản phẩm trước khi thêm vào giỏ hàng.');
                        return;
                    }

                    // Validation: stock > 0
                    if (stock <= 0) {
                        popup('error', 'Hết hàng', 'Rất tiếc, sản phẩm này đã hết hàng. Vui lòng quay lại sau hoặc chọn sản phẩm khác.');
                        return;
                    }

                    // Validation: quantity <= stock
                    if (quantity > stock) {
                        popup('warning', 'Số lượng vượt quá tồn kho', 'Vui lòng nhập số lượng ≤ ' + stock);
                        return;
                    }

                    // Validation: quantity > 0
                    if (quantity <= 0) {
                        popup('warning', 'Số lượng không hợp lệ', 'Vui lòng nhập số lượng > 0');
                        return;
                    }

                    // All validations passed - submit via AJAX
                    submitAddToCartAjax();
                });
            }
        } else {
            console.error('❌ addToCartForm not found! Element might not be in DOM yet');
        }

        function submitAddToCartAjax() {
            const formData = new FormData(addToCartForm);
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            // Show loading state
            const originalText = addToCartBtn.textContent;
            addToCartBtn.disabled = true;
            addToCartBtn.textContent = 'Đang xử lý...';

            fetch(addToCartForm.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: formData
            })
            .then(response => {
                if (!response.ok && response.status !== 302 && response.status !== 301) {
                    // For redirects or errors, try to parse JSON first
                    return response.json().then(data => {
                        throw new Error(data.message || 'Lỗi khi thêm vào giỏ hàng');
                    }).catch(() => {
                        throw new Error('Lỗi khi thêm vào giỏ hàng');
                    });
                }
                return response.json();
            })
            .then(data => {
                addToCartBtn.disabled = false;
                addToCartBtn.textContent = originalText;

                if (data.error) {
                    popup('error', 'Lỗi', data.error);
                } else {
                    showAddToCartSuccessPopup();
                }
            })
            .catch(error => {
                console.error('❌ AJAX error:', error);
                addToCartBtn.disabled = false;
                addToCartBtn.textContent = originalText;
                popup('error', 'Lỗi', error.message || 'Lỗi khi thêm vào giỏ hàng');
            });
        }

        function showAddToCartSuccessPopup() {
            const cartUrl = <?php echo json_encode(route('cart.list'), 15, 512) ?>;
            
            popup('success', 'Thêm vào giỏ hàng thành công', 'Bạn muốn thanh toán ngay hay tiếp tục mua sắm?', {
                showCancelButton: true,
                confirmButtonText: 'Thanh toán',
                cancelButtonText: 'Tiếp tục mua sắm'
            }).then(result => {
                if (result.isConfirmed) {
                    window.location.href = cartUrl;
                }
            });
        }

        function applyVariantSelection(btn, options = {}) {
            if (!btn) return;

            const variantId = btn.dataset.id;
            const price = btn.dataset.price;
            const finalPrice = btn.dataset.finalPrice || price;
            const hasDiscount = btn.dataset.hasDiscount === '1';
            const discountLabel = btn.dataset.discountLabel || '';
            const stock = parseInt(btn.dataset.stock || '0', 10);
            const variantImage = btn.dataset.image;

            const variantThumbs = Array.from(variantImgs).filter((img) => img.dataset.variantId === variantId);
            const targetThumb = variantThumbs.find((img) => img.dataset.isPrimary === '1') || variantThumbs[0] || null;
            const defaultSrc = (targetThumb && (targetThumb.dataset.imgbigurl || targetThumb.src)) || variantImage;
            const selectedSrc = options.preferredSrc || defaultSrc;

            selectedVariantInput.value = variantId;

            if (priceWrapEl) {
                const baseText = new Intl.NumberFormat('vi-VN').format(price) + ' đ';
                const finalText = new Intl.NumberFormat('vi-VN').format(finalPrice) + ' đ';

                if (hasDiscount) {
                    priceWrapEl.innerHTML = '<span id="price" class="text-danger">' + finalText + '</span>' +
                        '<small class="text-muted d-block"><del id="basePrice">' + baseText + '</del> <span id="discountBadge">' + discountLabel + '</span></small>';
                } else {
                    priceWrapEl.innerHTML = '<span id="price">' + baseText + '</span>';
                }
            }

            variantBtns.forEach((b) => {
                b.classList.remove('btn-primary');
                b.classList.add('btn-outline-secondary');
            });
            btn.classList.remove('btn-outline-secondary');
            btn.classList.add('btn-primary');

            updateStock(stock);
            updateManufactureAndExpiry(btn);

            if (selectedSrc) {
                changeMainImage(selectedSrc);
            }

            const targetIndex = Number.isInteger(options.preferredIndex)
                ? options.preferredIndex
                : (targetThumb ? Array.from(variantImgs).indexOf(targetThumb) : -1);

            if (targetIndex >= 0 && options.syncSlider !== false) {
                $slider.trigger('to.owl.carousel', [targetIndex, 300, true]);
            }
        }

        variantBtns.forEach((btn) => {
            btn.addEventListener('click', function () {
                applyVariantSelection(this);
            });
        });

        variantImgs.forEach((imgEl) => {
            imgEl.addEventListener('click', function () {
                const src = imgEl.dataset.imgbigurl || imgEl.src;
                if (src) {
                    changeMainImage(src);
                }

                const variantId = imgEl.dataset.variantId;
                if (variantId && variantBtnMap[variantId]) {
                    const thumbIndex = Array.from(variantImgs).indexOf(imgEl);
                    applyVariantSelection(variantBtnMap[variantId], {
                        preferredSrc: src,
                        preferredIndex: thumbIndex,
                        syncSlider: false,
                    });
                }
            });
        });

        if (quantityInput) {
            quantityInput.addEventListener('input', function () {
                const max = parseInt(quantityInput.max);
                let value = parseInt(quantityInput.value);

                if (!Number.isNaN(max) && value > max) quantityInput.value = max;
                if (value < 1) quantityInput.value = 1;
            });
        }

        if (variantBtns.length > 0) {
            const defaultVariantBtn = Array.from(variantBtns).find((btn) => {
                return parseInt(btn.dataset.stock || '0', 10) > 0;
            }) || variantBtns[0];

            applyVariantSelection(defaultVariantBtn);
        }

        const reviewForm = document.querySelector('.review-form');
        if (reviewForm) {
            reviewForm.addEventListener('submit', function (e) {
                const rating = reviewForm.querySelector('[name="rating"]').value;
                const content = reviewForm.querySelector('[name="content"]').value.trim();
                if (!rating || !content) {
                    e.preventDefault();
                    popup('warning', 'Thiếu thông tin đánh giá', 'Vui lòng chọn số sao và viết đánh giá!');
                }
            });
        }

    });
</script>
<?php echo $__env->make('layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\luanvan\resources\views/pages/product_detail.blade.php ENDPATH**/ ?>