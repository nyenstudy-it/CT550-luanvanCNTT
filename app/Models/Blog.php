<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Blog extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'summary',
        'content',
        'image'
    ];

    // Tạo slug tự động nếu muốn
    public static function booted()
    {
        static::creating(function ($blog) {
            if (!$blog->slug) {
                $blog->slug = Str::slug($blog->title) . '-' . time();
            }
        });
    }
    public function blocks()
    {
        return $this->hasMany(BlogBlock::class)->orderBy('position');
    }
}
