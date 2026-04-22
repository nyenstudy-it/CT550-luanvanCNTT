<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Thêm UNIQUE constraint trên customers.user_id
        // Đảm bảo 1 user = 1 customer
        Schema::table('customers', function (Blueprint $table) {
            $table->unique('user_id', 'unique_customers_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropUnique('unique_customers_user_id');
        });
    }
};
