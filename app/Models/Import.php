<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ImportItem;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Import extends Model
{
    protected $fillable = [
        'staff_id',
        'supplier_id',
        'import_date',
        'total_amount'
    ];

    public function items()
    {
        return $this->hasMany(ImportItem::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    
}
