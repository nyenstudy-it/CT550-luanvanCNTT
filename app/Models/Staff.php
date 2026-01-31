<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
     * Quan hệ: Staff thuộc về User
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Quan hệ: Staff có nhiều ca làm
     */
    public function attendances()
     {
        return $this->hasMany(Attendance::class, 'staff_id', 'user_id');
     }


    // helper
    public function isResigned()
    {
        return $this->employment_status === 'resigned';
    }
    // /**
    //  * Quan hệ: Staff có nhiều bảng lương
    //  */
    // public function salaries()
    // {
    //     return $this->hasMany(Salary::class, 'staff_id', 'user_id');
    // }
}
