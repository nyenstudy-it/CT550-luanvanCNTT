

<?php $__env->startSection('navbar'); ?>
    <?php echo $__env->make('admin.layouts.navbar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php
        $authUser = Auth::user();
        $isAdmin = $authUser->role === 'admin';
        $isStaff = $authUser->role === 'staff';
        $position = $isStaff ? ($authUser->staff?->position ?? null) : null;
        $canViewDashboard = $isAdmin || ($isStaff && $position === 'cashier');

        $statusLabels = [
            'pending' => 'Chờ xử lý',
            'confirmed' => 'Đã xác nhận',
            'shipping' => 'Đang giao',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy',
            'refund_requested' => 'Yêu cầu hoàn hàng',
            'refunded' => 'Đã hoàn tiền',
            'unknown' => 'Không xác định',
        ];
    ?>

    <div class="container-fluid pt-4 px-4">
        <?php if(!$canViewDashboard): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <strong>Không có quyền xem!</strong> Bạn không có quyền truy cập trang Thống kê. Vui lòng liên hệ quản trị viên
                nếu cần hỗ trợ.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php else: ?>
            <!-- Header + Quick Actions -->
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                <div>
                    <h4 class="mb-1">Thống Kê</h4>
                    <small class="text-muted">Tháng <?php echo e($monthLabel ?? ''); ?></small>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <form method="GET" action="<?php echo e(route('admin.dashboard')); ?>" class="d-flex gap-2 align-items-center">
                        <input id="month" name="month" type="month" class="form-control form-control-sm"
                            value="<?php echo e($selectedMonth); ?>" style="width: 150px;">
                        <button type="submit" class="btn btn-sm btn-primary">Xem</button>
                    </form>
                    <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal"
                        data-bs-target="#statsDetailModal">
                        Chi Tiết
                    </button>
                    <?php if($isAdmin): ?>
                        <a href="<?php echo e(route('admin.revenue.stats', ['month' => $selectedMonth])); ?>"
                            class="btn btn-sm btn-outline-success">
                            Báo Cáo
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- 4 KPI CHÍNH - Bootstrap Colors -->
            <div class="row g-3 mb-4">
                <div class="col-sm-6 col-lg-3">
                    <div class="card border-0 shadow-sm overflow-hidden h-100">
                        <div class="card-body p-4 light-primary text-dark">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <small class="d-block mb-1">Doanh Thu Hôm Nay</small>
                                    <h5 class="mb-1"><?php echo e(number_format($todayRevenue, 0, ',', '.')); ?> ₫</h5>
                                    <small>
                                        <?php if(($todayRevenueTrend ?? 0) >= 0): ?>
                                            <span class="badge bg-success">+<?php echo e(($todayRevenueTrend ?? 0)); ?>%</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger"><?php echo e(($todayRevenueTrend ?? 0)); ?>%</span>
                                        <?php endif; ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-lg-3">
                    <div class="card border-0 shadow-sm overflow-hidden h-100">
                        <div class="card-body p-4 light-info text-dark">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <small class="d-block mb-1">Doanh Thu Tuần Này</small>
                                    <h5 class="mb-1"><?php echo e(number_format($weekRevenue, 0, ',', '.')); ?> ₫</h5>
                                    <small><?php echo e($weekDateRange ?? ''); ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-lg-3">
                    <div class="card border-0 shadow-sm overflow-hidden h-100">
                        <div class="card-body p-4 light-success text-dark">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <small class="d-block mb-1">Doanh Thu Tháng</small>
                                    <h5 class="mb-1"><?php echo e(number_format($monthRevenue, 0, ',', '.')); ?> ₫</h5>
                                    <small><?php echo e($monthDateRange ?? ''); ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-lg-3">
                    <div class="card border-0 shadow-sm overflow-hidden h-100">
                        <div class="card-body p-4 light-warning text-dark">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <small class="d-block mb-1">Khách Quay Lại</small>
                                    <h5 class="mb-1"><?php echo e(number_format($returningCustomerRate, 2)); ?>%</h5>
                                    <small><?php echo e($newCustomersThisMonth ?? 0); ?> khách mới</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- COMPACT STATS GRID (6 cột) - Bootstrap Colors -->
            <div class="row g-2 mb-4">
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="card-stat text-center p-3 rounded border-0 shadow-sm h-100 light-primary text-dark"
                        title="Đơn hàng mới">
                        <small class="d-block mb-1">Đơn Mới</small>
                        <h6 class="mb-0"><?php echo e($newOrdersThisMonth ?? 0); ?></h6>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="card-stat text-center p-3 rounded border-0 shadow-sm h-100 light-danger text-dark"
                        title="Khách hàng mới">
                        <small class="d-block mb-1">Khách Mới</small>
                        <h6 class="mb-0"><?php echo e($newCustomersThisMonth ?? 0); ?></h6>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="card-stat text-center p-3 rounded border-0 shadow-sm h-100 light-info text-dark"
                        title="Đánh giá">
                        <small class="d-block mb-1">Đánh Giá</small>
                        <h6 class="mb-0"><?php echo e($reviewsThisMonth ?? 0); ?></h6>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="card-stat text-center p-3 rounded border-0 shadow-sm h-100 light-success text-dark"
                        title="Yêu thích">
                        <small class="d-block mb-1">Lượt Yêu Thích</small>
                        <h6 class="mb-0"><?php echo e($wishlistInteractions ?? 0); ?></h6>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="card-stat text-center p-3 rounded border-0 shadow-sm h-100 light-warning text-dark"
                        title="Chi phí hủy">
                        <small class="d-block mb-1"> Chi Phí Hủy</small>
                        <h6 class="mb-0">
                            <?php echo e(number_format($writeoffMetrics['total_writeoff_cost_month'] ?? 0, 0, ',', '.')); ?> ₫
                        </h6>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="card-stat text-center p-3 rounded border-0 shadow-sm h-100 light-secondary text-dark"
                        title="Tài khoản hoạt động">
                        <small class="d-block mb-1">TK Hoạt Động</small>
                        <h6 class="mb-0"><?php echo e($accountStats['active_accounts'] ?? 0); ?></h6>
                    </div>
                </div>
            </div>

            <!-- THỐNG KÊ KINH DOANH TUẦN & THÁNG NÀY -->
            <div class="row g-3 mt-1">
                <!-- TUẦN NÀY -->
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header light-primary text-dark">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">THỐNG KÊ TUẦN NÀY</h6>
                                <small class="text-muted"><?php echo e($weekDateRange); ?></small>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-6">
                                    <div class="p-3 bg-light rounded">
                                        <small class="text-muted d-block mb-1">Doanh Thu</small>
                                        <h5 class="text-success mb-0"><?php echo e(number_format($thisWeekRevenue, 0, ',', '.')); ?> ₫</h5>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-3 bg-light rounded">
                                        <small class="text-muted d-block mb-1">Giờ Làm</small>
                                        <h5 class="text-info mb-0"><?php echo e($thisWeekHours); ?> h</h5>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-3 bg-light rounded">
                                        <small class="text-muted d-block mb-1">Chi Phí Nhân Sự</small>
                                        <h5 class="text-danger mb-0"><?php echo e(number_format($thisWeekSalary, 0, ',', '.')); ?> ₫</h5>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-3 bg-light rounded">
                                        <small class="text-muted d-block mb-1">Lợi Nhuận</small>
                                        <h5 class="mb-0" style="color: <?php if($thisWeekProfit >= 0): ?> #28a745 <?php else: ?> #dc3545 <?php endif; ?>">
                                            <?php echo e(number_format($thisWeekProfit, 0, ',', '.')); ?> ₫
                                        </h5>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary w-100 mt-3" data-bs-toggle="modal"
                                data-bs-target="#profitDetailModalWeek">
                                Xem Chi Tiết Chi Phí
                            </button>
                        </div>
                    </div>
                </div>
                <!-- THÁNG NÀY -->
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header light-success text-dark">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">THỐNG KÊ THÁNG NÀY</h6>
                                <small class="text-muted"><?php echo e($monthDateRange); ?></small>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-6">
                                    <div class="p-3 bg-light rounded">
                                        <small class="text-muted d-block mb-1">Doanh Thu</small>
                                        <h5 class="text-success mb-0"><?php echo e(number_format($thisMonthRevenue, 0, ',', '.')); ?> ₫</h5>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-3 bg-light rounded">
                                        <small class="text-muted d-block mb-1">Giờ Làm</small>
                                        <h5 class="text-info mb-0"><?php echo e($thisMonthHours); ?> h</h5>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-3 bg-light rounded">
                                        <small class="text-muted d-block mb-1">Chi Phí Nhân Sự</small>
                                        <h5 class="text-danger mb-0"><?php echo e(number_format($thisMonthSalary, 0, ',', '.')); ?> ₫</h5>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-3 bg-light rounded">
                                        <small class="text-muted d-block mb-1">Lợi Nhuận</small>
                                        <h5 class="mb-0" style="color: <?php if($thisMonthProfit >= 0): ?> #28a745 <?php else: ?> #dc3545 <?php endif; ?>">
                                            <?php echo e(number_format($thisMonthProfit, 0, ',', '.')); ?> ₫
                                        </h5>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-success w-100 mt-3" data-bs-toggle="modal"
                                data-bs-target="#profitDetailModalMonth">
                                Xem Chi Tiết Chi Phí
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BẢNG SO SÁNH 4 TUẦN GẦN NHẤT -->
            <div class="row g-4 mt-1">
                <div class="col-12">
                    <div class="bg-light rounded p-4">
                        <h6 class="mb-3">So Sánh 4 Tuần Gần Nhất</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered table-hover bg-white small">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tuần</th>
                                        <th class="text-end">Số Ca</th>
                                        <th class="text-end">Giờ Làm</th>
                                        <th class="text-end">Doanh Thu</th>
                                        <th class="text-end">Tiền Nhân Sự</th>
                                        <th class="text-end">Lợi Nhuận</th>
                                        <th class="text-end">Margin %</th>
                                        <th class="text-center">Chi Tiết</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $weeklyComparison; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $week): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr <?php if($index == 3): ?> class="table-info font-weight-bold" <?php endif; ?>>
                                            <td><?php echo e($week['week']); ?> <?php if($index == 3): ?> <span class="badge bg-info ms-1">Tuần
                                            này</span> <?php endif; ?></td>
                                            <td class="text-end"><?php echo e($week['shifts']); ?></td>
                                            <td class="text-end"><?php echo e($week['hours']); ?>h</td>
                                            <td class="text-end font-weight-bold text-primary">
                                                <?php echo e(number_format($week['revenue'], 0, ',', '.')); ?>đ
                                            </td>
                                            <td class="text-end text-danger"><?php echo e(number_format($week['salary'], 0, ',', '.')); ?>đ</td>
                                            <td class="text-end"
                                                style="color: <?php if($week['profit'] >= 0): ?> #28a745 <?php else: ?> #dc3545 <?php endif; ?>">
                                                <strong><?php echo e(number_format($week['profit'], 0, ',', '.')); ?>đ</strong>
                                            </td>
                                            <td class="text-end">
                                                <span
                                                    class="badge <?php if($week['profit_margin'] >= 0): ?> bg-success <?php else: ?> bg-danger <?php endif; ?>">
                                                    <?php echo e($week['profit_margin']); ?>%
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                                    data-bs-target="#profitDetailModal<?php echo e($index); ?>">
                                                    Xem
                                                </button>
                                            </td>
                                        </tr>
                                        <!-- Modal Chi Tiết Lợi Nhuận -->
                                        <div class="modal fade" id="profitDetailModal<?php echo e($index); ?>" tabindex="-1">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-light">
                                                        <h5 class="modal-title">Chi Tiết Lợi Nhuận - Tuần <?php echo e($week['week']); ?>

                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <!-- Revenue Breakdown -->
                                                        <div class="card border-0 bg-light mb-3">
                                                            <div class="card-body p-3">
                                                                <h6 class="mb-2 text-primary"><strong>Chi Tiết Lợi Nhuận Tuần
                                                                        <?php echo e($week['week']); ?></strong></h6>

                                                                <!-- Income Section -->
                                                                <div class="mb-3">
                                                                    <h6 class="text-success small mb-2">DOANH THU</h6>
                                                                    <ul class="list-group list-group-sm">
                                                                        <li
                                                                            class="list-group-item d-flex justify-content-between small">
                                                                            <span>Doanh Thu Bán</span>
                                                                            <strong
                                                                                class="text-success">+<?php echo e(number_format($week['revenue'], 0, ',', '.')); ?>đ</strong>
                                                                        </li>
                                                                    </ul>
                                                                </div>

                                                                <!-- Deductions Section -->
                                                                <div class="mb-3">
                                                                    <h6 class="text-danger small mb-2">KHOẢN TRỪNG</h6>
                                                                    <ul class="list-group list-group-sm">
                                                                        <li
                                                                            class="list-group-item d-flex justify-content-between small">
                                                                            <span>Giảm Giá</span>
                                                                            <strong
                                                                                class="text-danger">-<?php echo e(number_format($week['discounts'], 0, ',', '.')); ?>đ</strong>
                                                                        </li>
                                                                        <li
                                                                            class="list-group-item d-flex justify-content-between small">
                                                                            <span>Giá Vốn Hàng Bán (COGS)</span>
                                                                            <strong
                                                                                class="text-danger">-<?php echo e(number_format($week['cogs'], 0, ',', '.')); ?>đ</strong>
                                                                        </li>
                                                                        <li
                                                                            class="list-group-item d-flex justify-content-between small">
                                                                            <span>Chi Phí Vận Chuyển</span>
                                                                            <strong
                                                                                class="text-danger">-<?php echo e(number_format($week['shipping_cost'], 0, ',', '.')); ?>đ</strong>
                                                                        </li>
                                                                        <li
                                                                            class="list-group-item d-flex justify-content-between small">
                                                                            <span>Tiền Nhân Công</span>
                                                                            <strong
                                                                                class="text-danger">-<?php echo e(number_format($week['staff_cost'], 0, ',', '.')); ?>đ</strong>
                                                                        </li>
                                                                        <li
                                                                            class="list-group-item d-flex justify-content-between small">
                                                                            <span>Hao Hụt Hàng Tồn Kho</span>
                                                                            <strong
                                                                                class="text-danger">-<?php echo e(number_format($week['inventory_shrinkage'], 0, ',', '.')); ?>đ</strong>
                                                                        </li>
                                                                    </ul>
                                                                </div>

                                                                <!-- Result Section -->
                                                                <hr class="my-2">
                                                                <div class="text-center">
                                                                    <p class="mb-1 small text-muted"><strong>= LỢI NHUẬN
                                                                            THỰC</strong></p>
                                                                    <h4 class="mb-0"
                                                                        style="color: <?php if($week['profit'] >= 0): ?> #28a745 <?php else: ?> #dc3545 <?php endif; ?>">
                                                                        <strong><?php echo e(number_format($week['profit'], 0, ',', '.')); ?>đ</strong>
                                                                    </h4>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <h6 class="mb-2">Chi Tiết Phân Tích:</h6>
                                                        <ul class="list-group list-group-flush small">
                                                            <li class="list-group-item d-flex justify-content-between">
                                                                <span>Số ca làm việc:</span>
                                                                <strong><?php echo e($week['shifts']); ?> ca</strong>
                                                            </li>
                                                            <li class="list-group-item d-flex justify-content-between">
                                                                <span>Tổng giờ làm:</span>
                                                                <strong><?php echo e($week['hours']); ?> giờ</strong>
                                                            </li>
                                                            <li class="list-group-item d-flex justify-content-between">
                                                                <span>Doanh thu trung bình/ca:</span>
                                                                <strong><?php echo e($week['shifts'] > 0 ? number_format($week['revenue'] / $week['shifts'], 0, ',', '.') : 0); ?>đ</strong>
                                                            </li>
                                                            <li class="list-group-item d-flex justify-content-between">
                                                                <span>Chi phí nhân sự/ca:</span>
                                                                <strong><?php echo e($week['shifts'] > 0 ? number_format($week['staff_cost'] / $week['shifts'], 0, ',', '.') : 0); ?>đ</strong>
                                                            </li>
                                                            <li class="list-group-item d-flex justify-content-between">
                                                                <span>Lợi nhuận/ca:</span>
                                                                <strong><?php echo e($week['shifts'] > 0 ? number_format($week['profit'] / $week['shifts'], 0, ',', '.') : 0); ?>đ</strong>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">Đóng</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Chi Tiết Lợi Nhuận - Tuần Này -->
            <div class="modal fade" id="profitDetailModalWeek" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-light">
                            <h5 class="modal-title">Chi Tiết Lợi Nhuận - Tuần Này <small
                                    class="text-muted d-block mt-1"><?php echo e($weekDateRange); ?></small></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <!-- Revenue Breakdown -->
                            <div class="card border-0 bg-light mb-3">
                                <div class="card-body p-3">
                                    <h6 class="mb-2 text-primary"><strong>Chi Tiết Lợi Nhuận Tuần Này</strong></h6>

                                    <!-- Income Section -->
                                    <div class="mb-3">
                                        <h6 class="text-success small mb-2">DOANH THU</h6>
                                        <ul class="list-group list-group-sm">
                                            <li class="list-group-item d-flex justify-content-between small">
                                                <span>Doanh Thu Bán</span>
                                                <strong
                                                    class="text-success">+<?php echo e(number_format($thisWeekProfitDetails['revenue'], 0, ',', '.')); ?>đ</strong>
                                            </li>
                                        </ul>
                                    </div>

                                    <!-- Deductions Section -->
                                    <div class="mb-3">
                                        <h6 class="text-danger small mb-2">KHOẢN TRỪNG</h6>
                                        <ul class="list-group list-group-sm">
                                            <li class="list-group-item d-flex justify-content-between small">
                                                <span>Giảm Giá</span>
                                                <strong
                                                    class="text-danger">-<?php echo e(number_format($thisWeekProfitDetails['discounts'], 0, ',', '.')); ?>đ</strong>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between small">
                                                <span>Giá Vốn Hàng Bán (COGS)</span>
                                                <strong
                                                    class="text-danger">-<?php echo e(number_format($thisWeekProfitDetails['cogs'], 0, ',', '.')); ?>đ</strong>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between small">
                                                <span>Chi Phí Vận Chuyển</span>
                                                <strong
                                                    class="text-danger">-<?php echo e(number_format($thisWeekProfitDetails['shipping_cost'], 0, ',', '.')); ?>đ</strong>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between small">
                                                <span>Tiền Nhân Công</span>
                                                <strong
                                                    class="text-danger">-<?php echo e(number_format($thisWeekProfitDetails['staff_cost'], 0, ',', '.')); ?>đ</strong>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between small">
                                                <span>Hao Hụt Hàng Tồn Kho</span>
                                                <strong
                                                    class="text-danger">-<?php echo e(number_format($thisWeekProfitDetails['inventory_shrinkage'], 0, ',', '.')); ?>đ</strong>
                                            </li>
                                        </ul>
                                    </div>

                                    <!-- Result Section -->
                                    <hr class="my-2">
                                    <div class="text-center">
                                        <p class="mb-1 small text-muted"><strong>= LỢI NHUẬN THỰC</strong></p>
                                        <h4 class="mb-0" style="color: <?php if($thisWeekProfit >= 0): ?> #28a745 <?php else: ?> #dc3545 <?php endif; ?>">
                                            <strong><?php echo e(number_format($thisWeekProfit, 0, ',', '.')); ?>đ</strong>
                                        </h4>
                                    </div>
                                </div>
                            </div>

                            <h6 class="mb-2">Thống Kê Tuần Hiện Tại:</h6>
                            <ul class="list-group list-group-flush small">
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Số ca làm việc:</span>
                                    <strong><?php echo e(array_key_exists(3, $weeklyComparison) ? $weeklyComparison[3]['shifts'] : 0); ?>

                                        ca</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Tổng giờ làm:</span>
                                    <strong><?php echo e($thisWeekHours); ?> giờ</strong>
                                </li>
                            </ul>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Chi Tiết Lợi Nhuận - Tháng Này -->
            <div class="modal fade" id="profitDetailModalMonth" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-light">
                            <h5 class="modal-title">Chi Tiết Lợi Nhuận - Tháng Này <small
                                    class="text-muted d-block mt-1"><?php echo e($monthDateRange); ?></small></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <!-- Revenue Breakdown -->
                            <div class="card border-0 bg-light mb-3">
                                <div class="card-body p-3">
                                    <h6 class="mb-2 text-primary"><strong>Chi Tiết Lợi Nhuận Tháng Này</strong></h6>

                                    <!-- Income Section -->
                                    <div class="mb-3">
                                        <h6 class="text-success small mb-2">DOANH THU</h6>
                                        <ul class="list-group list-group-sm">
                                            <li class="list-group-item d-flex justify-content-between small">
                                                <span>Doanh Thu Bán</span>
                                                <strong
                                                    class="text-success">+<?php echo e(number_format($thisMonthProfitDetails['revenue'], 0, ',', '.')); ?>đ</strong>
                                            </li>
                                        </ul>
                                    </div>

                                    <!-- Deductions Section -->
                                    <div class="mb-3">
                                        <h6 class="text-danger small mb-2">KHOẢN TRỪ</h6>
                                        <ul class="list-group list-group-sm">
                                            <li class="list-group-item d-flex justify-content-between small">
                                                <span>Giảm Giá</span>
                                                <strong
                                                    class="text-danger">-<?php echo e(number_format($thisMonthProfitDetails['discounts'], 0, ',', '.')); ?>đ</strong>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between small">
                                                <span>Giá Vốn Hàng Bán (COGS)</span>
                                                <strong
                                                    class="text-danger">-<?php echo e(number_format($thisMonthProfitDetails['cogs'], 0, ',', '.')); ?>đ</strong>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between small">
                                                <span>Chi Phí Vận Chuyển</span>
                                                <strong
                                                    class="text-danger">-<?php echo e(number_format($thisMonthProfitDetails['shipping_cost'], 0, ',', '.')); ?>đ</strong>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between small">
                                                <span>Tiền Nhân Công</span>
                                                <strong
                                                    class="text-danger">-<?php echo e(number_format($thisMonthProfitDetails['staff_cost'], 0, ',', '.')); ?>đ</strong>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between small">
                                                <span>Hao Hụt Hàng Tồn Kho</span>
                                                <strong
                                                    class="text-danger">-<?php echo e(number_format($thisMonthProfitDetails['inventory_shrinkage'], 0, ',', '.')); ?>đ</strong>
                                            </li>
                                        </ul>
                                    </div>

                                    <!-- Result Section -->
                                    <hr class="my-2">
                                    <div class="text-center">
                                        <p class="mb-1 small text-muted"><strong>= LỢI NHUẬN THỰC</strong></p>
                                        <h4 class="mb-0" style="color: <?php if($thisMonthProfit >= 0): ?> #28a745 <?php else: ?> #dc3545 <?php endif; ?>">
                                            <strong><?php echo e(number_format($thisMonthProfit, 0, ',', '.')); ?>đ</strong>
                                        </h4>
                                    </div>
                                </div>
                            </div>

                            <h6 class="mb-2">Thống Kê Tháng Hiện Tại:</h6>
                            <ul class="list-group list-group-flush small">
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Số ca làm việc:</span>
                                    <strong><?php echo e(array_key_exists(11, $monthlyComparison) ? $monthlyComparison[11]['shifts'] : 0); ?>

                                        ca</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Tổng giờ làm:</span>
                                    <strong><?php echo e($thisMonthHours); ?> giờ</strong>
                                </li>
                            </ul>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- MODAL CHI TIẾT THỐNG KÊ (6 TABS) -->
            <div class="modal fade" id="statsDetailModal" tabindex="-1">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header bg-light">
                            <h5 class="modal-title">Chi Tiết Thống Kê - Tháng <?php echo e($monthLabel ?? ''); ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-0">
                            <!-- Tab Navigation -->
                            <ul class="nav nav-tabs border-bottom-0" role="tablist" style="background: #f8f9fa; padding: 1rem;">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="revenueTab" data-bs-toggle="tab"
                                        data-bs-target="#revenueContent" type="button" role="tab">
                                        Doanh Thu
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="ordersTab" data-bs-toggle="tab" data-bs-target="#ordersContent"
                                        type="button" role="tab">
                                        Đơn Hàng
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="customersTab" data-bs-toggle="tab"
                                        data-bs-target="#customersContent" type="button" role="tab">
                                        Khách Hàng
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="productsTab" data-bs-toggle="tab"
                                        data-bs-target="#productsContent" type="button" role="tab">
                                        Sản Phẩm
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="accountsTab" data-bs-toggle="tab"
                                        data-bs-target="#accountsContent" type="button" role="tab">
                                        Tài Khoản
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="inventoryTab" data-bs-toggle="tab"
                                        data-bs-target="#inventoryContent" type="button" role="tab">
                                        Kho Hàng
                                    </button>
                                </li>
                            </ul>

                            <!-- Tab Content -->
                            <div class="tab-content p-3">
                                <!-- TAB 1: DOANH THU -->
                                <div class="tab-pane fade show active" id="revenueContent" role="tabpanel">
                                    <div class="accordion" id="revenueAccordion">
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="headingRevenue">
                                                <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                                    data-bs-target="#collapseRevenue">
                                                    Tổng Doanh Thu
                                                </button>
                                            </h2>
                                            <div id="collapseRevenue" class="accordion-collapse collapse show"
                                                data-bs-parent="#revenueAccordion">
                                                <div class="accordion-body">
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <div class="p-3 bg-light rounded">
                                                                <small class="text-muted">Hôm Nay</small>
                                                                <h5 class="text-success mb-0">
                                                                    <?php echo e(number_format($todayRevenue, 0, ',', '.')); ?> ₫
                                                                </h5>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="p-3 bg-light rounded">
                                                                <small class="text-muted">Tuần Này</small>
                                                                <h5 class="text-info mb-0">
                                                                    <?php echo e(number_format($weekRevenue, 0, ',', '.')); ?> ₫
                                                                </h5>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="p-3 bg-light rounded">
                                                                <small class="text-muted">Tháng Này</small>
                                                                <h5 class="text-primary mb-0">
                                                                    <?php echo e(number_format($thisMonthRevenue ?? 0, 0, ',', '.')); ?> ₫
                                                                </h5>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="p-3 bg-light rounded">
                                                                <small class="text-muted">Tháng Chọn</small>
                                                                <h5 class="mb-0"><?php echo e(number_format($monthRevenue, 0, ',', '.')); ?>

                                                                    ₫</h5>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- TAB 2: ĐƠN HÀNG -->
                                <div class="tab-pane fade" id="ordersContent" role="tabpanel">
                                    <div class="accordion" id="ordersAccordion">
                                        <div class="accordion-item">
                                            <h2 class="accordion-header">
                                                <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                                    data-bs-target="#collapseOrders">
                                                    Thống Kê Đơn Hàng
                                                </button>
                                            </h2>
                                            <div id="collapseOrders" class="accordion-collapse collapse show"
                                                data-bs-parent="#ordersAccordion">
                                                <div class="accordion-body">
                                                    <div class="row g-2">
                                                        <div class="col-6 col-md-4">
                                                            <div class="p-2 border rounded text-center">
                                                                <small class="text-muted d-block">Đơn Mới</small>
                                                                <h6 class="mb-0 text-primary"><?php echo e($newOrdersThisMonth ?? 0); ?>

                                                                </h6>
                                                            </div>
                                                        </div>
                                                        <div class="col-6 col-md-4">
                                                            <div class="p-2 border rounded text-center">
                                                                <small class="text-muted d-block">Hoàn Thành</small>
                                                                <h6 class="mb-0 text-success">
                                                                    <?php echo e($orderStatusSummary['completed'] ?? 0); ?>

                                                                </h6>
                                                            </div>
                                                        </div>
                                                        <div class="col-6 col-md-4">
                                                            <div class="p-2 border rounded text-center">
                                                                <small class="text-muted d-block">Hủy/Hoàn</small>
                                                                <h6 class="mb-0 text-danger">
                                                                    <?php echo e(($orderStatusSummary['cancelled'] ?? 0) + ($orderStatusSummary['refunded'] ?? 0)); ?>

                                                                </h6>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- TAB 3: KHÁCH HÀNG -->
                                <div class="tab-pane fade" id="customersContent" role="tabpanel">
                                    <div class="accordion" id="customersAccordion">
                                        <div class="accordion-item">
                                            <h2 class="accordion-header">
                                                <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                                    data-bs-target="#collapseCustomers">
                                                    Thống Kê Khách Hàng
                                                </button>
                                            </h2>
                                            <div id="collapseCustomers" class="accordion-collapse collapse show"
                                                data-bs-parent="#customersAccordion">
                                                <div class="accordion-body">
                                                    <div class="row g-2">
                                                        <div class="col-6 col-md-4">
                                                            <div class="p-2 border rounded text-center">
                                                                <small class="text-muted d-block">Khách Mới</small>
                                                                <h6 class="mb-0 text-primary"><?php echo e($newCustomersThisMonth ?? 0); ?>

                                                                </h6>
                                                            </div>
                                                        </div>
                                                        <div class="col-6 col-md-4">
                                                            <div class="p-2 border rounded text-center">
                                                                <small class="text-muted d-block">Quay Lại</small>
                                                                <h6 class="mb-0 text-success">
                                                                    <?php echo e(number_format($returningCustomerRate, 2)); ?>%
                                                                </h6>
                                                            </div>
                                                        </div>
                                                        <div class="col-6 col-md-4">
                                                            <div class="p-2 border rounded text-center">
                                                                <small class="text-muted d-block">Hủy Nhiều</small>
                                                                <h6 class="mb-0 text-warning">
                                                                    <?php echo e(count($cancellationByCustomer)); ?>

                                                                </h6>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- TAB 4: SẢN PHẨM -->
                                <div class="tab-pane fade" id="productsContent" role="tabpanel">
                                    <div class="accordion" id="productsAccordion">
                                        <div class="accordion-item">
                                            <h2 class="accordion-header">
                                                <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                                    data-bs-target="#collapseProducts">
                                                    Thống Kê Sản Phẩm
                                                </button>
                                            </h2>
                                            <div id="collapseProducts" class="accordion-collapse collapse show"
                                                data-bs-parent="#productsAccordion">
                                                <div class="accordion-body">
                                                    <div class="row g-2">
                                                        <div class="col-6 col-md-4">
                                                            <div class="p-2 border rounded text-center">
                                                                <small class="text-muted d-block">Đánh Giá</small>
                                                                <h6 class="mb-0 text-warning"><?php echo e($reviewsThisMonth ?? 0); ?></h6>
                                                            </div>
                                                        </div>
                                                        <div class="col-6 col-md-4">
                                                            <div class="p-2 border rounded text-center">
                                                                <small class="text-muted d-block">Yêu Thích</small>
                                                                <h6 class="mb-0 text-info"><?php echo e($wishlistInteractions ?? 0); ?></h6>
                                                            </div>
                                                        </div>
                                                        <div class="col-6 col-md-4">
                                                            <div class="p-2 border rounded text-center">
                                                                <small class="text-muted d-block">Bán Chạy</small>
                                                                <h6 class="mb-0 text-success"><?php echo e(count($topProductsMonth)); ?>

                                                                </h6>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- TAB 5: TÀI KHOẢN -->
                                <div class="tab-pane fade" id="accountsContent" role="tabpanel">
                                    <div class="accordion" id="accountsAccordion">
                                        <div class="accordion-item">
                                            <h2 class="accordion-header">
                                                <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                                    data-bs-target="#collapseAccounts">
                                                    Thống Kê Tài Khoản
                                                </button>
                                            </h2>
                                            <div id="collapseAccounts" class="accordion-collapse collapse show"
                                                data-bs-parent="#accountsAccordion">
                                                <div class="accordion-body">
                                                    <div class="row g-2">
                                                        <div class="col-6 col-md-4">
                                                            <div class="p-2 border rounded text-center">
                                                                <small class="text-muted d-block">Mới</small>
                                                                <h6 class="mb-0 text-primary">
                                                                    <?php echo e($accountStats['new_accounts'] ?? 0); ?>

                                                                </h6>
                                                            </div>
                                                        </div>
                                                        <div class="col-6 col-md-4">
                                                            <div class="p-2 border rounded text-center">
                                                                <small class="text-muted d-block">Hoạt Động</small>
                                                                <h6 class="mb-0 text-success">
                                                                    <?php echo e($accountStats['active_accounts'] ?? 0); ?>

                                                                </h6>
                                                            </div>
                                                        </div>
                                                        <div class="col-6 col-md-4">
                                                            <div class="p-2 border rounded text-center">
                                                                <small class="text-muted d-block">Khóa</small>
                                                                <h6 class="mb-0 text-danger">
                                                                    <?php echo e($accountStats['locked_accounts'] ?? 0); ?>

                                                                </h6>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- TAB 6: KHO HÀNG -->
                                <div class="tab-pane fade" id="inventoryContent" role="tabpanel">
                                    <div class="accordion" id="inventoryAccordion">
                                        <div class="accordion-item">
                                            <h2 class="accordion-header">
                                                <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                                    data-bs-target="#collapseInventory">
                                                    Chi Phí Hủy
                                                </button>
                                            </h2>
                                            <div id="collapseInventory" class="accordion-collapse collapse show"
                                                data-bs-parent="#inventoryAccordion">
                                                <div class="accordion-body">
                                                    <div class="row g-2">
                                                        <div class="col-6 col-md-4">
                                                            <div class="p-2 border rounded text-center">
                                                                <small class="text-muted d-block">Hôm Nay</small>
                                                                <h6 class="mb-0 text-warning text-truncate">
                                                                    <?php echo e(number_format($writeoffMetrics['total_writeoff_cost_today'] ?? 0, 0, ',', '.')); ?>

                                                                    ₫
                                                                </h6>
                                                            </div>
                                                        </div>
                                                        <div class="col-6 col-md-4">
                                                            <div class="p-2 border rounded text-center">
                                                                <small class="text-muted d-block">Tuần Này</small>
                                                                <h6 class="mb-0 text-info text-truncate">
                                                                    <?php echo e(number_format($writeoffMetrics['total_writeoff_cost_week'] ?? 0, 0, ',', '.')); ?>

                                                                    ₫
                                                                </h6>
                                                            </div>
                                                        </div>
                                                        <div class="col-6 col-md-4">
                                                            <div class="p-2 border rounded text-center">
                                                                <small class="text-muted d-block">Tháng Này</small>
                                                                <h6 class="mb-0 text-danger text-truncate">
                                                                    <?php echo e(number_format($writeoffMetrics['total_writeoff_cost_month'] ?? 0, 0, ',', '.')); ?>

                                                                    ₫
                                                                </h6>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="row g-4 mt-1">
                <div class="col-lg-5">
                    <div class="bg-light rounded p-4 h-100">
                        <h6 class="mb-3">Tiền bán theo từng ngày trong tháng</h6>
                        <canvas id="dailyRevenueChart" style="max-height: 280px;"></canvas>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="bg-light rounded p-4 h-100">
                        <h6 class="mb-3">Tình trạng đơn trong tháng</h6>
                        <canvas id="orderStatusChart" style="max-height: 280px;"></canvas>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="bg-light rounded p-4 h-100">
                        <h6 class="mb-3">Nhóm sản phẩm bán nhiều</h6>
                        <canvas id="categoryMixChart" style="max-height: 280px;"></canvas>
                    </div>
                </div>
            </div>

            <!-- Top Customer Stats - Merged -->
            <div class="row g-4 mt-1">
                <div class="col-12">
                    <div class="bg-light rounded p-4 h-100">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="mb-0">Phân Tích Khách Hàng Tháng <?php echo e($monthLabel); ?></h6>
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                data-bs-target="#customerDetailModal">
                                Xem Chi Tiết
                            </button>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="card border-0 light-primary text-dark h-100">
                                    <div class="card-body">
                                        <p class="mb-2 small">Khách Hàng Đặt Nhiều Đơn Nhất</p>
                                        <?php if($topCustomersByOrders->isNotEmpty()): ?>
                                            <?php $__currentLoopData = $topCustomersByOrders->take(1); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <h6 class="mb-1"><?php echo e($customer->customer_name); ?></h6>
                                                <small>Số đơn: <?php echo e(number_format($customer->orders_count)); ?> |Tổng chi:
                                                    <?php echo e(number_format($customer->total_spent, 0, ',', '.')); ?>₫</small>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        <?php else: ?>
                                            <small>Chưa có dữ liệu</small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-0 light-success text-dark h-100">
                                    <div class="card-body">
                                        <p class="mb-2 small">Khách Hàng Có Giá Trị Cao Nhất</p>
                                        <?php if($topCustomersByValue->isNotEmpty()): ?>
                                            <?php $__currentLoopData = $topCustomersByValue->take(1); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <h6 class="mb-1"><?php echo e($customer->customer_name); ?></h6>
                                                <small>Tổng chi: <?php echo e(number_format($customer->total_spent, 0, ',', '.')); ?>₫ | Số
                                                    đơn: <?php echo e(number_format($customer->orders_count)); ?></small>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        <?php else: ?>
                                            <small>Chưa có dữ liệu</small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Chi Tiết Khách Hàng -->
            <div class="modal fade" id="customerDetailModal" tabindex="-1">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header bg-light">
                            <h5 class="modal-title">Chi Tiết Phân Tích Khách Hàng - Tháng <?php echo e($monthLabel); ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <!-- Tab Navigation -->
                            <ul class="nav nav-tabs mb-3" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="ordersTab" data-bs-toggle="tab"
                                        data-bs-target="#ordersContent" type="button" role="tab">
                                        Đặt Nhiều Đơn
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="valueTab" data-bs-toggle="tab" data-bs-target="#valueContent"
                                        type="button" role="tab">
                                        Giá Trị Cao
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="cancellationTab" data-bs-toggle="tab"
                                        data-bs-target="#cancellationContent" type="button" role="tab">
                                        Hủy Đơn Nhiều
                                    </button>
                                </li>
                            </ul>

                            <!-- Tab Content -->
                            <div class="tab-content">
                                <!-- Top by Orders -->
                                <div class="tab-pane fade show active" id="ordersContent" role="tabpanel">
                                    <h6 class="mb-2">Top 3 Khách Hàng Đặt Nhiều Đơn Nhất</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Khách Hàng</th>
                                                    <th class="text-end">Số Đơn</th>
                                                    <th class="text-end">Tổng Chi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $__empty_1 = true; $__currentLoopData = $topCustomersByOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                    <tr>
                                                        <td><?php echo e($index + 1); ?></td>
                                                        <td><?php echo e($customer->customer_name); ?></td>
                                                        <td class="text-end">
                                                            <strong><?php echo e(number_format($customer->orders_count)); ?></strong>
                                                        </td>
                                                        <td class="text-end text-success">
                                                            <strong><?php echo e(number_format($customer->total_spent, 0, ',', '.')); ?>₫</strong>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                    <tr>
                                                        <td colspan="4">Chưa có dữ liệu trong tháng đã chọn</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Top by Value -->
                                <div class="tab-pane fade" id="valueContent" role="tabpanel">
                                    <h6 class="mb-2">Top 3 Khách Hàng Có Giá Trị Đơn Cao Nhất</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Khách Hàng</th>
                                                    <th class="text-end">Tổng Chi</th>
                                                    <th class="text-end">Số Đơn</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $__empty_1 = true; $__currentLoopData = $topCustomersByValue; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                    <tr>
                                                        <td><?php echo e($index + 1); ?></td>
                                                        <td><?php echo e($customer->customer_name); ?></td>
                                                        <td class="text-end text-success">
                                                            <strong><?php echo e(number_format($customer->total_spent, 0, ',', '.')); ?>₫</strong>
                                                        </td>
                                                        <td class="text-end"><?php echo e(number_format($customer->orders_count)); ?></td>
                                                    </tr>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                    <tr>
                                                        <td colspan="4">Chưa có dữ liệu trong tháng đã chọn</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Cancellation Tracking -->
                                <div class="tab-pane fade" id="cancellationContent" role="tabpanel">
                                    <h6 class="mb-2">Khách Hàng Hủy Đơn Nhiều Nhất (Cần Theo Dõi/Xem Xét)</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Khách Hàng</th>
                                                    <th class="text-end">Số Đơn Đã Hủy</th>
                                                    <th>Đề Xuất</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $__empty_1 = true; $__currentLoopData = $cancellationByCustomer; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                    <tr>
                                                        <td><?php echo e($item->customer_name); ?></td>
                                                        <td class="text-end">
                                                            <strong><?php echo e(number_format($item->cancelled_count)); ?></strong>
                                                        </td>
                                                        <td>
                                                            <?php if($item->cancelled_count >= 3): ?>
                                                                <span class="badge bg-danger">Nên xem xét khóa</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-secondary">Theo dõi thêm</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                    <tr>
                                                        <td colspan="3">Không có khách hàng hủy đơn trong tháng</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Top Products & Promotions Table -->
            <div class="row g-4 mt-1">
                <div class="col-lg-8">
                    <div class="bg-light rounded p-4 h-100">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="mb-0">Sản Phẩm Bán Chạy & Đánh Giá Cao - Tháng <?php echo e($monthLabel); ?></h6>
                            <small class="text-muted">Hiển thị với hình ảnh sản phẩm</small>
                        </div>
                        <div class="row g-2">
                            <?php $__empty_1 = true; $__currentLoopData = $topProductsMonth; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <div class="col-md-6 col-lg-4">
                                    <div class="card border-0 shadow-sm h-100 position-relative overflow-hidden"
                                        style="transition: transform 0.3s ease;">
                                        <!-- Product Image -->
                                        <div class="position-relative bg-light d-flex align-items-center justify-content-center"
                                            style="height: 200px; overflow: hidden;">
                                            <?php if($product->product_image && file_exists(public_path('storage/' . $product->product_image))): ?>
                                                <img src="<?php echo e(asset('storage/' . $product->product_image)); ?>"
                                                    alt="<?php echo e($product->product_name); ?>" class="w-100 h-100" style="object-fit: cover;">
                                            <?php else: ?>
                                                <div class="text-center p-3" style="width: 100%;">
                                                    <i style="font-size: 3rem; color: #ccc;">?</i>
                                                    <p class="text-muted small mt-2 mb-0">Chưa có hình ảnh</p>
                                                </div>
                                            <?php endif; ?>
                                            <!-- Rank Badge -->
                                            <span class="badge bg-danger position-absolute top-0 start-0 m-2"
                                                style="font-size: 1.1rem; z-index: 10;">
                                                #<?php echo e($index + 1); ?>

                                            </span>
                                        </div>
                                        <!-- Product Info -->
                                        <div class="card-body pb-2">
                                            <h6 class="card-title small mb-2 text-truncate" title="<?php echo e($product->product_name); ?>">
                                                <?php echo e(\Illuminate\Support\Str::limit($product->product_name, 40, '...')); ?>

                                            </h6>
                                            <div class="small text-muted">
                                                <p class="mb-1">Đã bán: <strong
                                                        class="text-dark"><?php echo e(number_format($product->sold_qty)); ?></strong></p>
                                                <p class="mb-0">Lợi nhuận: <strong
                                                        class="text-success"><?php echo e(number_format($product->total_profit, 0, ',', '.')); ?>₫</strong>
                                                </p>
                                            </div>
                                        </div>
                                        <!-- Footer -->
                                        <div class="card-footer bg-light border-top small">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span>Đánh giá:</span>
                                                <span class="badge bg-primary">
                                                    <?php if(isset($bestSellerTopRatedProducts) && $bestSellerTopRatedProducts->where('product_id', $product->product_id)->first()): ?>
                                                        <?php echo e(number_format($bestSellerTopRatedProducts->where('product_id', $product->product_id)->first()->avg_rating ?? 0, 1)); ?>/5
                                                    <?php else: ?>
                                                        N/A
                                                    <?php endif; ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <div class="col-12">
                                    <div class="alert alert-info mb-0">Chưa có dữ liệu sản phẩm bán tốt trong tháng này</div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="bg-light rounded p-4 h-100">
                        <h6 class="mb-3">Mức độ dùng mã giảm giá theo chiến dịch</h6>
                        <div class="table-responsive">
                            <table class="table text-start align-middle table-bordered table-hover mb-0">
                                <thead>
                                    <tr class="text-dark">
                                        <th>Mã/chiến dịch</th>
                                        <th>Đơn áp dụng</th>
                                        <th>Tỷ lệ áp dụng</th>
                                        <th>Tổng giảm</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__empty_1 = true; $__currentLoopData = $voucherCampaignStats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <tr>
                                            <td><?php echo e($item->campaign_code); ?></td>
                                            <td><?php echo e(number_format($item->applied_orders)); ?></td>
                                            <td><?php echo e(number_format($item->apply_rate, 2)); ?>%</td>
                                            <td><?php echo e(number_format($item->total_discount, 0, ',', '.')); ?> ₫</td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <tr>
                                            <td colspan="4">Tháng này chưa có đơn dùng mã giảm giá</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Category Analytics Charts -->
            <div class="row g-4 mt-1">
                <div class="col-lg-6">
                    <div class="bg-light rounded p-4 h-100">
                        <h6 class="mb-3">Thống kê số lượng sản phẩm theo danh mục</h6>
                        <canvas id="productCountByCategoryChart" style="max-height: 320px;"></canvas>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="bg-light rounded p-4 h-100">
                        <h6 class="mb-3">Chi tiết số lượng sản phẩm theo danh mục</h6>
                        <div class="table-responsive">
                            <table class="table text-start align-middle table-bordered table-hover mb-0">
                                <thead>
                                    <tr class="text-dark">
                                        <th>Danh mục</th>
                                        <th>Số sản phẩm</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__empty_1 = true; $__currentLoopData = $productCountByCategory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <tr>
                                            <td><?php echo e($item->category_name); ?></td>
                                            <td><?php echo e(number_format($item->product_count)); ?></td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <tr>
                                            <td colspan="2">Chưa có dữ liệu danh mục sản phẩm</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Orders & Reviews -->
            <div class="row g-4 mt-1">
                <div class="col-lg-6">
                    <div class="bg-light rounded p-4 h-100">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="mb-0">Tóm tắt đơn hàng trong tháng</h6>
                            <span
                                class="badge <?php echo e($cancelOrRefundRate >= 20 ? 'bg-danger' : ($cancelOrRefundRate >= 10 ? 'bg-warning text-dark' : 'bg-success')); ?>">
                                Hủy/hoàn: <?php echo e(number_format($cancelOrRefundRate, 2)); ?>%
                            </span>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="border rounded p-2 h-100">
                                    <small class="text-muted d-block">Tổng đơn trong tháng</small>
                                    <strong><?php echo e(number_format(array_sum($orderStatusSummary))); ?></strong>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-2 h-100">
                                    <small class="text-muted d-block">Đơn hoàn thành</small>
                                    <strong class="text-success"><?php echo e(number_format($orderStatusSummary['completed'])); ?></strong>
                                </div>
                            </div>
                        </div>
                        <canvas id="monthlyOrderSummaryChart" style="max-height: 300px;"></canvas>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="bg-light rounded p-4 h-100">
                        <h6 class="mb-3">Đánh giá gần đây</h6>
                        <?php $__empty_1 = true; $__currentLoopData = $recentReviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="border-bottom py-2">
                                <p class="mb-1"><strong><?php echo e($review->customer->user->name ?? 'Khách hàng'); ?></strong> -
                                    <?php echo e($review->product->name ?? 'Sản phẩm'); ?>

                                </p>
                                <p class="mb-1">Điểm: <?php echo e(number_format($review->rating, 1)); ?>/5</p>
                                <small class="text-muted"><?php echo e($review->content ?: 'Không có nội dung.'); ?></small>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <p class="mb-0 text-muted">Chưa có đánh giá.</p>
                        <?php endif; ?>
                        <p class="mt-3 mb-0">Đánh giá được duyệt tháng này:
                            <strong><?php echo e(number_format((int) max(0, $reviewsThisMonth ?? 0), 0)); ?></strong>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Expiring Products Table -->
            <div class="row g-4 mt-1">
                <div class="col-12">
                    <div class="bg-light rounded p-4">
                        <h6 class="mb-3">Sản phẩm hết hạn trong tháng <?php echo e($monthLabel); ?> cần ưu tiên xử lý</h6>
                        <div class="table-responsive">
                            <table class="table text-start align-middle table-bordered table-hover mb-0">
                                <thead>
                                    <tr class="text-dark">
                                        <th>Sản phẩm</th>
                                        <th>Mã sản phẩm</th>
                                        <th>Hạn dùng</th>
                                        <th>Tồn còn</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__empty_1 = true; $__currentLoopData = $expiringProductsToPush; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <tr>
                                            <td><?php echo e($item->product_name); ?></td>
                                            <td><?php echo e($item->sku ?: 'Không có'); ?></td>
                                            <td><?php echo e(\Carbon\Carbon::parse($item->expired_at)->format('d/m/Y')); ?></td>
                                            <td><?php echo e(number_format($item->remaining_qty)); ?></td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <tr>
                                            <td colspan="4">Không có sản phẩm hết hạn trong tháng đã chọn</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        (function () {
            const dailyRevenueLabels = <?php echo json_encode($dashboardCharts['daily_revenue']['labels'], 15, 512) ?>;
            const dailyRevenueValues = <?php echo json_encode($dashboardCharts['daily_revenue']['values'], 15, 512) ?>;

            const orderStatusLabels = <?php echo json_encode($dashboardCharts['order_status']['labels'], 15, 512) ?>;
            const orderStatusData = <?php echo json_encode($dashboardCharts['order_status']['data'], 15, 512) ?>;

            const categoryMixLabels = <?php echo json_encode($dashboardCharts['category_mix']['labels'], 15, 512) ?>;
            const categoryMixData = <?php echo json_encode($dashboardCharts['category_mix']['data'], 15, 512) ?>;

            const productCountByCategoryLabels = <?php echo json_encode($dashboardCharts['product_count_by_category']['labels'], 15, 512) ?>;
            const productCountByCategoryData = <?php echo json_encode($dashboardCharts['product_count_by_category']['data'], 15, 512) ?>;

            const monthlyOrderSummaryLabels = [
                'Chờ xử lý',
                'Đã xác nhận',
                'Đang giao',
                'Hoàn thành',
                'Đã hủy',
                'Đã hoàn tiền'
            ];
            const monthlyOrderSummaryData = [
                <?php echo json_encode((int) $orderStatusSummary['pending'], 15, 512) ?>,
                <?php echo json_encode((int) $orderStatusSummary['confirmed'], 15, 512) ?>,
                <?php echo json_encode((int) $orderStatusSummary['shipping'], 15, 512) ?>,
                <?php echo json_encode((int) $orderStatusSummary['completed'], 15, 512) ?>,
                <?php echo json_encode((int) $orderStatusSummary['cancelled'], 15, 512) ?>,
                <?php echo json_encode((int) $orderStatusSummary['refunded'], 15, 512) ?>
            ];

            const moneyFormat = (value) => {
                return new Intl.NumberFormat('vi-VN').format(Math.round(value || 0)) + ' ₫';
            };

            const dailyRevenueEl = document.getElementById('dailyRevenueChart');
            if (dailyRevenueEl && dailyRevenueLabels.length) {
                new Chart(dailyRevenueEl, {
                    type: 'line',
                    data: {
                        labels: dailyRevenueLabels,
                        datasets: [{
                            label: 'Tiền bán trong ngày',
                            data: dailyRevenueValues,
                            borderColor: '#0d6efd',
                            backgroundColor: 'rgba(13,110,253,0.16)',
                            fill: true,
                            tension: 0.3
                        }]
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
                        }
                    }
                });
            }

            const orderStatusEl = document.getElementById('orderStatusChart');
            if (orderStatusEl && orderStatusLabels.length) {
                new Chart(orderStatusEl, {
                    type: 'doughnut',
                    data: {
                        labels: orderStatusLabels,
                        datasets: [{
                            data: orderStatusData,
                            backgroundColor: ['#0d6efd', '#20c997', '#ffc107', '#dc3545', '#6c757d', '#0dcaf0']
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
            }

            const categoryMixEl = document.getElementById('categoryMixChart');
            if (categoryMixEl && categoryMixLabels.length) {
                new Chart(categoryMixEl, {
                    type: 'pie',
                    data: {
                        labels: categoryMixLabels,
                        datasets: [{
                            data: categoryMixData,
                            backgroundColor: ['#0d6efd', '#20c997', '#ffc107', '#fd7e14', '#6f42c1', '#dc3545']
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
            }

            const productCountByCategoryEl = document.getElementById('productCountByCategoryChart');
            if (productCountByCategoryEl && productCountByCategoryLabels.length) {
                new Chart(productCountByCategoryEl, {
                    type: 'bar',
                    data: {
                        labels: productCountByCategoryLabels,
                        datasets: [{
                            label: 'Số lượng sản phẩm',
                            data: productCountByCategoryData,
                            backgroundColor: 'rgba(13, 110, 253, 0.72)'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
            }

            const monthlyOrderSummaryEl = document.getElementById('monthlyOrderSummaryChart');
            if (monthlyOrderSummaryEl) {
                new Chart(monthlyOrderSummaryEl, {
                    type: 'bar',
                    data: {
                        labels: monthlyOrderSummaryLabels,
                        datasets: [{
                            label: 'Số đơn',
                            data: monthlyOrderSummaryData,
                            backgroundColor: ['#ffc107', '#0d6efd', '#0dcaf0', '#198754', '#dc3545', '#6c757d'],
                            borderRadius: 6,
                            barThickness: 18,
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: (context) => `${context.raw} đơn`
                                }
                            }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            }
                        }
                    }
                });
            }
        })();
    </script>

    <style>
        /* Light Color Classes */
        .light-primary {
            background-color: #e7f1ff;
            color: #0d6efd;
        }

        .light-info {
            background-color: #e7f5ff;
            color: #0dcaf0;
        }

        .light-success {
            background-color: #e8f5e9;
            color: #198754;
        }

        .light-warning {
            background-color: #fff9e6;
            color: #ffc107;
        }

        .light-danger {
            background-color: #ffe7e7;
            color: #dc3545;
        }

        .light-secondary {
            background-color: #f0f0f0;
            color: #6c757d;
        }

        /* KPI Card Hover Effects */
        .card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }

        /* Compact Stats Grid */
        .card-stat {
            transition: all 0.3s ease;
            background-color: #f8f9fa;
        }

        .card-stat:hover {
            background-color: #e9ecef;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.08);
        }

        /* Modal Styling */
        .modal-content {
            border: none;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        }

        .modal-header {
            border-bottom: 2px solid #f0f0f0;
        }

        /* Tab Navigation */
        .nav-tabs .nav-link {
            color: #495057;
            border: none;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
        }

        .nav-tabs .nav-link:hover {
            color: #0d6efd;
            border-bottom-color: #0d6efd;
        }

        .nav-tabs .nav-link.active {
            color: #0d6efd;
            background-color: transparent;
            border-bottom-color: #0d6efd;
        }

        /* Responsive Utilities */
        @media (max-width: 768px) {
            .d-flex.flex-wrap {
                gap: 1rem;
            }

            .col-lg-2 {
                flex: 0 0 calc(33.333% - 0.5rem);
            }

            .compact-stat-grid {
                gap: 0.5rem;
            }
        }

        /* Table Responsive */
        .table-responsive {
            border-radius: 0.375rem;
        }

        /* Badge Styling */
        .badge {
            padding: 0.5rem 0.75rem;
            font-weight: 500;
        }

        /* List Group */
        .list-group-item {
            background-color: #ffffff;
            border-color: #e9ecef;
        }

        /* Card Footer */
        .card-footer {
            background-color: #f8f9fa;
            border-top: 1px solid #dee2e6;
        }

        /* Utility Classes */
        .text-white-50 {
            color: rgba(255, 255, 255, 0.5);
        }

        .text-white-75 {
            color: rgba(255, 255, 255, 0.75);
        }

        .opacity-50 {
            opacity: 0.5;
        }
    </style>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('admin.layouts.layout_admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\luanvan\resources\views/admin/dashboard.blade.php ENDPATH**/ ?>