



<?php $__env->startSection('content'); ?>

    <div class="container-fluid pt-4 px-4">

        <?php
$adminReturn = $order->returns->sortByDesc('id')->first();
        ?>

        
        <div class="d-flex justify-content-between align-items-center mb-3">

            <h5>Chi tiết đơn hàng #<?php echo e($order->id); ?></h5>

            <div>

                <a href="<?php echo e(route('admin.orders')); ?>" class="btn btn-light">
                    Quay lại
                </a>

                <?php if(!in_array($order->status, ['cancelled', 'completed', 'refund_requested', 'refunded'])): ?>

                    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#cancelModalDetail<?php echo e($order->id); ?>">
                        Huỷ đơn
                    </button>

                <?php endif; ?>

                <?php if($order->status == 'refund_requested' && !$adminReturn): ?>

                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#refundChoiceModal">
                        Xử lý yêu cầu hoàn hàng
                    </button>

                <?php endif; ?>


            </div>

        </div>

        <?php if(!in_array($order->status, ['cancelled', 'completed', 'refund_requested', 'refunded'])): ?>
            <div class="modal fade" id="cancelModalDetail<?php echo e($order->id); ?>">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Hủy đơn #<?php echo e($order->id); ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <form method="POST" action="<?php echo e(route('admin.orders.cancel', $order->id)); ?>">
                            <?php echo csrf_field(); ?>
                            <div class="modal-body">
                                <label class="form-label">Lý do hủy đơn</label>

                                <select name="reason" class="form-select mb-2" required>
                                    <option value="">Chọn lý do hủy</option>
                                    <?php $__currentLoopData = ($cancelReasonPresets ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reasonCode => $reasonLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($reasonCode); ?>"><?php echo e($reasonLabel); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>

                                <input type="text" name="reason_note" class="form-control" maxlength="255"
                                    placeholder="Ghi chú thêm (tuỳ chọn, đặc biệt khi chọn Lý do khác)">
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                <button type="submit" class="btn btn-danger">Xác nhận hủy</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        
        <div class="order-card mb-4">

            <h6 class="mb-3">Tiến trình đơn hàng</h6>

            <?php
$steps = [
    'pending' => 1,
    'confirmed' => 2,
    'shipping' => 3,
    'completed' => 4,
    'cancelled' => 4,
    'refund_requested' => 4,
    'refunded' => 4,
];

$current = $steps[$order->status] ?? 1;
            ?>

            <div class="order-progress">

                <div class="order-step">
                    <div class="order-circle <?php echo e($current >= 1 ? 'active' : ''); ?>">1</div>
                    <div class="order-label">Chờ xác nhận</div>
                </div>

                <div class="order-step">
                    <div class="order-circle <?php echo e($current >= 2 ? 'active' : ''); ?>">2</div>
                    <div class="order-label">Đã xác nhận</div>
                </div>

                <div class="order-step">
                    <div class="order-circle <?php echo e($current >= 3 ? 'active' : ''); ?>">3</div>
                    <div class="order-label">Đang giao</div>
                </div>

                <div class="order-step">
                    <div class="order-circle <?php echo e($current >= 4 ? 'active' : ''); ?>">4</div>

                    
                    <div class="order-label">
                        <?php if($order->status == 'refunded'): ?>
                            Đã hoàn tiền
                        <?php elseif($order->status == 'refund_requested'): ?>
                            Chờ hoàn hàng
                        <?php elseif($order->status == 'cancelled'): ?>
                            Đã hủy
                        <?php else: ?>
                            Hoàn thành
                        <?php endif; ?>
                    </div>

                </div>

            </div>

        </div>


        <div class="row">

            
            <div class="col-lg-4">

                
                <div class="order-card mb-4">

                    <h6 class="mb-3">Thông tin đơn hàng</h6>

                    <table class="table table-sm">

                        <tr>
                            <td width="130">Mã đơn</td>
                            <td><b>#<?php echo e($order->id); ?></b></td>
                        </tr>

                        <tr>
                            <td>Ngày đặt</td>
                            <td><?php echo e($order->created_at?->format('d/m/Y H:i') ?? '---'); ?></td>
                        </tr>

                        <tr>
                            <td>Trạng thái</td>
                            <td>
                                <?php if($order->status == 'pending'): ?>
                                    <span class="badge bg-warning">Chờ xác nhận</span>

                                <?php elseif($order->status == 'confirmed'): ?>
                                    <span class="badge bg-info">Đã xác nhận</span>

                                <?php elseif($order->status == 'shipping'): ?>
                                    <span class="badge bg-primary">Đang giao</span>

                                <?php elseif($order->status == 'completed'): ?>
                                    <span class="badge bg-success">Hoàn thành</span>

                                <?php elseif($order->status == 'cancelled'): ?>
                                    <span class="badge bg-danger">Đã hủy</span>

                                <?php elseif($order->status == 'refund_requested'): ?>
                                    <span class="badge bg-warning text-dark">Chờ hoàn hàng</span>

                                <?php elseif($order->status == 'refunded'): ?>
                                    <span class="badge bg-success">Đã hoàn tiền</span>
                                <?php endif; ?>

                            </td>
                        </tr>

                        <tr>
                            <td>Phương thức TT</td>
                            <td><?php echo e($order->payment?->method ?? 'COD'); ?></td>
                        </tr>

                        <tr>
                            <td>Trạng thái TT</td>

                            <td>

                                <?php if($order->payment): ?>

                                    <?php if($order->payment->status == 'paid'): ?>
                                        <span class="badge bg-success">Đã thanh toán</span>

                                    <?php elseif($order->payment->status == 'pending'): ?>
                                        <span class="badge bg-warning text-dark">Chưa thanh toán</span>

                                    <?php elseif($order->payment->status == 'failed'): ?>
                                        <span class="badge bg-danger">Thanh toán lỗi</span>
                                    <?php endif; ?>

                                <?php else: ?>

                                    <span class="badge bg-warning text-dark">Chưa thanh toán</span>

                                <?php endif; ?>

                            </td>

                        </tr>

                        <?php if($order->payment && $order->payment->refund_status): ?>

                            <tr>
                                <td>Hoàn tiền</td>
                                <td>

                                    <?php if($order->payment->refund_status == 'pending'): ?>
                                        <span class="badge bg-warning text-dark">Chờ xử lý</span>

                                    <?php elseif($order->payment->refund_status == 'completed'): ?>
                                        <span class="badge bg-success">Đã hoàn</span>

                                    <?php elseif($order->payment->refund_status == 'failed'): ?>
                                        <span class="badge bg-danger">Bị từ chối</span>
                                    <?php endif; ?>

                                </td>
                            </tr>

                            <?php if($order->payment && $order->payment->refund_amount): ?>

                                <tr>
                                    <td>Số tiền hoàn</td>
                                    <td class="text-success">
                                        <?php echo e(number_format($order->payment->refund_amount, 0, ',', '.')); ?> đ
                                    </td>
                                </tr>

                            <?php endif; ?>


                        <?php endif; ?>


                    </table>

                </div>

                
                <div class="order-card mb-4">

                    <h6 class="mb-3">Thông tin khách hàng</h6>

                    <p><b>Tên:</b> <?php echo e($order->receiver_name); ?></p>

                    <p><b>SĐT:</b> <?php echo e($order->receiver_phone); ?></p>

                    <p><b>Địa chỉ:</b> <?php echo e($order->shipping_address); ?></p>

                </div>

                <?php if($adminReturn || ($order->payment && $order->payment->refund_status)): ?>
                    <div class="order-card mb-4">

                        <h6 class="mb-3">Chi tiết hoàn hàng & Kiểm tra</h6>

                        <?php if($adminReturn): ?>
                            <div class="mb-3 p-3" style="border:1px solid #e5e7eb; border-radius:10px; background:#f9fafb;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <b>Trạng thái xử lý:</b>
                                        <span
                                            class="badge 
                                                                                                                                                                    <?php if($adminReturn->status == 'requested'): ?> bg-secondary
                                                                                                                                                                    <?php elseif($adminReturn->status == 'approved'): ?> bg-info
                                                                                                                                                                    <?php elseif($adminReturn->status == 'given_to_shipper'): ?> bg-primary
                                                                                                                                                                    <?php elseif($adminReturn->status == 'goods_received'): ?> bg-warning text-dark
                                                                                                                                                                    <?php elseif($adminReturn->status == 'inspected_defective'): ?> bg-danger
                                                                                                                                                                    <?php elseif($adminReturn->status == 'inspected_good'): ?> bg-success
                                                                                                                                                                    <?php elseif($adminReturn->status == 'rejected'): ?> bg-danger
                                                                                                                                                                    <?php elseif($adminReturn->status == 'refunded'): ?> bg-primary
                                                                                                                                                                    <?php endif; ?>
                                                                                                                                                                ">
                                            <?php echo e($adminReturn->status_vn); ?>

                                        </span>
                                    </div>
                                </div>
                                <small class="text-muted d-block mt-2">
                                    <?php if($adminReturn->status === 'requested'): ?>
                                        Cần duyệt hoặc từ chối yêu cầu của khách.
                                    <?php elseif($adminReturn->status === 'approved'): ?>
                                        Đang chờ khách giao hàng cho shipper.
                                    <?php elseif($adminReturn->status === 'given_to_shipper'): ?>
                                        Khách đã gửi hàng cho shipper, cần xác nhận hàng về kho.
                                    <?php elseif($adminReturn->status === 'goods_received'): ?>
                                        Hàng đã về kho, cần nhập kết quả kiểm tra.
                                    <?php elseif(in_array($adminReturn->status, ['inspected_defective', 'inspected_good'])): ?>
                                        Đã có kết quả kiểm tra, sẵn sàng hoàn tất hoàn tiền.
                                    <?php endif; ?>
                                </small>
                            </div>

                            <p><b>Lý do hoàn hàng:</b> <?php echo e($adminReturn->reason_vn); ?></p>
                            <p><b>Mô tả:</b> <?php echo e($adminReturn->description ?: '---'); ?></p>

                            <?php if($adminReturn->images && $adminReturn->images->count()): ?>
                                <div class="mb-3">
                                    <b class="d-block mb-2">Hình ảnh khách gửi:</b>
                                    <div class="d-flex flex-wrap" style="gap:10px;">
                                        <?php $__currentLoopData = $adminReturn->images; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $refundImage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <a href="<?php echo e(asset('storage/' . $refundImage->image_path)); ?>" target="_blank" rel="noopener">
                                                <img src="<?php echo e(asset('storage/' . $refundImage->image_path)); ?>" width="88" height="88"
                                                    class="rounded border" style="object-fit:cover;">
                                            </a>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            
                            <hr class="my-3">

                            
                            <?php if($adminReturn->status === 'requested'): ?>
                                <div class="mb-3">
                                    <p class="mb-2"><b>BƯỚC 1: Xem xét yêu cầu</b></p>
                                    <div class="row">
                                        <div class="col">
                                            <form method="POST" action="<?php echo e(route('admin.returns.approve', $adminReturn->id)); ?>"
                                                style="display:inline">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit" class="btn btn-success btn-sm w-100">
                                                    DUYỆT - Shipper lấy hàng
                                                </button>
                                            </form>
                                        </div>
                                        <div class="col">
                                            <button class="btn btn-danger btn-sm w-100" data-bs-toggle="modal"
                                                data-bs-target="#rejectReasonModal">
                                                TỪ CHỐI
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            
                            <?php if($adminReturn->status === 'approved'): ?>
                                <div class="mb-3 p-3" style="border:1px dashed #f59e0b; border-radius:10px; background:#fffbeb;">
                                    <b>BƯỚC 2: Chờ khách gửi hàng cho shipper</b>
                                    <br><small>Khi khách xác nhận đã gửi hàng, trạng thái sẽ chuyển sang "Đã gửi hàng cho
                                        shipper".</small>
                                </div>
                            <?php endif; ?>

                            
                            <?php if($adminReturn->status === 'given_to_shipper'): ?>
                                <div class="mb-3">
                                    <p class="mb-2"><b>BƯỚC 3: Hàng từ shipper đã về?</b></p>
                                    <form method="POST" action="<?php echo e(route('admin.returns.markReceivedFromShipper', $adminReturn->id)); ?>"
                                        style="display:inline">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="btn btn-outline-primary btn-sm w-100">
                                            Hàng từ shipper đã về - Tiến hành kiểm tra
                                        </button>
                                    </form>
                                </div>
                            <?php endif; ?>

                            
                            <?php if($adminReturn->status === 'goods_received'): ?>
                                <div class="mb-3">
                                    <p class="mb-2"><b>BƯỚC 4: Kiểm tra xong? Kết quả?</b></p>
                                    <div class="row">
                                        <div class="col">
                                            <button class="btn btn-danger btn-sm w-100" data-bs-toggle="modal"
                                                data-bs-target="#inspectionModal" data-result="defective">
                                                Hàng lỗi
                                            </button>
                                        </div>
                                        <div class="col">
                                            <button class="btn btn-success btn-sm w-100" data-bs-toggle="modal"
                                                data-bs-target="#inspectionModal" data-result="good">
                                                Hàng đạt
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            
                            <?php if(in_array($adminReturn->status, ['inspected_defective', 'inspected_good']) && in_array($order->status, ['refund_requested', 'completed'])): ?>
                                <div class="mb-3">
                                    <p class="mb-2"><b>BƯỚC 5: Hoàn tiền</b></p>
                                    <div class="mb-2 p-2" style="border:1px solid #facc15; border-radius:8px; background:#fffbeb;">
                                        Kiểm tra:
                                        <strong><?php echo e($adminReturn->inspection_result === 'defective' ? 'LỖI' : 'ĐẠT'); ?></strong>
                                        <?php if($adminReturn->inspection_notes): ?>
                                            <br><small>Ghi chú: <?php echo e($adminReturn->inspection_notes); ?></small>
                                        <?php endif; ?>
                                    </div>
                                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#refundChoiceModal">
                                        Xử lý hoàn hàng - Chọn hành động
                                    </button>
                                </div>
                            <?php endif; ?>

                        <?php endif; ?>

                        <?php if($order->payment && $order->payment->refund_status): ?>
                            <table class="table table-sm mb-0">
                                <tr>
                                    <td width="140">Trạng thái hoàn</td>
                                    <td>
                                        <?php if($order->payment->refund_status == 'pending'): ?>
                                            <span class="badge bg-warning text-dark">Chờ xử lý</span>
                                        <?php elseif($order->payment->refund_status == 'completed'): ?>
                                            <span class="badge bg-success">Đã hoàn tiền</span>
                                        <?php elseif($order->payment->refund_status == 'failed'): ?>
                                            <span class="badge bg-danger">Từ chối hoàn tiền</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Số tiền hoàn</td>
                                    <td class="text-success">
                                        <?php echo e(number_format($order->payment->refund_amount ?? 0, 0, ',', '.')); ?> đ
                                    </td>
                                </tr>
                                <tr>
                                    <td>Thời gian hoàn</td>
                                    <td><?php echo e($order->payment->refund_at?->format('d/m/Y H:i') ?? '---'); ?></td>
                                </tr>
                            </table>
                        <?php endif; ?>

                    </div>
                <?php endif; ?>


                <?php if(!in_array($order->status, ['completed', 'cancelled', 'refund_requested', 'refunded'])): ?>
                    
                    <div class="order-card">

                        <h6 class="mb-3">Cập nhật trạng thái</h6>

                        <form method="POST" action="<?php echo e(route('admin.orders.updateStatus', $order->id)); ?>">

                            <?php echo csrf_field(); ?>

                            <select name="status" class="form-select mb-3">

                                <option value="pending" <?php echo e($order->status == 'pending' ? 'selected' : ''); ?>>
                                    Chờ xác nhận
                                </option>

                                <option value="confirmed" <?php echo e($order->status == 'confirmed' ? 'selected' : ''); ?>>
                                    Đã xác nhận
                                </option>

                                <option value="shipping" <?php echo e($order->status == 'shipping' ? 'selected' : ''); ?>>
                                    Đang giao
                                </option>

                                <option value="completed" <?php echo e($order->status == 'completed' ? 'selected' : ''); ?>>
                                    Hoàn thành
                                </option>

                            </select>

                            <button class="btn btn-primary w-100">

                                Cập nhật trạng thái

                            </button>

                        </form>

                    </div>

                <?php endif; ?>

            </div>

            
            <div class="col-lg-8">

                
                <div class="order-card">

                    <h6 class="mb-3">Sản phẩm đã đặt</h6>

                    <div class="table-responsive">

                        <table class="table align-middle">

                            <thead>

                                <tr>
                                    <th width="80">Ảnh</th>
                                    <th>Sản phẩm</th>
                                    <th width="120">Đơn giá</th>
                                    <th width="80">SL</th>
                                    <th width="150">Thành tiền</th>
                                </tr>

                            </thead>

                            <tbody>

                                <?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                                    <tr>

                                        <td>

                                            <?php
                                                $variant = $item->variant ?? null;
                                                $product = $variant && $variant->product ? $variant->product : ($item->product ?? null);

                                                $variantImagePath = null;
                                                $productImagePath = null;
                                                $fallbackProductImagePath = $product?->image ?? null;

                                                if ($variant && !empty($variant->images) && $variant->images->count()) {
                                                    $variantImagePath = $variant->images->first()->image_path ?? null;
                                                }

                                                if ($product && !empty($product->images) && $product->images->count()) {
                                                    $productImagePath = $product->images->first()->image_path ?? null;
                                                }

                                                $imagePath = $variantImagePath ?? $productImagePath ?? $fallbackProductImagePath ?? null;
                                                if ($imagePath) {
                                                    $imgUrl = str_starts_with($imagePath, 'frontend') ? asset($imagePath) : asset('storage/' . $imagePath);
                                                } else {
                                                    $imgUrl = asset('frontend/images/product/product-1.jpg');
                                                }
                                            ?>

                                            <img src="<?php echo e($imgUrl); ?>" width="60" height="60" class="rounded border" style="object-fit:cover">

                                        </td>

                                        <td>

                                            <?php
                                                $productName = '-';
                                                if (!empty($variant) && !empty($variant->product)) {
                                                    $productName = $variant->product->name ?? '-';
                                                } elseif (!empty($item->product)) {
                                                    $productName = $item->product->name ?? '-';
                                                }
                                            ?>

                                            <b><?php echo e($productName); ?></b>

                                            <br>

                                            <small class="text-muted">
                                                <?php if($variant && $variant->volume): ?>
                                                    <?php echo e($variant->volume); ?>

                                                <?php endif; ?>

                                                <?php if($variant && $variant->weight): ?>
                                                    <?php echo e($variant->weight); ?>

                                                <?php endif; ?>

                                                <?php if($variant && $variant->size): ?>
                                                    Size <?php echo e($variant->size); ?>

                                                <?php endif; ?>

                                                <?php if($variant && $variant->color): ?>
                                                    <?php echo e($variant->color); ?>

                                                <?php endif; ?>

                                            </small>

                                        </td>

                                        <td>
                                            <?php echo e(number_format($item->price, 0, ',', '.')); ?> đ
                                        </td>

                                        <td>
                                            <?php echo e($item->quantity); ?>

                                        </td>

                                        <td>
                                            <?php echo e(number_format($item->subtotal, 0, ',', '.')); ?> đ
                                        </td>

                                    </tr>

                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                            </tbody>

                        </table>

                    </div>

                </div>

                
                <?php
                    // Tổng tiền sản phẩm (lấy từ DB 'subtotal' đã lưu)
                    $storedSubtotal = $order->items->sum('subtotal');

                    // Tổng tính toán chuẩn (price * quantity)
                    $computedSubtotal = $order->items->reduce(function($carry, $it) {
                        return $carry + (float)($it->price * $it->quantity);
                    }, 0);

                    // Phí vận chuyển
                    $shipping = $order->shipping_fee ?? 0;

                    // Giảm giá
                    $discount = $order->discount_amount ?? 0;

                    // Tổng thanh toán dựa trên subtotal lưu
                    $total_stored = $storedSubtotal + $shipping - $discount;

                    // Tổng thanh toán dựa trên tính toán
                    $total_computed = $computedSubtotal + $shipping - $discount;
                ?>

                <div class="order-card mt-4">
                    <h6 class="mb-3">Thanh toán</h6>

                    <div class="d-flex justify-content-between mb-2">
                        <span>Tổng tiền hàng </span>
                        <span><?php echo e(number_format($storedSubtotal, 0, ',', '.')); ?> đ</span>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span>Phí vận chuyển</span>
                        <span><?php echo e(number_format($shipping, 0, ',', '.')); ?> đ</span>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span>Giảm giá</span>
                        <span><?php echo e(number_format($discount, 0, ',', '.')); ?> đ</span>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between">
                        <b>Tổng thanh toán</b>
                        <b class="text-danger fs-5">
                            <?php echo e(number_format($total_stored, 0, ',', '.')); ?> đ
                        </b>
                    </div>
                </div>

                
                <?php if($order->status == 'cancelled' && $order->cancellation): ?>

                    <div class="order-card mt-4">

                        <h6>Lý do hủy đơn</h6>

                        <p class="text-danger">

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

                    </div>

                <?php endif; ?>

            </div>

        </div>

    </div>

    
    
    
    
    <div class="modal fade" id="refundChoiceModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Xử lý hoàn hàng - Chọn hành động xử lý hàng</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-4 p-3" style="border:1px solid #dbeafe; border-radius:10px; background:#f8fbff;">
                        <strong>Thông tin yêu cầu của khách:</strong>
                        <p class="mb-1 mt-1">Lý do: <strong><?php echo e($adminReturn ? $adminReturn->reason_vn : '---'); ?></strong>
                        </p>
                        <p class="mb-0">Mô tả: <?php echo e($adminReturn ? ($adminReturn->description ?: '(Không có)') : '---'); ?></p>
                    </div>

                    <div class="row">
                        
                        <div class="col-md-6 mb-3">
                            <div class="card border-success h-100">
                                <div class="card-body text-center">
                                    <h5 class="card-title mb-3">CỘNG KHO</h5>
                                    <p class="card-text text-muted mb-3">
                                        Sản phẩm TỐT, khách chỉ chọn nhầm
                                    </p>
                                    
                                    <form method="POST"
                                        action="<?php echo e(route('admin.orders.approveRefundWithChoice', $order->id)); ?>"
                                        style="display:inline">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="action" value="restore_stock">
                                        <button type="submit" class="btn btn-success w-100">
                                            Cộng lại kho
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        
                        <div class="col-md-6 mb-3">
                            <div class="card border-danger h-100">
                                <div class="card-body text-center">
                                    <h5 class="card-title mb-3">HỦY HÀNG</h5>
                                    <p class="card-text text-muted mb-3">
                                        Sản phẩm LỖI, cần hủy/writeoff
                                    </p>
                                    
                                    <form method="POST"
                                        action="<?php echo e(route('admin.orders.approveRefundWithChoice', $order->id)); ?>"
                                        style="display:inline">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="action" value="create_writeoff">
                                        <button type="submit" class="btn btn-danger w-100">
                                            Hủy hàng (Writeoff)
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-2 p-2" style="border-top:1px dashed #d1d5db; color:#6b7280; font-size:13px;">
                        Writeoff riêng biệt được xử lý tại màn Kho.
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

    
    
    
    <?php if($adminReturn && $adminReturn->status === 'requested'): ?>
        <div class="modal fade" id="rejectReasonModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">TỪ CHỐI YÊU CẦU HOÀN HÀNG</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form method="POST" action="<?php echo e(route('admin.returns.reject', $adminReturn->id)); ?>">
                            <?php echo csrf_field(); ?>
                            <div class="mb-3">
                                <label class="form-label">Lý do từ chối</label>
                                <textarea name="rejection_reason" class="form-control" rows="4"
                                    placeholder="Nhập lý do từ chối..." required></textarea>
                                <small class="text-muted">Khách hàng sẽ nhận được thông báo với lý do này</small>
                            </div>
                            <button type="submit" class="btn btn-danger w-100">
                                Xác nhận từ chối
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    
    
    
    <?php if($adminReturn && $adminReturn->status === 'goods_received'): ?>
        <div class="modal fade" id="inspectionModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title">NHẬP KẾT QUẢ KIỂM TRA</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form method="POST" action="<?php echo e(route('admin.returns.markInspected', $adminReturn->id)); ?>"
                            id="inspectionForm">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="inspection_result" id="inspectionResult" value="">

                            <div class="mb-3">
                                <label class="form-label">Ghi chú kiểm tra</label>
                                <textarea name="inspection_notes" class="form-control" rows="3"
                                    placeholder="Mô tả chi tiết lỗi hoặc tình trạng của hàng..."></textarea>
                            </div>

                            <button type="submit" class="btn w-100" id="inspectionSubmitBtn">
                                Lưu kết quả kiểm tra
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.querySelectorAll('[data-bs-target="#inspectionModal"]').forEach(btn => {
                btn.addEventListener('click', function () {
                    const result = this.getAttribute('data-result');
                    document.getElementById('inspectionResult').value = result;

                    if (result === 'defective') {
                        document.getElementById('inspectionSubmitBtn').className = 'btn btn-danger w-100';
                        document.getElementById('inspectionSubmitBtn').innerText = 'Xác nhận lỗi';
                    } else {
                        document.getElementById('inspectionSubmitBtn').className = 'btn btn-success w-100';
                        document.getElementById('inspectionSubmitBtn').innerText = 'Xác nhận đạt';
                    }
                });
            });
        </script>
    <?php endif; ?>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.layout_admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\luanvan\resources\views/admin/orders/detail.blade.php ENDPATH**/ ?>