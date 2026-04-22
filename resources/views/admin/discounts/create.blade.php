@extends('admin.layouts.layout_admin')

@section('content')
    <div class="container-fluid pt-4 px-4">
        <div class="row g-4">

            <div class="col-12">
                <div class="bg-light rounded h-100 p-4">
                    <h6 class="mb-4">Thêm mã giảm giá</h6>

                    @php
                        $audienceOptions = \App\Models\Discount::audienceOptions();
                    @endphp

                    <form method="POST" action="{{ route('admin.discounts.store') }}">
                        @csrf

                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label">Mã giảm giá</label>
                            <div class="col-sm-10">
                                <input type="text" name="code" class="form-control" value="{{ old('code') }}" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label">Loại</label>
                            <div class="col-sm-10">
                                <select name="type" id="discountType" class="form-select" required>
                                    <option value="">-- Chọn loại --</option>
                                    <option value="percent" {{ old('type') == 'percent' ? 'selected' : '' }}>Phần trăm (%)
                                    </option>
                                    <option value="fixed" {{ old('type') == 'fixed' ? 'selected' : '' }}>Tiền cố định</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3" id="maxDiscountWrap" style="display: none;">
                            <label class="col-sm-2 col-form-label">Giảm tối đa</label>
                            <div class="col-sm-10">
                                <input type="number" name="max_discount" class="form-control"
                                    value="{{ old('max_discount') }}" step="0.01" min="0"
                                    placeholder="Ví dụ: 100000 cho mã giảm 15% tối đa 100.000 đ">
                                <small class="text-muted">Chỉ áp dụng cho mã giảm theo phần trăm.</small>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label">Đối tượng áp dụng</label>
                            <div class="col-sm-10">
                                <select name="audience" class="form-select" required>
                                    @foreach($audienceOptions as $audienceValue => $audienceLabel)
                                        <option value="{{ $audienceValue }}" {{ old('audience', 'all') === $audienceValue ? 'selected' : '' }}>
                                            {{ $audienceLabel }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Khách mới là khách chưa có đơn hoàn thành. Khách quay lại là khách
                                    đã có ít nhất 1 đơn hoàn thành.</small>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label">Phạm vi áp dụng</label>
                            <div class="col-sm-10">
                                <select name="scope" id="discountScope" class="form-select" required>
                                    <option value="all" {{ old('scope', 'all') == 'all' ? 'selected' : '' }}>Toàn shop
                                    </option>
                                    <option value="product" {{ old('scope') == 'product' ? 'selected' : '' }}>Theo sản phẩm
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3" id="productSelectWrap" style="display: none;">
                            <label class="col-sm-2 col-form-label">Sản phẩm áp dụng</label>
                            <div class="col-sm-10">
                                <select name="product_ids[]" class="form-select" multiple size="8">
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" {{ collect(old('product_ids', []))->contains($product->id) ? 'selected' : '' }}>
                                            {{ $product->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Giữ Ctrl hoặc Cmd để chọn nhiều sản phẩm.</small>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label">Giá trị giảm</label>
                            <div class="col-sm-10">
                                <input type="number" name="value" class="form-control" value="{{ old('value') }}"
                                    step="0.01" min="0" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label">Giới hạn sử dụng</label>
                            <div class="col-sm-10">
                                <input type="number" name="usage_limit" class="form-control"
                                    value="{{ old('usage_limit') }}" min="1" placeholder="Không bắt buộc">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label">Đơn tối thiểu áp dụng</label>
                            <div class="col-sm-10">
                                <input type="number" name="min_order_value" class="form-control"
                                    value="{{ old('min_order_value', 0) }}" min="0" step="0.01">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label">Ngày bắt đầu</label>
                            <div class="col-sm-4">
                                <input type="date" name="start_at" class="form-control" value="{{ old('start_at') }}">
                            </div>

                            <label class="col-sm-2 col-form-label">Ngày kết thúc</label>
                            <div class="col-sm-4">
                                <input type="date" name="end_at" class="form-control" value="{{ old('end_at') }}">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            Thêm mã giảm giá
                        </button>

                        <a href="{{ route('admin.discounts.index') }}" class="btn btn-secondary ms-2">
                            Quay lại
                        </a>

                    </form>
                </div>
            </div>

        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const typeEl = document.getElementById('discountType');
                const scopeEl = document.getElementById('discountScope');
                const maxDiscountWrap = document.getElementById('maxDiscountWrap');
                const productWrap = document.getElementById('productSelectWrap');

                function toggleMaxDiscount() {
                    maxDiscountWrap.style.display = typeEl.value === 'percent' ? '' : 'none';
                }

                function toggleProductSelect() {
                    productWrap.style.display = scopeEl.value === 'product' ? '' : 'none';
                }

                typeEl.addEventListener('change', toggleMaxDiscount);
                scopeEl.addEventListener('change', toggleProductSelect);
                toggleMaxDiscount();
                toggleProductSelect();
            });
        </script>
    @endpush
@endsection