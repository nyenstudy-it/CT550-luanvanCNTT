<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AutoCloseAttendances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:auto-close';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically close attendances that forgot checkout after 2 hours past expected end';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $timezone = 'Asia/Ho_Chi_Minh';
            $now = Carbon::now($timezone);
            $today = $now->toDateString();

            // Chỉ tự chốt công từ hôm qua trở về trước (tránh chốt nhầm ca đang làm trong ngày).
            $yesterday = $now->copy()->subDay()->toDateString();
            $unclosed = Attendance::where('check_in', '!=', null)
                ->where('check_out', null)
                ->where('work_date', '<=', $yesterday)
                ->get();

            if ($unclosed->isEmpty()) {
                $this->info('✓ No unclosed attendances to auto-close');
                return Command::SUCCESS;
            }

            $closedCount = 0;
            $this->info("Found {$unclosed->count()} unclosed attendances");

            foreach ($unclosed as $attendance) {
                /** @var \App\Models\Attendance $attendance */
                try {
                    $expectedEnd = Carbon::parse(
                        $attendance->work_date . ' ' . $attendance->expected_check_out,
                        $timezone
                    );

                    // Tự chốt check-out về giờ kết ca dự kiến.
                    $attendance->check_out = $expectedEnd->format('H:i:s');
                    $attendance->is_completed = 1;
                    $attendance->is_auto_checkout_forced = 1;

                    $computed = $attendance->computedValues();
                    $attendance->worked_minutes = $computed['worked_minutes'] ?? null;
                    $attendance->salary_amount = $computed['salary_amount'] ?? null;
                    try {
                        $expectedStartTime = Carbon::parse(
                            $attendance->work_date . ' ' . $attendance->expected_check_in,
                            $timezone
                        );
                        $checkInTime = Carbon::parse($attendance->work_date . ' ' . $attendance->check_in, $timezone);
                        $lateMinutes = $expectedStartTime->diffInMinutes($checkInTime);
                        $attendance->is_late = $lateMinutes > 15 ? 1 : 0;
                    } catch (\Exception $e) {
                    }

                    $attendance->save();

                    $closedCount++;
                    $this->line("  ✓ Auto-closed: #{$attendance->id} ({$attendance->work_date}) - Salary: " .
                        number_format($attendance->salary_amount) . "đ");
                } catch (\Exception $e) {
                    $this->error("  ✗ Failed to auto-close #{$attendance->id}: " . $e->getMessage());
                    continue;
                }
            }

            $this->info("✓ Auto-closed {$closedCount} attendances");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Get hourly rate for the staff based on employment type
     */
    private function getHourlyRate($attendance)
    {
        // Get shift type from expected_check_in
        if ($attendance->expected_check_in == '05:00:00') {
            // Morning shift - 20,000đ/h
            return 20000;
        } elseif ($attendance->expected_check_in == '14:00:00') {
            // Afternoon shift - 15,000đ/h
            return 15000;
        } else {
            // Default
            return 15000;
        }
    }
}
