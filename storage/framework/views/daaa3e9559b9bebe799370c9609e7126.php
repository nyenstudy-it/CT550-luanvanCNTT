<div class="row mb-4">
<div class="col-md-4">
    <?php if($product->image): ?>
        <img src="<?php echo e(asset('storage/' . $product->image)); ?>" class="img-fluid rounded border">
    <?php else: ?>
        <div class="text-muted text-center py-5 border rounded">
            Không có ảnh
        </div>
    <?php endif; ?>
</div>


    <div class="col-md-8">
        <h5 class="mb-3"><?php echo e($product->name); ?></h5>

        <p><b>Danh mục:</b> <?php echo e($product->category->name ?? '—'); ?></p>
        <p><b>Nhà cung cấp:</b> <?php echo e($product->supplier->name ?? '—'); ?></p>

        <p><b>Mô tả:</b><br>
            <?php echo e($product->description ?? '—'); ?>

        </p>

        <p><b>Hướng dẫn sử dụng:</b><br>
            <?php echo e($product->usage_instructions ?? '—'); ?>

        </p>

        <p><b>Bảo quản:</b>
            <?php echo e($product->storage_instructions ?? '—'); ?>

        </p>

        <p>
            <b>OCOP:</b>
            <?php echo e($product->ocop_star ?? '—'); ?> ⭐
            (<?php echo e($product->ocop_year ?? '—'); ?>)
        </p>

        <p>
            <b>Trạng thái:</b>
            <?php if($product->status === 'active'): ?>
                <span class="badge bg-success">Đang bán</span>
            <?php else: ?>
                <span class="badge bg-secondary">Ngừng bán</span>
            <?php endif; ?>
        </p>
    </div>
</div>

<hr>

<h6 class="mb-3">Danh sách biến thể</h6>

<table class="table table-bordered align-middle">
    <thead class="table-light">
        <tr class="text-center">
            <th>Ảnh</th>
            <th>SKU</th>
            <th>Thuộc tính</th>
            <th>Giá</th>
            <th>Tồn kho</th>
        </tr>
    </thead>
    <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $product->variants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
                <td class="text-center">
                    <?php if($variant->primaryImage): ?>
                        <img src="<?php echo e(asset('storage/' . $variant->primaryImage->image_path)); ?>" width="60" height="60"
                            class="rounded border">
                    <?php else: ?>
                        —
                    <?php endif; ?>
                </td>

                <td><?php echo e($variant->sku); ?></td>

                <td>
                    <?php
    $attrs = [];
    if ($variant->color)
        $attrs[] = 'Màu: ' . $variant->color;
    if ($variant->size)
        $attrs[] = 'Size: ' . $variant->size;
    if ($variant->volume)
        $attrs[] = 'Dung tích: ' . $variant->volume;
    if ($variant->weight)
        $attrs[] = 'Khối lượng: ' . $variant->weight;
                    ?>

                    <?php if(count($attrs)): ?>
                        <ul class="mb-0 ps-3">
                            <?php $__currentLoopData = $attrs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li><?php echo e($attr); ?></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    <?php else: ?>
                        —
                    <?php endif; ?>
                </td>

                <td class="text-end">
                    <?php echo e(number_format($variant->price)); ?> đ
                </td>

                <td class="text-center">
                    <?php echo e($variant->inventory->quantity ?? 0); ?>

                </td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
                <td colspan="5" class="text-center text-muted">
                    Chưa có biến thể
                </td>
            </tr>
        <?php endif; ?>
    </tbody>
</table><?php /**PATH C:\xampp\htdocs\luanvan\resources\views/admin/products/popup.blade.php ENDPATH**/ ?>