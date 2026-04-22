<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class Discount extends Model
{
    public const AUDIENCE_ALL = 'all';
    public const AUDIENCE_NEW_CUSTOMER = 'new_customer';
    public const AUDIENCE_RETURNING_CUSTOMER = 'returning_customer';

    protected $table = 'discounts';

    protected $fillable = [
        'code',
        'type',
        'value',
        'max_discount',
        'audience',
        'usage_limit',
        'used_count',
        'min_order_value',
        'start_at',
        'end_at',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_order_value' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    public static function audienceOptions(): array
    {
        return [
            self::AUDIENCE_ALL => 'Tất cả khách hàng',
            self::AUDIENCE_NEW_CUSTOMER => 'Khách mới đăng ký',
            self::AUDIENCE_RETURNING_CUSTOMER => 'Khách quay lại mua',
        ];
    }

    public function getAudienceLabelAttribute(): string
    {
        return static::audienceOptions()[$this->audience ?? self::AUDIENCE_ALL] ?? 'Tất cả khách hàng';
    }

    public function getValueLabelAttribute(): string
    {
        if ($this->type === 'percent') {
            $label = rtrim(rtrim(number_format((float) $this->value, 2, '.', ''), '0'), '.') . '%';

            if (!is_null($this->max_discount)) {
                $label .= ' (tối đa ' . number_format((float) $this->max_discount, 0, ',', '.') . ' đ)';
            }

            return $label;
        }

        return number_format((float) $this->value, 0, ',', '.') . ' đ';
    }

    public function getAudienceRestrictionMessageAttribute(): ?string
    {
        return match ($this->audience ?? self::AUDIENCE_ALL) {
            self::AUDIENCE_NEW_CUSTOMER => 'Mã này chỉ áp dụng cho khách hàng mới chưa có đơn hoàn thành.',
            self::AUDIENCE_RETURNING_CUSTOMER => 'Mã này chỉ áp dụng cho khách hàng đã có đơn hoàn thành trước đó.',
            default => null,
        };
    }

    public function isActive(): bool
    {
        $now = Carbon::now();

        if (array_key_exists('is_active', $this->attributes) && !$this->is_active) {
            return false;
        }

        if ($this->start_at && $this->start_at->gt($now)) {
            return false;
        }

        if ($this->end_at && $this->end_at->lt($now)) {
            return false;
        }

        if ($this->usage_limit && $this->used_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    public function incrementUsed(): void
    {
        $this->increment('used_count');
    }

    public function getDiscountAmount(float $total): float
    {
        if (!$this->isActive()) return 0;

        if ($this->type === 'percent') {
            $amount = round($total * $this->value / 100, 2);

            if (!is_null($this->max_discount)) {
                $amount = min($amount, (float) $this->max_discount);
            }

            return min($amount, $total);
        }

        return min((float) $this->value, $total);
    }

    public function isEligibleForCompletedOrdersCount(?int $completedOrdersCount): bool
    {
        return match ($this->audience ?? self::AUDIENCE_ALL) {
            self::AUDIENCE_NEW_CUSTOMER => $completedOrdersCount === 0,
            self::AUDIENCE_RETURNING_CUSTOMER => !is_null($completedOrdersCount) && $completedOrdersCount > 0,
            default => true,
        };
    }

    public function isEligibleForUser(?User $user): bool
    {
        if (!$user || !$user->isCustomer()) {
            return $this->isEligibleForCompletedOrdersCount(null);
        }

        $completedOrdersCount = $user->orders()
            ->where('status', 'completed')
            ->count();

        return $this->isEligibleForCompletedOrdersCount($completedOrdersCount);
    }

    public function usages()
    {
        return $this->hasMany(DiscountUsage::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'discount_product', 'discount_id', 'product_id');
    }

    public function isProductScoped(): bool
    {
        if ($this->relationLoaded('products')) {
            return $this->products->isNotEmpty();
        }

        return $this->products()->exists();
    }

    public function getEligibleSubtotal(Collection $cartItems): float
    {
        if (!$this->isProductScoped()) {
            return (float) $cartItems->sum(function ($item) {
                return (float) ($item['price'] ?? 0) * (int) ($item['quantity'] ?? 0);
            });
        }

        $productIds = $this->relationLoaded('products')
            ? $this->products->pluck('id')
            : $this->products()->pluck('products.id');

        return (float) $cartItems
            ->filter(function ($item) use ($productIds) {
                return isset($item['product_id']) && $productIds->contains((int) $item['product_id']);
            })
            ->sum(function ($item) {
                return (float) ($item['price'] ?? 0) * (int) ($item['quantity'] ?? 0);
            });
    }
}
