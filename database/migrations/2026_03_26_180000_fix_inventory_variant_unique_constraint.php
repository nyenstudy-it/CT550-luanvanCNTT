<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            $duplicateGroups = DB::table('inventories')
                ->selectRaw('product_variant_id, MIN(id) as keep_id, SUM(quantity) as total_quantity, COUNT(*) as row_count')
                ->groupBy('product_variant_id')
                ->havingRaw('COUNT(*) > 1')
                ->get();

            foreach ($duplicateGroups as $group) {
                DB::table('inventories')
                    ->where('id', $group->keep_id)
                    ->update(['quantity' => (int) $group->total_quantity]);

                DB::table('inventories')
                    ->where('product_variant_id', $group->product_variant_id)
                    ->where('id', '!=', $group->keep_id)
                    ->delete();
            }
        });

        Schema::table('inventories', function (Blueprint $table) {
            $table->unique('product_variant_id', 'inventories_product_variant_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->dropUnique('inventories_product_variant_id_unique');
        });
    }
};
