<!-- Header Section Begin -->
<header class="header">
    <div class="header__top">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 col-md-6">
                    <div class="header__top__left">
                        <ul>
                            <li><i class="fa fa-envelope"></i>senhongocopp@gmail.com</li>
                            <li>Giao hàng tận nơi miễn phí với đơn hàng chỉ từ 199k</li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6">
                    <div class="header__top__right">
                        <div class="header__top__right__social">
                            <a href="#"><i class="fa fa-facebook"></i></a>
                            <a href="#"><i class="fa fa-twitter"></i></a>
                            <a href="#"><i class="fa fa-linkedin"></i></a>
                            <a href="#"><i class="fa fa-pinterest-p"></i></a>
                        </div>
                        <div class="header__top__right__language">
                            <img src="<?php echo e(asset('frontend/images/language.jpg')); ?>" alt="">
                            <div>Tiếng Việt</div>
                            <span class="arrow_carrot-down"></span>
                            <ul>
                                <li><a href="#">Tiếng Anh</a></li>
                                <li><a href="#">Tiếng Việt</a></li>
                            </ul>
                        </div>
                        <div class="header__top__right__auth">

                            <?php if(auth()->guard()->check()): ?>
                                <?php if(auth()->user()->role === 'customer'): ?>
                                    <div class="dropdown">
                                        <a href="#">
                                            <i class="fa fa-user"></i>
                                            <?php echo e(auth()->user()->name); ?>

                                        </a>
                                        <ul class="header__menu__dropdown">
                                            <li>
                                                <a href="<?php echo e(route('customer.profile')); ?>">
                                                    Hồ sơ cá nhân
                                                </a>
                                            </li>
                                            <li>
                                                <a href="<?php echo e(route('orders.my')); ?>">
                                                    Đơn hàng của tôi
                                                </a>
                                            </li>

                                            <li>
                                                <a href="<?php echo e(route('discounts')); ?>">
                                                    Mã giảm giá của tôi
                                                </a>
                                            </li>

                                            <li>
                                                <form action="<?php echo e(route('logout')); ?>" method="POST">
                                                    <?php echo csrf_field(); ?>
                                                    <button type="submit">
                                                        Đăng xuất
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="dropdown">
                                    <a href="<?php echo e(route('login')); ?>">
                                        <i class="fa fa-user"></i> Đăng nhập
                                    </a>
                                    <ul class="header__menu__dropdown">
                                        <li>
                                            <a href="<?php echo e(route('register')); ?>">
                                                Đăng ký
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="row">
            <div class="col-lg-3">
                <div class="header__logo">
                    <a href="./index.html"><img src="<?php echo e(asset('frontend/images/logo.png')); ?>" alt=""></a>
                </div>
            </div>
            <div class="col-lg-7">
                <nav class="header__menu">
                    <ul>
                        <li class="<?php echo e(request()->routeIs('pages.trangchu', 'pages.home') ? 'active' : ''); ?>">
                            <a href="<?php echo e(route('pages.trangchu')); ?>">Trang chủ</a>
                        </li>
                        <li class="<?php echo e(request()->routeIs('products.index', 'products.show') ? 'active' : ''); ?>">
                            <a href="<?php echo e(route('products.index')); ?>">Sản phẩm</a>
                        </li>
                        <li class="<?php echo e(request()->routeIs('blogs.index', 'blogs.show') ? 'active' : ''); ?>">
                            <a href="<?php echo e(route('blogs.index')); ?>">Tin tức</a>
                        </li>
                        <li class="<?php echo e(request()->routeIs('contact') ? 'active' : ''); ?>">
                            <a href="<?php echo e(route('contact')); ?>">Liên hệ</a>
                        </li>
                    </ul>
                </nav>
            </div>
            <div class="col-lg-2">
                <div class="header__cart">
                    <ul>
                        <li>
                            <a href="<?php echo e(route('wishlist.index')); ?>">
                                <i class="fa fa-heart"></i>
                                <span><?php echo e(auth()->check() ? auth()->user()->wishlists()->count() : 0); ?></span>
                            </a>
                        </li>

                        <li>
                            <a href="<?php echo e(route('cart.list')); ?>">
                                <i class="fa fa-shopping-bag"></i>
                                <span><?php echo e(session('cart') ? count(session('cart')) : 0); ?></span>
                            </a>
                        </li>

                        <?php if(auth()->guard()->check()): ?>
                            <?php if(auth()->user()->role === 'customer'): ?>
                                <li class="nav-item dropdown position-relative">
                                    <a href="#" class="customer-notification-toggle position-relative">
                                        <i class="fa fa-bell"></i>
                                        <?php if($unreadCount > 0): ?>
                                            <span class="notification-badge"><?php echo e($unreadCount); ?></span>
                                        <?php endif; ?>
                                    </a>

                                    <ul class="customer-notification-dropdown">
                                        <?php
                                            $customerNotifications = collect($notifications ?? [])->filter();
                                        ?>
                                        <?php $__empty_1 = true; $__currentLoopData = $customerNotifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $noti): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                            <li>
                                                <a href="<?php echo e(route('customer.notifications.read', $noti->id)); ?>"
                                                    class="dropdown-item <?php echo e(!$noti->is_read ? 'unread' : ''); ?> <?php echo e($noti->type === 'chat_staff_reply' ? 'js-open-store-chat' : ''); ?>"
                                                    data-notification-type="<?php echo e($noti->type); ?>"
                                                    data-read-url="<?php echo e(route('customer.notifications.read', $noti->id)); ?>">

                                                    <h6 class="fw-normal mb-1"><?php echo e($noti->title); ?></h6>
                                                    <small class="text-muted d-block"><?php echo e($noti->display_content); ?></small>
                                                    <small class="text-muted"><?php echo e($noti->created_at->diffForHumans()); ?></small>
                                                </a>
                                            </li>

                                            <hr class="dropdown-divider">

                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                            <li><span class="dropdown-item text-center">Không có thông báo</span></li>
                                        <?php endif; ?>

                                        <li>
                                            <a href="<?php echo e(route('customer.notifications')); ?>"
                                                class="dropdown-item text-center view-all">
                                                Xem tất cả
                                            </a>

                                        </li>
                                    </ul>
                                </li>

                            <?php endif; ?>
                        <?php endif; ?>
                    </ul>
                </div>

            </div>
        </div>
        <div class="humberger__open">
            <i class="fa fa-bars"></i>
        </div>
    </div>
</header>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const bell = document.querySelector('.customer-notification-toggle');
        const dropdown = document.querySelector('.customer-notification-dropdown');
        const storeChatLinks = document.querySelectorAll('.js-open-store-chat');
        const storeChatToggle = document.getElementById('store-chatbox-toggle');

        if (bell && dropdown) {
            bell.addEventListener('click', function (e) {
                e.preventDefault();
                dropdown.classList.toggle('show');
            });

            document.addEventListener('click', function (e) {
                if (!bell.contains(e.target) && !dropdown.contains(e.target)) {
                    dropdown.classList.remove('show');
                }
            });
        }

        storeChatLinks.forEach(function (link) {
            link.addEventListener('click', function (e) {
                e.preventDefault();

                const readUrl = this.dataset.readUrl;
                if (readUrl) {
                    fetch(readUrl, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin',
                    }).catch(function () {
                        // Ignore silently, popup still opens.
                    });
                }

                dropdown.classList.remove('show');
                if (storeChatToggle) {
                    storeChatToggle.click();
                }
            });
        });
    });

</script><?php /**PATH C:\xampp\htdocs\luanvan\resources\views/pages/components/header.blade.php ENDPATH**/ ?>