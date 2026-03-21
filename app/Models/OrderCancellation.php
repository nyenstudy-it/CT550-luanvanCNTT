<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderCancellation extends Model
{
    protected $fillable = [
        'order_id',
        'cancelled_by',
        'reason',
        'cancelled_at'
    ];
}
