<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Re-add late_status that was accidentally dropped
            if (!Schema::hasColumn('attendances', 'late_status')) {
                $table->enum('late_status', ['pending', 'approved', 'rejected'])
                    ->nullable()
                    ->after('is_late');
            }

            // Verify early_leave_status exists
            if (!Schema::hasColumn('attendances', 'early_leave_status')) {
                $table->enum('early_leave_status', ['pending', 'approved', 'rejected'])
                    ->default('pending')
                    ->after('is_early_leave');
            }

            // Re-add late_reason that was accidentally dropped
            if (!Schema::hasColumn('attendances', 'late_reason')) {
                $table->text('late_reason')
                    ->nullable()
                    ->after('is_late');
            }
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn([
                'late_status',
                'late_reason',
            ]);
            // Note: DO NOT drop early_leave_status as it's still in use
        });
    }
};
