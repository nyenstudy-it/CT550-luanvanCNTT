<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Supplier;
use App\Models\CategoryProduct;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'supplier_id',
        'name',
        'price',
        'description',
        'usage_instructions',
        'manufacture_date',
        'expiry_date',
        'storage_instructions',
        'weight_volume',
        'ocop_star',
        'ocop_year',
        'image',
        'status',
    ];
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function category()
    {
        return $this->belongsTo(CategoryProduct::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }
}
