

<?php $__env->startSection('content'); ?>
    <div class="container-fluid pt-4 px-4">

        <div class="bg-light rounded p-4">

            <div class="row g-3 mb-4">
                <div class="col-12 col-sm-4">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Tổng phiếu nhập</small>
                        <h4 class="mb-0"><?php echo e($imports->total()); ?></h4>
                    </div>
                </div>
                <div class="col-12 col-sm-4">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Đang hiển thị</small>
                        <h4 class="mb-0 text-primary"><?php echo e($imports->count()); ?></h4>
                    </div>
                </div>
                <div class="col-12 col-sm-4">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Trang hiện tại</small>
                        <h4 class="mb-0 text-success"><?php echo e($imports->currentPage()); ?>/<?php echo e($imports->lastPage()); ?></h4>
                    </div>
                </div>
            </div>

            <form method="GET" class="row g-3 mb-3">

                <div class="col-md-3">
                    <label>Nhà phân phối</label>
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
                    <label>Từ ngày</label>
                    <input type="date" name="date_from" class="form-control" value="<?php echo e(request('date_from')); ?>">
                </div>

                <div class="col-md-2">
                    <label>Đến ngày</label>
                    <input type="date" name="date_to" class="form-control" value="<?php echo e(request('date_to')); ?>">
                </div>

                <div class="col-md-2">
                    <label>Tổng tiền từ</label>
                    <input type="number" name="min_total" class="form-control" value="<?php echo e(request('min_total')); ?>">
                </div>

                <div class="col-md-2">
                    <label>Đến</label>
                    <input type="number" name="max_total" class="form-control" value="<?php echo e(request('max_total')); ?>">
                </div>

                <div class="col-md-1 d-flex align-items-end">
                    <button class="btn btn-primary w-100">Lọc</button>
                </div>

            </form>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h6 class="mb-1">Danh sách phiếu nhập kho</h6>
                    <small class="text-muted">Ưu tiên đủ thông tin để tìm phiếu, đối chiếu người nhập và mở lại phiếu khi
                        cần in/xem nhanh.</small>
                </div>
                <a href="<?php echo e(route('admin.imports.create')); ?>" class="btn btn-primary btn-sm">
                    + Nhập kho
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>STT</th>
                            <th>Mã phiếu</th>
                            <th>Nhà phân phối</th>
                            <th>Người nhập</th>
                            <th>Ngày nhập</th>
                            <th>Số dòng</th>
                            <th>Tổng SL nhập</th>
                            <th>Tổng tiền</th>
                            <th width="180">Thao tác</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php $__currentLoopData = $imports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $import): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><?php echo e($imports->firstItem() + $index); ?></td>
                                <td>
                                    <span class="fw-semibold">PN-<?php echo e(str_pad($import->id, 5, '0', STR_PAD_LEFT)); ?></span>
                                </td>
                                <td><?php echo e($import->supplier->name ?? '—'); ?></td>
                                <td><?php echo e($import->staff->name ?? '—'); ?></td>
                                <td><?php echo e(\Carbon\Carbon::parse($import->import_date)->format('d/m/Y')); ?></td>
                                <td><?php echo e(number_format($import->items_count ?? 0)); ?></td>
                                <td><?php echo e(number_format($import->total_quantity ?? 0)); ?></td>
                                <td><?php echo e(number_format($import->total_amount)); ?> đ</td>
                                <td>
                                    <div class="d-flex flex-wrap gap-2">
                                        <a href="<?php echo e(route('admin.imports.show', $import->id)); ?>" class="btn btn-sm btn-info">
                                            Xem
                                        </a>
                                        <a href="<?php echo e(route('admin.imports.print', $import->id)); ?>"
                                            class="btn btn-sm btn-outline-secondary">
                                            In phiếu
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                        <?php if($imports->isEmpty()): ?>
                            <tr>
                                <td colspan="9" class="text-center">
                                    Chưa có phiếu nhập nào
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <div class="mt-3">
                    <?php echo e($imports->links()); ?>

                </div>

            </div>

        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.layout_admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\luanvan\resources\views/admin/imports/list.blade.php ENDPATH**/ ?>