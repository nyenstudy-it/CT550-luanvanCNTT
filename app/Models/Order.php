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
        'previous_status',
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


    /**
     * Relation to the `users` table for queries and eager loading.
     * Use `customerUser()` when you need a proper Relation (whereHas, with).
     */
    public function customerUser()
    {
        return $this->belongsTo(\App\Models\User::class, 'customer_id');
    }

    /**
     * Relation to the `customers` table.
     * Maps `orders.customer_id` -> `customers.user_id` so it can be used
     * with `with('customer')`, `whereHas('customer')`, eager loading, etc.
     */
    public function customer()
    {
        return $this->belongsTo(\App\Models\Customer::class, 'customer_id', 'user_id');
    }

    /**
     * Backward-compatible property accessor: `$order->customer`.
     * Prefer returning a `Customer` model (if exists where customers.user_id = orders.customer_id).
     * Fallback to the `User` model when no Customer record exists.
     */
    public function getCustomerAttribute()
    {
        // If a `customer` relation exists or was eager-loaded, prefer it
        try {
            if (method_exists($this, 'customer')) {
                $rel = $this->getRelationValue('customer');
                if ($rel) return $rel;
            }
        } catch (\Exception $e) {
            // ignore and fallback
        }

        // Fallback: if there's a Customer record keyed by user_id, return it
        if (isset($this->attributes['customer_id']) && $this->attributes['customer_id'] !== null) {
            $cust = \App\Models\Customer::where('user_id', $this->attributes['customer_id'])->first();
            if ($cust) return $cust;
        }

        // Last fallback: return the related User model (if present)
        return $this->customerUser()->getResults();
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
