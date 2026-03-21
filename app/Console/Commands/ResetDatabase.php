<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:reset-all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Truncate all important tables and run AdminSeeder';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Tắt FOREIGN_KEY_CHECKS...');
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $tables = [
            'users',
            'staffs',
            'customers',
            'category_products',
            'suppliers',
            'products',
            'product_variants',
            'product_images',
            'inventories',
            'imports',
            'import_items',
            'orders',
            'order_items',
            'order_cancellations',
            'payments',
            'discounts',
            'discount_usages',
            'attendances',
            'salaries',
            'sessions'
        ];

        foreach ($tables as $table) {
            $this->info("Truncate bảng: $table");
            DB::table($table)->truncate();
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        $this->info('Đã truncate xong tất cả bảng.');

        // Chạy AdminSeeder
        $this->info('Chạy lại AdminSeeder...');
        \Artisan::call('db:seed', [
            '--class' => 'AdminSeeder'
        ]);
        $this->info('AdminSeeder chạy xong.');

        $this->info('Hoàn tất reset database!');
    }
}
