<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'method',
        'amount',
        'transaction_code',
        'status',
        'paid_at',
        'refund_amount',
        'refund_status',
        'refund_at'
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'refund_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
