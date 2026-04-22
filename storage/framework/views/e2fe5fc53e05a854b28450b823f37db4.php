<?php
    $adminAlerts = [
        ['key' => 'success', 'class' => 'success', 'icon' => 'fa-check-circle'],
        ['key' => 'error', 'class' => 'danger', 'icon' => 'fa-exclamation-circle'],
        ['key' => 'warning', 'class' => 'warning', 'icon' => 'fa-exclamation-triangle'],
        ['key' => 'info', 'class' => 'info', 'icon' => 'fa-info-circle'],
    ];
?>

<?php $__currentLoopData = $adminAlerts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php if(session($alert['key'])): ?>
        <div class="alert alert-<?php echo e($alert['class']); ?> alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
            <i class="fa <?php echo e($alert['icon']); ?>"></i>
            <span><?php echo e(session($alert['key'])); ?></span>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php /**PATH C:\xampp\htdocs\luanvan\resources\views/admin/partials/alert.blade.php ENDPATH**/ ?>