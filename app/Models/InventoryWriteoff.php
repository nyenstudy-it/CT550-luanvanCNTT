<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryWriteoff extends Model
{
    protected $fillable = [
        'product_variant_id',
        'import_item_id',
        'quantity_written_off',
        'unit_cost',
        'total_cost',
        'reason',
        'note',
        'written_off_by',
        'discovered_by',
        'discovered_at',
    ];

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function importItem()
    {
        return $this->belongsTo(ImportItem::class);
    }

    public function writtenBy()
    {
        return $this->belongsTo(User::class, 'written_off_by');
    }

    public function discoveredBy()
    {
        return $this->belongsTo(User::class, 'discovered_by');
    }
}
