@extends('admin.layouts.layout_admin')

@section('content')
<div class="container-fluid pt-4 px-4">
    <div class="bg-light rounded p-4">

        <h6 class="mb-4">
            Chỉnh sửa biến thể – {{ $product?->name ?? 'Sản phẩm' }}
        </h6>

        <form method="POST"
              action="{{ route('admin.products.variants.update', $variant->id) }}"
              enctype="multipart/form-data">
            @csrf

            {{-- Màu sắc --}}
            <div class="mb-3">
                <label>Màu sắc</label>
                <input type="text" name="color" class="form-control"
                       value="{{ old('color', $variant->color) }}">
            </div>

            {{-- Size --}}
            <div class="mb-3">
                <label>Kích cỡ</label>
                <input type="text" name="size" class="form-control"
                       value="{{ old('size', $variant->size) }}">
            </div>

            {{-- Dung tích --}}
            <div class="mb-3">
                <label>Dung tích</label>
                <input type="text" name="volume" class="form-control"
                       value="{{ old('volume', $variant->volume) }}">
            </div>

            {{-- Khối lượng --}}
            <div class="mb-3">
                <label>Khối lượng</label>
                <input type="text" name="weight" class="form-control"
                       value="{{ old('weight', $variant->weight) }}">
            </div>

            {{-- Giá --}}
            <div class="mb-3">
                <label>Giá <span class="text-danger">*</span></label>
                <input type="number" name="price" class="form-control"
                       value="{{ old('price', $variant->price) }}" min="0" required>
            </div>

            {{-- NSX --}}
            <div class="mb-3">
                <label>Ngày sản xuất</label>
                <input type="date" name="manufacture_date" class="form-control"
                       value="{{ old('manufacture_date', $variant->manufacture_date) }}">
            </div>

            {{-- HSD --}}
            <div class="mb-3">
                <label>Hạn sử dụng</label>
                <input type="date" name="expired_at" class="form-control"
                       value="{{ old('expired_at', $variant->expired_at) }}">
            </div>

            {{-- Thêm ảnh mới --}}
            <div class="mb-3">
                <label>Thêm ảnh mới</label>
                <input type="file" name="images[]" multiple
                       class="form-control" accept="image/*">
            </div>

            {{-- Ảnh hiện tại --}}
            <div class="mb-3">
                <label class="fw-bold">Ảnh hiện tại (chọn ảnh chính)</label>
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

            <button class="btn btn-primary">
                Cập nhật
            </button>

            <a href="{{ route('admin.products.variants.index', $product->id) }}"
               class="btn btn-secondary ms-2">
                Quay lại
            </a>
        </form>

    </div>
</div>
@endsection
