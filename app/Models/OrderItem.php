<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Order;
use App\Models\ProductVariant;


class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_variant_id',
        'quantity',
        'price',
        'subtotal',
        'cost_price',
        'batch_details'
    ];

    protected $casts = [
        'batch_details' => 'array',
    ];

    // Quan hệ với Order
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function product()
    {
        return $this->hasOneThrough(
            \App\Models\Product::class,   // Model đích
            \App\Models\ProductVariant::class, // Model trung gian
            'id',                         // Khóa trên ProductVariant (foreign key của OrderItem)
            'id',                         // Khóa trên Product
            'product_variant_id',         // Khóa local trên OrderItem
            'product_id'                  // Khóa local trên ProductVariant
        );
    }
}
