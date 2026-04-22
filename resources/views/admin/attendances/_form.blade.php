@php
    $isEdit = isset($attendance);
    $positionMap = [
        'cashier' => 'Thu ngân',
        'warehouse' => 'Nhân viên kho',
        'order_staff' => 'Nhân viên đơn hàng',
    ];
    $statusMap = [
        'probation' => 'Thử việc',
        'official' => 'Chính thức',
        'resigned' => 'Đã nghỉ',
    ];
@endphp

@once
    <style>
        .attendance-form-section {
            border: 1px solid #e9ecef;
            border-radius: 14px;
            background: #fff;
            padding: 20px;
            height: 100%;
        }

        .attendance-form-title {
            font-size: 1rem;
            font-weight: 700;
            color: #191c24;
            margin-bottom: 4px;
        }

        .attendance-form-note {
            font-size: 12px;
            color: #6c757d;
            margin-bottom: 18px;
        }

        .attendance-shift-card {
            border-radius: 12px;
            padding: 14px;
            background: #f8f9fa;
            border: 1px solid #e9ecef;
        }

        .attendance-calendar-wrap {
            border: 1px solid #e9ecef;
            border-radius: 14px;
            background: #fff;
            padding: 16px;
        }
    </style>
@endonce

<div class="row g-4 mb-4">
    <div class="col-12 col-xl-7">
        <div class="attendance-form-section h-100">
            <div class="attendance-form-title">Thông tin ca làm</div>

            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Nhân viên</label>
                    @if ($isEdit)
                        <input type="text" class="form-control"
                            value="{{ $attendance->user?->name }} - {{ $positionMap[$attendance->staff->position] ?? $attendance->staff->position }}"
                            readonly>
                        <input type="hidden" name="staff_id" value="{{ $attendance->staff_id }}">
                    @else
                        <select name="staff_id" class="form-select" required>
                            <option value="">-- Chọn nhân viên --</option>
                            @foreach ($staffs as $staff)
                                <option value="{{ $staff->user_id }}" {{ old('staff_id') == $staff->user_id ? 'selected' : '' }}>
                                    {{ $staff->user->name }}
                                    ({{ $positionMap[$staff->position] ?? $staff->position }})
                                    - {{ $statusMap[$staff->employment_status] ?? $staff->employment_status }}
                                </option>
                            @endforeach
                        </select>
                    @endif
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label">Ngày làm</label>
                    <input type="date" name="work_date"
                        value="{{ old('work_date', $isEdit ? $attendance->work_date : '') }}" class="form-control"
                        required>
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label">Ca làm</label>
                    <select name="shift" id="shiftSelect" class="form-select" required>
                        <option value="">-- Chọn ca làm --</option>
                        <option value="morning" {{ old('shift', $isEdit ? $attendance->shift : '') == 'morning' ? 'selected' : '' }}>
                            Ca sáng
                        </option>
                        <option value="afternoon" {{ old('shift', $isEdit ? $attendance->shift : '') == 'afternoon' ? 'selected' : '' }}>
                            Ca chiều
                        </option>
                    </select>
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label">Giờ vào dự kiến</label>
                    <input type="time" name="expected_check_in" id="checkInInput"
                        value="{{ old('expected_check_in', $isEdit && $attendance->expected_check_in ? \Carbon\Carbon::parse($attendance->expected_check_in)->format('H:i') : '') }}"
                        class="form-control" required>
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label">Giờ ra dự kiến</label>
                    <input type="time" name="expected_check_out" id="checkOutInput"
                        value="{{ old('expected_check_out', $isEdit && $attendance->expected_check_out ? \Carbon\Carbon::parse($attendance->expected_check_out)->format('H:i') : '') }}"
                        class="form-control" required>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-5">
        <div class="attendance-form-section h-100">
            <div class="attendance-form-title">Khung giờ gợi ý</div>

            <div class="attendance-shift-card mb-3">
                <div class="fw-semibold mb-1">Ca sáng</div>
                <div class="text-muted small">08:00 - 11:00</div>
            </div>

            <div class="attendance-shift-card mb-3">
                <div class="fw-semibold mb-1">Ca chiều</div>
                <div class="text-muted small">13:00 - 16:00</div>
            </div>

            <div class="attendance-shift-card">
                <div class="fw-semibold mb-1">Lưu ý</div>
                <div class="text-muted small">Mỗi nhân viên chỉ nên có một ca cho cùng ngày và cùng khung thời gian để
                    tránh trùng lịch.</div>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-end gap-2 flex-wrap mb-4">
    <a href="{{ route('admin.attendances.index') }}" class="btn btn-secondary">Quay lại</a>
    <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
</div>

<div class="attendance-calendar-wrap">
    <div class="attendance-form-title">Lịch ca làm việc</div>
    <div class="attendance-form-note">Theo dõi nhanh các ca đã được phân để tránh chồng lịch trên cùng ngày.</div>
    <div id="calendar"></div>
</div>