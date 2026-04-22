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
        // Tính toán absent_count từ ATTENDANCES
        // absent = những ngày chưa check-in hoặc chưa check-out
        
        // Bước 1: Tạo trigger Auto-update absent_count khi create/update/delete attendance
        $createTrigger = <<<SQL
            CREATE TRIGGER update_salary_absent_count_on_attendance
            AFTER INSERT ON attendances
            FOR EACH ROW
            BEGIN
                UPDATE salaries
                SET absent_count = COALESCE((
                    SELECT COUNT(DISTINCT work_date)
                    FROM attendances
                    WHERE staff_id = NEW.staff_id
                    AND MONTH(work_date) = MONTH(NEW.work_date)
                    AND YEAR(work_date) = YEAR(NEW.work_date)
                    AND check_in IS NULL
                ), 0)
                WHERE staff_id = NEW.staff_id
                AND MONTH = MONTH(NEW.work_date)
                AND YEAR = YEAR(NEW.work_date);
            END
        SQL;

        $updateTrigger = <<<SQL
            CREATE TRIGGER update_salary_absent_count_on_attendance_update
            AFTER UPDATE ON attendances
            FOR EACH ROW
            BEGIN
                UPDATE salaries
                SET absent_count = COALESCE((
                    SELECT COUNT(DISTINCT work_date)
                    FROM attendances
                    WHERE staff_id = NEW.staff_id
                    AND MONTH(work_date) = MONTH(NEW.work_date)
                    AND YEAR(work_date) = YEAR(NEW.work_date)
                    AND check_in IS NULL
                ), 0)
                WHERE staff_id = NEW.staff_id
                AND MONTH = MONTH(NEW.work_date)
                AND YEAR = YEAR(NEW.work_date);
            END
        SQL;

        // Bước 2: Tính toán lại tất cả existing absent_count
        DB::statement(<<<SQL
            UPDATE salaries s
            SET absent_count = COALESCE((
                SELECT COUNT(DISTINCT work_date)
                FROM attendances a
                WHERE a.staff_id = s.staff_id
                AND MONTH(a.work_date) = s.month
                AND YEAR(a.work_date) = s.year
                AND a.check_in IS NULL
            ), 0)
        SQL);

        // Bước 3: Tạo triggers (nếu MySQL hỗ trợ)
        try {
            DB::statement('DROP TRIGGER IF EXISTS update_salary_absent_count_on_attendance');
            DB::statement($createTrigger);
            
            DB::statement('DROP TRIGGER IF EXISTS update_salary_absent_count_on_attendance_update');
            DB::statement($updateTrigger);
        } catch (\Exception $e) {
            \Log::warning('Could not create triggers: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop triggers
        try {
            DB::statement('DROP TRIGGER IF EXISTS update_salary_absent_count_on_attendance');
            DB::statement('DROP TRIGGER IF EXISTS update_salary_absent_count_on_attendance_update');
        } catch (\Exception $e) {
            \Log::warning('Could not drop triggers: ' . $e->getMessage());
        }
    }
};
