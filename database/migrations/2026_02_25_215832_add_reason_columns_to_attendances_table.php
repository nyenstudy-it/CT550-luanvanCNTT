<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {

            // Lý do trễ
            $table->text('late_reason')->nullable()->after('is_late');
            $table->enum('late_status', ['pending', 'approved', 'rejected'])
                ->default('pending')
                ->after('late_reason');

            // Lý do về sớm
            $table->text('early_leave_reason')->nullable()->after('is_early_leave');
            $table->enum('early_leave_status', ['pending', 'approved', 'rejected'])
                ->default('pending')
                ->after('early_leave_reason');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn([
                'late_reason',
                'late_status',
                'early_leave_reason',
                'early_leave_status'
            ]);
        });
    }
};
