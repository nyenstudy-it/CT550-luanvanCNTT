<?php

namespace App\Http\Controllers;

use App\Models\ImportItem;
use App\Models\InventoryWriteoff;
use Illuminate\Http\Request;
use App\Models\Inventory;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InventoryController extends Controller
{
    public function list(Request $request)
    {
        $keyword = trim((string) $request->query('keyword', ''));
        $stockStatus = (string) $request->query('stock_status', 'all');
        $expiryStatus = (string) $request->query('expiry_status', 'all');
        $staleStatus = (string) $request->query('stale_status', 'all');
        $sortBy = (string) $request->query('sort_by', 'risk_desc');

        $lowStockThreshold = max(1, (int) $request->query('low_stock_threshold', 5));
        $expiringInDays = max(1, (int) $request->query('expiring_days', 30));
        $defaultStaleDays = 90;
        $promotionWindowDays = 180;

        $today = Carbon::today();

        $query = Inventory::with(['variant.product.category']);

        if ($keyword !== '') {
            $query->where(function ($builder) use ($keyword) {
                $builder->whereHas('variant', function ($variantQuery) use ($keyword) {
                    $variantQuery->where('sku', 'like', "%{$keyword}%")
                        ->orWhere('color', 'like', "%{$keyword}%")
                        ->orWhere('size', 'like', "%{$keyword}%")
                        ->orWhere('volume', 'like', "%{$keyword}%")
                        ->orWhere('weight', 'like', "%{$keyword}%")
                        ->orWhereHas('product', function ($productQuery) use ($keyword) {
                            $productQuery->where('name', 'like', "%{$keyword}%");
                        });
                });
            });
        }

        $baseInventories = $query->get();

        $activeBatchItems = ImportItem::query()
            ->with('import:id,import_date')
            ->where('remaining_quantity', '>', 0)
            ->orderBy('expired_at')
            ->orderBy('id')
            ->get()
            ->groupBy('product_variant_id');

        $enrichedInventories = $baseInventories->map(function (Inventory $inventory) use (
            $activeBatchItems,
            $today,
            $lowStockThreshold,
            $expiringInDays,
            $defaultStaleDays,
            $promotionWindowDays
        ) {
            $variant = $inventory->variant;
            $quantity = (int) $inventory->quantity;
            $staleThresholdDays = $this->resolveStaleThresholdDays($variant?->product?->category?->name, $defaultStaleDays);

            $activeBatches = $activeBatchItems
                ->get($inventory->product_variant_id, collect())
                ->map(function (ImportItem $batch) use ($today, $expiringInDays, $promotionWindowDays) {
                    $expiryDate = $batch->expired_at ? Carbon::parse($batch->expired_at)->startOfDay() : null;
                    $manufactureDate = $batch->manufacture_date ? Carbon::parse($batch->manufacture_date)->startOfDay() : null;
                    $importedAt = $batch->import?->import_date
                        ? Carbon::parse($batch->import->import_date)->startOfDay()
                        : $batch->created_at?->copy()->startOfDay();
                    $remainingQuantity = (int) $batch->remaining_quantity;
                    $daysToExpire = $expiryDate ? $today->diffInDays($expiryDate, false) : null;

                    if (!$expiryDate) {
                        $status = 'no_expiry';
                        $status_label = 'Không có HSD';
                    } elseif ($expiryDate->lt($today)) {
                        $status = 'expired';
                        $status_label = 'Đã hết hạn';
                    } elseif ($daysToExpire <= $expiringInDays) {
                        $status = 'expiring_soon';
                        $status_label = 'Sắp hết hạn';
                    } elseif ($daysToExpire <= $promotionWindowDays) {
                        $status = 'promotion_candidate';
                        $status_label = 'Nên đẩy bán';
                    } else {
                        $status = 'safe';
                        $status_label = 'An toàn';
                    }

                    return [
                        'id' => $batch->id,
                        'import_id' => $batch->import_id,
                        'remaining_quantity' => $remainingQuantity,
                        'quantity' => (int) $batch->quantity,
                        'unit_price' => (float) $batch->unit_price,
                        'manufacture_date' => $manufactureDate,
                        'expired_at' => $expiryDate,
                        'imported_at' => $importedAt,
                        'days_to_expire' => $daysToExpire,
                        'status' => $status,
                        'status_label' => $status_label,
                    ];
                })
                ->sortBy(function (array $batch) {
                    return [
                        $batch['expired_at']?->timestamp ?? PHP_INT_MAX,
                        $batch['imported_at']?->timestamp ?? PHP_INT_MAX,
                    ];
                })
                ->values();

            $nearestExpiryBatch = $activeBatches->first(fn(array $batch) => $batch['expired_at'] !== null);
            $oldestBatch = $activeBatches->sortBy(function (array $batch) {
                return $batch['imported_at']?->timestamp ?? PHP_INT_MAX;
            })->first();

            $expiryDate = $nearestExpiryBatch['expired_at'] ?? null;
            $oldestBatchAt = $oldestBatch['imported_at'] ?? null;
            $stockAgeDays = $oldestBatchAt ? $oldestBatchAt->diffInDays($today) : null;

            $expiredBatchQuantity = (int) $activeBatches
                ->where('status', 'expired')
                ->sum('remaining_quantity');
            $expiringBatchQuantity = (int) $activeBatches
                ->where('status', 'expiring_soon')
                ->sum('remaining_quantity');
            $promotionBatchQuantity = (int) $activeBatches
                ->where('status', 'promotion_candidate')
                ->sum('remaining_quantity');
            $safeBatchQuantity = (int) $activeBatches
                ->where('status', 'safe')
                ->sum('remaining_quantity');
            $noExpiryBatchQuantity = (int) $activeBatches
                ->where('status', 'no_expiry')
                ->sum('remaining_quantity');

            $isOutOfStock = $quantity <= 0;
            $isLowStock = !$isOutOfStock && $quantity <= $lowStockThreshold;
            $isExpired = $expiredBatchQuantity > 0;
            $isExpiringSoon = $expiringBatchQuantity > 0;
            $isPromotionCandidate = ($expiringBatchQuantity + $promotionBatchQuantity) > 0 && $quantity > 0;
            $isStale = $stockAgeDays !== null && $stockAgeDays > $staleThresholdDays && $quantity > 0;

            $daysToExpire = $expiryDate ? $today->diffInDays($expiryDate, false) : null;

            $riskScore = 0;
            $riskScore += $isOutOfStock ? 120 : 0;
            $riskScore += $isExpired ? 90 : 0;
            $riskScore += $isLowStock ? 60 : 0;
            $riskScore += $isExpiringSoon ? 40 : 0;
            $riskScore += $isStale ? 20 : 0;

            $alerts = [];
            if ($isOutOfStock) {
                $alerts[] = 'out_of_stock';
            }
            if ($isLowStock) {
                $alerts[] = 'low_stock';
            }
            if ($isExpired) {
                $alerts[] = 'expired';
            }
            if ($isExpiringSoon) {
                $alerts[] = 'expiring_soon';
            }
            if ($isStale) {
                $alerts[] = 'stale_stock';
            }

            $inventory->setAttribute('expiry_date', $expiryDate);
            $inventory->setAttribute('days_to_expire', $daysToExpire);
            $inventory->setAttribute('oldest_batch_at', $oldestBatchAt);
            $inventory->setAttribute('stock_age_days', $stockAgeDays);
            $inventory->setAttribute('is_out_of_stock', $isOutOfStock);
            $inventory->setAttribute('is_low_stock', $isLowStock);
            $inventory->setAttribute('is_expired', $isExpired);
            $inventory->setAttribute('is_expiring_soon', $isExpiringSoon);
            $inventory->setAttribute('is_promotion_candidate', $isPromotionCandidate);
            $inventory->setAttribute('is_stale', $isStale);
            $inventory->setAttribute('stale_threshold_days', $staleThresholdDays);
            $inventory->setAttribute('risk_score', $riskScore);
            $inventory->setAttribute('alert_tags', $alerts);
            $inventory->setAttribute('total_remaining_batch_quantity', (int) $activeBatches->sum('remaining_quantity'));
            $inventory->setAttribute('active_batch_count', $activeBatches->count());
            $inventory->setAttribute('expired_batch_quantity', $expiredBatchQuantity);
            $inventory->setAttribute('expiring_batch_quantity', $expiringBatchQuantity);
            $inventory->setAttribute('promotion_batch_quantity', $promotionBatchQuantity);
            $inventory->setAttribute('safe_batch_quantity', $safeBatchQuantity);
            $inventory->setAttribute('no_expiry_batch_quantity', $noExpiryBatchQuantity);
            $inventory->setAttribute('active_batches', $activeBatches->take(3));
            $inventory->setAttribute('has_more_batches', $activeBatches->count() > 3);
            $inventory->setAttribute('batch_status_breakdown', [
                'expired' => $expiredBatchQuantity,
                'expiring' => $expiringBatchQuantity,
                'promotion' => $promotionBatchQuantity,
                'safe' => $safeBatchQuantity,
                'no_expiry' => $noExpiryBatchQuantity,
            ]);

            return $inventory;
        });

        $this->syncInventoryNotifications($enrichedInventories);

        $summary = [
            'total_variants' => $enrichedInventories->count(),
            'out_of_stock' => $enrichedInventories->where('is_out_of_stock', true)->count(),
            'low_stock' => $enrichedInventories->where('is_low_stock', true)->count(),
            'expired' => $enrichedInventories->where('is_expired', true)->count(),
            'expiring_soon' => $enrichedInventories->where('is_expiring_soon', true)->count(),
            'promotion_candidates' => $enrichedInventories->where('is_promotion_candidate', true)->count(),
            'stale_stock' => $enrichedInventories->where('is_stale', true)->count(),
            'normal_stock' => $enrichedInventories->filter(function (Inventory $inventory) {
                return empty($inventory->alert_tags);
            })->count(),
        ];

        $alertPreview = $enrichedInventories
            ->filter(fn(Inventory $inventory) => !empty($inventory->alert_tags))
            ->sortByDesc('risk_score')
            ->take(8)
            ->values();

        $filteredInventories = $enrichedInventories
            ->filter(function (Inventory $inventory) use ($stockStatus) {
                return match ($stockStatus) {
                    'out' => $inventory->is_out_of_stock,
                    'low' => $inventory->is_low_stock,
                    'ok' => !$inventory->is_out_of_stock && !$inventory->is_low_stock,
                    default => true,
                };
            })
            ->filter(function (Inventory $inventory) use ($expiryStatus) {
                return match ($expiryStatus) {
                    'expired' => $inventory->is_expired,
                    'expiring' => $inventory->is_expiring_soon,
                    'promo' => $inventory->is_promotion_candidate,
                    'safe' => !$inventory->is_expired && !$inventory->is_expiring_soon && $inventory->expiry_date,
                    'no_expiry' => !$inventory->expiry_date,
                    default => true,
                };
            })
            ->filter(function (Inventory $inventory) use ($staleStatus) {
                return match ($staleStatus) {
                    'stale' => $inventory->is_stale,
                    'fresh' => !$inventory->is_stale,
                    default => true,
                };
            });

        $sortedInventories = match ($sortBy) {
            'quantity_asc' => $filteredInventories->sortBy('quantity')->values(),
            'quantity_desc' => $filteredInventories->sortByDesc('quantity')->values(),
            'expiry_asc' => $filteredInventories->sortBy(function (Inventory $inventory) {
                return $inventory->expiry_date ? $inventory->expiry_date->timestamp : PHP_INT_MAX;
            })->values(),
            'stock_age_desc' => $filteredInventories->sortByDesc(function (Inventory $inventory) {
                return $inventory->stock_age_days ?? -1;
            })->values(),
            default => $filteredInventories
                ->filter(fn(Inventory $inventory) => $inventory->is_promotion_candidate)
                ->sortBy(function (Inventory $inventory) {
                    return [
                        $inventory->days_to_expire ?? PHP_INT_MAX,
                        -1 * ($inventory->risk_score ?? 0),
                    ];
                })
                ->concat(
                    $filteredInventories
                        ->reject(fn(Inventory $inventory) => $inventory->is_promotion_candidate)
                        ->sortByDesc('risk_score')
                )
                ->values(),
        };

        $perPage = 15;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentItems = $sortedInventories
            ->slice(($currentPage - 1) * $perPage, $perPage)
            ->values();

        $inventories = new LengthAwarePaginator(
            $currentItems,
            $sortedInventories->count(),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('admin.inventories.list', compact(
            'inventories',
            'summary',
            'alertPreview',
            'lowStockThreshold',
            'expiringInDays',
            'promotionWindowDays'
        ));
    }

    private function resolveStaleThresholdDays(?string $categoryName, int $fallbackDays = 90): int
    {
        $normalizedCategory = $this->normalizeCategoryName($categoryName);

        if (
            str_contains($normalizedCategory, 'my pham thien nhien')
            || str_contains($normalizedCategory, 'do thu cong my nghe')
            || str_contains($normalizedCategory, 'thu cong my nghe')
        ) {
            return 365;
        }

        if (str_contains($normalizedCategory, 'tinh dau thien nhien')) {
            return 180;
        }

        return $fallbackDays;
    }

    private function normalizeCategoryName(?string $categoryName): string
    {
        $value = trim((string) $categoryName);

        if ($value === '') {
            return '';
        }

        $value = mb_strtolower($value, 'UTF-8');

        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        if ($ascii !== false) {
            $value = $ascii;
        }

        $value = preg_replace('/[^a-z0-9\s]/', ' ', $value) ?? $value;
        $value = preg_replace('/\s+/', ' ', $value) ?? $value;

        return trim($value);
    }

    public function writeoffExpired(int $variantId)
    {
        $today = Carbon::today();

        $expiredBatches = ImportItem::where('product_variant_id', $variantId)
            ->where('remaining_quantity', '>', 0)
            ->whereNotNull('expired_at')
            ->whereDate('expired_at', '<', $today)
            ->get();

        if ($expiredBatches->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy lô hàng hết hạn còn tồn kho.',
            ], 422);
        }

        DB::transaction(function () use ($expiredBatches, $variantId) {
            $totalWrittenOff = 0;

            foreach ($expiredBatches as $batch) {
                $qty = (int) $batch->remaining_quantity;
                $cost = (float) $batch->unit_price;

                $writeoffData = [
                    'product_variant_id' => $variantId,
                    'import_item_id'     => $batch->id,
                    'quantity_written_off' => $qty,
                    'unit_cost'          => $cost,
                    'total_cost'         => $qty * $cost,
                    'reason'             => 'expired',
                    'written_off_by'     => Auth::id(),
                ];
                if (Schema::hasColumns('inventory_writeoffs', ['discovered_by', 'discovered_at'])) {
                    $writeoffData['discovered_by'] = Auth::id();
                    $writeoffData['discovered_at'] = now();
                }
                InventoryWriteoff::create($writeoffData);

                $batch->update(['remaining_quantity' => 0]);
                $totalWrittenOff += $qty;
            }

            $inventory = Inventory::where('product_variant_id', $variantId)->first();
            if ($inventory) {
                $newQty = max(0, $inventory->quantity - $totalWrittenOff);
                $inventory->update(['quantity' => $newQty]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Đã xuất kho ' . $expiredBatches->count() . ' lô hàng hết hạn.',
        ]);
    }

    /**
     * ƯU TIÊN 1: Writeoff trực tiếp hàng lỗi từ inventory
     * POST /admin/inventories/{variantId}/writeoff-direct
     */
    public function writeoffDirect(Request $request, int $variantId)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|in:damaged,broken_packaging,water_damage,manufacturing_flaw,color_fading,contaminated,stock_adjustment,other',
            'note' => 'nullable|string|max:255',
        ]);

        $inventory = Inventory::where('product_variant_id', $variantId)->firstOrFail();

        if ((int) $inventory->quantity < (int) $validated['quantity']) {
            return response()->json([
                'success' => false,
                'message' => 'Số lượng hủy vượt quá tồn kho hiện tại (' . $inventory->quantity . ').',
            ], 422);
        }

        DB::transaction(function () use ($validated, $variantId, $inventory) {
            $quantity = (int) $validated['quantity'];
            $totalWrittenOff = 0;

            // FIFO: Take from earliest batches
            $remainingQty = $quantity;
            $batches = ImportItem::where('product_variant_id', $variantId)
                ->where('remaining_quantity', '>', 0)
                ->orderBy('id', 'asc')
                ->get();

            foreach ($batches as $batch) {
                if ($remainingQty <= 0) break;

                $qtyToWriteoff = min($remainingQty, (int) $batch->remaining_quantity);
                $cost = (float) $batch->unit_price;
                $totalCost = $qtyToWriteoff * $cost;

                $writeoffData = [
                    'product_variant_id' => $variantId,
                    'import_item_id'     => $batch->id,
                    'quantity_written_off' => $qtyToWriteoff,
                    'unit_cost'          => $cost,
                    'total_cost'         => $totalCost,
                    'reason'             => $validated['reason'],
                    'note'               => $validated['note'],
                    'written_off_by'     => Auth::id(),
                ];
                if (Schema::hasColumns('inventory_writeoffs', ['discovered_by', 'discovered_at'])) {
                    $writeoffData['discovered_by'] = Auth::id();
                    $writeoffData['discovered_at'] = now();
                }
                InventoryWriteoff::create($writeoffData);

                $batch->update(['remaining_quantity' => (int) $batch->remaining_quantity - $qtyToWriteoff]);
                $totalWrittenOff += $qtyToWriteoff;
                $remainingQty -= $qtyToWriteoff;
            }

            // Update main inventory
            $newQty = max(0, (int) $inventory->quantity - $totalWrittenOff);
            $inventory->update(['quantity' => $newQty]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Đã hủy ' . $validated['quantity'] . ' sản phẩm lỗi thành công.',
        ]);
    }

    public function batchPopup(int $variantId)
    {
        $inventory = Inventory::with(['variant.product'])
            ->where('product_variant_id', $variantId)
            ->firstOrFail();

        $batchItems = ImportItem::query()
            ->with(['import.supplier', 'import.staff'])
            ->where('product_variant_id', $variantId)
            ->orderByDesc('id')
            ->get();

        $variant = $inventory->variant;
        $sellingPrice = (float) ($variant->price ?? 0);

        return view('admin.inventories.partials.batch_price_popup', [
            'inventory' => $inventory,
            'variant' => $variant,
            'batchItems' => $batchItems,
            'sellingPrice' => $sellingPrice,
        ]);
    }

    private function syncInventoryNotifications(Collection $inventories): void
    {
        $recipientIds = Notification::recipientIdsForGroups(['admin', 'warehouse']);

        if (empty($recipientIds)) {
            return;
        }

        $activeNotificationIds = [];

        foreach ($inventories as $inventory) {
            if (empty($inventory->alert_tags)) {
                continue;
            }

            $productName = $inventory->variant?->product?->name ?? 'Sản phẩm';
            $sku = $inventory->variant?->sku ?? 'N/A';
            $variantId = $inventory->product_variant_id;

            $payloads = [];

            if ($inventory->is_out_of_stock) {
                $payloads[] = [
                    'type' => 'inventory_out_of_stock',
                    'title' => 'Cảnh báo hết hàng',
                    'content' => "{$productName} ({$sku}) đã hết hàng.",
                ];
            }

            if ($inventory->is_low_stock) {
                $payloads[] = [
                    'type' => 'inventory_low_stock',
                    'title' => 'Cảnh báo sắp hết hàng',
                    'content' => "{$productName} ({$sku}) chỉ còn {$inventory->quantity} sản phẩm trong kho.",
                ];
            }

            if ($inventory->is_expired) {
                $payloads[] = [
                    'type' => 'inventory_expired',
                    'title' => 'Cảnh báo hết hạn sử dụng',
                    'content' => "{$productName} ({$sku}) đã hết hạn sử dụng.",
                ];
            }

            if ($inventory->is_expiring_soon) {
                $days = max(0, (int) $inventory->days_to_expire);
                $payloads[] = [
                    'type' => 'inventory_expiring_soon',
                    'title' => 'Cảnh báo sắp hết hạn',
                    'content' => "{$productName} ({$sku}) sẽ hết hạn sau {$days} ngày.",
                ];
            }

            if ($inventory->is_stale) {
                $age = (int) ($inventory->stock_age_days ?? 0);
                $payloads[] = [
                    'type' => 'inventory_stale_stock',
                    'title' => 'Cảnh báo tồn kho lâu',
                    'content' => "{$productName} ({$sku}) đã tồn kho {$age} ngày.",
                ];
            }

            foreach ($recipientIds as $recipientId) {
                foreach ($payloads as $payload) {
                    $notification = Notification::firstOrNew([
                        'user_id' => $recipientId,
                        'type' => $payload['type'],
                        'related_id' => $variantId,
                    ]);

                    $oldTitle = $notification->title;
                    $oldContent = $notification->content;

                    $notification->title = $payload['title'];
                    $notification->content = $payload['content'];

                    if (!$notification->exists) {
                        $notification->is_read = false;
                    } elseif ($oldTitle !== $payload['title'] || $oldContent !== $payload['content']) {
                        $notification->is_read = false;
                    }

                    $notification->save();
                    $activeNotificationIds[] = $notification->id;
                }
            }
        }

        $inventoryTypes = [
            'inventory_out_of_stock',
            'inventory_low_stock',
            'inventory_expired',
            'inventory_expiring_soon',
            'inventory_stale_stock',
        ];

        $cleanupQuery = Notification::query()
            ->whereIn('user_id', $recipientIds)
            ->whereIn('type', $inventoryTypes);

        if (!empty($activeNotificationIds)) {
            $cleanupQuery->whereNotIn('id', $activeNotificationIds);
        }

        $cleanupQuery->delete();
    }
}
