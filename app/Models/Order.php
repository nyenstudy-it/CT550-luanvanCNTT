<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Inventory;
use App\Models\ImportItem;
use App\Models\OrderCancellation;
use App\Models\OrderReturn;
use App\Models\OrderReturnImage;
use App\Models\Product;

class Order extends Model
{
    protected $fillable = [
        'customer_id',
        'receiver_name',
        'receiver_phone',
        'shipping_address',
        'note',
        'total_amount',
        'status',
        'shipping_fee',
        'discount_amount',
        'discount_code',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class); 
    }
    
   public function payment()
{
    return $this->hasOne(Payment::class)->latestOfMany();
}


    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function cancellation()
    {
        return $this->hasOne(OrderCancellation::class);
    }

    public function returnInfo()
    {
        return $this->hasOne(\App\Models\OrderReturn::class);
    }

    public function returns()
    {
        return $this->hasMany(OrderReturn::class, 'order_id'); // OrderReturn là model của bảng trả hàng/hoàn tiền
    }

    public function order_items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
