<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ProductVariant;


class ImportItem extends Model
{
    protected $fillable = [
        'import_id',
        'product_variant_id',
        'quantity',
        'unit_price'
    ];

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
