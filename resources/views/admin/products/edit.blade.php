@extends('admin.layouts.layout_admin')

@section('content')
<div class="container-fluid pt-4 px-4">
    <div class="row g-4">

        <div class="col-12">
            <div class="bg-light rounded h-100 p-4">

                <h6 class="mb-4">Chỉnh sửa sản phẩm</h6>

                <form method="POST"
                      action="{{ route('admin.products.update', $product->id) }}"
                      enctype="multipart/form-data">
                    @csrf

                    {{-- TÊN SẢN PHẨM --}}
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label">
                            Tên sản phẩm <span class="text-danger">*</span>
                        </label>
                        <div class="col-sm-10">
                            <input type="text" name="name"
                                   class="form-control"
                                   value="{{ old('name', $product->name) }}" required>
                        </div>
                    </div>

                    {{-- DANH MỤC --}}
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label">
                            Danh mục <span class="text-danger">*</span>
                        </label>
                        <div class="col-sm-10">
                            <select name="category_id" class="form-select" required>
                                <option value="">-- Chọn danh mục --</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- NHÀ CUNG CẤP --}}
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label">
                            Nhà cung cấp <span class="text-danger">*</span>
                        </label>
                        <div class="col-sm-10">
                            <select name="supplier_id" class="form-select" required>
                                <option value="">-- Chọn nhà cung cấp --</option>
                                @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}"
                                        {{ old('supplier_id', $product->supplier_id) == $supplier->id ? 'selected' : '' }}>
                                        {{ $supplier->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- ẢNH ĐẠI DIỆN --}}
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label">Ảnh đại diện</label>
                        <div class="col-sm-10">

                            @if ($product->image)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $product->image) }}"
                                         width="120" height="120"
                                         class="rounded border"
                                         style="object-fit: cover">
                                </div>
                            @endif

                            <input type="file"
                                   name="image"
                                   class="form-control"
                                   accept="image/*">

                            <small class="text-muted">
                                Không chọn ảnh mới → giữ nguyên ảnh hiện tại
                            </small>
                        </div>
                        
                    </div>

                    {{-- MÔ TẢ --}}
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label">Mô tả</label>
                        <div class="col-sm-10">
                            <textarea name="description"
                                      class="form-control"
                                      rows="4">{{ old('description', $product->description) }}</textarea>
                        </div>
                    </div>

                    {{-- HƯỚNG DẪN SỬ DỤNG --}}
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label">Hướng dẫn sử dụng</label>
                        <div class="col-sm-10">
                            <textarea name="usage_instructions"
                                      class="form-control"
                                      rows="3">{{ old('usage_instructions', $product->usage_instructions) }}</textarea>
                        </div>
                    </div>

                    {{-- BẢO QUẢN --}}
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label">Bảo quản</label>
                        <div class="col-sm-10">
                            <input type="text" name="storage_instructions"
                                   class="form-control"
                                   value="{{ old('storage_instructions', $product->storage_instructions) }}">
                        </div>
                    </div>

                    {{-- OCOP --}}
                    <div class="row mb-3">
                        <label class="col-sm-2 col-form-label">OCOP ⭐</label>
                        <div class="col-sm-4">
                            <input type="number" name="ocop_star"
                                   class="form-control"
                                   min="0" max="5"
                                   value="{{ old('ocop_star', $product->ocop_star) }}">
                        </div>

                        <label class="col-sm-2 col-form-label">Năm OCOP</label>
                        <div class="col-sm-4">
                            <input type="number" name="ocop_year"
                                   class="form-control"
                                   value="{{ old('ocop_year', $product->ocop_year) }}">
                        </div>
                    </div>

                    {{-- TRẠNG THÁI --}}
                    <div class="row mb-4">
                        <label class="col-sm-2 col-form-label">Trạng thái</label>
                        <div class="col-sm-10">
                            <select name="status" class="form-select">
                                <option value="active"
                                    {{ old('status', $product->status) == 'active' ? 'selected' : '' }}>
                                    Đang bán
                                </option>
                                <option value="inactive"
                                    {{ old('status', $product->status) == 'inactive' ? 'selected' : '' }}>
                                    Ngừng bán
                                </option>
                            </select>
                        </div>
                    </div>

                    {{-- NÚT --}}
                    <button type="submit" class="btn btn-primary">
                        Cập nhật
                    </button>

                    <a href="{{ route('admin.products.list') }}"
                       class="btn btn-secondary ms-2">
                        Quay lại
                    </a>

                </form>

            </div>
        </div>

    </div>
</div>
@endsection
