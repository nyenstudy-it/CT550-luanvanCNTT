@extends('admin.layouts.layout_admin')

@section('content')
    <div class="container-fluid pt-4 px-4">
        <div class="row g-4">

            <div class="col-12">
                <div class="bg-light rounded h-100 p-4">
                    <h6 class="mb-4">Thêm sản phẩm</h6>

                    <form method="POST"
                          action="{{ route('admin.products.store') }}"
                          enctype="multipart/form-data">
                        @csrf

                        {{-- Tên sản phẩm --}}
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label">Tên sản phẩm</label>
                            <div class="col-sm-10">
                                <input type="text"
                                       name="name"
                                       class="form-control"
                                       value="{{ old('name') }}"
                                       required>
                            </div>
                        </div>

                        {{-- Danh mục --}}
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label">Danh mục</label>
                            <div class="col-sm-10">
                                <select name="category_id" class="form-select" required>
                                    <option value="">-- Chọn danh mục --</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}"
                                            {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Nhà cung cấp --}}
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label">Nhà cung cấp</label>
                            <div class="col-sm-10">
                                <select name="supplier_id" class="form-select" required>
                                    <option value="">-- Chọn nhà cung cấp --</option>
                                    @foreach ($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}"
                                            {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                            {{ $supplier->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Giá --}}
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label">Giá (VNĐ)</label>
                            <div class="col-sm-10">
                                <input type="number"
                                       name="price"
                                       class="form-control"
                                       value="{{ old('price') }}"
                                       min="0"
                                       required>
                            </div>
                        </div>

                        {{-- Mô tả --}}
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label">Mô tả</label>
                            <div class="col-sm-10">
                                <textarea name="description"
                                          class="form-control"
                                          rows="4">{{ old('description') }}</textarea>
                            </div>
                        </div>

                        {{-- Hướng dẫn sử dụng --}}
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label">Hướng dẫn sử dụng</label>
                            <div class="col-sm-10">
                                <textarea name="usage_instructions"
                                          class="form-control"
                                          rows="4">{{ old('usage_instructions') }}</textarea>
                            </div>
                        </div>

                        {{-- Ngày SX + HSD --}}
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label">Ngày SX</label>
                            <div class="col-sm-4">
                                <input type="date"
                                       name="manufacture_date"
                                       class="form-control"
                                       value="{{ old('manufacture_date') }}">
                            </div>

                            <label class="col-sm-2 col-form-label">Hạn sử dụng</label>
                            <div class="col-sm-4">
                                <input type="date"
                                       name="expiry_date"
                                       class="form-control"
                                       value="{{ old('expiry_date') }}">
                            </div>
                        </div>

                        {{-- Bảo quản --}}
                        <div class="row mb-4">
                            <label class="col-sm-2 col-form-label">Bảo quản</label>
                            <div class="col-sm-10">
                                <input type="text"
                                       name="storage_instructions"
                                       class="form-control"
                                       value="{{ old('storage_instructions') }}">
                            </div>
                        </div>

                        {{-- OCOP --}}
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label">OCOP ⭐</label>
                            <div class="col-sm-4">
                                <input type="number"
                                       name="ocop_star"
                                       min="0"
                                       max="5"
                                       class="form-control"
                                       value="{{ old('ocop_star') }}">
                            </div>

                            <label class="col-sm-2 col-form-label">Năm OCOP</label>
                            <div class="col-sm-4">
                                <input type="number"
                                       name="ocop_year"
                                       class="form-control"
                                       value="{{ old('ocop_year') }}">
                            </div>
                        </div>

                        {{-- Hình ảnh --}}
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label">
                                Hình ảnh
                                <br>
                                <small class="text-muted">
                                    Ảnh đầu tiên là ảnh đại diện
                                </small>
                            </label>
                            <div class="col-sm-10">
                                <input type="file"
                                       name="images[]"
                                       class="form-control"
                                       multiple
                                       accept="image/*"
                                       required>
                            </div>
                        </div>

                        {{-- Trạng thái --}}
                        <div class="row mb-4">
                            <label class="col-sm-2 col-form-label">Trạng thái</label>
                            <div class="col-sm-10">
                                <select name="status" class="form-select">
                                    <option value="active">Đang bán</option>
                                    <option value="inactive">Ngừng bán</option>
                                </select>
                            </div>
                        </div>

                        {{-- Nút --}}
                        <button type="submit" class="btn btn-primary">
                            Thêm sản phẩm
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
