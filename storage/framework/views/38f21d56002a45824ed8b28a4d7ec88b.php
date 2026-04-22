

<?php $__env->startSection('hero'); ?>
    <?php echo $__env->make('pages.components.hero', ['showBanner' => false, 'heroNormal' => true], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>

    <style>
        .return-policy-card {
            border: 1px solid #e8efe0;
            border-left: 4px solid #7fad39;
            background: #fbfef7;
        }

        .return-policy-list {
            margin: 0;
            padding-left: 18px;
        }

        .return-policy-list li {
            margin-bottom: 8px;
            line-height: 1.55;
        }

        .btn-detail {
            display: inline-block;
            padding: 10px 20px;
            background: #fff;
            color: #333;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-decoration: none;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .btn-detail:hover {
            background: #f5f5f5;
            border-color: #999;
        }

        /* Notification Status Boxes */
        .notification-status-box {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            background: #f9fafb;
            margin-bottom: 12px;
            padding: 16px;
            font-size: 14px;
            line-height: 1.5;
        }

        .notification-status-box.success {
            border-color: #d1fae5;
            background: #ecfdf5;
            color: #059669;
        }

        .notification-status-box.error {
            border-color: #fee2e2;
            background: #fef2f2;
            color: #dc2626;
        }

        .notification-status-box-action {
            margin-top: 12px;
        }

        /* Luôn đảm bảo orderStatusAlert hiển thị */
        #orderStatusAlert {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
        }

        .refund-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.45);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2000;
            padding: 16px;
        }

        .refund-modal-card {
            width: 100%;
            max-width: 560px;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 16px 36px rgba(0, 0, 0, 0.2);
            padding: 22px;
            position: relative;
        }

        .refund-modal-title {
            margin-bottom: 6px;
            font-weight: 700;
        }

        .refund-modal-subtitle {
            margin-bottom: 16px;
            color: #666;
            font-size: 14px;
        }

        .refund-modal-close {
            position: absolute;
            top: 8px;
            right: 12px;
            border: none;
            background: transparent;
            font-size: 28px;
            line-height: 1;
            color: #888;
            cursor: pointer;
        }

        .refund-label {
            font-weight: 600;
            margin-bottom: 6px;
            display: inline-block;
        }

        .refund-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 4px;
        }
    </style>

    <section class="breadcrumb-section set-bg" data-setbg="<?php echo e(asset('frontend/images/breadcrumb.jpg')); ?>">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <div class="breadcrumb__text">

                        <h2>Chi tiết đơn hàng</h2>

                        <div class="breadcrumb__option">
                            <a href="<?php echo e(route('pages.home')); ?>">Trang chủ</a>
                            <a href="<?php echo e(route('orders.my')); ?>">Đơn mua</a>
                            <span>#<?php echo e($order->id); ?></span>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </section>

    <section class="order-detail spad">
        <div class="container">


            <div class="row">

                <div class="col-lg-10 offset-lg-1">

                    <!-- ORDER INFO -->
                    <div class="order-card mb-4">

                        <div class="order-header">

                            <h4>
                                Đơn hàng #<?php echo e($order->id); ?>

                            </h4>

                            <?php
                                $statusText = [
                                    'pending' => 'Chờ xử lý',
                                    'confirmed' => 'Đã xác nhận',
                                    'shipping' => 'Đang giao',
                                    'completed' => 'Hoàn thành',
                                    'cancelled' => 'Đã huỷ',
                                    'refund_requested' => 'Đang yêu cầu hoàn hàng',
                                    'refunded' => 'Đã hoàn tiền'
                                ];

                                $return = $order->returns->sortByDesc('id')->first();

                            ?>

                            <span class="order-status status-<?php echo e($order->status); ?>">
                                <?php echo e($statusText[$order->status] ?? $order->status); ?>

                            </span>


                        </div>

                        <div class="order-meta">

                            <div>
                                <strong>Ngày đặt:</strong>
                                <?php echo e($order->created_at->format('d/m/Y H:i')); ?>

                            </div>

                            <div>
                                <strong>Thanh toán:</strong>

                                <?php if($order->payment): ?>

                                    <?php
                                        $method = $order->payment->method;
                                        $status = $order->payment->status;

                                        $paymentStatus = [
                                            'pending' => 'Chưa thanh toán',
                                            'paid' => 'Đã thanh toán',
                                            'failed' => 'Thanh toán thất bại'
                                        ];
                                    ?>

                                    <span
                                        class="payment-status 
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <?php if(strtolower($method) == 'cod'): ?> 
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            status-cod 
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <?php else: ?> 
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            status-<?php echo e($status); ?> 
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <?php endif; ?>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    ">
                                        <?php if(strtolower($method) == 'cod'): ?>
                                            <?php if($order->payment->status == 'paid'): ?>
                                                Đã thanh toán
                                            <?php else: ?>
                                                Chưa thanh toán
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <?php echo e($paymentStatus[$status] ?? $status); ?>

                                        <?php endif; ?>
                                    </span>


                                <?php else: ?>
                                    <span class="payment-status status-pending">
                                        Chưa thanh toán
                                    </span>
                                <?php endif; ?>
                            </div>

                        </div>
                        <?php if($order->payment): ?>

                            <div class="order-meta mt-2">

                                <div>
                                    <strong>Phương thức:</strong>
                                    <?php
                                        $methodText = [
                                            'momo' => 'Ví MoMo',
                                            'cod' => 'Thanh toán khi nhận hàng',
                                            'vnpay' => 'VNPay'
                                        ];
                                    ?>

                                    <?php echo e($methodText[strtolower($order->payment->method)] ?? strtoupper($order->payment->method)); ?>


                                </div>

                                <div>
                                    <strong>Mã giao dịch:</strong>
                                    <span style="color:#ee4d2d;font-weight:600">
                                        <?php echo e($order->payment->transaction_code ?? '---'); ?>



                                    </span>
                                </div>

                            </div>

                            <div class="order-meta mt-2">

                                <div>
                                    <strong>Số tiền:</strong>
                                    <?php echo e(number_format($order->payment->amount)); ?> đ
                                </div>

                                <div>
                                    <strong>Thời gian thanh toán:</strong>
                                    <?php echo e($order->payment->paid_at ? $order->payment->paid_at->format('d/m/Y H:i') : '---'); ?>


                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="order-meta mt-2">

                            <div>
                                <strong>Mã đơn:</strong> #<?php echo e($order->id); ?>

                            </div>

                        </div>


                        <?php
                            $orderStatusInfo = [
                                'pending' => 'Đơn hàng sẽ được <b>xác nhận trong 1 - 2 ngày</b>. Vui lòng chờ cửa hàng xử lý đơn hàng của bạn.',
                                'confirmed' => 'Đơn hàng đã được xác nhận. Thời gian giao hàng dự kiến <b>3 - 5 ngày</b>.',
                                'shipping' => 'Đơn hàng đang được giao. Vui lòng chú ý điện thoại để nhận hàng.',
                                'completed' => 'Đơn hàng đã giao thành công. Nếu có vấn đề, bạn có thể <b>yêu cầu hoàn hàng</b>.',
                                'refund_requested' => 'Yêu cầu hoàn hàng đang được xử lý. Vui lòng chờ cửa hàng phản hồi.',
                                'refunded' => 'Đơn hàng đã được hoàn tiền thành công.',
                                'cancelled' => 'Đơn hàng đã bị hủy. Nếu cần hỗ trợ thêm, vui lòng liên hệ cửa hàng.',
                            ];
                        ?>

                        <div id="orderStatusAlert" class="alert alert-info mt-3"
                            style="display:block !important; visibility:visible !important; opacity:1 !important; position:relative;">
                            <?php echo $orderStatusInfo[$order->status] ?? 'Đơn hàng đang được xử lý. Vui lòng theo dõi cập nhật trạng thái mới nhất.'; ?>


                            <?php if($return): ?>
                                <?php
                                    $returnStatusClass = match ($return->status) {
                                        'requested' => 'background:#eef2ff;color:#3730a3;',
                                        'approved' => 'background:#dcfce7;color:#166534;',
                                        'rejected' => 'background:#fee2e2;color:#991b1b;',
                                        'given_to_shipper' => 'background:#fef3c7;color:#92400e;',
                                        'goods_received' => 'background:#dbeafe;color:#1e40af;',
                                        'inspected_defective', 'inspected_good', 'refunded' => 'background:#d1fae5;color:#065f46;',
                                        default => 'background:#f3f4f6;color:#374151;'
                                    };
                                ?>

                                <div style="margin-top:10px; border-top:1px solid #dbeafe; padding-top:10px;">
                                    <strong>Trạng thái hoàn hàng:</strong>
                                    <span
                                        style="display:inline-block; padding:4px 10px; border-radius:999px; font-size:13px; <?php echo e($returnStatusClass); ?>">
                                        <?php echo e($return->status_vn); ?>

                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if(in_array($order->status, ['confirmed'])): ?>
                            <div class="order-card mb-4">

                                <h5 class="mb-3">Thông tin vận chuyển</h5>

                                <p>
                                    <strong>Đơn vị vận chuyển:</strong> Giao hàng nhanh (Demo)
                                </p>

                                <p>
                                    <strong>Dự kiến giao:</strong>
                                    <?php echo e($order->created_at->addDays(3)->format('d/m/Y')); ?> -
                                    <?php echo e($order->created_at->addDays(5)->format('d/m/Y')); ?>

                                </p>

                                <p>
                                    <strong>Trạng thái:</strong>
                                    <?php if($order->status == 'confirmed'): ?>
                                        Đang chuẩn bị hàng tại kho
                                    <?php elseif($order->status == 'shipping'): ?>
                                        Đang giao đến bạn
                                    <?php endif; ?>
                                </p>

                            </div>
                        <?php endif; ?>


                        <div class="order-card mb-4">

                            <h5 class="mb-3">Lịch sử đơn hàng</h5>

                            <ul class="order-history">

                                <li>
                                    <span><?php echo e($order->created_at->format('d/m/Y H:i')); ?></span>
                                    <p>Đặt hàng thành công</p>
                                </li>

                                <?php if(!empty($orderHistoryNotifications) && $orderHistoryNotifications->isNotEmpty()): ?>
                                    <?php $__currentLoopData = $orderHistoryNotifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $historyNoti): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li>
                                            <span><?php echo e($historyNoti->created_at->format('d/m/Y H:i')); ?></span>
                                            <p><?php echo e($historyNoti->display_content); ?></p>
                                        </li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php else: ?>
                                    <?php if(in_array($order->status, ['confirmed', 'shipping', 'completed'])): ?>
                                        <li>
                                            <span><?php echo e($order->updated_at->format('d/m/Y H:i')); ?></span>
                                            <p>Đơn hàng đã được xác nhận</p>
                                        </li>
                                    <?php endif; ?>

                                    <?php if(in_array($order->status, ['shipping', 'completed'])): ?>
                                        <li>
                                            <span><?php echo e($order->updated_at->format('d/m/Y H:i')); ?></span>
                                            <p>Đơn hàng đang được giao</p>
                                        </li>
                                    <?php endif; ?>

                                    <?php if($order->status == 'completed'): ?>
                                        <li>
                                            <span><?php echo e($order->updated_at->format('d/m/Y H:i')); ?></span>
                                            <p>Đơn hàng đã giao thành công</p>
                                        </li>
                                    <?php endif; ?>

                                    <?php if($order->status == 'refund_requested'): ?>
                                        <li>
                                            <span><?php echo e($order->updated_at->format('d/m/Y H:i')); ?></span>
                                            <p>Yêu cầu hoàn hàng đang được xử lý</p>
                                        </li>
                                    <?php endif; ?>

                                    <?php if($order->status == 'refunded'): ?>
                                        <li>
                                            <span><?php echo e($order->payment->refund_at?->format('d/m/Y H:i')); ?></span>
                                            <p>Đơn hàng đã được hoàn tiền</p>
                                        </li>
                                    <?php endif; ?>
                                <?php endif; ?>


                            </ul>

                        </div>



                    </div>

                    <?php if(!in_array($order->status, ['cancelled'])): ?>

                        <div class="order-card mb-4">

                            <h5>Trạng thái đơn hàng</h5>

                            <div class="order-timeline">

                                
                                <div
                                    class="timeline-item 
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <?php echo e(in_array($order->status, ['pending', 'confirmed', 'shipping', 'completed', 'cancelled', 'refund_requested', 'refunded']) ? 'active' : ''); ?>">
                                    <div class="timeline-dot"></div>
                                    <p>Đặt hàng</p>
                                </div>

                                
                                <div
                                    class="timeline-item 
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <?php echo e(in_array($order->status, ['confirmed', 'shipping', 'completed', 'cancelled', 'refund_requested', 'refunded']) ? 'active' : ''); ?>">
                                    <div class="timeline-dot"></div>
                                    <p>Xác nhận</p>
                                </div>

                                
                                <div
                                    class="timeline-item 
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <?php echo e(in_array($order->status, ['shipping', 'completed', 'refund_requested', 'refunded']) ? 'active' : ''); ?>">
                                    <div class="timeline-dot"></div>
                                    <p>Đang giao</p>
                                </div>

                                
                                <div
                                    class="timeline-item 
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        <?php echo e(in_array($order->status, ['completed', 'cancelled', 'refund_requested', 'refunded']) ? 'active' : ''); ?>">
                                    <div class="timeline-dot"></div>

                                    <p>
                                        <?php if($order->status == 'cancelled'): ?>
                                            Đã huỷ
                                        <?php elseif($order->status == 'refund_requested'): ?>
                                            Chờ hoàn hàng
                                        <?php elseif($order->status == 'refunded'): ?>
                                            Đã hoàn tiền
                                        <?php else: ?>
                                            Hoàn thành
                                        <?php endif; ?>
                                    </p>

                                </div>

                            </div>


                        </div>

                    <?php endif; ?>

                    <?php if($order->status == 'cancelled' && $order->cancellation): ?>

                        <div class="order-card mb-4 cancel-info">

                            <h5 class="mb-3">Thông tin huỷ đơn</h5>

                            <?php
                                $reasonText = [
                                    'change_mind' => 'Không muốn mua nữa',
                                    'wrong_product' => 'Chọn nhầm sản phẩm',
                                    'wrong_address' => 'Sai địa chỉ giao hàng',
                                    'found_cheaper' => 'Tìm được nơi rẻ hơn',
                                    'delivery_too_long' => 'Thời gian giao quá lâu',
                                    'other' => 'Lý do khác',
                                    'admin_cancel' => 'Huỷ bởi quản trị viên'
                                ];
                            ?>

                            <p>
                                <strong>Người huỷ:</strong>
                                <?php echo e($order->cancellation->cancelled_by == 'admin' ? 'Quản trị viên' : 'Khách hàng'); ?>

                            </p>

                            <p>
                                <strong>Lý do:</strong>

                                <?php
                                    $rawReason = (string) $order->cancellation->reason;
                                    [$reasonCode, $reasonNote] = array_pad(explode(':', $rawReason, 2), 2, null);
                                    $reasonText = [
                                        'change_mind' => 'Không muốn mua nữa',
                                        'wrong_product' => 'Chọn nhầm sản phẩm',
                                        'wrong_address' => 'Sai địa chỉ giao hàng',
                                        'found_cheaper' => 'Tìm được nơi rẻ hơn',
                                        'delivery_too_long' => 'Thời gian giao quá lâu',
                                        'customer_cancel' => 'Khách hàng tự huỷ đơn',
                                        'admin_cancel' => 'Đơn hàng bị huỷ bởi quản trị viên',
                                        'out_of_stock' => 'Hết hàng trong kho',
                                        'cannot_contact' => 'Không liên hệ được khách hàng',
                                        'delivery_area_unavailable' => 'Khu vực giao hàng tạm ngưng phục vụ',
                                        'suspected_fraud' => 'Đơn hàng có dấu hiệu rủi ro/gian lận',
                                        'system_error' => 'Lỗi hệ thống xử lý đơn hàng',
                                        'other' => 'Lý do khác',
                                    ];
                                    $reasonLabel = $reasonText[$reasonCode] ?? $rawReason;
                                ?>

                                <?php echo e($reasonLabel); ?><?php echo e($reasonNote ? ' - ' . $reasonNote : ''); ?>


                            </p>


                            <p>
                                <strong>Thời gian huỷ:</strong>
                                <?php echo e(\Carbon\Carbon::parse($order->cancellation->cancelled_at)->format('d/m/Y H:i')); ?>

                            </p>

                        </div>

                    <?php endif; ?>


                    <!-- SHIPPING INFO -->
                    <div class="order-card mb-4">

                        <h5 class="mb-3">Thông tin giao hàng</h5>

                        <p><strong>Người nhận:</strong> <?php echo e($order->receiver_name); ?></p>

                        <p><strong>SĐT:</strong> <?php echo e($order->receiver_phone); ?></p>

                        <p><strong>Địa chỉ nhận hàng:</strong>
                            <?php echo e($order->shipping_address ?: ($order->customer->address ?? 'N/A')); ?>

                        </p>

                        <?php if($order->note): ?>
                            <p><strong>Ghi chú:</strong> <?php echo e($order->note); ?></p>
                        <?php endif; ?>
                    </div>



                    <!-- PRODUCT LIST -->

                    <div class="order-card">

                        <h5 class="mb-3">Sản phẩm</h5>

                        <?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php
                                            $product = $item->variant->product;
                                            $variantImagePath = $item->variant?->images?->first()?->image_path;
                                            $productImagePath = $product?->images?->first()?->image_path ?? $product?->image;
                                            $displayImagePath = $variantImagePath ?: $productImagePath;
                                        ?>
                                        <div class="order-product">

                                            <img src="<?php echo e($displayImagePath ? asset('storage/' . $displayImagePath) : asset('frontend/images/product/product-1.jpg')); ?>"
                                                width="70" alt="<?php echo e($product->name); ?>"
                                                onerror="this.src='<?php echo e(asset('frontend/images/product/product-1.jpg')); ?>';">

                                            <div class="product-info">

                                                <div class="product-name">
                                                    <?php echo e($product->name); ?>

                                                </div>

                                                <div class="product-variant">
                                                    Phân loại:
                                                    <?php echo e($item->variant?->size
                            ?? $item->variant?->volume
                            ?? $item->variant?->weight
                            ?? $item->variant?->color
                            ?? '---'); ?>


                                                </div>

                                                <div class="product-qty">
                                                    x<?php echo e((int) max(1, $item->quantity ?? 1)); ?>

                                                </div>

                                            </div>

                                            <?php if($order->status == 'completed'): ?>
                                                <?php
                                                    $customer = auth()->user()->customer ?? null;
                                                    $alreadyReviewed = false;
                                                    if ($customer) {
                                                        $alreadyReviewed = \App\Models\Review::where('customer_id', $customer->user_id)
                                                            ->where('product_id', $product->id)
                                                            ->whereIn('status', ['pending', 'approved'])
                                                            ->exists();
                                                    }
                                                ?>
                                                <div class="mt-2">
                                                    <?php if($alreadyReviewed): ?>
                                                        <span class="badge bg-success">
                                                            <i class="fa fa-check me-1"></i>Đã đánh giá
                                                        </span>
                                                    <?php else: ?>
                                                        <a href="<?php echo e(route('reviews.form', ['product' => $product->id, 'order' => $order->id])); ?>"
                                                            class="btn btn-sm btn-outline-primary">Đánh giá</a>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>

                                            <div class="product-price">

                                                <div>
                                                    <?php echo e(number_format(max(0, (int) ($item->price ?? 0)), 0)); ?> đ
                                                </div>

                                                <div class="product-subtotal">
                                                    <?php echo e(number_format(max(0, (int) ($item->subtotal ?? 0)), 0)); ?> đ
                                                </div>

                                            </div>

                                        </div>

                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                        <?php
                            $subtotal = $order->items->sum('subtotal');

                            $shipping = $order->shipping_fee ?? 0;

                            $discount = $order->discount_amount ?? 0;

                            $total = $subtotal + $shipping - $discount;
                        ?>

                        <div class="order-summary">
                            <div>Tạm tính: <?php echo e(number_format($subtotal)); ?> đ</div>
                            <div>Phí vận chuyển: <?php echo e(number_format($shipping)); ?> đ</div>
                            <div>Giảm giá: <?php echo e(number_format($discount)); ?> đ</div>
                            <hr>
                            <div style="font-size:22px">
                                Tổng thanh toán:
                                <span>
                                    <?php echo e(number_format($total)); ?> đ
                                </span>
                            </div>
                        </div>

                    </div>

                    <div class="text-end mt-4">
                        <?php
                            $hasActiveReturn = $order->returns()
                                ->whereNotIn('status', ['rejected', 'refunded'])
                                ->exists();
                        ?>

                        <!-- Back Button -->
                        <a href="<?php echo e(route('orders.my')); ?>" class="btn-back">
                            Quay lại đơn mua
                        </a>

                        <!-- Cancel Order Button - for pending/confirmed orders -->
                        <?php if(in_array($order->status, ['pending', 'confirmed'])): ?>
                            <button type="button" class="btn btn-outline-danger" onclick="cancelOrder(<?php echo e($order->id); ?>)">
                                <i class="fa fa-times me-1"></i>Hủy đơn hàng
                            </button>
                        <?php endif; ?>

                        <!-- Pay Again Button - for VNPay/MoMo payment not yet successful -->
                        <?php if($order->status == 'pending' && $order->payment && in_array(strtolower($order->payment->method), ['momo', 'vnpay']) && in_array($order->payment->status, ['failed', 'pending'])): ?>
                            <button type="button" class="btn btn-primary" onclick="retryPayment(<?php echo e($order->id); ?>)">
                                <i class="fa fa-refresh me-1"></i>Thanh toán lại
                            </button>
                        <?php endif; ?>

                        <!-- Mark as Received Button - for shipping orders -->
                        <?php if($order->status == 'shipping'): ?>
                            <button type="button" class="btn btn-success" onclick="markAsReceived(<?php echo e($order->id); ?>)">
                                <i class="fa fa-check me-1"></i>Đã nhận hàng
                            </button>
                        <?php endif; ?>

                        <!-- Mark Given to Shipper - for approved returns -->
                        <?php if($return && $return->status === 'approved'): ?>
                            <button type="button" class="btn btn-info" onclick="markGivenToShipper(<?php echo e($return->id); ?>)">
                                <i class="fa fa-truck me-1"></i>Đã giao cho shipper
                            </button>
                        <?php endif; ?>

                        <!-- Request Refund Button -->
                        <?php if($order->status == 'completed' && $order->payment && $order->payment->status == 'paid' && !$hasActiveReturn): ?>
                            <button type="button" class="btn btn-warning" onclick="showRefundPolicyModal()">
                                <i class="fa fa-undo me-1"></i>Yêu cầu hoàn hàng
                            </button>
                        <?php endif; ?>

                        <!-- View Return Details Button -->
                        <?php if($return && $return->status !== 'rejected'): ?>
                            <button type="button" class="btn-detail" onclick="showReturnDetailFullPopup()">
                                <i class="fa fa-info-circle me-1"></i>Xem chi tiết hoàn hàng
                            </button>
                        <?php endif; ?>

                        <!-- View Return Processing Button -->
                        <?php if($return && $return->status === 'requested'): ?>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="showReturnRequestedPopup()">
                                <i class="fa fa-clock-o me-1"></i>Xem chi tiết xử lý
                            </button>
                        <?php endif; ?>

                        <!-- Track Return Processing Button -->
                        <?php if($return && $return->status === 'given_to_shipper'): ?>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="showReturnDetailPopup()">
                                <i class="fa fa-truck me-1"></i>Theo dõi xử lý hoàn hàng
                            </button>
                        <?php endif; ?>

                    </div>

                    <div id="refundPolicyModal" class="refund-modal-overlay" style="display:none;">
                        <div class="refund-modal-card">
                            <button type="button" class="refund-modal-close" onclick="closeRefundPolicyModal()"
                                aria-label="Đóng">&times;</button>
                            <h5 class="refund-modal-title">Chính sách hoàn trả</h5>
                            <p class="refund-modal-subtitle">Vui lòng đọc kỹ chính sách trước khi gửi yêu cầu hoàn hàng.</p>

                            <div class="return-policy-card rounded p-3 mb-3">
                                <h6 class="mb-2">Chính sách hoàn trả sau khi đã nhận hàng</h6>
                                <ul class="return-policy-list mb-3">
                                    <li>Đơn hàng chỉ được gửi yêu cầu hoàn hàng sau khi đã ở trạng thái hoàn thành.</li>
                                    <li>Yêu cầu hoàn hàng cần có lý do; bạn có thể bổ sung mô tả và hình ảnh để đối soát
                                        nhanh hơn.</li>
                                    <li>Yêu cầu sẽ chuyển sang trạng thái xử lý hoàn hàng và chờ duyệt.</li>
                                </ul>
                                <div class="form-check mb-0">
                                    <input class="form-check-input" type="checkbox" value="1" id="refund_policy_confirm"
                                        required>
                                    <label class="form-check-label" for="refund_policy_confirm">
                                        Tôi đã đọc và đồng ý với chính sách hoàn trả.
                                    </label>
                                </div>
                            </div>

                            <div class="refund-actions">
                                <button type="button" class="btn btn-outline-secondary"
                                    onclick="closeRefundPolicyModal()">Hủy</button>
                                <button type="button" class="btn btn-warning" onclick="continueToRefundForm()">Tiếp
                                    tục</button>
                            </div>
                        </div>
                    </div>

                    <div id="refundModal" class="refund-modal-overlay" style="display:none;">
                        <div class="refund-modal-card">
                            <button type="button" class="refund-modal-close" onclick="closeRefundModal()"
                                aria-label="Đóng">&times;</button>
                            <h5 class="refund-modal-title">Yêu cầu hoàn hàng</h5>
                            <p class="refund-modal-subtitle">Vui lòng cung cấp thông tin để cửa hàng xử lý nhanh hơn.</p>

                            <form method="POST" action="<?php echo e(route('orders.refund', $order->id)); ?>"
                                enctype="multipart/form-data">
                                <?php echo csrf_field(); ?>
                                <div class="mb-3">
                                    <label class="refund-label">Lý do hoàn hàng <span style="color:red;">*</span></label>
                                    <select name="reason" class="form-control" required>
                                        <option value="">Chọn lý do</option>
                                        <option value="wrong_product">Nhận nhầm sản phẩm</option>
                                        <option value="product_defect">Sản phẩm lỗi</option>
                                        <option value="other">Khác</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="refund-label">Mô tả thêm (tùy chọn)</label>
                                    <textarea name="description" class="form-control" rows="3"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="refund-label">Hình ảnh (tối đa 5 ảnh)</label>
                                    <input type="file" name="images[]" class="form-control" accept="image/*" multiple>
                                </div>
                                <div class="refund-actions">
                                    <button type="button" class="btn btn-outline-secondary"
                                        onclick="closeRefundModal()">Hủy</button>
                                    <button type="submit" class="btn btn-warning">Gửi yêu cầu</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div id="returnRequestedPopup" class="refund-modal-overlay" style="display:none;">
                        <div class="refund-modal-card" style="max-width:520px;">
                            <button type="button" class="refund-modal-close" onclick="closeReturnRequestedPopup()"
                                aria-label="Đóng">&times;</button>
                            <h5 class="refund-modal-title">Yêu cầu hoàn hàng đang chờ duyệt</h5>
                            <p class="refund-modal-subtitle">Cửa hàng đã nhận yêu cầu và đang kiểm tra thông tin đơn hàng
                                của bạn.</p>
                            <ul class="return-policy-list mb-3">
                                <li>Trạng thái hiện tại: <strong>Chờ duyệt hoàn hàng</strong>.</li>
                                <li>Khi được duyệt, shipper sẽ liên hệ để nhận hàng hoàn.</li>
                                <li>Sau khi hàng về kho và kiểm tra xong, hệ thống sẽ cập nhật bước tiếp theo.</li>
                            </ul>
                            <div class="refund-actions">
                                <button type="button" class="btn btn-primary" onclick="closeReturnRequestedPopup()">Đã
                                    hiểu</button>
                            </div>
                        </div>
                    </div>

                    <div id="returnDetailPopup" class="refund-modal-overlay" style="display:none;">
                        <div class="refund-modal-card" style="max-width:520px;">
                            <button type="button" class="refund-modal-close" onclick="closeReturnDetailPopup()"
                                aria-label="Đóng">&times;</button>
                            <h5 class="refund-modal-title">Chi tiết xử lý hoàn hàng</h5>
                            <p class="refund-modal-subtitle">Hàng của bạn đang được vận chuyển đến kho cửa hàng để kiểm tra.
                            </p>
                            <ul class="return-policy-list mb-3">
                                <li>Trạng thái hiện tại: <strong>Đã gửi cho shipper</strong>.</li>
                                <li>Cửa hàng đã ghi nhận việc gửi hàng của bạn.</li>
                                <li>Hãy chờ kho nhận hàng và kiểm tra chất lượng sản phẩm.</li>
                            </ul>
                            <div class="refund-actions">
                                <button type="button" class="btn btn-primary" onclick="closeReturnDetailPopup()">Đã
                                    hiểu</button>
                            </div>
                        </div>
                    </div>

                    <?php if($return && $return->status !== 'rejected'): ?>
                        <div id="returnDetailFullPopup"
                            style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:2000; padding:16px; align-items:center; justify-content:center;">
                            <div
                                style="width:100%; max-width:650px; max-height:80vh; overflow-y:auto; background:#fff; border-radius:14px; box-shadow:0 16px 36px rgba(0,0,0,.2); padding:22px; position:relative;">
                                <button type="button" onclick="closeReturnDetailFullPopup()" aria-label="Đóng"
                                    style="position:absolute; top:8px; right:12px; border:none; background:transparent; font-size:28px; line-height:1; color:#888; cursor:pointer;">&times;</button>

                                <h5 style="margin-bottom:6px; font-weight:700;">Xem chi tiết hoàn hàng</h5>
                                <p style="margin-bottom:14px; color:#666; font-size:14px;">
                                    Thông tin yêu cầu và xử lý hoàn hàng của đơn #<?php echo e($order->id); ?>

                                </p>

                                <div class="mb-3"
                                    style="border:1px solid #e5e7eb; border-radius:8px; padding:12px; background:#f9fafb;">
                                    <h6 style="margin-bottom:10px; font-weight:600;">Thông tin yêu cầu hoàn hàng</h6>
                                    <div style="font-size:14px; line-height:1.6;">
                                        <div class="mb-2">
                                            <strong>Trạng thái:</strong> <?php echo e($return->status_vn); ?>

                                        </div>
                                        <div class="mb-2">
                                            <strong>Lý do:</strong> <?php echo e($return->reason_vn); ?>

                                        </div>
                                        <?php if($return->description): ?>
                                            <div class="mb-2">
                                                <strong>Mô tả:</strong> <?php echo e($return->description); ?>

                                            </div>
                                        <?php endif; ?>
                                        <div class="mb-0">
                                            <strong>Ngày gửi:</strong> <?php echo e($return->created_at->format('d/m/Y H:i')); ?>

                                        </div>
                                    </div>
                                </div>

                                <?php if($return->images && $return->images->count()): ?>
                                    <div class="mb-3"
                                        style="border:1px solid #e5e7eb; border-radius:8px; padding:12px; background:#f9fafb;">
                                        <h6 style="margin-bottom:10px; font-weight:600;">Hình ảnh hoàn hàng</h6>
                                        <div
                                            style="display:grid; grid-template-columns:repeat(auto-fill, minmax(90px, 1fr)); gap:10px;">
                                            <?php $__currentLoopData = $return->images; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $img): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <a href="<?php echo e(asset('storage/' . $img->image_path)); ?>" target="_blank" rel="noopener">
                                                    <img src="<?php echo e(asset('storage/' . $img->image_path)); ?>" alt="Ảnh hoàn hàng"
                                                        style="width:100%; height:90px; object-fit:cover; border-radius:6px; border:1px solid #ddd;"
                                                        onerror="this.style.display='none';">
                                                </a>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <div class="mb-3"
                                    style="border:1px solid #e5e7eb; border-radius:8px; padding:12px; background:#f9fafb;">
                                    <h6 style="margin-bottom:10px; font-weight:600;">Kết quả xử lý</h6>
                                    <div style="font-size:14px; line-height:1.6;">
                                        <?php if($return->approved_at): ?>
                                            <div class="mb-2">
                                                <strong>Thời gian duyệt:</strong> <?php echo e($return->approved_at->format('d/m/Y H:i')); ?>

                                            </div>
                                        <?php endif; ?>
                                        <?php if($return->inspected_at): ?>
                                            <div class="mb-2">
                                                <strong>Thời gian kiểm tra:</strong>
                                                <?php echo e($return->inspected_at->format('d/m/Y H:i')); ?>

                                            </div>
                                        <?php endif; ?>
                                        <?php if($return->inspection_result): ?>
                                            <div class="mb-2">
                                                <strong>Kết quả kiểm tra:</strong>
                                                <?php if($return->inspection_result === 'defective'): ?>
                                                    Hàng lỗi
                                                <?php elseif($return->inspection_result === 'good'): ?>
                                                    Hàng đạt
                                                <?php else: ?>
                                                    <?php echo e($return->inspection_result); ?>

                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if($return->inspection_notes): ?>
                                            <div class="mb-0">
                                                <strong>Ghi chú:</strong> <?php echo e($return->inspection_notes); ?>

                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <?php if($order->payment && $order->payment->refund_status): ?>
                                    <div class="mb-3"
                                        style="border:1px solid #e5e7eb; border-radius:8px; padding:12px; background:#f9fafb;">
                                        <h6 style="margin-bottom:10px; font-weight:600;">Thông tin hoàn tiền</h6>
                                        <div style="font-size:14px; line-height:1.6;">
                                            <div class="mb-2">
                                                <strong>Trạng thái hoàn tiền:</strong>
                                                <?php if($order->payment->refund_status === 'pending'): ?>
                                                    Chờ xử lý
                                                <?php elseif($order->payment->refund_status === 'completed'): ?>
                                                    Đã hoàn tiền
                                                <?php else: ?>
                                                    <?php echo e($order->payment->refund_status); ?>

                                                <?php endif; ?>
                                            </div>
                                            <div class="mb-2">
                                                <strong>Số tiền hoàn:</strong>
                                                <?php echo e(number_format($order->payment->refund_amount ?? 0, 0, ',', '.')); ?> đ
                                            </div>
                                            <?php if($order->payment->refund_at): ?>
                                                <div class="mb-0">
                                                    <strong>Ngày hoàn tiền:</strong>
                                                    <?php echo e($order->payment->refund_at->format('d/m/Y H:i')); ?>

                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <div class="text-end mt-3">
                                    <button type="button" class="btn btn-primary"
                                        onclick="closeReturnDetailFullPopup()">Đóng</button>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>


                    <form id="cancelForm" method="POST" action="<?php echo e(route('orders.cancel', $order->id)); ?>">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="reason" id="cancel_reason">
                    </form>
                    <script>
                        // Modal handlers
                        function ensureOrderStatusAlertVisible() {
                            const alertBox = document.getElementById('orderStatusAlert');
                            if (!alertBox) return;
                            alertBox.removeAttribute('hidden');
                            alertBox.classList.remove('d-none');
                            alertBox.style.setProperty('display', 'block', 'important');
                            alertBox.style.setProperty('visibility', 'visible', 'important');
                            alertBox.style.setProperty('opacity', '1', 'important');
                            alertBox.style.setProperty('position', 'relative', 'important');
                        }

                        function showRefundPolicyModal() {
                            const modal = document.getElementById('refundPolicyModal');
                            if (modal) modal.style.display = 'flex';
                        }

                        function closeRefundPolicyModal() {
                            const policyModal = document.getElementById('refundPolicyModal');
                            const policyConfirm = document.getElementById('refund_policy_confirm');
                            if (policyModal) policyModal.style.display = 'none';
                            if (policyConfirm) policyConfirm.checked = false;
                            ensureOrderStatusAlertVisible();
                        }

                        function showRefundModal() {
                            const modal = document.getElementById('refundModal');
                            if (modal) modal.style.display = 'flex';
                        }

                        function closeRefundModal() {
                            const modal = document.getElementById('refundModal');
                            if (modal) modal.style.display = 'none';
                            ensureOrderStatusAlertVisible();
                        }

                        function continueToRefundForm() {
                            const policyConfirm = document.getElementById('refund_policy_confirm');
                            if (!policyConfirm || !policyConfirm.checked) {
                                policyConfirm.reportValidity();
                                return;
                            }
                            closeRefundPolicyModal();
                            showRefundModal();
                        }

                        function showReturnRequestedPopup() {
                            const popup = document.getElementById('returnRequestedPopup');
                            if (popup) popup.style.display = 'flex';
                        }

                        function closeReturnRequestedPopup() {
                            const popup = document.getElementById('returnRequestedPopup');
                            if (popup) popup.style.display = 'none';
                            ensureOrderStatusAlertVisible();
                        }

                        function showReturnDetailPopup() {
                            const popup = document.getElementById('returnDetailPopup');
                            if (popup) popup.style.display = 'flex';
                        }

                        function closeReturnDetailPopup() {
                            const popup = document.getElementById('returnDetailPopup');
                            if (popup) popup.style.display = 'none';
                            ensureOrderStatusAlertVisible();
                        }

                        function showReturnDetailFullPopup() {
                            const popup = document.getElementById('returnDetailFullPopup');
                            if (popup) popup.style.display = 'flex';
                        }

                        function closeReturnDetailFullPopup() {
                            const popup = document.getElementById('returnDetailFullPopup');
                            if (popup) popup.style.display = 'none';
                            ensureOrderStatusAlertVisible();
                        }

                        // ════════════════════════════════════════════════════════════════
                        // Global event listeners for modal interactions
                        // ════════════════════════════════════════════════════════════════

                        document.addEventListener('click', function (e) {
                            const policyModal = document.getElementById('refundPolicyModal');
                            const refundModal = document.getElementById('refundModal');
                            const returnRequestPopup = document.getElementById('returnRequestedPopup');
                            const returnDetailPopup = document.getElementById('returnDetailPopup');
                            const returnDetailFullPopup = document.getElementById('returnDetailFullPopup');

                            if (policyModal && policyModal.style.display !== 'none' && e.target === policyModal) {
                                closeRefundPolicyModal();
                            }
                            if (refundModal && refundModal.style.display !== 'none' && e.target === refundModal) {
                                closeRefundModal();
                            }
                            if (returnRequestPopup && returnRequestPopup.style.display !== 'none' && e.target === returnRequestPopup) {
                                closeReturnRequestedPopup();
                            }
                            if (returnDetailPopup && returnDetailPopup.style.display !== 'none' && e.target === returnDetailPopup) {
                                closeReturnDetailPopup();
                            }
                            if (returnDetailFullPopup && returnDetailFullPopup.style.display !== 'none' && e.target === returnDetailFullPopup) {
                                closeReturnDetailFullPopup();
                            }
                        });

                        document.addEventListener('keydown', function (e) {
                            if (e.key === 'Escape') {
                                closeRefundPolicyModal();
                                closeRefundModal();
                                closeReturnRequestedPopup();
                                closeReturnDetailPopup();
                                closeReturnDetailFullPopup();
                            }
                        });

                        document.addEventListener('DOMContentLoaded', ensureOrderStatusAlertVisible);

                        // Define popup function to support additional options
                        function popup(icon, title, text, additionalOptions) {
                            if (window.ocopPopup && typeof window.ocopPopup.fire === 'function') {
                                return window.ocopPopup.fire(Object.assign({
                                    icon: icon,
                                    title: title,
                                    text: text,
                                    confirmButtonColor: '#7fad39'
                                }, additionalOptions || {}));
                            }

                            if (typeof Swal !== 'undefined') {
                                return Swal.fire(Object.assign({
                                    icon: icon,
                                    title: title,
                                    text: text,
                                    confirmButtonColor: '#7fad39'
                                }, additionalOptions || {}));
                            }

                            return Promise.resolve({ isConfirmed: false, isDismissed: true });
                        }

                        function cancelOrder(orderId) {
                            popup('warning', 'Huỷ đơn hàng', 'Vui lòng chọn lý do huỷ đơn', {

                                input: 'select',

                                inputOptions: {
                                    change_mind: 'Không muốn mua nữa',
                                    wrong_product: 'Chọn nhầm sản phẩm',
                                    wrong_address: 'Sai địa chỉ giao hàng',
                                    found_cheaper: 'Tìm được nơi rẻ hơn',
                                    delivery_too_long: 'Thời gian giao quá lâu',
                                    other: 'Lý do khác'
                                },

                                inputPlaceholder: 'Chọn lý do',

                                showCancelButton: true,

                                confirmButtonText: 'Huỷ đơn',

                                cancelButtonText: 'Đóng',

                                confirmButtonColor: '#ee4d2d',

                                cancelButtonColor: '#6c757d',

                                inputValidator: (value) => {
                                    if (!value) {
                                        return 'Bạn phải chọn lý do huỷ!'
                                    }
                                }

                            }).then((result) => {

                                if (result.isConfirmed) {

                                    document.getElementById('cancel_reason').value = result.value;

                                    document.getElementById('cancelForm').submit();

                                }

                            });

                        }

                        // Mark order as received
                        function markAsReceived(orderId) {
                            popup('question', 'Xác nhận đã nhận hàng', 'Bạn đã nhận được hàng chưa?', {
                                showCancelButton: true,
                                confirmButtonText: 'Đã nhận',
                                cancelButtonText: 'Chưa',
                                confirmButtonColor: '#28a745',
                                cancelButtonColor: '#6c757d'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    // Submit form to mark as received
                                    const form = document.createElement('form');
                                    form.method = 'POST';
                                    form.action = `/order/${orderId}/received`;
                                    const csrfToken = document.querySelector('meta[name="csrf-token"]');
                                    if (csrfToken) {
                                        const input = document.createElement('input');
                                        input.type = 'hidden';
                                        input.name = '_token';
                                        input.value = csrfToken.content;
                                        form.appendChild(input);
                                    }
                                    document.body.appendChild(form);
                                    form.submit();
                                }
                            });
                        }

                        // Mark return as given to shipper
                        function markGivenToShipper(returnId) {
                            popup('question', 'Xác nhận đã giao cho shipper', 'Bạn đã giao hàng cho shipper chưa?', {
                                showCancelButton: true,
                                confirmButtonText: 'Đã giao',
                                cancelButtonText: 'Chưa',
                                confirmButtonColor: '#17a2b8',
                                cancelButtonColor: '#6c757d'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    // Submit form to mark as given to shipper
                                    const form = document.createElement('form');
                                    form.method = 'POST';
                                    form.action = `/return/${returnId}/mark-given-to-shipper`;
                                    const csrfToken = document.querySelector('meta[name="csrf-token"]');
                                    if (csrfToken) {
                                        const input = document.createElement('input');
                                        input.type = 'hidden';
                                        input.name = '_token';
                                        input.value = csrfToken.content;
                                        form.appendChild(input);
                                    }
                                    document.body.appendChild(form);
                                    form.submit();
                                }
                            });
                        }

                        // Retry payment
                        function retryPayment(orderId) {
                            popup('question', 'Chọn phương thức thanh toán', 'Vui lòng chọn phương thức thanh toán', {
                                input: 'select',
                                inputOptions: {
                                    momo: 'Ví MoMo',
                                    vnpay: 'VNPay'
                                },
                                inputPlaceholder: 'Chọn phương thức',
                                showCancelButton: true,
                                confirmButtonText: 'Tiếp tục',
                                cancelButtonText: 'Hủy',
                                confirmButtonColor: '#007bff',
                                cancelButtonColor: '#6c757d',
                                inputValidator: (value) => {
                                    if (!value) {
                                        return 'Vui lòng chọn phương thức thanh toán!'
                                    }
                                }
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    const method = result.value;
                                    if (method === 'momo') {
                                        window.location.href = `/payment/momo/${orderId}`;
                                    } else if (method === 'vnpay') {
                                        window.location.href = `/payment/vnpay/${orderId}`;
                                    }
                                }
                            });
                        }

                    </script>


                </div>

            </div>

        </div>
    </section>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\luanvan\resources\views/pages/order-detail.blade.php ENDPATH**/ ?>