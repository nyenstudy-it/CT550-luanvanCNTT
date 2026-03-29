<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerMessage extends Model
{
    protected $fillable = [
        'customer_id',
        'product_id',
        'staff_id',
        'message',
        'sender_type',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Khách hàng gửi tin nhắn
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    // Nhân viên trả lời
    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    // Sản phẩm được hỏi
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
