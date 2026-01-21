<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('staff_id');

            $table->date('work_date');

            $table->enum('shift', ['morning', 'afternoon']);

            $table->time('expected_check_in');
            $table->time('expected_check_out');

            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();

            $table->timestamps();

            // FK
            $table->foreign('staff_id')
                ->references('user_id')
                ->on('staffs')
                ->onDelete('cascade');

            // Mỗi nhân viên – mỗi ngày – mỗi ca chỉ 1 dòng
            $table->unique(['staff_id', 'work_date', 'shift']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
