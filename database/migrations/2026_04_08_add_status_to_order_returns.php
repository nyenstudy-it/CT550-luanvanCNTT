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
        Schema::table('order_returns', function (Blueprint $table) {
            // Add status column to track inspection workflow
            // requested -> approved/rejected -> given_to_shipper -> goods_received -> inspected_defective/inspected_good -> refunded
            $table->enum('status', [
                'requested',              // Khách yêu cầu
                'approved',               // Admin duyệt, chờ khách gửi shipper
                'rejected',               // Admin từ chối
                'given_to_shipper',       // Khách đã đưa hàng cho shipper
                'goods_received',         // Admin/Staff nhận hàng từ shipper
                'inspected_defective',    // Kiểm tra xong, xác nhận LỖI
                'inspected_good',         // Kiểm tra xong, OK
                'refunded'                // Đã hoàn tiền
            ])->default('requested')->after('reason');

            // Track who inspected and when
            $table->unsignedBigInteger('inspected_by')->nullable()->after('status');
            $table->timestamp('inspected_at')->nullable()->after('inspected_by');
            $table->text('inspection_notes')->nullable()->after('inspected_at');

            // Track admin approval/rejection
            $table->unsignedBigInteger('approved_by')->nullable()->after('inspection_notes');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->text('rejection_reason')->nullable()->after('approved_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_returns', function (Blueprint $table) {
            $table->dropColumn(['status', 'inspected_by', 'inspected_at', 'inspection_notes']);
        });
    }
};
