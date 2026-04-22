<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Contact;
use Carbon\Carbon;

class NormalizeContactsSeeder extends Seeder
{
    public function run(): void
    {
        // Fetch the latest 5 contacts (the ones created by recent seeder)
        $latest = Contact::orderBy('id', 'desc')->take(5)->get();
        $ids = $latest->pluck('id')->toArray();

        if (count($ids) === 0) {
            $this->command->info('No contacts found to normalize.');
            return;
        }

        // Realistic messages about the store
        $messages = [
            'Tôi muốn hỏi về chính sách đổi trả cho sản phẩm này, có thể cho tôi biết chi tiết được không?',
            'Sản phẩm còn hàng tại chi nhánh Hà Nội không? Tôi muốn mua và nhận tại cửa hàng.',
            'Xin hỏi thời gian giao hàng thường là bao lâu và có giao nhanh trong ngày không?',
            'Sản phẩm có bảo hành không và nếu cần bảo hành thì thủ tục như thế nào?',
            'Tôi muốn hỏi liệu cửa hàng có nhận đặt trước và giữ hàng cho khách không?'
        ];

        $now = Carbon::now();
        $start = Carbon::create(2026, 4, 1, 8, 0, 0);

        // Update the selected contacts with realistic data and random dates
        foreach ($latest as $i => $contact) {
            $randomDate = Carbon::createFromTimestamp(rand($start->getTimestamp(), $now->getTimestamp()));

            $contact->update([
                'name' => $contact->name ?: ('Khách hàng ' . Str::random(4)),
                'email' => $contact->email ?: 'khach' . ($i + 1) . '@example.test',
                'message' => $messages[$i % count($messages)],
                'status' => 'pending',
                'reply' => null,
                'reply_by' => null,
                'replied_at' => null,
                'created_at' => $randomDate,
                'updated_at' => $randomDate,
            ]);
        }

        // Delete all other contacts, keep only these 5
        Contact::whereNotIn('id', $ids)->delete();

        $this->command->info('Normalized contacts: kept ' . count($ids) . ' contacts with realistic messages and randomized dates.');
    }
}
