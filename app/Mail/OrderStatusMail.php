<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public string $mailType,
        public ?string $oldStatus = null,
        public ?string $newStatus = null
    ) {}

    public function build(): self
    {
        return $this->subject($this->subjectLine())
            ->view('mail.orders.status')
            ->with([
                'statusLabel' => fn(?string $status) => $this->statusLabel($status),
                'paymentMethodLabel' => fn(?string $method) => $this->paymentMethodLabel($method),
                'paymentStatusLabel' => fn(?string $status) => $this->paymentStatusLabel($status),
                'headline' => $this->headline(),
                'summaryText' => $this->summaryText(),
            ]);
    }

    public function subjectLine(): string
    {
        if ($this->mailType === 'created') {
            return 'Xác nhận đơn hàng #' . $this->order->id;
        }

        if ($this->newStatus === 'cancelled') {
            return 'Thông báo hủy đơn #' . $this->order->id;
        }

        if ($this->newStatus === 'refund_requested') {
            return 'Đã tiếp nhận yêu cầu hoàn tiền đơn #' . $this->order->id;
        }

        if ($this->newStatus === 'refunded') {
            return 'Đã hoàn tiền đơn #' . $this->order->id;
        }

        if ($this->newStatus === 'completed') {
            return 'Đơn hàng #' . $this->order->id . ' đã hoàn thành';
        }

        return 'Cập nhật trạng thái đơn hàng #' . $this->order->id;
    }

    public function headline(): string
    {
        if ($this->mailType === 'created') {
            return 'Đơn hàng đã được tạo thành công';
        }

        return match ($this->newStatus) {
            'cancelled' => 'Đơn hàng đã được hủy',
            'refund_requested' => 'Yêu cầu hoàn tiền đang được xử lý',
            'refunded' => 'Đơn hàng đã được hoàn tiền',
            'confirmed' => 'Đơn hàng đã được xác nhận',
            'shipping' => 'Đơn hàng đang được giao',
            'completed' => 'Đơn hàng đã hoàn thành',
            default => 'Trạng thái đơn hàng đã được cập nhật',
        };
    }

    public function summaryText(): string
    {
        if ($this->mailType === 'created') {
            return 'Cảm ơn bạn đã đặt hàng tại SEN HỒNG OCOP. Chúng tôi đã tiếp nhận và đang xử lý đơn hàng của bạn.';
        }

        return 'Hệ thống vừa ghi nhận thay đổi trạng thái đơn hàng. Vui lòng theo dõi thông tin chi tiết bên dưới.';
    }

    public function statusLabel(?string $status): string
    {
        return match ($status) {
            'pending' => 'Chờ xử lý',
            'confirmed' => 'Đã xác nhận',
            'shipping' => 'Đang giao',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy',
            'refund_requested' => 'Yêu cầu hoàn tiền',
            'refunded' => 'Đã hoàn tiền',
            default => (string) $status,
        };
    }

    public function paymentMethodLabel(?string $method): string
    {
        return match (strtolower((string) $method)) {
            'cod' => 'Thanh toán khi nhận hàng (COD)',
            'momo' => 'Ví MoMo',
            '' => 'Chưa cập nhật',
            default => (string) $method,
        };
    }

    public function paymentStatusLabel(?string $status): string
    {
        return match (strtolower((string) $status)) {
            'pending' => 'Chờ thanh toán',
            'paid' => 'Đã thanh toán',
            'failed' => 'Thanh toán thất bại',
            'refunded' => 'Đã hoàn tiền',
            '' => 'Chưa cập nhật',
            default => (string) $status,
        };
    }
}
