<?php

namespace App\Http\Controllers;

use App\Models\Salary;
use App\Models\Attendance;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SalaryController extends Controller
{
    public function index()
    {
        $query = Salary::with('staff')
            ->whereHas('staff.user', function ($q) {
                $q->whereNotIn('role', ['admin']);
            });

        if (request('month')) {
            $query->where('month', request('month'));
        }

        if (request('year')) {
            $query->where('year', request('year'));
        }

        $salaries = $query->orderByDesc('year')
            ->orderByDesc('month')
            ->get()
            ->map(function ($salary) {
                $workDays = Attendance::where('staff_id', $salary->staff_id)
                    ->whereMonth('work_date', $salary->month)
                    ->whereYear('work_date', $salary->year)
                    ->where('is_completed', 1)
                    ->distinct('work_date')
                    ->count('work_date');

                $salary->work_days = $workDays;
                return $salary;
            });

        $selectedYear  = request('year',  now()->year);
        $selectedMonth = request('month', now()->month);

        $weeklyBreakdown = DB::table('attendances')
            ->join('staffs', 'staffs.user_id', '=', 'attendances.staff_id')
            ->join('users', 'users.id', '=', 'staffs.user_id')
            ->where('users.role', '!=', 'admin')
            ->whereMonth('work_date', $selectedMonth)
            ->whereYear('work_date', $selectedYear)
            ->where('attendances.is_completed', 1)
            ->whereNotNull('attendances.salary_amount')
            ->selectRaw('users.name as staff_name, WEEK(work_date, 1) as week_num, MIN(work_date) as week_start, SUM(attendances.worked_minutes) as total_minutes, SUM(attendances.salary_amount) as week_salary')
            ->groupBy('users.name', DB::raw('WEEK(work_date, 1)'))
            ->orderBy('users.name')
            ->orderBy('week_num')
            ->get()
            ->map(function ($row) {
                $row->total_hours = $row->total_minutes > 0 ? round($row->total_minutes / 60, 2) : 0;
                $row->week_label  = 'Tuần ' . \Carbon\Carbon::parse($row->week_start)->format('W') .
                    ' (' . \Carbon\Carbon::parse($row->week_start)->startOfWeek()->format('d/m') . ')';
                return $row;
            });

        return view('admin.salaries.index', compact('salaries', 'weeklyBreakdown', 'selectedMonth', 'selectedYear'));
    }

    public function monthlySalary($month, $year)
    {
        $month = (int) $month;
        $year  = (int) $year;

        return view('admin.attendances.monthly_salary', compact('month', 'year'));
    }

    /**
     * Tính và lưu lương tháng cho một nhân viên.
     *
     * Công thức:
     * - Tổng lương = giờ làm thực tế * lương/giờ
     * - Phạt = phạt đi trễ + phạt về sớm
     * - Thực lãnh = tổng lương - phạt + thưởng (nếu đủ điều kiện)
     */
    public function calculateMonthly($staffUserId, $month, $year)
    {
        $month = (int) $month;
        $year  = (int) $year;

        $staff = Staff::where('user_id', $staffUserId)->firstOrFail();

        $isProbation = $staff->probation_end && Carbon::parse($staff->probation_end)->isFuture();
        $hourlyWage = $isProbation ?
            ($staff->probation_hourly_wage ?? 50000) : ($staff->official_hourly_wage ?? 50000);

        $attendances = Attendance::where('staff_id', $staff->user_id)
            ->whereMonth('work_date', $month)
            ->whereYear('work_date', $year)
            ->where('is_completed', 1)
            ->get();

        if ($attendances->isEmpty()) {
            return back()->with('error', 'Không có dữ liệu chấm công tháng này!');
        }

        $totalWorkedMinutes = (int) $attendances->sum(function ($attendance) {
            return $attendance->worked_minutes ?? 0;
        });

        $totalSalary = ($totalWorkedMinutes / 60) * $hourlyWage;

        $lateCount = (int) $attendances->where('is_late', 1)->count();

        $earlyLeaveCount = (int) $attendances->where('is_early_leave', 1)->count();

        $penaltyAmount = 0;
        if ($lateCount > 5) {
            $penaltyAmount += 200000;
        }
        if ($earlyLeaveCount > 3) {
            $penaltyAmount += 200000;
        }

        $bonusAmount = 0;
        if ($lateCount <= 3 && $earlyLeaveCount <= 3) {
            $bonusAmount = 300000;
        }

        $finalSalary = $totalSalary - $penaltyAmount + $bonusAmount;

        Salary::updateOrCreate(
            [
                'staff_id' => $staff->user_id,
                'month'    => $month,
                'year'     => $year,
            ],
            [
                'total_minutes' => $totalWorkedMinutes,
                'total_hours'   => round($totalWorkedMinutes / 60, 2),
                'total_salary'  => round($totalSalary, 2),
                'late_count' => $lateCount,
                'early_leave_count' => $earlyLeaveCount,
                'absent_count' => 0,
                'penalty_amount' => round($penaltyAmount, 2),
                'bonus_amount' => round($bonusAmount, 2),
                'final_salary' => round($finalSalary, 2),
            ]
        );

        return back()->with('success', "Đã tính lương tháng $month/$year thành công! Lương: " . number_format($finalSalary, 0) . " đ");
    }
}
