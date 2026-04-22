<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('customer_messages', function (Blueprint $table) {
            // Add additional indexes for better query performance
            // Query: is_read = false AND sender_type IN ('staff', 'admin')
            if (!$this->indexExists('customer_messages', 'idx_unread_by_type')) {
                $table->index(['is_read', 'sender_type'], 'idx_unread_by_type');
            }
            // Query: ORDER BY created_at DESC
            if (!$this->indexExists('customer_messages', 'idx_created_desc')) {
                $table->index(['created_at'], 'idx_created_desc');
            }
        });
    }

    public function down()
    {
        Schema::table('customer_messages', function (Blueprint $table) {
            $table->dropIndexIfExists('idx_unread_by_type');
            $table->dropIndexIfExists('idx_created_desc');
        });
    }

    private function indexExists($table, $indexName)
    {
        return collect(\Illuminate\Support\Facades\DB::select(
            "SHOW INDEX FROM {$table} WHERE Key_name = ?",
            [$indexName]
        ))->isNotEmpty();
    }
};
