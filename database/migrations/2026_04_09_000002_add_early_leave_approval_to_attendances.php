<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add columns for early leave approval workflow
     */
    public function up(): void
    {
        if (Schema::hasTable('attendances')) {
            Schema::table('attendances', function (Blueprint $table) {
                if (!Schema::hasColumn('attendances', 'early_leave_status')) {
                    $table->enum('early_leave_status', ['pending', 'approved', 'rejected', 'none'])->default('none')->after('is_early_leave');
                }

                if (!Schema::hasColumn('attendances', 'early_leave_pay_percent')) {
                    $table->integer('early_leave_pay_percent')->default(100)->after('early_leave_status');
                }

                if (!Schema::hasColumn('attendances', 'early_leave_approved_by')) {
                    $table->unsignedBigInteger('early_leave_approved_by')->nullable()->after('early_leave_pay_percent');
                }

                if (!Schema::hasColumn('attendances', 'early_leave_approved_at')) {
                    $table->timestamp('early_leave_approved_at')->nullable()->after('early_leave_approved_by');
                }

                if (!Schema::hasColumn('attendances', 'is_auto_checkout_forced')) {
                    $table->boolean('is_auto_checkout_forced')->default(false)->after('early_leave_approved_at');
                }
            });

            // Add foreign key in separate call to avoid issues
            Schema::table('attendances', function (Blueprint $table) {
                if (Schema::hasColumn('attendances', 'early_leave_approved_by')) {
                    try {
                        $table->foreign('early_leave_approved_by')->references('user_id')->on('staffs')->nullOnDelete();
                    } catch (\Exception $e) {
                        // Foreign key might already exist
                    }
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('attendances')) {
            Schema::table('attendances', function (Blueprint $table) {
                if (Schema::hasColumn('attendances', 'early_leave_approved_by')) {
                    $table->dropForeignKeyIfExists('attendances_early_leave_approved_by_foreign');
                    $table->dropColumn('early_leave_approved_by');
                }
                if (Schema::hasColumn('attendances', 'early_leave_approved_at')) {
                    $table->dropColumn('early_leave_approved_at');
                }
                if (Schema::hasColumn('attendances', 'early_leave_pay_percent')) {
                    $table->dropColumn('early_leave_pay_percent');
                }
                if (Schema::hasColumn('attendances', 'early_leave_status')) {
                    $table->dropColumn('early_leave_status');
                }
                if (Schema::hasColumn('attendances', 'is_auto_checkout_forced')) {
                    $table->dropColumn('is_auto_checkout_forced');
                }
            });
        }
    }
};
