<?php

namespace Database\Seeders;

use App\Models\Salary;
use App\Models\Staff;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SalarySeeder extends Seeder
{
    /**
     * Tính lương nhân viên tháng 3/2026 từ chấm công
     */
    public function run(): void
    {
        // Kiểm tra đã có salary tháng 3/2026 chưa
        $existingSalary = Salary::where('month', 3)->where('year', 2026)->count();

        if ($existingSalary > 0) {
            $this->command->info("ℹ️  Đã có " . $existingSalary . " records lương tháng 3/2026. Skip seeder này.");
            return;
        }

        // Lấy tất cả staff (ngoại trừ admin)
        $staffs = Staff::whereHas('user', function ($query) {
            $query->where('role', '!=', 'admin');
        })->get();

        if ($staffs->isEmpty()) {
            $this->command->error('Chưa có staff trong DB!');
            return;
        }

        $this->command->info("💰 Tính lương tháng 3/2026 cho " . $staffs->count() . " nhân viên...");

        // Tháng 3
        $month = 3;
        $year = 2026;

        $salaryCount = 0;

        foreach ($staffs as $staff) {
            // Lấy attendance tháng 3 của staff này
            $attendances = Attendance::where('staff_id', $staff->user_id)
                ->whereYear('work_date', $year)
                ->whereMonth('work_date', $month)
                ->get();

            // Tính tổng giờ làm
            $totalMinutes = 0;
            $dayCount = 0;

            foreach ($attendances as $att) {
                if ($att->check_in && $att->check_out) {
                    // Tính phút làm việc
                    try {
                        $checkIn = Carbon::createFromFormat('H:i:s', $att->check_in);
                        $checkOut = Carbon::createFromFormat('H:i:s', $att->check_out);

                        // Dùng abs() để đảm bảo dương
                        $minutes = abs($checkOut->diffInMinutes($checkIn));
                        $totalMinutes += $minutes;
                        $dayCount++;
                    } catch (\Exception $e) {
                        // Skip nếu parse lỗi
                        continue;
                    }
                }
            }

            $totalHours = $totalMinutes / 60;

            // Lấy hourly wage (ưu tiên official_hourly_wage > probation_hourly_wage)
            $hourlyWage = (float)($staff->official_hourly_wage ?? $staff->probation_hourly_wage ?? 0);

            // Tính lương (đảm bảo dương)
            $totalSalary = max(0, $totalHours * $hourlyWage);

            // Tạo salary record
            Salary::create([
                'staff_id' => $staff->user_id,
                'month' => $month,
                'year' => $year,
                'total_minutes' => (int)$totalMinutes,
                'total_hours' => (float)round($totalHours, 2),
                'total_salary' => (float)round($totalSalary, 2),
            ]);

            $this->command->line("  👤 {$staff->user->name}: {$dayCount} ngày, " . round($totalHours, 2) . "h × " . number_format($hourlyWage) . " = " . number_format($totalSalary) . " VNĐ");

            $salaryCount++;
        }

        $this->command->info("✅ Tính lương thành công cho {$salaryCount} nhân viên tháng 3/2026");
    }
}
