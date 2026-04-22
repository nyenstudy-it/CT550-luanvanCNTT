<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\User;
use App\Models\Staff;
use App\Models\Notification;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\IpUtils;
use App\Models\AllowedNetwork;
use Illuminate\Support\Facades\Cache;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        try {
            $this->recordAdminNetwork($request->ip());
        } catch (\Exception $e) {
            //
        }

        $query = Attendance::with('staff.user');
        if ($request->from_date) {
            $query->whereDate('work_date', '>=', $request->from_date);
        }

        if ($request->to_date) {
            $query->whereDate('work_date', '<=', $request->to_date);
        }

        if ($request->shift) {
            $query->where('shift', $request->shift);
        }
        if ($request->staff_id) {
            $query->where('staff_id', $request->staff_id);
        }

        $attendances = $query
            ->orderBy('work_date', 'desc')
            ->orderByRaw("FIELD(shift, 'afternoon', 'morning')")
            ->paginate(10);

        if ($request->status) {
            $attendances->setCollection(
                $attendances->getCollection()->filter(function ($item) use ($request) {
                    return $item->computed_status === $request->status;
                })
            );
        }

        $calendarEvents = $attendances->map(function ($a) {

            $shiftLabel = $a->shift === 'morning'
                ? 'Ca sáng'
                : 'Ca chiều';

            return [
                'title' => $a->staff->user->name . ' - ' . $shiftLabel,
                'start' => $a->work_date . 'T' . $a->expected_check_in,
                'end'   => $a->work_date . 'T' . $a->expected_check_out,
                'extendedProps' => [
                    'name' => $a->staff->user->name,
                    'date' => $a->work_date,
                    'shift' => $shiftLabel,
                    'expected_in' => $a->expected_check_in,
                    'expected_out' => $a->expected_check_out,
                    'check_in' => $a->check_in,
                    'check_out' => $a->check_out,
                    'worked_minutes' => $a->worked_minutes,
                    'salary' => $a->salary_amount,
                    'is_late' => $a->is_late,
                    'is_early_leave' => $a->is_early_leave,
                ],
            ];
        });

        $staffs = Staff::with('user')->get();

        $today = Carbon::today();
        $startOfWeek = $today->copy()->startOfWeek();
        $endOfWeek = $today->copy()->endOfWeek();

        $thisWeekSalary = Attendance::whereDate('work_date', '>=', $startOfWeek)
            ->whereDate('work_date', '<=', $endOfWeek)
            ->sum('salary_amount');

        $thisWeekMinutes = Attendance::whereDate('work_date', '>=', $startOfWeek)
            ->whereDate('work_date', '<=', $endOfWeek)
            ->sum('worked_minutes');
        $thisWeekHours = $thisWeekMinutes ? round($thisWeekMinutes / 60, 2) : 0;

        $thisWeekRevenue = Order::where('status', 'completed')
            ->whereBetween('created_at', [$startOfWeek->startOfDay(), $endOfWeek->endOfDay()])
            ->sum('total_amount');

        $weeklyStats = [];
        for ($i = 3; $i >= 0; $i--) {
            $weekStart = $today->copy()->subWeeks($i)->startOfWeek();
            $weekEnd = $today->copy()->subWeeks($i)->endOfWeek();

            $weeklySalary = Attendance::whereDate('work_date', '>=', $weekStart)
                ->whereDate('work_date', '<=', $weekEnd)
                ->sum('salary_amount');

            $weeklyMinutes = Attendance::whereDate('work_date', '>=', $weekStart)
                ->whereDate('work_date', '<=', $weekEnd)
                ->sum('worked_minutes');
            $weeklyHours = $weeklyMinutes ? round($weeklyMinutes / 60, 2) : 0;

            $weeklyRevenue = Order::where('status', 'completed')
                ->whereBetween('created_at', [$weekStart->startOfDay(), $weekEnd->endOfDay()])
                ->sum('total_amount');

            $weeklyStats[] = [
                'week' => $weekStart->format('d/m') . ' - ' . $weekEnd->format('d/m'),
                'salary' => $weeklySalary,
                'hours' => $weeklyHours,
                'revenue' => $weeklyRevenue,
                'shifts' => Attendance::whereDate('work_date', '>=', $weekStart)
                    ->whereDate('work_date', '<=', $weekEnd)
                    ->count(),
            ];
        }

        $allCalendarEvents = Attendance::whereDate('work_date', '>=', '2026-03-05')
            ->whereDate('work_date', '<=', Carbon::now()->toDateString())
            ->with('staff.user')
            ->orderBy('work_date')
            ->orderBy('shift')
            ->get()
            ->map(function ($a) {
                $shiftLabel = $a->shift === 'morning' ? 'Ca sáng' : 'Ca chiều';
                return [
                    'title' => $a->staff->user->name . ' - ' . $shiftLabel,
                    'start' => $a->work_date . 'T' . $a->expected_check_in,
                    'end'   => $a->work_date . 'T' . $a->expected_check_out,
                    'extendedProps' => [
                        'name' => $a->staff->user->name,
                        'date' => $a->work_date,
                        'shift' => $shiftLabel,
                    ],
                ];
            });

        return view('admin.attendances.index', compact(
            'attendances',
            'calendarEvents',
            'allCalendarEvents',
            'staffs',
            'thisWeekSalary',
            'thisWeekHours',
            'thisWeekRevenue',
            'weeklyStats'
        ));
    }

    /**
     * Record current admin IP into cache so staff on same public IP can check in.
     * Cache key: 'attendance.admin_ips' => array of IP strings
     */
    private function recordAdminNetwork(string $ip): void
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return;
        }

        $key = 'attendance.admin_ips';
        $ips = Cache::get($key, []);
        if (!in_array($ip, $ips, true)) {
            $ips[] = $ip;
            Cache::put($key, array_values($ips), 600);
        } else {
            Cache::put($key, array_values($ips), 600);
        }
    }

    /**
     * Load allowed networks from DB with caching. Falls back to empty array.
     */
    private function getAllowedNetworks(): array
    {
        return Cache::remember('attendance.allowed_networks', 300, function () {
            return AllowedNetwork::pluck('cidr')->toArray();
        });
    }

    public function staffIndex(Request $request)
    {
        $user = Auth::user();

        // Get month/year from request or use current
        $month = $request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);

        $query = Attendance::where('staff_id', $user->id)
            ->whereMonth('work_date', $month)
            ->whereYear('work_date', $year);

        $attendances = $query
            ->orderByRaw("CASE 
                WHEN check_in IS NULL THEN 1 
                ELSE 0 
            END DESC")
            ->orderBy('work_date', 'desc')
            ->orderByRaw("FIELD(shift, 'morning', 'afternoon')")
            ->paginate(10);

        // Calculate totals for selected month
        $totalWorkedMinutes = Attendance::where('staff_id', $user->id)
            ->whereMonth('work_date', $month)
            ->whereYear('work_date', $year)
            ->sum('worked_minutes');

        $totalSalary = Attendance::where('staff_id', $user->id)
            ->whereMonth('work_date', $month)
            ->whereYear('work_date', $year)
            ->sum('salary_amount');

        $serverNow = Carbon::now('Asia/Ho_Chi_Minh');

        return view('admin.attendances.staff_attendances', compact('attendances', 'totalWorkedMinutes', 'totalSalary', 'month', 'year', 'serverNow'));
    }

    public function create()
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        $staffs = Staff::whereHas('user')
            ->with('user')
            ->get();

        // Lấy attendance đúng quan hệ
        $calendarEvents = Attendance::whereHas('staff.user')
            ->with('staff.user')
            ->get()
            ->map(function ($a) {
                return [
                    'title' => $a->staff->user->name . ' - ' .
                        ($a->shift === 'morning' ? 'Ca sáng' : 'Ca chiều'),
                    'start' => $a->work_date . 'T' . $a->expected_check_in,
                    'end'   => $a->work_date . 'T' . $a->expected_check_out,
                ];
            });

        return view('admin.attendances.create', compact(
            'staffs',
            'calendarEvents'
        ));
    }

    public function store(Request $request)
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        $request->validate([
            'staff_id' => [
                'required',
                'exists:users,id',
                Rule::unique('attendances')->where(
                    fn($q) =>
                    $q->where('staff_id', $request->staff_id)
                        ->where('work_date', $request->work_date)
                        ->where('shift', $request->shift)
                ),
            ],
            'work_date' => 'required|date',
            'shift' => 'required|in:morning,afternoon',
            'expected_check_in' => 'required',
            'expected_check_out' => 'required',
        ], [
            'staff_id.required' => 'Vui lòng chọn nhân viên.',
            'staff_id.exists' => 'Nhân viên được chọn không hợp lệ.',
            'staff_id.unique' => 'Nhân viên này đã được phân ca trong ngày và ca đã chọn.',
            'work_date.required' => 'Vui lòng chọn ngày làm việc.',
            'work_date.date' => 'Ngày làm việc không đúng định dạng.',
            'shift.required' => 'Vui lòng chọn ca làm việc.',
            'shift.in' => 'Ca làm việc không hợp lệ.',
            'expected_check_in.required' => 'Vui lòng nhập giờ vào dự kiến.',
            'expected_check_out.required' => 'Vui lòng nhập giờ ra dự kiến.',
        ]);

        Attendance::create($request->only([
            'staff_id',
            'work_date',
            'shift',
            'expected_check_in',
            'expected_check_out',
        ]));

        return redirect()
            ->route('admin.attendances.index')
            ->with('success', 'Phân ca làm việc thành công.');
    }

    public function edit($id)
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        $attendance = Attendance::with('staff.user')->findOrFail($id);

        $staffs = Staff::with('user')->get();

        $calendarEvents = Attendance::whereHas('staff.user')
            ->with('staff.user')
            ->get()
            ->map(function ($a) {
                return [
                    'title' => $a->staff->user->name . ' - ' .
                        ($a->shift === 'morning' ? 'Ca sáng' : 'Ca chiều'),
                    'start' => $a->work_date . 'T' . $a->expected_check_in,
                    'end'   => $a->work_date . 'T' . $a->expected_check_out,
                ];
            });

        return view('admin.attendances.edit', compact(
            'attendance',
            'staffs',
            'calendarEvents'
        ));
    }

    public function update(Request $request, $id)
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        $request->validate([
            'staff_id' => 'required|exists:users,id',
            'work_date' => 'required|date',
            'shift' => 'required|in:morning,afternoon',
            'expected_check_in' => 'required',
            'expected_check_out' => 'required',
        ], [
            'staff_id.required' => 'Vui lòng chọn nhân viên.',
            'staff_id.exists' => 'Nhân viên được chọn không hợp lệ.',
            'work_date.required' => 'Vui lòng chọn ngày làm việc.',
            'work_date.date' => 'Ngày làm việc không đúng định dạng.',
            'shift.required' => 'Vui lòng chọn ca làm việc.',
            'shift.in' => 'Ca làm việc không hợp lệ.',
            'expected_check_in.required' => 'Vui lòng nhập giờ vào dự kiến.',
            'expected_check_out.required' => 'Vui lòng nhập giờ ra dự kiến.',
        ]);

        $attendance = Attendance::findOrFail($id);

        $attendance->update($request->only([
            'work_date',
            'shift',
            'expected_check_in',
            'expected_check_out',
        ]));

        return redirect()
            ->route('admin.attendances.index')
            ->with('success', 'Cập nhật phân ca thành công!');
    }

    public function destroy($id)
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        $attendance = Attendance::findOrFail($id);

        if ($attendance->check_in) {
            return back()->with('error', 'Không thể xoá ca đã chấm công!');
        }

        $attendance->delete();

        return back()->with('success', 'Xoá ca thành công!');
    }

    public function checkIn(Request $request, Attendance $attendance)
    {
        $user = Auth::user();
        $now  = Carbon::now('Asia/Ho_Chi_Minh');

        $validated = $request->validate([
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'network_type' => 'nullable|string|max:50',
        ]);

        if ($attendance->staff_id !== $user->id) {
            abort(403);
        }

        if ($attendance->work_date !== $now->toDateString()) {
            return response()->json(['status' => 'error', 'message' => 'Chưa tới ngày làm việc'], 400);
        }

        DB::beginTransaction();
        try {
            $attendance = Attendance::lockForUpdate()
                ->where('id', $attendance->id)
                ->where('staff_id', $user->id)
                ->where('work_date', $now->toDateString())
                ->firstOrFail();

            if ($attendance->check_in) {
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => 'Bạn đã check-in'], 400);
            }

            // IP-only policy: chấp nhận nếu IP client nằm trong danh sách mạng cho phép
            $allowedNetworks = $this->getAllowedNetworks();
            $clientIp = $request->ip();
            $allowed = $this->isAllowedNetwork($clientIp, $allowedNetworks);
            if (!$allowed) {
                return response()->json(['status' => 'error', 'message' => 'Chấm công chỉ hợp lệ khi kết nối Wi-Fi công ty. Vui lòng chuyển sang mạng Wi-Fi công ty để chấm công.'], 400);
            }

            $expectedStart = Carbon::parse(
                $attendance->work_date . ' ' . $attendance->expected_check_in,
                'Asia/Ho_Chi_Minh'
            );

            $lateMinutes = $expectedStart->diffInMinutes($now, false);

            if ($lateMinutes < 0) {
                return response()->json(['status' => 'error', 'message' => 'Chưa tới giờ làm'], 400);
            }

            if ($lateMinutes > 120) {
                return response()->json(['status' => 'error', 'message' => 'Trễ quá 2 tiếng'], 400);
            }

            $isLate = $lateMinutes > 15;

            $verificationMethod = $allowed ? 'wifi' : 'none';

            $attendance->update([
                'check_in' => $now,
                'is_late'  => $isLate ? 1 : 0,
                // Lưu IP và loại mạng (doanh nghiệp muốn giữ network_type)
                'check_in_ip' => $clientIp,
                'check_in_network_type' => $validated['network_type'] ?? null,
                'check_in_verification_method' => $verificationMethod,
            ]);

            $adminRecipients = Notification::recipientIdsForGroups(['admin']);
            $shiftLabel = $attendance->shift === 'morning' ? 'ca sáng' : 'ca chiều';

            $locationInfo = '';

            Notification::createForRecipients($adminRecipients, [
                'type' => 'attendance_check_in',
                'title' => 'Nhân viên vừa chấm công',
                'content' => $user->name . ' đã check-in ' . $shiftLabel . ' ngày ' . $attendance->work_date . '.' . $locationInfo,
                'related_id' => $attendance->id,
            ]);

            $successMsg = 'Check-in thành công';
            if ($isLate) {
                $successMsg .= ' (Trễ ' . $lateMinutes . ' phút)';
            }

            DB::commit();
            return response()->json(['status' => 'success', 'message' => $successMsg]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Lỗi khi check-in: ' . $e->getMessage()], 500);
        }
    }

    public function checkOut(Request $request, Attendance $attendance)
    {
        $user  = Auth::user();
        $staff = $user->staff;

        $validated = $request->validate([
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'network_type' => 'nullable|string|max:50',
        ]);

        if (!$staff || $attendance->staff_id !== $staff->user_id) {
            abort(403);
        }

        DB::beginTransaction();
        try {
            $attendance = Attendance::lockForUpdate()
                ->where('id', $attendance->id)
                ->where('staff_id', $staff->user_id)
                ->firstOrFail();

            if (!$attendance->check_in) {
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => 'Chưa check-in.'], 400);
            }

            if ($attendance->check_out) {
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => 'Đã check-out rồi.'], 400);
            }

            $accessResult = $this->evaluateAttendanceAccess(
                $request->ip(),
                $validated['latitude'] ?? null,
                $validated['longitude'] ?? null
            );

            if (!$accessResult['allowed']) {
                return response()->json(['status' => 'error', 'message' => $accessResult['message']], 400);
            }

            $now = Carbon::now('Asia/Ho_Chi_Minh');

            $checkIn = Carbon::parse($attendance->check_in, 'Asia/Ho_Chi_Minh');

            $expectedStart = Carbon::parse(
                $attendance->work_date . ' ' . $attendance->expected_check_in,
                'Asia/Ho_Chi_Minh'
            );

            $expectedEnd = Carbon::parse(
                $attendance->work_date . ' ' . $attendance->expected_check_out,
                'Asia/Ho_Chi_Minh'
            );

            // Xác định về sớm trước, sau đó mới áp dụng quy tắc auto-checkout.
            $isEarlyLeave = $now->lt($expectedEnd);

            if ($isEarlyLeave) {
                $reasonType = $request->input('reason_type');
                $reason     = $request->input('reason');

                if (!$reason || $reasonType !== 'early') {
                    DB::rollBack();
                    return response()->json(['status' => 'error', 'message' => 'Bạn đang check-out trước giờ. Vui lòng nhập lý do.'], 400);
                }

                $attendance->early_leave_reason = $reason;
                $attendance->early_leave_status = 'pending';
            }

            // Auto-checkout: nếu quá 2 giờ sau giờ kết ca thì coi như quên checkout và chốt về giờ kết ca.
            $twoHoursAfterExpectedEnd = $expectedEnd->copy()->addHours(2);

            if ($now->gte($twoHoursAfterExpectedEnd)) {
                $checkOut = $expectedEnd;
            } else {
                $checkOut = $now;
            }

            if ($checkOut->lte($checkIn)) {
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => 'Thời gian không hợp lệ.'], 400);
            }

            $attendance->is_early_leave = $isEarlyLeave ? 1 : 0;

            // Quy tắc tính công:
            // - Nếu vào trễ > 15 phút: tính theo thời gian thực tế (check_in -> check_out).
            // - Nếu vào trễ <= 15 phút:
            //   - Về sớm: tính từ giờ dự kiến vào ca đến giờ checkout thực tế.
            //   - Không về sớm: tính đủ thời lượng ca.

            $expectedStart = Carbon::parse(
                $attendance->work_date . ' ' . $attendance->expected_check_in,
                'Asia/Ho_Chi_Minh'
            );

            $expectedEnd = Carbon::parse(
                $attendance->work_date . ' ' . $attendance->expected_check_out,
                'Asia/Ho_Chi_Minh'
            );

            $expectedMinutes = $expectedStart->diffInMinutes($expectedEnd);

            $lateMinutes = $expectedStart->diffInMinutes($checkIn, false);

            if ($lateMinutes > 0) {
                $attendance->is_late = 1;
            }

            if ($attendance->is_early_leave) {
                if ($lateMinutes > 15) {
                    $workedMinutes = $checkIn->diffInMinutes($checkOut);
                } else {
                    $workedMinutes = $expectedStart->diffInMinutes($checkOut);
                }
            } else if ($lateMinutes > 15) {
                $workedMinutes = $checkIn->diffInMinutes($checkOut);
            } else {
                $workedMinutes = $expectedMinutes;
            }

            if ($workedMinutes < 0) {
                $workedMinutes = 0;
            }

            if ($staff->employment_status === 'official') {
                $hourlyRate = $staff->official_hourly_wage ?? 20000;
            } else {
                $hourlyRate = $staff->probation_hourly_wage ?? 15000;
            }

            $salary = ($workedMinutes / 60) * $hourlyRate;

            $attendance->check_out      = $checkOut;
            $attendance->worked_minutes = $workedMinutes;
            $attendance->salary_amount  = round($salary);
            $attendance->is_completed   = 1;
            $attendance->save();
            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Check-out thành công.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Lỗi khi check-out: ' . $e->getMessage()], 500);
        }
    }

    public function pending()
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        $attendances = Attendance::with('staff.user')
            ->where('early_leave_status', 'pending')
            ->orderBy('work_date', 'desc')
            ->get();

        return view('admin.attendances.pending', compact('attendances'));
    }

    public function approveEarly(Attendance $attendance)
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        if ($attendance->early_leave_status !== 'pending') {
            return back()->with('error', 'Không có yêu cầu duyệt.');
        }

        $attendance->early_leave_status = 'approved';

        [$workedMinutes, $hourlyRate] = $this->calculateEarlyLeaveSalaryByDecision($attendance, true);

        $attendance->worked_minutes = $workedMinutes;
        $attendance->salary_amount = round(($workedMinutes / 60) * $hourlyRate);

        $attendance->save();

        return back()->with('success', 'Đã duyệt về sớm. Lương tính đến giờ nhân viên xin về sớm.');
    }

    public function rejectEarly(Attendance $attendance)
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        if ($attendance->early_leave_status !== 'pending') {
            return back()->with('error', 'Không có yêu cầu duyệt.');
        }

        $attendance->early_leave_status = 'rejected';

        [$workedMinutes, $hourlyRate] = $this->calculateEarlyLeaveSalaryByDecision($attendance, false);

        $attendance->worked_minutes = $workedMinutes;
        $attendance->salary_amount = round(($workedMinutes / 60) * $hourlyRate);

        $attendance->save();

        return back()->with('success', 'Đã từ chối về sớm. Lương được tính đến hết ca.');
    }

    private function calculateEarlyLeaveSalaryByDecision(Attendance $attendance, bool $isApproved): array
    {
        $staff = $attendance->staff;

        $actualCheckIn = Carbon::parse($attendance->work_date . ' ' . $attendance->check_in, 'Asia/Ho_Chi_Minh');
        $actualCheckOut = Carbon::parse($attendance->work_date . ' ' . $attendance->check_out, 'Asia/Ho_Chi_Minh');
        $expectedStart = Carbon::parse($attendance->work_date . ' ' . $attendance->expected_check_in, 'Asia/Ho_Chi_Minh');
        $expectedEnd = Carbon::parse($attendance->work_date . ' ' . $attendance->expected_check_out, 'Asia/Ho_Chi_Minh');

        $lateMinutes = $expectedStart->diffInMinutes($actualCheckIn, false);
        $salaryStart = $lateMinutes > 15 ? $actualCheckIn : $expectedStart;
        $salaryEnd = $isApproved ? $actualCheckOut : $expectedEnd;

        $workedMinutes = $salaryStart->diffInMinutes($salaryEnd, false);
        if ($workedMinutes < 0) {
            $workedMinutes = 0;
        }

        if ($staff->employment_status === 'official') {
            $hourlyRate = $staff->official_hourly_wage ?? 20000;
        } else {
            $hourlyRate = $staff->probation_hourly_wage ?? 15000;
        }

        return [$workedMinutes, $hourlyRate];
    }
    public function submitEarlyReason(Request $request, Attendance $attendance)
    {
        $staff = Auth::user()->staff;

        if (!$staff || $attendance->staff_id !== $staff->user_id) {
            abort(403);
        }

        if (!$attendance->is_early_leave) {
            return back()->with('error', 'Ca này không có về sớm.');
        }

        if (in_array($attendance->early_leave_status, ['approved', 'rejected'])) {
            return back()->with('error', 'Yêu cầu đã được xử lý.');
        }

        $request->validate([
            'early_leave_reason' => 'required|string|max:500'
        ]);

        $attendance->update([
            'early_leave_reason' => $request->early_leave_reason,
            'early_leave_status' => 'pending'
        ]);

        return back()->with('success', 'Đã gửi lý do về sớm.');
    }

    private function evaluateAttendanceAccess(string $ip, ?float $latitude, ?float $longitude): array
    {
        // Simplified: only network-based check (company requested "same network with admin")
        $allowedNetworks = $this->getAllowedNetworks();
        $allowByNetwork = $this->isAllowedNetwork($ip, $allowedNetworks);

        if (!$allowByNetwork) {
            return [
                'allowed' => false,
                'method' => 'none',
                'distance_meters' => null,
                'message' => 'Chấm công bị từ chối: cần kết nối cùng mạng Wi-Fi công ty.',
            ];
        }

        return [
            'allowed' => true,
            'method' => 'wifi',
            'distance_meters' => null,
            'message' => null,
        ];
    }

    private function isAllowedNetwork(string $ip, array $allowedNetworks): bool
    {
        if (empty($allowedNetworks)) {
            return false;
        }

        return IpUtils::checkIp($ip, $allowedNetworks);
    }

    private function calculateDistanceMeters(
        float $originLat,
        float $originLng,
        float $targetLat,
        float $targetLng
    ): float {
        $earthRadius = 6371000;

        $latFrom = deg2rad($originLat);
        $lngFrom = deg2rad($originLng);
        $latTo = deg2rad($targetLat);
        $lngTo = deg2rad($targetLng);

        $latDelta = $latTo - $latFrom;
        $lngDelta = $lngTo - $lngFrom;

        $angle = 2 * asin(sqrt(
            pow(sin($latDelta / 2), 2)
                + cos($latFrom) * cos($latTo) * pow(sin($lngDelta / 2), 2)
        ));

        return $earthRadius * $angle;
    }
}
