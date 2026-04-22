@extends('admin.layouts.layout_admin')

@section('content')
    <div class="container-fluid pt-4 px-4">
        <div class="bg-light rounded p-4">

            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div>
                    <h5 class="mb-1">Danh sách danh mục sản phẩm</h5>
                </div>
                <a href="{{ route('admin.categories.create') }}" class="btn btn-sm btn-success">+ Thêm danh mục</a>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-12 col-sm-4">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Tổng danh mục</small>
                        <h4 class="mb-0">{{ $summary['total'] ?? 0 }}</h4>
                    </div>
                </div>
                <div class="col-12 col-sm-4">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Có hình ảnh</small>
                        <h4 class="mb-0 text-success">{{ $summary['with_image'] ?? 0 }}</h4>
                    </div>
                </div>
                <div class="col-12 col-sm-4">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Chưa có hình ảnh</small>
                        <h4 class="mb-0 text-warning">{{ $summary['without_image'] ?? 0 }}</h4>
                    </div>
                </div>
            </div>

            <form method="GET" action="{{ route('admin.categories.list') }}"
                class="row g-3 mb-4 border rounded bg-white p-3">
                <div class="col-md-5">
                    <label class="form-label">Từ khóa</label>
                    <input type="text" name="keyword" value="{{ request('keyword') }}" class="form-control"
                        placeholder="Tên danh mục hoặc mô tả...">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Ảnh</label>
                    <select name="has_image" class="form-select">
                        <option value="">-- Tất cả --</option>
                        <option value="yes" {{ request('has_image') === 'yes' ? 'selected' : '' }}>Có ảnh</option>
                        <option value="no" {{ request('has_image') === 'no' ? 'selected' : '' }}>Chưa có ảnh</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">Lọc</button>
                    <a href="{{ route('admin.categories.list') }}" class="btn btn-secondary">Đặt lại</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="60">STT</th>
                            <th>Tên danh mục</th>
                            <th width="100">Hình ảnh</th>
                            <th>Mô tả</th>
                            <th width="180">Thao tác</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($categories as $index => $category)
                            <tr>
                                <td>{{ $categories->firstItem() + $index }}</td>
                                <td>{{ $category->name }}</td>
                                <td>
                                    @if ($category->image_url)
                                        <img src="{{ asset('storage/' . $category->image_url) }}" width="60" height="60"
                                            class="rounded object-fit-cover">
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>{{ $category->description ?? '—' }}</td>
                                <td>
                                    <a href="{{ route('admin.categories.edit', $category->id) }}"
                                        class="btn btn-sm btn-warning mb-1">
                                        Sửa
                                    </a>

                                    <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST"
                                        class="d-inline" onsubmit="return confirm('Xóa danh mục này?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger mb-1">
                                            Xóa
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">Chưa có danh mục nào</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $categories->appends(request()->query())->links() }}
            </div>

        </div>
    </div>
@endsection