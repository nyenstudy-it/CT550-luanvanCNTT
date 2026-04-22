

<?php $__env->startSection('content'); ?>
<div class="container-fluid pt-4 px-4">
    <div class="bg-light rounded p-4">
        <div class="mb-4">
            <h5 class="mb-1">Phân ca làm việc cho nhân viên</h5>
        </div>

        <?php if($errors->any()): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo e(route('admin.attendances.store')); ?>">
            <?php echo csrf_field(); ?>
            <?php ($submitLabel = 'Lưu ca làm'); ?>
            <?php echo $__env->make('admin.attendances._form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
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

        const events = <?php echo json_encode($calendarEvents, 15, 512) ?>;

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

<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.layout_admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\luanvan\resources\views/admin/attendances/create.blade.php ENDPATH**/ ?>