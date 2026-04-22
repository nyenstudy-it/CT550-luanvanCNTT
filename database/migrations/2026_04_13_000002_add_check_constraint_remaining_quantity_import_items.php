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
        // Thêm CHECK constraint cho remaining_quantity
        // Ensure: 0 <= remaining_quantity <= quantity
        Schema::table('import_items', function (Blueprint $table) {
            // MySQL/MariaDB syntax for CHECK constraint
            DB::statement('ALTER TABLE import_items ADD CONSTRAINT chk_remaining_quantity 
                CHECK (remaining_quantity >= 0 AND remaining_quantity <= quantity)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('import_items', function (Blueprint $table) {
            // Drop CHECK constraint
            DB::statement('ALTER TABLE import_items DROP CONSTRAINT chk_remaining_quantity');
        });
    }
};
