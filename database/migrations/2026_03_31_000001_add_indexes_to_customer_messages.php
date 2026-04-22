<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - Add critical indexes for chat performance
     */
    public function up(): void
    {
        Schema::table('customer_messages', function (Blueprint $table) {
            // Index for customer's message history (most important)
            $table->index(['customer_id', 'created_at'], 'idx_customer_created_at');

            // Index for unread count queries (sender_type + is_read + customer_id)
            $table->index(['customer_id', 'sender_type', 'is_read'], 'idx_customer_sender_read');

            // Index for quick lookup by ID (already primary but ensure exists)
            $table->index(['sender_type', 'created_at'], 'idx_sender_created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_messages', function (Blueprint $table) {
            $table->dropIndex('idx_customer_created_at');
            $table->dropIndex('idx_customer_sender_read');
            $table->dropIndex('idx_sender_created_at');
        });
    }
};
