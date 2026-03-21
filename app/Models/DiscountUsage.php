<?php

namespace App\Models;
use App\Models\Customer;
use App\Models\Discount;

use Illuminate\Database\Eloquent\Model;

class DiscountUsage extends Model
{
    protected $fillable = [
        'discount_id',
        'user_id',
        'order_id',
    ];

    public function discount()
    {
        return $this->belongsTo(Discount::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    public function order()
    {
        return $this->belongsTo(\App\Models\Order::class);
    }
}
