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
        'gender'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function getFullAddressAttribute()
    {
        if (!$this->address) return '';

        $jsonPath = public_path('data/vietnam.json');

        if (!file_exists($jsonPath)) {
            return $this->address;
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

        return collect([
            $this->address,
            $wardName,
            $districtName,
            $provinceName
        ])->filter()->implode(', ');
    }
}
