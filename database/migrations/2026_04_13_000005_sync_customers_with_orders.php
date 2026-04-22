<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix xung đột: 32 customers nhưng 167 orders
        // Bước 1: Tìm tất cả user_id từ orders mà không có customer record
        // Bước 2: Tạo customer records cho những users đó
        
        $orderCustomers = DB::table('orders')
            ->distinct()
            ->pluck('customer_id');

        $existingCustomers = DB::table('customers')
            ->pluck('user_id');

        $missingCustomers = $orderCustomers->diff($existingCustomers);

        if ($missingCustomers->count() > 0) {
            foreach ($missingCustomers as $userId) {
                // Lấy thông tin user
                $user = DB::table('users')->find($userId);
                
                if ($user) {
                    // Tạo customer record nếu user là customer role
                    if ($user->role === 'customer') {
                        DB::table('customers')->insertOrIgnore([
                            'user_id' => $userId,
                            'phone' => '0000000000',
                            'address' => null,
                            'province' => null,
                            'district' => null,
                            'ward' => null,
                            'is_default_address' => 0,
                            'date_of_birth' => null,
                            'gender' => null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }

        // Bước 3: Recalculate order totals to ensure consistency
        // Verify total_amount = SUM(order_items.subtotal)
        DB::statement(<<<SQL
            UPDATE orders o
            SET total_amount = COALESCE((
                SELECT SUM(subtotal)
                FROM order_items
                WHERE order_id = o.id
            ), 0)
            WHERE id IN (
                SELECT DISTINCT order_id FROM order_items
            )
        SQL);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Không delete customer records được tạo (data sensitivity)
        // Chỉ log warning
        \Log::warning('Migration down called for fixing customers. Customer records remain in database.');
    }
};
