<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đơn hàng #{{ $order->id }}</title>
</head>

<body style="margin:0;padding:0;background:#f4f8f2;font-family:Arial,sans-serif;color:#1f2937;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0"
        style="background:#f4f8f2;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="680" cellspacing="0" cellpadding="0"
                    style="width:100%;max-width:680px;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e5e7eb;">
                    <tr>
                        <td
                            style="background:linear-gradient(135deg,#7fad39 0%,#628f2c 100%);padding:22px 24px;color:#ffffff;">
                            <div style="font-size:12px;letter-spacing:1.2px;text-transform:uppercase;opacity:0.9;">SEN
                                HỒNG OCOP</div>
                            <h1 style="margin:8px 0 0;font-size:24px;line-height:1.3;">{{ $headline }}</h1>
                            <p style="margin:8px 0 0;font-size:14px;opacity:0.95;">Mã đơn hàng #{{ $order->id }}</p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:22px 24px 6px;">
                            <p style="margin:0 0 10px;font-size:15px;">Xin chào
                                <strong>{{ optional($order->customer->user)->name ?? 'Quý khách' }}</strong>,
                            </p>
                            <p style="margin:0 0 12px;font-size:14px;line-height:1.6;color:#4b5563;">{{ $summaryText }}
                            </p>

                            @if($mailType !== 'created')
                                <div
                                    style="background:#f0f8e8;border:1px solid #d5e8bd;border-radius:10px;padding:12px 14px;margin-bottom:12px;">
                                    <p style="margin:0;font-size:13px;color:#3f6212;">
                                        <strong>Trạng thái:</strong>
                                        {{ $statusLabel($oldStatus) }}
                                        <span style="display:inline-block;margin:0 6px;color:#6b7280;">-></span>
                                        {{ $statusLabel($newStatus) }}
                                    </p>
                                </div>
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:6px 24px 0;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0"
                                style="border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;">
                                <tr>
                                    <td colspan="2"
                                        style="padding:12px 14px;background:#f9fafb;font-size:14px;font-weight:700;color:#111827;">
                                        Thông tin giao hàng</td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 14px;font-size:13px;color:#6b7280;width:160px;">Người nhận
                                    </td>
                                    <td style="padding:10px 14px;font-size:13px;color:#111827;">
                                        {{ $order->receiver_name }}
                                    </td>
                                </tr>
                                <tr>
                                    <td
                                        style="padding:10px 14px;font-size:13px;color:#6b7280;border-top:1px solid #f3f4f6;">
                                        Số điện thoại</td>
                                    <td
                                        style="padding:10px 14px;font-size:13px;color:#111827;border-top:1px solid #f3f4f6;">
                                        {{ $order->receiver_phone }}
                                    </td>
                                </tr>
                                <tr>
                                    <td
                                        style="padding:10px 14px;font-size:13px;color:#6b7280;border-top:1px solid #f3f4f6;">
                                        Địa chỉ</td>
                                    <td
                                        style="padding:10px 14px;font-size:13px;color:#111827;border-top:1px solid #f3f4f6;">
                                        {{ $order->shipping_address }}
                                    </td>
                                </tr>
                                <tr>
                                    <td
                                        style="padding:10px 14px;font-size:13px;color:#6b7280;border-top:1px solid #f3f4f6;">
                                        Thanh toán</td>
                                    <td
                                        style="padding:10px 14px;font-size:13px;color:#111827;border-top:1px solid #f3f4f6;">
                                        {{ $paymentMethodLabel(optional($order->payment)->method) }} /
                                        {{ $paymentStatusLabel(optional($order->payment)->status) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td
                                        style="padding:10px 14px;font-size:13px;color:#6b7280;border-top:1px solid #f3f4f6;">
                                        Trạng thái đơn</td>
                                    <td
                                        style="padding:10px 14px;font-size:13px;color:#111827;border-top:1px solid #f3f4f6;">
                                        <strong>{{ $statusLabel($order->status) }}</strong>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:16px 24px 0;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0"
                                style="border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;">
                                <tr style="background:#f9fafb;">
                                    <th align="left" style="padding:12px 14px;font-size:13px;color:#374151;">Sản phẩm
                                    </th>
                                    <th align="right" style="padding:12px 14px;font-size:13px;color:#374151;">SL</th>
                                    <th align="right" style="padding:12px 14px;font-size:13px;color:#374151;">Đơn giá
                                    </th>
                                    <th align="right" style="padding:12px 14px;font-size:13px;color:#374151;">Thành tiền
                                    </th>
                                </tr>
                                @foreach($order->items as $item)
                                    <tr>
                                        <td
                                            style="padding:10px 14px;font-size:13px;color:#111827;border-top:1px solid #f3f4f6;">
                                            {{ optional(optional($item->variant)->product)->name ?? ('Biến thể #' . $item->product_variant_id) }}
                                        </td>
                                        <td align="right"
                                            style="padding:10px 14px;font-size:13px;color:#111827;border-top:1px solid #f3f4f6;">
                                            {{ (int) $item->quantity }}
                                        </td>
                                        <td align="right"
                                            style="padding:10px 14px;font-size:13px;color:#111827;border-top:1px solid #f3f4f6;">
                                            {{ number_format((float) $item->price, 0, ',', '.') }} VND
                                        </td>
                                        <td align="right"
                                            style="padding:10px 14px;font-size:13px;color:#111827;border-top:1px solid #f3f4f6;">
                                            {{ number_format((float) ($item->subtotal ?? ($item->price * $item->quantity)), 0, ',', '.') }}
                                            VND
                                        </td>
                                    </tr>
                                @endforeach
                                <tr style="background:#f9fafb;">
                                    <td colspan="3" align="right"
                                        style="padding:12px 14px;font-size:13px;color:#374151;border-top:1px solid #e5e7eb;">
                                        <strong>Tổng thanh toán</strong>
                                    </td>
                                    <td align="right"
                                        style="padding:12px 14px;font-size:14px;color:#111827;border-top:1px solid #e5e7eb;">
                                        <strong>{{ number_format((float) $order->total_amount, 0, ',', '.') }}
                                            VND</strong>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:18px 24px 26px;">
                            <p style="margin:0;font-size:12px;color:#6b7280;line-height:1.6;">
                                Cảm ơn bạn đã mua sắm tại SEN HỒNG OCOP.<br>
                                Nếu cần hỗ trợ, vui lòng liên hệ bộ phận chăm sóc khách hàng của shop.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>