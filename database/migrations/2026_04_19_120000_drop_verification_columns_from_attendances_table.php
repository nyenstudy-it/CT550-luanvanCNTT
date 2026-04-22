<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Keep check_in_ip only; drop other verification columns
            $cols = [
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
            ];

            foreach ($cols as $c) {
                if (Schema::hasColumn('attendances', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            if (!Schema::hasColumn('attendances', 'check_in_ip')) {
                $table->string('check_in_ip', 45)->nullable()->after('check_in');
            }
            if (!Schema::hasColumn('attendances', 'check_in_latitude')) {
                $table->decimal('check_in_latitude', 10, 7)->nullable()->after('check_in_ip');
            }
            if (!Schema::hasColumn('attendances', 'check_in_longitude')) {
                $table->decimal('check_in_longitude', 10, 7)->nullable()->after('check_in_latitude');
            }
            if (!Schema::hasColumn('attendances', 'check_in_network_type')) {
                $table->string('check_in_network_type', 50)->nullable()->after('check_in_longitude');
            }
            if (!Schema::hasColumn('attendances', 'check_in_distance_meters')) {
                $table->decimal('check_in_distance_meters', 8, 2)->nullable()->after('check_in_network_type');
            }
            if (!Schema::hasColumn('attendances', 'check_in_verification_method')) {
                $table->string('check_in_verification_method', 20)->nullable()->after('check_in_distance_meters');
            }

            if (!Schema::hasColumn('attendances', 'check_out_ip')) {
                $table->string('check_out_ip', 45)->nullable()->after('check_out');
            }
            if (!Schema::hasColumn('attendances', 'check_out_latitude')) {
                $table->decimal('check_out_latitude', 10, 7)->nullable()->after('check_out_ip');
            }
            if (!Schema::hasColumn('attendances', 'check_out_longitude')) {
                $table->decimal('check_out_longitude', 10, 7)->nullable()->after('check_out_latitude');
            }
            if (!Schema::hasColumn('attendances', 'check_out_network_type')) {
                $table->string('check_out_network_type', 50)->nullable()->after('check_out_longitude');
            }
            if (!Schema::hasColumn('attendances', 'check_out_distance_meters')) {
                $table->decimal('check_out_distance_meters', 8, 2)->nullable()->after('check_out_network_type');
            }
            if (!Schema::hasColumn('attendances', 'check_out_verification_method')) {
                $table->string('check_out_verification_method', 20)->nullable()->after('check_out_distance_meters');
            }
        });
    }
};
