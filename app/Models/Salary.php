<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Salary extends Model
{
    protected $fillable = [
        'staff_id',
        'month',
        'year',
        'total_minutes',
        'total_hours',
        'total_salary',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id', 'user_id');
    }
}
