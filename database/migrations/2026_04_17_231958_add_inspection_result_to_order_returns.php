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
        Schema::table('order_returns', function (Blueprint $table) {
            // Store inspection result (good/defective) permanently
            // so we don't lose this info when status changes to 'refunded'
            $table->enum('inspection_result', ['good', 'defective'])->nullable()->after('inspection_notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_returns', function (Blueprint $table) {
            $table->dropColumn(['inspection_result']);
        });
    }
};
