<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\OrderCancellation;
use App\Models\OrderReturn;
use App\Models\Customer;
use App\Models\ProductVariant;
use App\Models\ImportItem;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderSeeder extends Seeder
{
    /**
     * Tạo 99 đơn cho tháng 3/2026: phân bố đều 20 khách hàng, lộn xộn tự nhiên
     * - Mỗi đơn ≤ 5 triệu
     * - Nhiều trạng thái: pending, confirmed, shipped, completed, cancelled, returned
     * - Mã WELCOME10 chỉ dùng cho đơn đầu tiên của khách hàng
     * - Sản phẩm và hủy/hoàn cân bằng
     */
    public function run(): void
    {
        // Kiểm tra đã có order tháng 3/2026 chưa
        $marchStart = Carbon::create(2026, 3, 1)->startOfDay();
        $marchEnd = Carbon::create(2026, 3, 31)->endOfDay();
        $existingOrders = Order::whereBetween('created_at', [$marchStart, $marchEnd])->count();

        if ($existingOrders > 0) {
            $this->command->info("ℹ️  Đã có {$existingOrders} order tháng 3/2026. Skip seeder.");
            return;
        }

        $customers = Customer::limit(20)->get();
        if ($customers->count() < 20) {
            $this->command->error("❌ Chỉ có " . $customers->count() . " khách. Cần 20!");
            return;
        }

        // Lấy tất cả 79 product variants
        $variants = ProductVariant::all();
        if ($variants->isEmpty()) {
            $this->command->error('❌ Không có product variants.');
            return;
        }

        // Phương thức thanh toán theo tỷ lệ
        $paymentMethods = [
            'COD' => 0.40,
            'VNPAY' => 0.35,
            'MOMO' => 0.25,
        ];

        // Các trạng thái đơn hàng
        $statuses = [
            'pending' => 0.05,      // 5%
            'confirmed' => 0.10,    // 10%
            'shipping' => 0.20,     // 20%
            'completed' => 0.50,    // 50%
            'cancelled' => 0.10,    // 10%
            'refunded' => 0.05,     // 5%
        ];

        $cancelReasons = ['Đổi ý', 'Tìm được nơi rẻ hơn', 'Hết hàng', 'Khách hủy', 'Lỗi hệ thống'];
        $returnReasons = ['Sản phẩm lỗi', 'Không đúng mô tả', 'Kích cỡ không vừa', 'Hư trong vận chuyển'];

        // ==== BƯỚC 1: TẠO DANH SÁCH 99 ĐƠN LỘN LẦN ====
        // Phân bố: 99/20 = 4-5 đơn/khách
        $orderAssignments = [];
        foreach ($customers as $i => $customer) {
            // Phân bố cơ bản
            $assignCount = $i < 19 ? 5 : 4;  // 19 khách × 5 + 1 khách × 4 = 99
            for ($j = 0; $j < $assignCount; $j++) {
                $orderAssignments[] = $customer;
            }
        }
        // Lộn xộn lại
        shuffle($orderAssignments);

        // ==== BƯỚC 2: TRACK SỬ DỤNG DISCOUNT THEO KHÁCH ====
        $customerFirstOrder = [];
        foreach ($customers as $customer) {
            $customerFirstOrder[$customer->id] = true;
        }

        // ==== BƯỚC 3: TẠO CÁC ĐƠN HÀNG ====
        $marchStart = Carbon::create(2026, 3, 1, 0, 0, 0);
        $marchEnd = Carbon::create(2026, 3, 31, 23, 59, 59);

        // Phân bố đều trong tháng
        $daysInMonth = 31;
        $ordersPerDay = ceil(99 / $daysInMonth);  // ~3.2 đơn/ngày

        $orderIndex = 0;
        $currentDate = $marchStart->copy();

        while ($currentDate <= $marchEnd && $orderIndex < count($orderAssignments)) {
            // Random 2-4 đơn/ngày (lộn xộn)
            $ordersToday = min(
                fake()->numberBetween(2, 4),
                count($orderAssignments) - $orderIndex
            );

            for ($i = 0; $i < $ordersToday; $i++) {
                if ($orderIndex >= count($orderAssignments)) break;

                $customer = $orderAssignments[$orderIndex];
                $orderIndex++;

                // === Trạng thái ===
                $status = $this->pickWeightedStatus($statuses);
                $isCancel = $status === 'cancelled';
                $isRefund = $status === 'refunded';

                // === Phương thức thanh toán ===
                $paymentMethod = $this->pickWeightedPayment($paymentMethods);

                // === Tạo Order ===
                $orderedAt = $currentDate->copy()
                    ->addHours(fake()->numberBetween(8, 22))
                    ->addMinutes(fake()->numberBetween(0, 59));

                $order = Order::create([
                    'customer_id' => $customer->id,
                    'receiver_name' => $customer->user->name,
                    'receiver_phone' => $customer->phone,
                    'shipping_address' => $customer->address,
                    'note' => fake()->optional(0.3)->sentence(),
                    'total_amount' => 0,  // Tính lại sau
                    'status' => $isCancel || $isRefund ? 'completed' : $status,
                    'shipping_fee' => fake()->randomElement([0, 20000, 30000, 50000]),
                    'discount_amount' => 0,  // Tính lại nếu có code
                    'discount_code' => null,  // Gán code nếu đơn đầu tiên
                    'created_at' => $orderedAt,
                    'updated_at' => $orderedAt,
                ]);

                // === Thêm items vào order ===
                $itemCount = fake()->numberBetween(1, 3);  // 1-3 items để ≤ 5 triệu
                $totalAmount = 0;
                $itemsCreated = 0;

                // Lất những items mà còn hàng
                $availableVariants = $variants->shuffle();

                for ($j = 0; $j < min($itemCount, $availableVariants->count()); $j++) {
                    $variant = $availableVariants[$j];
                    $quantity = fake()->numberBetween(1, 3);

                    // Lấy giá
                    $price = $variant->price ?? fake()->numberBetween(50000, 500000);

                    // Lấy batch từ import_items (phần còn lại của hàng)
                    $importItems = ImportItem::where('product_variant_id', $variant->id)
                        ->where('remaining_quantity', '>', 0)
                        ->get();

                    if ($importItems->isEmpty()) continue;

                    $batchDetails = null;
                    $actualQty = 0;
                    $batches = [];
                    $remainingQty = $quantity;

                    foreach ($importItems as $batch) {
                        if ($remainingQty <= 0) break;

                        $batchQty = min($remainingQty, $batch->remaining_quantity);
                        if ($batchQty > 0) {
                            $batches[] = [
                                'batch_id' => $batch->id,
                                'quantity' => $batchQty,
                                'unit_price' => (string)$batch->unit_price,
                            ];

                            // ⭐ Trừ hàng
                            $batch->update([
                                'remaining_quantity' => $batch->remaining_quantity - $batchQty,
                            ]);

                            $actualQty += $batchQty;
                            $remainingQty -= $batchQty;
                        }
                    }

                    if ($actualQty > 0) {
                        $subtotal = $price * $actualQty;

                        // ⭐ Kiểm soát để không vượt 5 triệu
                        if ($totalAmount + $subtotal > 5000000) {
                            // Tính lại quantity để không vượt limit
                            $maxSubtotal = 5000000 - $totalAmount - ($order->shipping_fee ?? 0);
                            if ($maxSubtotal <= 0) break;  // Đủ hàng rồi

                            $actualQty = intval(floor($maxSubtotal / $price));
                            if ($actualQty <= 0) break;

                            $subtotal = $price * $actualQty;
                        }

                        OrderItem::create([
                            'order_id' => $order->id,
                            'product_variant_id' => $variant->id,
                            'quantity' => $actualQty,
                            'price' => $price,
                            'subtotal' => $subtotal,
                            'cost_price' => $price * fake()->randomFloat(1, 0.5, 0.8),
                            'batch_details' => !empty($batches) ? json_encode($batches) : null,
                        ]);

                        $totalAmount += $subtotal;
                        $itemsCreated++;
                    }

                    if ($itemsCreated >= $itemCount) break;
                }

                if ($itemsCreated === 0) {
                    // Không có hàng - xóa order này
                    $order->delete();
                    continue;
                }

                // === Tính discount ===
                $discountAmount = 0;
                $discountCode = null;

                // Chỉ dùng WELCOME10 cho đơn đầu tiên của khách
                if (isset($customerFirstOrder[$customer->id]) && $customerFirstOrder[$customer->id]) {
                    $discountCode = 'WELCOME10';
                    // 10% của tổng (không tính shipping)
                    $discountAmount = round($totalAmount * 0.10);
                    $customerFirstOrder[$customer->id] = false;  // Đã dùng
                }

                // === Tính tổng cuối cùng ===
                $totalAmount += $order->shipping_fee ?? 0;
                $totalAmount -= $discountAmount;
                $totalAmount = max(0, $totalAmount);

                // === Cập nhật order ===
                $order->update([
                    'total_amount' => $totalAmount,
                    'discount_amount' => $discountAmount,
                    'discount_code' => $discountCode,
                ]);

                // === Tạo Payment ===
                $paymentStatus = $isCancel || $isRefund ? 'paid' : 'paid';
                $payment = Payment::create([
                    'order_id' => $order->id,
                    'method' => $paymentMethod,
                    'amount' => $totalAmount,
                    'status' => $paymentStatus,
                    'transaction_code' => 'TXN' . str_pad($order->id, 10, '0', STR_PAD_LEFT),
                    'paid_at' => $orderedAt->addHours(fake()->numberBetween(0, 48)),
                ]);

                // === Hủy đơn nếu status = cancelled ===
                if ($isCancel) {
                    OrderCancellation::create([
                        'order_id' => $order->id,
                        'cancelled_by' => fake()->randomElement(['customer', 'staff']),
                        'reason' => $cancelReasons[array_rand($cancelReasons)],
                        'cancelled_at' => $orderedAt->addDays(fake()->numberBetween(0, 5)),
                    ]);
                    // Hoàn tiền
                    $payment->update([
                        'refund_status' => 'completed',
                        'refund_amount' => $totalAmount,
                        'refund_at' => $orderedAt->addDays(fake()->numberBetween(1, 7)),
                    ]);
                }

                // === Trả hàng nếu status = refunded ===
                if ($isRefund) {
                    OrderReturn::create([
                        'order_id' => $order->id,
                        'reason' => $returnReasons[array_rand($returnReasons)],
                        'description' => fake()->optional(0.7)->sentence(),
                    ]);
                    // Hoàn tiền
                    $payment->update([
                        'refund_status' => 'completed',
                        'refund_amount' => $totalAmount,
                        'refund_at' => $orderedAt->addDays(fake()->numberBetween(3, 10)),
                    ]);
                }
            }

            $currentDate->addDay();
        }

        $this->command->info("✅ Tạo thành công $orderIndex đơn hàng tháng 3/2026");
        $this->command->info("   - Phân bố 20 khách hàng");
        $this->command->info("   - Mỗi đơn ≤ 5 triệu");
        $this->command->info("   - WELCOME10 cho đơn đầu tiên");
    }

    /**
     * Chọn ngẫu nhiên theo tỉ lệ
     */
    private function pickWeightedStatus(array $weights): string
    {
        $rand = fake()->randomFloat(4, 0, 1);
        $cumulative = 0;

        foreach ($weights as $key => $weight) {
            $cumulative += $weight;
            if ($rand <= $cumulative) {
                return $key;
            }
        }

        return 'completed';
    }

    private function pickWeightedPayment(array $weights): string
    {
        $rand = fake()->randomFloat(4, 0, 1);
        $cumulative = 0;

        foreach ($weights as $key => $weight) {
            $cumulative += $weight;
            if ($rand <= $cumulative) {
                return $key;
            }
        }

        return 'COD';
    }
}
