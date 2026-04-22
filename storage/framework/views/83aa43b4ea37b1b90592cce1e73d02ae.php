<div class="mb-3">
    <h6 class="mb-1"><?php echo e($variant->product->name ?? 'Sản phẩm'); ?></h6>
    <div class="small text-muted">
        SKU: <?php echo e($variant->sku ?? 'N/A'); ?> |
        Giá bán hiện tại: <span class="fw-semibold text-dark"><?php echo e(number_format($sellingPrice)); ?> đ</span> |
        Tồn kho hiện tại: <span class="fw-semibold"><?php echo e(number_format((int) $inventory->quantity)); ?></span>
    </div>
</div>

<style>
    .batch-status-card {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 10px 12px;
        background: #fcfdff;
    }

    .batch-status-card.is-expired {
        border-color: #f1b0b7;
        background: #fff6f6;
    }

    .batch-status-card.is-expiring {
        border-color: #f7d58a;
        background: #fffaf2;
    }

    .batch-status-card.is-safe {
        border-color: #badbcc;
        background: #f5fff8;
    }

    .batch-status-card.is-no-expiry {
        border-color: #d6d8db;
        background: #f8f9fa;
    }
</style>

<div class="row g-2 mb-3">
    <?php $__currentLoopData = $batchItems->where('remaining_quantity', '>', 0)->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $batch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
            $expiry = $batch->expired_at ? \Carbon\Carbon::parse($batch->expired_at)->startOfDay() : null;
            $today = \Carbon\Carbon::today();
            $daysToExpire = $expiry ? $today->diffInDays($expiry, false) : null;
            $statusClass = !$expiry ? 'is-no-expiry' : ($daysToExpire < 0 ? 'is-expired' : ($daysToExpire <= 30 ? 'is-expiring' : 'is-safe'));
            $statusBadge = !$expiry ? ['bg-secondary', 'Không có HSD'] : ($daysToExpire < 0 ? ['bg-danger', 'Đã hết hạn'] : ($daysToExpire <= 30 ? ['bg-warning text-dark', 'Sắp hết hạn'] : ['bg-success', 'An toàn']));
        ?>
        <div class="col-12 col-lg-4">
            <div class="batch-status-card <?php echo e($statusClass); ?> h-100">
                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                    <div class="fw-semibold">PN-<?php echo e(str_pad($batch->import_id, 5, '0', STR_PAD_LEFT)); ?></div>
                    <span class="badge <?php echo e($statusBadge[0]); ?>"><?php echo e($statusBadge[1]); ?></span>
                </div>
                <div class="small text-muted">Còn lại: <span
                        class="fw-semibold text-dark"><?php echo e(number_format((int) $batch->remaining_quantity)); ?></span></div>
                <div class="small text-muted">NSX: <span
                        class="fw-semibold text-dark"><?php echo e($batch->manufacture_date ? \Carbon\Carbon::parse($batch->manufacture_date)->format('d/m/Y') : '—'); ?></span>
                </div>
                <div class="small text-muted">HSD: <span
                        class="fw-semibold text-dark"><?php echo e($expiry ? $expiry->format('d/m/Y') : 'Không có'); ?></span></div>
                <?php if(!is_null($daysToExpire)): ?>
                    <div
                        class="small mt-2 <?php echo e($daysToExpire < 0 ? 'text-danger' : ($daysToExpire <= 30 ? 'text-warning' : 'text-success')); ?>">
                        <?php echo e($daysToExpire < 0 ? 'Quá hạn ' . abs($daysToExpire) . ' ngày' : 'Còn ' . $daysToExpire . ' ngày đến hạn'); ?>

                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>Lần nhập</th>
                <th>Ngày nhập</th>
                <th>Nhà phân phối</th>
                <th>Người nhập</th>
                <th>NSX</th>
                <th>HSD</th>
                <th>Trạng thái lô</th>
                <th>SL nhập</th>
                <th>Còn lại</th>
                <th>Đã bán</th>
                <th>Giá nhập/lô</th>
                <th>Giá bán hiện tại</th>
                <th>Chênh lệch</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $batchItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $batch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $importQty = max(0, (int) ($batch->quantity ?? 0));
                    $remainingQty = max(0, (int) ($batch->remaining_quantity ?? 0));
                    $soldQty = max(0, min($importQty, $importQty - $remainingQty));
                    $importPrice = max(0, (float) ($batch->unit_price ?? 0));
                    $diff = $sellingPrice - $importPrice;
                    $expiry = $batch->expired_at ? \Carbon\Carbon::parse($batch->expired_at)->startOfDay() : null;
                    $manufactureDate = $batch->manufacture_date ? \Carbon\Carbon::parse($batch->manufacture_date)->format('d/m/Y') : '—';
                    $daysToExpire = $expiry ? \Carbon\Carbon::today()->diffInDays($expiry, false) : null;
                    $statusBadge = !$expiry
                        ? ['bg-secondary', 'Không có HSD']
                        : ($daysToExpire < 0
                            ? ['bg-danger', 'Đã hết hạn']
                            : ($daysToExpire <= 30 ? ['bg-warning text-dark', 'Sắp hết hạn'] : ['bg-success', 'An toàn']));
                    $importDate = $batch->import?->import_date
                        ? \Carbon\Carbon::parse($batch->import->import_date)->format('d/m/Y')
                        : ($batch->import?->created_at ? $batch->import->created_at->format('d/m/Y') : '—');
                ?>
                <tr>
                    <td>
                        <span class="fw-semibold">PN-<?php echo e(str_pad($batch->import_id, 5, '0', STR_PAD_LEFT)); ?></span>
                    </td>
                    <td>
                        <?php echo e($importDate); ?>

                    </td>
                    <td><?php echo e($batch->import?->supplier?->name ?? '—'); ?></td>
                    <td><?php echo e($batch->import?->staff?->name ?? '—'); ?></td>
                    <td><?php echo e($manufactureDate); ?></td>
                    <td><?php echo e($expiry ? $expiry->format('d/m/Y') : 'Không có'); ?></td>
                    <td>
                        <span class="badge <?php echo e($statusBadge[0]); ?>"><?php echo e($statusBadge[1]); ?></span>
                        <?php if(!is_null($daysToExpire)): ?>
                            <div
                                class="small mt-1 <?php echo e($daysToExpire < 0 ? 'text-danger' : ($daysToExpire <= 30 ? 'text-warning' : 'text-muted')); ?>">
                                <?php echo e($daysToExpire < 0 ? 'Quá hạn ' . abs($daysToExpire) . ' ngày' : 'Còn ' . $daysToExpire . ' ngày'); ?>

                            </div>
                        <?php endif; ?>
                    </td>
                    <td class="text-end"><?php echo e(number_format($importQty, 0)); ?></td>
                    <td class="text-end <?php echo e($remainingQty == 0 ? 'text-warning' : ''); ?>">
                        <?php echo e(number_format($remainingQty, 0)); ?></td>
                    <td class="text-end"><?php echo e(number_format($soldQty, 0)); ?></td>
                    <td class="text-end"><?php echo e(number_format($importPrice, 0)); ?> đ</td>
                    <td class="text-end"><?php echo e(number_format($sellingPrice, 0)); ?> đ</td>
                    <td class="text-end <?php echo e($diff >= 0 ? 'text-success' : 'text-danger'); ?> fw-semibold">
                        <?php echo e(number_format($diff)); ?> đ
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="13" class="text-center text-muted">Chưa có dữ liệu lô nhập cho biến thể này.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if($batchItems->isNotEmpty()): ?>
    <div class="small text-muted mt-2">
        Ghi chú: Chênh lệch = Giá bán hiện tại - Giá nhập từng lô (để tham khảo biên lợi nhuận gộp theo lô).
    </div>
<?php endif; ?><?php /**PATH C:\xampp\htdocs\luanvan\resources\views/admin/inventories/partials/batch_price_popup.blade.php ENDPATH**/ ?>