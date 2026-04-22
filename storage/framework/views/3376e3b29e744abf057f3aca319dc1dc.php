

<?php $__env->startSection('content'); ?>
    <div class="container-fluid pt-4 px-4">
        <div class="bg-light rounded p-4">

            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div>
                    <h5 class="mb-1">Danh sách danh mục sản phẩm</h5>
                </div>
                <a href="<?php echo e(route('admin.categories.create')); ?>" class="btn btn-sm btn-success">+ Thêm danh mục</a>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-12 col-sm-4">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Tổng danh mục</small>
                        <h4 class="mb-0"><?php echo e($summary['total'] ?? 0); ?></h4>
                    </div>
                </div>
                <div class="col-12 col-sm-4">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Có hình ảnh</small>
                        <h4 class="mb-0 text-success"><?php echo e($summary['with_image'] ?? 0); ?></h4>
                    </div>
                </div>
                <div class="col-12 col-sm-4">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Chưa có hình ảnh</small>
                        <h4 class="mb-0 text-warning"><?php echo e($summary['without_image'] ?? 0); ?></h4>
                    </div>
                </div>
            </div>

            <form method="GET" action="<?php echo e(route('admin.categories.list')); ?>"
                class="row g-3 mb-4 border rounded bg-white p-3">
                <div class="col-md-5">
                    <label class="form-label">Từ khóa</label>
                    <input type="text" name="keyword" value="<?php echo e(request('keyword')); ?>" class="form-control"
                        placeholder="Tên danh mục hoặc mô tả...">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Ảnh</label>
                    <select name="has_image" class="form-select">
                        <option value="">-- Tất cả --</option>
                        <option value="yes" <?php echo e(request('has_image') === 'yes' ? 'selected' : ''); ?>>Có ảnh</option>
                        <option value="no" <?php echo e(request('has_image') === 'no' ? 'selected' : ''); ?>>Chưa có ảnh</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">Lọc</button>
                    <a href="<?php echo e(route('admin.categories.list')); ?>" class="btn btn-secondary">Đặt lại</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="60">STT</th>
                            <th>Tên danh mục</th>
                            <th width="100">Hình ảnh</th>
                            <th>Mô tả</th>
                            <th width="180">Thao tác</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($categories->firstItem() + $index); ?></td>
                                <td><?php echo e($category->name); ?></td>
                                <td>
                                    <?php if($category->image_url): ?>
                                        <img src="<?php echo e(asset('storage/' . $category->image_url)); ?>" width="60" height="60"
                                            class="rounded object-fit-cover">
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo e($category->description ?? '—'); ?></td>
                                <td>
                                    <a href="<?php echo e(route('admin.categories.edit', $category->id)); ?>"
                                        class="btn btn-sm btn-warning mb-1">
                                        Sửa
                                    </a>

                                    <form action="<?php echo e(route('admin.categories.destroy', $category->id)); ?>" method="POST"
                                        class="d-inline" onsubmit="return confirm('Xóa danh mục này?')">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button class="btn btn-sm btn-danger mb-1">
                                            Xóa
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">Chưa có danh mục nào</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                <?php echo e($categories->appends(request()->query())->links()); ?>

            </div>

        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.layout_admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\luanvan\resources\views/admin/categories/list.blade.php ENDPATH**/ ?>