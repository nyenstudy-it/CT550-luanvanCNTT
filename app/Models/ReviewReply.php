<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewReply extends Model
{
    protected $fillable = [
        'review_id',
        'parent_id',
        'user_id',
        'author_name',
        'is_admin',
        'content',
        'status'
    ];

    public function review()
    {
        return $this->belongsTo(Review::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function children()
    {
        return $this->hasMany(ReviewReply::class, 'parent_id');
    }
}
