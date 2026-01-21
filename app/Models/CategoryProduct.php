<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Product;

class CategoryProduct extends Model
{
    protected $table = 'category_products';

    protected $fillable = [
        'name',
        'description',
        'image_url',
    ];

    // public function products()
    // {
    //     return $this->hasMany(Product::class, 'category_product_id');
    // }
}
