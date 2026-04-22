<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AllowedNetwork extends Model
{
    use HasFactory;

    protected $table = 'allowed_networks';

    protected $fillable = [
        'cidr',
        'label',
    ];
}
