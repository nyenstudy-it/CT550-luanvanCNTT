<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'role',
        'status',
        'locked_reason',
        'locked_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function staff()
    {
        return $this->hasOne(Staff::class, 'user_id');
    }

    public function isStaff()
    {
        return $this->role === 'staff';
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function customer()
    {
        return $this->hasOne(Customer::class, 'user_id')->withDefault();
    }

    public function isCustomer()
    {
        return $this->role === 'customer';
    }
    public function isActive()
    {
        return $this->status === 'active';
    }
    /**
     * Backward-compatibility accessor: many callers expect `$model->user_id`.
     * Return the primary `id` so code using `->customer->user_id` continues working
     * when `customer()` resolves to a `User` instance.
     */
    public function getUserIdAttribute()
    {
        return $this->id;
    }
    public function orders()
    {
        // Orders are stored against `customers.id` (customers.user_id -> users.id).
        // Use hasManyThrough to fetch orders for this user's customer record.
        return $this->hasManyThrough(
            \App\Models\Order::class,
            \App\Models\Customer::class,
            'user_id',     // Foreign key on customers table referencing users.id
            'customer_id', // Foreign key on orders table referencing customers.id
            'id',          // Local key on users table
            'id'           // Local key on customers table
        );
    }

    /**
     * @return HasManyThrough
     */
    public function ordersRelation(): HasManyThrough
    {
        return $this->orders();
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }
}
