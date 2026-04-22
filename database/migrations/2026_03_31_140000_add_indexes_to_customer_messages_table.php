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
        Schema::table('customer_messages', function (Blueprint $table) {
            // Index cho query unreadCount: WHERE customer_id = X AND sender_type != 'customer' AND is_read = false
            $table->index(['customer_id', 'sender_type', 'is_read']);
            // Index cho query getMessages: ORDER BY created_at
            $table->index(['customer_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_messages', function (Blueprint $table) {
            $table->dropIndex(['customer_id', 'sender_type', 'is_read']);
            $table->dropIndex(['customer_id', 'created_at']);
        });
    }
};
