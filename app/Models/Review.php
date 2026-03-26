<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Order;
use App\Models\User;
use App\Models\Customer;
use App\Models\ReviewLike;
use App\Models\ReviewReply;

/**
 * @property-read \App\Models\User $customer
 */
class Review extends Model
{
    protected $fillable = ['product_id', 'customer_id', 'rating', 'content', 'status'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    // public function user()
    // {
    //     return $this->belongsTo(User::class);
    // }
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function likes()
    {
        return $this->hasMany(ReviewLike::class);
    }

    public function replies()
    {
        return $this->hasMany(ReviewReply::class);
    }

    /**
     * Top-level replies (no parent)
     */
    public function topReplies()
    {
        return $this->hasMany(ReviewReply::class)->whereNull('parent_id');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
}
