<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Staff;
use App\Models\User;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_id',
        'work_date',
        'expected_check_in',
        'expected_check_out',
        'check_in',
        'check_out',
        'shift',
        'is_late',
    ];

    protected $appends = [
        'worked_minutes',
        'salary_amount',
        'is_early_leave',
        'computed_status'
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id', 'user_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }


    public function getStatusAttribute()
    {
        if ($this->check_in && $this->check_out) {
            return 'Đã hoàn thành';
        }

        if ($this->check_in && !$this->check_out) {
            return 'Đang làm';
        }

        if (!$this->check_in && now()->toDateString() > $this->work_date) {
            return 'Vắng mặt';
        }

        return 'Đã phân ca';
    }

    public function getComputedStatusAttribute()
    {
        $now = Carbon::now('Asia/Ho_Chi_Minh');

        $start = Carbon::parse($this->work_date . ' ' . $this->expected_check_in);

        if ($this->check_in && $this->check_out) {
            return 'completed';
        }

        if ($this->check_in && !$this->check_out) {
            return 'working';
        }

        // quá 2 tiếng chưa check-in => vắng
        if (!$this->check_in && $now->gt($start->copy()->addHours(2))) {
            return 'absent';
        }

        return 'scheduled';
    }

    public function getWorkedMinutesAttribute()
    {
        if (!$this->check_in || !$this->check_out) {
            return 0;
        }

        $shiftStart = Carbon::parse($this->work_date . ' ' . $this->expected_check_in);
        $shiftEnd   = Carbon::parse($this->work_date . ' ' . $this->expected_check_out);

        $checkIn  = Carbon::parse($this->check_in);
        $checkOut = Carbon::parse($this->check_out);

        // Nếu check-in sau khi ca kết thúc → 0 giờ
        if ($checkIn->gte($shiftEnd)) {
            return 0;
        }

        // ===== Clamp check-in =====
        if ($checkIn->lte($shiftStart)) {
            $effectiveStart = $shiftStart;
        } else {
            $lateMinutes = $shiftStart->diffInMinutes($checkIn);

            // Trễ <= 15 phút vẫn tính full
            $effectiveStart = $lateMinutes <= 15 ? $shiftStart : $checkIn;
        }

        // ===== Clamp check-out =====
        if ($checkOut->gte($shiftEnd)) {
            $effectiveEnd = $shiftEnd;
        } else {
            $effectiveEnd = $checkOut;
        }

        // Bảo vệ tuyệt đối không cho âm
        if ($effectiveEnd->lessThanOrEqualTo($effectiveStart)) {
            return 0;
        }

        return abs($effectiveEnd->diffInMinutes($effectiveStart));
    }

    public function getSalaryAmountAttribute()
    {
        $hourlyRate = 25000;

        if ($this->worked_minutes <= 0) {
            return 0;
        }

        return round(($this->worked_minutes / 60) * $hourlyRate);
    }

    public function getIsEarlyLeaveAttribute()
    {
        if (!$this->check_out) {
            return false;
        }

        return $this->check_out < $this->expected_check_out;
    }
}
