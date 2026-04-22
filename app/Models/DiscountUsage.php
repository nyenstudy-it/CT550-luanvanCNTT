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

    /**
     * NOTE: DB migration stores `user_id` (references `users.id`).
     * Keep method name `customer()` for backward compatibility but
     * return the related `User` model based on `user_id`.
     */
    public function customer()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
    public function order()
    {
        return $this->belongsTo(\App\Models\Order::class);
    }
}
