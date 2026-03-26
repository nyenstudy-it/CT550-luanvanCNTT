<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Supplier;
use App\Models\CategoryProduct;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'supplier_id',
        'name',
        'description',
        'usage_instructions',
        'storage_instructions',
        'ocop_star',
        'ocop_year',
        'image',
        'status'
    ];

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function category()
    {
        return $this->belongsTo(CategoryProduct::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)
            ->whereNull('product_variant_id')
            ->where('is_primary', 1);
    }
    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function product_images()
    {
        return $this->images();
    }
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Only approved reviews (for public display / average calculation)
     */
    public function approvedReviews()
    {
        return $this->hasMany(Review::class)->where('status', 'approved');
    }

    /**
     * Average rating from approved reviews (rounded to 1 decimal)
     */
    public function getAvgRatingAttribute()
    {
        $avg = $this->approvedReviews()->avg('rating');
        return $avg ? round($avg, 1) : 0.0;
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
