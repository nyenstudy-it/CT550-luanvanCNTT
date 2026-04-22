

<?php $__env->startSection('content'); ?>
    <div class="container-fluid pt-4 px-4">
        <div class="bg-light rounded p-4">

            <form method="GET" action="<?php echo e(route('admin.attendances.index')); ?>" class="row g-3 mb-3">

                <div class="col-md-3">
                    <label class="form-label">Từ ngày</label>
                    <input type="date" name="from_date" value="<?php echo e(request('from_date')); ?>" class="form-control">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Đến ngày</label>
                    <input type="date" name="to_date" value="<?php echo e(request('to_date')); ?>" class="form-control">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Ca</label>
                    <select name="shift" class="form-select">
                        <option value="">-- Tất cả --</option>
                        <option value="morning" <?php echo e(request('shift') == 'morning' ? 'selected' : ''); ?>>Ca sáng</option>
                        <option value="afternoon" <?php echo e(request('shift') == 'afternoon' ? 'selected' : ''); ?>>Ca chiều</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="">-- Tất cả --</option>
                        <option value="scheduled">Đã phân ca</option>
                        <option value="working">Đang làm</option>
                        <option value="completed">Hoàn thành</option>
                        <option value="absent">Vắng mặt</option>
                    </select>
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Lọc</button>
                </div>

            </form>



            <div class="d-flex justify-content-between mb-3">
                <h6>Phân ca nhân viên</h6>
                <a href="<?php echo e(route('admin.attendances.create')); ?>" class="btn btn-primary btn-sm">
                    + Phân ca
                </a>
            </div>

            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>STT</th>
                        <th>Nhân viên</th>
                        <th>Chức vụ</th>
                        <th>Ngày</th>
                        <th>Ca</th>
                        <th>Trạng thái</th>
                        <th>Giờ làm</th>
                        <th>Lương ca</th>
                        <th>Chấm công</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>

                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $attendances; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $attendance): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                        <?php
                            $shiftText = match ($attendance->shift) {
                                'morning' => 'Ca sáng',
                                'afternoon' => 'Ca chiều',
                                default => 'Không xác định',
                            };

                            $badgeMap = [
                                'scheduled' => ['label' => 'Đã phân ca', 'class' => 'bg-info'],
                                'working' => ['label' => 'Đang làm', 'class' => 'bg-warning'],
                                'completed' => ['label' => 'Đã hoàn thành', 'class' => 'bg-success'],
                                'absent' => ['label' => 'Vắng mặt', 'class' => 'bg-danger'],
                            ];

                            $statusKey = $attendance->computed_status;
                            $status = $badgeMap[$statusKey] ?? ['label' => '--', 'class' => 'bg-secondary'];

                            // Use worked_minutes - accessor will prefer DB value
                            $workedHours = !is_null($attendance->worked_minutes)
                                ? round($attendance->worked_minutes / 60, 2)
                                : null;

                            // Tính pending salary nếu early_leave && pending
                            $displaySalary = $attendance->salary_amount;
                            if ($attendance->is_early_leave && $attendance->early_leave_status === 'pending') {
                                $rate = $attendance->staff->employment_status === 'official'
                                    ? ($attendance->staff->official_hourly_wage ?? 20000)
                                    : ($attendance->staff->probation_hourly_wage ?? 15000);

                                $actualCheckIn = \Carbon\Carbon::parse($attendance->work_date . ' ' . $attendance->check_in);
                                $actualCheckOut = \Carbon\Carbon::parse($attendance->work_date . ' ' . $attendance->check_out);
                                $expectedCheckIn = \Carbon\Carbon::parse($attendance->work_date . ' ' . $attendance->expected_check_in);
                                $expectedCheckOut = \Carbon\Carbon::parse($attendance->work_date . ' ' . $attendance->expected_check_out);

                                // Tính trễ (dương = trễ, âm = sớm) — expected->diffInMinutes(checkIn)
                                $lateMinutes = $expectedCheckIn->diffInMinutes($actualCheckIn, false);

                                // Pending salary = dự tính khi DUYỆT (đến giờ nhân viên xin về sớm).
                                if ($lateMinutes <= 15) {
                                    $pendingWorkedMinutes = $expectedCheckIn->diffInMinutes($actualCheckOut);
                                } else {
                                    $pendingWorkedMinutes = $actualCheckIn->diffInMinutes($actualCheckOut);
                                }

                                $displaySalary = round(($pendingWorkedMinutes / 60) * $rate);
                            }

                        ?>

                        <tr>
                            <td><?php echo e($index + 1); ?></td>
                            <td><?php echo e($attendance->staff->user->name); ?></td>
                            <td>
                                <?php
                                    $positionMap = [
                                        'cashier' => 'Thu ngân',
                                        'warehouse' => 'Nhân viên kho',
                                        'order_staff' => 'Nhân viên đơn hàng',
                                    ];
                                ?>

                                <?php echo e($positionMap[$attendance->staff->position] ?? $attendance->staff->position); ?>

                            </td>
                            <td><?php echo e($attendance->work_date); ?></td>
                            <td><?php echo e($shiftText); ?></td>


                            <td>
                                <span class="badge <?php echo e($status['class']); ?>">
                                    <?php echo e($status['label']); ?>

                                </span>

                                <?php if($attendance->is_late): ?>
                                    <span class="badge bg-danger mt-1">Đi trễ</span>
                                <?php endif; ?>

                                <?php if($attendance->is_early_leave): ?>
                                    <span class="badge bg-warning mt-1">Về sớm</span>
                                    <?php if($attendance->early_leave_reason): ?>
                                        <small class="d-block text-muted">(<?php echo e($attendance->early_leave_reason); ?>)</small>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php if(!is_null($workedHours)): ?>
                                    <?php echo e($workedHours); ?> giờ
                                <?php else: ?>
                                    <span class="text-muted">--</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php if(!is_null($displaySalary)): ?>
                                    <?php echo e(number_format($displaySalary, 0, ',', '.')); ?> đ
                                <?php else: ?>
                                    <span class="text-muted">--</span>
                                <?php endif; ?>
                            </td>

                            <td class="text-center">
                                <?php if($attendance->check_in): ?>
                                    <div class="mb-2">
                                        <span class="text-muted small">Vào:</span>
                                        <span class="badge bg-success">
                                            <?php echo e(\Carbon\Carbon::parse($attendance->check_in)->format('H:i')); ?>

                                        </span>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted small">Chưa vào</span>
                                <?php endif; ?>

                                <?php if($attendance->check_out): ?>
                                    <div>
                                        <span class="text-muted small">Ra:</span>
                                        <span class="badge bg-danger">
                                            <?php echo e(\Carbon\Carbon::parse($attendance->check_out)->format('H:i')); ?>

                                        </span>
                                    </div>
                                <?php elseif($attendance->check_in): ?>
                                    <div class="text-muted small">
                                        Chưa ra
                                    </div>
                                <?php endif; ?>

                                <?php if(!$attendance->check_in): ?>
                                    <span class="text-muted fst-italic">
                                        Chưa chấm công
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td class="text-center">

                                <?php if(!$attendance->check_in): ?>
                                    <a href="<?php echo e(route('admin.attendances.edit', $attendance->id)); ?>" class="btn btn-sm btn-warning">
                                        Sửa
                                    </a>

                                    <form action="<?php echo e(route('admin.attendances.destroy', $attendance->id)); ?>" method="POST"
                                        class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xoá ca này?')">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            Xoá
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <?php if($attendance->check_out): ?>

                                                <button type="button" class="btn btn-sm btn-info mt-1" data-bs-toggle="modal"
                                                    data-bs-target="#detailModal<?php echo e($attendance->id); ?>">
                                                    Xem chi tiết
                                                </button>
                                                <div class="modal fade" id="detailModal<?php echo e($attendance->id); ?>" tabindex="-1">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">

                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Chi tiết ca làm</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>

                                                            <div class="modal-body">

                                                                <p><strong>Nhân viên:</strong> <?php echo e($attendance->staff->user->name); ?></p>
                                                                <p><strong>Ngày làm:</strong> <?php echo e($attendance->work_date); ?></p>
                                                                <p><strong>Ca:</strong> <?php echo e($shiftText); ?></p>

                                                                <hr>

                                                                <div class="mb-3">
                                                                    <h6 class="text-muted">Kỳ vọng</h6>
                                                                    <p class="mb-1"><small class="text-muted">Từ:</small>
                                                                        <strong><?php echo e($attendance->expected_check_in); ?></strong>
                                                                    </p>
                                                                    <p><small class="text-muted">Đến:</small>
                                                                        <strong><?php echo e($attendance->expected_check_out); ?></strong>
                                                                    </p>
                                                                </div>

                                                                <div class="mb-3">
                                                                    <h6 class="text-muted">Thực tế</h6>
                                                                    <p class="mb-1">
                                                                        <small class="text-muted">Giờ vào:</small>
                                                                        <?php if($attendance->check_in): ?>
                                                                            <strong><?php echo e(\Carbon\Carbon::parse($attendance->check_in)->format('H:i')); ?></strong>
                                                                        <?php else: ?>
                                                                            <span class="text-muted">--</span>
                                                                        <?php endif; ?>
                                                                    </p>
                                                                    <p>
                                                                        <small class="text-muted">Giờ ra:</small>
                                                                        <?php if($attendance->check_out): ?>
                                                                            <strong><?php echo e(\Carbon\Carbon::parse($attendance->check_out)->format('H:i')); ?></strong>
                                                                        <?php else: ?>
                                                                            <span class="text-muted">--</span>
                                                                        <?php endif; ?>
                                                                    </p>
                                                                </div>

                                                                <hr>

                                                                <p><strong>Tổng giờ làm:</strong>
                                                                    <?php echo e($attendance->worked_minutes
                                    ? round($attendance->worked_minutes / 60, 2) . ' giờ (' . $attendance->worked_minutes . ' phút)'
                                    : '--'); ?>

                                                                </p>

                                                                <p><strong>Lương ca:</strong>
                                                                    <?php if(!is_null($attendance->salary_amount)): ?>
                                                                        <span
                                                                            class="badge bg-success"><?php echo e(number_format($attendance->salary_amount, 0, ',', '.')); ?>

                                                                            đ</span>
                                                                        <?php
                                                                            $rate = $attendance->staff->employment_status === 'official' ? '20.000' : '15.000';
                                                                        ?>
                                                                        <small class="text-muted d-block mt-1">
                                                                            (<?php echo e($rate); ?>đ/giờ ×
                                                                            <?php echo e(round((($attendance->attributes['worked_minutes'] ?? 0) / 60), 2)); ?>h)
                                                                        </small>
                                                                    <?php else: ?>
                                                                        <span class="text-muted">--</span>
                                                                    <?php endif; ?>
                                                                </p>

                                                                <hr>

                                                                <?php if($attendance->check_in_latitude && $attendance->check_in_longitude): ?>
                                                                    <p><strong> Vị trí Check-in:</strong></p>
                                                                    <p class="mb-1" style="font-size: 0.9rem;">
                                                                        <code><?php echo e($attendance->check_in_latitude); ?>, <?php echo e($attendance->check_in_longitude); ?></code>
                                                                    </p>
                                                                    <?php if($attendance->check_in_verification_method): ?>
                                                                        <?php
                                                                            $methodMap = [
                                                                                'wifi' => '✓ WiFi',
                                                                                'radius' => '✓ GPS (' . round($attendance->check_in_distance_meters, 1) . 'm)',
                                                                                'both' => '✓ WiFi + GPS (' . round($attendance->check_in_distance_meters, 1) . 'm)',
                                                                            ];
                                                                        ?>
                                                                        <span
                                                                            class="badge bg-info"><?php echo e($methodMap[$attendance->check_in_verification_method] ?? 'Unknown'); ?></span>
                                                                    <?php endif; ?>
                                                                    <?php if($attendance->check_in_network_type): ?>
                                                                        <span class="badge bg-secondary"><?php echo e($attendance->check_in_network_type); ?></span>
                                                                    <?php endif; ?>

                                                                    <hr>
                                                                <?php endif; ?>

                                                                <?php if($attendance->is_early_leave): ?>
                                                                    <hr>
                                                                    <p class="text-warning"><strong>Về sớm</strong></p>

                                                                    <?php if($attendance->early_leave_status === 'pending'): ?>
                                                                        <span class="badge bg-secondary">Chờ duyệt</span>
                                                                    <?php elseif($attendance->early_leave_status === 'approved'): ?>
                                                                        <span class="badge bg-success">Đã duyệt</span>
                                                                    <?php elseif($attendance->early_leave_status === 'rejected'): ?>
                                                                        <span class="badge bg-danger">Từ chối</span>
                                                                    <?php endif; ?>
                                                                <?php endif; ?>

                                                                <?php if($attendance->early_leave_reason): ?>
                                                                    <p class="mt-2"><strong>Lý do về sớm:</strong></p>
                                                                    <div class="border p-2 rounded bg-light">
                                                                        <?php echo e($attendance->early_leave_reason); ?>

                                                                    </div>
                                                                <?php endif; ?>

                                                                <?php if($attendance->is_early_leave && $attendance->early_leave_status === 'pending'): ?>
                                                                    <div class="mt-3">
                                                                        <form
                                                                            action="<?php echo e(route('admin.attendances.approveEarly', $attendance->id)); ?>"
                                                                            method="POST" class="d-inline">
                                                                            <?php echo csrf_field(); ?>
                                                                            <button type="submit" class="btn btn-success btn-sm">
                                                                                Duyệt
                                                                            </button>
                                                                        </form>

                                                                        <form action="<?php echo e(route('admin.attendances.rejectEarly', $attendance->id)); ?>"
                                                                            method="POST" class="d-inline ms-2">
                                                                            <?php echo csrf_field(); ?>
                                                                            <button type="submit" class="btn btn-danger btn-sm">
                                                                                Từ chối
                                                                            </button>
                                                                        </form>
                                                                    </div>
                                                                <?php endif; ?>

                                                            </div>

                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                                    Đóng
                                                                </button>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>


                                <?php endif; ?>

                            </td>
                        </tr>

                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="11" class="text-center text-muted">
                                Chưa có ca làm nào
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                <?php echo e($attendances->links()); ?>

            </div>

        </div>
        <hr class="my-4">

        <h6 class="mb-3">Lịch ca làm việc</h6>

        <div id="calendar"></div>

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
            const events = <?php echo json_encode($allCalendarEvents ?? [], 15, 512) ?>;

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'vi',
                height: 'auto',
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
<?php echo $__env->make('admin.layouts.layout_admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\luanvan\resources\views/admin/attendances/index.blade.php ENDPATH**/ ?>