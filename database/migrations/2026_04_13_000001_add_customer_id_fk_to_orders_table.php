<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Bước 1: Fix orphaned customer_ids (set to NULL hoặc xóa)
        // Tìm những orders có customer_id = 0 hoặc không tồn tại trong users
        DB::statement(<<<SQL
            DELETE FROM orders 
            WHERE customer_id NOT IN (SELECT id FROM users) 
            AND customer_id IS NOT NULL
        SQL);

        // Bước 2: Thêm FK constraint cho customer_id
        Schema::table('orders', function (Blueprint $table) {
            $table->foreign('customer_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Drop FK constraint
            $table->dropForeign(['customer_id']);
        });
    }
};
