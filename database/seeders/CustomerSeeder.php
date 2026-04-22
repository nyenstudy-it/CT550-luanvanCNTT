<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Customer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Load Vietnam JSON data
        $jsonPath = public_path('data/vietnam.json');
        $vietnamData = json_decode(file_get_contents($jsonPath), true);

        // 20 khách hàng cụ thể - 10 nam + 10 nữ Miền Nam
        $customers = [
            // 10 KHÁCH NAM
            ['name' => 'Lê Đức Huy', 'gender' => 'male'],
            ['name' => 'Nguyễn Thành Nam', 'gender' => 'male'],
            ['name' => 'Lê Minh Sang', 'gender' => 'male'],
            ['name' => 'Lê Quốc Kiệt', 'gender' => 'male'],
            ['name' => 'Trần Bình Trọng', 'gender' => 'male'],
            ['name' => 'Nguyễn Minh Tuấn', 'gender' => 'male'],
            ['name' => 'Lê Phát Tiến', 'gender' => 'male'],
            ['name' => 'Trần Khánh Minh', 'gender' => 'male'],
            ['name' => 'Đặng Minh Anh', 'gender' => 'male'],
            ['name' => 'Lê Anh Duy', 'gender' => 'male'],

            // 10 KHÁCH NỮ
            ['name' => 'Phạm Thị Mỹ Ngọc', 'gender' => 'female'],
            ['name' => 'Trần Thị Ngọc Hiền', 'gender' => 'female'],
            ['name' => 'Đặng Thị Mỹ Hương', 'gender' => 'female'],
            ['name' => 'Võ Anh Thư', 'gender' => 'female'],
            ['name' => 'Lê Nhật Hạ', 'gender' => 'female'],
            ['name' => 'Lý Thị Tường Vy', 'gender' => 'female'],
            ['name' => 'Phan Thu Ngân', 'gender' => 'female'],
            ['name' => 'Nguyễn Thị Loan', 'gender' => 'female'],
            ['name' => 'Lê Thị Ngọc Như', 'gender' => 'female'],
            ['name' => 'Nguyễn Thị Ngọc Trâm', 'gender' => 'female'],
        ];

        // TỈNH MIỀN NAM - tìm từ JSON data
        $southernProvinces = [];
        $southernKeywords = [
            'hồ chí minh',
            'bình dương',
            'đồng nai',
            'long an',
            'tiền giang',
            'bến tre',
            'vĩnh long',
            'tây ninh',
            'cần thơ',
            'sóc trăng',
            'bạc liêu',
            'cà mau',
            'kiên giang',
            'phú yên',
            'khánh hòa',
            'bình thuận'
        ];

        foreach ($vietnamData as $provinceCode => $provinceData) {
            $provinceName = strtolower($provinceData['name'] ?? '');
            foreach ($southernKeywords as $keyword) {
                if (strpos($provinceName, $keyword) !== false) {
                    $southernProvinces[] = $provinceCode;
                    break;
                }
            }
        }

        if (empty($southernProvinces)) {
            $southernProvinces = array_slice(array_keys($vietnamData), 0, 10);
        }

        // ĐƯỜNG PHỐ MIỀN NAM
        $vietnameseStreets = [
            'Nguyễn Huệ',
            'Lê Lợi',
            'Pasteur',
            'Tôn Đức Thắng',
            'Hoàn Kiếm',
            'Trần Hưng Đạo',
            'Nguyễn Công Trứ',
            'Cộng Hòa',
            'Ba Tháng Hai',
            'Phan Văn Trị',
            'Nguyễn Văn Cừ',
            'Quang Trung',
            'Hùng Vương',
            'Lý Thái Tổ',
            'Hàng Bai',
            'Giải Phóng',
            'Phan Chu Trinh',
            'Đinh Tiên Hoàng',
            'Lê Thánh Tôn',
            'Dã Tượng',
            'Cách Mạng Tháng 8',
            'Sư Vạn Hạnh',
            'Phan Dinh Phung',
            'Bùi Viện',
            'Nguyễn Huỳnh Đức',
            'Phan Bội Châu',
            'Tầm Vu',
            'Cao Đạt',
            'Hồ Hảo Hôn',
            'Thích Quảng Đức',
            'Công Trường Lam Sơn',
            'Vạn Kiếp',
            'Phan Xích Long',
            'An Dương Vương',
            'Âu Cơ',
            'Bạch Đằng',
            'Ba Vân',
            'Bà Huyện Thanh Quan',
            'Mạc Đĩnh Chi',
            'Phạm Ngũ Lão',
            'Võ Văn Tần',
            'Phạm Viết Chanh'
        ];

        foreach ($customers as $customerData) {
            $fullName = $customerData['name'];
            $gender = $customerData['gender'];

            // Create email from full name
            $email = strtolower(transliterator_transliterate('Any-Latin; Latin-ASCII; [\u0080-\u7FFF] remove', $fullName));
            $email = preg_replace('/[^a-z0-9]+/', '', $email) . '@gmail.com';

            // Ensure email is unique
            $baseEmail = $email;
            $counter = 1;
            while (User::where('email', $email)->exists()) {
                $email = str_replace('@gmail.com', $counter . '@gmail.com', $baseEmail);
                $counter++;
            }

            // Get random SOUTHERN province
            $provinceKey = $southernProvinces[array_rand($southernProvinces)];
            $province = $vietnamData[$provinceKey];

            // Get random district
            $districtKey = array_rand($province['quan-huyen']);
            $district = $province['quan-huyen'][$districtKey];

            // Get random ward (check if ward exists)
            $wardKey = null;
            if (isset($district['xa-phuong']) && is_array($district['xa-phuong']) && count($district['xa-phuong']) > 0) {
                $wardKey = array_rand($district['xa-phuong']);
            }

            // Create Vietnamese address
            $streetNumber = fake()->numberBetween(1, 999);
            $streetPrefix = ['Số', 'Lô'][array_rand(['Số', 'Lô'])];
            $streetTypeName = ['Đường', 'Phố', 'Ngõ', 'Lô'][array_rand(['Đường', 'Phố', 'Ngõ', 'Lô'])];
            $streetNameVN = $vietnameseStreets[array_rand($vietnameseStreets)];
            $address = "{$streetPrefix} {$streetNumber}, {$streetTypeName} {$streetNameVN}";

            // Create user
            $user = User::create([
                'name' => $fullName,
                'email' => $email,
                'password' => Hash::make('khachhang123'),
                'avatar' => null,
                'role' => 'customer',
                'status' => 'active',
                'email_verified_at' => now(),
            ]);

            // Create customer profile
            Customer::create([
                'user_id' => $user->id,
                'phone' => fake()->regexify('09[0-9]{8}'),
                'address' => $address,
                'province' => $provinceKey,
                'district' => $districtKey,
                'ward' => $wardKey,
                'date_of_birth' => fake()->dateTimeBetween('-60 years', '-18 years')->format('Y-m-d'),
                'gender' => $gender,
                'is_default_address' => true,
            ]);

            $this->command->info("Created customer: {$email} ({$fullName})");
        }
    }
}
