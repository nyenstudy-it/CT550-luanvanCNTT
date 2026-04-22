<?php

/**
 * Dịch vụ quản lý tồn kho theo nguyên tắc FIFO (nhập trước – xuất trước).
 *
 * Mục tiêu:
 * - Phân bổ lô hàng (batch) cho từng dòng sản phẩm khi tạo đơn.
 * - Hoàn kho theo đúng lô đã xuất khi hủy đơn.
 * - Ghi nhận writeoff khi xử lý hoàn hàng lỗi.
 * - Phát cảnh báo khi hết/thiếu hàng.
 */

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderReturn;
use App\Models\ImportItem;
use App\Models\InventoryWriteoff;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class FifoInventoryService
{
    /**
     * Gán lô hàng (batch) cho các dòng sản phẩm trong đơn theo FIFO.
     */
    public static function allocateBatchesForOrder(Order $order): array
    {
        $allocated = [];
        $outOfStockItems = [];

        DB::beginTransaction();

        try {
            foreach ($order->items as $orderItem) {
                $isOutOfStock = !self::allocateItemToEarliestBatch($orderItem, $allocated);

                if ($isOutOfStock) {
                    $outOfStockItems[] = [
                        'order_item_id' => $orderItem->id,
                        'product_name' => $orderItem->productVariant->product->name,
                        'quantity' => $orderItem->quantity
                    ];
                }
            }

            DB::commit();

            if (!empty($outOfStockItems)) {
                self::notifyOutOfStock($order, $outOfStockItems);
            }

            return [
                'success' => true,
                'allocated' => $allocated,
                'out_of_stock' => $outOfStockItems
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('FIFO allocation failed', ['error' => $e->getMessage(), 'order_id' => $order->id]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Phân bổ một dòng sản phẩm vào các lô sớm nhất còn hàng (FIFO).
     */
    private static function allocateItemToEarliestBatch(OrderItem $orderItem, &$allocated): bool
    {
        $needed = $orderItem->quantity;
        $productVariantId = $orderItem->product_variant_id;
        $batchDetails = [];

        $batches = ImportItem::where('product_variant_id', $productVariantId)
            ->where('remaining_quantity', '>', 0)
            ->orderBy('created_at', 'asc')
            ->get();

        if ($batches->isEmpty()) {
            return false;
        }

        foreach ($batches as $batch) {
            /** @var ImportItem $batch */
            if ($needed <= 0) break;

            $availableQty = $batch->remaining_quantity;
            $useQty = min($needed, $availableQty);

            $batch->decrement('remaining_quantity', $useQty);

            $batchDetails[] = [
                'batch_id' => $batch->id,
                'quantity' => $useQty,
                'unit_price' => $batch->unit_price
            ];

            if (count($batchDetails) === 1) {
                $orderItem->update([
                    'batch_id' => $batch->id,
                    'cost_price' => $batch->unit_price
                ]);
            }

            $needed -= $useQty;

            $allocated[] = [
                'order_item_id' => $orderItem->id,
                'batch_id' => $batch->id,
                'quantity' => $useQty,
                'cost_price' => $batch->unit_price
            ];
        }

        if (count($batchDetails) > 1 || $needed > 0) {
            $orderItem->update([
                'batch_details' => json_encode($batchDetails)
            ]);
        }

        return ($needed <= 0);
    }

    /**
     * Hoàn lại tồn kho khi đơn bị hủy.
     *
     * Nguyên tắc: hoàn đúng vào các lô đã trừ trước đó (theo `batch_details`/`batch_id`).
     */
    public static function restoreInventoryOnCancel(Order $order): array
    {
        $restored = [];

        DB::beginTransaction();

        try {
            foreach ($order->items as $orderItem) {
                $batchDetails = is_string($orderItem->batch_details)
                    ? json_decode($orderItem->batch_details, true) ?? []
                    : $orderItem->batch_details;

                if (empty($batchDetails) && $orderItem->batch_id) {
                    $batchDetails = [
                        [
                            'batch_id' => $orderItem->batch_id,
                            'quantity' => $orderItem->quantity,
                            'unit_price' => $orderItem->cost_price ?? 0
                        ]
                    ];
                }

                if (!is_array($batchDetails)) continue;

                foreach ($batchDetails as $batch) {
                    if (!isset($batch['batch_id'], $batch['quantity'])) continue;

                    $importItem = ImportItem::lockForUpdate()->find($batch['batch_id']);

                    if ($importItem) {
                        $restoreQty = (int)$batch['quantity'];
                        $importItem->increment('remaining_quantity', $restoreQty);

                        $restored[] = [
                            'batch_id' => $batch['batch_id'],
                            'quantity' => $restoreQty,
                            'product_id' => $importItem->product_variant_id
                        ];
                    }
                }
            }

            DB::commit();

            self::notifyInventoryRestored($order, $restored);

            return ['success' => true, 'restored' => $restored];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Inventory restore failed', ['error' => $e->getMessage(), 'order_id' => $order->id]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Tạo writeoff khi xử lý hoàn hàng lỗi.
     */
    public static function createWriteoffForReturn(OrderReturn $return, Order $order): array
    {
        $writeoffs = [];

        DB::beginTransaction();

        try {
            foreach ($order->items as $orderItem) {
                if (!$orderItem->batch_id) continue;

                $batch = ImportItem::find($orderItem->batch_id);
                if (!$batch) continue;

                $writeoff = InventoryWriteoff::create([
                    'product_variant_id' => $orderItem->product_variant_id,
                    'import_item_id' => $batch->id,
                    'quantity_written_off' => $orderItem->quantity,
                    'unit_cost' => $batch->unit_price,
                    'total_cost' => $orderItem->quantity * $batch->unit_price,
                    'reason' => 'returned',
                    'note' => "Order #{$order->id} - {$return->reason}",
                    'written_off_by' => Auth::id()
                ]);
                /** @var InventoryWriteoff $writeoff */

                $batch->decrement('remaining_quantity', $orderItem->quantity);

                $writeoffs[] = [
                    'writeoff_id' => $writeoff->getKey(),
                    'batch_id' => $batch->id,
                    'quantity' => $orderItem->quantity,
                    'reason' => $return->reason
                ];
            }

            DB::commit();

            self::notifyReturnProcessed($order, $return, $writeoffs);

            return ['success' => true, 'writeoffs' => $writeoffs];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Writeoff creation failed', ['error' => $e->getMessage()]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Trả về danh sách biến thể đã hết hàng.
     */
    public static function checkOutOfStockProducts(): array
    {
        $outOfStock = ImportItem::selectRaw('product_variant_id, SUM(remaining_quantity) as total')
            ->groupBy('product_variant_id')
            ->havingRaw('total <= 0')
            ->with('productVariant.product')
            ->get();

        return $outOfStock->toArray();
    }

    /**
     * Trả về danh sách biến thể sắp hết hàng (<= ngưỡng).
     */
    public static function getLowStockProducts(int $threshold = 5): array
    {
        $lowStock = ImportItem::selectRaw('product_variant_id, product, SUM(remaining_quantity) as total')
            ->having('total', '<=', $threshold)
            ->having('total', '>', 0)
            ->with('productVariant.product')
            ->get();

        return $lowStock->toArray();
    }

    private static function notifyOutOfStock(Order $order, array $items): void
    {
        $content = "Order #{$order->id}: " . count($items) . " sản phẩm HẾT HÀNG";

        Log::error('OUT_OF_STOCK ALERT', ['order_id' => $order->id, 'items' => $items]);

        self::notifyAdmin('out_of_stock', "HẾT HÀNG - ĐH#{$order->id}", $content, $order->id);
    }

    private static function notifyInventoryRestored(Order $order, array $restored): void
    {
        $total = array_sum(array_map(fn($x) => $x['quantity'], $restored));
        $content = "Hoàn kho: {$total} sản phẩm từ ĐH#{$order->id}";

        Log::info('INVENTORY_RESTORED', ['order_id' => $order->id, 'total' => $total]);

        self::notifyAdmin('inventory_restored', "HOÀN KHO - ĐH#{$order->id}", $content, $order->id);
    }

    private static function notifyReturnProcessed(Order $order, OrderReturn $return, array $writeoffs): void
    {
        $total = array_sum(array_map(fn($x) => $x['quantity'], $writeoffs));
        $content = "Hoàn hàng: {$total} sản phẩm lỗi từ ĐH#{$order->id}";

        Log::info('RETURN_PROCESSED', ['order_id' => $order->id, 'return_id' => $return->id]);

        self::notifyAdmin('return_processed', "HOÀN HÀNG - ĐH#{$order->id}", $content, $order->id);
    }

    private static function notifyAdmin(string $type, string $title, string $content, int $orderId): void
    {
        Notification::create([
            'user_id' => null, // broadcast
            'type' => $type,
            'title' => $title,
            'content' => $content,
            'related_id' => $orderId,
            'is_read' => false
        ]);

        Log::channel('inventory')->info("[{$type}] {$title}: {$content}");
    }
}
