<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Salary;
use Illuminate\Support\Facades\Auth;

class SalaryController extends Controller
{
    // ADMIN xem bảng lương
    public function index(Request $request)
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        $month = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;

        $salaries = Salary::with('staff.user')
            ->where('month', $month)
            ->where('year', $year)
            ->get();

        return view('admin.staff.salaries', compact(
            'salaries',
            'month',
            'year'
        ));
    }

    //  tính lương theo tháng
    public function calculate(Request $request)
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020'
        ]);

        $month = $request->month;
        $year = $request->year;

        // Lấy tất cả ca đã hoàn thành trong tháng
        $attendances = Attendance::whereMonth('work_date', $month)
            ->whereYear('work_date', $year)
            ->whereNotNull('check_in')
            ->whereNotNull('check_out')
            ->get()
            ->groupBy('staff_id');

        foreach ($attendances as $staffId => $records) {

            $totalMinutes = $records->sum('worked_minutes');

            // Lấy lương theo giờ (có thể lấy từ bảng staffs sau này)
            $hourlyRate = 25000;

            $totalSalary = ($totalMinutes / 60) * $hourlyRate;

            Salary::updateOrCreate(
                [
                    'staff_id' => $staffId,
                    'month' => $month,
                    'year' => $year
                ],
                [
                    'total_hours' => round($totalMinutes / 60, 2),
                    'total_salary' => round($totalSalary, 0)
                ]
            );
        }

        return back()->with('success', 'Tính lương thành công');
    }
}
