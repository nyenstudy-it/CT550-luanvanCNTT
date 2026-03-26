<?php

namespace App\Http\Controllers;

use App\Models\Salary;
use App\Models\Attendance;
use App\Models\Staff;
use Carbon\Carbon;

class SalaryController extends Controller
{
    public function index()
    {
        $query = Salary::with('staff');

        if (request('month')) {
            $query->where('month', request('month'));
        }

        if (request('year')) {
            $query->where('year', request('year'));
        }

        $salaries = $query->orderByDesc('year')
            ->orderByDesc('month')
            ->get();

        return view('admin.salaries.index', compact('salaries'));
    }


    public function calculateMonthly($staffUserId, $month, $year)
    {
        $month = (int) $month;
        $year  = (int) $year;

        $staff = Staff::where('user_id', $staffUserId)->firstOrFail();

        $attendances = Attendance::where('staff_id', $staff->user_id)
            ->whereMonth('work_date', $month)
            ->whereYear('work_date', $year)
            ->where('is_completed', 1)
            ->get();

        if ($attendances->isEmpty()) {
            return back()->with('error', 'Không có dữ liệu chấm công tháng này!');
        }

        $totalMinutes = (int) $attendances->sum('worked_minutes');
        $totalSalary  = (float) $attendances->sum('salary_amount');

        $totalHours = $totalMinutes > 0
            ? round($totalMinutes / 60, 2)
            : 0;

        Salary::updateOrCreate(
            [
                'staff_id' => $staff->user_id,
                'month'    => $month,
                'year'     => $year,
            ],
            [
                'total_minutes' => $totalMinutes,
                'total_hours'   => $totalHours,
                'total_salary'  => $totalSalary,
            ]
        );

        return back()->with('success', "Đã tính lương tháng $month/$year thành công!");
    }
}
