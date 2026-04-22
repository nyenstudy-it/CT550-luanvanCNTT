<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\OrderReturnImage;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderReturn extends Model
{
    protected $fillable = [
        'order_id',
        'reason',
        'description',
        'status',
        'inspected_by',
        'inspected_at',
        'inspection_notes',
        'inspection_result',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'inspected_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function images()
    {
        return $this->hasMany(OrderReturnImage::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function inspector()
    {
        return $this->belongsTo(\App\Models\User::class, 'inspected_by');
    }

    /**
     * Get status label in Vietnamese
     */
    public function getStatusVNAttribute()
    {
        return match ($this->status ?? 'requested') {
            'requested' => 'Chờ duyệt hoàn hàng',
            'approved' => 'Duyệt rồi - Chờ khách gửi',
            'rejected' => 'Yêu cầu bị từ chối',
            'given_to_shipper' => 'Đã gửi cho shipper',
            'goods_received' => 'Kho đã nhận hàng - Chờ kiểm tra',
            'inspected_defective' => 'Đã kiểm tra: Hàng lỗi',
            'inspected_good' => 'Đã kiểm tra: Hàng đạt',
            'refunded' => 'Đã hoàn tiền',
            default => $this->status,
        };
    }

    public function getReasonVNAttribute()
    {
        return match ($this->reason) {
            'wrong_product' => 'Nhận nhầm sản phẩm',
            'product_defect' => 'Sản phẩm lỗi',
            'other' => 'Khác',
            // Legacy/deprecated reasons
            'defective' => 'Hỏng/khuyết điểm',
            'changed_mind' => 'Đổi ý',
            'change_mind' => 'Đổi ý',
            'refund_request_retroactive' => 'Giao sai sản phẩm',
            default => $this->reason,
        };
    }
}
