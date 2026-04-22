

<?php $__env->startSection('content'); ?>
    <div class="container-fluid pt-4 px-2">
        <div class="bg-light rounded p-4">

            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div>
                    <h5 class="mb-1">Danh sách đánh giá</h5>
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
                            <button type="button" class="btn btn-warning btn-sm w-100" onclick="openSuggestLockModal()">Xem
                                danh sách</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-12 col-sm-3">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Tổng đánh giá</small>
                        <h4 class="mb-0"><?php echo e($summary['total'] ?? 0); ?></h4>
                    </div>
                </div>
                <div class="col-12 col-sm-3">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Chờ duyệt</small>
                        <h4 class="mb-0 text-warning"><?php echo e($summary['pending'] ?? 0); ?></h4>
                    </div>
                </div>
                <div class="col-12 col-sm-3">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Đã duyệt</small>
                        <h4 class="mb-0 text-success"><?php echo e($summary['approved'] ?? 0); ?></h4>
                    </div>
                </div>
                <div class="col-12 col-sm-3">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Từ chối</small>
                        <h4 class="mb-0 text-danger"><?php echo e($summary['rejected'] ?? 0); ?></h4>
                    </div>
                </div>
            </div>

            <form method="GET" action="<?php echo e(route('admin.reviews')); ?>" class="row g-3 mb-4 border rounded bg-white p-3">
                <div class="col-md-5">
                    <label class="form-label">Từ khóa</label>
                    <input type="text" name="keyword" class="form-control" value="<?php echo e(request('keyword')); ?>"
                        placeholder="Sản phẩm, khách hàng, nội dung...">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="">-- Tất cả --</option>
                        <option value="pending" <?php echo e(request('status') === 'pending' ? 'selected' : ''); ?>>Chờ duyệt</option>
                        <option value="approved" <?php echo e(request('status') === 'approved' ? 'selected' : ''); ?>>Đã duyệt</option>
                        <option value="rejected" <?php echo e(request('status') === 'rejected' ? 'selected' : ''); ?>>Từ chối</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Số sao</label>
                    <select name="rating" class="form-select">
                        <option value="">-- Tất cả --</option>
                        <?php for($i = 5; $i >= 1; $i--): ?>
                            <option value="<?php echo e($i); ?>" <?php echo e((string) request('rating') === (string) $i ? 'selected' : ''); ?>><?php echo e($i); ?>

                                sao</option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">Lọc</button>
                    <a href="<?php echo e(route('admin.reviews')); ?>" class="btn btn-secondary">Đặt lại</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="60">STT</th>
                            <th width="90">ID</th>
                            <th>Sản phẩm</th>
                            <th>Khách</th>
                            <th width="80">Sao</th>
                            <th width="100">Trạng thái</th>
                            <th>Nội dung</th>
                            <th width="140">Ngày</th>
                            <th width="150">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $reviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($reviews->firstItem() + $index); ?></td>
                                <td>#<?php echo e($review->id); ?></td>
                                <td><?php echo e($review->product?->name); ?></td>
                                <td><?php echo e($review->customer?->user->name ?? $review->customer?->name ?? $review->customer_id); ?>

                                </td>
                                <td><?php echo e($review->rating); ?></td>
                                <td>
                                    <?php if($review->status === 'pending'): ?>
                                        <span class="badge bg-warning text-dark">Chờ duyệt</span>
                                    <?php elseif($review->status === 'approved'): ?>
                                        <span class="badge bg-success">Đã duyệt</span>
                                    <?php elseif($review->status === 'rejected'): ?>
                                        <span class="badge bg-danger">Bị từ chối</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo e(Str::limit($review->content, 80)); ?></td>
                                <td><?php echo e($review->created_at->format('d/m/Y H:i')); ?></td>
                                <td>
                                    <div class="btn-group" role="group" aria-label="Actions">
                                        <?php if($review->status === 'pending'): ?>
                                            <form action="<?php echo e(route('admin.reviews.approve', $review)); ?>" method="POST"
                                                style="display:inline-block">
                                                <?php echo csrf_field(); ?>
                                                <button class="btn btn-sm btn-success">Duyệt</button>
                                            </form>
                                            <form action="<?php echo e(route('admin.reviews.reject', $review)); ?>" method="POST"
                                                style="display:inline-block">
                                                <?php echo csrf_field(); ?>
                                                <button class="btn btn-sm btn-danger">Từ chối</button>
                                            </form>
                                        <?php elseif($review->status === 'approved'): ?>
                                            <button type="button" class="btn btn-sm btn-success" disabled>Đã duyệt</button>
                                        <?php elseif($review->status === 'rejected'): ?>
                                            <form action="<?php echo e(route('admin.reviews.approve', $review)); ?>" method="POST"
                                                style="display:inline-block">
                                                <?php echo csrf_field(); ?>
                                                <button class="btn btn-sm btn-success">Duyệt lại</button>
                                            </form>
                                            <button type="button" class="btn btn-sm btn-danger" disabled>Đã từ chối</button>
                                        <?php endif; ?>

                                        <form action="<?php echo e(route('admin.reviews.reply', $review)); ?>" method="POST"
                                            style="display:inline-block; margin-left:4px;" class="d-none reply-fallback-form">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="content" value="Cảm ơn bạn đã đánh giá">
                                            <button class="btn btn-sm btn-primary">Trả lời</button>
                                        </form>

                                        <button type="button" class="btn btn-sm btn-info ms-1 btn-open-replies"
                                            data-review-id="<?php echo e($review->id); ?>"
                                            data-review-author="<?php echo e($review->customer?->user->name ?? $review->customer?->name ?? 'Khách'); ?>"
                                            data-review-content="<?php echo e(e($review->content)); ?>"
                                            data-review-created="<?php echo e($review->created_at->format('d/m/Y H:i')); ?>"
                                            data-bs-toggle="modal" data-bs-target="#repliesModal">Chi tiết</button>

                                        <form action="<?php echo e(route('admin.reviews.destroy', $review)); ?>" method="POST"
                                            style="display:inline-block; margin-left:4px;">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('Bạn có chắc muốn xóa đánh giá này?')">Xóa</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted">Chưa có đánh giá</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                
                <?php echo e($reviews->appends(request()->query())->links()); ?>

            </div>

        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        const csrfToken = '<?php echo e(csrf_token()); ?>';

        // Create a single shared modal for replies
        const repliesModal = document.createElement('div');
        repliesModal.innerHTML = `
                                                                                                                <div class="modal fade" id="repliesModal" tabindex="-1" aria-hidden="true">
                                                                                                                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                                                                                                        <div class="modal-content">
                                                                                                                            <div class="modal-header">
                                                                                                                                <h5 class="modal-title" id="repliesModalLabel">Chi tiết trả lời</h5>
                                                                                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                                                                            </div>
                                                                                                                            <div class="modal-body">
                                                                                                                                <h6>Đánh giá gốc</h6>
                                                                                                                                <p><strong id="origAuthor">-</strong> — <span id="origCreated">-</span></p>
                                                                                                                                <p id="origContent">-</p>
                                                                                                                                <hr>
                                                                                                                                <h6>Phản hồi</h6>
                                                                                                                                <div id="repliesList">
                                                                                                                                    <p class="text-muted">Đang tải...</p>
                                                                                                                                </div>
                                                                                                                                <hr>
                                                                                                                                <h6>Trả lời mới</h6>
                                                                                                                                <div>
                                                                                                                                <textarea id="replyContent" class="form-control" rows="3" placeholder="Nội dung trả lời"></textarea>
                                                                                                                                <div class="text-end mt-2">
                                                                                                                                    <button id="replySend" class="btn btn-primary btn-sm">Gửi</button>
                                                                                                                                    <button id="replyDefault" class="btn btn-secondary btn-sm ms-2">Gửi mặc định</button>
                                                                                                                                </div>
                                                                                                                            </div>
                                                                                                                            </div>
                                                                                                                            <div class="modal-footer">
                                                                                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                                                                                                            </div>
                                                                                                                        </div>
                                                                                                                    </div>
                                                                                                                </div>`;

        document.body.appendChild(repliesModal);

        let activeReviewId = null;

        document.addEventListener('click', function (e) {
            const btn = e.target.closest('.btn-open-replies');
            if (!btn) return;
            activeReviewId = btn.getAttribute('data-review-id');
            document.getElementById('origAuthor').textContent = btn.getAttribute('data-review-author');
            document.getElementById('origContent').textContent = btn.getAttribute('data-review-content');
            document.getElementById('origCreated').textContent = btn.getAttribute('data-review-created');
            loadReplies(activeReviewId);
        });

        function loadReplies(reviewId) {
            const list = document.getElementById('repliesList');
            list.innerHTML = '<p class="text-muted">Đang tải...</p>';
            fetch(`/admin/reviews/${reviewId}/replies`, {
                headers: { 'Accept': 'application/json' }
            })
                .then(r => r.json())
                .then(data => {
                    if (!data.replies || data.replies.length === 0) {
                        list.innerHTML = '<p class="text-muted">Chưa có phản hồi nào.</p>';
                        return;
                    }
                    const html = data.replies.map(r => `
                                                                                                                        <div class="mb-3">
                                                                                                                            <div><strong>${escapeHtml(r.author_name)}</strong> <span class="text-muted small">${r.created_at}</span></div>
                                                                                                                            <div class="mt-1">${escapeHtml(r.content)}</div>
                                                                                                                        </div>
                                                                                                                    `).join('');
                    list.innerHTML = html;
                }).catch(err => {
                    list.innerHTML = '<p class="text-danger">Không thể tải phản hồi.</p>';
                });
        }

        document.body.addEventListener('click', function (e) {
            if (e.target && (e.target.id === 'replySend' || e.target.id === 'replyDefault')) {
                const isDefault = e.target.id === 'replyDefault';
                const content = isDefault ? 'Cảm ơn bạn đã đánh giá' : document.getElementById('replyContent').value.trim();
                if (!content) {
                    if (typeof window.adminNotify === 'function') {
                        window.adminNotify('warning', 'Vui lòng nhập nội dung trả lời.');
                    }
                    return;
                }
                fetch(`/admin/reviews/${activeReviewId}/reply`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ content })
                }).then(r => {
                    if (!r.ok) throw r;
                    return r.json();
                }).then(() => {
                    document.getElementById('replyContent').value = '';
                    loadReplies(activeReviewId);
                }).catch(async err => {
                    let msg = 'Lỗi khi gửi phản hồi.';
                    try { const json = await err.json(); if (json.message) msg = json.message; } catch (_) { }
                    if (typeof window.adminNotify === 'function') {
                        window.adminNotify('error', msg);
                    }
                });
            }
        });

        function escapeHtml(unsafe) {
            return (unsafe || '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }
    </script>

    <!-- Modal Popup: Đề xuất khóa khách hàng -->
    <div class="modal fade" id="suggestLockModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title">Đề xuất khóa tài khoản</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <div class="bg-light p-2 rounded text-center">
                                <div class="text-muted small">Bình luận tiêu cực</div>
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

                    <div id="suggestLockLoading" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Đang tải...</span>
                        </div>
                    </div>

                    <div id="suggestLockList" style="display: none;">
                        <div class="list-group list-group-flush" id="customersList"></div>
                        <nav id="suggestLockPagination" class="mt-3" style="display: none;">
                            <ul class="pagination pagination-sm justify-content-center"></ul>
                        </nav>
                    </div>

                    <div id="suggestLockEmpty" style="display: none;" class="text-center py-4">
                        <div style="font-size: 2rem; color: #28a745; margin-bottom: 10px;">✓</div>
                        <h6>Không có khách hàng cần xem xét</h6>
                        <small class="text-muted">Tất cả khách hàng đều có số lượng bình luận tiêu cực bình thường.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Lock Confirmation -->
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

    <script>
        let currentLockCustomerId = null;
        let currentLockReason = null;

        function loadSuggestLockData(page = 1) {
            const loading = document.getElementById('suggestLockLoading');
            const list = document.getElementById('suggestLockList');
            const empty = document.getElementById('suggestLockEmpty');

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
                    renderCustomersList(data.suggestedCustomers);
                    renderPagination(data.pagination);
                })
                .catch(err => {
                    loading.style.display = 'none';
                    document.getElementById('customersList').innerHTML = '<div class="alert alert-danger">Lỗi tải dữ liệu</div>';
                    list.style.display = 'block';
                });
        }

        function renderCustomersList(customers) {
            const container = document.getElementById('customersList');
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
                                                                                            <span class="badge bg-danger">${item.rejected_count} review</span>
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

        function renderPagination(pagination) {
            const container = document.getElementById('suggestLockPagination');
            if (pagination.total <= pagination.per_page) {
                container.style.display = 'none';
                return;
            }

            let html = '';
            if (pagination.current_page > 1) {
                html += `<li class="page-item"><a class="page-link" href="#" onclick="loadSuggestLockData(${pagination.current_page - 1}); return false;">Trước</a></li>`;
            } else {
                html += `<li class="page-item disabled"><span class="page-link">Trước</span></li>`;
            }

            for (let i = 1; i <= pagination.last_page; i++) {
                if (i === pagination.current_page) {
                    html += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
                } else {
                    html += `<li class="page-item"><a class="page-link" href="#" onclick="loadSuggestLockData(${i}); return false;">${i}</a></li>`;
                }
            }

            if (pagination.current_page < pagination.last_page) {
                html += `<li class="page-item"><a class="page-link" href="#" onclick="loadSuggestLockData(${pagination.current_page + 1}); return false;">Sau</a></li>`;
            } else {
                html += `<li class="page-item disabled"><span class="page-link">Sau</span></li>`;
            }

            container.querySelector('ul').innerHTML = html;
            container.style.display = 'block';
        }

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
                    loadSuggestLockData(1);
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

        function openSuggestLockModal() {
            const modal = new bootstrap.Modal(document.getElementById('suggestLockModal'));
            modal.show();
            loadSuggestLockData(1);
        }

        // Load stats when page loads
        document.addEventListener('DOMContentLoaded', function () {
            loadSuggestLockData(1);
        });
    </script>


<?php $__env->stopPush(); ?>
<?php echo $__env->make('admin.layouts.layout_admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\luanvan\resources\views/admin/reviews/index.blade.php ENDPATH**/ ?>