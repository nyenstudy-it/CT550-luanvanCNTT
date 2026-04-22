<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Attendance;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AuditAttendanceSalaries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:audit {--from=} {--to=} {--output=} {--apply} {--backup=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Audit worked_minutes and salary_amount for attendances between dates';

    public function handle()
    {
        $from = $this->option('from') ?: now()->startOfMonth()->toDateString();
        $to = $this->option('to') ?: now()->toDateString();
        $output = $this->option('output') ?: storage_path('logs/attendance-audit.csv');
        $apply = (bool) $this->option('apply');
        $backupPath = $this->option('backup') ?: storage_path('logs/attendance-audit-backup-' . now()->format('Ymd_His') . '.csv');

        $this->info("Audit attendances from {$from} to {$to}");

        $rows = [];
        $header = [
            'attendance_id',
            'staff_id',
            'staff_name',
            'work_date',
            'expected_check_in',
            'expected_check_out',
            'check_in',
            'check_out',
            'is_early_leave_db',
            'early_leave_status_db',
            'stored_worked_minutes',
            'stored_salary_amount',
            'computed_worked_minutes',
            'computed_salary_amount',
            'mismatch'
        ];

        $attendances = Attendance::with('staff.user')
            ->whereDate('work_date', '>=', $from)
            ->whereDate('work_date', '<=', $to)
            ->orderBy('work_date')
            ->get();

        foreach ($attendances as $att) {
            $staffName = $att->staff->user->name ?? ($att->staff->user_id ?? 'N/A');

            // Parse times safely
            $expectedStart = $att->work_date && $att->expected_check_in
                ? Carbon::parse($att->work_date . ' ' . $att->expected_check_in, 'Asia/Ho_Chi_Minh')
                : null;
            $expectedEnd = $att->work_date && $att->expected_check_out
                ? Carbon::parse($att->work_date . ' ' . $att->expected_check_out, 'Asia/Ho_Chi_Minh')
                : null;

            $checkIn = null;
            if ($att->check_in) {
                $rawIn = $att->check_in;
                if (strpos($rawIn, '-') === false) { // no date present, prepend work_date
                    $rawIn = $att->work_date . ' ' . $rawIn;
                }
                $checkIn = Carbon::parse($rawIn, 'Asia/Ho_Chi_Minh');
            }

            $checkOut = null;
            if ($att->check_out) {
                $rawOut = $att->check_out;
                if (strpos($rawOut, '-') === false) { // no date present, prepend work_date
                    $rawOut = $att->work_date . ' ' . $rawOut;
                }
                $checkOut = Carbon::parse($rawOut, 'Asia/Ho_Chi_Minh');
            }

            $storedWorked = isset($att->worked_minutes) ? (int) $att->worked_minutes : null;
            $storedSalary = isset($att->salary_amount) ? (float) $att->salary_amount : null;

            // Compute expected values following AttendanceController::checkOut logic
            $computedWorked = 0;

            if (!$checkIn || !$checkOut || !$expectedStart || !$expectedEnd) {
                $computedWorked = 0;
            } else {
                $expectedMinutes = $expectedStart->diffInMinutes($expectedEnd);

                // Match Attendance model: expectedStart->diffInMinutes(checkIn)
                $lateMinutes = $expectedStart->diffInMinutes($checkIn);

                // Determine early leave from DB or by comparing times
                $isEarlyLeaveDb = (bool) ($att->is_early_leave ?? false);
                $isEarlyLeaveComputed = $checkOut->lt($expectedEnd);
                // Use DB flag if present, otherwise derive
                $isEarlyLeave = $isEarlyLeaveDb ? true : $isEarlyLeaveComputed;

                if ($isEarlyLeave) {
                    if ($lateMinutes > 15) {
                        $computedWorked = $checkIn->diffInMinutes($checkOut);
                    } else {
                        $computedWorked = $expectedStart->diffInMinutes($checkOut);
                    }
                } elseif ($lateMinutes > 15) {
                    $computedWorked = $checkIn->diffInMinutes($checkOut);
                } else {
                    $computedWorked = $expectedMinutes;
                }

                if ($computedWorked < 0) $computedWorked = 0;
            }

            // Hourly rate
            $hourlyRate = 20000;
            if ($att->staff) {
                if ($att->staff->employment_status === 'probation') {
                    $hourlyRate = $att->staff->probation_hourly_wage ?? 15000;
                } else {
                    $hourlyRate = $att->staff->official_hourly_wage ?? 20000;
                }
            }

            $computedSalary = round(($computedWorked / 60) * $hourlyRate, 0);

            $mismatch = '';
            if (!is_null($storedWorked) && $storedWorked !== $computedWorked) {
                $mismatch .= "worked_minutes mismatch (stored={$storedWorked},computed={$computedWorked}); ";
            }
            if (!is_null($storedSalary) && (int)$storedSalary !== (int)$computedSalary) {
                $mismatch .= "salary_amount mismatch (stored={$storedSalary},computed={$computedSalary}); ";
            }

            $rows[] = [
                $att->id,
                $att->staff_id,
                $staffName,
                $att->work_date,
                $att->expected_check_in,
                $att->expected_check_out,
                $att->check_in,
                $att->check_out,
                $att->is_early_leave,
                $att->early_leave_status,
                $storedWorked,
                $storedSalary,
                $computedWorked,
                $computedSalary,
                $mismatch,
            ];
        }

        // Write audit CSV
        $fp = fopen($output, 'w');
        if (!$fp) {
            $this->error('Cannot write to ' . $output);
            return 1;
        }

        fputcsv($fp, $header);
        foreach ($rows as $r) {
            fputcsv($fp, $r);
        }

        fclose($fp);

        $this->info('Audit CSV written: ' . $output);

        // If apply flag set, create backup and update DB for mismatched rows
        if ($apply) {
            $this->info('Apply mode enabled — creating backup and updating DB');

            $bfp = fopen($backupPath, 'w');
            if (!$bfp) {
                $this->error('Cannot write backup to ' . $backupPath);
                return 1;
            }

            // Backup header: include original values
            $backupHeader = array_merge($header, ['applied_at']);
            fputcsv($bfp, $backupHeader);

            foreach ($attendances as $att) {
                // find corresponding row in $rows by id
                // compute again (reuse computed values from $rows array)
                // simpler: recompute here to avoid mapping complexity

                $expectedStart = $att->work_date && $att->expected_check_in
                    ? Carbon::parse($att->work_date . ' ' . $att->expected_check_in, 'Asia/Ho_Chi_Minh')
                    : null;
                $expectedEnd = $att->work_date && $att->expected_check_out
                    ? Carbon::parse($att->work_date . ' ' . $att->expected_check_out, 'Asia/Ho_Chi_Minh')
                    : null;

                $checkIn = null;
                if ($att->check_in) {
                    $rawIn = $att->check_in;
                    if (strpos($rawIn, '-') === false) {
                        $rawIn = $att->work_date . ' ' . $rawIn;
                    }
                    $checkIn = Carbon::parse($rawIn, 'Asia/Ho_Chi_Minh');
                }

                $checkOut = null;
                if ($att->check_out) {
                    $rawOut = $att->check_out;
                    if (strpos($rawOut, '-') === false) {
                        $rawOut = $att->work_date . ' ' . $rawOut;
                    }
                    $checkOut = Carbon::parse($rawOut, 'Asia/Ho_Chi_Minh');
                }

                $computedWorked = 0;
                if ($checkIn && $checkOut && $expectedStart && $expectedEnd) {
                    $expectedMinutes = $expectedStart->diffInMinutes($expectedEnd);
                    // Match Attendance model: expectedStart->diffInMinutes(checkIn)
                    $lateMinutes = $expectedStart->diffInMinutes($checkIn);
                    $isEarlyLeaveDb = (bool) ($att->is_early_leave ?? false);
                    $isEarlyLeaveComputed = $checkOut->lt($expectedEnd);
                    $isEarlyLeave = $isEarlyLeaveDb ? true : $isEarlyLeaveComputed;

                    if ($isEarlyLeave) {
                        if ($lateMinutes > 15) {
                            $computedWorked = $checkIn->diffInMinutes($checkOut);
                        } else {
                            $computedWorked = $expectedStart->diffInMinutes($checkOut);
                        }
                    } elseif ($lateMinutes > 15) {
                        $computedWorked = $checkIn->diffInMinutes($checkOut);
                    } else {
                        $computedWorked = $expectedMinutes;
                    }

                    if ($computedWorked < 0) $computedWorked = 0;
                }

                $hourlyRate = 20000;
                if ($att->staff) {
                    if ($att->staff->employment_status === 'probation') {
                        $hourlyRate = $att->staff->probation_hourly_wage ?? 15000;
                    } else {
                        $hourlyRate = $att->staff->official_hourly_wage ?? 20000;
                    }
                }

                $computedSalary = round(($computedWorked / 60) * $hourlyRate, 0);

                $storedWorked = isset($att->worked_minutes) ? (int) $att->worked_minutes : null;
                $storedSalary = isset($att->salary_amount) ? (float) $att->salary_amount : null;

                $mismatch = '';
                if (!is_null($storedWorked) && $storedWorked !== $computedWorked) {
                    $mismatch .= "worked_minutes mismatch (stored={$storedWorked},computed={$computedWorked}); ";
                }
                if (!is_null($storedSalary) && (int)$storedSalary !== (int)$computedSalary) {
                    $mismatch .= "salary_amount mismatch (stored={$storedSalary},computed={$computedSalary}); ";
                }

                if ($mismatch !== '') {
                    // backup original row + computed
                    $backupRow = [
                        $att->id,
                        $att->staff_id,
                        $att->staff->user->name ?? ($att->staff->user_id ?? 'N/A'),
                        $att->work_date,
                        $att->expected_check_in,
                        $att->expected_check_out,
                        $att->check_in,
                        $att->check_out,
                        $att->is_early_leave,
                        $att->early_leave_status,
                        $storedWorked,
                        $storedSalary,
                        $computedWorked,
                        $computedSalary,
                        $mismatch,
                        now()->toDateTimeString(),
                    ];
                    fputcsv($bfp, $backupRow);

                    // update DB within transaction per-attendance
                    DB::beginTransaction();
                    try {
                        $att->worked_minutes = $computedWorked;
                        $att->salary_amount = $computedSalary;
                        $att->save();
                        DB::commit();
                    } catch (\Exception $e) {
                        DB::rollBack();
                        $this->error('Failed updating attendance id ' . $att->id . ': ' . $e->getMessage());
                    }
                }
            }

            fclose($bfp);
            $this->info('Backup written to: ' . $backupPath);
            $this->info('Apply complete. Updated mismatched rows.');
        }

        return 0;
    }
}
