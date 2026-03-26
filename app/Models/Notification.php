<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;

class Notification extends Model
{
    protected $fillable = [
        'type',
        'title',
        'content',
        'user_id',
        'related_id',
        'url',
        'is_read'
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'related_id');
    }

    public function getDisplayContentAttribute(): string
    {
        $content = trim((string) $this->content);

        return match ($this->type) {
            'order_success' => 'Đặt hàng thành công',
            'order_cancel' => 'Đơn hàng đã bị huỷ',
            'order_completed' => 'Đơn hàng đã giao thành công',
            'refund_request' => 'Yêu cầu hoàn tiền đang được xử lý',
            'order_refund' => 'Đơn hàng đã được hoàn tiền',
            'refund_rejected' => 'Yêu cầu hoàn tiền đã bị từ chối',
            'order_status_change' => $this->normalizeOrderStatusMessage($content),
            'inventory_out_of_stock' => 'Sản phẩm đã hết hàng',
            'inventory_low_stock' => 'Sản phẩm sắp hết hàng',
            'inventory_expired' => 'Sản phẩm đã hết hạn sử dụng',
            'inventory_expiring_soon' => 'Sản phẩm sắp hết hạn sử dụng',
            'inventory_stale_stock' => 'Sản phẩm tồn kho lâu',
            default => ($content !== '' ? $content : (string) $this->title),
        };
    }

    private function normalizeOrderStatusMessage(string $content): string
    {
        $text = mb_strtolower($content, 'UTF-8');

        if (str_contains($text, 'xác nhận') || str_contains($text, 'confirmed')) {
            return 'Đơn hàng đã được xác nhận';
        }

        if (str_contains($text, 'đang được giao') || str_contains($text, 'đang giao') || str_contains($text, 'shipping')) {
            return 'Đơn hàng đang được giao';
        }

        if (str_contains($text, 'giao thành công') || str_contains($text, 'đã được nhận') || str_contains($text, 'completed')) {
            return 'Đơn hàng đã giao thành công';
        }

        if (str_contains($text, 'hoàn tiền') && str_contains($text, 'xử lý')) {
            return 'Yêu cầu hoàn tiền đang được xử lý';
        }

        if (str_contains($text, 'đã được hoàn tiền') || str_contains($text, 'refunded')) {
            return 'Đơn hàng đã được hoàn tiền';
        }

        if (str_contains($text, 'huỷ') || str_contains($text, 'hủy') || str_contains($text, 'cancelled')) {
            return 'Đơn hàng đã bị huỷ';
        }

        return $content !== '' ? $content : 'Trạng thái đơn hàng đã được cập nhật';
    }

    public function getUrlAttribute()
    {
        $user = Auth::user();

        if (!$user) return '#';

        $orderTypes = [
            'order_success',
            'order_status_change',
            'order_cancel',
            'order_completed',
            'refund_request',
            'order_refund',
            'refund_rejected',
            'new_order'
        ];

        if (in_array($this->type, $orderTypes)) {

            // ADMIN / STAFF
            if (in_array($user->role, ['admin', 'staff', 'order_staff', 'warehouse'])) {
                return $this->related_id
                    ? route('admin.orders.detail', $this->related_id)
                    : route('admin.notifications');
            }

            // CUSTOMER
            return $this->related_id
                ? route('orders.detail', $this->related_id)
                : route('customer.notifications');
        }

        // DEFAULT
        return in_array($user->role, ['admin', 'staff', 'order_staff', 'warehouse'])
            ? route('admin.notifications')
            : route('customer.notifications');
    }
}
