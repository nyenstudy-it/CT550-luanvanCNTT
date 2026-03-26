<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\OrderReturnImage;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderReturn extends Model
{
    protected $fillable = ['order_id', 'reason', 'description'];

    public function images()
    {
        return $this->hasMany(OrderReturnImage::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function getReasonVNAttribute()
    {
        return match ($this->reason) {
            'wrong_product' => 'Sai sản phẩm',
            'defective' => 'Hỏng/khuyết điểm',
            'changed_mind' => 'Đổi ý',
            'other' => 'Khác',
            default => $this->reason,
        };
    }
}
