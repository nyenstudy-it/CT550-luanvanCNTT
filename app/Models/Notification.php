<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\User;

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
            'new_order' => 'Có đơn hàng mới cần xử lý',
            'new_import' => 'Có phiếu nhập kho mới',
            'new_review' => 'Có đánh giá mới từ khách hàng',
            'review_approved' => 'Đánh giá của bạn đã được duyệt',
            'review_rejected' => 'Đánh giá của bạn đã bị từ chối',
            'attendance_check_in' => 'Nhân viên vừa chấm công vào ca',
            'cashier_stats_update' => 'Dữ liệu thống kê thu ngân vừa được cập nhật',
            'chat_customer_message' => 'Khách hàng vừa gửi tin nhắn mới',
            'chat_staff_reply' => 'Cửa hàng vừa phản hồi tin nhắn của bạn',
            'order_success' => 'Đặt hàng thành công',
            'order_cancel' => 'Đơn hàng đã bị huỷ',
            'order_completed' => 'Đơn hàng đã giao thành công',
            'order_payment_success' => 'Thanh toán đơn hàng thành công',
            'refund_request' => 'Yêu cầu hoàn hàng đang được xử lý',
            'order_refund' => 'Đơn hàng đã được hoàn tiền',
            'refund_rejected' => 'Yêu cầu hoàn hàng đã bị từ chối',
            'return_shipped' => 'Khách hàng đã gửi hàng hoàn',
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

        if ((str_contains($text, 'hoàn tiền') || str_contains($text, 'hoàn hàng')) && str_contains($text, 'xử lý')) {
            return 'Yêu cầu hoàn hàng đang được xử lý';
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
            'order_payment_success',
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

        if (in_array($this->type, ['inventory_out_of_stock', 'inventory_low_stock', 'inventory_expired', 'inventory_expiring_soon', 'inventory_stale_stock'])) {
            return in_array($user->role, ['admin', 'staff', 'order_staff', 'warehouse'])
                ? route('admin.inventories.list')
                : route('customer.notifications');
        }

        if ($this->type === 'new_import') {
            return in_array($user->role, ['admin', 'staff', 'order_staff', 'warehouse'])
                ? route('admin.imports.list')
                : route('customer.notifications');
        }

        if ($this->type === 'new_review') {
            return in_array($user->role, ['admin', 'staff', 'order_staff', 'warehouse'])
                ? route('admin.reviews')
                : route('customer.notifications');
        }

        if (in_array($this->type, ['review_approved', 'review_rejected'])) {
            return in_array($user->role, ['admin', 'staff', 'order_staff', 'warehouse'])
                ? route('admin.reviews')
                : route('customer.notifications');
        }

        if ($this->type === 'attendance_check_in') {
            return in_array($user->role, ['admin', 'staff', 'order_staff', 'warehouse'])
                ? route('admin.attendances.index')
                : route('customer.notifications');
        }

        if ($this->type === 'cashier_stats_update') {
            return in_array($user->role, ['admin', 'staff', 'order_staff', 'warehouse'])
                ? route('admin.dashboard')
                : route('customer.notifications');
        }

        if ($this->type === 'chat_customer_message') {
            return in_array($user->role, ['admin', 'staff', 'order_staff', 'warehouse'])
                ? route('admin.dashboard', ['open_admin_chat' => 1, 'customer' => $this->related_id])
                : route('customer.notifications');
        }

        if ($this->type === 'chat_staff_reply') {
            return $user->role === 'customer'
                ? route('pages.trangchu', ['open_store_chat' => 1])
                : route('admin.dashboard', ['open_admin_chat' => 1]);
        }

        if ($this->type === 'return_shipped') {
            return in_array($user->role, ['admin', 'staff', 'order_staff', 'warehouse'])
                ? route('admin.orders.detail', $this->related_id)
                : route('customer.notifications');
        }

        // DEFAULT
        return in_array($user->role, ['admin', 'staff', 'order_staff', 'warehouse'])
            ? route('admin.notifications')
            : route('customer.notifications');
    }

    public static function recipientIdsForGroups(array $groups): array
    {
        $groups = array_values(array_unique(array_filter($groups)));

        if (empty($groups)) {
            return [];
        }

        $ids = [];

        if (in_array('admin', $groups, true)) {
            $ids = array_merge($ids, User::query()->where('role', 'admin')->pluck('id')->all());
        }

        $staffPositionGroups = array_values(array_intersect($groups, ['cashier', 'warehouse', 'order_staff']));
        if (!empty($staffPositionGroups)) {
            $ids = array_merge(
                $ids,
                User::query()
                    ->where('role', 'staff')
                    ->whereHas('staff', function ($query) use ($staffPositionGroups) {
                        $query->whereIn('position', $staffPositionGroups);
                    })
                    ->pluck('id')
                    ->all()
            );

            // Backward-compatible support if some environments still store role by position.
            $ids = array_merge(
                $ids,
                User::query()
                    ->whereIn('role', $staffPositionGroups)
                    ->pluck('id')
                    ->all()
            );
        }

        return array_values(array_unique($ids));
    }

    public static function createForRecipients(array $recipientIds, array $payload): void
    {
        $recipientIds = array_values(array_unique(array_filter($recipientIds)));

        if (empty($recipientIds)) {
            return;
        }

        foreach ($recipientIds as $recipientId) {
            $notification = static::firstOrNew([
                'user_id' => $recipientId,
                'type' => $payload['type'],
                'related_id' => $payload['related_id'] ?? null,
            ]);

            $oldTitle = $notification->title;
            $oldContent = $notification->content;

            $notification->title = $payload['title'];
            $notification->content = $payload['content'] ?? null;

            if (!$notification->exists) {
                $notification->is_read = false;
            } elseif ($oldTitle !== $notification->title || $oldContent !== $notification->content) {
                $notification->is_read = false;
            }

            $notification->save();
        }
    }
}
