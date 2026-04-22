<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Salary;

class Staff extends Model
{
    use HasFactory;

    protected $table = 'staffs';

    protected $primaryKey = 'user_id';
    public $incrementing = false;
    protected $keyType = 'int';

    public $timestamps = false;
    protected $fillable = [
        'user_id',
        'phone',
        'date_of_birth',
        'address',
        'position',
        'start_date',
        'probation_start',
        'probation_end',
        'employment_status',
        'probation_hourly_wage',
        'official_hourly_wage',
        'created_at',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'start_date' => 'date',
        'probation_start' => 'date',
        'probation_end' => 'date',
    ];

    /**
     * Relation to User (auth profile).
     * - `staffs.user_id` references `users.id`.
     * Use `$staff->user` to access authentication/profile fields (name, email).
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * HR relation: attendances for this staff profile.
     * - DB mapping: `attendances.staff_id` stores `users.id` values and
     *   is defined as FK -> `staffs.user_id` in migrations.
     * - This returns Attendance models where `attendances.staff_id = staffs.user_id`.
     * Use `$staff->attendances` when you need HR-level attendance data
     * (position, wages, employment_status come from `staffs`).
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'staff_id', 'user_id');
    }

    public function isResigned()
    {
        return $this->employment_status === 'resigned';
    }
    public function salaries()
    {
        return $this->hasMany(Salary::class, 'staff_id', 'user_id');
    }
}
