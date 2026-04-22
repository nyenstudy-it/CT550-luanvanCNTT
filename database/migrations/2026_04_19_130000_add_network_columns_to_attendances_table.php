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
        Schema::table('attendances', function (Blueprint $table) {
            if (!Schema::hasColumn('attendances', 'check_in_network_type')) {
                $table->string('check_in_network_type', 50)->nullable()->after('check_in_ip');
            }

            if (!Schema::hasColumn('attendances', 'check_in_verification_method')) {
                $table->string('check_in_verification_method', 20)->nullable()->after('check_in_network_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            if (Schema::hasColumn('attendances', 'check_in_verification_method')) {
                $table->dropColumn('check_in_verification_method');
            }

            if (Schema::hasColumn('attendances', 'check_in_network_type')) {
                $table->dropColumn('check_in_network_type');
            }
        });
    }
};
