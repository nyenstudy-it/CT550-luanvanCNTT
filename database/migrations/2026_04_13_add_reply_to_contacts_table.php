<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->text('reply')->nullable()->after('message');
            $table->unsignedBigInteger('reply_by')->nullable()->after('reply');
            $table->timestamp('replied_at')->nullable()->after('reply_by');
            $table->enum('status', ['pending', 'read', 'replied'])->change();

            $table->foreign('reply_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropForeignKey(['reply_by']);
            $table->dropColumn(['reply', 'reply_by', 'replied_at']);
            // Reset status enum back
            $table->enum('status', ['pending', 'read'])->change();
        });
    }
};
