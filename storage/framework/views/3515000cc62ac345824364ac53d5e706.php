

<?php $__env->startSection('content'); ?>
    <div class="container-fluid pt-4 px-4">
        <div class="bg-light rounded p-4">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">
                    Biến thể của sản phẩm: <b><?php echo e($product->name); ?></b>
                </h5>

                <a href="<?php echo e(route('admin.products.variants.create', $product->id)); ?>" class="btn btn-primary mb-3">
                    + Thêm biến thể
                </a>

            </div>
            <style>
                .variant-table {
                    table-layout: fixed;
                }

                .variant-table th,
                .variant-table td {
                    vertical-align: middle;
                    font-size: 14px;
                    padding: 10px 8px;
                }

                .variant-sku {
                    font-weight: 700;
                    font-size: 13px;
                    word-break: break-word;
                }

                .variant-attrs {
                    display: flex;
                    flex-wrap: wrap;
                    gap: 6px;
                }

                .variant-attr-chip {
                    display: inline-block;
                    padding: 3px 8px;
                    border-radius: 999px;
                    background: #eef2ff;
                    border: 1px solid #dbe4ff;
                    color: #334155;
                    font-size: 12px;
                    line-height: 1.4;
                }

                .variant-thumb {
                    width: 52px;
                    height: 52px;
                    object-fit: cover;
                    border-radius: 8px;
                    border: 1px solid #e5e7eb;
                }

                .variant-price {
                    font-weight: 700;
                    white-space: nowrap;
                }

                .variant-actions {
                    display: flex;
                    flex-direction: column;
                    gap: 6px;
                    align-items: stretch;
                }

                .variant-actions .btn {
                    width: 100%;
                }
            </style>

            <table class="table table-bordered align-middle variant-table">
                <colgroup>
                    <col style="width: 6%">
                    <col style="width: 22%">
                    <col style="width: 10%">
                    <col style="width: 34%">
                    <col style="width: 14%">
                    <col style="width: 14%">
                </colgroup>
                <thead class="table-light">
                    <tr class="text-center">
                        <th>STT</th>
                        <th>SKU</th>
                        <th>Ảnh</th>
                        <th>Thuộc tính biến thể</th>
                        <th>Giá</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $product->variants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $variant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="text-center"><?php echo e($index + 1); ?></td>
                            <td><span class="variant-sku"><?php echo e($variant->sku); ?></span></td>
                            <td class="text-center">
                                <?php if($variant->primaryImage): ?>
                                    <img src="<?php echo e(asset('storage/' . $variant->primaryImage->image_path)); ?>" class="variant-thumb"
                                        alt="<?php echo e($variant->sku); ?>">
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
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
                                    <div class="variant-attrs">
                                        <?php $__currentLoopData = $attrs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <span class="variant-attr-chip"><?php echo e($attr); ?></span>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <span class="variant-price"><?php echo e(number_format($variant->price)); ?> đ</span>
                            </td>
                            <td>
                                <div class="variant-actions">
                                    <a href="<?php echo e(route('admin.products.variants.edit', $variant->id)); ?>"
                                        class="btn btn-sm btn-warning">
                                        Sửa
                                    </a>

                                    <form method="POST" action="<?php echo e(route('admin.products.variants.destroy', $variant->id)); ?>">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button class="btn btn-sm btn-danger"
                                            onclick="return confirm('Xóa biến thể <?php echo e($variant->sku); ?> này?')">
                                            Xóa
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted">
                                Chưa có biến thể nào cho sản phẩm này.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <a href="<?php echo e(route('admin.products.list')); ?>" class="btn btn-secondary btn-sm">
                ← Quay lại
            </a>


        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.layout_admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\luanvan\resources\views/admin/products/variants/index.blade.php ENDPATH**/ ?>