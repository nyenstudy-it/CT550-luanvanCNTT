<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 1. Chuẩn hóa dữ liệu cũ
        DB::statement("
            UPDATE staffs
            SET position = 'cashier'
            WHERE position IN ('sales')
        ");

        DB::statement("
            UPDATE staffs
            SET position = 'delivery'
            WHERE position IN ('import')
        ");

        DB::statement("
            UPDATE staffs
            SET position = 'warehouse'
            WHERE position NOT IN ('cashier','warehouse','delivery')
            OR position IS NULL
        ");

        // 2. Đổi kiểu cột
        Schema::table('staffs', function (Blueprint $table) {
            $table->enum('position', [
                'cashier',
                'warehouse',
                'delivery'
            ])->nullable()->change();

            $table->enum('employment_type', [
                'fulltime',
                'parttime'
            ])->default('fulltime')->after('position');
        });
    }

    public function down(): void
    {
        Schema::table('staffs', function (Blueprint $table) {
            $table->string('position')->nullable()->change();
            $table->dropColumn('employment_type');
        });
    }
};
