<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('import_items', function (Blueprint $table) {
            $table->integer('remaining_quantity')
                ->after('quantity')
                ->nullable(false)
                ->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('import_items', function (Blueprint $table) {
            $table->dropColumn('remaining_quantity');
        });
    }
};
