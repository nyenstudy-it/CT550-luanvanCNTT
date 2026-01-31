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
        Schema::table('product_variants', function (Blueprint $table) {

            // XÓA CỘT CŨ SAI THIẾT KẾ
            $table->dropColumn('size');

            // THUỘC TÍNH BIẾN THỂ CHUẨN
            $table->string('volume', 50)->nullable()->after('sku');
            $table->string('weight', 50)->nullable()->after('volume');
            $table->string('color', 50)->nullable()->after('weight');
            $table->string('size', 50)->nullable()->after('color');

            // TRẠNG THÁI
            $table->boolean('is_active')->default(true)->after('expired_at');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {

            $table->dropColumn([
                'volume',
                'weight',
                'color',
                'is_active'
            ]);

            // trả lại cột cũ (để rollback)
            $table->string('size', 100);
        });
    }
};
