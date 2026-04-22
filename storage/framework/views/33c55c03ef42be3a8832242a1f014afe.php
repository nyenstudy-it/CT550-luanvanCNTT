<?php if($paginator->hasPages()): ?>
    <nav class="order-pagination" aria-label="Phân trang">
        
        <?php if($paginator->onFirstPage()): ?>
            <span class="page-btn disabled"><i class="fa fa-chevron-left"></i></span>
        <?php else: ?>
            <a href="<?php echo e($paginator->previousPageUrl()); ?>" class="page-btn"><i class="fa fa-chevron-left"></i></a>
        <?php endif; ?>

        
        <?php $__currentLoopData = $elements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $element): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if(is_string($element)): ?>
                <span class="page-btn disabled"><?php echo e($element); ?></span>
            <?php endif; ?>
            <?php if(is_array($element)): ?>
                <?php $__currentLoopData = $element; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if($page == $paginator->currentPage()): ?>
                        <span class="page-btn active"><?php echo e($page); ?></span>
                    <?php else: ?>
                        <a href="<?php echo e($url); ?>" class="page-btn"><?php echo e($page); ?></a>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        
        <?php if($paginator->hasMorePages()): ?>
            <a href="<?php echo e($paginator->nextPageUrl()); ?>" class="page-btn"><i class="fa fa-chevron-right"></i></a>
        <?php else: ?>
            <span class="page-btn disabled"><i class="fa fa-chevron-right"></i></span>
        <?php endif; ?>
    </nav>
<?php endif; ?><?php /**PATH C:\xampp\htdocs\luanvan\resources\views/vendor/pagination/custom.blade.php ENDPATH**/ ?>