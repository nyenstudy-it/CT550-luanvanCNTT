<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('staffs', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->primary();

            $table->string('phone')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->text('address')->nullable();
            $table->string('position')->nullable();

            $table->date('start_date')->nullable();
            $table->date('probation_start')->nullable();
            $table->date('probation_end')->nullable();

            $table->enum('employment_status', [
                'probation',
                'official',
                'resigned'
            ])->default('probation');

            $table->decimal('probation_hourly_wage', 10, 2)->nullable();
            $table->decimal('official_hourly_wage', 10, 2)->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staffs');
    }
};
