<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {

            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade');

            $table->string('phone');
            $table->text('address')->nullable();

            $table->string('province')->nullable();
            $table->string('district')->nullable();
            $table->string('ward')->nullable();

            $table->date('date_of_birth')->nullable();

            $table->enum('gender', ['male', 'female', 'other'])
                ->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
