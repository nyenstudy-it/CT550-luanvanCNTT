<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Drop leave_requests and leave_balances tables completely
     */
    public function up(): void
    {
        Schema::dropIfExists('leave_requests');
        Schema::dropIfExists('leave_balances');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We don't recreate these tables since we're permanently removing the feature
    }
};
