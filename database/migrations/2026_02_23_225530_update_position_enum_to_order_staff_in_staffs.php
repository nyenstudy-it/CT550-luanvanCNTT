<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            UPDATE staffs
            SET position = 'order_staff'
            WHERE position = 'delivery'
        ");

        DB::statement("
            ALTER TABLE staffs
            MODIFY position ENUM(
                'cashier',
                'warehouse',
                'order_staff'
            ) NULL
        ");
    }

    public function down(): void
    {
        DB::statement("
            UPDATE staffs
            SET position = 'delivery'
            WHERE position = 'order_staff'
        ");

        DB::statement("
            ALTER TABLE staffs
            MODIFY position ENUM(
                'cashier',
                'warehouse',
                'delivery'
            ) NULL
        ");
    }
};
