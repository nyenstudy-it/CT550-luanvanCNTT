<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Customer;
use App\Models\Contact;

class ContactsSeeder extends Seeder
{
    public function run(): void
    {
        $customerCount = Customer::count();
        $existingContacts = Contact::count();

        $this->command->info("Customers: $customerCount, existing contacts: $existingContacts");

        $toCreate = 5;

        $customers = Customer::with('user')->inRandomOrder()->take($toCreate)->get();

        $created = 0;

        foreach ($customers as $c) {
            $name = $c->user->name ?? ('Khách hàng ' . Str::random(4));
            $email = $c->user->email ?? (Str::slug($name) . '@example.test');

            Contact::create([
                'name' => $name,
                'email' => $email,
                'message' => 'Xin chào, tôi muốn hỏi về sản phẩm. Đây là tin nhắn mẫu #' . ($created + 1),
                'status' => 'pending',
            ]);

            $created++;
        }

        // If there were fewer than required customers, create synthetic contacts
        if ($created < $toCreate) {
            for ($i = $created; $i < $toCreate; $i++) {
                $name = 'Khách mẫu ' . ($i + 1);
                Contact::create([
                    'name' => $name,
                    'email' => 'test' . ($i + 1) . '@example.test',
                    'message' => 'Tin nhắn mẫu đến cửa hàng #' . ($i + 1),
                    'status' => 'pending',
                ]);
            }
        }

        $this->command->info("Inserted $toCreate sample contacts.");
    }
}
