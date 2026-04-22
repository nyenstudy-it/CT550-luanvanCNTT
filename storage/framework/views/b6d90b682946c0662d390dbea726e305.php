

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
                        <h2>Liên hệ</h2>
                        <div class="breadcrumb__option">
                            <a href="<?php echo e(route('pages.home')); ?>">Trang chủ</a>
                            <span>Liên hệ</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Breadcrumb Section End -->

    <!-- Contact Info Section Begin -->
    <section class="contact spad">
        <div class="container">
            <div class="row text-center mb-5">
                <?php
                    $contacts = [
                        ['icon' => 'icon_phone', 'title' => 'Điện thoại', 'text' => '+84 346 600 661'],
                        ['icon' => 'icon_pin_alt', 'title' => 'Địa chỉ', 'text' => 'Đại học Cần Thơ, đường 3/2, Phường Ninh Kiều, Cần Thơ'],
                        ['icon' => 'icon_clock_alt', 'title' => 'Giờ mở cửa', 'text' => '08:00 - 16:00'],
                        ['icon' => 'icon_mail_alt', 'title' => 'Email', 'text' => 'senhongocopp@gmail.com'],
                    ];
                ?>
                <?php $__currentLoopData = $contacts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="contact__widget">
                            <span class="<?php echo e($c['icon']); ?>"></span>
                            <h5><?php echo e($c['title']); ?></h5>
                            <p><?php echo e($c['text']); ?></p>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <!-- Form & Map Row -->
            <div class="row">
                <!-- Contact Form -->
                <div class="col-lg-6 mb-4">
                    <h4 class="mb-4">Gửi liên hệ</h4>

                    <form action="<?php echo e(route('contact.send')); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <div class="mb-3">
                            <input type="text" name="name" placeholder="Họ và tên" required class="form-control">
                        </div>
                        <div class="mb-3">
                            <input type="email" name="email" placeholder="Email" required class="form-control">
                        </div>
                        <div class="mb-3">
                            <textarea name="message" placeholder="Nội dung liên lạc" required class="form-control"
                                rows="6"></textarea>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="site-btn">GỬI</button>
                        </div>
                    </form>
                </div>

                <!-- Google Map -->
                <div class="col-lg-6 mb-4">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3918.014680446516!2d105.77459341526087!3d10.02945296294637!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31a0883ecf5e0c1f%3A0x2c0c83b6f8b0d4e7!2sNinh%20Ki%E1%BB%81u%2C%20C%E1%BA%A7n%20Th%C6%A1!5e0!3m2!1svi!2s!4v1586106673811!5m2!1svi!2s"
                        width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                </div>
            </div>
        </div>
    </section>
    <!-- Contact Section End -->
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\luanvan\resources\views/pages/contact.blade.php ENDPATH**/ ?>