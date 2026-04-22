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
    protected $fillable = ['product_id', 'customer_id', 'order_id', 'rating', 'content', 'is_anonymous', 'status'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    // public function user()
    // {
    //     return $this->belongsTo(User::class);
    // }
    /**
     * `reviews.customer_id` stores the related users.id.
     * Map it to customers.user_id so existing views can still use
     * `$review->customer?->user`.
     */
    public function customer()
    {
        return $this->belongsTo(\App\Models\Customer::class, 'customer_id', 'user_id');
    }

    public function likes()
    {
        return $this->hasMany(ReviewLike::class);
    }

    public function replies()
    {
        return $this->hasMany(ReviewReply::class);
    }

    public function topReplies()
    {
        return $this->hasMany(ReviewReply::class)->whereNull('parent_id');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
}
