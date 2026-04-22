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
            // Index for incremental loading queries (get messages after ID)
            $table->index(['customer_id', 'id'], 'idx_msg_customer_id');

            // Index for latest messages per customer (GROUP BY + MAX queries)
            $table->index(['id', 'customer_id'], 'idx_msg_id_customer');

            // Index for marking messages as read
            $table->index(['customer_id', 'is_read', 'sender_type'], 'idx_msg_unread');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_messages', function (Blueprint $table) {
            $table->dropIndex('idx_msg_customer_id');
            $table->dropIndex('idx_msg_id_customer');
            $table->dropIndex('idx_msg_unread');
        });
    }
};
