<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Staff;
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

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id', 'user_id');
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
        $end   = Carbon::parse($this->work_date . ' ' . $this->expected_check_out);

        if ($this->check_in && $this->check_out) {
            return 'completed';
        }

        if ($this->check_in && !$this->check_out) {
            return 'working';
        }

        // quá giờ 2 tiếng mà chưa check-in
        if (!$this->check_in && $now->gt($start->copy()->addHours(2))) {
            return 'absent';
        }

        return 'scheduled';
    }
}