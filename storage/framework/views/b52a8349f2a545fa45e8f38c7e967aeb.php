

<?php $__env->startSection('content'); ?>
    <div class="container-fluid pt-4 px-2">
        <div class="bg-light rounded p-4">

            <style>
                .admin-action-btn {
                    min-width: 78px;
                    font-weight: 600;
                }

                .admin-badge {
                    font-size: 11px;
                    font-weight: 600;
                    padding: 6px 10px;
                    border-radius: 999px;
                }
            </style>

            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div>
                    <h5 class="mb-1">Danh sách mã giảm giá</h5>
                </div>
                <a href="<?php echo e(route('admin.discounts.create')); ?>" class="btn btn-success btn-sm">+ Thêm mã giảm giá</a>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Tổng mã</small>
                        <h4 class="mb-0"><?php echo e($summary['total']); ?></h4>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Đang hiệu lực</small>
                        <h4 class="mb-0 text-success"><?php echo e($summary['active']); ?></h4>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Theo sản phẩm</small>
                        <h4 class="mb-0 text-primary"><?php echo e($summary['product_scoped']); ?></h4>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Toàn shop</small>
                        <h4 class="mb-0 text-dark"><?php echo e($summary['global']); ?></h4>
                    </div>
                </div>
            </div>

            <form method="GET" action="<?php echo e(route('admin.discounts.index')); ?>"
                class="row g-3 mb-4 border rounded bg-white p-3">
                <div class="col-md-3">
                    <label class="form-label">Mã giảm giá</label>
                    <input type="text" name="code" value="<?php echo e(request('code')); ?>" class="form-control" placeholder="Nhập mã">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Loại</label>
                    <select name="type" class="form-select">
                        <option value="">-- Tất cả --</option>
                        <option value="percent" <?php echo e(request('type') == 'percent' ? 'selected' : ''); ?>>Phần trăm (%)</option>
                        <option value="fixed" <?php echo e(request('type') == 'fixed' ? 'selected' : ''); ?>>Tiền cố định</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Phạm vi</label>
                    <select name="scope" class="form-select">
                        <option value="">-- Tất cả --</option>
                        <option value="all" <?php echo e(request('scope') == 'all' ? 'selected' : ''); ?>>Toàn shop</option>
                        <option value="product" <?php echo e(request('scope') == 'product' ? 'selected' : ''); ?>>Theo sản phẩm</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="">-- Tất cả --</option>
                        <option value="active" <?php echo e(request('status') == 'active' ? 'selected' : ''); ?>>Đang áp dụng</option>
                        <option value="upcoming" <?php echo e(request('status') == 'upcoming' ? 'selected' : ''); ?>>Chưa bắt đầu</option>
                        <option value="expired" <?php echo e(request('status') == 'expired' ? 'selected' : ''); ?>>Hết hạn</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Đối tượng</label>
                    <select name="audience" class="form-select">
                        <option value="">-- Tất cả --</option>
                        <?php $__currentLoopData = \App\Models\Discount::audienceOptions(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $audienceValue => $audienceLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($audienceValue); ?>" <?php echo e(request('audience') === $audienceValue ? 'selected' : ''); ?>>
                                <?php echo e($audienceLabel); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="col-md-1 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">Lọc</button>
                    <a href="<?php echo e(route('admin.discounts.index')); ?>" class="btn btn-outline-secondary">Đặt lại</a>
                </div>
            </form>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">Bảng mã giảm giá</h6>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="50">STT</th>
                            <th width="150">Mã giảm giá</th>
                            <th width="100">Loại</th>
                            <th width="100">Giá trị</th>
                            <th width="150">Đối tượng</th>
                            <th width="220">Phạm vi áp dụng</th>
                            <th width="100">Đã sử dụng / Giới hạn</th>
                            <th width="100">Trạng thái</th>
                            <th width="150">Ngày tạo</th>
                            <th width="150">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $discounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $discount): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($discounts->firstItem() + $index); ?></td>
                                <td><?php echo e($discount->code); ?></td>
                                <td>
                                    <?php if($discount->type == 'percent'): ?>
                                        Phần trăm (%)
                                    <?php else: ?>
                                        Tiền cố định
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <?php echo e($discount->value_label); ?>

                                </td>
                                <td><?php echo e($discount->audience_label); ?></td>
                                <td>
                                    <?php if($discount->products->isEmpty()): ?>
                                        <span class="badge bg-dark admin-badge">Toàn shop</span>
                                    <?php else: ?>
                                        <span class="badge bg-primary admin-badge"><?php echo e($discount->products->count()); ?> sản
                                            phẩm</span>
                                        <div class="small text-muted mt-1">
                                            <?php echo e($discount->products->pluck('name')->take(2)->implode(', ')); ?>

                                            <?php if($discount->products->count() > 2): ?>
                                                ...
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo e($discount->used_count); ?> /
                                    <?php echo e($discount->usage_limit ?? '∞'); ?>

                                </td>
                                <td>
                                    <?php
                                        $now = now();
                                        if ($discount->start_at && $now->lt($discount->start_at)) {
                                            $status = 'Chưa bắt đầu';
                                            $badge = 'secondary';
                                        } elseif ($discount->end_at && $now->gt($discount->end_at)) {
                                            $status = 'Hết hạn';
                                            $badge = 'danger';
                                        } else {
                                            $status = 'Đang áp dụng';
                                            $badge = 'success';
                                        }
                                    ?>
                                    <span class="badge bg-<?php echo e($badge); ?> admin-badge"><?php echo e($status); ?></span>
                                </td>
                                <td><?php echo e($discount->created_at?->format('d/m/Y H:i') ?? '-'); ?></td>
                                <td>
                                    <a href="<?php echo e(route('admin.discounts.edit', $discount->id)); ?>"
                                        class="btn btn-sm btn-outline-primary admin-action-btn mb-1">Sửa</a>

                                    <!-- Nút Chi tiết -->
                                    <button type="button" class="btn btn-sm btn-outline-info admin-action-btn mb-1"
                                        data-bs-toggle="modal" data-bs-target="#discountDetailModal<?php echo e($discount->id); ?>">
                                        Chi tiết
                                    </button>

                                    <form action="<?php echo e(route('admin.discounts.destroy', $discount->id)); ?>" method="POST"
                                        style="display:inline;">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button class="btn btn-sm btn-outline-danger admin-action-btn mb-1"
                                            onclick="return confirm('Xác nhận xóa?')">Xóa</button>
                                    </form>

                                    <!-- Modal Chi tiết -->
                                    <div class="modal fade" id="discountDetailModal<?php echo e($discount->id); ?>" tabindex="-1"
                                        aria-labelledby="discountDetailModalLabel<?php echo e($discount->id); ?>" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="discountDetailModalLabel<?php echo e($discount->id); ?>">
                                                        Chi tiết mã giảm giá
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Đóng"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p><strong>Mã giảm giá:</strong> <?php echo e($discount->code); ?></p>
                                                    <p><strong>Loại:</strong>
                                                        <?php if($discount->type == 'percent'): ?>
                                                            Phần trăm (%)
                                                        <?php else: ?>
                                                            Tiền cố định
                                                        <?php endif; ?>
                                                    </p>

                                                    <p><strong>Giá trị:</strong>
                                                        <?php echo e($discount->value_label); ?>

                                                    </p>
                                                    <p><strong>Đối tượng áp dụng:</strong> <?php echo e($discount->audience_label); ?></p>
                                                    <p><strong>Số lần sử dụng tối đa:</strong>
                                                        <?php echo e($discount->usage_limit ?? '∞'); ?></p>
                                                    <p><strong>Số lần đã sử dụng:</strong> <?php echo e($discount->used_count); ?></p>
                                                    <p><strong>Đơn tối thiểu áp dụng:</strong>
                                                        <?php echo e(number_format($discount->min_order_value, 0, ',', '.')); ?> đ</p>
                                                    <p><strong>Phạm vi:</strong>
                                                        <?php if($discount->products->isEmpty()): ?>
                                                            Toàn shop
                                                        <?php else: ?>
                                                            Theo <?php echo e($discount->products->count()); ?> sản phẩm
                                                        <?php endif; ?>
                                                    </p>
                                                    <?php if($discount->products->isNotEmpty()): ?>
                                                        <p><strong>Danh sách sản phẩm:</strong></p>
                                                        <ul class="mb-2">
                                                            <?php $__currentLoopData = $discount->products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                <li><?php echo e($product->name); ?></li>
                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                        </ul>
                                                    <?php endif; ?>
                                                    <p><strong>Ngày bắt đầu:</strong>
                                                        <?php echo e($discount->start_at?->format('d/m/Y H:i') ?? '-'); ?></p>
                                                    <p><strong>Ngày kết thúc:</strong>
                                                        <?php echo e($discount->end_at?->format('d/m/Y H:i') ?? '-'); ?></p>
                                                    <p><strong>Ngày tạo:</strong>
                                                        <?php echo e($discount->created_at?->format('d/m/Y H:i') ?? '-'); ?></p>
                                                    <p><strong>Ngày cập nhật:</strong>
                                                        <?php echo e($discount->updated_at?->format('d/m/Y H:i') ?? '-'); ?></p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">Đóng</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="10" class="text-center text-muted">Chưa có mã giảm giá</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                
                <?php echo e($discounts->appends(request()->query())->links()); ?>

            </div>

        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.layout_admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\luanvan\resources\views/admin/discounts/index.blade.php ENDPATH**/ ?>