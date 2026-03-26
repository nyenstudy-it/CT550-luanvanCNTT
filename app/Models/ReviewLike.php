<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Customer;

class ReviewLike extends Model
{
    protected $fillable = [
        'review_id',
        'customer_id'
    ];

    public function review()
    {
        return $this->belongsTo(Review::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}
