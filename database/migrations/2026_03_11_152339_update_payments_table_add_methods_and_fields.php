<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {

            $table->decimal('amount', 12, 2)->after('method');

            $table->string('transaction_code')->nullable()->after('amount');
        });

        DB::statement("
            ALTER TABLE payments 
            MODIFY method ENUM('COD','VNPAY','MOMO','BANK_TRANSFER')
        ");
    }

    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {

            $table->dropColumn('amount');
            $table->dropColumn('transaction_code');
        });

        DB::statement("
            ALTER TABLE payments 
            MODIFY method ENUM('COD','VNPAY')
        ");
    }
};
