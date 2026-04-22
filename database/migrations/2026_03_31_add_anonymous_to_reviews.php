<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            if (!Schema::hasColumn('reviews', 'is_anonymous')) {
                $table->boolean('is_anonymous')->default(false)->after('content');
            }
            if (!Schema::hasColumn('reviews', 'order_id')) {
                $table->foreignId('order_id')->nullable()->after('customer_id')->constrained()->cascadeOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropColumn('is_anonymous');
            $table->dropForeignIdFor(\App\Models\Order::class);
        });
    }
};
