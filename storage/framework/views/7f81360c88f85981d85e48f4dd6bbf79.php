

<?php $__env->startSection('content'); ?>
    <div class="container-fluid pt-4 px-4">
        <div class="bg-light rounded p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <div>
                    <h5 class="mb-1">Danh sách tồn kho</h5>
                </div>
                <span class="badge bg-primary">Tổng biến thể: <?php echo e($summary['total_variants']); ?></span>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-12 col-sm-6 col-xl-2">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Hết hàng</small>
                        <h4 class="mb-0 text-danger"><?php echo e($summary['out_of_stock']); ?></h4>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-2">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Sắp hết hàng</small>
                        <h4 class="mb-0 text-warning"><?php echo e($summary['low_stock']); ?></h4>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-2">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Đã hết hạn</small>
                        <h4 class="mb-0 text-danger"><?php echo e($summary['expired']); ?></h4>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-2">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Sắp hết hạn</small>
                        <h4 class="mb-0 text-warning"><?php echo e($summary['expiring_soon']); ?></h4>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-2">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Tồn kho lâu</small>
                        <h4 class="mb-0 text-secondary"><?php echo e($summary['stale_stock']); ?></h4>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-2">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Ổn định</small>
                        <h4 class="mb-0 text-success"><?php echo e($summary['normal_stock']); ?></h4>
                    </div>
                </div>
            </div>

            <div class="border rounded bg-white p-3 mb-4">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                    <h6 class="mb-0">
                        <i class="fa fa-bell text-warning me-2"></i>
                        Cảnh báo đã đẩy lên chuông cho Admin/Staff
                    </h6>
                    <small class="text-muted">Tự động cập nhật theo dữ liệu tồn kho hiện tại</small>
                </div>
                <?php if($alertPreview->isNotEmpty()): ?>
                    <div class="row g-2">
                        <?php $__currentLoopData = $alertPreview; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="col-12 col-lg-6">
                                <div class="border rounded p-2 h-100">
                                    <div class="fw-semibold text-dark small">
                                        <?php echo e($alert->variant->product->name ?? 'Sản phẩm'); ?>

                                        <span class="text-muted">(<?php echo e($alert->variant->sku ?? 'N/A'); ?>)</span>
                                    </div>
                                    <div class="small text-muted mt-1">
                                        <?php if($alert->is_out_of_stock): ?>
                                            <span class="badge bg-danger me-1">Hết hàng</span>
                                        <?php endif; ?>
                                        <?php if($alert->is_low_stock): ?>
                                            <span class="badge bg-warning text-dark me-1">Sắp hết hàng</span>
                                        <?php endif; ?>
                                        <?php if($alert->is_expired): ?>
                                            <span class="badge bg-danger me-1">Đã hết hạn</span>
                                        <?php endif; ?>
                                        <?php if($alert->is_expiring_soon): ?>
                                            <span class="badge bg-warning text-dark me-1">Sắp hết hạn</span>
                                        <?php endif; ?>
                                        <?php if($alert->is_stale): ?>
                                            <span class="badge bg-secondary me-1">Tồn kho lâu</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php else: ?>
                    <div class="text-success small">Hiện chưa có cảnh báo tồn kho cần đẩy lên chuông.</div>
                <?php endif; ?>
            </div>

            <form method="GET" action="<?php echo e(route('admin.inventories.list')); ?>" class="border rounded bg-white p-3 mb-4">
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-lg-4">
                        <label class="form-label mb-1">Tìm kiếm</label>
                        <input type="text" name="keyword" class="form-control" value="<?php echo e(request('keyword')); ?>"
                            placeholder="Tên sản phẩm, SKU, màu, size...">
                    </div>
                    <div class="col-12 col-sm-6 col-lg-2">
                        <label class="form-label mb-1">Trạng thái tồn</label>
                        <select name="stock_status" class="form-select">
                            <option value="all" <?php echo e(request('stock_status', 'all') === 'all' ? 'selected' : ''); ?>>Tất cả
                            </option>
                            <option value="out" <?php echo e(request('stock_status') === 'out' ? 'selected' : ''); ?>>Hết hàng</option>
                            <option value="low" <?php echo e(request('stock_status') === 'low' ? 'selected' : ''); ?>>Sắp hết hàng
                            </option>
                            <option value="ok" <?php echo e(request('stock_status') === 'ok' ? 'selected' : ''); ?>>Ổn định</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-2">
                        <label class="form-label mb-1">Hạn sử dụng</label>
                        <select name="expiry_status" class="form-select">
                            <option value="all" <?php echo e(request('expiry_status', 'all') === 'all' ? 'selected' : ''); ?>>Tất cả
                            </option>
                            <option value="expired" <?php echo e(request('expiry_status') === 'expired' ? 'selected' : ''); ?>>Đã hết hạn
                            </option>
                            <option value="expiring" <?php echo e(request('expiry_status') === 'expiring' ? 'selected' : ''); ?>>Sắp hết
                                hạn</option>
                            <option value="promo" <?php echo e(request('expiry_status') === 'promo' ? 'selected' : ''); ?>>Gợi ý KM (<=
                                6 tháng)</option>
                            <option value="safe" <?php echo e(request('expiry_status') === 'safe' ? 'selected' : ''); ?>>An toàn</option>
                            <option value="no_expiry" <?php echo e(request('expiry_status') === 'no_expiry' ? 'selected' : ''); ?>>Không
                                có hạn</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-2">
                        <label class="form-label mb-1">Tồn kho lâu</label>
                        <select name="stale_status" class="form-select">
                            <option value="all" <?php echo e(request('stale_status', 'all') === 'all' ? 'selected' : ''); ?>>Tất cả
                            </option>
                            <option value="stale" <?php echo e(request('stale_status') === 'stale' ? 'selected' : ''); ?>>Tồn kho lâu
                            </option>
                            <option value="fresh" <?php echo e(request('stale_status') === 'fresh' ? 'selected' : ''); ?>>Bán nhanh
                            </option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-2">
                        <label class="form-label mb-1">Sắp xếp</label>
                        <select name="sort_by" class="form-select">
                            <option value="risk_desc" <?php echo e(request('sort_by', 'risk_desc') === 'risk_desc' ? 'selected' : ''); ?>>
                                Ưu tiên gợi ý KM 6 tháng</option>
                            <option value="quantity_asc" <?php echo e(request('sort_by') === 'quantity_asc' ? 'selected' : ''); ?>>Tồn kho
                                tăng dần</option>
                            <option value="quantity_desc" <?php echo e(request('sort_by') === 'quantity_desc' ? 'selected' : ''); ?>>Tồn
                                kho giảm dần</option>
                            <option value="expiry_asc" <?php echo e(request('sort_by') === 'expiry_asc' ? 'selected' : ''); ?>>Hạn gần
                                nhất</option>
                            <option value="stock_age_desc" <?php echo e(request('sort_by') === 'stock_age_desc' ? 'selected' : ''); ?>>Tồn
                                kho lâu nhất</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-4 col-lg-2">
                        <label class="form-label mb-1">Ngưỡng sắp hết</label>
                        <input type="number" min="1" name="low_stock_threshold" class="form-control"
                            value="<?php echo e(request('low_stock_threshold', $lowStockThreshold)); ?>">
                    </div>
                    <div class="col-12 col-md-4 col-lg-2">
                        <label class="form-label mb-1">Sắp hết hạn (ngày)</label>
                        <input type="number" min="1" name="expiring_days" class="form-control"
                            value="<?php echo e(request('expiring_days', $expiringInDays)); ?>">
                    </div>
                    <div class="col-12 col-lg-6 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-filter me-1"></i> Lọc dữ liệu
                        </button>
                        <a href="<?php echo e(route('admin.inventories.list')); ?>" class="btn btn-outline-secondary">
                            Đặt lại
                        </a>
                    </div>
                </div>
            </form>

            <style>
                .inventory-table {
                    min-width: 1120px;
                    margin-bottom: 0;
                }

                .inventory-table th,
                .inventory-table td {
                    font-size: 14px;
                    vertical-align: top;
                }

                .inventory-table thead th {
                    white-space: nowrap;
                    font-size: 12px;
                    text-transform: uppercase;
                    letter-spacing: 0.03em;
                }

                .inventory-product-name {
                    font-weight: 700;
                    color: #1f2937;
                    line-height: 1.45;
                }

                .inventory-subtext {
                    font-size: 12px;
                    color: #6b7280;
                }

                .inventory-variant-meta {
                    display: flex;
                    flex-wrap: wrap;
                    gap: 6px;
                    margin-top: 8px;
                }

                .inventory-chip {
                    display: inline-flex;
                    align-items: center;
                    border-radius: 999px;
                    padding: 4px 10px;
                    font-size: 12px;
                    font-weight: 600;
                    background: #f3f4f6;
                    color: #374151;
                }

                .inventory-stock-value {
                    font-size: 24px;
                    font-weight: 700;
                    line-height: 1;
                }

                .inventory-batch-bar {
                    display: flex;
                    height: 8px;
                    border-radius: 999px;
                    overflow: hidden;
                    background: #e5e7eb;
                    margin: 10px 0 8px;
                }

                .inventory-batch-segment.expired {
                    background: #dc3545;
                }

                .inventory-batch-segment.expiring {
                    background: #ffc107;
                }

                .inventory-batch-segment.promotion {
                    background: #0d6efd;
                }

                .inventory-batch-segment.safe {
                    background: #198754;
                }

                .inventory-batch-segment.no-expiry {
                    background: #6c757d;
                }

                .inventory-summary-card {
                    border: 1px solid #e5e7eb;
                    border-radius: 12px;
                    padding: 10px 12px;
                    background: #fff;
                }

                .inventory-summary-card.is-expired {
                    background: #fff5f5;
                    border-color: #f1b0b7;
                }

                .inventory-summary-card.is-expiring {
                    background: #fffaf0;
                    border-color: #f7d58a;
                }

                .inventory-summary-card.is-safe {
                    background: #f3fff7;
                    border-color: #badbcc;
                }

                .inventory-actions {
                    min-width: 150px;
                }
            </style>

            <div class="table-responsive border rounded bg-white">
                <table class="table table-bordered table-hover align-middle inventory-table">
                    <colgroup>
                        <col style="width: 5%">
                        <col style="width: 22%">
                        <col style="width: 18%">
                        <col style="width: 20%">
                        <col style="width: 17%">
                        <col style="width: 10%">
                        <col style="width: 8%">
                    </colgroup>
                    <thead class="table-light">
                        <tr>
                            <th>STT</th>
                            <th>Sản phẩm</th>
                            <th>Biến thể</th>
                            <th>Tồn kho</th>
                            <th>Tóm tắt hạn dùng</th>
                            <th>Cảnh báo</th>
                            <th>Xử lý</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php $__currentLoopData = $inventories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $inv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $variantMeta = collect([
                                    $inv->variant->color,
                                    $inv->variant->size ? 'Size ' . $inv->variant->size : null,
                                    $inv->variant->volume,
                                    $inv->variant->weight,
                                ])->filter()->values();

                                $totalBatchQuantity = max(1, (int) $inv->total_remaining_batch_quantity);
                                $breakdown = $inv->batch_status_breakdown ?? [];
                                $deadlineCardClass = $inv->is_expired
                                    ? 'is-expired'
                                    : ($inv->is_expiring_soon ? 'is-expiring' : ($inv->expiry_date ? 'is-safe' : ''));
                            ?>
                            <tr>
                                <td><?php echo e($inventories->firstItem() + $index); ?></td>

                                <td>
                                    <div class="inventory-product-name"><?php echo e($inv->variant->product->name ?? 'Sản phẩm không xác định'); ?></div>
                                    <div class="inventory-subtext mt-1">SKU chính: <?php echo e($inv->variant->sku ?? 'N/A'); ?></div>
                                </td>

                                <td>
                                    <div class="fw-semibold"><?php echo e($inv->variant->sku ?? 'N/A'); ?></div>
                                    <?php if($variantMeta->isNotEmpty()): ?>
                                        <div class="inventory-variant-meta">
                                            <?php $__currentLoopData = $variantMeta; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $meta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <span class="inventory-chip"><?php echo e($meta); ?></span>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="inventory-subtext mt-2">Chưa có thuộc tính phân loại.</div>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <div class="inventory-stock-value <?php echo e($inv->is_out_of_stock || $inv->is_low_stock ? 'text-danger' : 'text-dark'); ?>">
                                        <?php echo e(number_format((int) max(0, $inv->quantity ?? 0), 0)); ?>

                                    </div>
                                    <div class="inventory-subtext mt-1">
                                        <?php echo e((int) ($inv->active_batch_count ?? 0)); ?> lô còn hàng | Theo lô: <?php echo e(number_format((int) max(0, $inv->total_remaining_batch_quantity ?? 0), 0)); ?>

                                    </div>

                                    <div class="inventory-batch-bar" title="Phân bổ tồn theo trạng thái hạn dùng">
                                        <?php
                                            $safeTotal = max(1, (int) max(0, ($breakdown['expired'] ?? 0) + ($breakdown['expiring'] ?? 0) + ($breakdown['promotion'] ?? 0) + ($breakdown['safe'] ?? 0) + ($breakdown['no_expiry'] ?? 0)));
                                        ?>
                                        <?php if(((int) ($breakdown['expired'] ?? 0)) > 0): ?>
                                            <span class="inventory-batch-segment expired" style="width: <?php echo e(((int) ($breakdown['expired'] ?? 0) / $safeTotal) * 100); ?>%"></span>
                                        <?php endif; ?>
                                        <?php if(((int) ($breakdown['expiring'] ?? 0)) > 0): ?>
                                            <span class="inventory-batch-segment expiring" style="width: <?php echo e(((int) ($breakdown['expiring'] ?? 0) / $safeTotal) * 100); ?>%"></span>
                                        <?php endif; ?>
                                        <?php if(((int) ($breakdown['promotion'] ?? 0)) > 0): ?>
                                            <span class="inventory-batch-segment promotion" style="width: <?php echo e(((int) ($breakdown['promotion'] ?? 0) / $safeTotal) * 100); ?>%"></span>
                                        <?php endif; ?>
                                        <?php if(((int) ($breakdown['safe'] ?? 0)) > 0): ?>
                                            <span class="inventory-batch-segment safe" style="width: <?php echo e(((int) ($breakdown['safe'] ?? 0) / $safeTotal) * 100); ?>%"></span>
                                        <?php endif; ?>
                                        <?php if(((int) ($breakdown['no_expiry'] ?? 0)) > 0): ?>
                                            <span class="inventory-batch-segment no-expiry" style="width: <?php echo e(((int) ($breakdown['no_expiry'] ?? 0) / $safeTotal) * 100); ?>%"></span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="inventory-subtext">
                                        <?php if(((int) ($breakdown['expired'] ?? 0)) > 0): ?>
                                            Hết hạn: <?php echo e(number_format((int) ($breakdown['expired'] ?? 0), 0)); ?>

                                        <?php endif; ?>
                                        <?php if(((int) ($breakdown['expiring'] ?? 0)) > 0): ?>
                                            <?php if(((int) ($breakdown['expired'] ?? 0)) > 0): ?> • <?php endif; ?>
                                            Sắp hết hạn: <?php echo e(number_format((int) ($breakdown['expiring'] ?? 0), 0)); ?>

                                        <?php endif; ?>
                                        <?php if(((int) ($breakdown['promotion'] ?? 0)) > 0): ?>
                                            <?php if((((int) ($breakdown['expired'] ?? 0)) + ((int) ($breakdown['expiring'] ?? 0))) > 0): ?> • <?php endif; ?>
                                            Nên đẩy bán: <?php echo e(number_format((int) ($breakdown['promotion'] ?? 0), 0)); ?>

                                        <?php endif; ?>
                                        <?php if(((int) ($breakdown['safe'] ?? 0)) > 0 && ((int) ($breakdown['expired'] ?? 0)) === 0 && ((int) ($breakdown['expiring'] ?? 0)) === 0 && ((int) ($breakdown['promotion'] ?? 0)) === 0): ?>
                                            An toàn: <?php echo e(number_format((int) ($breakdown['safe'] ?? 0), 0)); ?>

                                        <?php endif; ?>
                                        <?php if(((int) ($breakdown['no_expiry'] ?? 0)) > 0): ?>
                                            <?php if((((int) ($breakdown['expired'] ?? 0)) + ((int) ($breakdown['expiring'] ?? 0)) + ((int) ($breakdown['promotion'] ?? 0)) + ((int) ($breakdown['safe'] ?? 0))) > 0): ?> • <?php endif; ?>
                                            Không HSD: <?php echo e(number_format((int) ($breakdown['no_expiry'] ?? 0), 0)); ?>

                                        <?php endif; ?>
                                    </div>
                                </td>

                                <td>
                                    <div class="inventory-summary-card <?php echo e($deadlineCardClass); ?>">
                                        <?php if($inv->expiry_date): ?>
                                            <div class="fw-semibold"><?php echo e($inv->expiry_date->format('d/m/Y')); ?></div>
                                            <?php if($inv->is_expired): ?>
                                                <div class="inventory-subtext text-danger mt-1">Đã có lô quá hạn, cần xử lý ngay.</div>
                                            <?php elseif($inv->is_expiring_soon): ?>
                                                <div class="inventory-subtext text-warning mt-1">Có lô sắp hết hạn trong <?php echo e($inv->days_to_expire); ?> ngày.</div>
                                            <?php elseif($inv->is_promotion_candidate): ?>
                                                <div class="inventory-subtext text-primary mt-1">Có lô nằm trong cửa sổ khuyến mãi <?php echo e($promotionWindowDays); ?> ngày.</div>
                                            <?php else: ?>
                                                <div class="inventory-subtext text-success mt-1">Các lô có HSD vẫn đang an toàn.</div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <div class="fw-semibold">Không có hạn sử dụng</div>
                                            <div class="inventory-subtext mt-1">Biến thể này đang còn các lô không quản lý HSD.</div>
                                        <?php endif; ?>

                                        <div class="inventory-subtext mt-3">
                                            <?php if(!is_null($inv->stock_age_days)): ?>
                                                Lô còn hàng lâu nhất đã nằm kho <?php echo e($inv->stock_age_days); ?> ngày.
                                            <?php else: ?>
                                                Chưa xác định được tuổi lô tồn.
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <?php if($inv->is_out_of_stock): ?>
                                        <span class="badge bg-danger me-1 mb-1">Hết hàng</span>
                                    <?php endif; ?>
                                    <?php if($inv->is_low_stock): ?>
                                        <span class="badge bg-warning text-dark me-1 mb-1">Sắp hết hàng</span>
                                    <?php endif; ?>
                                    <?php if($inv->is_expired): ?>
                                        <span class="badge bg-danger me-1 mb-1">Có lô hết hạn</span>
                                    <?php endif; ?>
                                    <?php if($inv->is_expiring_soon): ?>
                                        <span class="badge bg-warning text-dark me-1 mb-1">Có lô sắp hết hạn</span>
                                    <?php endif; ?>
                                    <?php if($inv->is_promotion_candidate): ?>
                                        <span class="badge bg-primary me-1 mb-1">Nên đẩy bán</span>
                                    <?php endif; ?>
                                    <?php if($inv->is_stale): ?>
                                        <span class="badge bg-secondary me-1 mb-1">Tồn kho lâu</span>
                                    <?php endif; ?>
                                    <?php if(empty($inv->alert_tags)): ?>
                                        <span class="badge bg-success">Ổn định</span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <div class="inventory-actions d-flex flex-column gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-primary w-100 btn-show-batch-prices"
                                            data-url="<?php echo e(route('admin.inventories.batches', $inv->product_variant_id)); ?>"
                                            data-bs-toggle="modal" data-bs-target="#inventoryBatchModal">
                                            Xem lô chi tiết
                                        </button>

                                        <?php if($inv->is_expired): ?>
                                            <button type="button"
                                                class="btn btn-sm btn-danger w-100 btn-writeoff-expired"
                                                data-variant-id="<?php echo e($inv->product_variant_id); ?>"
                                                data-product-name="<?php echo e($inv->variant->product->name ?? 'Sản phẩm'); ?>"
                                                data-writeoff-url="<?php echo e(route('admin.inventories.writeoff', $inv->product_variant_id)); ?>"
                                                data-bs-toggle="modal" data-bs-target="#writeoffConfirmModal">
                                                Xuất kho hết hạn
                                            </button>
                                        <?php endif; ?>

                                        <button type="button"
                                            class="btn btn-sm btn-warning w-100 btn-writeoff-direct"
                                            data-variant-id="<?php echo e($inv->product_variant_id); ?>"
                                            data-product-name="<?php echo e($inv->variant->product->name ?? 'Sản phẩm'); ?>"
                                            data-current-qty="<?php echo e((int) $inv->quantity); ?>"
                                            data-writeoff-url="<?php echo e(route('admin.inventories.writeoff-direct', $inv->product_variant_id)); ?>"
                                            data-bs-toggle="modal" data-bs-target="#writeoffDirectModal">
                                            Hủy lỗi
                                        </button>

                                        <?php if($inv->is_expiring_soon || $inv->is_promotion_candidate): ?>
                                            <a href="<?php echo e(route('admin.discounts.create', ['product_id' => $inv->variant->product_id ?? ''])); ?>"
                                                class="btn btn-sm btn-warning w-100">
                                                Tạo mã KM
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                        <?php if($inventories->isEmpty()): ?>
                            <tr>
                                <td colspan="7" class="text-center">
                                    Không có dữ liệu phù hợp với bộ lọc hiện tại
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                <?php echo e($inventories->links()); ?>

            </div>

        </div>
    </div>

    <div class="modal fade" id="inventoryBatchModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Chi tiết lô nhập & giá</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="inventoryBatchModalBody">
                    <div class="text-center py-4 text-muted">Đang tải dữ liệu...</div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="modal fade" id="writeoffConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger">Xác nhận xuất kho hàng hết hạn</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Bạn đang chuẩn bị <strong>xuất toàn bộ lô hết hạn</strong> của sản phẩm:</p>
                    <p class="fw-bold fs-6" id="writeoffProductName"></p>
                    <div class="alert alert-warning mb-0">
                        <i class="fa fa-exclamation-triangle me-1"></i>
                        Hành động này sẽ:
                        <ul class="mb-0 mt-1">
                            <li>Đưa số lượng các lô hết hạn về 0</li>
                            <li>Trừ số lượng tương ứng khỏi tồn kho</li>
                            <li>Ghi nhận chi phí lỗ vào báo cáo doanh thu</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-danger" id="confirmWriteoffBtn">Xác nhận xuất kho</button>
                </div>
            </div>
        </div>
    </div>

    
    <div class="modal fade" id="writeoffDirectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-warning">Hủy sản phẩm lỗi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Hủy sản phẩm lỗi:</p>
                    <p class="fw-bold fs-6" id="writeoffDirectProductName"></p>

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Số lượng hủy</label>
                            <input type="number" id="writeoffDirectQuantity" class="form-control" 
                                min="1" placeholder="Nhập số lượng" required>
                            <small class="text-muted d-block mt-1" id="writeoffDirectQtyHint"></small>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Lý do hủy <span class="text-danger">*</span></label>
                            <select id="writeoffDirectReason" class="form-select" required>
                                <option value="">-- Chọn lý do --</option>
                                <option value="damaged">Hư hỏng / Lỗi chung</option>
                                <option value="broken_packaging">Bao bì bị phá</option>
                                <option value="water_damage">Ẩm / Mốc / Nước</option>
                                <option value="manufacturing_flaw">Lỗi sản xuất</option>
                                <option value="color_fading">Phai màu</option>
                                <option value="contaminated">Bị nhiễm bẩn</option>
                                <option value="stock_adjustment">Điều chỉnh kho</option>
                                <option value="other">Khác</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Ghi chú (tuỳ chọn)</label>
                            <textarea id="writeoffDirectNote" class="form-control" rows="2" placeholder="Mô tả chi tiết vấn đề..."></textarea>
                        </div>
                    </div>

                    <div class="alert alert-info mt-3 mb-0">
                        <i class="fa fa-info-circle me-1"></i>
                        Hành động này sẽ:
                        <ul class="mb-0 mt-2">
                            <li>Hủy số sản phẩm đã chỉ định khỏi tồn kho</li>
                            <li>Ghi nhận chi phí lỗ vào báo cáo doanh thu</li>
                            <li>Cập nhật lịch sử hủy hàng</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-warning" id="confirmWriteoffDirectBtn">Xác nhận hủy</button>
                </div>
            </div>
        </div>
    </div>

    
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
        <div id="writeoffToast" class="toast align-items-center border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="writeoffToastBody"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        document.addEventListener('click', function (event) {
            const button = event.target.closest('.btn-show-batch-prices');

            if (!button) {
                return;
            }

            const url = button.dataset.url;
            const modalBody = document.getElementById('inventoryBatchModalBody');

            modalBody.innerHTML = '<div class="text-center py-4 text-muted">Đang tải dữ liệu...</div>';

            fetch(url)
                .then((response) => {
                    if (!response.ok) {
                        throw new Error('Không thể tải dữ liệu lô nhập.');
                    }
                    return response.text();
                })
                .then((html) => {
                    modalBody.innerHTML = html;
                })
                .catch((error) => {
                    modalBody.innerHTML = '<div class="alert alert-danger mb-0">' + error.message + '</div>';
                });
        });

        // ----- Xuất kho hết hạn -----
        let pendingWriteoffUrl = null;
        let pendingWriteoffRow = null;

        document.addEventListener('click', function (event) {
            const btn = event.target.closest('.btn-writeoff-expired');
            if (!btn) return;

            pendingWriteoffUrl = btn.dataset.writeoffUrl;
            pendingWriteoffRow = btn.closest('tr');
            document.getElementById('writeoffProductName').textContent = btn.dataset.productName;
        });

        document.getElementById('confirmWriteoffBtn').addEventListener('click', function () {
            if (!pendingWriteoffUrl) return;

            const confirmBtn = this;
            confirmBtn.disabled = true;
            confirmBtn.textContent = 'Đang xử lý...';

            fetch(pendingWriteoffUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({}),
            })
            .then(res => res.json())
            .then(data => {
                const modal = bootstrap.Modal.getInstance(document.getElementById('writeoffConfirmModal'));
                if (modal) modal.hide();

                showWriteoffToast(data.success, data.message);

                if (data.success && pendingWriteoffRow) {
                    // Reload trang sau 1.5s để cập nhật số liệu
                    setTimeout(() => window.location.reload(), 1500);
                }
            })
            .catch(() => {
                showWriteoffToast(false, 'Đã xảy ra lỗi, vui lòng thử lại.');
            })
            .finally(() => {
                confirmBtn.disabled = false;
                confirmBtn.textContent = 'Xác nhận xuất kho';
                pendingWriteoffUrl = null;
                pendingWriteoffRow = null;
            });
        });

        function showWriteoffToast(success, message) {
            const toastEl = document.getElementById('writeoffToast');
            const toastBody = document.getElementById('writeoffToastBody');
            toastEl.className = 'toast align-items-center text-white border-0 ' + (success ? 'bg-success' : 'bg-danger');
            toastBody.textContent = message;
            const toast = new bootstrap.Toast(toastEl, { delay: 4000 });
            toast.show();
        }

        // ===== Hủy sản phẩm lỗi trực tiếp (ƯU TIÊN 1) =====
        let pendingWriteoffDirectUrl = null;

        document.addEventListener('click', function (event) {
            const btn = event.target.closest('.btn-writeoff-direct');
            if (!btn) return;

            pendingWriteoffDirectUrl = btn.dataset.writeoffUrl;
            const currentQty = parseInt(btn.dataset.currentQty) || 0;
            
            document.getElementById('writeoffDirectProductName').textContent = btn.dataset.productName;
            document.getElementById('writeoffDirectQuantity').value = '';
            document.getElementById('writeoffDirectQuantity').max = currentQty;
            document.getElementById('writeoffDirectReason').value = '';
            document.getElementById('writeoffDirectNote').value = '';
            document.getElementById('writeoffDirectQtyHint').textContent = `Tồn kho hiện tại: ${currentQty} sản phẩm`;
        });

        document.getElementById('confirmWriteoffDirectBtn').addEventListener('click', function () {
            if (!pendingWriteoffDirectUrl) return;

            const quantity = parseInt(document.getElementById('writeoffDirectQuantity').value) || 0;
            const reason = document.getElementById('writeoffDirectReason').value;
            const note = document.getElementById('writeoffDirectNote').value;

            if (quantity <= 0) {
                showWriteoffToast(false, 'Vui lòng nhập số lượng hợp lệ.');
                return;
            }

            if (!reason) {
                showWriteoffToast(false, 'Vui lòng chọn lý do hủy.');
                return;
            }

            const confirmBtn = this;
            confirmBtn.disabled = true;
            confirmBtn.textContent = 'Đang xử lý...';

            fetch(pendingWriteoffDirectUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    quantity: quantity,
                    reason: reason,
                    note: note || null,
                }),
            })
            .then(res => res.json())
            .then(data => {
                const modal = bootstrap.Modal.getInstance(document.getElementById('writeoffDirectModal'));
                if (modal) modal.hide();

                showWriteoffToast(data.success, data.message);

                if (data.success) {
                    // Reload trang sau 1.5s để cập nhật số liệu
                    setTimeout(() => window.location.reload(), 1500);
                }
            })
            .catch(() => {
                showWriteoffToast(false, 'Đã xảy ra lỗi, vui lòng thử lại.');
            })
            .finally(() => {
                confirmBtn.disabled = false;
                confirmBtn.textContent = 'Xác nhận hủy';
                pendingWriteoffDirectUrl = null;
            });
        });
    </script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('admin.layouts.layout_admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\luanvan\resources\views/admin/inventories/list.blade.php ENDPATH**/ ?>