<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

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
}
