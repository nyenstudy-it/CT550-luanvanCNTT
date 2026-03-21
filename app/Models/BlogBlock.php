<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlogBlock extends Model
{
    protected $fillable = ['blog_id', 
                            'type', 
                            'content', 
                            'image', 
                            'position'];

    public function blog()
    {
        return $this->belongsTo(Blog::class);
    }
}
