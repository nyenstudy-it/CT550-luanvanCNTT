<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Thêm column scenario_type để tracking 5 attendance scenarios
     * 
     * 1️⃣  Scenario 1: Vào trễ > 15p, hết ca đúng (không tính full)
     * 2️⃣  Scenario 2: Vào trễ ≤ 15p, hết ca đúng (được tha - FULL)
     * 3️⃣  Scenario 3: Vào đúng, về sớm (chờ duyệt)
     * 4️⃣  Scenario 4: Vào trễ > 15p + về sớm (chờ duyệt - worst case)
     * 5️⃣  Scenario 5: Auto-checkout (quên checkout quá 2 tiếng)
     *
     * @return void
     */
    public function up()
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->unsignedTinyInteger('scenario_type')
                ->default(0)
                ->after('early_leave_status')
                ->comment('1-5: Attendance scenario type indicator');

            // Index để query nhanh theo scenario
            $table->index('scenario_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex(['scenario_type']);
            $table->dropColumn('scenario_type');
        });
    }
};
