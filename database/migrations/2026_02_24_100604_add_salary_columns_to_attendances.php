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
        Schema::table('attendances', function (Blueprint $table) {

            $table->integer('worked_minutes')
                ->nullable()
                ->after('check_out');

            $table->boolean('is_early_leave')
                ->default(false)
                ->after('is_late');

            $table->boolean('is_completed')
                ->default(false)
                ->after('is_early_leave');

            $table->decimal('salary_amount', 12, 2)
                ->nullable()
                ->after('is_completed');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn([
                'worked_minutes',
                'is_early_leave',
                'is_completed',
                'salary_amount'
            ]);
        });
    }
};
