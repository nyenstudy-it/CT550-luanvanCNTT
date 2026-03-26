<?php

namespace App\Services;

use App\Models\Discount;
use App\Models\Product;
use Illuminate\Support\Collection;

class ProductPricingService
{
    private ?Collection $activeDiscounts = null;

    public function pricingForProduct(Product $product, float $basePrice): array
    {
        $basePrice = max(0, (float) $basePrice);
        $discount = $this->resolveBestDiscount($product, $basePrice);

        if (!$discount) {
            return [
                'base_price' => $basePrice,
                'final_price' => $basePrice,
                'discount_amount' => 0,
                'has_discount' => false,
                'discount_label' => null,
            ];
        }

        $discountAmount = $this->calculateDiscountAmount($discount, $basePrice);
        $finalPrice = max(0, $basePrice - $discountAmount);

        return [
            'base_price' => $basePrice,
            'final_price' => $finalPrice,
            'discount_amount' => $discountAmount,
            'has_discount' => $discountAmount > 0,
            'discount_label' => $this->formatDiscountLabel($discount),
        ];
    }

    public function enrichProducts(Collection $products): Collection
    {
        return $products->map(function (Product $product) {
            $basePrice = (float) optional($product->variants->first())->price;
            $pricing = $this->pricingForProduct($product, $basePrice);

            $product->setAttribute('display_base_price', $pricing['base_price']);
            $product->setAttribute('display_final_price', $pricing['final_price']);
            $product->setAttribute('display_discount_amount', $pricing['discount_amount']);
            $product->setAttribute('display_has_discount', $pricing['has_discount']);
            $product->setAttribute('display_discount_label', $pricing['discount_label']);

            return $product;
        });
    }

    private function resolveBestDiscount(Product $product, float $basePrice): ?Discount
    {
        $bestDiscount = null;
        $bestAmount = 0;

        foreach ($this->getActiveDiscounts() as $discount) {
            if (!$this->isDiscountApplicableToProduct($discount, $product->id)) {
                continue;
            }

            if ($discount->min_order_value && $basePrice < (float) $discount->min_order_value) {
                continue;
            }

            $amount = $this->calculateDiscountAmount($discount, $basePrice);

            if ($amount > $bestAmount) {
                $bestAmount = $amount;
                $bestDiscount = $discount;
            }
        }

        return $bestDiscount;
    }

    private function calculateDiscountAmount(Discount $discount, float $basePrice): float
    {
        $amount = (float) $discount->getDiscountAmount($basePrice);

        if ($discount->type === 'percent' && !is_null($discount->max_discount)) {
            $amount = min($amount, (float) $discount->max_discount);
        }

        return min($amount, $basePrice);
    }

    private function isDiscountApplicableToProduct(Discount $discount, int $productId): bool
    {
        return $discount->products->contains('id', $productId);
    }

    private function getActiveDiscounts(): Collection
    {
        if ($this->activeDiscounts !== null) {
            return $this->activeDiscounts;
        }

        $now = now();

        $this->activeDiscounts = Discount::with('products:id')
            ->whereHas('products')
            ->where(function ($q) use ($now) {
                $q->whereNull('start_at')->orWhere('start_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('end_at')->orWhere('end_at', '>=', $now);
            })
            ->where(function ($q) {
                $q->whereNull('usage_limit')->orWhereColumn('used_count', '<', 'usage_limit');
            })
            ->get();

        return $this->activeDiscounts;
    }

    private function formatDiscountLabel(Discount $discount): string
    {
        if ($discount->type === 'percent') {
            return '-' . rtrim(rtrim(number_format((float) $discount->value, 2, '.', ''), '0'), '.') . '%';
        }

        return '-' . number_format((float) $discount->value, 0, ',', '.') . ' đ';
    }
}
