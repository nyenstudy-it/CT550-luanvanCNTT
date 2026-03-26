@extends('admin.layouts.layout_admin')

@section('content')
    <div class="container-fluid pt-4 px-2">
        <div class="bg-light rounded p-4">

            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div>
                    <h5 class="mb-1">Danh sách đánh giá</h5>
                    <small class="text-muted">Duyệt, phản hồi và quản lý chất lượng đánh giá sản phẩm.</small>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-12 col-sm-4">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Tổng đánh giá</small>
                        <h4 class="mb-0">{{ $summary['total'] ?? 0 }}</h4>
                    </div>
                </div>
                <div class="col-12 col-sm-4">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Chờ duyệt</small>
                        <h4 class="mb-0 text-warning">{{ $summary['pending'] ?? 0 }}</h4>
                    </div>
                </div>
                <div class="col-12 col-sm-4">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Đã duyệt</small>
                        <h4 class="mb-0 text-success">{{ $summary['approved'] ?? 0 }}</h4>
                    </div>
                </div>
            </div>

            <form method="GET" action="{{ route('admin.reviews') }}" class="row g-3 mb-4 border rounded bg-white p-3">
                <div class="col-md-5">
                    <label class="form-label">Từ khóa</label>
                    <input type="text" name="keyword" class="form-control" value="{{ request('keyword') }}"
                        placeholder="Sản phẩm, khách hàng, nội dung...">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="">-- Tất cả --</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Chờ duyệt</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Đã duyệt</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Số sao</label>
                    <select name="rating" class="form-select">
                        <option value="">-- Tất cả --</option>
                        @for($i = 5; $i >= 1; $i--)
                            <option value="{{ $i }}" {{ (string) request('rating') === (string) $i ? 'selected' : '' }}>{{ $i }}
                                sao</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">Lọc</button>
                    <a href="{{ route('admin.reviews') }}" class="btn btn-secondary">Đặt lại</a>
                </div>
            </form>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="60">STT</th>
                            <th width="90">ID</th>
                            <th>Sản phẩm</th>
                            <th>Khách</th>
                            <th width="80">Sao</th>
                            <th>Nội dung</th>
                            <th width="140">Ngày</th>
                            <th width="150">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reviews as $index => $review)
                            <tr>
                                <td>{{ $reviews->firstItem() + $index }}</td>
                                <td>#{{ $review->id }}</td>
                                <td>{{ $review->product?->name }}</td>
                                <td>{{ $review->customer?->user->name ?? $review->customer?->name ?? $review->customer_id }}
                                </td>
                                <td>{{ $review->rating }}</td>
                                <td>{{ Str::limit($review->content, 80) }}</td>
                                <td>{{ $review->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="btn-group" role="group" aria-label="Actions">
                                        @if($review->status === 'pending')
                                            <form action="{{ route('admin.reviews.approve', $review) }}" method="POST"
                                                style="display:inline-block">
                                                @csrf
                                                <button class="btn btn-sm btn-success">Duyệt</button>
                                            </form>
                                            <form action="{{ route('admin.reviews.reject', $review) }}" method="POST"
                                                style="display:inline-block">
                                                @csrf
                                                <button class="btn btn-sm btn-danger">Từ chối</button>
                                            </form>
                                        @else
                                            <button type="button" class="btn btn-sm btn-success" disabled>Đã duyệt</button>
                                        @endif

                                        <form action="{{ route('admin.reviews.reply', $review) }}" method="POST"
                                            style="display:inline-block; margin-left:4px;" class="d-none reply-fallback-form">
                                            @csrf
                                            <input type="hidden" name="content" value="Cảm ơn bạn đã đánh giá">
                                            <button class="btn btn-sm btn-primary">Trả lời</button>
                                        </form>

                                        <button type="button" class="btn btn-sm btn-info ms-1 btn-open-replies"
                                            data-review-id="{{ $review->id }}"
                                            data-review-author="{{ $review->customer?->user->name ?? $review->customer?->name ?? 'Khách' }}"
                                            data-review-content="{{ e($review->content) }}"
                                            data-review-created="{{ $review->created_at->format('d/m/Y H:i') }}"
                                            data-bs-toggle="modal" data-bs-target="#repliesModal">Chi tiết</button>

                                        <form action="{{ route('admin.reviews.destroy', $review) }}" method="POST"
                                            style="display:inline-block; margin-left:4px;">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('Bạn có chắc muốn xóa đánh giá này?')">Xóa</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            {{-- Replies Modal will be a single shared modal instantiated below --}}
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">Chưa có đánh giá</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{-- PAGINATION --}}
                {{ $reviews->appends(request()->query())->links() }}
            </div>

        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const csrfToken = '{{ csrf_token() }}';

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
                    alert('Vui lòng nhập nội dung trả lời.');
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
                    alert(msg);
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
@endpush