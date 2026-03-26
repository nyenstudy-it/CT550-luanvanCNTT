<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('review_replies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('review_id');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('author_name');
            $table->boolean('is_admin')->default(false);
            $table->text('content');
            $table->string('status')->default('approved');
            $table->timestamps();

            $table->foreign('review_id')->references('id')->on('reviews')->onDelete('cascade');
            $table->foreign('parent_id')->references('id')->on('review_replies')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('review_replies');
    }
};
