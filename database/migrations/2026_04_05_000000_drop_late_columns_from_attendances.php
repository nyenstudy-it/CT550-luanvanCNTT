<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Drop deprecated late approval workflow columns
            $table->dropColumn([
                'late_reason',
                'late_status',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->text('late_reason')->nullable();
            $table->enum('late_status', ['pending', 'approved', 'rejected'])
                ->nullable();
        });
    }
};
