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
        Schema::table('import_items', function (Blueprint $table) {
            $table->date('manufacture_date')->nullable()->after('product_variant_id');
            $table->date('expired_at')->nullable()->after('manufacture_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('import_items', function (Blueprint $table) {
            $table->dropColumn(['manufacture_date', 'expired_at']);
        });
    }
};
