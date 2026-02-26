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
        Schema::create('salaries', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('staff_id');

            $table->integer('month');
            $table->integer('year');

            $table->integer('total_minutes')->default(0);
            $table->decimal('total_hours', 8, 2)->default(0);
            $table->decimal('total_salary', 12, 2)->default(0);

            $table->timestamps();

            $table->unique(['staff_id', 'month', 'year']);

            $table->foreign('staff_id')
                ->references('user_id')
                ->on('staffs')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salaries');
    }
};
