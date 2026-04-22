

<?php $__env->startSection('content'); ?>

    <style>
        .customer-summary-value {
            font-size: 1.8rem;
            font-weight: 700;
            line-height: 1;
        }

        .customer-name {
            font-weight: 600;
            color: #191c24;
        }

        .customer-subtext,
        .lock-reason {
            font-size: 12px;
            color: #6c757d;
        }

        .lock-reason {
            margin-top: 4px;
            line-height: 1.5;
        }

        .customer-action-group {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .customer-action-group form {
            margin: 0;
        }

        .customer-action-btn {
            min-width: 84px;
        }

        .customer-detail-card {
            border: 1px solid #e9ecef;
            border-radius: 10px;
            background: #fff;
            padding: 16px;
            height: 100%;
        }

        .customer-detail-label {
            display: block;
            font-size: 12px;
            color: #6c757d;
            margin-bottom: 4px;
        }

        .customer-detail-value {
            color: #191c24;
            font-weight: 600;
            word-break: break-word;
        }

        .customer-avatar {
            width: 88px;
            height: 88px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #eef2f6;
        }

        .customer-detail-header {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 20px;
        }
    </style>

    <div class="container-fluid pt-4 px-4">
        <div class="bg-light rounded p-4">

            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div>
                    <h5 class="mb-1">Danh sách khách hàng</h5>
                </div>
                <span class="badge bg-primary">Hiển thị <?php echo e($customers->count()); ?> / <?php echo e($customers->total()); ?> khách
                    hàng</span>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Tổng khách hàng</small>
                        <div class="customer-summary-value text-dark"><?php echo e($stats['total'] ?? 0); ?></div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Đang hoạt động</small>
                        <div class="customer-summary-value text-success"><?php echo e($stats['active'] ?? 0); ?></div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Đang bị khóa</small>
                        <div class="customer-summary-value text-danger"><?php echo e($stats['locked'] ?? 0); ?></div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Mới trong tháng</small>
                        <div class="customer-summary-value text-primary"><?php echo e($stats['new_this_month'] ?? 0); ?></div>
                    </div>
                </div>
            </div>

            <!-- Suggestion Cards -->
            <div class="row g-3 mb-4">
                <!-- Đánh giá tiêu cực -->
                <div class="col-md-6">
                    <div class="card border-warning">
                        <div class="card-body">
                            <h6 class="card-title mb-3">Đánh giá tiêu cực</h6>
                            <div class="row g-2 mb-3">
                                <div class="col-md-6">
                                    <div class="bg-light p-2 rounded text-center">
                                        <div class="text-muted small">Đánh giá bị từ chối</div>
                                        <h5 class="mb-0" id="statTotalRejected">-</h5>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="bg-light p-2 rounded text-center">
                                        <div class="text-muted small">Khách cần xem xét</div>
                                        <h5 class="mb-0" id="statCustomersFlagged">-</h5>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-warning btn-sm w-100"
                                onclick="openSuggestLockNegativeReviewsModal()">Xem danh sách</button>
                        </div>
                    </div>
                </div>

                <!-- Yêu cầu hoàn trả -->
                <div class="col-md-6">
                    <div class="card border-warning">
                        <div class="card-body">
                            <h6 class="card-title mb-3">Yêu cầu hoàn trả</h6>
                            <div class="row g-2 mb-3">
                                <div class="col-md-6">
                                    <div class="bg-light p-2 rounded text-center">
                                        <div class="text-muted small">Hoàn trả tháng này</div>
                                        <h5 class="mb-0" id="refundStatTotal">-</h5>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="bg-light p-2 rounded text-center">
                                        <div class="text-muted small">Khách cần xem xét</div>
                                        <h5 class="mb-0" id="refundStatCustomers">-</h5>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-warning btn-sm w-100"
                                onclick="openSuggestLockRefundRequestsModal()">Xem danh sách</button>
                        </div>
                    </div>
                </div>
            </div>

            <form method="GET" action="<?php echo e(route('admin.customers.list')); ?>"
                class="row g-3 mb-4 border rounded bg-white p-3">
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-lg-4">
                        <label class="form-label">Tìm theo tên</label>
                        <input type="text" name="keyword" value="<?php echo e(request('keyword')); ?>" class="form-control"
                            placeholder="Nhập tên khách hàng...">
                    </div>

                    <div class="col-12 col-sm-6 col-lg-3">
                        <label class="form-label">Tài khoản</label>
                        <select name="status" class="form-select">
                            <option value="">-- Tất cả --</option>

                            <option value="active" <?php echo e(request('status') == 'active' ? 'selected' : ''); ?>>
                                Hoạt động
                            </option>

                            <option value="locked" <?php echo e(request('status') == 'locked' ? 'selected' : ''); ?>>
                                Bị khóa
                            </option>
                        </select>
                    </div>

                    <div class="col-12 col-sm-6 col-lg-2">
                        <label class="form-label">Từ ngày</label>
                        <input type="date" name="date_from" value="<?php echo e(request('date_from')); ?>" class="form-control">
                    </div>

                    <div class="col-12 col-sm-6 col-lg-2">
                        <label class="form-label">Đến ngày</label>
                        <input type="date" name="date_to" value="<?php echo e(request('date_to')); ?>" class="form-control">
                    </div>

                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Lọc</button>
                        <a href="<?php echo e(route('admin.customers.list')); ?>" class="btn btn-secondary">Đặt lại</a>
                    </div>
                </div>
            </form>

            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div>
                    <h6 class="mb-1">Bảng khách hàng</h6>
                    <small class="text-muted">Trang <?php echo e($customers->currentPage()); ?>/<?php echo e($customers->lastPage()); ?>. Có thể
                        khóa nhanh tài khoản ngay trong danh sách.</small>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">

                    <thead class="table-light">
                        <tr>
                            <th>STT</th>
                            <th>Tên</th>
                            <th>Email</th>
                            <th>SĐT</th>
                            <th>Số đơn hàng</th>
                            <th>Ngày đăng ký</th>
                            <th>Trạng thái</th>
                            <th width="250">Thao tác</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php $__empty_1 = true; $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                            <tr>

                                <td>
                                    <?php echo e($customers->firstItem() + $index); ?>

                                </td>

                                <td>
                                    <div class="customer-name"><?php echo e($customer->user->name ?? '-'); ?></div>
                                    <div class="customer-subtext">Mã KH: #<?php echo e($customer->id); ?></div>
                                </td>

                                <td>
                                    <div><?php echo e($customer->user->email ?? '-'); ?></div>
                                    <?php if(!empty($customer->user?->email_verified_at)): ?>
                                        <div class="customer-subtext">Đã xác thực email</div>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <?php echo e($customer->phone ?? '-'); ?>

                                </td>

                                <td>
                                    <span class="badge bg-info"><?php echo e($customer->orders_count ?? 0); ?> đơn</span>
                                </td>

                                <td>
                                    <div><?php echo e($customer->created_at->format('d/m/Y')); ?></div>
                                    <div class="customer-subtext"><?php echo e($customer->created_at->format('H:i')); ?></div>
                                </td>

                                <td>

                                    <?php if($customer->user?->status === 'active'): ?>

                                        <span class="badge bg-success">
                                            Hoạt động
                                        </span>

                                    <?php else: ?>

                                        <span class="badge bg-danger">
                                            Bị khóa
                                        </span>

                                        <?php if(!empty($customer->user?->locked_reason)): ?>
                                            <div class="lock-reason" title="<?php echo e($customer->user->locked_reason); ?>">
                                                Lý do: <?php echo e($customer->user->locked_reason); ?>

                                            </div>
                                        <?php endif; ?>

                                        <?php if(!empty($customer->user?->locked_at)): ?>
                                            <div class="lock-reason">
                                                Khóa lúc:
                                                <?php echo e(\Illuminate\Support\Carbon::parse($customer->user->locked_at)->format('d/m/Y H:i')); ?>

                                            </div>
                                        <?php endif; ?>

                                    <?php endif; ?>

                                </td>

                                <td>
                                    <div class="customer-action-group">
                                        <button type="button" class="btn btn-sm btn-primary customer-action-btn"
                                            data-bs-toggle="modal" data-bs-target="#customerDetailModal-<?php echo e($customer->id); ?>">
                                            Xem
                                        </button>

                                        <?php if($customer->user?->status === 'active'): ?>
                                            <button type="button" class="btn btn-sm btn-danger customer-action-btn"
                                                data-bs-toggle="modal" data-bs-target="#lockModal-<?php echo e($customer->id); ?>">
                                                Khóa
                                            </button>
                                        <?php else: ?>
                                            <form method="POST" action="<?php echo e(route('admin.customers.unlock', $customer->user_id)); ?>"
                                                onsubmit="return confirm('Mở khóa khách hàng này?')">
                                                <?php echo csrf_field(); ?>
                                                <button class="btn btn-sm btn-success customer-action-btn">
                                                    Mở
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <form method="POST" action="<?php echo e(route('admin.customers.destroy', $customer->id)); ?>"
                                            onsubmit="return confirm('Xóa khách hàng này?')">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button class="btn btn-sm btn-danger customer-action-btn">
                                                Xóa
                                            </button>
                                        </form>
                                    </div>

                                </td>

                            </tr>

                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                            <tr>
                                <td colspan="8" class="text-center">
                                    Chưa có khách hàng nào
                                </td>
                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

                <?php $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="modal fade" id="customerDetailModal-<?php echo e($customer->id); ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Chi tiết khách hàng</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="customer-detail-header">
                                        <img src="<?php echo e($customer->user?->avatar ? asset('storage/' . $customer->user->avatar) : asset('img/user.jpg')); ?>"
                                            alt="<?php echo e($customer->user?->name ?? 'Khách hàng'); ?>" class="customer-avatar">
                                        <div>
                                            <h5 class="mb-1"><?php echo e($customer->user?->name ?? 'Không có tên'); ?></h5>
                                            <div class="text-muted small mb-2">Mã khách hàng: #<?php echo e($customer->id); ?></div>
                                            <?php if($customer->user?->status === 'active'): ?>
                                                <span class="badge bg-success">Hoạt động</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Bị khóa</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-12 col-md-6">
                                            <div class="customer-detail-card">
                                                <span class="customer-detail-label">Email</span>
                                                <div class="customer-detail-value"><?php echo e($customer->user?->email ?? '-'); ?></div>
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <div class="customer-detail-card">
                                                <span class="customer-detail-label">Số điện thoại</span>
                                                <div class="customer-detail-value"><?php echo e($customer->phone ?? '-'); ?></div>
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <div class="customer-detail-card">
                                                <span class="customer-detail-label">Ngày sinh</span>
                                                <div class="customer-detail-value">
                                                    <?php echo e($customer->date_of_birth ? \Illuminate\Support\Carbon::parse($customer->date_of_birth)->format('d/m/Y') : '-'); ?>

                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <div class="customer-detail-card">
                                                <span class="customer-detail-label">Giới tính</span>
                                                <div class="customer-detail-value">
                                                    <?php if($customer->gender === 'male'): ?>
                                                        Nam
                                                    <?php elseif($customer->gender === 'female'): ?>
                                                        Nữ
                                                    <?php elseif($customer->gender === 'other'): ?>
                                                        Khác
                                                    <?php else: ?>
                                                        -
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <div class="customer-detail-card">
                                                <span class="customer-detail-label">Số đơn hàng</span>
                                                <div class="customer-detail-value"><?php echo e($customer->orders_count ?? 0); ?> đơn</div>
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <div class="customer-detail-card">
                                                <span class="customer-detail-label">Email xác thực</span>
                                                <div class="customer-detail-value">
                                                    <?php echo e($customer->user?->email_verified_at ? $customer->user->email_verified_at->format('d/m/Y H:i') : 'Chưa xác thực'); ?>

                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="customer-detail-card">
                                                <span class="customer-detail-label">Địa chỉ mặc định</span>
                                                <div class="customer-detail-value"><?php echo e($customer->full_address ?: '-'); ?></div>
                                                <div class="customer-subtext mt-2">
                                                    <?php echo e($customer->is_default_address ? 'Đang đặt làm địa chỉ mặc định' : 'Chưa đặt làm địa chỉ mặc định'); ?>

                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <div class="customer-detail-card">
                                                <span class="customer-detail-label">Ngày đăng ký</span>
                                                <div class="customer-detail-value">
                                                    <?php echo e($customer->created_at->format('d/m/Y H:i')); ?>

                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-6">
                                            <div class="customer-detail-card">
                                                <span class="customer-detail-label">Cập nhật gần nhất</span>
                                                <div class="customer-detail-value">
                                                    <?php echo e($customer->updated_at->format('d/m/Y H:i')); ?>

                                                </div>
                                            </div>
                                        </div>

                                        <?php if(!empty($customer->user?->locked_reason) || !empty($customer->user?->locked_at)): ?>
                                            <div class="col-12">
                                                <div class="customer-detail-card">
                                                    <span class="customer-detail-label">Thông tin khóa tài khoản</span>
                                                    <div class="customer-detail-value">
                                                        <?php echo e($customer->user?->locked_reason ?? 'Không có lý do'); ?>

                                                    </div>
                                                    <?php if(!empty($customer->user?->locked_at)): ?>
                                                        <div class="customer-subtext mt-2">
                                                            Khóa lúc:
                                                            <?php echo e(\Illuminate\Support\Carbon::parse($customer->user->locked_at)->format('d/m/Y H:i')); ?>

                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if($customer->user && $customer->user->status === 'active'): ?>
                        <div class="modal fade" id="lockModal-<?php echo e($customer->id); ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="POST" action="<?php echo e(route('admin.customers.lock', $customer->user_id)); ?>">
                                        <?php echo csrf_field(); ?>
                                        <div class="modal-header">
                                            <h5 class="modal-title">Khóa tài khoản: <?php echo e($customer->user->name); ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">Lý do khóa</label>
                                                <select name="reason_key" class="form-select" required>
                                                    <?php $__currentLoopData = ($lockReasonPresets ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reasonKey => $reasonLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($reasonKey); ?>"><?php echo e($reasonLabel); ?></option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </select>
                                            </div>

                                            <div>
                                                <label class="form-label">Ghi chú thêm (tuỳ chọn)</label>
                                                <input type="text" name="reason_note" class="form-control" maxlength="255"
                                                    placeholder="Ví dụ: phát hiện 3 đơn bất thường trong ngày">
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                                            <button type="submit" class="btn btn-danger">Xác nhận khóa</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>


            
            <div class="d-flex justify-content-center mt-3 small">

                <?php echo e($customers->onEachSide(1)
        ->appends(request()->query())
        ->links('pagination::bootstrap-5')); ?>


            </div>

        </div>

    </div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        const csrfToken = '<?php echo e(csrf_token()); ?>';

        // ===== MODAL: Đề xuất khóa vì đánh giá tiêu cực =====
        function loadNegativeReviewsData(page = 1) {
            const loading = document.getElementById('negReviewLoading');
            const list = document.getElementById('negReviewList');
            const empty = document.getElementById('negReviewEmpty');

            loading.style.display = 'block';
            list.style.display = 'none';
            empty.style.display = 'none';

            fetch(`<?php echo e(route('admin.api.suggest-lock-negative-reviewers')); ?>?page=${page}`)
                .then(r => r.json())
                .then(data => {
                    loading.style.display = 'none';
                    document.getElementById('statTotalRejected').textContent = data.stats.total_rejected_this_month || 0;
                    document.getElementById('statCustomersFlagged').textContent = data.stats.customers_flagged || 0;

                    if (data.suggestedCustomers.length === 0) {
                        empty.style.display = 'block';
                        return;
                    }

                    list.style.display = 'block';
                    renderNegativeReviewsList(data.suggestedCustomers);
                    renderNegativeReviewsPagination(data.pagination, 'negReview');
                })
                .catch(err => {
                    loading.style.display = 'none';
                    document.getElementById('negReviewCustomersList').innerHTML = '<div class="alert alert-danger">Lỗi tải dữ liệu</div>';
                    list.style.display = 'block';
                });
        }

        function renderNegativeReviewsList(customers) {
            const container = document.getElementById('negReviewCustomersList');
            container.innerHTML = '';

            customers.forEach(item => {
                const customerName = item?.customer?.user?.name || item?.customer?.name || 'Khách hàng';
                const customerEmail = item?.customer?.user?.email || '-';
                const userId = item?.customer?.user?.id || item?.customer?.user_id || '';
                const html = `
                                                            <div class="list-group-item py-3 px-3 border-bottom">
                                                                <div class="d-flex justify-content-between align-items-start gap-2">
                                                                    <div class="flex-grow-1">
                                                                        <div class="fw-bold">${escapeHtml(customerName)}</div>
                                                                        <small class="text-muted">${escapeHtml(customerEmail)}</small>
                                                                        <div class="mt-2">
                                                                            <span class="badge bg-danger">${item.rejected_count} xem xét</span>
                                                                            <span class="badge bg-secondary">${item.customer.orders_count || 0} đơn</span>
                                                                        </div>
                                                                    </div>
                                                                    <div class="d-flex gap-2">
                                                                        <button class="btn btn-sm btn-warning" onclick="selectLockReason(${userId}, '${escapeHtml(customerName)}')">Khóa</button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        `;
                container.innerHTML += html;
            });
        }

        // ===== MODAL: Đề xuất khóa vì hoàn trả =====
        function loadRefundRequestsData(page = 1) {
            const loading = document.getElementById('refundLoading');
            const list = document.getElementById('refundList');
            const empty = document.getElementById('refundEmpty');

            loading.style.display = 'block';
            list.style.display = 'none';
            empty.style.display = 'none';

            fetch(`<?php echo e(route('admin.api.suggest-lock-refund-requests')); ?>?page=${page}`)
                .then(r => r.json())
                .then(data => {
                    loading.style.display = 'none';
                    document.getElementById('refundStatTotal').textContent = data.stats.total_refunds_this_month || 0;
                    document.getElementById('refundStatCustomers').textContent = data.stats.customers_flagged || 0;

                    if (data.suggestedCustomers.length === 0) {
                        empty.style.display = 'block';
                        return;
                    }

                    list.style.display = 'block';
                    renderRefundRequestsList(data.suggestedCustomers);
                    renderRefundRequestsPagination(data.pagination, 'refund');
                })
                .catch(err => {
                    loading.style.display = 'none';
                    document.getElementById('refundCustomersList').innerHTML = '<div class="alert alert-danger">Lỗi tải dữ liệu</div>';
                    list.style.display = 'block';
                });
        }

        function renderRefundRequestsList(customers) {
            const container = document.getElementById('refundCustomersList');
            container.innerHTML = '';

            customers.forEach(item => {
                const customerName = item?.customer?.user?.name || item?.customer?.name || 'Khách hàng';
                const customerEmail = item?.customer?.user?.email || '-';
                const userId = item?.customer?.user?.id || item?.customer?.user_id || '';
                const html = `
                                                            <div class="list-group-item py-3 px-3 border-bottom">
                                                                <div class="d-flex justify-content-between align-items-start gap-2">
                                                                    <div class="flex-grow-1">
                                                                        <div class="fw-bold">${escapeHtml(customerName)}</div>
                                                                        <small class="text-muted">${escapeHtml(customerEmail)}</small>
                                                                        <div class="mt-2">
                                                                            <span class="badge bg-danger">${item.refund_count} hoàn trả</span>
                                                                            <span class="badge bg-secondary">${item.customer.orders_count || 0} đơn</span>
                                                                        </div>
                                                                    </div>
                                                                    <div class="d-flex gap-2">
                                                                        <button class="btn btn-sm btn-warning" onclick="selectLockReason(${userId}, '${escapeHtml(customerName)}')">Khóa</button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        `;
                container.innerHTML += html;
            });
        }

        function renderNegativeReviewsPagination(pagination, prefix) {
            const container = document.getElementById(`${prefix}Pagination`);
            if (pagination.total <= pagination.per_page) {
                container.style.display = 'none';
                return;
            }

            let html = '';
            if (pagination.current_page > 1) {
                html += `<li class="page-item"><a class="page-link" href="#" onclick="loadNegativeReviewsData(${pagination.current_page - 1}); return false;">Trước</a></li>`;
            } else {
                html += `<li class="page-item disabled"><span class="page-link">Trước</span></li>`;
            }

            for (let i = 1; i <= pagination.last_page; i++) {
                if (i === pagination.current_page) {
                    html += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
                } else {
                    html += `<li class="page-item"><a class="page-link" href="#" onclick="loadNegativeReviewsData(${i}); return false;">${i}</a></li>`;
                }
            }

            if (pagination.current_page < pagination.last_page) {
                html += `<li class="page-item"><a class="page-link" href="#" onclick="loadNegativeReviewsData(${pagination.current_page + 1}); return false;">Sau</a></li>`;
            } else {
                html += `<li class="page-item disabled"><span class="page-link">Sau</span></li>`;
            }

            container.querySelector('ul').innerHTML = html;
            container.style.display = 'block';
        }

        function renderRefundRequestsPagination(pagination, prefix) {
            const container = document.getElementById(`${prefix}Pagination`);
            if (pagination.total <= pagination.per_page) {
                container.style.display = 'none';
                return;
            }

            let html = '';
            if (pagination.current_page > 1) {
                html += `<li class="page-item"><a class="page-link" href="#" onclick="loadRefundRequestsData(${pagination.current_page - 1}); return false;">Trước</a></li>`;
            } else {
                html += `<li class="page-item disabled"><span class="page-link">Trước</span></li>`;
            }

            for (let i = 1; i <= pagination.last_page; i++) {
                if (i === pagination.current_page) {
                    html += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
                } else {
                    html += `<li class="page-item"><a class="page-link" href="#" onclick="loadRefundRequestsData(${i}); return false;">${i}</a></li>`;
                }
            }

            if (pagination.current_page < pagination.last_page) {
                html += `<li class="page-item"><a class="page-link" href="#" onclick="loadRefundRequestsData(${pagination.current_page + 1}); return false;">Sau</a></li>`;
            } else {
                html += `<li class="page-item disabled"><span class="page-link">Sau</span></li>`;
            }

            container.querySelector('ul').innerHTML = html;
            container.style.display = 'block';
        }

        let currentLockCustomerId = null;
        let currentLockReason = null;

        function selectLockReason(userId, userName) {
            currentLockCustomerId = userId;
            document.getElementById('lockCustomerName').textContent = userName;
            document.getElementById('lockReasonSelect').value = '';
            document.getElementById('lockNote').value = '';
            const modal = new bootstrap.Modal(document.getElementById('lockConfirmModal'));
            modal.show();
        }

        function submitLock() {
            const reason = document.getElementById('lockReasonSelect').value;
            if (!currentLockCustomerId || !reason) {
                alert('Vui lòng chọn lý do khóa');
                return;
            }
            currentLockReason = reason;

            const note = document.getElementById('lockNote').value || '';

            fetch(`<?php echo e(route('admin.customers.lock', ':id')); ?>`.replace(':id', currentLockCustomerId), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify({
                    reason_key: currentLockReason,
                    reason_note: note,
                })
            })
                .then(async r => {
                    const contentType = r.headers.get('content-type') || '';
                    const isJson = contentType.includes('application/json');
                    const data = isJson ? await r.json() : { message: 'Phiên đăng nhập đã hết hạn hoặc phản hồi không hợp lệ.' };
                    if (!r.ok) {
                        throw {
                            status: r.status,
                            statusText: r.statusText,
                            message: data.message || data.errors || 'Unknown error'
                        };
                    }
                    if (!isJson) {
                        throw {
                            status: r.status,
                            statusText: r.statusText,
                            message: data.message
                        };
                    }
                    return data;
                })
                .then(data => {
                    try {
                        const modalInst = bootstrap.Modal.getInstance(document.getElementById('lockConfirmModal'));
                        if (modalInst) modalInst.hide();
                    } catch (e) {
                        console.warn('Modal hide error:', e);
                    }
                    alert('Đã khóa tài khoản');
                    loadNegativeReviewsData(1);
                    loadRefundRequestsData(1);
                    document.getElementById('lockNote').value = '';
                })
                .catch(err => {
                    let message = 'Lỗi: Không thể khóa tài khoản';

                    if (err.message) {
                        message = err.message;
                    } else if (err.status) {
                        message = `Lỗi ${err.status}: ${err.statusText || 'Unknown'}`;
                    } else if (typeof err === 'string') {
                        message = err;
                    }

                    alert(message);
                    console.error('Lock error:', err);
                });
        }

        function escapeHtml(text) {
            const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
            return (text || '').replace(/[&<>"']/g, m => map[m]);
        }

        function openSuggestLockNegativeReviewsModal() {
            const modal = new bootstrap.Modal(document.getElementById('negReviewModal'));
            modal.show();
            loadNegativeReviewsData(1);
        }

        function openSuggestLockRefundRequestsModal() {
            const modal = new bootstrap.Modal(document.getElementById('refundModal'));
            modal.show();
            loadRefundRequestsData(1);
        }

        // Khóa dropdown select event
        document.addEventListener('change', function (e) {
            if (e.target && e.target.id === 'lockReasonSelect') {
                const selectedOption = e.target.options[e.target.selectedIndex];
                document.getElementById('lockReason').textContent = selectedOption.text;
            }
        });

        // Load stats when page loads
        document.addEventListener('DOMContentLoaded', function () {
            loadNegativeReviewsData(1);
            loadRefundRequestsData(1);
        });
    </script>


    <!-- Modal: Đánh giá tiêu cực -->
    <div class="modal fade" id="negReviewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title">Đánh giá tiêu cực</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <div class="bg-light p-2 rounded text-center">
                                <div class="text-muted small">Đánh giá bị từ chối</div>
                                <h5 class="mb-0" id="statTotalRejected">0</h5>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="bg-light p-2 rounded text-center">
                                <div class="text-muted small">Khách cần xem xét</div>
                                <h5 class="mb-0" id="statCustomersFlagged">0</h5>
                            </div>
                        </div>
                    </div>

                    <div id="negReviewLoading" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Đang tải...</span>
                        </div>
                    </div>

                    <div id="negReviewList" style="display: none;">
                        <div class="list-group list-group-flush" id="negReviewCustomersList"></div>
                        <nav id="negReviewPagination" class="mt-3" style="display: none;">
                            <ul class="pagination pagination-sm justify-content-center"></ul>
                        </nav>
                    </div>

                    <div id="negReviewEmpty" style="display: none;" class="text-center py-4">
                        <div style="font-size: 2rem; color: #28a745; margin-bottom: 10px;">✓</div>
                        <h6>Không có khách hàng cần xem xét</h6>
                        <small class="text-muted">Tất cả khách hàng đều bình thường.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Yêu cầu hoàn trả -->
    <div class="modal fade" id="refundModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title">Yêu cầu hoàn trả</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <div class="bg-light p-2 rounded text-center">
                                <div class="text-muted small">Hoàn trả tháng này</div>
                                <h5 class="mb-0" id="refundStatTotal">0</h5>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="bg-light p-2 rounded text-center">
                                <div class="text-muted small">Khách cần xem xét</div>
                                <h5 class="mb-0" id="refundStatCustomers">0</h5>
                            </div>
                        </div>
                    </div>

                    <div id="refundLoading" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Đang tải...</span>
                        </div>
                    </div>

                    <div id="refundList" style="display: none;">
                        <div class="list-group list-group-flush" id="refundCustomersList"></div>
                        <nav id="refundPagination" class="mt-3" style="display: none;">
                            <ul class="pagination pagination-sm justify-content-center"></ul>
                        </nav>
                    </div>

                    <div id="refundEmpty" style="display: none;" class="text-center py-4">
                        <div style="font-size: 2rem; color: #28a745; margin-bottom: 10px;">✓</div>
                        <h6>Không có khách hàng cần xem xét</h6>
                        <small class="text-muted">Tất cả khách hàng đều bình thường.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Lock Confirmation -->
    <div class="modal fade" id="lockConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Xác nhận khóa tài khoản</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Khóa tài khoản: <strong id="lockCustomerName"></strong></p>
                    <div class="mb-3">
                        <label class="form-label">Lý do khóa:</label>
                        <select id="lockReasonSelect" class="form-select">
                            <option value="">-- Chọn lý do --</option>
                            <option value="negative_reviews">Quá nhiều đánh giá tiêu cực</option>
                            <option value="spam">Spam/lạm dụng hệ thống</option>
                            <option value="fraud">Gian lận</option>
                            <option value="refund_abuse">Lạm dụng hoàn tiền</option>
                            <option value="other">Lý do khác</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ghi chú (tùy chọn):</label>
                        <textarea id="lockNote" class="form-control" rows="2" placeholder="..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-danger" onclick="submitLock()">Khóa ngay</button>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('admin.layouts.layout_admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\luanvan\resources\views/admin/customers/list.blade.php ENDPATH**/ ?>