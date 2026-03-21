<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('blog_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_id')->constrained('blogs')->cascadeOnDelete();
            $table->enum('type', ['text', 'image']); // loại block
            $table->text('content')->nullable();      // nếu là text
            $table->string('image')->nullable();      // nếu là image
            $table->integer('position')->default(0);  // thứ tự hiển thị
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_blocks');
    }
};
