<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class Discount extends Model
{
    protected $table = 'discounts';

    protected $fillable = [
        'code',
        'type',
        'value',
        'usage_limit',
        'used_count',
        'min_order_value',
        'start_at',
        'end_at',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_order_value' => 'decimal:2',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];


    public function isActive(): bool
    {
        $now = Carbon::now();

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
            return round($total * $this->value / 100, 2);
        }

        return min($this->value, $total); 
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
