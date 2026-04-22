<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\AllowedNetwork;
use App\Models\Attendance;
use App\Models\User;
use App\Http\Controllers\AttendanceController;
use Carbon\Carbon;

class SimulateAttendanceCheckin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * --cidr=*      one or more CIDR entries to seed (optional)
     * --ip=         the client IP to simulate
     * --staffId=    optional staff user id to use
     * --admin-ip=   optional: simulate admin current IP to record in cache
     */
    protected $signature = 'attendance:simulate-checkin {--cidr=*} {--ip=} {--staffId=} {--admin-ip=}';

    protected $description = 'Seed allowed_networks and simulate a staff check-in from given IP';

    public function handle(): int
    {
        $cidrs = $this->option('cidr');
        $ip = $this->option('ip');
        $staffId = $this->option('staffId');

        if (!$ip) {
            $this->error('Usage: php artisan attendance:simulate-checkin --ip=203.0.113.5 [--cidr=...] [--admin-ip=...] [--staffId=]');
            return 1;
        }

        // If CIDRs provided, reset allowed networks to provided list
        if (!empty($cidrs)) {
            AllowedNetwork::truncate();
            foreach ($cidrs as $c) {
                AllowedNetwork::create(['cidr' => $c, 'label' => 'simulated']);
            }
            Cache::forget('attendance.allowed_networks');
        }

        // If admin-ip provided, record it into admin_ips cache to simulate admin being on that network
        $adminIp = $this->option('admin-ip');
        if ($adminIp) {
            Cache::put('attendance.admin_ips', [$adminIp], 600);
            $this->info('Recorded admin ip in cache: ' . $adminIp);
        }

        // Determine staff user
        if ($staffId) {
            $staffUser = User::find($staffId);
            if (!$staffUser) {
                $this->error('staffId not found: ' . $staffId);
                return 1;
            }
        } else {
            $staffUser = User::where('role', '!=', 'admin')->first();
            if (!$staffUser) {
                $staffUser = User::first();
            }
        }

        $this->info('Using staff id: ' . $staffUser->id . ' (' . ($staffUser->name ?? 'N/A') . ')');

        // Login as staff
        Auth::loginUsingId($staffUser->id);

        // Prepare attendance for today
        $today = Carbon::now()->toDateString();
        $attendance = Attendance::firstOrCreate([
            'staff_id' => $staffUser->id,
            'work_date' => $today,
        ], [
            'shift' => 'morning',
            'expected_check_in' => '08:00:00',
            'expected_check_out' => '11:00:00',
        ]);

        // Reset check-in/check-out fields to ensure a fresh test
        $attendance->check_in = null;
        $attendance->check_out = null;
        $attendance->check_in_ip = null;
        $attendance->check_in_network_type = null;
        $attendance->check_in_verification_method = null;
        $attendance->is_late = 0;
        $attendance->is_completed = 0;
        $attendance->worked_minutes = null;
        $attendance->salary_amount = null;
        $attendance->save();

        // Build request with IP
        $request = Request::create('/attendances/check-in', 'POST', ['network_type' => 'WiFi']);
        $request->headers->set('X-Forwarded-For', $ip);
        $request->server->set('REMOTE_ADDR', $ip);

        $controller = new AttendanceController();
        $response = $controller->checkIn($request, $attendance);

        $this->info('HTTP status: ' . $response->getStatusCode());
        $this->line('Response: ' . $response->getContent());

        return 0;
    }
}
