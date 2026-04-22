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
        'check_in_ip',
        'check_in_network_type',
        'check_in_verification_method',
        'early_leave_reason',
        'early_leave_status',
        'early_leave_approved_by',
        'early_leave_approved_at',
        'is_auto_checkout_forced',
        'worked_minutes',
        'late_minutes',
        'scenario_type',
        'salary_amount',
        'is_completed',
    ];

    protected $appends = [
        'is_early_leave',
        'computed_status'
    ];

    /**
     * Quan hệ đến hồ sơ nhân sự (`staffs`).
     *
     * Khóa ngoại: `attendances.staff_id` -> `staffs.user_id`.
     */
    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id', 'user_id');
    }

    /**
     * Quan hệ nhanh đến bảng `users` (phục vụ lấy thông tin tài khoản).
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    // Luôn tính `is_early_leave` và `is_late` theo accessor để tránh lệch dữ liệu lưu DB.
    public function getAttribute($key)
    {
        if ($key === 'is_early_leave') {
            return $this->getIsEarlyLeaveAttribute();
        }
        if ($key === 'is_late') {
            return $this->getIsLateAttribute();
        }
        return parent::getAttribute($key);
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

        $shiftEnd = Carbon::parse(
            $this->work_date . ' ' . $this->expected_check_out,
            'Asia/Ho_Chi_Minh'
        );

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
        // Về sớm khi giờ ra thực tế < giờ ra dự kiến.
        if (!$this->attributes['check_out'] || !$this->attributes['expected_check_out']) {
            return false;
        }

        $expectedEnd = Carbon::parse(
            $this->attributes['work_date'] . ' ' . $this->attributes['expected_check_out'],
            'Asia/Ho_Chi_Minh'
        );

        $checkOut = Carbon::parse(
            $this->attributes['work_date'] . ' ' . $this->attributes['check_out'],
            'Asia/Ho_Chi_Minh'
        );

        return $checkOut->lt($expectedEnd);
    }
    public function getLateMinutesAttribute()
    {
        if (!$this->attributes['check_in'] || !$this->attributes['expected_check_in']) return 0;

        $shiftStart = Carbon::parse(
            $this->attributes['work_date'] . ' ' . $this->attributes['expected_check_in'],
            'Asia/Ho_Chi_Minh'
        );

        $checkIn = Carbon::parse(
            $this->attributes['work_date'] . ' ' . $this->attributes['check_in'],
            'Asia/Ho_Chi_Minh'
        );

        if ($checkIn->lte($shiftStart)) return 0;

        return $shiftStart->diffInMinutes($checkIn);
    }

    public function getIsLateAttribute()
    {
        // Đi trễ khi số phút trễ > 15 phút.
        return $this->late_minutes > 15;
    }
    public function getWorkedHoursAttribute()
    {
        return $this->worked_minutes
            ? round($this->worked_minutes / 60, 2)
            : 0;
    }

    /**
     * Tính số phút làm việc theo quy tắc chấm công hiện hành.
     * Ưu tiên dùng giá trị đã lưu trong DB; nếu chưa có thì tính từ check-in/check-out.
     */
    public function getWorkedMinutesAttribute()
    {
        if (isset($this->attributes['worked_minutes']) && !is_null($this->attributes['worked_minutes'])) {
            return (int) $this->attributes['worked_minutes'];
        }

        if (empty($this->attributes['check_in']) || empty($this->attributes['check_out'])) {
            return 0;
        }

        $computed = $this->computeWorkedAndSalary();
        return isset($computed['worked_minutes']) ? (int)$computed['worked_minutes'] : 0;
    }

    /**
     * Tính lương theo giờ làm và mức lương/giờ của nhân viên.
     * Ưu tiên dùng `salary_amount` đã lưu trong DB nếu có.
     */
    public function getSalaryAmountAttribute()
    {
        if (isset($this->attributes['salary_amount']) && !is_null($this->attributes['salary_amount'])) {
            return (float) $this->attributes['salary_amount'];
        }

        // Nếu chưa lưu, tính từ worked_minutes
        if (!$this->worked_minutes || $this->worked_minutes <= 0) {
            return null;
        }

        try {
            if (!$this->staff || !$this->staff->exists) {
                return null;
            }

            $hourlyRate = 20000;

            if ($this->staff->employment_status === 'probation') {
                $hourlyRate = $this->staff->probation_hourly_wage
                    ? (int) $this->staff->probation_hourly_wage
                    : 15000;
            } else if ($this->staff->employment_status === 'official') {
                $hourlyRate = $this->staff->official_hourly_wage
                    ? (int) $this->staff->official_hourly_wage
                    : 20000;
            }

            $workedHours = $this->worked_minutes / 60;
            return round($workedHours * $hourlyRate, 0);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Tự tính và lưu `worked_minutes`/`salary_amount` khi thay đổi check-in/check-out.
     */
    protected static function booted()
    {
        static::saving(function (Attendance $att) {
            if ($att->isDirty('check_in') || $att->isDirty('check_out')) {
                $calc = $att->computeWorkedAndSalary();

                if (is_null($calc['worked_minutes'])) {
                    $att->attributes['worked_minutes'] = null;
                } else {
                    $att->attributes['worked_minutes'] = (int) $calc['worked_minutes'];
                }

                if (is_null($calc['salary_amount'])) {
                    $att->attributes['salary_amount'] = null;
                } else {
                    $att->attributes['salary_amount'] = (float) $calc['salary_amount'];
                }
            }
        });
    }

    /**
     * Tính lại `worked_minutes` và `salary_amount` từ dữ liệu hiện tại (không dùng giá trị đã lưu).
     */
    protected function computeWorkedAndSalary()
    {
        $expectedStart = null;
        $expectedEnd = null;
        if (!empty($this->attributes['work_date']) && !empty($this->attributes['expected_check_in'])) {
            try {
                $expectedStart = Carbon::parse($this->attributes['work_date'] . ' ' . $this->attributes['expected_check_in'], 'Asia/Ho_Chi_Minh');
            } catch (\Exception $e) {
                $expectedStart = null;
            }
        }
        if (!empty($this->attributes['work_date']) && !empty($this->attributes['expected_check_out'])) {
            try {
                $expectedEnd = Carbon::parse($this->attributes['work_date'] . ' ' . $this->attributes['expected_check_out'], 'Asia/Ho_Chi_Minh');
            } catch (\Exception $e) {
                $expectedEnd = null;
            }
        }

        $checkIn = null;
        if (!empty($this->attributes['check_in'])) {
            $rawIn = $this->attributes['check_in'];
            if (strpos($rawIn, '-') === false && !empty($this->attributes['work_date'])) {
                $rawIn = $this->attributes['work_date'] . ' ' . $rawIn;
            }
            try {
                $checkIn = Carbon::parse($rawIn, 'Asia/Ho_Chi_Minh');
            } catch (\Exception $e) {
                $checkIn = null;
            }
        }

        $checkOut = null;
        if (!empty($this->attributes['check_out'])) {
            $rawOut = $this->attributes['check_out'];
            if (strpos($rawOut, '-') === false && !empty($this->attributes['work_date'])) {
                $rawOut = $this->attributes['work_date'] . ' ' . $rawOut;
            }
            try {
                $checkOut = Carbon::parse($rawOut, 'Asia/Ho_Chi_Minh');
            } catch (\Exception $e) {
                $checkOut = null;
            }
        }

        if (!$checkIn || !$checkOut || !$expectedStart || !$expectedEnd) {
            return ['worked_minutes' => null, 'salary_amount' => null];
        }

        $expectedMinutes = $expectedStart->diffInMinutes($expectedEnd);
        $lateMinutes = max(0, $expectedStart->diffInMinutes($checkIn));

        $isEarlyLeaveDb = isset($this->attributes['is_early_leave']) ? (bool)$this->attributes['is_early_leave'] : false;
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

        $hourlyRate = 20000;
        try {
            if ($this->staff && $this->staff->employment_status === 'probation') {
                $hourlyRate = $this->staff->probation_hourly_wage ?? 15000;
            } elseif ($this->staff) {
                $hourlyRate = $this->staff->official_hourly_wage ?? 20000;
            }
        } catch (\Exception $e) {
            $hourlyRate = 20000;
        }

        $computedSalary = round(($computedWorked / 60) * $hourlyRate, 0);

        return ['worked_minutes' => (int)$computedWorked, 'salary_amount' => (float)$computedSalary];
    }

    /**
     * Trả về giá trị tính toán lại (không phụ thuộc dữ liệu đã lưu).
     */
    public function computedValues()
    {
        return $this->computeWorkedAndSalary();
    }
}
