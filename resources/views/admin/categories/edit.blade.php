@extends('admin.layouts.layout_admin')

@section('content')
    <div class="container-fluid pt-4 px-4">
        <div class="row g-4">

            <div class="col-12">
                <div class="bg-light rounded h-100 p-4">
                    <h6 class="mb-4">Chỉnh sửa danh mục sản phẩm</h6>

                    <form method="POST" action="{{ route('admin.categories.update', $category->id) }}"
                        enctype="multipart/form-data">
                        @csrf

                        {{-- Tên danh mục --}}
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label">Tên danh mục</label>
                            <div class="col-sm-10">
                                <input type="text" name="name" class="form-control"
                                    value="{{ old('name', $category->name) }}" required>
                            </div>
                        </div>

                        {{-- Mô tả --}}
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label">Mô tả</label>
                            <div class="col-sm-10">
                                <textarea name="description" class="form-control"
                                    style="height: 150px;">{{ old('description', $category->description) }}</textarea>
                            </div>
                        </div>

                        {{-- Hình ảnh --}}
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label">Hình ảnh</label>
                            <div class="col-sm-10">

                                @if ($category->image_url)
                                    <div class="mb-2">
                                        <img src="{{ asset('storage/' . $category->image_url) }}" width="100" class="rounded">
                                    </div>
                                @endif

                                <input type="file" name="image" class="form-control">
                                <small class="text-muted">
                                    Chọn ảnh mới nếu muốn thay đổi
                                </small>
                            </div>
                        </div>

                        {{-- Nút --}}
                        <button type="submit" class="btn btn-primary">
                            Cập nhật
                        </button>

                        <a href="{{ route('admin.categories.list') }}" class="btn btn-secondary ms-2">
                            Quay lại
                        </a>
                    </form>

                </div>
            </div>

        </div>
    </div>
@endsection