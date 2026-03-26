<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('review_likes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('review_id');
            $table->unsignedBigInteger('customer_id');
            $table->timestamps();

            $table->foreign('review_id')->references('id')->on('reviews')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->unique(['review_id', 'customer_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('review_likes');
    }
};
