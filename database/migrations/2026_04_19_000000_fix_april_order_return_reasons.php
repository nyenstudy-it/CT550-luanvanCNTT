<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration
{
    /**
     * Run the migrations.
     * Normalize `reason` values for `order_returns` created in April 2026.
     * This maps human-readable or legacy values to canonical codes used by the app.
     */
    public function up(): void
    {
        $from = '2026-04-01 00:00:00';
        $to = '2026-04-30 23:59:59';

        $rows = DB::table('order_returns')
            ->whereBetween('created_at', [$from, $to])
            ->get();

        foreach ($rows as $r) {
            $orig = (string) ($r->reason ?? '');
            $lower = mb_strtolower($orig);

            // If already a known code, skip
            $known = ['wrong_product', 'product_defect', 'other', 'defective', 'changed_mind', 'change_mind'];
            if (in_array($orig, $known, true)) {
                continue;
            }

            $new = null;

            if (str_contains($lower, 'nhầm') || str_contains($lower, 'nhan nham') || str_contains($lower, 'nhận nhầm') || str_contains($lower, 'nhan nham')) {
                $new = 'wrong_product';
            } elseif (str_contains($lower, 'lỗi') || str_contains($lower, 'hỏng') || str_contains($lower, 'hong') || str_contains($lower, 'defect') || str_contains($lower, 'defective')) {
                // prefer frontend code 'product_defect'
                $new = 'product_defect';
            } elseif (str_contains($lower, 'đổi') || str_contains($lower, 'doi') || str_contains($lower, 'change')) {
                $new = 'changed_mind';
            } else {
                $new = 'other';
            }

            if ($new && $new !== $orig) {
                DB::table('order_returns')->where('id', $r->id)->update(['reason' => $new]);
            }
        }
    }

    /**
     * Reverse the migrations.
     * No-op: original free-text reasons are not restored by this migration.
     */
    public function down(): void
    {
        // Intentionally empty.
    }
};
