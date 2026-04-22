<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contact extends Model
{
    protected $fillable = ['name', 'email', 'message', 'status', 'reply', 'reply_by', 'replied_at'];

    protected $casts = [
        'replied_at' => 'datetime',
    ];

    /**
     * Get the user who replied to this contact
     */
    public function repliedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reply_by', 'id');
    }
}
