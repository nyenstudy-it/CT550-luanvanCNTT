@extends('admin.layouts.layout_admin')

@section('content')
<div class="container-fluid pt-4 px-4">
    <div class="bg-light rounded p-4">

        <h6 class="mb-4">
            Chỉnh sửa biến thể – {{ $product->name }}
        </h6>

        {{-- Hiển thị lỗi --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST"
              action="{{ route('admin.products.variants.update', $variant->id) }}"
              enctype="multipart/form-data">
            @csrf

            {{-- THUỘC TÍNH BIẾN THỂ --}}
            <div class="row">
                {{-- MÀU SẮC --}}
                <div class="col-md-3 mb-3">
                    <label>Màu sắc</label>
                    <input type="text" name="color" class="form-control"
                           value="{{ old('color', $variant->color) }}">
                </div>

                {{-- KÍCH CỠ --}}
                <div class="col-md-3 mb-3">
                    <label>Kích cỡ</label>
                    <input type="text" name="size" class="form-control"
                           value="{{ old('size', $variant->size) }}">
                </div>

                {{-- DUNG TÍCH --}}
                <div class="col-md-3 mb-3">
                    <label>Dung tích</label>
                    <input type="text" name="volume" class="form-control"
                           value="{{ old('volume', $variant->volume) }}">
                </div>

                {{-- KHỐI LƯỢNG --}}
                <div class="col-md-3 mb-3">
                    <label>Khối lượng</label>
                    <input type="text" name="weight" class="form-control"
                           value="{{ old('weight', $variant->weight) }}">
                </div>
            </div>

            <small class="text-muted d-block mb-3">
                * Phải nhập ít nhất 1 trong các thuộc tính trên để tạo biến thể
            </small>

            {{-- GIÁ --}}
            <div class="mb-3">
                <label>Giá <span class="text-danger">*</span></label>
                <input type="number" name="price" class="form-control"
                       min="0" required
                       value="{{ old('price', $variant->price) }}">
            </div>

            <div class="row">
                {{-- NSX --}}
                <div class="col-md-6 mb-3">
                    <label>Ngày sản xuất</label>
                    <input type="date" name="manufacture_date" class="form-control"
                           value="{{ old('manufacture_date', $variant->manufacture_date) }}">
                </div>

                {{-- HSD --}}
                <div class="col-md-6 mb-3">
                    <label>Hạn sử dụng</label>
                    <input type="date" name="expired_at" class="form-control"
                           value="{{ old('expired_at', $variant->expired_at) }}">
                </div>
            </div>

            {{-- THÊM ẢNH MỚI --}}
            <div class="mb-3">
                <label>Ảnh biến thể (thêm mới)</label>
                <input type="file" name="images[]" class="form-control"
                       multiple accept="image/*">
                <small class="text-muted">
                    Có thể chọn nhiều ảnh. Ảnh đầu tiên sẽ là ảnh đại diện nếu chưa chọn ảnh chính.
                </small>
            </div>

            {{-- ẢNH HIỆN TẠI --}}
            @if ($variant->images->count())
                <div class="mb-3">
                    <label class="fw-bold">Ảnh hiện tại (chọn ảnh đại diện)</label>
                    <div class="d-flex flex-wrap gap-3 mt-2">
                        @foreach ($variant->images as $img)
                            <label style="cursor:pointer; text-align:center">
                                <input type="radio"
                                       name="primary_image_id"
                                       value="{{ $img->id }}"
                                       {{ $img->is_primary ? 'checked' : '' }}
                                       class="form-check-input d-block mx-auto mb-1">

                                <img src="{{ asset('storage/' . $img->image_path) }}"
                                     width="80" height="80"
                                     class="rounded border"
                                     style="object-fit:cover">
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif

            <button class="btn btn-primary">
                Cập nhật biến thể
            </button>

            <a href="{{ route('admin.products.variants.index', $product->id) }}"
               class="btn btn-secondary ms-2">
                Quay lại
            </a>
        </form>

    </div>
</div>
@endsection
