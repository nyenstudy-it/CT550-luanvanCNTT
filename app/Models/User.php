<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

    public function customer(){
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
    public function orders()
    {
        return $this->hasMany(\App\Models\Order::class, 'customer_id', 'id');
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }
}
