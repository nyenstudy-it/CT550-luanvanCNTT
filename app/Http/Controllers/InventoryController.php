<?php

namespace App\Http\Controllers;

use App\Models\ImportItem;
use Illuminate\Http\Request;
use App\Models\Inventory;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

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
        $staleDays = max(1, (int) $request->query('stale_days', 60));

        $today = Carbon::today();

        $query = Inventory::with(['variant.product']);

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

        $batchStats = ImportItem::query()
            ->where('remaining_quantity', '>', 0)
            ->selectRaw('product_variant_id, MIN(created_at) as oldest_batch_at, SUM(remaining_quantity) as total_remaining_quantity')
            ->groupBy('product_variant_id')
            ->get()
            ->keyBy('product_variant_id');

        $enrichedInventories = $baseInventories->map(function (Inventory $inventory) use (
            $batchStats,
            $today,
            $lowStockThreshold,
            $expiringInDays,
            $staleDays
        ) {
            $variant = $inventory->variant;
            $expiryDate = $variant?->expired_at ? Carbon::parse($variant->expired_at)->startOfDay() : null;
            $quantity = (int) $inventory->quantity;

            $batchStat = $batchStats->get($inventory->product_variant_id);
            $oldestBatchAt = $batchStat?->oldest_batch_at ? Carbon::parse($batchStat->oldest_batch_at)->startOfDay() : null;
            $stockAgeDays = $oldestBatchAt ? $oldestBatchAt->diffInDays($today) : null;

            $isOutOfStock = $quantity <= 0;
            $isLowStock = !$isOutOfStock && $quantity <= $lowStockThreshold;
            $isExpired = $expiryDate ? $expiryDate->lt($today) : false;
            $isExpiringSoon = $expiryDate
                ? ($expiryDate->gte($today) && $expiryDate->lte($today->copy()->addDays($expiringInDays)))
                : false;
            $isStale = $stockAgeDays !== null && $stockAgeDays >= $staleDays && $quantity > 0;

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
            $inventory->setAttribute('is_stale', $isStale);
            $inventory->setAttribute('risk_score', $riskScore);
            $inventory->setAttribute('alert_tags', $alerts);
            $inventory->setAttribute('total_remaining_batch_quantity', (int) ($batchStat?->total_remaining_quantity ?? 0));

            return $inventory;
        });

        $this->syncInventoryNotifications($enrichedInventories);

        $summary = [
            'total_variants' => $enrichedInventories->count(),
            'out_of_stock' => $enrichedInventories->where('is_out_of_stock', true)->count(),
            'low_stock' => $enrichedInventories->where('is_low_stock', true)->count(),
            'expired' => $enrichedInventories->where('is_expired', true)->count(),
            'expiring_soon' => $enrichedInventories->where('is_expiring_soon', true)->count(),
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
            default => $filteredInventories->sortByDesc('risk_score')->values(),
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
            'staleDays'
        ));
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
        $recipientIds = User::query()
            ->whereIn('role', ['admin', 'staff', 'warehouse', 'order_staff'])
            ->pluck('id')
            ->all();

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
