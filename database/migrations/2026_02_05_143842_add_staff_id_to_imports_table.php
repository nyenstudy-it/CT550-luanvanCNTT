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
        Schema::table('imports', function (Blueprint $table) {
            $table->unsignedBigInteger('staff_id')->before('supplier_id');

            $table->foreign('staff_id')
                ->references('user_id')
                ->on('staffs');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('imports', function (Blueprint $table) {
            $table->dropForeign(['staff_id']);
            $table->dropColumn('staff_id');
        });
    }
};
