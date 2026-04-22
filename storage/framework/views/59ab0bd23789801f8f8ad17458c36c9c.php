

<?php $__env->startSection('content'); ?>

    <div class="container">

        <h3>Thanh toán MoMo</h3>

        <div class="card p-4">

            <p>Mã đơn hàng: <strong>#<?php echo e($order->id); ?></strong></p>

            <p>Số tiền cần thanh toán:</p>

            <h4 style="color:#d82d8b">
                <?php echo e(number_format($order->total_amount)); ?> VND
            </h4>

            <form action="<?php echo e(route('momo.process', $order->id)); ?>" method="POST">
                <?php echo csrf_field(); ?>

                <button class="site-btn" style="background:#d82d8b">
                    Thanh toán với MoMo
                </button>

            </form>

        </div>

    </div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\luanvan\resources\views/pages/payment/momo.blade.php ENDPATH**/ ?>