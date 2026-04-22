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
        Schema::table('inventory_writeoffs', function (Blueprint $table) {
            // Add reason values: broken_packaging, water_damage, manufacturing_flaw, color_fading, contaminated, stock_adjustment
            // Modify the enum type to include new reasons
            $table->dropColumn('reason');

            $table->enum('reason', [
                'expired',
                'damaged',
                'broken_packaging',
                'water_damage',
                'manufacturing_flaw',
                'color_fading',
                'contaminated',
                'stock_adjustment',
                'other'
            ])->default('expired')->after('total_cost');

            // Add tracking columns for who discovered the defect
            $table->unsignedBigInteger('discovered_by')->nullable()->comment('Ai phát hiện sản phẩm lỗi');
            $table->timestamp('discovered_at')->nullable()->comment('Khi nào phát hiện');

            // Add foreign key for discovered_by
            $table->foreign('discovered_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_writeoffs', function (Blueprint $table) {
            // Drop foreign key and columns
            $table->dropForeign(['discovered_by']);
            $table->dropColumn('discovered_by', 'discovered_at');

            // Restore original enum
            $table->dropColumn('reason');
            $table->enum('reason', ['expired', 'damaged', 'other'])->default('expired')->after('total_cost');
        });
    }
};
