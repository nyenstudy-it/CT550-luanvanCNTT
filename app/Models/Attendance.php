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
        // 'worked_minutes',
        // 'salary_amount',
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

        $shiftStart = Carbon::parse($this->work_date . ' ' . $this->expected_check_in);
        $shiftEnd   = Carbon::parse($this->work_date . ' ' . $this->expected_check_out);

        if ($this->check_in && $this->check_out) {
            return 'completed';
        }

        if ($this->check_in && !$this->check_out) {
            return 'working';
        }

        if (!$this->check_in && $now->greaterThan($shiftEnd)) {
            return 'absent';
        }

        return 'scheduled';
    }

    public function getIsEarlyLeaveAttribute()
    {
        if (!$this->check_out) {
            return false;
        }

        return $this->check_out < $this->expected_check_out;
    }
    public function getLateMinutesAttribute()
    {
        if (!$this->check_in) return 0;

        $shiftStart = Carbon::parse($this->work_date . ' ' . $this->expected_check_in);
        $checkIn = Carbon::parse($this->check_in);

        if ($checkIn->lte($shiftStart)) return 0;

        return $shiftStart->diffInMinutes($checkIn);
    }

    public function getWorkedHoursAttribute()
    {
        return $this->worked_minutes
            ? round($this->worked_minutes / 60, 2)
            : 0;
    }
}
