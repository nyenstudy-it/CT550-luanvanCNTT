@extends('admin.layouts.layout_admin')

@section('content')
    <div class="container-fluid pt-4 px-4">
        <div class="bg-light rounded p-4">

            <h6 class="mb-4">Phân ca làm việc cho nhân viên</h6>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.attendances.store') }}">
                @csrf

                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label">Nhân viên</label>
                    <div class="col-sm-10">
                        <select name="staff_id" class="form-select" required>
                            <option value="">-- Chọn nhân viên --</option>
                        @php
                            $position = [
                                'cashier' => 'Thu ngân',
                                'warehouse' => 'Nhân viên kho',
                                'order_staff' => 'Nhân viên đơn hàng',
                            ];

                            $status = [
                                'probation' => 'Thử việc',
                                'official' => 'Chính thức',
                                'resigned' => 'Đã nghỉ',
                            ];
                        @endphp

                        @foreach($staffs as $staff)
                            <option value="{{ $staff->user_id }}" {{ old('staff_id') == $staff->user_id ? 'selected' : '' }}>

                                {{ $staff->user->name }}
                                ({{ $position[$staff->position] ?? $staff->position }})
                                - {{ $status[$staff->employment_status] ?? $staff->employment_status }}

                            </option>
                        @endforeach

                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label">Ngày làm</label>
                    <div class="col-sm-10">
                        <input type="date" name="work_date" value="{{ old('work_date') }}" class="form-control" required>
                    </div>
                </div>

                <div class="row mb-4">
                    <label class="col-sm-2 col-form-label">Ca làm</label>
                    <div class="col-sm-10">
                        <select name="shift" id="shiftSelect" class="form-select" required>
                            <option value="">-- Chọn ca làm --</option>
                            <option value="morning" {{ old('shift') == 'morning' ? 'selected' : '' }}>
                                Ca sáng
                            </option>
                            <option value="afternoon" {{ old('shift') == 'afternoon' ? 'selected' : '' }}>
                                Ca chiều
                            </option>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-6">
                        <label class="form-label">Giờ vào dự kiến</label>
                        <input type="time" name="expected_check_in" id="checkInInput" value="{{ old('expected_check_in') }}"
                            class="form-control" required>
                    </div>

                    <div class="col-sm-6">
                        <label class="form-label">Giờ ra dự kiến</label>
                        <input type="time" name="expected_check_out" id="checkOutInput"
                            value="{{ old('expected_check_out') }}" class="form-control" required>
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <a href="{{ route('admin.attendances.index') }}" class="btn btn-secondary me-2">
                        Quay lại
                    </a>
                    <button type="submit" class="btn btn-primary">
                        Lưu ca làm
                    </button>
                </div>

            </form>
            <hr class="my-4">

            <h6 class="mb-3">Lịch ca làm việc</h6>

            <div id="calendar"></div>


        </div>
    </div>

    <script>
        document.getElementById('shiftSelect').addEventListener('change', function () {

            const shift = this.value;
            const checkInInput = document.getElementById('checkInInput');
            const checkOutInput = document.getElementById('checkOutInput');

            if (shift === 'morning') {
                checkInInput.value = '08:00';
                checkOutInput.value = '11:00';
            }
            else if (shift === 'afternoon') {
                checkInInput.value = '13:00';
                checkOutInput.value = '16:00';
            }
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const calendarEl = document.getElementById('calendar');
            const events = @json($calendarEvents);

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'timeGridWeek',
                locale: 'vi',
                height: 600,
                slotMinTime: '07:00:00',
                slotMaxTime: '17:00:00',
                allDaySlot: false,
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                events: events,
            });

            calendar.render();
        });
    </script>

@endsection