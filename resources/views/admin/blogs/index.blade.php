@extends('admin.layouts.layout_admin')

@section('content')
    <div class="container-fluid pt-4 px-4">
        <div class="bg-light rounded p-4">

            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div>
                    <h5 class="mb-1">Danh sách blog</h5>
                </div>
                <a href="{{ route('admin.blogs.create') }}" class="btn btn-sm btn-success">+ Thêm blog</a>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-12 col-sm-4">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Tổng blog</small>
                        <h4 class="mb-0">{{ $summary['total'] ?? 0 }}</h4>
                    </div>
                </div>
                <div class="col-12 col-sm-4">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Tạo trong tháng</small>
                        <h4 class="mb-0 text-primary">{{ $summary['this_month'] ?? 0 }}</h4>
                    </div>
                </div>
                <div class="col-12 col-sm-4">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Có ảnh đại diện</small>
                        <h4 class="mb-0 text-success">{{ $summary['with_image'] ?? 0 }}</h4>
                    </div>
                </div>
            </div>

            <form method="GET" action="{{ route('admin.blogs.index') }}" class="row g-3 mb-4 border rounded bg-white p-3">
                <div class="col-md-5">
                    <label class="form-label">Từ khóa</label>
                    <input type="text" name="keyword" class="form-control" value="{{ request('keyword') }}"
                        placeholder="Tiêu đề, slug, tóm tắt...">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Từ ngày</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Đến ngày</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">Lọc</button>
                    <a href="{{ route('admin.blogs.index') }}" class="btn btn-secondary">Đặt lại</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="50">STT</th>
                            <th>Ảnh</th>
                            <th>Tiêu đề</th>
                            <th>Slug</th>
                            <th>Ngày tạo</th>
                            <th width="220">Thao tác</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($blogs as $index => $blog)
                            <tr>
                                <td>{{ $blogs->firstItem() + $index }}</td>
                                <td>
                                    @if ($blog->image)
                                        <img src="{{ asset('storage/' . $blog->image) }}" width="60" height="60" class="rounded"
                                            style="object-fit: cover">
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>{{ $blog->title }}</td>
                                <td>{{ $blog->slug }}</td>
                                <td>{{ $blog->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <!-- Button Chi tiết -->
                                    <button type="button" class="btn btn-sm btn-info mb-1" data-bs-toggle="modal"
                                        data-bs-target="#blogModal{{ $blog->id }}">
                                        Chi tiết
                                    </button>

                                    <a href="{{ route('admin.blogs.edit', $blog->id) }}"
                                        class="btn btn-sm btn-warning mb-1">Sửa</a>

                                    <form action="{{ route('admin.blogs.destroy', $blog->id) }}" method="POST" class="d-inline"
                                        onsubmit="return confirm('Xóa blog này?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger mb-1">Xóa</button>
                                    </form>
                                </td>
                            </tr>

                            <!-- Modal Chi tiết -->
                            <div class="modal fade" id="blogModal{{ $blog->id }}" tabindex="-1"
                                aria-labelledby="blogModalLabel{{ $blog->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-xl modal-dialog-scrollable">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="blogModalLabel{{ $blog->id }}">Chi tiết Blog:
                                                {{ $blog->title }}
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <strong>Slug:</strong> {{ $blog->slug }}
                                            </div>
                                            <div class="mb-3">
                                                <strong>Ngày tạo:</strong> {{ $blog->created_at->format('d/m/Y H:i') }}
                                            </div>
                                            <div class="mb-3">
                                                <strong>Ảnh chính:</strong><br>
                                                @if($blog->image)
                                                    <img src="{{ asset('storage/' . $blog->image) }}" class="img-fluid mb-3">
                                                @else
                                                    <span class="text-muted">Không có ảnh</span>
                                                @endif
                                            </div>
                                            <div class="mb-3">
                                                <strong>Tóm tắt:</strong>
                                                <p>{{ $blog->summary ?? '—' }}</p>
                                            </div>
                                            <div class="mb-3">
                                                <strong>Nội dung:</strong>
                                                <p>{!! nl2br(e($blog->content)) !!}</p>
                                            </div>

                                            @if($blog->blocks->count())
                                                <div class="mb-3">
                                                    <strong>Blocks:</strong>
                                                    @foreach($blog->blocks as $block)
                                                        <div class="mb-2 border p-2 rounded">
                                                            <span class="badge bg-info mb-1">{{ ucfirst($block->type) }}</span>

                                                            {{-- Nếu có content --}}
                                                            @if($block->content)
                                                                <p>{!! nl2br(e($block->content)) !!}</p>
                                                            @endif

                                                            {{-- Nếu có ảnh --}}
                                                            @if($block->image)
                                                                <div class="mb-2">
                                                                    <img src="{{ asset('storage/' . $block->image) }}" class="img-fluid">
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif

                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">Đóng</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">Chưa có blog nào</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="mt-3">
                    {{ $blogs->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- Auto dismiss --}}
    <script>
        setTimeout(() => {
            document.querySelectorAll('.auto-dismiss').forEach(el => el.remove());
        }, 3000);
    </script>
@endsection