

<?php $__env->startSection('hero'); ?>
    <?php echo $__env->make('pages.components.hero', ['showBanner' => false], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

    <!-- Breadcrumb -->
    <section class="breadcrumb-section set-bg" data-setbg="<?php echo e(asset('frontend/images/breadcrumb.jpg')); ?>">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <div class="breadcrumb__text">
                        <h2>Thông báo</h2>
                        <div class="breadcrumb__option">
                            <a href="<?php echo e(route('pages.home')); ?>">Trang chủ</a>
                            <span>Thông báo</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Notification List -->
    <div class="container py-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
            <h3 class="mb-0">Thông báo của tôi</h3>
            <form method="POST" action="<?php echo e(route('customer.notifications.markAllRead')); ?>">
                <?php echo csrf_field(); ?>
                <button type="submit" class="btn btn-sm btn-outline-primary" <?php echo e($unreadCount > 0 ? '' : 'disabled'); ?>>
                    Đọc tất cả
                </button>
            </form>
        </div>

        <div class="list-group">
            <?php $__empty_1 = true; $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $noti): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <a href="<?php echo e(route('customer.notifications.read', $noti->id)); ?>" class="list-group-item list-group-item-action">
                    
                    <h6 class="fw-bold mb-1"><?php echo e($noti->title); ?></h6>

                    
                    <p class="mb-1 text-muted"><?php echo e($noti->display_content); ?></p>

                    
                    <small class="text-muted"><?php echo e($noti->created_at->diffForHumans()); ?></small>
                </a>
                <hr class="my-1">
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="list-group-item text-center text-muted">
                    Không có thông báo
                </div>
            <?php endif; ?>
        </div>

        
        <?php if($notifications->count() > 0): ?>
            <div class="mt-3 text-center">
                <a href="<?php echo e(route('customer.notifications')); ?>" class="btn btn-primary btn-sm">Làm mới</a>
            </div>
        <?php endif; ?>
    </div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\luanvan\resources\views/pages/notifications.blade.php ENDPATH**/ ?>