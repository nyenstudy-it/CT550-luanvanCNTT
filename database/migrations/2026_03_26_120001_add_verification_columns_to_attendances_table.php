<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->string('check_in_ip', 45)->nullable()->after('check_in');
            $table->decimal('check_in_latitude', 10, 7)->nullable()->after('check_in_ip');
            $table->decimal('check_in_longitude', 10, 7)->nullable()->after('check_in_latitude');
            $table->string('check_in_network_type', 50)->nullable()->after('check_in_longitude');
            $table->decimal('check_in_distance_meters', 8, 2)->nullable()->after('check_in_network_type');
            $table->string('check_in_verification_method', 20)->nullable()->after('check_in_distance_meters');

            $table->string('check_out_ip', 45)->nullable()->after('check_out');
            $table->decimal('check_out_latitude', 10, 7)->nullable()->after('check_out_ip');
            $table->decimal('check_out_longitude', 10, 7)->nullable()->after('check_out_latitude');
            $table->string('check_out_network_type', 50)->nullable()->after('check_out_longitude');
            $table->decimal('check_out_distance_meters', 8, 2)->nullable()->after('check_out_network_type');
            $table->string('check_out_verification_method', 20)->nullable()->after('check_out_distance_meters');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn([
                'check_in_ip',
                'check_in_latitude',
                'check_in_longitude',
                'check_in_network_type',
                'check_in_distance_meters',
                'check_in_verification_method',
                'check_out_ip',
                'check_out_latitude',
                'check_out_longitude',
                'check_out_network_type',
                'check_out_distance_meters',
                'check_out_verification_method',
            ]);
        });
    }
};
