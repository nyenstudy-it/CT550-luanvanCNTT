<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('salaries', function (Blueprint $table) {

            $table->integer('late_count')->default(0)->after('total_salary');
            $table->integer('early_leave_count')->default(0)->after('late_count');
            $table->integer('absent_count')->default(0)->after('early_leave_count');

            $table->decimal('penalty_amount', 12, 2)->default(0);
            $table->decimal('bonus_amount', 12, 2)->default(0);

            $table->decimal('final_salary', 12, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('salaries', function (Blueprint $table) {
            $table->dropColumn([
                'late_count',
                'early_leave_count',
                'absent_count',
                'penalty_amount',
                'bonus_amount',
                'final_salary'
            ]);
        });
    }
};
