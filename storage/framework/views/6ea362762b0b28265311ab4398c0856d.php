

<?php $__env->startSection('content'); ?>
            <div class="container-fluid pt-4 px-4">
                <div class="bg-light rounded p-4">

                    <div class="row g-3 mb-4">
                        <div class="col-12 col-sm-4">
                            <div class="border rounded bg-white p-3 h-100">
                                <small class="text-muted d-block mb-1">Tổng sản phẩm</small>
                                <h4 class="mb-0"><?php echo e($products->total()); ?></h4>
                            </div>
                        </div>
                        <div class="col-12 col-sm-4">
                            <div class="border rounded bg-white p-3 h-100">
                                <small class="text-muted d-block mb-1">Đang hiển thị</small>
                                <h4 class="mb-0 text-primary"><?php echo e($products->count()); ?></h4>
                            </div>
                        </div>
                        <div class="col-12 col-sm-4">
                            <div class="border rounded bg-white p-3 h-100">
                                <small class="text-muted d-block mb-1">Trang hiện tại</small>
                                <h4 class="mb-0 text-success"><?php echo e($products->currentPage()); ?>/<?php echo e($products->lastPage()); ?></h4>
                            </div>
                        </div>
                    </div>

                    <form method="GET" action="<?php echo e(route('admin.products.list')); ?>" class="row g-3 mb-3">
                        <div class="col-md-2">
                            <label class="form-label">Danh mục</label>
                            <select name="category_id" class="form-select">
                                <option value="">-- Tất cả --</option>
                                <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($category->id); ?>" <?php echo e(request('category_id') == $category->id ? 'selected' : ''); ?>>
                                        <?php echo e($category->name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Nhà phân phối</label>
                            <select name="supplier_id" class="form-select">
                                <option value="">-- Tất cả --</option>
                                <?php $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($supplier->id); ?>" <?php echo e(request('supplier_id') == $supplier->id ? 'selected' : ''); ?>>
                                        <?php echo e($supplier->name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Trạng thái</label>
                            <select name="status" class="form-select">
                                <option value="">-- Tất cả --</option>
                                <option value="active" <?php echo e(request('status') == 'active' ? 'selected' : ''); ?>>
                                    Đang bán
                                </option>
                                <option value="inactive" <?php echo e(request('status') == 'inactive' ? 'selected' : ''); ?>>
                                    Ngừng bán
                                </option>
                            </select>
                        </div>

                        <div class="col-md-1">
                            <label class="form-label">Giá từ</label>
                            <input type="number" name="min_price" value="<?php echo e(request('min_price')); ?>" class="form-control">
                        </div>

                        <div class="col-md-1">
                            <label class="form-label">Đến</label>
                            <input type="number" name="max_price" value="<?php echo e(request('max_price')); ?>" class="form-control">
                        </div>

                        <div class="col-md-1 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary w-100">Lọc</button>
                        </div>

                    </form>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">Danh sách sản phẩm</h6>
                        <a href="<?php echo e(route('admin.products.create')); ?>" class="btn btn-primary btn-sm">
                            + Thêm sản phẩm
                        </a>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th width="50">STT</th>
                                    <th width="80">Ảnh</th>
                                    <th>Tên sản phẩm</th>
                                    <th>Danh mục</th>
                                    <th>Nhà phân phối</th>
                                    <th>Giá</th>
                                    
                                    <th>Trạng thái</th>
                                    <th class="text-center">Số biến thể</th>
                                    <th width="180">Thao tác</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                            <tr>
                                                                <td><?php echo e($index + 1); ?></td>
                                                                <td class="text-center">
                                                                    <?php if($product->image): ?>
                                                                        <img src="<?php echo e(asset('storage/' . $product->image)); ?>" width="60" height="60"
                                                                            class="rounded border" style="object-fit: cover">
                                                                    <?php else: ?>
                                                                        <span class="text-muted">—</span>
                                                                    <?php endif; ?>
                                                                </td>


                                                                <td><?php echo e($product->name); ?></td>

                                                                <td><?php echo e($product->category->name ?? '—'); ?></td>

                                                                <td><?php echo e($product->supplier->name ?? '—'); ?></td>

                                                                <td>
                                                                    <?php if($product->variants->count()): ?>
                                                                        <?php
        $minPrice = $product->variants->min('price');
        $maxPrice = $product->variants->max('price');
                                                                        ?>

                                                                        <?php if($minPrice == $maxPrice): ?>
                                                                            <?php echo e(number_format($minPrice, 0, ',', '.')); ?> đ
                                                                        <?php else: ?>
                                                                            <?php echo e(number_format($minPrice, 0, ',', '.')); ?>

                                                                            –
                                                                            <?php echo e(number_format($maxPrice, 0, ',', '.')); ?> đ
                                                                        <?php endif; ?>
                                                                    <?php else: ?>
                                                                        <span class="text-muted">Chưa có giá</span>
                                                                    <?php endif; ?>
                                                                </td>

                                                                
                                                                

                                                                <td>
                                                                    <?php if($product->status === 'active'): ?>
                                                                        <span class="badge bg-success">Đang bán</span>
                                                                    <?php else: ?>
                                                                        <span class="badge bg-secondary">Ngừng bán</span>
                                                                    <?php endif; ?>
                                                                </td>

                                                                <td class="text-center">
                                                                    <?php echo e($product->variants_count); ?>

                                                                </td>
                                                                <td>
                                                                    <a href="<?php echo e(route('admin.products.edit', $product->id)); ?>"
                                                                        class="btn btn-sm btn-warning mb-1">
                                                                        Sửa
                                                                    </a>

                                                                    <a href="<?php echo e(route('admin.products.variants.index', $product->id)); ?>"
                                                                        class="btn btn-sm btn-info mb-1">
                                                                        Biến thể
                                                                    </a>

                                                                    <button class="btn btn-sm btn-secondary mb-1 btn-show-product" data-id="<?php echo e($product->id); ?>">
                                                                        Xem chi tiết
                                                                    </button>


                                                                    <form action="<?php echo e(route('admin.products.destroy', $product->id)); ?>" method="POST"
                                                                        class="d-inline" onsubmit="return confirm('Vô hiệu hoá sản phẩm này?')">
                                                                        <?php echo csrf_field(); ?>
                                                                        <?php echo method_field('DELETE'); ?>
                                                                        <button class="btn btn-sm btn-danger">
                                                                            Ẩn
                                                                        </button>
                                                                    </form>
                                                                </td>
                                                            </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="10" class="text-center text-muted">
                                            Chưa có sản phẩm nào
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>

                        </table>
                        <?php echo e($products->appends(request()->query())->links()); ?>


                    </div>
                </div>
            </div>


            <div class="modal fade" id="productDetailModal" tabindex="-1">
                <div class="modal-dialog modal-xl modal-dialog-scrollable">
                    <div class="modal-content">

                        <div class="modal-header">
                            <h5 class="modal-title">Chi tiết sản phẩm</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body" id="productDetailContent">
                            <div class="text-center text-muted py-5">
                                Đang tải dữ liệu...
                            </div>
                        </div>

                    </div>
                </div>
            </div>
    <?php $__env->startPush('scripts'); ?>
        <script>
            document.addEventListener('click', function (e) {
                if (e.target.classList.contains('btn-show-product')) {

                    let productId = e.target.dataset.id;

                    let modalEl = document.getElementById('productDetailModal');
                    let modal = new bootstrap.Modal(modalEl);

                    modal.show();

                    document.getElementById('productDetailContent').innerHTML =
                        '<div class="text-center py-5">Đang tải...</div>';

                    fetch('/admin/products/' + productId + '/popup')
                        .then(res => res.text())
                        .then(html => {
                            document.getElementById('productDetailContent').innerHTML = html;
                        });
                }
            });
        </script>
    <?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.layout_admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\luanvan\resources\views/admin/products/list.blade.php ENDPATH**/ ?>