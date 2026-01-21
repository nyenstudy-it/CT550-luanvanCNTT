@extends('admin.layouts.layout_admin')

@section('content')
    <div class="container-fluid pt-4 px-4">
        <div class="bg-light rounded p-4">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">Danh sách danh mục sản phẩm</h6>
                <a href="{{ route('admin.categories.create') }}" class="btn btn-primary btn-sm">
                    + Thêm danh mục
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>STT</th>
                            <th>Tên danh mục</th>
                            <th>Hình ảnh</th>
                            <th>Mô tả</th>
                            <th width="150">Thao tác</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($categories as $index => $category)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $category->name }}</td>
                                <td>
                                    @if ($category->image_url)
                                        <img src="{{ asset('storage/' . $category->image_url) }}" width="60" height="60"
                                            class="rounded object-fit-cover">
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ $category->description ?? '—' }}</td>
                                <td>
                                    <a href="{{ route('admin.categories.edit', $category->id) }}"
                                        class="btn btn-sm btn-warning">
                                        Sửa
                                    </a>

                                    <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST"
                                        class="d-inline" onsubmit="return confirm('Xóa danh mục này?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger">
                                            Xóa
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach

                        @if ($categories->isEmpty())
                            <tr>
                                <td colspan="5" class="text-center">
                                    Chưa có danh mục nào
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

        </div>
    </div>
@endsection