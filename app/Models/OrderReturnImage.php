<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\OrderReturn;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderReturnImage extends Model
{
    protected $fillable = ['order_return_id', 'image_path'];

    public function return()
    {
        return $this->belongsTo(OrderReturn::class, 'order_return_id');
    }

    
}
