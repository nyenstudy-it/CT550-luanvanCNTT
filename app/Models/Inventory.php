<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Inventory extends Model
{
    protected $primaryKey = 'product_variant_id';
    public $incrementing = false;

    protected $fillable = ['product_variant_id', 'quantity'];

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
