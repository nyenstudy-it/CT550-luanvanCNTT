<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->decimal('refund_amount', 10, 2)->nullable()->after('amount');
            $table->string('refund_status')->nullable()->after('refund_amount'); // pending, completed
            $table->timestamp('refund_at')->nullable()->after('refund_status');
        });
    }

    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['refund_amount', 'refund_status', 'refund_at']);
        });
    }
};
