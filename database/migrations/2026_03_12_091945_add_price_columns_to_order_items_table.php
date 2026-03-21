<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {

            $table->decimal('subtotal', 12, 2)->after('price');

            $table->decimal('cost_price', 12, 2)->after('subtotal');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {

            $table->dropColumn('subtotal');

            $table->dropColumn('cost_price');
        });
    }
};
