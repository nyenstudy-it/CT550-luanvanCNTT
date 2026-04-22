

<?php $__env->startSection('content'); ?>
    <div class="container-fluid pt-4 px-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
            <div>
                <h5 class="mb-1">Báo Cáo Doanh Thu</h5>
                <small class="text-muted">Tháng <?php echo e($monthLabel); ?></small>
            </div>
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <form method="GET" action="<?php echo e(route('admin.revenue.stats')); ?>" class="d-flex gap-2 align-items-center">
                    <input name="month" type="month" class="form-control form-control-sm" value="<?php echo e($selectedMonth); ?>">
                    <button type="submit" class="btn btn-sm btn-primary">Xem</button>
                </form>
                <a href="<?php echo e(route('admin.revenue.export.excel', ['month' => $selectedMonth])); ?>"
                    class="btn btn-sm btn-outline-success">Excel</a>
                <a href="<?php echo e(route('admin.revenue.export.pdf', ['month' => $selectedMonth])); ?>"
                    class="btn btn-sm btn-outline-danger">PDF</a>
                <a href="<?php echo e(route('admin.dashboard', ['month' => $selectedMonth])); ?>"
                    class="btn btn-sm btn-outline-primary">Quay Lại</a>
            </div>
        </div>

        <!-- 4 KPI - Simple Bootstrap Colors -->
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded overflow-hidden">
                    <div class="card-body p-4 light-primary text-dark">
                        <small class="d-block mb-1">Tổng Tiền Bán Ra</small>
                        <h5 class="mb-0"><?php echo e(number_format($revenueSummary['gross_sale'], 0, ',', '.')); ?> ₫</h5>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded overflow-hidden">
                    <div class="card-body p-4 light-danger text-dark">
                        <small class="d-block mb-1">Tiền Đã Hoàn Khách</small>
                        <h5 class="mb-0"><?php echo e(number_format($revenueSummary['refund_amount'], 0, ',', '.')); ?> ₫</h5>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded overflow-hidden">
                    <div class="card-body p-4 light-info text-dark">
                        <small class="d-block mb-1">Tiền Thực Nhận</small>
                        <h5 class="mb-0"><?php echo e(number_format($revenueSummary['net_revenue'], 0, ',', '.')); ?> ₫</h5>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded overflow-hidden">
                    <div class="card-body p-4 text-dark"
                        style="background: <?php echo e($revenueSummary['estimated_profit'] >= 0 ? '#e8f5e9' : '#ffe7e7'); ?>;">
                        <small class="d-block mb-1">Lãi Ước Tính</small>
                        <h5 class="mb-0"><?php echo e(number_format($revenueSummary['estimated_profit'], 0, ',', '.')); ?> ₫</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart: Daily Revenue -->
    <div class="container-fluid pt-2 px-4 pb-4">
        <div class="row g-3">
            <div class="col-lg-12">
                <div class=\"card border-0 shadow-sm\">
                    <div class=\"card-header light-primary text-dark\">
                        <h6 class="\" mb-0\">Tiền Thực Nhận Theo Ngày Trong Tháng</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="revenueMonthlyChart" style="max-height: 320px;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts: Refund Ratio & Payment Methods -->
    <div class="container-fluid pt-2 px-4 pb-4">
        <div class="row g-3">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-danger text-white">
                        <h6 class="mb-0">Tỷ Lệ Hoàn Tiền Theo Phương Thức Thanh Toán</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 h-100">
                            <div class="col-lg-6 d-flex align-items-center justify-content-center">
                                <div style="height: 280px; width: 100%;">
                                    <canvas id="revenueWeeklyChart"></canvas>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <?php $__empty_1 = true; $__currentLoopData = $refundByMethod; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <div class="d-flex justify-content-between border-bottom py-2 small mb-2">
                                        <span class="fw-bold"><?php echo e($item->method_label); ?></span>
                                        <span>
                                            <span
                                                class="badge bg-info"><?php echo e(number_format((int) max(0, $item->refund_count ?? 0), 0)); ?>/<?php echo e(number_format((int) max(0, $item->paid_count ?? 0), 0)); ?></span>
                                            <span
                                                class="badge <?php echo e($item->refund_rate > 5 ? 'bg-danger' : 'bg-success'); ?>"><?php echo e(number_format($item->refund_rate, 2)); ?>%</span>
                                        </span>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <p class="mb-0 text-muted text-center">Không có dữ liệu hoàn tiền</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">Số Đơn Theo Phương Thức Thanh Toán</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="revenuePaymentChart" style="max-height: 310px;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cost Breakdown Section -->
    <div class="container-fluid pt-2 px-4 pb-4">
        <div class="row g-3">
            <div class="col-lg-6">
                <?php
                    $totalMonthlyCost =
                        (float) $revenueSummary['import_cost'] +
                        (float) $revenueSummary['salary_cost'] +
                        (float) $revenueSummary['shipping_cost'] +
                        (float) $revenueSummary['cogs'] +
                        (float) ($revenueSummary['total_writeoff_cost'] ?? 0);
                    $shipRate = (float) ($revenueSummary['ship_to_revenue_rate'] ?? 0);
                    $costItems = [
                        ['label' => 'Nhập hàng', 'value' => (float) $revenueSummary['import_cost'], 'color' => '#0d6efd'],
                        ['label' => 'Lương nhân sự', 'value' => (float) $revenueSummary['salary_cost'], 'color' => '#6f42c1'],
                        ['label' => 'Phí giao hàng', 'value' => (float) $revenueSummary['shipping_cost'], 'color' => '#fd7e14'],
                        ['label' => 'Giá vốn đã bán', 'value' => (float) $revenueSummary['cogs'], 'color' => '#dc3545'],
                        ['label' => 'Lỗ hàng hết hạn/hư hỏng', 'value' => (float) ($revenueSummary['total_writeoff_cost'] ?? 0), 'color' => '#6c757d'],
                    ];
                ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">Chi Tiết Chi Phí Kinh Doanh</h6>
                    </div>
                    <div class="card-body">
                        <div class="bg-primary text-white rounded p-3 mb-3">
                            <small class="d-block mb-1">Tổng Chi Phí Tháng <?php echo e($monthLabel); ?></small>
                            <h4 class="mb-0"><?php echo e(number_format($totalMonthlyCost, 0, ',', '.')); ?> ₫</h4>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="border rounded p-2 text-center">
                                    <small class="text-muted d-block mb-1">Nhập Hàng</small>
                                    <strong
                                        class="text-primary"><?php echo e(number_format($revenueSummary['import_cost'], 0, ',', '.')); ?>

                                        ₫</strong>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-2 text-center">
                                    <small class="text-muted d-block mb-1">Lương NV</small>
                                    <strong
                                        class="text-primary"><?php echo e(number_format($revenueSummary['salary_cost'], 0, ',', '.')); ?>

                                        ₫</strong>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-2 text-center">
                                    <small class="text-muted d-block mb-1">Phí Giao</small>
                                    <strong
                                        class="text-primary"><?php echo e(number_format($revenueSummary['shipping_cost'], 0, ',', '.')); ?>

                                        ₫</strong>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-2 text-center">
                                    <small class="text-muted d-block mb-1">Giá Vốn</small>
                                    <strong class="text-primary"><?php echo e(number_format($revenueSummary['cogs'], 0, ',', '.')); ?>

                                        ₫</strong>
                                </div>
                            </div>
                            <?php if(($revenueSummary['total_writeoff_cost'] ?? 0) > 0): ?>
                                <div class="col-12">
                                    <div class="border border-2 border-danger rounded p-2 bg-white text-center">
                                        <small class="text-muted d-block mb-1">Hàng Hết Hạn & Hư Hỏng</small>
                                        <strong
                                            class="text-danger"><?php echo e(number_format($revenueSummary['total_writeoff_cost'], 0, ',', '.')); ?>

                                            ₫</strong>
                                        <div class="mt-2">
                                            <span class="badge bg-warning text-dark me-2">Hết hạn:
                                                <?php echo e(number_format($revenueSummary['writeoff_cost'], 0, ',', '.')); ?> ₫</span>
                                            <span class="badge bg-danger">Hư:
                                                <?php echo e(number_format($revenueSummary['damaged_loss'], 0, ',', '.')); ?> ₫</span>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <p class="small mb-2"><strong>Tỷ Lệ Chi Phí:</strong></p>
                        <div class="row g-2 align-items-center">
                            <div class="col-lg-5">
                                <canvas id="monthlyCostBreakdownChart" style="max-height: 220px;"></canvas>
                            </div>
                            <div class="col-lg-7">
                                <?php $__currentLoopData = $costItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $ratio = $totalMonthlyCost > 0 ? round(($item['value'] / $totalMonthlyCost) * 100, 2) : 0;
                                    ?>
                                    <div class="mb-2">
                                        <div class="d-flex justify-content-between small mb-1">
                                            <span><?php echo e($item['label']); ?></span>
                                            <strong><?php echo e(number_format($ratio, 2)); ?>%</strong>
                                        </div>
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar" role="progressbar"
                                                style="width: <?php echo e($ratio); ?>%; background-color: <?php echo e($item['color']); ?>;"></div>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>

                        <div class="mt-3 p-2 rounded" style="background: #f0f8ff;">
                            <small class="text-muted">Phí giao/Doanh thu:</small>
                            <span
                                class="badge <?php echo e($shipRate >= 8 ? 'bg-danger' : ($shipRate >= 4 ? 'bg-warning text-dark' : 'bg-success')); ?>">
                                <?php echo e(number_format($shipRate, 2)); ?>%
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-danger text-white">
                        <h6 class="mb-0">Phân Tích Hủy & Hoàn Tiền</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-lg-6">
                                <p class="small fw-bold mb-2">Lý Do Hủy Đơn</p>
                                <?php if($cancelByReason->isNotEmpty()): ?>
                                    <canvas id="cancelReasonChart" style="max-height: 200px;"></canvas>
                                    <div class="mt-2">
                                        <?php $__currentLoopData = $cancelByReason; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div class="d-flex justify-content-between border-bottom py-1 small">
                                                <span><?php echo e($item->reason_label); ?></span>
                                                <span class="badge bg-warning text-dark"><?php echo e(number_format($item->rate, 2)); ?>%</span>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                <?php else: ?>
                                    <p class="mb-0 text-muted text-center py-3">Không có đơn hủy</p>
                                <?php endif; ?>
                            </div>
                            <div class="col-lg-6">
                                <p class="small fw-bold mb-2">Lý Do Hoàn Tiền</p>
                                <?php if($refundByReason->isNotEmpty()): ?>
                                    <canvas id="refundReasonChart" style="max-height: 200px;"></canvas>
                                    <div class="mt-2">
                                        <?php $__currentLoopData = $refundByReason; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div class="d-flex justify-content-between border-bottom py-1 small">
                                                <span><?php echo e($item->reason_label); ?></span>
                                                <span
                                                    class="badge bg-danger"><?php echo e(number_format((int) max(0, $item->total ?? 0), 0)); ?>

                                                    lượt</span>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                <?php else: ?>
                                    <p class="mb-0 text-muted text-center py-3">Không có hoàn tiền</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid pt-4 px-4 pb-4">
        <div class="row g-4">
            <div class="col-lg-12">
                <div class="row g-4">
                    <div class="col-lg-6">
                        <div class="bg-light rounded p-4 h-100">
                            <h6 class="mb-3">Báo cáo nhập hàng theo nhà cung cấp</h6>
                            <div class="table-responsive">
                                <table class="table text-start align-middle table-bordered table-hover mb-0">
                                    <thead>
                                        <tr class="text-dark">
                                            <th>Nhà cung cấp</th>
                                            <th>Số lần nhập</th>
                                            <th>Giá trị nhập</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__empty_1 = true; $__currentLoopData = $importsBySupplier; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                            <tr>
                                                <td><?php echo e($item->supplier_name); ?></td>
                                                <td><?php echo e(number_format((int) max(0, $item->import_count ?? 0), 0)); ?></td>
                                                <td><?php echo e(number_format((int) max(0, $item->total_value ?? 0), 0, ',', '.')); ?> ₫
                                                </td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                            <tr>
                                                <td colspan="3">Tháng này chưa có dữ liệu nhập theo nhà cung cấp</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="bg-light rounded p-4 h-100">
                            <h6 class="mb-3">Báo cáo nhập hàng theo nhóm sản phẩm</h6>
                            <div class="table-responsive">
                                <table class="table text-start align-middle table-bordered table-hover mb-0">
                                    <thead>
                                        <tr class="text-dark">
                                            <th>Nhóm sản phẩm</th>
                                            <th>Số lượng nhập</th>
                                            <th>Giá trị nhập</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__empty_1 = true; $__currentLoopData = $importsByCategory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                            <tr>
                                                <td><?php echo e($item->category_name); ?></td>
                                                <td><?php echo e(number_format((int) max(0, $item->total_qty ?? 0), 0)); ?></td>
                                                <td><?php echo e(number_format((int) max(0, $item->total_value ?? 0), 0, ',', '.')); ?> ₫
                                                </td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                            <tr>
                                                <td colspan="3">Tháng này chưa có dữ liệu nhập theo nhóm</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <?php if(isset($writeoffDetails) && $writeoffDetails->isNotEmpty()): ?>
        <div class="container-fluid pt-4 px-4">
            <div class="row g-4">
                <div class="col-lg-12">
                    <div class="bg-light rounded p-4 border border-danger">
                        <h6 class="mb-3 text-danger">

                            Chi tiết lỗ hàng hết hạn &amp; hư hỏng tháng <?php echo e($monthLabel); ?>

                        </h6>
                        <div class="table-responsive">
                            <table class="table text-start align-middle table-bordered table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Sản phẩm</th>
                                        <th>SKU</th>
                                        <th>Lý do</th>
                                        <th>Số lượng xuất kho</th>
                                        <th>Thiệt hại (tiền nhập)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $writeoffDetails; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td><?php echo e($row->product_name); ?></td>
                                            <td><?php echo e($row->sku); ?></td>
                                            <td>
                                                <span
                                                    class="badge <?php echo e($row->reason === 'expired' ? 'bg-danger' : 'bg-warning text-dark'); ?>">
                                                    <?php echo e($row->reason_label); ?>

                                                </span>
                                            </td>
                                            <td><?php echo e(number_format((int) max(0, $row->total_qty ?? 0), 0)); ?></td>
                                            <td class="text-danger fw-bold">
                                                <?php echo e(number_format((int) max(0, $row->total_cost ?? 0), 0, ',', '.')); ?> ₫
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="4" class="text-end fw-bold">Tổng thiệt hại:</td>
                                        <td class="text-danger fw-bold">
                                            <?php echo e(number_format($writeoffDetails->sum('total_cost'), 0, ',', '.')); ?> ₫
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="container-fluid pt-4 px-4 pb-4">
        <div class="row g-4">
            <div class="col-lg-12">
                <div class="bg-light rounded p-4 h-100">
                    <h6 class="mb-3">Chi phí nhân sự theo bộ phận (tháng <?php echo e($monthLabel); ?>)</h6>
                    <div class="table-responsive">
                        <table class="table text-start align-middle table-bordered table-hover mb-0">
                            <thead>
                                <tr class="text-dark">
                                    <th>Bộ phận</th>
                                    <th>Số nhân sự</th>
                                    <th>Chi phí lương</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $salaryByDepartment; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($item->department_label); ?></td>
                                        <td><?php echo e(number_format((int) max(0, $item->staff_count ?? 0), 0)); ?></td>
                                        <td><?php echo e(number_format((int) max(0, $item->final_salary ?? 0), 0, ',', '.')); ?> ₫</td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="3">Tháng này chưa có dữ liệu lương theo bộ phận</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        (function () {
            const monthlyLabels = <?php echo json_encode($monthlyFinance['labels'], 15, 512) ?>;
            const monthlyNetRevenue = <?php echo json_encode($monthlyFinance['values'], 15, 512) ?>;

            const refundMethodLabels = <?php echo json_encode($refundByMethod->pluck('method_label'), 15, 512) ?>;
            const refundMethodRates = <?php echo json_encode($refundByMethod->pluck('refund_rate'), 15, 512) ?>;

            const paymentLabels = <?php echo json_encode($paymentMethod['labels'], 15, 512) ?>;
            const paymentCounts = <?php echo json_encode($paymentMethod['counts'], 15, 512) ?>;
            const cancelReasonLabels = <?php echo json_encode($cancelByReason->pluck('reason_label'), 15, 512) ?>;
            const cancelReasonData = <?php echo json_encode($cancelByReason->pluck('total'), 15, 512) ?>;
            const refundReasonLabels = <?php echo json_encode($refundByReason->pluck('reason_label'), 15, 512) ?>;
            const refundReasonData = <?php echo json_encode($refundByReason->pluck('total'), 15, 512) ?>;

            const costLabels = ['Nhập hàng', 'Lương nhân sự', 'Phí giao hàng', 'Giá vốn đã bán'];
            const costData = [
                <?php echo json_encode((float) $revenueSummary['import_cost'], 15, 512) ?>,
                <?php echo json_encode((float) $revenueSummary['salary_cost'], 15, 512) ?>,
                <?php echo json_encode((float) $revenueSummary['shipping_cost'], 15, 512) ?>,
                <?php echo json_encode((float) $revenueSummary['cogs'], 15, 512) ?>
            ];

            const moneyFormat = (value) => {
                return new Intl.NumberFormat('vi-VN').format(Math.round(value || 0)) + ' ₫';
            };

            const monthlyEl = document.getElementById('revenueMonthlyChart');
            if (monthlyEl && monthlyLabels.length) {
                new Chart(monthlyEl, {
                    type: 'line',
                    data: {
                        labels: monthlyLabels,
                        datasets: [{
                            label: 'Tiền thực nhận theo ngày',
                            data: monthlyNetRevenue,
                            borderColor: '#0d6efd',
                            backgroundColor: 'rgba(13, 110, 253, 0.14)',
                            fill: true,
                            tension: 0.35
                        }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                ticks: {
                                    callback: (value) => moneyFormat(value)
                                }
                            }
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: (context) => `${context.dataset.label}: ${moneyFormat(context.raw)}`
                                }
                            }
                        }
                    }
                });
            }

            const weeklyEl = document.getElementById('revenueWeeklyChart');
            if (weeklyEl && refundMethodLabels.length) {
                new Chart(weeklyEl, {
                    type: 'bar',
                    data: {
                        labels: refundMethodLabels,
                        datasets: [{
                            label: 'Tỷ lệ hoàn (%)',
                            data: refundMethodRates,
                            backgroundColor: 'rgba(220, 53, 69, 0.75)'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                ticks: {
                                    callback: (value) => `${value}%`
                                }
                            }
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: (context) => `${context.dataset.label}: ${context.raw}%`
                                }
                            }
                        }
                    }
                });
            }

            const paymentEl = document.getElementById('revenuePaymentChart');
            if (paymentEl && paymentLabels.length) {
                new Chart(paymentEl, {
                    type: 'pie',
                    data: {
                        labels: paymentLabels,
                        datasets: [{
                            data: paymentCounts,
                            backgroundColor: ['#0d6efd', '#198754', '#ffc107', '#6f42c1', '#dc3545']
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
            }

            const monthlyCostEl = document.getElementById('monthlyCostBreakdownChart');
            if (monthlyCostEl) {
                new Chart(monthlyCostEl, {
                    type: 'doughnut',
                    data: {
                        labels: costLabels,
                        datasets: [{
                            data: costData,
                            backgroundColor: ['#0d6efd', '#6f42c1', '#fd7e14', '#dc3545']
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: (context) => `${context.label}: ${moneyFormat(context.raw)}`
                                }
                            }
                        }
                    }
                });
            }

            const cancelReasonEl = document.getElementById('cancelReasonChart');
            if (cancelReasonEl && cancelReasonLabels.length) {
                new Chart(cancelReasonEl, {
                    type: 'doughnut',
                    data: {
                        labels: cancelReasonLabels,
                        datasets: [{
                            data: cancelReasonData,
                            backgroundColor: ['#dc3545', '#fd7e14', '#ffc107', '#20c997', '#0d6efd', '#6f42c1']
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: (context) => `${context.label}: ${context.raw} đơn`
                                }
                            }
                        }
                    }
                });
            }

            const refundReasonEl = document.getElementById('refundReasonChart');
            if (refundReasonEl && refundReasonLabels.length) {
                new Chart(refundReasonEl, {
                    type: 'doughnut',
                    data: {
                        labels: refundReasonLabels,
                        datasets: [{
                            data: refundReasonData,
                            backgroundColor: ['#0d6efd', '#20c997', '#ffc107', '#fd7e14', '#6f42c1', '#dc3545']
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: (context) => `${context.label}: ${context.raw} lượt`
                                }
                            }
                        }
                    }
                });
            }

        })();
    </script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('admin.layouts.layout_admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\luanvan\resources\views/admin/revenue_statistics.blade.php ENDPATH**/ ?>