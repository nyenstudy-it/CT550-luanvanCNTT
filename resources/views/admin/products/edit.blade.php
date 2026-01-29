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

                        {{-- Tên sản phẩm --}}
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label">Tên sản phẩm</label>
                            <div class="col-sm-10">
                                <input type="text" name="name"
                                       class="form-control"
                                       value="{{ old('name', $product->name) }}" required>
                            </div>
                        </div>

                        {{-- Danh mục --}}
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label">Danh mục</label>
                            <div class="col-sm-10">
                                <select name="category_id" class="form-select" required>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}"
                                            {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
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
                                    @foreach ($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}"
                                            {{ old('supplier_id', $product->supplier_id) == $supplier->id ? 'selected' : '' }}>
                                            {{ $supplier->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Giá --}}
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label">Giá</label>
                            <div class="col-sm-10">
                                <input type="number" name="price"
                                       class="form-control"
                                       value="{{ old('price', $product->price) }}" required>
                            </div>
                        </div>

                        {{-- Mô tả --}}
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label">Mô tả</label>
                            <div class="col-sm-10">
                                <textarea name="description"
                                          class="form-control"
                                          style="height: 120px;">{{ old('description', $product->description) }}</textarea>
                            </div>
                        </div>

                        {{-- Hướng dẫn sử dụng --}}
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label">Hướng dẫn sử dụng</label>
                            <div class="col-sm-10">
                                <textarea name="usage_instructions"
                                          class="form-control"
                                          style="height: 120px;">{{ old('usage_instructions', $product->usage_instructions) }}</textarea>
                            </div>
                        </div>

                        {{-- Ngày SX + HSD --}}
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label">Ngày SX</label>
                            <div class="col-sm-4">
                                <input type="date" name="manufacture_date"
                                       class="form-control"
                                       value="{{ old('manufacture_date', $product->manufacture_date) }}">
                            </div>

                            <label class="col-sm-2 col-form-label">Hạn sử dụng</label>
                            <div class="col-sm-4">
                                <input type="date" name="expiry_date"
                                       class="form-control"
                                       value="{{ old('expiry_date', $product->expiry_date) }}">
                            </div>
                        </div>

                        {{-- Bảo quản + Khối lượng --}}
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label">Bảo quản</label>
                            <div class="col-sm-4">
                                <input type="text" name="storage_instructions"
                                       class="form-control"
                                       value="{{ old('storage_instructions', $product->storage_instructions) }}">
                            </div>

                            <label class="col-sm-2 col-form-label">Khối lượng</label>
                            <div class="col-sm-4">
                                <input type="text" name="weight_volume"
                                       class="form-control"
                                       value="{{ old('weight_volume', $product->weight_volume) }}">
                            </div>
                        </div>

                        {{-- OCOP --}}
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label">OCOP ⭐</label>
                            <div class="col-sm-4">
                                <input type="number" name="ocop_star"
                                       min="0" max="5"
                                       class="form-control"
                                       value="{{ old('ocop_star', $product->ocop_star) }}">
                            </div>

                            <label class="col-sm-2 col-form-label">Năm OCOP</label>
                            <div class="col-sm-4">
                                <input type="number" name="ocop_year"
                                       class="form-control"
                                       value="{{ old('ocop_year', $product->ocop_year) }}">
                            </div>
                        </div>

                        {{-- Ảnh --}}
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label">Hình ảnh</label>
                            <div class="col-sm-10">
                                @if ($product->image)
                                    <div class="mb-2">
                                        <img src="{{ asset('storage/' . $product->image) }}"
                                             width="120" class="rounded">
                                    </div>
                                @endif
                                <input type="file" name="images[]" class="form-control" multiple>
                                {{-- Ảnh chi tiết đã thêm --}}
                                @if ($product->images->count())
                                    <div class="row mt-3">
                                        <label class="col-sm-2 col-form-label">Ảnh chi tiết</label>
                                        <div class="col-sm-10">
                                            <div class="row">
                                                @foreach ($product->images as $img)
                                                    <div class="col-md-3 mb-3 text-center">
                                                        <img src="{{ asset('storage/' . $img->image) }}" class="img-fluid rounded mb-1"
                                                            style="height: 120px; object-fit: cover;">
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endif

                            </div>
                        </div>

                        {{-- Trạng thái --}}
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

                        {{-- Nút --}}
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
