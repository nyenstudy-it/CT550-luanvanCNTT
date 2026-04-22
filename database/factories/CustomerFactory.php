<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Load Vietnam JSON data to get provinces, districts, wards
        $jsonPath = public_path('data/vietnam.json');
        $vietnamData = json_decode(file_get_contents($jsonPath), true);

        // Get random province
        $provinceCode = array_key_first($vietnamData);
        $province = $vietnamData[$provinceCode];

        // Get random district
        $districtCode = array_key_first($province['quan-huyen']);
        $district = $province['quan-huyen'][$districtCode];

        // Get random ward
        $wardCode = array_key_first($district['xa-phuong']);
        $ward = $district['xa-phuong'][$wardCode];

        return [
            'user_id' => User::factory(),
            'phone' => $this->faker->regexify('09[0-9]{8}'),
            'address' => $this->faker->streetAddress(),
            'province' => $provinceCode,
            'district' => $districtCode,
            'ward' => $wardCode,
            'date_of_birth' => $this->faker->dateTimeBetween('-60 years', '-18 years')->format('Y-m-d'),
            'gender' => $this->faker->randomElement(['male', 'female', 'other']),
            'is_default_address' => true,
        ];
    }
}
