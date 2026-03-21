@extends('admin.layouts.layout_admin')

@section('content')
    <div class="container-fluid pt-4 px-4">
        <div class="row g-4">
            <div class="col-12">
                <div class="bg-light rounded h-100 p-4">
                    <h6 class="mb-4">Chỉnh sửa mã giảm giá</h6>

                    <form method="POST" action="{{ route('admin.discounts.update', $discount->id) }}">
                        @csrf

                        {{-- Mã giảm giá --}}
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label">Mã giảm giá</label>
                            <div class="col-sm-10">
                                <input type="text" name="code" class="form-control"
                                    value="{{ old('code', $discount->code) }}" required>
                            </div>
                        </div>

                        {{-- Loại giảm giá --}}
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label">Loại</label>
                            <div class="col-sm-10">
                                <select name="type" class="form-select" required>
                                    <option value="percent" {{ old('type', $discount->type) == 'percent' ? 'selected' : '' }}>
                                        Phần trăm (%)</option>
                                    <option value="fixed" {{ old('type', $discount->type) == 'fixed' ? 'selected' : '' }}>Tiền
                                        cố định</option>
                                </select>
                            </div>
                        </div>

                        {{-- Giá trị --}}
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label">Giá trị</label>
                            <div class="col-sm-10">
                                <input type="number" name="value" class="form-control" min="0"
                                    value="{{ old('value', $discount->value) }}" required>
                            </div>
                        </div>

                        {{-- Giới hạn sử dụng --}}
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label">Số lần sử dụng tối đa</label>
                            <div class="col-sm-10">
                                <input type="number" name="usage_limit" class="form-control" min="0"
                                    value="{{ old('usage_limit', $discount->usage_limit) }}"
                                    placeholder="Để trống nếu không giới hạn">
                            </div>
                        </div>

                        {{-- Số lần đã sử dụng --}}
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label">Số lần đã sử dụng</label>
                            <div class="col-sm-10">
                                <input type="number" name="used_count" class="form-control" min="0"
                                    value="{{ old('used_count', $discount->used_count) }}" required>
                            </div>
                        </div>

                        {{-- Đơn tối thiểu --}}
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label">Đơn tối thiểu áp dụng</label>
                            <div class="col-sm-10">
                                <input type="number" name="min_order_value" class="form-control" min="0"
                                    value="{{ old('min_order_value', $discount->min_order_value) }}">
                            </div>
                        </div>

                        {{-- Thời gian --}}
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label">Bắt đầu</label>
                            <div class="col-sm-4">
                                <input type="datetime-local" name="start_at" class="form-control"
                                    value="{{ old('start_at', $discount->start_at?->format('Y-m-d\TH:i')) }}">
                            </div>

                            <label class="col-sm-2 col-form-label">Kết thúc</label>
                            <div class="col-sm-4">
                                <input type="datetime-local" name="end_at" class="form-control"
                                    value="{{ old('end_at', $discount->end_at?->format('Y-m-d\TH:i')) }}">
                            </div>
                        </div>

                        {{-- Nút lưu --}}
                        <button type="submit" class="btn btn-primary">Cập nhật</button>
                        <a href="{{ route('admin.discounts.index') }}" class="btn btn-secondary ms-2">Quay lại</a>

                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection