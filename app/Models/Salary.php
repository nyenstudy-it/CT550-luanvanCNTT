<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Salary extends Model
{
    protected $fillable = [
        'staff_id',
        'month',
        'year',
        'total_minutes',
        'total_hours',
        'total_salary',
        'late_count',
        'early_leave_count',
        'absent_count',
        'penalty_amount',
        'bonus_amount',
        'final_salary',
    ];

    protected $appends = [
        'penalty_late',
        'penalty_early_leave',
        'bonus_diligent',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id', 'user_id');
    }

    /**
     * Tính phạt đi trễ: > 5 lần/tháng = -200.000đ
     */
    public function getPenaltyLateAttribute()
    {
        if ($this->late_count > 5) {
            return 200000;
        }
        return 0;
    }

    /**
     * Tính phạt về sớm: > 3 lần/tháng = -200.000đ
     */
    public function getPenaltyEarlyLeaveAttribute()
    {
        if ($this->early_leave_count > 3) {
            return 200000;
        }
        return 0;
    }

    /**
     * Tính thưởng chuyên cần:
     * Điều kiện: không đi trễ (<=3), không vắng không phép (0), không về sớm (<=3)
     * Thưởng: +300.000đ
     */
    public function getBonusDiligentAttribute()
    {
        // Không trễ quá 3 lần, không vắng không phép, không về sớm quá 3 lần
        if ($this->late_count <= 3 && $this->absent_count == 0 && $this->early_leave_count <= 3) {
            return 300000;
        }
        return 0;
    }

    /**
     * Tính tổng phạt
     */
    public function getTotalPenaltyAttribute()
    {
        return $this->penalty_late + $this->penalty_early_leave;
    }

    /**
     * Tính tổng thưởng
     */
    public function getTotalBonusAttribute()
    {
        return $this->bonus_diligent;
    }

    /**
     * Tính lương cuối cùng
     */
    public function getFinalSalaryCalculatedAttribute()
    {
        $base = $this->total_salary ?? 0;
        $penalty = $this->total_penalty;
        $bonus = $this->total_bonus;

        return $base - $penalty + $bonus;
    }

    /**
     * Tính và lưu lương tháng theo dữ liệu chấm công.
     */
    public static function calculateMonthly($staff_id, $month, $year)
    {
        $attendances = Attendance::where('staff_id', $staff_id)
            ->whereYear('work_date', $year)
            ->whereMonth('work_date', $month)
            ->get();

        $lateCount = $attendances->where('is_late', true)->count();
        // `is_early_leave` là accessor nên dùng filter() để đếm.
        $earlyLeaveCount = $attendances->filter(fn($a) => $a->is_early_leave)->count();
        $absentCount = $attendances->where('computed_status', 'absent')->count();

        $totalSalary = $attendances->sum('salary_amount');
        $totalMinutes = $attendances->sum('worked_minutes');

        // Tính phạt và thưởng
        $penaltyLate = $lateCount > 5 ? 200000 : 0;
        $penaltyEarlyLeave = $earlyLeaveCount > 3 ? 200000 : 0;
        $bonusDiligent = ($lateCount <= 3 && $absentCount == 0 && $earlyLeaveCount <= 3) ? 300000 : 0;

        $totalPenalty = $penaltyLate + $penaltyEarlyLeave;
        $totalBonus = $bonusDiligent;
        $finalSalary = $totalSalary - $totalPenalty + $totalBonus;

        // Upsert salary record
        $salary = self::updateOrCreate(
            [
                'staff_id' => $staff_id,
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

        return $salary;
    }
}
