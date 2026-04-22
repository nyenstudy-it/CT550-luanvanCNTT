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
        // Thêm UNIQUE + NOT NULL constraint cho SKU
        // Bước 1: Xóa SKU NULL (nếu có)
        DB::table('product_variants')
            ->whereNull('sku')
            ->delete();

        // Bước 2: Modify SKU column to NOT NULL và UNIQUE
        Schema::table('product_variants', function (Blueprint $table) {
            $table->string('sku')->nullable(false)->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            // Revert SKU to nullable, remove UNIQUE
            $table->dropUnique(['sku']);
            $table->string('sku')->nullable()->change();
        });
    }
};
