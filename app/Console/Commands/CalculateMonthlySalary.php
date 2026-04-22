<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Salary;
use App\Models\Staff;
use App\Models\Attendance;

class CalculateMonthlySalary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'salary:calculate {--month=3} {--year=2026}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate monthly salary with penalties and bonuses';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $month = $this->option('month');
        $year = $this->option('year');

        $this->info("=== CALCULATING MONTHLY SALARY FOR {$month}/{$year} ===\n");

        $staffs = Staff::all();

        $totalFinal = 0;

        foreach ($staffs as $staff) {
            $attendances = Attendance::where('staff_id', $staff->user_id)
                ->whereYear('work_date', $year)
                ->whereMonth('work_date', $month)
                ->get();

            $lateCount = $attendances->where('is_late', true)->count();
            $earlyLeaveCount = $attendances->filter(function ($a) {
                return $a->salary_amount && $a->salary_amount < 60000;
            })->count();
            $absentCount = $attendances->where('computed_status', 'absent')->count();
            $totalSalary = $attendances->sum('salary_amount');
            $totalMinutes = $attendances->sum('worked_minutes');

            $penaltyLate = $lateCount > 5 ? 200000 : 0;
            $penaltyEarlyLeave = $earlyLeaveCount > 3 ? 200000 : 0;
            $bonusDiligent = ($lateCount <= 3 && $absentCount == 0 && $earlyLeaveCount <= 3) ? 300000 : 0;

            $totalPenalty = $penaltyLate + $penaltyEarlyLeave;
            $totalBonus = $bonusDiligent;
            $finalSalary = $totalSalary - $totalPenalty + $totalBonus;

            $salary = Salary::updateOrCreate(
                [
                    'staff_id' => $staff->user_id,
                    'month' => $month,
                    'year' => $year,
                ],
                [
                    'total_minutes' => $totalMinutes,
                    'total_hours' => round($totalMinutes / 60, 2),
                    'total_salary' => $totalSalary,
                    'late_count' => $lateCount,
                    'early_leave_count' => $earlyLeaveCount,
                    'absent_count' => $absentCount,
                    'penalty_amount' => $totalPenalty,
                    'bonus_amount' => $totalBonus,
                    'final_salary' => $finalSalary,
                ]
            );

            $totalFinal += $finalSalary;

            $this->line("✓ {$staff->user->name}");
            $this->line("  Base: " . number_format($totalSalary, 0, ',', '.') . "đ | Late: {$lateCount}x (-" . number_format($penaltyLate, 0, ',', '.') . "đ) | Early: {$earlyLeaveCount}x (-" . number_format($penaltyEarlyLeave, 0, ',', '.') . "đ) | Bonus: +" . number_format($bonusDiligent, 0, ',', '.') . "đ");
            $this->line("  Final: " . number_format($finalSalary, 0, ',', '.') . "đ\n");
        }

        $this->warn("\n╔═══════════════════════════════════════════════════════╗");
        $this->warn("║  GRAND TOTAL: " . str_pad(number_format($totalFinal, 0, ',', '.') . "đ", 37) . "║");
        $this->warn("╚═══════════════════════════════════════════════════════╝");
    }
}
