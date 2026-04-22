<?php

namespace App\Services;

use App\Mail\OrderStatusMail;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OrderEmailService
{
    public function sendOrderCreatedEmail(int $orderId): void
    {
        $order = $this->loadOrderForMail($orderId);
        if (!$order) {
            return;
        }

        $this->sendToCustomer($order, new OrderStatusMail($order, 'created'));
    }

    public function sendOrderStatusChangedEmail(int $orderId, ?string $oldStatus, string $newStatus): void
    {
        $order = $this->loadOrderForMail($orderId);
        if (!$order) {
            return;
        }

        if ($oldStatus === $newStatus) {
            return;
        }

        $this->sendToCustomer($order, new OrderStatusMail($order, 'status_changed', $oldStatus, $newStatus));
    }

    private function loadOrderForMail(int $orderId): ?Order
    {
        return Order::query()
            ->with([
                'customer.user',
                'items.variant.product',
                'payment',
            ])
            ->find($orderId);
    }

    private function sendToCustomer(Order $order, OrderStatusMail $mail): void
    {
        $email = trim((string) optional(optional($order->customer)->user)->email);

        if ($email === '') {
            return;
        }

        try {
            Mail::to($email)->send($mail);
        } catch (\Throwable $e) {
            Log::warning('Failed to send order email', [
                'order_id' => $order->id,
                'mail_type' => $mail->mailType,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
