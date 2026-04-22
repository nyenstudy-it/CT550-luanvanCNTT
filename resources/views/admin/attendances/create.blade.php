@extends('admin.layouts.layout_admin')

@section('content')
<div class="container-fluid pt-4 px-4">
    <div class="bg-light rounded p-4">
        <div class="mb-4">
            <h5 class="mb-1">Phân ca làm việc cho nhân viên</h5>
        </div>

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
            @php($submitLabel = 'Lưu ca làm')
            @include('admin.attendances._form')
        </form>
    </div>
</div>

<script>
    const shiftSelect = document.getElementById('shiftSelect');
    const checkInInput = document.getElementById('checkInInput');
    const checkOutInput = document.getElementById('checkOutInput');

    function applyShiftPreset(shift, force = false) {
        if (!checkInInput || !checkOutInput) {
            return;
        }

        if (shift === 'morning' && (force || (!checkInInput.value && !checkOutInput.value))) {
            checkInInput.value = '08:00';
            checkOutInput.value = '11:00';
        }

        if (shift === 'afternoon' && (force || (!checkInInput.value && !checkOutInput.value))) {
            checkInInput.value = '13:00';
            checkOutInput.value = '16:00';
        }
    }

    if (shiftSelect) {
        applyShiftPreset(shiftSelect.value);
        shiftSelect.addEventListener('change', function () {
            applyShiftPreset(this.value, true);
        });
    }
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const calendarEl = document.getElementById('calendar');

        if (!calendarEl) {
            return;
        }

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