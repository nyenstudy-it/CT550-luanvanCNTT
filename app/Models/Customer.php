<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Order;

class Customer extends Model
{
    protected $fillable = [
        'user_id',
        'phone',
        'address',
        'province',
        'district',
        'ward',
        'date_of_birth',
        'gender',
        'is_default_address'
    ];

    protected $casts = [
        'is_default_address' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orders()
    {
        // Orders currently store user ids in `customer_id` column. To support
        // existing data, fetch orders where orders.customer_id == users.id
        // via hasManyThrough on User if necessary. Keep this helper for
        // code that expects Customer->orders() returning orders belonging
        // to the customer's user record.
        return $this->hasMany(Order::class, 'customer_id');
    }

    public function getFullAddressAttribute()
    {
        $jsonPath = public_path('data/vietnam.json');

        if (!file_exists($jsonPath)) {
            return (string) ($this->address ?? '');
        }

        $data = json_decode(file_get_contents($jsonPath), true);

        $provinceName = '';
        $districtName = '';
        $wardName = '';

        if (isset($data[$this->province])) {

            $province = $data[$this->province];
            $provinceName = $province['name_with_type'] ?? '';

            if (isset($province['quan-huyen'][$this->district])) {

                $district = $province['quan-huyen'][$this->district];
                $districtName = $district['name_with_type'] ?? '';

                if (isset($district['xa-phuong'][$this->ward])) {

                    $ward = $district['xa-phuong'][$this->ward];
                    $wardName = $ward['name_with_type'] ?? '';
                }
            }
        }

        $fullAddress = collect([
            $this->address,
            $wardName,
            $districtName,
            $provinceName
        ])->filter()->implode(', ');

        return $fullAddress;
    }
}
